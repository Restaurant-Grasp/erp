<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use App\Models\Ledger;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaxController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:sales.taxes.view')->only(['index', 'show']);
        $this->middleware('permission:sales.taxes.create')->only(['create', 'store']);
        $this->middleware('permission:sales.taxes.edit')->only(['edit', 'update']);
        $this->middleware('permission:sales.taxes.delete')->only('destroy');
    }

    /**
     * Display a listing of taxes.
     */
    public function index(Request $request)
    {
        $query = Tax::with(['ledger', 'creator']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('percent', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Type filter
        if ($request->filled('applicable_for')) {
            $query->where('applicable_for', $request->applicable_for);
        }

        $taxes = $query->orderBy('name')->paginate(15);

        return view('sales.taxes.index', compact('taxes'));
    }

    /**
     * Show the form for creating a new tax.
     */
    public function create()
    {
        // Get tax liability ledgers
        $taxLiabilityGroup = Group::where('name', 'like', '%tax%')->first();
        $ledgers = collect();
        if ($taxLiabilityGroup) {
            $ledgers = Ledger::where('group_id', $taxLiabilityGroup->id)->orderBy('name')->get();
        }

        return view('sales.taxes.create', compact('ledgers'));
    }

    /**
     * Store a newly created tax.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:300|unique:taxes,name',
            'percent' => 'required|numeric|min:0|max:100',
            'applicable_for' => 'required|in:product,service,both',
            'ledger_id' => 'required|exists:ledgers,id',
            'status' => 'required|boolean'
        ]);

        $validated['created_by'] = Auth::id();

        Tax::create($validated);

        return redirect()->route('sales.taxes.index')
            ->with('success', 'Tax created successfully.');
    }

    /**
     * Display the specified tax.
     */
    public function show(Tax $tax)
    {
        $tax->load(['ledger', 'creator']);

        return view('sales.taxes.show', compact('tax'));
    }

    /**
     * Show the form for editing the specified tax.
     */
    public function edit(Tax $tax)
    {
        // Get tax liability ledgers
        $taxLiabilityGroup = Group::where('name', 'like', '%tax%')->first();
        $ledgers = collect();

        if ($taxLiabilityGroup) {
            $ledgers = Ledger::where('group_id', $taxLiabilityGroup->id)->orderBy('name')->get();
        }

        return view('sales.taxes.edit', compact('tax', 'ledgers'));
    }

    /**
     * Update the specified tax.
     */
    public function update(Request $request, Tax $tax)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:300|unique:taxes,name,' . $tax->id,
            'percent' => 'required|numeric|min:0|max:100',
            'applicable_for' => 'required|in:product,service,both',
            'ledger_id' => 'required|exists:ledgers,id',
            'status' => 'required|boolean'
        ]);

        $tax->update($validated);

        return redirect()->route('sales.taxes.index')
            ->with('success', 'Tax updated successfully.');
    }

    /**
     * Remove the specified tax.
     */
    public function destroy(Tax $tax)
    {
        // Check if tax is being used
        $isUsedInQuotations = DB::table('quotation_items')->where('tax_id', $tax->id)->exists();
        $isUsedInInvoices = DB::table('sales_invoice_items')->where('tax_id', $tax->id)->exists();

        if ($isUsedInQuotations || $isUsedInInvoices) {
            return redirect()->route('sales.taxes.index')
                ->with('error', 'Cannot delete tax that is being used in quotations or invoices.');
        }

        $tax->delete();

        return redirect()->route('sales.taxes.index')
            ->with('success', 'Tax deleted successfully.');
    }

    /**
     * Get taxes for dropdown
     */
    public function getTaxesForDropdown(Request $request)
    {

        $itemType = $request->get('item_type', 'both');

        $query = Tax::active();

        if ($itemType !== 'both') {
            if ($itemType === 'product') {
                $query->forProducts();
            } elseif ($itemType === 'service') {
                $query->forServices();
            }
        }

        $taxes = $query->orderBy('name')->get(['id', 'name', 'percent']);

        return response()->json($taxes);
    }

    /**
     * Calculate tax amount
     */
    public function calculateTax(Request $request)
    {
        $validated = $request->validate([
            'tax_id' => 'required|exists:taxes,id',
            'amount' => 'required|numeric|min:0'
        ]);

        $tax = Tax::find($validated['tax_id']);
        $taxAmount = $tax->calculateTaxAmount($validated['amount']);

        return response()->json([
            'tax_rate' => $tax->percent,
            'tax_amount' => $taxAmount,
            'total_amount' => $validated['amount'] + $taxAmount
        ]);
    }

    /**
     * API: Get all taxes
     */
    public function apiIndex(Request $request)
    {
        $query = Tax::with(['ledger']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('applicable_for')) {
            $query->where('applicable_for', $request->applicable_for);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('percent', 'like', "%{$search}%");
            });
        }

        $taxes = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $taxes,
            'total' => $taxes->count()
        ]);
    }
    /**
     * API: Get single tax
     */
    public function apiShow(Tax $tax)
    {
        $tax->load(['ledger', 'creator']);

        // Get usage statistics
        $tax->usage_stats = [
            'quotations' => DB::table('quotation_items')->where('tax_id', $tax->id)->count(),
            'invoices' => DB::table('sales_invoice_items')->where('tax_id', $tax->id)->count(),
            'total_collected' => DB::table('sales_invoice_items')->where('tax_id', $tax->id)->sum('tax_amount')
        ];

        return response()->json([
            'success' => true,
            'data' => $tax
        ]);
    }
    /**
     * API: Search taxes
     */
    public function apiSearch($query)
    {
        $taxes = Tax::where('name', 'like', "%{$query}%")
            ->where('status', 1)
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'percent', 'applicable_for']);

        return response()->json([
            'success' => true,
            'data' => $taxes
        ]);
    }
}
