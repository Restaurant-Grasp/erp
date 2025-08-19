<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Category;
use App\Models\Staff;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\ServiceType;
use App\Models\CustomerServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:customers.view')->only(['index', 'show']);
        $this->middleware('permission:customers.create')->only(['create', 'store']);
        $this->middleware('permission:customers.edit')->only(['edit', 'update']);
        $this->middleware('permission:customers.delete')->only('destroy');
    }

    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        $query = Customer::with(['category', 'assignedTo', 'ledger']);

        // Search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_code', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(15);
        $categories = Category::where('type', 'customer')->where('status', 1)->get();

        return view('customers.index', compact('customers', 'categories'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        // Check if trade debtors group exists
        $tradeDebtorGroup = Group::where('td', 1)->first();
        if (!$tradeDebtorGroup) {
            return redirect()->route('customers.index')
                ->with('error', 'Trade Debtors group not configured. Please configure accounting groups first.');
        }

        $categories = Category::where('type', 'customer')->where('status', 1)->orderBy('name')->get();
        $staff = Staff::where('status', 'active')->orderBy('name')->get();
        $serviceTypes = ServiceType::where('status', 1)->orderBy('name')->get();

        return view('customers.create', compact('categories', 'staff', 'serviceTypes'));
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:customers,email',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'registration_no' => 'nullable|string|max:100',
            'tax_no' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'source' => 'required|in:online,reference,direct,other',
            'reference_by' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:staff,id',
            'notes' => 'nullable|string',
            'service_types' => 'array',
            'service_types.*' => 'exists:service_types,id',

        ]);

        DB::beginTransaction();
        try {
            // Generate customer code
            $customerCode = $this->generateCustomerCode();
            // Get trade debtors group
            $tradeDebtorGroup = Group::where('td', 1)->first();
            if (!$tradeDebtorGroup) {
                throw new \Exception('Trade Debtors group not found');
            }
            $existingLedgersCount = Ledger::where('group_id', $tradeDebtorGroup->id)->count();
            $rightCodeNumber = $existingLedgersCount + 1;


            $rightCode = str_pad($rightCodeNumber, 4, '0', STR_PAD_LEFT);

            while (Ledger::where('right_code', $rightCode)->exists()) {
                $rightCodeNumber++;
                $rightCode = str_pad($rightCodeNumber, 4, '0', STR_PAD_LEFT);
            }
            $leftCode = str_pad($tradeDebtorGroup->code, 4, '0', STR_PAD_LEFT);

            // Create ledger for customer
            $ledgerName = $validated['company_name'] . ' (' . $customerCode . ')';

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


            // Create customer
            $validated['customer_code'] = $customerCode;
            $validated['ledger_id'] = $ledger->id;
            $validated['created_by'] = Auth::id();
            $validated['status'] = 'active';

            $customer = Customer::create($validated);

            // Attach service types if provided
            if ($request->has('service_types')) {
                foreach ($request->service_types as $serviceTypeId) {
                    CustomerServiceType::create([
                        'customer_id' => $customer->id,
                        'service_type_id' => $serviceTypeId
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error creating customer: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
        $customer->load([
            'category',
            'assignedTo',
            'createdBy',
            'ledger',
            'lead',
            'serviceTypes',
            'invoices' => function ($query) {
                $query->latest()->limit(10);
            },
            'quotations' => function ($query) {
                $query->latest()->limit(10);
            }
        ]);

        // Get customer statistics
        $statistics = [
            'total_invoices' => $customer->invoices()->count(),
            'total_revenue' => $customer->invoices()->sum('total_amount'),
            'outstanding_amount' => $customer->invoices()->whereIn('status', ['pending', 'partial'])->sum('balance_amount'),
            'total_quotations' => $customer->quotations()->count(),
            'quoted_value' => $customer->quotations()->sum('total_amount')
        ];

        return view('customers.show', compact('customer', 'statistics'));
    }

    /**
     * Show the form for editing the customer.
     */
    public function edit(Customer $customer)
    {
        $categories = Category::where('type', 'customer')->where('status', 1)->orderBy('name')->get();
        $staff = Staff::where('status', 'active')->orderBy('name')->get();
        $serviceTypes = ServiceType::where('status', 1)->orderBy('name')->get();
        $selectedServiceTypes = $customer->serviceTypes->pluck('id')->toArray();

        return view('customers.edit', compact('customer', 'categories', 'staff', 'serviceTypes', 'selectedServiceTypes'));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'registration_no' => 'nullable|string|max:100',
            'tax_no' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'source' => 'required|in:online,reference,direct,other',
            'reference_by' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:staff,id',
            'status' => 'required|in:active,inactive,blocked',
            'notes' => 'nullable|string',
            'service_types' => 'array',
            'service_types.*' => 'exists:service_types,id',

        ]);

        DB::beginTransaction();
        try {
            $customer->update($validated);

            // Update ledger name if company name changed
            if ($customer->ledger && $customer->wasChanged('company_name')) {
                $ledgerName = $validated['company_name'] . ' (' . $customer->customer_code . ')';
                $customer->ledger->update(['name' => $ledgerName]);
            }

            // Update service types
            CustomerServiceType::where('customer_id', $customer->id)->delete();
            if ($request->has('service_types')) {
                foreach ($request->service_types as $serviceTypeId) {
                    CustomerServiceType::create([
                        'customer_id' => $customer->id,
                        'service_type_id' => $serviceTypeId
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('customers.index', $customer)->with('success', 'Customer updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error updating customer: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(Customer $customer)
    {
        // Check for related records
        if ($customer->invoices()->count() > 0) {
            return redirect()->route('customers.index')->with('error', 'Cannot delete customer with invoices.');
        }

        if ($customer->quotations()->count() > 0) {
            return redirect()->route('customers.index')->with('error', 'Cannot delete customer with quotations.');
        }

        DB::beginTransaction();
        try {
            // Delete related records
            CustomerServiceType::where('customer_id', $customer->id)->delete();

            // Delete ledger if exists
            if ($customer->ledger) {
                $customer->ledger->delete();
            }

            $customer->delete();

            DB::commit();
            return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('customers.index')->with('error', 'Error deleting customer: ' . $e->getMessage());
        }
    }

    /**
     * Show customer statement
     */
    public function statement(Customer $customer)
    {
        $invoices = $customer->invoices()
            ->with('items')
            ->orderBy('invoice_date', 'desc')
            ->get();

        $payments = []; // Implement payment retrieval when payment module is ready

        return view('customers.statement', compact('customer', 'invoices', 'payments'));
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
     * Search customers for autocomplete
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        $customers = Customer::where(function ($q) use ($query) {
            $q->where('customer_code', 'like', "%{$query}%")
                ->orWhere('company_name', 'like', "%{$query}%")
                ->orWhere('contact_person', 'like', "%{$query}%");
        })
            ->where('status', 'active')
            ->limit(10)
            ->get(['id', 'customer_code', 'company_name', 'contact_person']);

        return response()->json($customers);
    }
}
