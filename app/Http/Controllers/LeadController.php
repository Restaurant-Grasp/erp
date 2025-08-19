<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadDocument;
use App\Models\TempleCategory;
use App\Models\Customer;
use App\Models\Staff;
use App\Models\Ledger;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:leads.view')->only(['index', 'show']);
        $this->middleware('permission:leads.create')->only(['create', 'store']);
        $this->middleware('permission:leads.edit')->only(['edit', 'update']);
        $this->middleware('permission:leads.delete')->only('destroy');
        $this->middleware('permission:leads.convert')->only(['convertToCustomer', 'processConversion']);
    }

    /**
     * Display a listing of the leads.
     */
    public function index(Request $request)
    {
        $query = Lead::with(['templeCategory', 'assignedTo', 'createdBy']);

        // Search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('lead_no', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('lead_status', $request->status);
        }

        // Temple category filter
        if ($request->filled('category')) {
            $query->where('temple_category_id', $request->category);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate(15);
        $templeCategories = TempleCategory::where('status', 1)->get();

        return view('leads.index', compact('leads', 'templeCategories'));
    }

    /**
     * Show the form for creating a new lead.
     */
    public function create()
    {
        $templeCategories = TempleCategory::where('status', 1)->orderBy('name')->get();
        $staff = Staff::where('status', 'active')->orderBy('name')->get();

        return view('leads.create', compact('templeCategories', 'staff'));
    }

    /**
     * Store a newly created lead in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'temple_category_id' => 'nullable|exists:temple_categories,id',
            'temple_size' => 'nullable|in:small,medium,large,very_large',
            'source' => 'required|in:online,reference,cold_call,exhibition,advertisement,other',
            'source_details' => 'nullable|string|max:255',
            'interested_in' => 'nullable|string',
            'assigned_to' => 'nullable|exists:staff,id',
            'notes' => 'nullable|string',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240'
        ]);

        DB::beginTransaction();
        try {
            $validated['created_by'] = Auth::id();
            $validated['lead_status'] = 'new';
            $validated['country'] = 'Malaysia'; // Default for temple management

            $lead = Lead::create($validated);

            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $this->storeDocument($lead, $document);
                }
            }
 
            // Create initial activity
            LeadActivity::create([
                'lead_id' => $lead->id,
                'activity_type' => 'note',
                'activity_date' => now(),
                'subject' => 'Lead Created',
                'description' => 'New lead created in the system',
                'created_by' => Auth::id()
            ]);

            DB::commit();
            return redirect()->route('leads.index')->with('success', 'Lead created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error creating lead: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified lead.
     */
    public function show(Lead $lead)
    {
        $lead->load([
            'templeCategory',
            'assignedTo',
            'createdBy',
            'activities.createdBy',
            'documents.uploadedBy',
            'quotations',
            'customer'
        ]);

        return view('leads.show', compact('lead'));
    }

    /**
     * Show the form for editing the specified lead.
     */
    public function edit(Lead $lead)
    {
        if ($lead->is_converted) {
            return redirect()->route('leads.show', $lead)->with('error', 'Converted leads cannot be edited.');
        }

        $templeCategories = TempleCategory::where('status', 1)->orderBy('name')->get();
        $staff = Staff::where('status', 'active')->orderBy('name')->get();

        return view('leads.edit', compact('lead', 'templeCategories', 'staff'));
    }

    /**
     * Update the specified lead in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        if ($lead->is_converted) {
            return redirect()->route('leads.show', $lead)->with('error', 'Converted leads cannot be edited.');
        }

        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'temple_category_id' => 'nullable|exists:temple_categories,id',
            'temple_size' => 'nullable|in:small,medium,large,very_large',
            'source' => 'required|in:online,reference,cold_call,exhibition,advertisement,other',
            'source_details' => 'nullable|string|max:255',
            'interested_in' => 'nullable|string',
            'lead_status' => 'required|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'assigned_to' => 'nullable|exists:staff,id',
            'next_followup_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'lost_reason' => 'nullable|required_if:lead_status,lost|string',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240'
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $lead->lead_status;
            $lead->update($validated);

            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $this->storeDocument($lead, $document);
                }
            }

            // Log status change activity
            if ($oldStatus != $lead->lead_status) {
                LeadActivity::create([
                    'lead_id' => $lead->id,
                    'activity_type' => 'note',
                    'activity_date' => now(),
                    'subject' => 'Status Changed',
                    'description' => "Lead status changed from {$oldStatus} to {$lead->lead_status}",
                    'created_by' => Auth::id(),
                ]);
            }

            DB::commit();
            return redirect()->route('leads.show', $lead)->with('success', 'Lead updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error updating lead: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified lead from storage.
     */
    public function destroy(Lead $lead)
    {
        if ($lead->is_converted) {
            return redirect()->route('leads.index')->with('error', 'Converted leads cannot be deleted.');
        }

        if ($lead->quotations()->count() > 0) {
            return redirect()->route('leads.index')->with('error', 'Cannot delete lead with quotations.');
        }

        $lead->delete();
        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully.');
    }

    /**
     * Show the form to convert lead to customer.
     */
    public function convertToCustomer(Lead $lead)
    {
        if ($lead->is_converted) {
            return redirect()->route('leads.show', $lead)->with('error', 'This lead has already been converted.');
        }

        if (!in_array($lead->lead_status, ['qualified', 'proposal', 'negotiation'])) {
            return redirect()->route('leads.show', $lead)->with('error', 'Only qualified leads can be converted to customers.');
        }

        // Get the trade debtor group for customer ledger creation
        $tradeDebtorGroup = Group::where('td', 1)->first();

        if (!$tradeDebtorGroup) {
            return redirect()->route('leads.show', $lead)
                ->with('error', 'Trade Debtors group not configured. Please configure accounting groups first.');
        }

        return view('leads.convert', compact('lead', 'tradeDebtorGroup'));
    }

    /**
     * Process the lead to customer conversion.
     */
    public function processConversion(Request $request, Lead $lead)
    {
        if ($lead->is_converted) {
            return redirect()->route('leads.show', $lead)->with('error', 'This lead has already been converted.');
        }

        $validated = $request->validate([
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100'
        ]);

        DB::beginTransaction();
        try {
            // Check if trade debtors group exists
            $tradeDebtorGroup = Group::where('td', 1)->first();
            if (!$tradeDebtorGroup) {
                throw new \Exception('Trade Debtors group not configured');
            }

            // Generate customer code
            $customerCode = $this->generateCustomerCode();
            $existingLedgersCount = Ledger::where('group_id', $tradeDebtorGroup->id)->count();
            $rightCodeNumber = $existingLedgersCount + 1;


            $rightCode = str_pad($rightCodeNumber, 4, '0', STR_PAD_LEFT);

            while (Ledger::where('right_code', $rightCode)->exists()) {
                $rightCodeNumber++;
                $rightCode = str_pad($rightCodeNumber, 4, '0', STR_PAD_LEFT);
            }
            $leftCode = str_pad($tradeDebtorGroup->id, 4, '0', STR_PAD_LEFT);

            // Create ledger
            $companyName = $lead->company_name ?: $lead->contact_person;
            $ledgerName = $companyName . ' (' . $customerCode . ')';

            $ledger = Ledger::create([
                'group_id' => $tradeDebtorGroup->id,
                'name' => $ledgerName,
                'type' => 0,
                'reconciliation' => 0,
                'aging' => 1,
                'credit_aging' => 0,
                'left_code' => $leftCode,
                'right_code' => $rightCode,
            ]);

            // Create customer record
            $customer = Customer::create([
                'customer_code' => $customerCode,
                'ledger_id' => $ledger->id,
                'company_name' => $companyName,
                'contact_person' => $lead->contact_person,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'mobile' => $lead->mobile,
                'address_line1' => $lead->address,
                'city' => $lead->city,
                'state' => $lead->state,
                'country' => $lead->country,
                'credit_limit' => $validated['credit_limit'] ?? 0,
                'credit_days' => $validated['credit_days'] ?? 30,
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
                'source' => $lead->source,
                'reference_by' => $lead->source_details,
                'assigned_to' => $lead->assigned_to,
                'status' => 'active',
                'notes' => $lead->notes,
                'created_by' => Auth::id(),
                'lead_id' => $lead->id
            ]);

            // Update lead with conversion details
            $lead->update([
                'converted_to_customer_id' => $customer->id,
                'conversion_date' => now(),
                'lead_status' => 'won'
            ]);

            // Log conversion activity
            LeadActivity::create([
                'lead_id' => $lead->id,
                'activity_type' => 'note',
                'activity_date' => now(),
                'subject' => 'Lead Converted',
                'description' => "Lead converted to customer: {$customer->customer_code}",
                'created_by' => Auth::id(),
            ]);

            DB::commit();
            return redirect()->route('customers.show', $customer)->with('success', 'Lead converted to customer successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error converting lead: ' . $e->getMessage());
        }
    }

    /**
     * Upload a document for the lead.
     */
    public function uploadDocument(Request $request, Lead $lead)
    {
        // Validate the request
        $request->validate([
            'documents' => 'required|array|min:1',
            'documents.*' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240'
        ]);

        DB::beginTransaction();
        try {
            $uploadedCount = 0;

            foreach ($request->file('documents') as $document) {
                $this->storeDocument($lead, $document);
                $uploadedCount++;
            }

            DB::commit();
            return redirect()->back()->with('success', "{$uploadedCount} document(s) uploaded successfully.");
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error uploading documents: ' . $e->getMessage());
        }
    }

    private function storeDocument(Lead $lead, $file)
    {
        $originalName = $file->getClientOriginalName();
        $fileName = time() . '_' . str_replace(' ', '_', $originalName);
        $filePath = $file->storeAs('leads/' . $lead->id, $fileName, 'public');

        // Create database record
        LeadDocument::create([
            'lead_id' => $lead->id,
            'document_name' => $originalName,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'file_type' => $file->getMimeType(),
            'uploaded_by' => Auth::id()
        ]);

        return true;
    }

    /**
     * Download a lead document.
     */
    public function downloadDocument(Lead $lead, LeadDocument $document)
    {
        if ($document->lead_id !== $lead->id) {
            abort(404);
        }

        $filePath = Storage::disk('public')->path($document->file_path);

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        return response()->download($filePath, $document->document_name);
    }

    /**
     * Delete a lead document.
     */
    public function deleteDocument(Lead $lead, LeadDocument $document)
    {
        if ($document->lead_id !== $lead->id) {
            abort(404);
        }

        $document->delete();
        return redirect()->back()->with('success', 'Document deleted successfully.');
    }

    /**
     * Generate a unique customer code.
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
     * Store activity for a lead
     */
    public function storeActivity(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'activity_type' => 'required|in:call,email,meeting,note',
            'subject' => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        $validated['lead_id'] = $lead->id;
        $validated['activity_date'] = now();
        $validated['created_by'] = Auth::id();

        LeadActivity::create($validated);

        // Update lead's last activity date
        $lead->update([
            'last_activity_date' => now(),
            'last_contact_date' => now()
        ]);

        return redirect()->back()->with('success', 'Activity recorded successfully.');
    }

    /**
     * Search leads for autocomplete
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        $leads = Lead::where(function ($q) use ($query) {
            $q->where('lead_no', 'like', "%{$query}%")
                ->orWhere('company_name', 'like', "%{$query}%")
                ->orWhere('contact_person', 'like', "%{$query}%");
        })
            ->where('lead_status', '!=', 'lost')
            ->whereNull('converted_to_customer_id')
            ->limit(10)
            ->get(['id', 'lead_no', 'company_name', 'contact_person']);

        return response()->json($leads);
    }
}
