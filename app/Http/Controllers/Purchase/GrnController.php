<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceiptNote;
use App\Models\GrnItem;
use App\Models\GrnDocument;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\ProductSerialNumber;
use App\Models\Staff;
use App\Models\Uom;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GrnController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:purchases.grn.view')->only(['index', 'show', 'downloadDocument']);
        $this->middleware('permission:purchases.grn.create')->only(['create', 'store', 'createFromInvoice']);
        $this->middleware('permission:purchases.grn.edit')->only(['edit', 'update']);
        $this->middleware('permission:purchases.grn.delete')->only(['deleteDocument']);
    }

    /**
     * Display a listing of GRNs.
     */
    public function index(Request $request)
    {
        $query = GoodsReceiptNote::with(['vendor', 'purchaseOrder', 'purchaseInvoice', 'receivedBy', 'createdBy']);

        // Search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('grn_no', 'like', "%{$search}%")
                    ->orWhereHas('vendor', function ($vendorQuery) use ($search) {
                        $vendorQuery->where('company_name', 'like', "%{$search}%")
                            ->orWhere('vendor_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('purchaseOrder', function ($poQuery) use ($search) {
                        $poQuery->where('po_no', 'like', "%{$search}%");
                    })
                    ->orWhereHas('purchaseInvoice', function ($invoiceQuery) use ($search) {
                        $invoiceQuery->where('invoice_no', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Vendor filter
        if ($request->filled('vendor')) {
            $query->where('vendor_id', $request->vendor);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('grn_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('grn_date', '<=', $request->date_to);
        }

        $grns = $query->orderBy('created_at', 'desc')->paginate(15);
        $vendors = Vendor::where('status', 'active')->orderBy('company_name')->get();

        return view('purchase.grn.index', compact('grns', 'vendors'));
    }

    /**
     * Show the form for creating a new GRN.
     */
    public function create()
    {
        $vendors = Vendor::where('status', 'active')->orderBy('company_name')->get();
        $staff = Staff::where('status', 'active')->orderBy('name')->get();
        $products = Product::where('is_active', 1)->orderBy('name')->get();
        $uoms = Uom::where('status', 1)->orderBy('name')->get();

        return view('purchase.grn.create', compact('vendors', 'staff', 'products', 'uoms'));
    }

    /**
     * Show form to create GRN from Invoice
     */
    public function createFromInvoice(PurchaseInvoice $invoice)
    {
        $invoice->load(['vendor', 'items.product', 'items.uom', 'purchaseOrder']);
        $staff = Staff::where('status', 'active')->orderBy('name')->get();
        $uoms = Uom::where('status', 1)->orderBy('name')->get();
        return view('purchase.grn.create-from-invoice', compact('invoice', 'staff', 'uoms'));
    }

    /**
     * Store a newly created GRN.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'po_id' => 'nullable|exists:purchase_orders,id',
            'invoice_id' => 'nullable|exists:purchase_invoices,id',
            'grn_date' => 'required|date',
            'received_by' => 'required|exists:staff,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.invoice_item_id' => 'nullable|integer',
            'items.*.po_item_id' => 'nullable|integer',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.damaged_quantity' => 'nullable|numeric|min:0',
            'items.*.uom_id' => 'nullable|exists:uom,id',
            'items.*.batch_no' => 'nullable|string|max:100',
            'items.*.expiry_date' => 'nullable|date|after:today',
            'items.*.damage_reason' => 'nullable|string',
            'items.*.replacement_required' => 'nullable|boolean',
            'items.*.notes' => 'nullable|string',
            'items.*.serial_numbers' => 'nullable|array',
            'items.*.serial_numbers.*.serial_number' => 'required_with:items.*.serial_numbers|string',
            'items.*.serial_numbers.*.warranty_start_date' => 'nullable|date',
            'items.*.serial_numbers.*.warranty_end_date' => 'nullable|date|after:items.*.serial_numbers.*.warranty_start_date',
            // Document validation
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png,gif,bmp,doc,docx,xls,xlsx|max:10240', // 10MB max
            'document_descriptions' => 'nullable|array',
            'document_descriptions.*' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Create GRN
            $grn = GoodsReceiptNote::create([
                'vendor_id' => $validated['vendor_id'],
                'grn_no' => $this->generateGrnNumber(),
                'po_id' => $validated['po_id'],
                'invoice_id' => $validated['invoice_id'],
                'grn_date' => $validated['grn_date'],
                'received_by' => $validated['received_by'],
                'status' => 'draft',
                'notes' => $validated['notes'],
                'created_by' => Auth::id(),
            ]);

            // Process each item
            foreach ($request->items as $itemData) {
                $quantity = floatval($itemData['quantity']);
                $damagedQuantity = floatval($itemData['damaged_quantity'] ?? 0);
                $acceptedQuantity = $quantity - $damagedQuantity;

                // Create GRN item
                $grnItem = GrnItem::create([
                    'grn_id' => $grn->id,
                    'po_item_id' => $itemData['po_item_id'] ?? null,
                    'invoice_item_id' => $itemData['invoice_item_id'] ?? null,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $quantity,
                    'damaged_quantity' => $damagedQuantity,
                    'accepted_quantity' => $acceptedQuantity,
                    'uom_id' => $itemData['uom_id'] ?? null,
                    'batch_no' => $itemData['batch_no'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'damage_reason' => $itemData['damage_reason'] ?? null,
                    'replacement_required' => !empty($itemData['replacement_required']),
                    'notes' => $itemData['notes'] ?? null,
                ]);

                // Handle serial numbers if provided
                if (!empty($itemData['serial_numbers'])) {
                    foreach ($itemData['serial_numbers'] as $serialData) {
                        if (!empty($serialData['serial_number'])) {
                            ProductSerialNumber::create([
                                'product_id' => $itemData['product_id'],
                                'serial_number' => $serialData['serial_number'],
                                'warranty_start_date' => $serialData['warranty_start_date'] ?? null,
                                'warranty_end_date' => $serialData['warranty_end_date'] ?? null,
                                'warranty_status' => 'active',
                                'current_status' => 'in_stock',
                                'grn_id' => $grn->id,
                                'grn_item_id' => $grnItem->id,
                            ]);
                        }
                    }
                }

                // Update inventory transaction
                \App\Models\InventoryTransaction::create([
                    'transaction_date' => $validated['grn_date'],
                    'transaction_type' => 'purchase',
                    'reference_type' => 'grn',
                    'reference_id' => $grn->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $acceptedQuantity, // Only accepted quantity goes to inventory
                    'notes' => "GRN: {$grn->grn_no}",
                    'created_by' => Auth::id(),
                ]);

                // Update PO item received quantity if applicable
                if (!empty($itemData['po_item_id'])) {
                    $poItem = \App\Models\PurchaseOrderItem::find($itemData['po_item_id']);
                    if ($poItem) {
                        $poItem->increment('received_quantity', $quantity);
                        $poItem->decrement('remaining_quantity', $quantity);
                    }
                }

                // Update Invoice item received quantity if applicable
                if (!empty($itemData['invoice_item_id'])) {
                    $invoiceItem = \App\Models\PurchaseInvoiceItem::find($itemData['invoice_item_id']);
                    if ($invoiceItem) {
                        $invoiceItem->increment('received_quantity', $quantity);
                    }
                }

                // Create return request for damaged items
                if ($damagedQuantity > 0) {
                    $this->createReturnForDamagedItems($grn, $grnItem, $damagedQuantity, $itemData['damage_reason'] ?? 'Damaged during delivery');
                }
            }

            // Handle document uploads
            if ($request->hasFile('documents')) {
                $this->handleDocumentUploads($request, $grn);
            }

            // Update GRN status
            $this->updateGrnStatus($grn);

            // Update related PO status if applicable
            if ($validated['po_id']) {
                $this->updatePoStatus($validated['po_id']);
            }

            // Update related Invoice status if applicable
            if ($validated['invoice_id']) {
                $this->updateInvoiceStatus($validated['invoice_id']);
            }

            DB::commit();
            return redirect()->route('purchase.grn.show', $grn)
                ->with('success', 'GRN created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error creating GRN: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified GRN.
     */
    public function show(GoodsReceiptNote $grn)
    {
        $grn->load([
            'vendor',
            'purchaseOrder',
            'purchaseInvoice',
            'items.product',
            'items.uom',
            'items.poItem',
            'items.invoiceItem',
            'receivedBy',
            'createdBy',
            'documents.uploadedBy'
        ]);

        // Get serial numbers for items
        $serialNumbers = ProductSerialNumber::where('grn_id', $grn->id)
            ->with('product')
            ->get()
            ->groupBy('grn_item_id');

        // Get related returns
        $returns = PurchaseReturn::where('grn_id', $grn->id)
            ->with('items.product')
            ->get();

        return view('purchase.grn.show', compact('grn', 'serialNumbers', 'returns'));
    }

    /**
     * Show the form for editing the GRN.
     */
    public function edit(GoodsReceiptNote $grn)
    {
        // Only allow editing if status is draft
        if ($grn->status !== 'draft') {
            return redirect()->route('purchase.grn.show', $grn)
                ->with('error', 'Cannot edit GRN that is not in draft status.');
        }

        $grn->load(['items.product', 'items.uom', 'documents']);
        $vendors = Vendor::where('status', 'active')->orderBy('company_name')->get();
        $staff = Staff::where('status', 'active')->orderBy('name')->get();
        $products = Product::where('is_active', 1)->orderBy('name')->get();
        $uoms = Uom::where('status', 1)->orderBy('name')->get();

        // Get existing serial numbers
        $serialNumbers = ProductSerialNumber::where('grn_id', $grn->id)
            ->get()
            ->groupBy('grn_item_id');

        return view('purchase.grn.edit', compact('grn', 'vendors', 'staff', 'products', 'uoms', 'serialNumbers'));
    }

    /**
     * Update the specified GRN.
     */
    public function update(Request $request, GoodsReceiptNote $grn)
    {
        // Only allow updating if status is draft
        if ($grn->status !== 'draft') {
            return redirect()->route('purchase.grn.show', $grn)
                ->with('error', 'Cannot update GRN that is not in draft status.');
        }

        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'grn_date' => 'required|date',
            'received_by' => 'required|exists:staff,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.damaged_quantity' => 'nullable|numeric|min:0',
            'items.*.uom_id' => 'nullable|exists:uom,id',
            'items.*.batch_no' => 'nullable|string|max:100',
            'items.*.expiry_date' => 'nullable|date|after:today',
            'items.*.damage_reason' => 'nullable|string',
            'items.*.replacement_required' => 'nullable|boolean',
            'items.*.notes' => 'nullable|string',
            // Document validation
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png,gif,bmp,doc,docx,xls,xlsx|max:10240', // 10MB max
            'document_descriptions' => 'nullable|array',
            'document_descriptions.*' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Update GRN
            $grn->update([
                'vendor_id' => $validated['vendor_id'],
                'grn_date' => $validated['grn_date'],
                'received_by' => $validated['received_by'],
                'notes' => $validated['notes'],
            ]);

            // Delete existing items and serial numbers
            ProductSerialNumber::where('grn_id', $grn->id)->delete();
            $grn->items()->delete();

            // Recreate items
            foreach ($request->items as $itemData) {
                $quantity = floatval($itemData['quantity']);
                $damagedQuantity = floatval($itemData['damaged_quantity'] ?? 0);
                $acceptedQuantity = $quantity - $damagedQuantity;

                $grnItem = GrnItem::create([
                    'grn_id' => $grn->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $quantity,
                    'damaged_quantity' => $damagedQuantity,
                    'accepted_quantity' => $acceptedQuantity,
                    'uom_id' => $itemData['uom_id'] ?? null,
                    'batch_no' => $itemData['batch_no'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'damage_reason' => $itemData['damage_reason'] ?? null,
                    'replacement_required' => !empty($itemData['replacement_required']),
                    'notes' => $itemData['notes'] ?? null,
                ]);

                // Handle serial numbers if provided
                if (!empty($itemData['serial_numbers'])) {
                    foreach ($itemData['serial_numbers'] as $serialData) {
                        if (!empty($serialData['serial_number'])) {
                            ProductSerialNumber::create([
                                'product_id' => $itemData['product_id'],
                                'serial_number' => $serialData['serial_number'],
                                'warranty_start_date' => $serialData['warranty_start_date'] ?? null,
                                'warranty_end_date' => $serialData['warranty_end_date'] ?? null,
                                'warranty_status' => 'active',
                                'current_status' => 'in_stock',
                                'grn_id' => $grn->id,
                                'grn_item_id' => $grnItem->id,
                            ]);
                        }
                    }
                }
            }

            // Handle new document uploads
            if ($request->hasFile('documents')) {
                $this->handleDocumentUploads($request, $grn);
            }

            DB::commit();
            return redirect()->route('purchase.grn.show', $grn)
                ->with('success', 'GRN updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error updating GRN: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Handle document uploads for GRN
     */
    private function handleDocumentUploads(Request $request, GoodsReceiptNote $grn)
    {
        $documents = $request->file('documents');
        $descriptions = $request->input('document_descriptions', []);

        foreach ($documents as $index => $file) {
            if ($file && $file->isValid()) {
                // Generate unique filename
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $fileName = 'grn_' . $grn->id . '_' . Str::uuid() . '.' . $extension;

                // Store file
                $filePath = $file->storeAs('grn_documents', $fileName, 'public');

                // Create document record
                GrnDocument::create([
                    'grn_id' => $grn->id,
                    'original_name' => $originalName,
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'file_type' => $extension,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'document_type' => 'delivery_order',
                    'description' => $descriptions[$index] ?? null,
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }
    }

    /**
     * Download a GRN document
     */
    public function downloadDocument(GrnDocument $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    /**
     * Delete a GRN document
     */
    public function deleteDocument(GrnDocument $document)
    {
        // Only allow deletion if GRN is in draft status
        if ($document->grn->status !== 'draft') {
            return response()->json(['error' => 'Cannot delete GRN that is not in draft status.'], 403);
        }

        try {
            $document->delete();
            return response()->json(['success' => 'Document deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error deleting document: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get invoice items for GRN creation
     */
    public function getInvoiceItems(Request $request)
    {
        $invoiceId = $request->invoice_id;

        $invoice = PurchaseInvoice::with(['items.product.uom', 'items.service', 'vendor'])
            ->find($invoiceId);

        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        $items = $invoice->items->map(function ($item) {
            // Only return product items (not services) for GRN
            if ($item->item_type === 'product') {
                return [
                    'id' => $item->id,
                    'product_id' => $item->item_id,
                    'product_name' => $item->product->name,
                    'product_code' => $item->product->product_code,
                    'quantity' => $item->quantity,
                    'received_quantity' => $item->received_quantity,
                    'remaining_quantity' => $item->quantity - $item->received_quantity,
                    'uom_id' => $item->uom_id,
                    'uom_name' => $item->uom ? $item->uom->name : null,
                    'has_serial_number' => $item->product->has_serial_number,
                    'has_warranty' => $item->product->has_warranty,
                    'warranty_period_months' => $item->product->warranty_period_months,
                ];
            }
            return null;
        })->filter();

        return response()->json([
            'invoice' => [
                'id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'vendor_id' => $invoice->vendor_id,
                'vendor_name' => $invoice->vendor->company_name
            ],
            'items' => $items->values()
        ]);
    }

    /**
     * Create return request for damaged items
     */
    private function createReturnForDamagedItems($grn, $grnItem, $damagedQuantity, $reason)
    {
        $return = PurchaseReturn::create([
            'vendor_id' => $grn->vendor_id,
            'grn_id' => $grn->id,
            'invoice_id' => $grn->invoice_id,
            'return_date' => $grn->grn_date,
            'return_type' => 'damaged',
            'status' => 'pending',
            'notes' => "Auto-created from GRN {$grn->grn_no} for damaged items",
            'created_by' => Auth::id(),
        ]);

        PurchaseReturnItem::create([
            'return_id' => $return->id,
            'grn_item_id' => $grnItem->id,
            'product_id' => $grnItem->product_id,
            'quantity' => $damagedQuantity,
            'reason' => $reason,
            'replacement_required' => $grnItem->replacement_required,
        ]);
    }

    /**
     * Update GRN status based on items
     */
    private function updateGrnStatus($grn)
    {
        $totalItems = $grn->items()->count();
        $itemsWithQuantity = $grn->items()->where('quantity', '>', 0)->count();

        if ($itemsWithQuantity === 0) {
            $grn->update(['status' => 'draft']);
        } elseif ($itemsWithQuantity === $totalItems) {
            $grn->update(['status' => 'completed']);
        } else {
            $grn->update(['status' => 'partial']);
        }
    }

    /**
     * Update PO status based on received quantities
     */
    private function updatePoStatus($poId)
    {
        $po = PurchaseOrder::with('items')->find($poId);
        if (!$po) return;

        $totalQuantity = $po->items->sum('quantity');
        $receivedQuantity = $po->items->sum('received_quantity');

        $receivedPercentage = $totalQuantity > 0 ? ($receivedQuantity / $totalQuantity) * 100 : 0;

        if ($receivedQuantity >= $totalQuantity) {
            $po->update([
                'status' => 'received',
                'received_percentage' => 100
            ]);
        } elseif ($receivedQuantity > 0) {
            $po->update([
                'status' => 'partial',
                'received_percentage' => $receivedPercentage
            ]);
        }
    }

    /**
     * Update Invoice status based on received quantities
     */
    private function updateInvoiceStatus($invoiceId)
    {
        $invoice = PurchaseInvoice::with('items')->find($invoiceId);
        if (!$invoice) return;

        $totalQuantity = $invoice->items->sum('quantity');
        $receivedQuantity = $invoice->items->sum('received_quantity');

        $receivedPercentage = $totalQuantity > 0 ? ($receivedQuantity / $totalQuantity) * 100 : 0;

        $invoice->update([
            'received_percentage' => $receivedPercentage
        ]);
    }
    /**
     * Generate unique GRN number
     */
    private function generateGrnNumber()
    {
  
    $yearMonth = now()->format('Ym'); // e.g., 202508
    $prefix = "GRN" . $yearMonth;

    $newNumber = 1;
    do {
        $grnNo = $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
        $exists = GoodsReceiptNote::where('grn_no', $grnNo)->exists();
        $newNumber++;
    } while ($exists);

    return $grnNo;
        
    }
}
