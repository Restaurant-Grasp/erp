<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\GoodsReceiptNote;
use App\Models\PurchaseInvoice;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\ProductSerialNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:purchases.returns.view')->only(['index', 'show']);
        $this->middleware('permission:purchases.returns.create')->only(['create', 'store']);
    }

    /**
     * Display a listing of purchase returns.
     */
    public function index(Request $request)
    {
        $query = PurchaseReturn::with(['vendor', 'grn', 'invoice', 'createdBy']);

        // Search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_no', 'like', "%{$search}%")
                  ->orWhereHas('vendor', function($vendorQuery) use ($search) {
                      $vendorQuery->where('company_name', 'like', "%{$search}%")
                                  ->orWhere('vendor_code', 'like', "%{$search}%");
                  })
                  ->orWhereHas('grn', function($grnQuery) use ($search) {
                      $grnQuery->where('grn_no', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Return type filter
        if ($request->filled('return_type')) {
            $query->where('return_type', $request->return_type);
        }

        // Vendor filter
        if ($request->filled('vendor')) {
            $query->where('vendor_id', $request->vendor);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('return_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('return_date', '<=', $request->date_to);
        }

        $returns = $query->orderBy('created_at', 'desc')->paginate(15);
        $vendors = Vendor::where('status', 'active')->orderBy('company_name')->get();

        return view('purchase.returns.index', compact('returns', 'vendors'));
    }

    /**
     * Show the form for creating a new purchase return.
     */
    public function create()
    {
        $vendors = Vendor::where('status', 'active')->orderBy('company_name')->get();
        $grns = GoodsReceiptNote::where('status', '!=', 'draft')
                               ->with('vendor')
                               ->orderBy('created_at', 'desc')
                               ->get();

        return view('purchase.returns.create', compact('vendors', 'grns'));
    }

    /**
     * Store a newly created purchase return.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'grn_id' => 'nullable|exists:goods_receipt_notes,id',
            'invoice_id' => 'nullable|exists:purchase_invoices,id',
            'return_date' => 'required|date',
            'return_type' => 'required|in:damaged,defective,wrong_item,excess',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.grn_item_id' => 'nullable|integer',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.serial_number_id' => 'nullable|exists:product_serial_numbers,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.reason' => 'required|string',
            'items.*.replacement_required' => 'nullable|boolean',
            'items.*.replacement_po_no' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            // Calculate total amount
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += floatval($item['quantity']) * floatval($item['unit_price'] ?? 0);
            }

            // Create purchase return
            $return = PurchaseReturn::create([
                'vendor_id' => $validated['vendor_id'],
                'grn_id' => $validated['grn_id'],
                'invoice_id' => $validated['invoice_id'],
                'return_date' => $validated['return_date'],
                'return_type' => $validated['return_type'],
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'notes' => $validated['notes'],
                'created_by' => Auth::id(),
            ]);

            // Create return items
            foreach ($request->items as $itemData) {
                $returnItem = PurchaseReturnItem::create([
                    'return_id' => $return->id,
                    'grn_item_id' => $itemData['grn_item_id'] ?? null,
                    'product_id' => $itemData['product_id'],
                    'serial_number_id' => $itemData['serial_number_id'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'] ?? 0,
                    'total_amount' => floatval($itemData['quantity']) * floatval($itemData['unit_price'] ?? 0),
                    'reason' => $itemData['reason'],
                    'replacement_required' => !empty($itemData['replacement_required']),
                    'replacement_po_no' => $itemData['replacement_po_no'] ?? null,
                ]);

                // Update serial number status if applicable
                if (!empty($itemData['serial_number_id'])) {
                    ProductSerialNumber::where('id', $itemData['serial_number_id'])
                                      ->update([
                                          'current_status' => 'returned',
                                          'warranty_status' => 'void'
                                      ]);
                }

                // Create inventory transaction for return
                \App\Models\InventoryTransaction::create([
                    'transaction_date' => $validated['return_date'],
                    'transaction_type' => 'return',
                    'reference_type' => 'purchase_return',
                    'reference_id' => $return->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => -floatval($itemData['quantity']), // Negative for outgoing
                    'notes' => "Return: {$return->return_no} - {$itemData['reason']}",
                    'created_by' => Auth::id(),
                ]);
            }

            DB::commit();
            return redirect()->route('purchase.returns.show', $return)
                           ->with('success', 'Purchase Return created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error creating purchase return: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified purchase return.
     */
    public function show(PurchaseReturn $return)
    {
        $return->load([
            'vendor',
            'grn.items.product',
            'invoice',
            'items.product',
            'items.grnItem',
            'items.serialNumber',
            'createdBy'
        ]);

        return view('purchase.returns.show', compact('return'));
    }

    /**
     * Approve the purchase return.
     */
    public function approve(PurchaseReturn $return)
    {
        if ($return->status !== 'pending') {
            return redirect()->route('purchase.returns.show', $return)
                           ->with('error', 'Return is not pending approval.');
        }

        $return->update(['status' => 'approved']);

        return redirect()->route('purchase.returns.show', $return)
                       ->with('success', 'Purchase Return approved successfully.');
    }

    /**
     * Mark return as returned to vendor.
     */
    public function markReturned(PurchaseReturn $return)
    {
        if ($return->status !== 'approved') {
            return redirect()->route('purchase.returns.show', $return)
                           ->with('error', 'Return must be approved first.');
        }

        $return->update(['status' => 'returned']);

        return redirect()->route('purchase.returns.show', $return)
                       ->with('success', 'Return marked as returned to vendor.');
    }

    /**
     * Mark return as credited.
     */
    public function markCredited(PurchaseReturn $return)
    {
        if ($return->status !== 'returned') {
            return redirect()->route('purchase.returns.show', $return)
                           ->with('error', 'Return must be returned to vendor first.');
        }

        $return->update(['status' => 'credited']);

        return redirect()->route('purchase.returns.show', $return)
                       ->with('success', 'Return marked as credited.');
    }

    /**
     * Get GRN items for return creation
     */
    public function getGrnItems(Request $request)
    {
        $grnId = $request->grn_id;
        
        $grn = GoodsReceiptNote::with(['items.product', 'vendor'])
                              ->find($grnId);

        if (!$grn) {
            return response()->json(['error' => 'GRN not found'], 404);
        }

        $items = $grn->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_code' => $item->product->product_code,
                'quantity' => $item->quantity,
                'accepted_quantity' => $item->accepted_quantity,
                'damaged_quantity' => $item->damaged_quantity,
                'has_serial_number' => $item->product->has_serial_number,
            ];
        });

        // Get serial numbers for this GRN
        $serialNumbers = ProductSerialNumber::where('grn_id', $grnId)
                                           ->with('product')
                                           ->get()
                                           ->groupBy('grn_item_id');

        return response()->json([
            'grn' => [
                'id' => $grn->id,
                'grn_no' => $grn->grn_no,
                'vendor_id' => $grn->vendor_id,
                'vendor_name' => $grn->vendor->company_name
            ],
            'items' => $items,
            'serial_numbers' => $serialNumbers
        ]);
    }

    /**
     * Get replacement status report
     */
    public function replacementReport(Request $request)
    {
        $query = PurchaseReturn::with(['vendor', 'items.product'])
                              ->whereHas('items', function($q) {
                                  $q->where('replacement_required', true);
                              });

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Vendor filter
        if ($request->filled('vendor')) {
            $query->where('vendor_id', $request->vendor);
        }

        $returns = $query->orderBy('return_date', 'desc')->paginate(15);
        $vendors = Vendor::where('status', 'active')->orderBy('company_name')->get();

        return view('purchase.returns.replacement-report', compact('returns', 'vendors'));
    }

    /**
     * Update the specified purchase return.
     */
    public function update(Request $request, PurchaseReturn $return)
    {
        // Only allow updating if status is pending
        if ($return->status !== 'pending') {
            return redirect()->route('purchase.returns.show', $return)
                           ->with('error', 'Cannot update return that is not pending.');
        }

        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'return_date' => 'required|date',
            'return_type' => 'required|in:damaged,defective,wrong_item,excess',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.reason' => 'required|string',
            'items.*.replacement_required' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Calculate total amount
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += floatval($item['quantity']) * floatval($item['unit_price'] ?? 0);
            }

            // Update purchase return
            $return->update([
                'vendor_id' => $validated['vendor_id'],
                'return_date' => $validated['return_date'],
                'return_type' => $validated['return_type'],
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'],
            ]);

            // Delete existing items and recreate
            $return->items()->delete();
            
            foreach ($request->items as $itemData) {
                PurchaseReturnItem::create([
                    'return_id' => $return->id,
                    'grn_item_id' => $itemData['grn_item_id'] ?? null,
                    'product_id' => $itemData['product_id'],
                    'serial_number_id' => $itemData['serial_number_id'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'] ?? 0,
                    'total_amount' => floatval($itemData['quantity']) * floatval($itemData['unit_price'] ?? 0),
                    'reason' => $itemData['reason'],
                    'replacement_required' => !empty($itemData['replacement_required']),
                    'replacement_po_no' => $itemData['replacement_po_no'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('purchase.returns.show', $return)
                           ->with('success', 'Purchase Return updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error updating purchase return: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified purchase return.
     */
    public function destroy(PurchaseReturn $return)
    {
        // Only allow deleting if status is pending
        if ($return->status !== 'pending') {
            return redirect()->route('purchase.returns.index')
                           ->with('error', 'Cannot delete return that is not pending.');
        }

        DB::beginTransaction();
        try {
            // Restore serial number status if applicable
            foreach ($return->items as $item) {
                if ($item->serial_number_id) {
                    ProductSerialNumber::where('id', $item->serial_number_id)
                                      ->update([
                                          'current_status' => 'in_stock',
                                          'warranty_status' => 'active'
                                      ]);
                }
            }

            $return->items()->delete();
            $return->delete();

            DB::commit();
            return redirect()->route('purchase.returns.index')
                           ->with('success', 'Purchase Return deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('purchase.returns.index')
                           ->with('error', 'Error deleting purchase return: ' . $e->getMessage());
        }
    }

    /**
     * Bulk approve returns
     */
    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'return_ids' => 'required|array|min:1',
            'return_ids.*' => 'exists:purchase_returns,id'
        ]);

        $updated = PurchaseReturn::whereIn('id', $validated['return_ids'])
                                ->where('status', 'pending')
                                ->update(['status' => 'approved']);

        return redirect()->route('purchase.returns.index')
                       ->with('success', "Successfully approved {$updated} purchase returns.");
    }

    /**
     * Export returns to Excel/CSV
     */
    public function export(Request $request)
    {
        // Placeholder for future export functionality
        return redirect()->route('purchase.returns.index')
                       ->with('info', 'Export functionality will be implemented soon.');
    }

    /**
     * Get return statistics for dashboard
     */
    public function getReturnStats()
    {
        $stats = [
            'total_returns' => PurchaseReturn::count(),
            'pending_returns' => PurchaseReturn::where('status', 'pending')->count(),
            'approved_returns' => PurchaseReturn::where('status', 'approved')->count(),
            'returned_to_vendor' => PurchaseReturn::where('status', 'returned')->count(),
            'credited_returns' => PurchaseReturn::where('status', 'credited')->count(),
            'replacement_required' => PurchaseReturn::whereHas('items', function($q) {
                $q->where('replacement_required', true);
            })->count(),
            'total_return_value' => PurchaseReturn::sum('total_amount'),
            'monthly_returns' => PurchaseReturn::whereMonth('return_date', now()->month)
                                             ->whereYear('return_date', now()->year)
                                             ->count()
        ];

        return response()->json($stats);
    }

    /**
     * Get return trends for charts
     */
    public function getReturnTrends(Request $request)
    {
        $period = $request->get('period', '6_months'); // 6_months, 1_year, custom
        
        $startDate = match($period) {
            '6_months' => now()->subMonths(6),
            '1_year' => now()->subYear(),
            'custom' => $request->get('start_date', now()->subMonths(6)),
            default => now()->subMonths(6)
        };

        $endDate = $period === 'custom' ? $request->get('end_date', now()) : now();

        $trends = PurchaseReturn::selectRaw("
                DATE_FORMAT(return_date, '%Y-%m') as month,
                COUNT(*) as return_count,
                SUM(total_amount) as return_value,
                SUM(CASE WHEN return_type = 'damaged' THEN 1 ELSE 0 END) as damaged_count,
                SUM(CASE WHEN return_type = 'defective' THEN 1 ELSE 0 END) as defective_count
            ")
            ->whereBetween('return_date', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json($trends);
    }
}