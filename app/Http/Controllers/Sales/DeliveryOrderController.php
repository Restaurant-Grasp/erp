<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\DeliveryOrderSerial;
use App\Models\SalesInvoice;
use App\Models\Customer;
use App\Models\ProductSerialNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeliveryOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:sales.delivery_orders.view')->only(['index', 'show']);
        $this->middleware('permission:sales.delivery_orders.create')->only(['create', 'store']);
        $this->middleware('permission:sales.delivery_orders.edit')->only(['edit', 'update']);
        $this->middleware('permission:sales.delivery_orders.delete')->only('destroy');
    }

    /**
     * Display a listing of delivery orders.
     */
    public function index(Request $request)
    {
        $query = DeliveryOrder::with(['customer', 'invoice', 'createdBy']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('do_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('company_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('invoice', function ($q) use ($search) {
                      $q->where('invoice_no', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('do_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('do_date', '<=', $request->date_to);
        }

        $deliveryOrders = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('sales.delivery-orders.index', compact('deliveryOrders'));
    }

    /**
     * Show the form for creating a new delivery order.
     */
    public function create(Request $request)
    {
        $customers = Customer::where('status', 'active')->orderBy('company_name')->get();
        $invoices = collect();
        
        // Pre-fill data if coming from invoice
        $invoice = null;
        if ($request->filled('invoice_id')) {
            $invoice = SalesInvoice::with(['customer', 'items' => function ($query) {
                $query->where('delivery_status', '!=', 'delivered');
            }])->find($request->invoice_id);
            
            if ($invoice) {
                $invoices = collect([$invoice]);
            }
        }

        return view('sales.delivery-orders.create', compact('customers', 'invoices', 'invoice'));
    }

    /**
     * Store a newly created delivery order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'do_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'invoice_id' => 'nullable|exists:sales_invoices,id',
            'delivery_address' => 'required|string',
            'delivery_date' => 'nullable|date',
            'delivered_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.invoice_item_id' => 'nullable|exists:sales_invoice_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.delivered_quantity' => 'required|numeric|min:0|lte:items.*.quantity',
            'items.*.damaged_quantity' => 'required|numeric|min:0',
            'items.*.replacement_quantity' => 'required|numeric|min:0',
            'items.*.uom_id' => 'nullable|exists:uom,id',
            'items.*.warranty_start_date' => 'nullable|date',
            'items.*.warranty_end_date' => 'nullable|date|after_or_equal:items.*.warranty_start_date',
            'items.*.serial_numbers' => 'nullable|array',
            'items.*.serial_numbers.*' => 'nullable|string',
            'items.*.notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // Create delivery order
            $doData = $validated;
            unset($doData['items']);
            $doData['do_no'] = DeliveryOrder::generateDoNumber();
            $doData['status'] = 'pending';
            $doData['created_by'] = Auth::id();

            $deliveryOrder = DeliveryOrder::create($doData);

            // Create delivery order items
            foreach ($validated['items'] as $itemData) {
                $item = DeliveryOrderItem::create([
                    'do_id' => $deliveryOrder->id,
                    'invoice_item_id' => $itemData['invoice_item_id'] ?? null,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'delivered_quantity' => $itemData['delivered_quantity'],
                    'damaged_quantity' => $itemData['damaged_quantity'],
                    'replacement_quantity' => $itemData['replacement_quantity'],
                    'uom_id' => $itemData['uom_id'] ?? null,
                    'warranty_start_date' => $itemData['warranty_start_date'] ?? null,
                    'warranty_end_date' => $itemData['warranty_end_date'] ?? null,
                    'serial_numbers' => $itemData['serial_numbers'] ?? null,
                    'notes' => $itemData['notes'] ?? null
                ]);

                // Handle serial numbers if product has serial tracking
                if ($item->has_serial_tracking && !empty($itemData['serial_numbers'])) {
                    $this->handleSerialNumbers($item, $itemData['serial_numbers'], $itemData);
                }

                // Handle damaged items and replacements
                if ($itemData['damaged_quantity'] > 0 && $itemData['replacement_quantity'] > 0) {
                    $this->handleReplacements($item, $itemData);
                }

                // Update delivery status
                $item->updateDeliveryStatus();
            }

            // Update delivery order status
            $deliveryOrder->updateDeliveryStatus();

            DB::commit();
            return redirect()->route('sales.delivery-orders.show', $deliveryOrder)
                           ->with('success', 'Delivery order created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error creating delivery order: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified delivery order.
     */
    public function show(DeliveryOrder $deliveryOrder)
    {
        $deliveryOrder->load([
            'customer',
            'invoice',
            'items.product',
            'items.uom',
            'items.serialNumbers.serialNumber',
            'createdBy'
        ]);

        return view('sales.delivery-orders.show', compact('deliveryOrder'));
    }

    /**
     * Show the form for editing the specified delivery order.
     */
    public function edit(DeliveryOrder $deliveryOrder)
    {
        if ($deliveryOrder->status === 'delivered') {
            return redirect()->route('sales.delivery-orders.show', $deliveryOrder)
                           ->with('error', 'Delivered orders cannot be edited.');
        }

        $customers = Customer::where('status', 'active')->orderBy('company_name')->get();
        $deliveryOrder->load(['items.product', 'items.serialNumbers']);

        return view('sales.delivery-orders.edit', compact('deliveryOrder', 'customers'));
    }

    /**
     * Update the specified delivery order.
     */
    public function update(Request $request, DeliveryOrder $deliveryOrder)
    {
        if ($deliveryOrder->status === 'delivered') {
            return redirect()->route('sales.delivery-orders.show', $deliveryOrder)
                           ->with('error', 'Delivered orders cannot be edited.');
        }

        $validated = $request->validate([
            'do_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'delivery_address' => 'required|string',
            'delivery_date' => 'nullable|date',
            'delivered_by' => 'nullable|string|max:255',
            'received_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:delivery_order_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.delivered_quantity' => 'required|numeric|min:0|lte:items.*.quantity',
            'items.*.damaged_quantity' => 'required|numeric|min:0',
            'items.*.replacement_quantity' => 'required|numeric|min:0',
            'items.*.warranty_start_date' => 'nullable|date',
            'items.*.warranty_end_date' => 'nullable|date|after_or_equal:items.*.warranty_start_date',
            'items.*.notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // Update delivery order
            $doData = $validated;
            unset($doData['items']);
            $deliveryOrder->update($doData);

            // Update items
            $existingItemIds = collect();
            
            foreach ($validated['items'] as $itemData) {
                if (isset($itemData['id'])) {
                    // Update existing item
                    $item = DeliveryOrderItem::find($itemData['id']);
                    $item->update([
                        'quantity' => $itemData['quantity'],
                        'delivered_quantity' => $itemData['delivered_quantity'],
                        'damaged_quantity' => $itemData['damaged_quantity'],
                        'replacement_quantity' => $itemData['replacement_quantity'],
                        'warranty_start_date' => $itemData['warranty_start_date'] ?? null,
                        'warranty_end_date' => $itemData['warranty_end_date'] ?? null,
                        'notes' => $itemData['notes'] ?? null
                    ]);
                    $existingItemIds->push($item->id);
                } else {
                    // Create new item
                    $item = DeliveryOrderItem::create([
                        'do_id' => $deliveryOrder->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'delivered_quantity' => $itemData['delivered_quantity'],
                        'damaged_quantity' => $itemData['damaged_quantity'],
                        'replacement_quantity' => $itemData['replacement_quantity'],
                        'warranty_start_date' => $itemData['warranty_start_date'] ?? null,
                        'warranty_end_date' => $itemData['warranty_end_date'] ?? null,
                        'notes' => $itemData['notes'] ?? null
                    ]);
                    $existingItemIds->push($item->id);
                }

                // Update delivery status
                $item->updateDeliveryStatus();
            }

            // Delete removed items
            $deliveryOrder->items()->whereNotIn('id', $existingItemIds)->delete();

            // Update delivery order status
            $deliveryOrder->updateDeliveryStatus();

            DB::commit();
            return redirect()->route('sales.delivery-orders.show', $deliveryOrder)
                           ->with('success', 'Delivery order updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error updating delivery order: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified delivery order.
     */
    public function destroy(DeliveryOrder $deliveryOrder)
    {
        if ($deliveryOrder->status === 'delivered') {
            return redirect()->route('sales.delivery-orders.index')
                           ->with('error', 'Delivered orders cannot be deleted.');
        }

        DB::beginTransaction();
        try {
            // Delete serial number records
            foreach ($deliveryOrder->items as $item) {
                $item->serialNumbers()->delete();
            }
            
            // Delete items
            $deliveryOrder->items()->delete();
            
            // Delete delivery order
            $deliveryOrder->delete();

            // Update invoice delivery status if linked
            if ($deliveryOrder->invoice) {
                $deliveryOrder->invoice->updateDeliveryStatus();
            }

            DB::commit();
            return redirect()->route('sales.delivery-orders.index')
                           ->with('success', 'Delivery order deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('sales.delivery-orders.index')
                           ->with('error', 'Error deleting delivery order: ' . $e->getMessage());
        }
    }

    /**
     * Mark delivery order as delivered
     */
    public function markDelivered(DeliveryOrder $deliveryOrder)
    {
        if ($deliveryOrder->status === 'delivered') {
            return redirect()->route('sales.delivery-orders.show', $deliveryOrder)
                           ->with('error', 'Delivery order is already marked as delivered.');
        }

        $deliveryOrder->update([
            'status' => 'delivered',
            'delivery_date' => now()->toDateString()
        ]);

        // Update all items as completed
        foreach ($deliveryOrder->items as $item) {
            $item->update(['delivery_status' => 'completed']);
        }

        // Update invoice delivery status
        if ($deliveryOrder->invoice) {
            $deliveryOrder->invoice->updateDeliveryStatus();
        }

        return redirect()->route('sales.delivery-orders.show', $deliveryOrder)
                        ->with('success', 'Delivery order marked as delivered successfully.');
    }

    /**
     * Generate PDF of delivery order
     */
    public function pdf(DeliveryOrder $deliveryOrder)
    {
        $deliveryOrder->load([
            'customer',
            'invoice',
            'items.product',
            'items.uom',
            'items.serialNumbers.serialNumber'
        ]);

        // TODO: Implement PDF generation
        return response()->json(['message' => 'PDF generation will be implemented']);
    }

    /**
     * Get pending invoices for customer
     */
    public function getPendingInvoices(Request $request)
    {
        $customerId = $request->get('customer_id');
        
        if (!$customerId) {
            return response()->json([]);
        }

        $invoices = SalesInvoice::where('customer_id', $customerId)
                               ->whereHas('items', function ($query) {
                                   $query->where('delivery_status', '!=', 'delivered');
                               })
                               ->with(['items' => function ($query) {
                                   $query->where('delivery_status', '!=', 'delivered')
                                         ->with('product');
                               }])
                               ->get();

        return response()->json($invoices);
    }

    /**
     * Handle serial numbers for delivered items
     */
    private function handleSerialNumbers(DeliveryOrderItem $item, array $serialNumbers, array $itemData)
    {
        foreach ($serialNumbers as $serialNo) {
            if (empty($serialNo)) continue;

            // Create or find serial number
            $serialNumber = ProductSerialNumber::firstOrCreate([
                'product_id' => $item->product_id,
                'serial_number' => $serialNo
            ], [
                'warranty_start_date' => $itemData['warranty_start_date'] ?? null,
                'warranty_end_date' => $itemData['warranty_end_date'] ?? null,
                'current_status' => 'sold',
                'customer_id' => $item->deliveryOrder->customer_id,
                'sales_invoice_id' => $item->deliveryOrder->invoice_id
            ]);

            // Create delivery order serial record
            DeliveryOrderSerial::create([
                'do_item_id' => $item->id,
                'serial_number_id' => $serialNumber->id,
                'status' => 'delivered',
                'warranty_start_date' => $itemData['warranty_start_date'] ?? null,
                'warranty_end_date' => $itemData['warranty_end_date'] ?? null
            ]);
        }
    }

    /**
     * Handle damaged items and replacements
     */
    private function handleReplacements(DeliveryOrderItem $item, array $itemData)
    {
        for ($i = 0; $i < $itemData['replacement_quantity']; $i++) {
            // Generate new serial number for replacement
            $newSerialNo = $this->generateSerialNumber($item->product_id);

            // Create new serial number record
            $newSerial = ProductSerialNumber::create([
                'product_id' => $item->product_id,
                'serial_number' => $newSerialNo,
                'warranty_start_date' => $itemData['warranty_start_date'] ?? null,
                'warranty_end_date' => $itemData['warranty_end_date'] ?? null,
                'current_status' => 'sold',
                'customer_id' => $item->deliveryOrder->customer_id,
                'sales_invoice_id' => $item->deliveryOrder->invoice_id,
                'is_replacement' => true,
                'replacement_reason' => 'Damaged during delivery',
                'replacement_date' => now()->toDateString()
            ]);

            // Create delivery order serial record
            DeliveryOrderSerial::create([
                'do_item_id' => $item->id,
                'serial_number_id' => $newSerial->id,
                'status' => 'delivered',
                'warranty_start_date' => $itemData['warranty_start_date'] ?? null,
                'warranty_end_date' => $itemData['warranty_end_date'] ?? null,
                'notes' => 'Replacement for damaged item'
            ]);
        }
    }

    /**
     * Generate serial number
     */
    private function generateSerialNumber($productId)
    {
        $prefix = 'PROD';
        $year = now()->year;
        
        $lastSerial = ProductSerialNumber::where('product_id', $productId)
                                        ->where('serial_number', 'like', "{$prefix}-{$year}-%")
                                        ->orderBy('serial_number', 'desc')
                                        ->first();

        if ($lastSerial) {
            $lastNumber = intval(substr($lastSerial->serial_number, strrpos($lastSerial->serial_number, '-') + 1));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . $year . '-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}