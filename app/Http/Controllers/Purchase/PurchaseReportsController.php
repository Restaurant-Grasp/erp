<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseInvoice;
use App\Models\GoodsReceiptNote;
use App\Models\PurchaseReturn;
use App\Models\Vendor;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:purchases.reports.view');
    }

    /**
     * Purchase Summary Report
     */
    public function purchaseSummary(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vendorId = $request->get('vendor_id');
        $status = $request->get('status');

        // Purchase Orders Summary
        $poQuery = PurchaseOrder::whereBetween('po_date', [$startDate, $endDate]);
        if ($vendorId) $poQuery->where('vendor_id', $vendorId);
        if ($status) $poQuery->where('status', $status);

        $purchaseOrders = $poQuery->with('vendor')->get();
        
        $poSummary = [
            'total_pos' => $purchaseOrders->count(),
            'total_amount' => $purchaseOrders->sum('total_amount'),
            'pending_approval' => $purchaseOrders->where('approval_status', 'pending')->count(),
            'approved' => $purchaseOrders->where('approval_status', 'approved')->count(),
            'rejected' => $purchaseOrders->where('approval_status', 'rejected')->count(),
            'partially_received' => $purchaseOrders->where('status', 'partial')->count(),
            'fully_received' => $purchaseOrders->where('status', 'received')->count(),
        ];

        // Purchase Invoices Summary
        $invoiceQuery = PurchaseInvoice::whereBetween('invoice_date', [$startDate, $endDate]);
        if ($vendorId) $invoiceQuery->where('vendor_id', $vendorId);

        $purchaseInvoices = $invoiceQuery->with('vendor')->get();
        
        $invoiceSummary = [
            'total_invoices' => $purchaseInvoices->count(),
            'total_amount' => $purchaseInvoices->sum('total_amount'),
            'paid_amount' => $purchaseInvoices->sum('paid_amount'),
            'outstanding_amount' => $purchaseInvoices->sum('balance_amount'),
            'direct_invoices' => $purchaseInvoices->where('invoice_type', 'direct')->count(),
            'po_conversion_invoices' => $purchaseInvoices->where('invoice_type', 'po_conversion')->count(),
            'overdue_invoices' => $purchaseInvoices->where('status', 'overdue')->count(),
        ];

        // Monthly Trend Data (Last 12 months)
        $monthlyTrend = PurchaseOrder::select(
                DB::raw('DATE_FORMAT(po_date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as total_pos'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->where('po_date', '>=', Carbon::now()->subMonths(12))
            ->groupBy(DB::raw('DATE_FORMAT(po_date, "%Y-%m")'))
            ->orderBy('month')
            ->get();

        // Top Vendors by Purchase Amount
        $topVendors = PurchaseOrder::select(
                'vendors.company_name',
                'vendors.vendor_code',
                DB::raw('COUNT(purchase_orders.id) as total_pos'),
                DB::raw('SUM(purchase_orders.total_amount) as total_amount')
            )
            ->join('vendors', 'purchase_orders.vendor_id', '=', 'vendors.id')
            ->whereBetween('purchase_orders.po_date', [$startDate, $endDate])
            ->groupBy('vendors.id', 'vendors.company_name', 'vendors.vendor_code')
            ->orderBy('total_amount', 'desc')
            ->limit(10)
            ->get();

        // Product Category Analysis
        $categoryAnalysis = PurchaseOrder::select(
                'categories.name as category_name',
                DB::raw('COUNT(DISTINCT purchase_orders.id) as total_pos'),
                DB::raw('SUM(purchase_order_items.quantity) as total_quantity'),
                DB::raw('SUM(purchase_order_items.total_amount) as total_amount')
            )
            ->join('purchase_order_items', 'purchase_orders.id', '=', 'purchase_order_items.po_id')
            ->join('products', function($join) {
                $join->on('purchase_order_items.item_id', '=', 'products.id')
                     ->where('purchase_order_items.item_type', '=', 'product');
            })
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('purchase_orders.po_date', [$startDate, $endDate])
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_amount', 'desc')
            ->get();

        $vendors = Vendor::where('status', 'active')->orderBy('company_name')->get();

        return view('purchase.reports.purchase-summary', compact(
            'poSummary', 'invoiceSummary', 'monthlyTrend', 'topVendors', 
            'categoryAnalysis', 'vendors', 'startDate', 'endDate', 'vendorId', 'status'
        ));
    }

    /**
     * Vendor Performance Report
     */
    public function vendorPerformance(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $vendorId = $request->get('vendor_id');

        $query = DB::table('vendors')
            ->leftJoin('purchase_orders', function($join) use ($startDate, $endDate) {
                $join->on('vendors.id', '=', 'purchase_orders.vendor_id')
                     ->whereBetween('purchase_orders.po_date', [$startDate, $endDate]);
            })
            ->leftJoin('goods_receipt_notes', 'purchase_orders.id', '=', 'goods_receipt_notes.po_id')
            ->leftJoin('purchase_returns', 'goods_receipt_notes.id', '=', 'purchase_returns.grn_id')
            ->select(
                'vendors.id',
                'vendors.company_name',
                'vendors.vendor_code',
                'vendors.payment_terms',
                DB::raw('COUNT(DISTINCT purchase_orders.id) as total_pos'),
                DB::raw('SUM(purchase_orders.total_amount) as total_purchase_amount'),
                DB::raw('AVG(purchase_orders.total_amount) as avg_order_value'),
                DB::raw('COUNT(DISTINCT goods_receipt_notes.id) as total_grns'),
                DB::raw('COUNT(DISTINCT purchase_returns.id) as total_returns'),
                DB::raw('SUM(purchase_returns.total_amount) as total_return_amount'),
                DB::raw('AVG(DATEDIFF(goods_receipt_notes.grn_date, purchase_orders.po_date)) as avg_delivery_days'),
                DB::raw('SUM(CASE WHEN purchase_orders.status = "received" THEN 1 ELSE 0 END) as completed_pos'),
                DB::raw('SUM(CASE WHEN purchase_orders.approval_status = "approved" AND DATEDIFF(CURDATE(), purchase_orders.po_date) <= vendors.payment_terms THEN 1 ELSE 0 END) as on_time_deliveries')
            )
            ->where('vendors.status', 'active');

        if ($vendorId) {
            $query->where('vendors.id', $vendorId);
        }

        $vendorPerformance = $query->groupBy(
                'vendors.id', 'vendors.company_name', 'vendors.vendor_code', 'vendors.payment_terms'
            )
            ->having('total_pos', '>', 0)
            ->orderBy('total_purchase_amount', 'desc')
            ->get();

        // Calculate performance metrics
        $vendorPerformance->transform(function ($vendor) {
            $vendor->return_percentage = $vendor->total_purchase_amount > 0 
                ? ($vendor->total_return_amount / $vendor->total_purchase_amount) * 100 
                : 0;
            
            $vendor->completion_rate = $vendor->total_pos > 0 
                ? ($vendor->completed_pos / $vendor->total_pos) * 100 
                : 0;
            
            $vendor->on_time_delivery_rate = $vendor->total_pos > 0 
                ? ($vendor->on_time_deliveries / $vendor->total_pos) * 100 
                : 0;

            // Performance score calculation (weighted average)
            $vendor->performance_score = (
                ($vendor->completion_rate * 0.4) + 
                ($vendor->on_time_delivery_rate * 0.4) + 
                ((100 - $vendor->return_percentage) * 0.2)
            );

            return $vendor;
        });

        $vendors = Vendor::where('status', 'active')->orderBy('company_name')->get();

        return view('purchase.reports.vendor-performance', compact(
            'vendorPerformance', 'vendors', 'startDate', 'endDate', 'vendorId'
        ));
    }

    /**
     * Pending Approvals Report
     */
    public function pendingApprovals(Request $request)
    {
        $vendorId = $request->get('vendor_id');
        $ageFilter = $request->get('age_filter'); // today, week, month, all

        $query = PurchaseOrder::with(['vendor', 'createdBy'])
            ->where('approval_status', 'pending');

        if ($vendorId) {
            $query->where('vendor_id', $vendorId);
        }

        // Apply age filter
        switch ($ageFilter) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                break;
            default:
                // All pending approvals
                break;
        }

        $pendingApprovals = $query->orderBy('created_at', 'desc')->get();

        // Calculate aging
        $pendingApprovals->transform(function ($po) {
            $po->days_pending = Carbon::now()->diffInDays($po->created_at);
            $po->urgency_level = $this->getUrgencyLevel($po->days_pending);
            return $po;
        });

        // Summary statistics
        $summary = [
            'total_pending' => $pendingApprovals->count(),
            'total_amount' => $pendingApprovals->sum('total_amount'),
            'urgent_count' => $pendingApprovals->where('urgency_level', 'urgent')->count(),
            'high_count' => $pendingApprovals->where('urgency_level', 'high')->count(),
            'medium_count' => $pendingApprovals->where('urgency_level', 'medium')->count(),
            'low_count' => $pendingApprovals->where('urgency_level', 'low')->count(),
            'avg_pending_days' => $pendingApprovals->avg('days_pending'),
            'oldest_pending_days' => $pendingApprovals->max('days_pending'),
        ];

        $vendors = Vendor::where('status', 'active')->orderBy('company_name')->get();

        return view('purchase.reports.pending-approvals', compact(
            'pendingApprovals', 'summary', 'vendors', 'vendorId', 'ageFilter'
        ));
    }

    /**
     * GRN Status Report
     */
    public function grnStatus(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vendorId = $request->get('vendor_id');
        $status = $request->get('status');

        $query = GoodsReceiptNote::with(['vendor', 'purchaseOrder', 'purchaseInvoice'])
            ->whereBetween('grn_date', [$startDate, $endDate]);

        if ($vendorId) $query->where('vendor_id', $vendorId);
        if ($status) $query->where('status', $status);

        $grns = $query->orderBy('grn_date', 'desc')->get();

        // GRN Summary
        $grnSummary = [
            'total_grns' => $grns->count(),
            'draft_grns' => $grns->where('status', 'draft')->count(),
            'partial_grns' => $grns->where('status', 'partial')->count(),
            'completed_grns' => $grns->where('status', 'completed')->count(),
            'total_items_received' => 0,
            'total_items_damaged' => 0,
            'total_returns_created' => 0,
        ];

        // Calculate item statistics
        foreach ($grns as $grn) {
            $grnSummary['total_items_received'] += $grn->total_accepted_quantity;
            $grnSummary['total_items_damaged'] += $grn->total_damaged_quantity;
            $grnSummary['total_returns_created'] += $grn->returns->count();
        }

        // Pending GRNs (POs without complete GRNs)
        $pendingGrnQuery = PurchaseOrder::with(['vendor', 'grns'])
            ->where('approval_status', 'approved')
            ->whereIn('status', ['approved', 'partial'])
            ->whereBetween('po_date', [$startDate, $endDate]);

        if ($vendorId) $pendingGrnQuery->where('vendor_id', $vendorId);

        $pendingGrns = $pendingGrnQuery->get()->filter(function ($po) {
            return $po->received_percentage < 100;
        });

        // GRN Efficiency Analysis
        $grnEfficiency = DB::table('goods_receipt_notes')
            ->join('purchase_orders', 'goods_receipt_notes.po_id', '=', 'purchase_orders.id')
            ->join('vendors', 'goods_receipt_notes.vendor_id', '=', 'vendors.id')
            ->select(
                'vendors.company_name',
                'vendors.vendor_code',
                DB::raw('COUNT(goods_receipt_notes.id) as total_grns'),
                DB::raw('SUM(CASE WHEN goods_receipt_notes.status = "completed" THEN 1 ELSE 0 END) as completed_grns'),
                DB::raw('AVG(DATEDIFF(goods_receipt_notes.grn_date, purchase_orders.po_date)) as avg_grn_delay_days'),
                DB::raw('COUNT(DISTINCT purchase_returns.id) as total_returns')
            )
            ->leftJoin('purchase_returns', 'goods_receipt_notes.id', '=', 'purchase_returns.grn_id')
            ->whereBetween('goods_receipt_notes.grn_date', [$startDate, $endDate])
            ->groupBy('vendors.id', 'vendors.company_name', 'vendors.vendor_code')
            ->having('total_grns', '>', 0)
            ->orderBy('total_grns', 'desc')
            ->get();

        // Calculate completion rates
        $grnEfficiency->transform(function ($item) {
            $item->completion_rate = $item->total_grns > 0 
                ? ($item->completed_grns / $item->total_grns) * 100 
                : 0;
            return $item;
        });

        // Serial Number Summary
        $serialNumberSummary = DB::table('product_serial_numbers')
            ->join('goods_receipt_notes', 'product_serial_numbers.grn_id', '=', 'goods_receipt_notes.id')
            ->whereBetween('goods_receipt_notes.grn_date', [$startDate, $endDate])
            ->select(
                DB::raw('COUNT(*) as total_serials'),
                DB::raw('SUM(CASE WHEN warranty_status = "active" THEN 1 ELSE 0 END) as active_warranty'),
                DB::raw('SUM(CASE WHEN warranty_status = "expired" THEN 1 ELSE 0 END) as expired_warranty'),
                DB::raw('SUM(CASE WHEN warranty_status = "void" THEN 1 ELSE 0 END) as void_warranty'),
                DB::raw('SUM(CASE WHEN current_status = "in_stock" THEN 1 ELSE 0 END) as in_stock'),
                DB::raw('SUM(CASE WHEN current_status = "sold" THEN 1 ELSE 0 END) as sold'),
                DB::raw('SUM(CASE WHEN current_status = "returned" THEN 1 ELSE 0 END) as returned')
            )
            ->first();

        $vendors = Vendor::where('status', 'active')->orderBy('company_name')->get();

        return view('purchase.reports.grn-status', compact(
            'grns', 'grnSummary', 'pendingGrns', 'grnEfficiency', 'serialNumberSummary',
            'vendors', 'startDate', 'endDate', 'vendorId', 'status'
        ));
    }

    /**
     * Purchase Analytics Dashboard
     */
    public function analyticsDashboard(Request $request)
    {
        $period = $request->get('period', 'month'); // month, quarter, year

        switch ($period) {
            case 'quarter':
                $startDate = Carbon::now()->startOfQuarter();
                $endDate = Carbon::now()->endOfQuarter();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
        }

        // Key Metrics
        $metrics = [
            'total_purchase_value' => PurchaseOrder::whereBetween('po_date', [$startDate, $endDate])->sum('total_amount'),
            'total_pos' => PurchaseOrder::whereBetween('po_date', [$startDate, $endDate])->count(),
            'pending_approvals' => PurchaseOrder::where('approval_status', 'pending')->count(),
            'overdue_invoices' => PurchaseInvoice::where('status', 'overdue')->sum('balance_amount'),
            'avg_order_value' => PurchaseOrder::whereBetween('po_date', [$startDate, $endDate])->avg('total_amount'),
            'vendor_count' => Vendor::where('status', 'active')->count(),
        ];

        // Purchase Trends (Daily for current month)
        $trendData = PurchaseOrder::select(
                DB::raw('DATE(po_date) as date'),
                DB::raw('COUNT(*) as po_count'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->whereBetween('po_date', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(po_date)'))
            ->orderBy('date')
            ->get();

        // Top Products by Purchase Value
        $topProducts = DB::table('purchase_order_items')
            ->join('purchase_orders', 'purchase_order_items.po_id', '=', 'purchase_orders.id')
            ->join('products', function($join) {
                $join->on('purchase_order_items.item_id', '=', 'products.id')
                     ->where('purchase_order_items.item_type', '=', 'product');
            })
            ->select(
                'products.name',
                'products.product_code',
                DB::raw('SUM(purchase_order_items.quantity) as total_quantity'),
                DB::raw('SUM(purchase_order_items.total_amount) as total_amount')
            )
            ->whereBetween('purchase_orders.po_date', [$startDate, $endDate])
            ->groupBy('products.id', 'products.name', 'products.product_code')
            ->orderBy('total_amount', 'desc')
            ->limit(10)
            ->get();

        return view('purchase.reports.analytics-dashboard', compact(
            'metrics', 'trendData', 'topProducts', 'period', 'startDate', 'endDate'
        ));
    }

    /**
     * Get urgency level based on pending days
     */
    private function getUrgencyLevel($days)
    {
        if ($days >= 7) return 'urgent';
        if ($days >= 5) return 'high';
        if ($days >= 3) return 'medium';
        return 'low';
    }

    /**
     * Export purchase summary to Excel
     */
    public function exportPurchaseSummary(Request $request)
    {
        // This would integrate with a package like Laravel Excel
        // For now, return JSON data that can be processed by frontend
        
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $data = PurchaseOrder::with(['vendor', 'items.product'])
            ->whereBetween('po_date', [$startDate, $endDate])
            ->get()
            ->map(function ($po) {
                return [
                    'PO Number' => $po->po_no,
                    'Date' => $po->po_date->format('Y-m-d'),
                    'Vendor' => $po->vendor->company_name,
                    'Total Amount' => $po->total_amount,
                    'Status' => $po->status,
                    'Approval Status' => $po->approval_status,
                    'Received %' => $po->received_percentage,
                ];
            });

        return response()->json($data);
    }
}