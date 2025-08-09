<?php


namespace App\Http\Controllers\Sales;
use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Service;
use App\Models\Package;
use App\Models\Tax;
use App\Models\Uom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:sales.quotations.view')->only(['index', 'show']);
        $this->middleware('permission:sales.quotations.create')->only(['create', 'store']);
        $this->middleware('permission:sales.quotations.edit')->only(['edit', 'update']);
        $this->middleware('permission:sales.quotations.delete')->only('destroy');
        $this->middleware('permission:sales.quotations.approve')->only(['approve', 'reject']);
    }

    /**
     * Display a listing of quotations.
     */
    public function index(Request $request)
    {
        $query = Quotation::with(['customer', 'lead', 'createdBy', 'approvedBy']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('quotation_no', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('company_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('lead', function ($q) use ($search) {
                      $q->where('company_name', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%");
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

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('quotation_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('quotation_date', '<=', $request->date_to);
        }

        $quotations = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('sales.quotations.index', compact('quotations'));
    }

    /**
     * Show the form for creating a new quotation.
     */
    public function create(Request $request)
    {
        $customers = Customer::where('status', 'active')->orderBy('company_name')->get();
        $leads = Lead::whereIn('lead_status', ['new', 'contacted', 'qualified', 'proposal', 'negotiation'])
                    ->orderBy('company_name')->get();
        
        // Pre-fill data if coming from lead or customer
        $customer = null;
        $lead = null;
        
        if ($request->filled('customer_id')) {
            $customer = Customer::find($request->customer_id);
        }
        
        if ($request->filled('lead_id')) {
            $lead = Lead::find($request->lead_id);
        }

        return view('sales.quotations.create', compact('customers', 'leads', 'customer', 'lead'));
    }

    /**
     * Store a newly created quotation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'quotation_date' => 'required|date',
            'customer_id' => 'nullable|exists:customers,id',
            'lead_id' => 'nullable|exists:leads,id',
            'valid_until' => 'required|date|after:quotation_date',
            'reference_no' => 'nullable|string|max:100',
            'subject' => 'nullable|string|max:500',
            'currency' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0',
            'discount_type' => 'required|in:percentage,amount',
            'discount_value' => 'required|numeric|min:0',
            'terms_conditions' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:product,service,package',
            'items.*.item_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_type' => 'required|in:percentage,amount',
            'items.*.discount_value' => 'required|numeric|min:0',
            'items.*.tax_id' => 'nullable|exists:taxes,id',
            'items.*.description' => 'nullable|string'
        ], [
            'customer_id.required_without' => 'Either customer or lead must be selected.',
            'lead_id.required_without' => 'Either customer or lead must be selected.'
        ]);

        // Validate that either customer or lead is provided
        if (!$validated['customer_id'] && !$validated['lead_id']) {
            return back()->withErrors(['customer_id' => 'Either customer or lead must be selected.'])->withInput();
        }

        DB::beginTransaction();
        try {
            // Create quotation
            $quotationData = $validated;
            unset($quotationData['items']);
            $quotationData['created_by'] = Auth::id();
            $quotationData['status'] = 'draft';
            $quotationData['approval_status'] = 'pending';

            $quotation = Quotation::create($quotationData);

            // Create quotation items
            foreach ($validated['items'] as $index => $itemData) {
                $itemData['quotation_id'] = $quotation->id;
                $itemData['sort_order'] = $index + 1;
                QuotationItem::create($itemData);
            }

            // Calculate totals
            $quotation->calculateTotals();

            DB::commit();
            return redirect()->route('sales.quotations.show', $quotation)
                           ->with('success', 'Quotation created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error creating quotation: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified quotation.
     */
    public function show(Quotation $quotation)
    {
        $quotation->load([
            'customer',
            'lead', 
            'items.product',
            'items.service',
            'items.package',
            'items.uom',
            'items.tax',
            'createdBy',
            'approvedBy',
            'convertedInvoice',
            'parentQuotation',
            'revisions'
        ]);

        return view('sales.quotations.show', compact('quotation'));
    }

    /**
     * Show the form for editing the specified quotation.
     */
    public function edit(Quotation $quotation)
    {
        if (!$quotation->can_be_edited) {
            return redirect()->route('sales.quotations.show', $quotation)
                           ->with('error', 'Quotation cannot be edited in current status.');
        }

        $customers = Customer::where('status', 'active')->orderBy('company_name')->get();
        $leads = Lead::whereIn('lead_status', ['new', 'contacted', 'qualified', 'proposal', 'negotiation'])
                    ->orderBy('company_name')->get();

        $quotation->load('items');

        return view('sales.quotations.edit', compact('quotation', 'customers', 'leads'));
    }

    /**
     * Update the specified quotation.
     */
    public function update(Request $request, Quotation $quotation)
    {
        if (!$quotation->can_be_edited) {
            return redirect()->route('sales.quotations.show', $quotation)
                           ->with('error', 'Quotation cannot be edited in current status.');
        }

        $validated = $request->validate([
            'quotation_date' => 'required|date',
            'customer_id' => 'nullable|exists:customers,id',
            'lead_id' => 'nullable|exists:leads,id',
            'valid_until' => 'required|date|after:quotation_date',
            'reference_no' => 'nullable|string|max:100',
            'subject' => 'nullable|string|max:500',
            'currency' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0',
            'discount_type' => 'required|in:percentage,amount',
            'discount_value' => 'required|numeric|min:0',
            'terms_conditions' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:product,service,package',
            'items.*.item_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_type' => 'required|in:percentage,amount',
            'items.*.discount_value' => 'required|numeric|min:0',
            'items.*.tax_id' => 'nullable|exists:taxes,id',
            'items.*.description' => 'nullable|string'
        ]);

        // Validate that either customer or lead is provided
        if (!$validated['customer_id'] && !$validated['lead_id']) {
            return back()->withErrors(['customer_id' => 'Either customer or lead must be selected.'])->withInput();
        }

        DB::beginTransaction();
        try {
            // Update quotation
            $quotationData = $validated;
            unset($quotationData['items']);
            $quotation->update($quotationData);

            // Delete existing items
            $quotation->items()->delete();

            // Create new items
            foreach ($validated['items'] as $index => $itemData) {
                $itemData['quotation_id'] = $quotation->id;
                $itemData['sort_order'] = $index + 1;
                QuotationItem::create($itemData);
            }

            // Calculate totals
            $quotation->calculateTotals();

            DB::commit();
            return redirect()->route('sales.quotations.show', $quotation)
                           ->with('success', 'Quotation updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error updating quotation: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified quotation.
     */
    public function destroy(Quotation $quotation)
    {
        if (!$quotation->can_be_deleted) {
            return redirect()->route('sales.quotations.index')
                           ->with('error', 'Quotation cannot be deleted in current status.');
        }

        DB::beginTransaction();
        try {
            $quotation->items()->delete();
            $quotation->delete();
            
            DB::commit();
            return redirect()->route('sales.quotations.index')
                           ->with('success', 'Quotation deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('sales.quotations.index')
                           ->with('error', 'Error deleting quotation: ' . $e->getMessage());
        }
    }

    /**
     * Create revision of quotation
     */
    public function createRevision(Quotation $quotation)
    {
        if (!$quotation->can_be_edited) {
            return redirect()->route('sales.quotations.show', $quotation)
                           ->with('error', 'Cannot create revision for quotation in current status.');
        }

        try {
            $revision = $quotation->createRevision();
            
            return redirect()->route('sales.quotations.edit', $revision)
                           ->with('success', 'Quotation revision created successfully.');
        } catch (\Exception $e) {
            return redirect()->route('sales.quotations.show', $quotation)
                           ->with('error', 'Error creating revision: ' . $e->getMessage());
        }
    }

    /**
     * Approve quotation
     */
    public function approve(Quotation $quotation)
    {
        if (!$quotation->can_be_approved) {
            return redirect()->route('sales.quotations.show', $quotation)
                           ->with('error', 'Quotation cannot be approved in current status.');
        }

        try {
            $invoice = $quotation->approve(Auth::id());
            
            return redirect()->route('sales.invoices.show', $invoice)
                           ->with('success', 'Quotation approved and converted to invoice successfully.');
        } catch (\Exception $e) {
            return redirect()->route('sales.quotations.show', $quotation)
                           ->with('error', 'Error approving quotation: ' . $e->getMessage());
        }
    }

    /**
     * Reject quotation
     */
    public function reject(Request $request, Quotation $quotation)
    {
        if (!$quotation->can_be_approved) {
            return redirect()->route('sales.quotations.show', $quotation)
                           ->with('error', 'Quotation cannot be rejected in current status.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        $quotation->update([
            'approval_status' => 'rejected',
            'status' => 'rejected',
            'rejected_reason' => $validated['rejection_reason']
        ]);

        return redirect()->route('sales.quotations.show', $quotation)
                        ->with('success', 'Quotation rejected successfully.');
    }

    /**
     * Send quotation to customer/lead
     */
    public function send(Quotation $quotation)
    {
        // TODO: Implement email sending functionality
        
        $quotation->update([
            'status' => 'sent',
            'sent_date' => now()
        ]);

        return redirect()->route('sales.quotations.show', $quotation)
                        ->with('success', 'Quotation sent successfully.');
    }

    /**
     * Generate PDF of quotation
     */
    public function pdf(Quotation $quotation)
    {
        $quotation->load([
            'customer',
            'lead',
            'items.product',
            'items.service', 
            'items.package',
            'items.uom',
            'items.tax'
        ]);

        // TODO: Implement PDF generation
        // This can be done using packages like TCPDF, DOMPDF, or Laravel Snappy

        return response()->json(['message' => 'PDF generation will be implemented']);
    }

    /**
     * Get items for quotation (products, services, packages)
     */
    public function getItems(Request $request)
    {
         
        $type = $request->get('type', 'product');
        $search = $request->get('search', '');
  
        switch ($type) {
            case 'product':
                $items = Product::where('is_active', 1)
                               ->where('name', 'like', "%{$search}%")
                               ->limit(10)
                               ->get(['id', 'name', 'selling_price as price', 'uom_id']);
                break;
            case 'service':
                $items = Service::where('status', 1)
                               ->where('name', 'like', "%{$search}%")
                               ->limit(10)
                               ->get(['id', 'name', 'base_price as price']);
                break;
            case 'package':
                $items = Package::where('status', 1)
                               ->where('name', 'like', "%{$search}%")
                               ->limit(10)
                               ->get(['id', 'name', 'package_price as price']);
                break;
            default:
                $items = collect();
        }

        return response()->json($items);
    }

    /**
     * Duplicate quotation
     */
    public function duplicate(Quotation $quotation)
    {
        DB::beginTransaction();
        try {
            $newQuotation = $quotation->replicate([
                'quotation_no',
                'status',
                'approval_status',
                'approved_by',
                'approved_date',
                'sent_date',
                'accepted_date',
                'converted_to_invoice_id'
            ]);

            $newQuotation->quotation_date = now()->toDateString();
            $newQuotation->valid_until = now()->addDays(30)->toDateString();
            $newQuotation->status = 'draft';
            $newQuotation->approval_status = 'pending';
            $newQuotation->created_by = Auth::id();
            $newQuotation->save();

            // Copy items
            foreach ($quotation->items as $item) {
                $newItem = $item->replicate();
                $newItem->quotation_id = $newQuotation->id;
                $newItem->save();
            }

            DB::commit();
            return redirect()->route('sales.quotations.edit', $newQuotation)
                           ->with('success', 'Quotation duplicated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('sales.quotations.show', $quotation)
                           ->with('error', 'Error duplicating quotation: ' . $e->getMessage());
        }
    }
}