<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\Product;
use App\Models\Service;
use App\Models\Package;
use App\Models\Tax;
use App\Models\Uom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:sales.invoices.view')->only(['index', 'show']);
        $this->middleware('permission:sales.invoices.create')->only(['create', 'store']);
        $this->middleware('permission:sales.invoices.edit')->only(['edit', 'update']);
        $this->middleware('permission:sales.invoices.cancel')->only('cancel');
    }

    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        $query = SalesInvoice::with(['customer', 'quotation', 'createdBy']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhere('po_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('company_name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        // Due date filter
        if ($request->filled('due_status')) {
            switch ($request->due_status) {
                case 'overdue':
                    $query->overdue();
                    break;
                case 'due_today':
                    $query->whereDate('due_date', now()->toDateString())
                          ->whereIn('status', ['pending', 'partial']);
                    break;
                case 'due_this_week':
                    $query->whereBetween('due_date', [now(), now()->addWeek()])
                          ->whereIn('status', ['pending', 'partial']);
                    break;
            }
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15);

        // Calculate statistics
        $statistics = [
            'total_invoices' => SalesInvoice::count(),
            'total_amount' => SalesInvoice::sum('total_amount'),
            'outstanding_amount' => SalesInvoice::whereIn('status', ['pending', 'partial'])->sum('balance_amount'),
            'overdue_amount' => SalesInvoice::overdue()->sum('balance_amount')
        ];

        return view('sales.invoices.index', compact('invoices', 'statistics'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create(Request $request)
    {
        $customers = Customer::where('status', 'active')->orderBy('company_name')->get();
        
        // Pre-fill data if coming from quotation
        $quotation = null;
        if ($request->filled('quotation_id')) {
            $quotation = Quotation::with('items')->find($request->quotation_id);
        }

        return view('sales.invoices.create', compact('customers', 'quotation'));
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'reference_no' => 'nullable|string|max:100',
            'po_no' => 'nullable|string|max:100',
            'currency' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0',
            'payment_terms' => 'required|integer|min:1',
            'discount_type' => 'required|in:percentage,amount',
            'discount_value' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
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

        DB::beginTransaction();
        try {
            // Create invoice
            $invoiceData = $validated;
            unset($invoiceData['items']);
            $invoiceData['created_by'] = Auth::id();
            $invoiceData['status'] = 'pending';
            $invoiceData['due_date'] = now()->addDays($validated['payment_terms']);

            $invoice = SalesInvoice::create($invoiceData);

            // Create invoice items
            foreach ($validated['items'] as $index => $itemData) {
                $itemData['invoice_id'] = $invoice->id;
                $itemData['delivered_quantity'] = 0;
                $itemData['delivery_status'] = 'not_delivered';
                $itemData['sort_order'] = $index + 1;
                SalesInvoiceItem::create($itemData);
            }

            // Calculate totals
            $invoice->calculateTotals();

            // Create accounting entries
            $invoice->createAccountingEntries();

            // Submit to e-invoice system if enabled
            if (config('settings.e_invoice_auto_submit', false)) {
                $invoice->submitToEInvoice();
            }

            DB::commit();
            return redirect()->route('sales.invoices.show', $invoice)
                           ->with('success', 'Invoice created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error creating invoice: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show(SalesInvoice $invoice)
    {
        $invoice->load([
            'customer',
            'quotation',
            'items.product',
            'items.service',
            'items.package',
            'items.uom',
            'items.tax',
            'deliveryOrders.items',
            'createdBy'
        ]);

        return view('sales.invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(SalesInvoice $invoice)
    {
        if ($invoice->status === 'paid' || $invoice->status === 'cancelled') {
            return redirect()->route('sales.invoices.show', $invoice)
                           ->with('error', 'Invoice cannot be edited in current status.');
        }

        $customers = Customer::where('status', 'active')->orderBy('company_name')->get();
        $invoice->load('items');

        return view('sales.invoices.edit', compact('invoice', 'customers'));
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, SalesInvoice $invoice)
    {
        if ($invoice->status === 'paid' || $invoice->status === 'cancelled') {
            return redirect()->route('sales.invoices.show', $invoice)
                           ->with('error', 'Invoice cannot be edited in current status.');
        }

        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'reference_no' => 'nullable|string|max:100',
            'po_no' => 'nullable|string|max:100',
            'currency' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0',
            'payment_terms' => 'required|integer|min:1',
            'discount_type' => 'required|in:percentage,amount',
            'discount_value' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
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

        DB::beginTransaction();
        try {
            // Update invoice
            $invoiceData = $validated;
            unset($invoiceData['items']);
            $invoiceData['due_date'] = $invoice->invoice_date->addDays($validated['payment_terms']);
            $invoice->update($invoiceData);

            // Delete existing items
            $invoice->items()->delete();

            // Create new items
            foreach ($validated['items'] as $index => $itemData) {
                $itemData['invoice_id'] = $invoice->id;
                $itemData['delivered_quantity'] = 0;
                $itemData['delivery_status'] = 'not_delivered';
                $itemData['sort_order'] = $index + 1;
                SalesInvoiceItem::create($itemData);
            }

            // Calculate totals
            $invoice->calculateTotals();

            // Update accounting entries
            $invoice->createAccountingEntries();

            DB::commit();
            return redirect()->route('sales.invoices.show', $invoice)
                           ->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error updating invoice: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Cancel the specified invoice.
     */
    public function cancel(Request $request, SalesInvoice $invoice)
    {
        if (!$invoice->can_be_cancelled) {
            return redirect()->route('sales.invoices.show', $invoice)
                           ->with('error', 'Invoice cannot be cancelled as payments have been made.');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:1000'
        ]);

        try {
            $invoice->cancel();
            $invoice->update(['notes' => ($invoice->notes . "\n\nCancellation Reason: " . $validated['cancellation_reason'])]);

            return redirect()->route('sales.invoices.show', $invoice)
                           ->with('success', 'Invoice cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()->route('sales.invoices.show', $invoice)
                           ->with('error', 'Error cancelling invoice: ' . $e->getMessage());
        }
    }

    /**
     * Create delivery order from invoice
     */
    public function createDeliveryOrder(SalesInvoice $invoice)
    {
        // Check if invoice has items that need delivery
        $pendingItems = $invoice->items()->where('delivery_status', '!=', 'delivered')->count();
        
        if ($pendingItems === 0) {
            return redirect()->route('sales.invoices.show', $invoice)
                           ->with('error', 'All items have been delivered.');
        }

        return redirect()->route('sales.delivery-orders.create', ['invoice_id' => $invoice->id])
                        ->with('success', 'Create delivery order for pending items.');
    }

    /**
     * Submit to e-invoice
     */
    public function submitEInvoice(SalesInvoice $invoice)
    {
        if ($invoice->e_invoice_status !== 'not_submitted') {
            return redirect()->route('sales.invoices.show', $invoice)
                           ->with('error', 'Invoice has already been submitted to e-invoice system.');
        }

        try {
            $invoice->submitToEInvoice();
            
            return redirect()->route('sales.invoices.show', $invoice)
                           ->with('success', 'Invoice submitted to e-invoice system successfully.');
        } catch (\Exception $e) {
            return redirect()->route('sales.invoices.show', $invoice)
                           ->with('error', 'Error submitting to e-invoice: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF of invoice
     */
    public function pdf(SalesInvoice $invoice)
    {
        $invoice->load([
            'customer',
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
     * Duplicate invoice
     */
    public function duplicate(SalesInvoice $invoice)
    {
        DB::beginTransaction();
        try {
            $newInvoice = $invoice->replicate([
                'invoice_no',
                'status',
                'paid_amount',
                'balance_amount',
                'e_invoice_status',
                'e_invoice_uuid',
                'e_invoice_submission_date',
                'entry_id'
            ]);

            $newInvoice->invoice_date = now()->toDateString();
            $newInvoice->due_date = now()->addDays($invoice->payment_terms);
            $newInvoice->status = 'pending';
            $newInvoice->balance_amount = $newInvoice->total_amount;
            $newInvoice->created_by = Auth::id();
            $newInvoice->save();

            // Copy items
            foreach ($invoice->items as $item) {
                $newItem = $item->replicate([
                    'delivered_quantity',
                    'delivery_status'
                ]);
                $newItem->invoice_id = $newInvoice->id;
                $newItem->delivered_quantity = 0;
                $newItem->delivery_status = 'not_delivered';
                $newItem->save();
            }

            DB::commit();
            return redirect()->route('sales.invoices.edit', $newInvoice)
                           ->with('success', 'Invoice duplicated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('sales.invoices.show', $invoice)
                           ->with('error', 'Error duplicating invoice: ' . $e->getMessage());
        }
    }

    /**
     * Get invoice statistics
     */
    public function getStatistics()
    {
        $statistics = [
            'total_invoices' => SalesInvoice::count(),
            'pending_invoices' => SalesInvoice::pending()->count(),
            'overdue_invoices' => SalesInvoice::overdue()->count(),
            'total_revenue' => SalesInvoice::sum('total_amount'),
            'outstanding_amount' => SalesInvoice::whereIn('status', ['pending', 'partial'])->sum('balance_amount'),
            'overdue_amount' => SalesInvoice::overdue()->sum('balance_amount'),
            'this_month_revenue' => SalesInvoice::whereMonth('invoice_date', now()->month)
                                              ->whereYear('invoice_date', now()->year)
                                              ->sum('total_amount')
        ];

        return response()->json($statistics);
    }
}