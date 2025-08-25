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
use App\Models\Group;
use App\Models\Ledger;
use App\Services\QuotationPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CrmSetting;
use App\Helpers\SettingsHelper; // Add this import
use Illuminate\Support\Facades\Log;

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
            'customer_id' => 'required_without:lead_id|nullable|exists:customers,id',
            'lead_id' => 'required_without:customer_id|nullable|exists:leads,id',
            'valid_until' => 'required|date|after:quotation_date',
            'reference_no' => 'nullable|string|max:100',
            'subject' => 'nullable|string|max:500',
            'discount_type' => 'required|in:percentage,amount',
            'discount_value' => 'required|numeric|min:0',
            'terms_conditions' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:product,service',
            'items.*.item_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_type' => 'required|in:percentage,amount',
            'items.*.discount_value' => 'required|numeric|min:0',
            'items.*.tax_id' => 'nullable|exists:taxes,id',
            'items.*.description' => 'nullable|string',
        ]);

        // Validate that either customer or lead is provided
        if (!$validated['customer_id'] &&  !$validated['lead_id']) {

            return back()->withErrors(['customer_id' => 'Either customer or lead must be selected.'])->withInput();
        }

        DB::beginTransaction();
        try {
            // Create quotation
            $quotationData = $validated;

            $quotationData['created_by'] = Auth::id();
            $quotationData['status'] = 'draft';
            $quotationData['approval_status'] = 'pending';
            $quotationData['cloud_server_hosting'] = $request->has('cloud_server_hosting') ? 1 : 0;

            $quotation = Quotation::create($quotationData);

            $items = $quotationData['items'] ?? [];
            if (!empty($request->packageSelect)) {
                $items[] = [
                    'item_type' => 'package',
                    'item_id' => $request->packageSelect,
                    'quantity' => 1,
                    'unit_price' => 0, // or fetch the package price if needed
                    'discount_type' => $quotationData['discount_type'] ?? 'amount',
                    'discount_value' => $quotationData['discount_value'] ?? 0,
                    'description' => 'Package item',
                ];
            }
            // Create quotation items
            foreach ($items as $index => $itemData) {
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
            $quotationData['cloud_server_hosting'] = $request->has('cloud_server_hosting') ? 1 : 0;
            $quotation->update($quotationData);

            // Delete existing items
            $quotation->items()->delete();

            // Create new items
            $items = $quotationData['items'] ?? [];
            if (!empty($request->packageSelect)) {
                $items[] = [
                    'item_type' => 'package',
                    'item_id' => $request->packageSelect,
                    'quantity' => 1,
                    'unit_price' => 0, // or fetch the package price if needed
                    'discount_type' => $quotationData['discount_type'] ?? 'amount',
                    'discount_value' => $quotationData['discount_value'] ?? 0,
                    'description' => 'Package item',
                ];
            }
            // Create quotation items
            foreach ($items as $index => $itemData) {
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

        DB::beginTransaction();
        try {
            // Pre-checks before approval
            if ($quotation->lead_id && !$quotation->customer_id) {
                // Check if trade debtors group exists before attempting conversion
                $tradeDebtorGroup = Group::where('td', 1)->first();
                if (!$tradeDebtorGroup) {
                    throw new \Exception('Trade Debtors group not configured. Cannot convert lead to customer.');
                }

                // Check if lead still exists and is valid for conversion
                $lead = $quotation->lead;
                if (!$lead) {
                    throw new \Exception('Lead not found. Cannot proceed with approval.');
                }

                // Check if lead is already converted
                if ($lead->converted_to_customer_id) {
                    // Use existing customer
                    $quotation->update(['customer_id' => $lead->converted_to_customer_id]);
                }
            }

            // Approve and convert to invoice
            $invoice = $quotation->approve(Auth::id());

            DB::commit();

            return redirect()->route('sales.invoices.show', $invoice)
                ->with('success', 'Quotation approved and converted to invoice successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            // Log the error for debugging
            Log::error('Quotation approval error: ' . $e->getMessage(), [
                'quotation_id' => $quotation->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('sales.quotations.show', $quotation)
                ->with('error', 'Error approving quotation: ' . $e->getMessage());
        }
    }

    /**
     * Get customer details for pre-filling payment terms
     */
    public function getCustomerDetails(Request $request)
    {
        $customerId = $request->get('customer_id');

        if (!$customerId) {
            return response()->json(['error' => 'Customer ID required'], 400);
        }

        $customer = Customer::find($customerId);

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        return response()->json([
            'credit_days' => $customer->credit_days,
            'credit_limit' => $customer->credit_limit,
            'discount_percentage' => $customer->discount_percentage
        ]);
    }

    /**
     * Check quotation conversion readiness
     */
    public function checkConversionReadiness(Quotation $quotation)
    {
        $checks = [
            'has_items' => $quotation->items()->count() > 0,
            'has_customer_or_lead' => $quotation->customer_id || $quotation->lead_id,
            'trade_debtors_configured' => Group::where('td', 1)->exists(),
            'is_approved' => $quotation->approval_status === 'approved',
            'not_expired' => !$quotation->is_expired,
            'not_converted' => $quotation->status !== 'converted'
        ];

        $allChecksPass = collect($checks)->every(function ($check) {
            return $check === true;
        });

        return response()->json([
            'ready' => $allChecksPass,
            'checks' => $checks,
            'message' => $allChecksPass ? 'Ready for conversion' : 'Some requirements not met'
        ]);
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
                return $this->getPackages($request);
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

    public function preview(Quotation $quotation)
    {
        $quotation->load([
            'customer',
            'lead',
            'items.product',
            'items.service',
            'items.package',
            'items.uom',
            'items.tax',
            'createdBy'
        ]);

        return view('sales.quotations.preview', compact('quotation'));
    }

    /**
     * Generate PDF for quotation
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
            'items.tax',
            'createdBy'
        ]);

        // Get company information from settings
        $companyInfo = $this->getCompanyInfo();

        // Get terms and conditions from settings
        $termsConditions = SettingsHelper::getSetting('sales', 'terms_and_conditions');

        $data = [
            'quotation' => $quotation,
            'companyInfo' => $companyInfo,
            'termsConditions' => $termsConditions ?? $quotation->terms_conditions
        ];

        $pdf = PDF::loadView('sales.quotations.pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->getDomPDF()->set_option('isRemoteEnabled', true);

        return $pdf->download('quotation_' . $quotation->quotation_no . '.pdf');
    }

    /**
     * Generate print view for quotation
     */
    public function print(Quotation $quotation)
    {
        $quotation->load([
            'customer',
            'lead',
            'items.product',
            'items.service',
            'items.package',
            'items.uom',
            'items.tax',
            'createdBy'
        ]);

        // Get company information from settings
        $companyInfo = $this->getCompanyInfo();

        // Get terms and conditions from settings
        $termsConditions = SettingsHelper::getSetting('sales', 'terms_and_conditions');

        $data = [
            'quotation' => $quotation,
            'companyInfo' => $companyInfo,
            'termsConditions' => $termsConditions ?? $quotation->terms_conditions
        ];

        return view('sales.quotations.print', $data);
    }

    /**
     * Get company information from settings for quotations
     */
    private function getCompanyInfo()
    {
        return [
            'name' => SettingsHelper::getSetting('general', 'name', 'Company Name Not Set'),
            'address' => SettingsHelper::getSetting('general', 'address', 'Address Not Set'),
            'pincode' => SettingsHelper::getSetting('general', 'pincode', 'Pincode Not Set'),
            'state' => SettingsHelper::getSetting('general', 'state', 'State Not Set'),
            'country' => SettingsHelper::getSetting('general', 'country', 'MY'),
            'registration_number' => SettingsHelper::getSetting('general', 'registration_number', 'Registration Number Not Set'),
            'phone' => SettingsHelper::getSetting('general', 'phone', 'Phone Not Set'),
            'email' => SettingsHelper::getSetting('general', 'email', 'Email Not Set'),
            'website' => SettingsHelper::getSetting('general', 'website', 'Website Not Set'),
            'logo' => SettingsHelper::getCompanyLogo('general', 'logo',),
            'sub_logo' => SettingsHelper::getCompanySubLogo('general', 'sub_logo',),
            'currency' => SettingsHelper::getSetting('general', 'currency', 'MYR'),
        ];
    }

    /**
     * Calculate payment terms for quotation (if needed for future use)
     */
    private function calculatePaymentTerms($quotation)
    {
        $totalAmount = $quotation->total_amount;
        $paymentTerms = [];

        // Default payment schedule (you can customize this)
        $schedule = [
            ['name' => 'Deposit', 'percentage' => 25, 'days_offset' => 0],
            ['name' => 'Upon Installation', 'percentage' => 25, 'days_offset' => 15],
            ['name' => 'Third Payment', 'percentage' => 25, 'days_offset' => 45],
            ['name' => 'Final Payment', 'percentage' => 25, 'days_offset' => 75]
        ];

        foreach ($schedule as $index => $term) {
            $amount = ($totalAmount * $term['percentage']) / 100;
            $date = $quotation->quotation_date->addDays($term['days_offset']);

            $paymentTerms[] = [
                'no' => $index + 1,
                'item' => $term['name'],
                'date' => $date->format('d/m/Y'),
                'description' => '',
                'amount' => $amount
            ];
        }

        return $paymentTerms;
    }
    /**
     * Manual convert to invoice (separate from approval)
     */
    public function convertToInvoice(Quotation $quotation)
    {
        if ($quotation->status === 'converted') {
            return redirect()->route('sales.invoices.show', $quotation->convertedInvoice)
                ->with('info', 'Quotation has already been converted to invoice.');
        }

        if ($quotation->approval_status !== 'approved') {
            return redirect()->route('sales.quotations.show', $quotation)
                ->with('error', 'Quotation must be approved before conversion to invoice.');
        }

        DB::beginTransaction();
        try {
            $invoice = $quotation->convertToInvoice();

            DB::commit();
            return redirect()->route('sales.invoices.show', $invoice)
                ->with('success', 'Quotation converted to invoice successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('sales.quotations.show', $quotation)
                ->with('error', 'Error converting quotation: ' . $e->getMessage());
        }
    }

    /**
     * Manual convert lead to customer (separate method)
     */
    public function convertLeadToCustomer(Quotation $quotation)
    {
        if (!$quotation->lead_id) {
            return redirect()->route('sales.quotations.show', $quotation)
                ->with('error', 'This quotation is not associated with a lead.');
        }

        if ($quotation->customer_id) {
            return redirect()->route('sales.quotations.show', $quotation)
                ->with('info', 'Lead has already been converted to customer.');
        }

        DB::beginTransaction();
        try {
            // Check if lead is already converted
            $lead = $quotation->lead;
            if ($lead->converted_to_customer_id) {
                // Use existing customer
                $quotation->update(['customer_id' => $lead->converted_to_customer_id]);

                DB::commit();
                return redirect()->route('sales.quotations.show', $quotation)
                    ->with('success', 'Quotation linked to existing customer from lead conversion.');
            }

            // Convert lead to customer using the private method logic
            $customerId = $this->performLeadToCustomerConversion($quotation->lead);

            // Update quotation with new customer
            $quotation->update(['customer_id' => $customerId]);

            DB::commit();
            return redirect()->route('sales.quotations.show', $quotation)
                ->with('success', 'Lead converted to customer and linked to quotation successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('sales.quotations.show', $quotation)
                ->with('error', 'Error converting lead to customer: ' . $e->getMessage());
        }
    }
    /**
     * Private method to perform lead to customer conversion
     */
    private function performLeadToCustomerConversion(Lead $lead)
    {
        // Check if trade debtors group exists
        $tradeDebtorGroup = Group::where('td', 1)->first();
        if (!$tradeDebtorGroup) {
            throw new \Exception('Trade Debtors group not configured');
        }

        // Generate customer code
        $customerCode = $this->generateCustomerCode();

        // Create ledger for customer
        $ledgerName = ($lead->company_name ?: $lead->contact_person) . ' (' . $customerCode . ')';
        $ledger = Ledger::create([
            'group_id' => $tradeDebtorGroup->id,
            'name' => $ledgerName,
            'type' => 0,
            'reconciliation' => 0,
            'aging' => 1,
            'credit_aging' => 0
        ]);

        // Create customer from lead data
        $customer = Customer::create([
            'customer_code' => $customerCode,
            'ledger_id' => $ledger->id,
            'company_name' => $lead->company_name ?: $lead->contact_person,
            'contact_person' => $lead->contact_person,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'mobile' => $lead->mobile,
            'address_line1' => $lead->address,
            'city' => $lead->city,
            'state' => $lead->state,
            'country' => $lead->country ?: 'Malaysia',
            'source' => 'lead_conversion',
            'reference_by' => 'Lead: ' . $lead->lead_no,
            'assigned_to' => $lead->assigned_to,
            'status' => 'active',
            'notes' => 'Converted from lead: ' . $lead->lead_no,
            'lead_id' => $lead->id,
            'created_by' => auth()->id()
        ]);

        // Update lead with conversion details
        $lead->update([
            'lead_status' => 'won',
            'converted_to_customer_id' => $customer->id,
            'conversion_date' => now()
        ]);

        return $customer->id;
    }

    /**
     * Generate unique customer code
     */
    private function generateCustomerCode()
    {
        $prefix = 'CU';
        $newNumber = 1;

        do {
            $code = $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
            $exists = Customer::where('customer_code', $code)->exists();
            $newNumber++;
        } while ($exists);

        return $code;
    }
    /**
     * Get packages for dropdown
     */
    public function getPackages(Request $request)
    {
        $packages = Package::where('status', 1)
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->get(['id', 'name', 'code', 'package_price', 'subtotal', 'discount_percentage']);

        return response()->json($packages->map(function ($package) {
            return [
                'id' => $package->id,
                'name' => $package->name,
                'code' => $package->code,
                'price' => $package->package_price,
                'subtotal' => $package->subtotal,
                'discount_percentage' => $package->discount_percentage,
                'formatted_price' => 'RM ' . number_format($package->package_price, 2)
            ];
        }));
    }

    /**
     * Get package details including items
     */
    public function getPackageDetails($packageId)
    {
        $package = Package::with(['packageItems.service', 'packageItems.product'])
            ->where('status', 1)
            ->find($packageId);

        if (!$package) {
            return response()->json(['error' => 'Package not found'], 404);
        }

        return response()->json([
            'id' => $package->id,
            'name' => $package->name,
            'code' => $package->code,
            'description' => $package->description,
            'package_price' => $package->package_price,
            'subtotal' => $package->subtotal,
            'discount_percentage' => $package->discount_percentage,
            'discount_amount' => $package->discount_amount,
            'items' => $package->packageItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'service_id' => $item->service_id,
                    'product_id' => $item->product_id,
                    'item_type' => $item->item_type,
                    'item_name' => $item->service ? $item->service->name : ($item->product ? $item->product->name : 'N/A'),
                    'quantity' => $item->quantity,
                    'amount' => $item->amount,
                    'discount_percentage' => $item->discount_percentage,
                ];
            })
        ]);
    }

    /**
     * Get items based on type (existing method - update to include package)
     */
}
