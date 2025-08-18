<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Category;
use App\Models\Staff;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\ServiceType;
use App\Models\VendorServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:vendors.view')->only(['index', 'show']);
        $this->middleware('permission:vendors.create')->only(['create', 'store']);
        $this->middleware('permission:vendors.edit')->only(['edit', 'update']);
        $this->middleware('permission:vendors.delete')->only('destroy');
    }

    /**
     * Display a listing of vendors.
     */
    public function index(Request $request)
    {
        $query = Vendor::with(['category', 'ledger']);

        // Search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('vendor_code', 'like', "%{$search}%")
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

        $vendors = $query->orderBy('created_at', 'desc')->paginate(15);
        $categories = Category::where('type', 'vendor')->where('status', 1)->get();

        return view('vendors.index', compact('vendors', 'categories'));
    }

    /**
     * Show the form for creating a new vendor.
     */
    public function create()
    {
        // Check if trade creditors group exists
         $tradeCreditorsGroup = Group::where('tc', 1)->first();
        if (!$tradeCreditorsGroup) {
            return redirect()->route('vendors.index')
                ->with('error', 'Trade Creditors group not configured. Please configure accounting groups first.');
        }

        $categories = Category::where('type', 'vendor')->where('status', 1)->orderBy('name')->get();
        $serviceTypes = ServiceType::where('status', 1)->orderBy('name')->get();

        return view('vendors.create', compact('categories', 'serviceTypes'));
    }

    /**
     * Store a newly created vendor.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:vendors,email',
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
            'payment_terms' => 'nullable|integer|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'service_types' => 'array',
            'service_types.*' => 'exists:service_types,id'
        ]);

        DB::beginTransaction();
        try {
            // Generate vendor code
            $vendorCode = $this->generateVendorCode();

            // Get trade creditors group
             $tradeCreditorsGroup = Group::where('tc', 1)->first();
          
            if (!$tradeCreditorsGroup) {
                throw new \Exception('Trade Creditors group not found');
            }
           $existingLedgersCount = Ledger::where('group_id', $tradeCreditorsGroup->id)->count();
            $rightCodeNumber = $existingLedgersCount + 1;

 
            $rightCode = str_pad($rightCodeNumber, 4, '0', STR_PAD_LEFT);

            while (Ledger::where('right_code', $rightCode)->exists()) {
                $rightCodeNumber++;
                $rightCode = str_pad($rightCodeNumber, 4, '0', STR_PAD_LEFT);
            }
            $leftCode = str_pad($tradeCreditorsGroup->id, 4, '0', STR_PAD_LEFT);
            // Create ledger for vendor
            $ledgerName = $validated['company_name'] . ' (' . $vendorCode . ')';
            $ledger = Ledger::create([
                'group_id' => $tradeCreditorsGroup->id,
                'name' => $ledgerName,
                'type' => 0,
                'reconciliation' => 0,
                'aging' => 1,
                'credit_aging' => 1,
                'left_code' => $leftCode,
                'right_code' => $rightCode,
            ]);

            // Create vendor
            $validated['vendor_code'] = $vendorCode;
            $validated['ledger_id'] = $ledger->id;
            $validated['created_by'] = Auth::id();
            $validated['status'] = 'active';
            $validated['payment_terms'] = $validated['payment_terms'] ?? 30;
            $validated['country'] = $validated['country'] ?? 'Malaysia';

            $vendor = Vendor::create($validated);

            // Attach service types if provided
            if ($request->has('service_types')) {
                foreach ($request->service_types as $serviceTypeId) {
                    VendorServiceType::create([
                        'vendor_id' => $vendor->id,
                        'service_type_id' => $serviceTypeId
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('vendors.index')->with('success', 'Vendor created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error creating vendor: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified vendor.
     */
    public function show(Vendor $vendor)
    {
        $vendor->load([
            'category',
            'createdBy',
            'ledger',
            'serviceTypes'
        ]);

        // Get vendor statistics
        $statistics = [
            'total_purchases' => 0, // Will be implemented when purchase orders are ready
            'total_amount' => 0,
            'outstanding_amount' => 0,
            'total_products' => $vendor->products()->count()
        ];

        return view('vendors.show', compact('vendor', 'statistics'));
    }

    /**
     * Show the form for editing the vendor.
     */
    public function edit(Vendor $vendor)
    {
        $categories = Category::where('type', 'vendor')->where('status', 1)->orderBy('name')->get();
        $serviceTypes = ServiceType::where('status', 1)->orderBy('name')->get();
        $selectedServiceTypes = $vendor->serviceTypes->pluck('id')->toArray();

        return view('vendors.edit', compact('vendor', 'categories', 'serviceTypes', 'selectedServiceTypes'));
    }

    /**
     * Update the specified vendor.
     */
    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:vendors,email,' . $vendor->id,
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
            'payment_terms' => 'nullable|integer|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive,blocked',
            'notes' => 'nullable|string',
            'service_types' => 'array',
            'service_types.*' => 'exists:service_types,id'
        ]);

        DB::beginTransaction();
        try {
            $vendor->update($validated);

            // Update ledger name if company name changed
            if ($vendor->ledger && $vendor->wasChanged('company_name')) {
                $ledgerName = $validated['company_name'] . ' (' . $vendor->vendor_code . ')';
                $vendor->ledger->update(['name' => $ledgerName]);
            }

            // Update service types
            VendorServiceType::where('vendor_id', $vendor->id)->delete();
            if ($request->has('service_types')) {
                foreach ($request->service_types as $serviceTypeId) {
                    VendorServiceType::create([
                        'vendor_id' => $vendor->id,
                        'service_type_id' => $serviceTypeId
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('vendors.show', $vendor)->with('success', 'Vendor updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error updating vendor: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified vendor.
     */
    public function destroy(Vendor $vendor)
    {
        // Check for related records
        if ($vendor->products()->count() > 0) {
            return redirect()->route('vendors.index')->with('error', 'Cannot delete vendor with assigned products.');
        }

        DB::beginTransaction();
        try {
            // Delete related records
            VendorServiceType::where('vendor_id', $vendor->id)->delete();

            // Delete ledger if exists
            if ($vendor->ledger) {
                $vendor->ledger->delete();
            }

            $vendor->delete();

            DB::commit();
            return redirect()->route('vendors.index')->with('success', 'Vendor deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('vendors.index')->with('error', 'Error deleting vendor: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique vendor code
     */
    private function generateVendorCode()
    {
        $prefix = 'VE';
        $lastVendor = Vendor::where('vendor_code', 'like', $prefix . '%')
            ->orderBy('vendor_code', 'desc')
            ->first();

        if ($lastVendor) {
            $lastNumber = intval(substr($lastVendor->vendor_code, strlen($prefix)));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Search vendors for autocomplete
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        $vendors = Vendor::where(function ($q) use ($query) {
            $q->where('vendor_code', 'like', "%{$query}%")
                ->orWhere('company_name', 'like', "%{$query}%")
                ->orWhere('contact_person', 'like', "%{$query}%");
        })
            ->where('status', 'active')
            ->limit(10)
            ->get(['id', 'vendor_code', 'company_name', 'contact_person']);

        return response()->json($vendors);
    }
}
