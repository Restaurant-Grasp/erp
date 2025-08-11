<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Service;
use App\Models\UOM;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class PurchaseInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:purchases.invoices.view')->only(['index', 'show']);
        $this->middleware('permission:purchases.invoices.create')->only(['create', 'store', 'createFromPo']);
        $this->middleware('permission:purchases.invoices.edit')->only(['edit', 'update']);
        $this->middleware('permission:purchases.invoices.delete')->only('destroy');
    }

    /**
     * Display a listing of purchase invoices.
     */
    public function index(Request $request)
    {
        $query = PurchaseInvoice::with(['vendor', 'purchaseOrder', 'createdBy']);

        // Search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhere('vendor_invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('vendor', function($vendorQuery) use ($search) {
                      $vendorQuery->where('company_name', 'like', "%{$search}%")
                                  ->orWhere('vendor_code', 'like', "%{$search}%");
                  })
                  ->orWhereHas('purchaseOrder', function($poQuery) use ($search) {
                      $poQuery->where('po_no', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Invoice type filter
        if ($request->filled('invoice_type')) {
            $query->where('invoice_type', $request->invoice_type);
        }

        // Vendor filter
        if ($request->filled('vendor')) {
            $query->where('vendor_id', $request->vendor);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15);
        $vendors = Vendor::where('status', 'active')->orderBy('company_name')->get();

        return view('purchase.invoices.index', compact('invoices', 'vendors'));
    }

    /**
     * Show the form for creating a new purchase invoice.
     */
    public function create()
    {
        $vendors = Vendor::where('status', 'active')->orderBy('company_name')->get();
        $products = Product::where('is_active', 1)->orderBy('name')->get();
        $services = Service::where('status', 1)->orderBy('name')->get();
        $uoms = UOM::where('status', 1)->orderBy('name')->get();
        $taxes = Tax::where('status', 1)->orderBy('name')->get();

        return view('purchase.invoices.create', compact('vendors', 'products', 'services', 'uoms', 'taxes'));
    }

    /**
     * Show form to create invoice from PO
     */
    public function createFromPo(PurchaseOrder $order)
    {
        if ($order->approval_status !== 'approved') {
            return redirect()->route('purchase.orders.show', $order)
                           ->with('error', 'Purchase Order must be approved first.');
        }

        $order->load(['vendor', 'items']);
        $uoms = UOM::where('status', 1)->orderBy('name')->get();
        $taxes = Tax::where('status', 1)->orderBy('name')->get();

        return view('purchase.invoices.create-from-po', compact('order', 'uoms', 'taxes'));
    }

    /**
     * Store a newly created purchase invoice.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'po_id' => 'nullable|exists:purchase_orders,id',
            'vendor_invoice_no' => 'nullable|string|max:100',
            'invoice_date' => 'required|date',
            'currency' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.0001',
            'payment_terms' => 'required|integer|min:0',
            'discount_type' => 'required|in:percentage,amount',
            'discount_value' => 'nullable|numeric|min:0',
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
                    'po_item_id' => $item['po_item_id'] ?? null,
                    'description' => $item['description'] ?? '',
                    'quantity' => $quantity,
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

            // Calculate invoice level discount
            $discountAmount = 0;
            if (!empty($validated['discount_value'])) {
                if ($validated['discount_type'] === 'percentage') {
                    $discountAmount = ($subtotal * $validated['discount_value']) / 100;
                } else {
                    $discountAmount = $validated['discount_value'];
                }
            }

            $totalAmount = $subtotal - $discountAmount + $totalTax;
            $dueDate = Carbon::parse($validated['invoice_date'])->addDays((int) $validated['payment_terms']);

            // Determine invoice type
            $invoiceType = $validated['po_id'] ? 'po_conversion' : 'direct';

            // Create purchase invoice
            $invoice = PurchaseInvoice::create([
                'vendor_id' => $validated['vendor_id'],
                'po_id' => $validated['po_id'],
                'vendor_invoice_no' => $validated['vendor_invoice_no'],
                'invoice_date' => $validated['invoice_date'],
                'invoice_type' => $invoiceType,
                'currency' => $validated['currency'],
                'exchange_rate' => $validated['exchange_rate'],
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $totalTax,
                'total_amount' => $totalAmount,
                'balance_amount' => $totalAmount,
                'payment_terms' => $validated['payment_terms'],
                'due_date' => $dueDate,
                'status' => 'pending',
                'notes' => $validated['notes'],
                'created_by' => Auth::id(),
            ]);

            // Create purchase invoice items
            foreach ($items as $item) {
                PurchaseInvoiceItem::create(array_merge($item, [
                    'invoice_id' => $invoice->id
                ]));
            }

            // Update PO items received quantity if this is from PO
            if ($validated['po_id']) {
                foreach ($request->items as $index => $item) {
                    if (!empty($item['po_item_id'])) {
                        $poItem = \App\Models\PurchaseOrderItem::find($item['po_item_id']);
                        if ($poItem) {
                            $poItem->increment('received_quantity', $item['quantity']);
                            $poItem->decrement('remaining_quantity', $item['quantity']);
                        }
                    }
                }

                // Update PO status
                $po = PurchaseOrder::find($validated['po_id']);
                $totalReceived = $po->items->sum('received_quantity');
                $totalOrdered = $po->items->sum('quantity');
                
                if ($totalReceived >= $totalOrdered) {
                    $po->update(['status' => 'received']);
                } elseif ($totalReceived > 0) {
                    $po->update(['status' => 'partial']);
                }
            }

            // Call account migration
            $this->accountMigration($invoice);

            DB::commit();
            return redirect()->route('purchase.invoices.index')
                           ->with('success', 'Purchase Invoice created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error creating purchase invoice: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified purchase invoice.
     */
    public function show(PurchaseInvoice $invoice)
    {
        $invoice->load([
            'vendor',
            'purchaseOrder',
            'items.product',
            'items.service',
            'items.uom',
            'items.poItem',
            'createdBy'
        ]);

        // Get related GRNs
        $grns = $invoice->grns()->with('items.product')->get();

        return view('purchase.invoices.show', compact('invoice', 'grns'));
    }

    /**
     * Show the form for editing the purchase invoice.
     */
    public function edit(PurchaseInvoice $invoice)
    {
        // Only allow editing if status is draft or pending
        if (!in_array($invoice->status, ['draft', 'pending'])) {
            return redirect()->route('purchase.invoices.show', $invoice)
                           ->with('error', 'Cannot edit purchase invoice with current status.');
        }

        $invoice->load(['items', 'vendor']);
        $vendors = Vendor::where('status', 'active')->orderBy('company_name')->get();
        $products = Product::where('is_active', 1)->orderBy('name')->get();
        $services = Service::where('status', 1)->orderBy('name')->get();
        $uoms = UOM::where('status', 1)->orderBy('name')->get();
        $taxes = Tax::where('status', 1)->orderBy('name')->get();

        return view('purchase.invoices.edit', compact('invoice', 'vendors', 'products', 'services', 'uoms', 'taxes'));
    }

    /**
     * Update the specified purchase invoice.
     */
    public function update(Request $request, PurchaseInvoice $invoice)
    {
        // Only allow updating if status is draft or pending
        if (!in_array($invoice->status, ['draft', 'pending'])) {
            return redirect()->route('purchase.invoices.show', $invoice)
                           ->with('error', 'Cannot update purchase invoice with current status.');
        }

        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'vendor_invoice_no' => 'nullable|string|max:100',
            'invoice_date' => 'required|date',
            'currency' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.0001',
            'payment_terms' => 'required|integer|min:0',
            'discount_type' => 'required|in:percentage,amount',
            'discount_value' => 'nullable|numeric|min:0',
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
                    'po_item_id' => $item['po_item_id'] ?? null,
                    'description' => $item['description'] ?? '',
                    'quantity' => $quantity,
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
            $dueDate = Carbon::parse($validated['invoice_date'])->addDays((int) $validated['payment_terms']);

            // Update purchase invoice
            $invoice->update([
                'vendor_id' => $validated['vendor_id'],
                'vendor_invoice_no' => $validated['vendor_invoice_no'],
                'invoice_date' => $validated['invoice_date'],
                'currency' => $validated['currency'],
                'exchange_rate' => $validated['exchange_rate'],
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $totalTax,
                'total_amount' => $totalAmount,
                'balance_amount' => $totalAmount - $invoice->paid_amount,
                'payment_terms' => $validated['payment_terms'],
                'due_date' => $dueDate,
                'notes' => $validated['notes'],
            ]);

            // Delete existing items and recreate
            $invoice->items()->delete();
            foreach ($items as $item) {
                PurchaseInvoiceItem::create(array_merge($item, [
                    'invoice_id' => $invoice->id
                ]));
            }

            DB::commit();
            return redirect()->route('purchase.invoices.index')
                           ->with('success', 'Purchase Invoice updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error updating purchase invoice: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified purchase invoice.
     */
    public function destroy(PurchaseInvoice $invoice)
    {
        // Only allow deleting if status is draft or pending
        if (!in_array($invoice->status, ['draft', 'pending'])) {
            return redirect()->route('purchase.invoices.index')
                           ->with('error', 'Cannot delete purchase invoice with current status.');
        }

        DB::beginTransaction();
        try {
            // If this invoice is from PO, update PO item quantities
            if ($invoice->po_id) {
                foreach ($invoice->items as $item) {
                    if ($item->po_item_id) {
                        $poItem = \App\Models\PurchaseOrderItem::find($item->po_item_id);
                        if ($poItem) {
                            $poItem->decrement('received_quantity', $item->quantity);
                            $poItem->increment('remaining_quantity', $item->quantity);
                        }
                    }
                }
            }

            $invoice->items()->delete();
            $invoice->delete();

            DB::commit();
            return redirect()->route('purchase.invoices.index')
                           ->with('success', 'Purchase Invoice deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('purchase.invoices.index')
                           ->with('error', 'Error deleting purchase invoice: ' . $e->getMessage());
        }
    }

    /**
     * Get PO items for invoice creation
     */
    public function getPoItems(Request $request)
    {
        $poId = $request->po_id;
        
        $po = PurchaseOrder::with(['items.product', 'items.service', 'items.uom'])
                          ->where('id', $poId)
                          ->where('approval_status', 'approved')
                          ->first();

        if (!$po) {
            return response()->json(['error' => 'Purchase Order not found or not approved'], 404);
        }

        $items = $po->items->map(function ($item) {
            return [
                'id' => $item->id,
                'item_type' => $item->item_type,
                'item_id' => $item->item_id,
                'item_name' => $item->item_type === 'product' 
                              ? $item->product->name 
                              : $item->service->name,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'received_quantity' => $item->received_quantity,
                'remaining_quantity' => $item->remaining_quantity,
                'uom_id' => $item->uom_id,
                'uom_name' => $item->uom ? $item->uom->name : null,
                'unit_price' => $item->unit_price,
                'discount_type' => $item->discount_type,
                'discount_value' => $item->discount_value,
                'tax_rate' => $item->tax_rate,
            ];
        });

        return response()->json([
            'po' => [
                'id' => $po->id,
                'po_no' => $po->po_no,
                'vendor_id' => $po->vendor_id,
                'currency' => $po->currency,
                'exchange_rate' => $po->exchange_rate
            ],
            'items' => $items
        ]);
    }

    /**
     * Account migration placeholder
     */
    private function accountMigration($invoice)
    {
        // Empty function for future account integration
        // Will be implemented when accounting system integration is explained
    }

    /**
     * E-Invoice submission placeholder
     */
    public function submitEInvoice(PurchaseInvoice $invoice)
    {
        // Empty function for future e-invoice integration
        // Will be implemented when e-invoice system integration is explained
        
        return redirect()->route('purchase.invoices.show', $invoice)
                       ->with('info', 'E-Invoice submission will be implemented soon.');
    }
}