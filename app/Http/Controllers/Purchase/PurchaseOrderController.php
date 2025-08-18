<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Service;
use App\Models\Uom;
use App\Models\Tax;
use App\Models\ProductVendor;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchaseInvoice;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:purchases.po.view')->only(['index', 'show']);
        $this->middleware('permission:purchases.po.create')->only(['create', 'store']);
        $this->middleware('permission:purchases.po.edit')->only(['edit', 'update']);
        $this->middleware('permission:purchases.po.delete')->only('destroy');
        $this->middleware('permission:purchases.po.approve')->only(['approve', 'reject']);
    }

    /**
     * Display a listing of purchase orders.
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['vendor', 'createdBy', 'approvedBy']);

        // Search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_no', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhereHas('vendor', function($vendorQuery) use ($search) {
                      $vendorQuery->where('company_name', 'like', "%{$search}%")
                                  ->orWhere('vendor_code', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Approval status filter
        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        // Vendor filter
        if ($request->filled('vendor')) {
            $query->where('vendor_id', $request->vendor);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('po_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('po_date', '<=', $request->date_to);
        }

        $purchaseOrders = $query->orderBy('created_at', 'desc')->paginate(15);
        $vendors = Vendor::where('status', 'active')->orderBy('company_name')->get();

        return view('purchase.orders.index', compact('purchaseOrders', 'vendors'));
    }

    /**
     * Show the form for creating a new purchase order.
     */
    public function create()
    {
        $vendors = Vendor::where('status', 'active')->orderBy('company_name')->get();
        $products = Product::where('is_active', 1)->orderBy('name')->get();
        $services = Service::where('status', 1)->orderBy('name')->get();
        $uoms = Uom::where('status', 1)->orderBy('name')->get();
        $taxes = Tax::where('status', 1)->orderBy('name')->get();

        return view('purchase.orders.create', compact('vendors', 'products', 'services', 'uoms', 'taxes'));
    }

    /**
     * Store a newly created purchase order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'po_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:po_date',
            'reference_no' => 'nullable|string|max:100',
            'currency' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.0001',
            'discount_type' => 'required|in:percentage,amount',
            'discount_value' => 'nullable|numeric|min:0',
            'terms_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:product,service',
            'items.*.item_id' => 'required|integer',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.uom_id' => 'nullable|exists:uom,id',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_type' => 'required|in:percentage,amount',
            'items.*.discount_value' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = 0;
            $totalTax = 0;
            $items = [];

            foreach ($request->items as $item) {
                $quantity = floatval($item['quantity']);
                $unitPrice = floatval($item['unit_price']);
                $discountValue = floatval($item['discount_value'] ?? 0);
                $taxRate = floatval($item['tax_rate'] ?? 0);

                // Calculate line total before discount
                $lineTotal = $quantity * $unitPrice;

                // Calculate discount
                $discountAmount = 0;
                if ($discountValue > 0) {
                    if ($item['discount_type'] === 'percentage') {
                        $discountAmount = ($lineTotal * $discountValue) / 100;
                    } else {
                        $discountAmount = $discountValue;
                    }
                }

                // Calculate amount after discount
                $afterDiscount = $lineTotal - $discountAmount;

                // Calculate tax
                $taxAmount = ($afterDiscount * $taxRate) / 100;

                // Calculate total for this line
                $totalAmount = $afterDiscount + $taxAmount;

                $items[] = [
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'description' => $item['description'] ?? '',
                    'quantity' => $quantity,
                    'remaining_quantity' => $quantity,
                    'uom_id' => $item['uom_id'] ?? null,
                    'unit_price' => $unitPrice,
                    'discount_type' => $item['discount_type'],
                    'discount_value' => $discountValue,
                    'discount_amount' => $discountAmount,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'sort_order' => count($items) + 1
                ];

                $subtotal += $lineTotal;
                $totalTax += $taxAmount;
            }

            // Calculate PO level discount
            $discountAmount = 0;
            if (!empty($validated['discount_value'])) {
                if ($validated['discount_type'] === 'percentage') {
                    $discountAmount = ($subtotal * $validated['discount_value']) / 100;
                } else {
                    $discountAmount = $validated['discount_value'];
                }
            }

            $totalAmount = $subtotal - $discountAmount + $totalTax;

            // Create purchase order
            $purchaseOrder = PurchaseOrder::create([
                'vendor_id' => $validated['vendor_id'],
                'po_date' => $validated['po_date'],
                'delivery_date' => $validated['delivery_date'],
                'reference_no' => $validated['reference_no'],
                'currency' => $validated['currency'],
                'exchange_rate' => $validated['exchange_rate'],
                'subtotal' => $subtotal,
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'] ?? 0,
                'discount_amount' => $discountAmount,
                'tax_amount' => $totalTax,
                'total_amount' => $totalAmount,
                'terms_conditions' => $validated['terms_conditions'],
                'status' => 'draft',
                'approval_status' => 'pending',
                'notes' => $validated['notes'],
                'created_by' => Auth::id(),
            ]);

            // Create purchase order items
            foreach ($items as $item) {
                PurchaseOrderItem::create(array_merge($item, [
                    'po_id' => $purchaseOrder->id
                ]));
            }

            DB::commit();
            return redirect()->route('purchase.orders.index', $purchaseOrder)
                           ->with('success', 'Purchase Order created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error creating purchase order: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified purchase order.
     */
    public function show(PurchaseOrder $order)
    {
        $order->load([
            'vendor',
            'items.product',
            'items.service',
            'items.uom',
            'createdBy',
            'approvedBy'
        ]);

        // Get related invoices and GRNs
        $invoices = $order->invoices()->with('items')->get();
        $grns = $order->grns()->with('items')->get();

        return view('purchase.orders.show', compact('order', 'invoices', 'grns'));
    }

    /**
     * Show the form for editing the purchase order.
     */
    public function edit(PurchaseOrder $order)
    {
        // Only allow editing if status is draft
        if ($order->status !== 'draft') {
            return redirect()->route('purchase.orders.show', $order)
                           ->with('error', 'Cannot edit purchase order that is not in draft status.');
        }

        $order->load(['items']);
        $vendors = Vendor::where('status', 'active')->orderBy('company_name')->get();
        $products = Product::where('is_active', 1)->orderBy('name')->get();
        $services = Service::where('status', 1)->orderBy('name')->get();
        $uoms = Uom::where('status', 1)->orderBy('name')->get();
        $taxes = Tax::where('status', 1)->orderBy('name')->get();

        return view('purchase.orders.edit', compact('order', 'vendors', 'products', 'services', 'uoms', 'taxes'));
    }

    /**
     * Update the specified purchase order.
     */
    public function update(Request $request, PurchaseOrder $order)
    {
        // Only allow updating if status is draft
        if ($order->status !== 'draft') {
            return redirect()->route('purchase.orders.show', $order)
                           ->with('error', 'Cannot update purchase order that is not in draft status.');
        }

        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'po_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:po_date',
            'reference_no' => 'nullable|string|max:100',
            'currency' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.0001',
            'discount_type' => 'required|in:percentage,amount',
            'discount_value' => 'nullable|numeric|min:0',
            'terms_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:product,service',
            'items.*.item_id' => 'required|integer',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.uom_id' => 'nullable|exists:uom,id',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_type' => 'required|in:percentage,amount',
            'items.*.discount_value' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Recalculate totals (same logic as store)
            $subtotal = 0;
            $totalTax = 0;
            $items = [];

            foreach ($request->items as $item) {
                $quantity = floatval($item['quantity']);
                $unitPrice = floatval($item['unit_price']);
                $discountValue = floatval($item['discount_value'] ?? 0);
                $taxRate = floatval($item['tax_rate'] ?? 0);

                $lineTotal = $quantity * $unitPrice;

                $discountAmount = 0;
                if ($discountValue > 0) {
                    if ($item['discount_type'] === 'percentage') {
                        $discountAmount = ($lineTotal * $discountValue) / 100;
                    } else {
                        $discountAmount = $discountValue;
                    }
                }

                $afterDiscount = $lineTotal - $discountAmount;
                $taxAmount = ($afterDiscount * $taxRate) / 100;
                $totalAmount = $afterDiscount + $taxAmount;

                $items[] = [
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'description' => $item['description'] ?? '',
                    'quantity' => $quantity,
                    'remaining_quantity' => $quantity,
                    'uom_id' => $item['uom_id'] ?? null,
                    'unit_price' => $unitPrice,
                    'discount_type' => $item['discount_type'],
                    'discount_value' => $discountValue,
                    'discount_amount' => $discountAmount,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'sort_order' => count($items) + 1
                ];

                $subtotal += $lineTotal;
                $totalTax += $taxAmount;
            }

            $discountAmount = 0;
            if (!empty($validated['discount_value'])) {
                if ($validated['discount_type'] === 'percentage') {
                    $discountAmount = ($subtotal * $validated['discount_value']) / 100;
                } else {
                    $discountAmount = $validated['discount_value'];
                }
            }

            $totalAmount = $subtotal - $discountAmount + $totalTax;

            // Update purchase order
            $order->update([
                'vendor_id' => $validated['vendor_id'],
                'po_date' => $validated['po_date'],
                'delivery_date' => $validated['delivery_date'],
                'reference_no' => $validated['reference_no'],
                'currency' => $validated['currency'],
                'exchange_rate' => $validated['exchange_rate'],
                'subtotal' => $subtotal,
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'] ?? 0,
                'discount_amount' => $discountAmount,
                'tax_amount' => $totalTax,
                'total_amount' => $totalAmount,
                'terms_conditions' => $validated['terms_conditions'],
                'notes' => $validated['notes'],
            ]);

            // Delete existing items and recreate
            $order->items()->delete();
            foreach ($items as $item) {
                PurchaseOrderItem::create(array_merge($item, [
                    'po_id' => $order->id
                ]));
            }

            DB::commit();
            return redirect()->route('purchase.orders.index', $order)
                           ->with('success', 'Purchase Order updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error updating purchase order: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Approve the purchase order.
     */
    public function approve(Request $request, PurchaseOrder $order)
    {
        $validated = $request->validate([
            'approval_notes' => 'nullable|string|max:1000'
        ]);

        if ($order->approval_status !== 'pending') {
            return redirect()->route('purchase.orders.show', $order)
                           ->with('error', 'Purchase order is not pending approval.');
        }

        DB::beginTransaction();
        try {
            $order->update([
                'approval_status' => 'approved',
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_date' => now(),
                'approval_notes' => $validated['approval_notes']
            ]);

            // Auto-convert to invoice after approval
            $this->convertToInvoice($order);

            DB::commit();
            return redirect()->route('purchase.orders.show', $order)
                           ->with('success', 'Purchase Order approved successfully and converted to invoice.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error approving purchase order: ' . $e->getMessage());
        }
    }

    /**
     * Reject the purchase order.
     */
    public function reject(Request $request, PurchaseOrder $order)
    {
        $validated = $request->validate([
            'approval_notes' => 'required|string|max:1000'
        ]);

        if ($order->approval_status !== 'pending') {
            return redirect()->route('purchase.orders.show', $order)
                           ->with('error', 'Purchase order is not pending approval.');
        }

        $order->update([
            'approval_status' => 'rejected',
            'status' => 'cancelled',
            'approved_by' => Auth::id(),
            'approved_date' => now(),
            'approval_notes' => $validated['approval_notes']
        ]);

        return redirect()->route('purchase.orders.show', $order)
                       ->with('success', 'Purchase Order rejected.');
    }

    /**
     * Remove the specified purchase order.
     */
    public function destroy(PurchaseOrder $order)
    {
        // Only allow deleting if status is draft
        if ($order->status !== 'draft') {
            return redirect()->route('purchase.orders.index')
                           ->with('error', 'Cannot delete purchase order that is not in draft status.');
        }

        DB::beginTransaction();
        try {
            $order->items()->delete();
            $order->delete();

            DB::commit();
            return redirect()->route('purchase.orders.index')
                           ->with('success', 'Purchase Order deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('purchase.orders.index')
                           ->with('error', 'Error deleting purchase order: ' . $e->getMessage());
        }
    }

    /**
     * Get vendor products with pricing
     */
    public function getVendorProducts(Request $request)
    {
        $vendorId = $request->vendor_id;
        
        $products = ProductVendor::with(['product.uom', 'product.category'])
                                ->where('vendor_id', $vendorId)
                                ->orderBy('is_preferred', 'desc')
                                ->get()
                                ->map(function ($item) {
                                    return [
                                        'id' => $item->product->id,
                                        'name' => $item->product->name,
                                        'code' => $item->product->product_code,
                                        'uom' => $item->product->uom ? $item->product->uom->name : null,
                                        'uom_id' => $item->product->uom_id,
                                        'vendor_price' => $item->vendor_price,
                                        'cost_price' => $item->product->cost_price,
                                        'is_preferred' => $item->is_preferred,
                                        'has_serial_number' => $item->product->has_serial_number
                                    ];
                                });

        return response()->json($products);
    }

    /**
     * Convert Purchase Order to Invoice
     */
    private function convertToInvoice(PurchaseOrder $order)
    {
        $invoice = PurchaseInvoice::create([
            'vendor_id' => $order->vendor_id,
            'po_id' => $order->id,
            'invoice_date' => now()->toDateString(),
            'invoice_type' => 'po_conversion',
            'currency' => $order->currency,
            'exchange_rate' => $order->exchange_rate,
            'subtotal' => $order->subtotal,
            'discount_amount' => $order->discount_amount,
            'tax_amount' => $order->tax_amount,
            'total_amount' => $order->total_amount,
            'balance_amount' => $order->total_amount,
            'payment_terms' => 30,
            'due_date' => now()->addDays(30),
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);

        // Copy items
        foreach ($order->items as $item) {
            PurchaseInvoiceItem::create([
                'invoice_id' => $invoice->id,
                'po_item_id' => $item->id,
                'item_type' => $item->item_type,
                'item_id' => $item->item_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'uom_id' => $item->uom_id,
                'unit_price' => $item->unit_price,
                'discount_type' => $item->discount_type,
                'discount_value' => $item->discount_value,
                'discount_amount' => $item->discount_amount,
                'tax_rate' => $item->tax_rate,
                'tax_amount' => $item->tax_amount,
                'total_amount' => $item->total_amount,
                'sort_order' => $item->sort_order,
            ]);
        }

        // Call empty account migration function
        $this->accountMigration($invoice);

        return $invoice;
    }

    /**
     * Account migration placeholder
     */
    private function accountMigration($invoice)
    {
        // Empty function for future account integration
        // Will be implemented when accounting system integration is explained
    }
}