<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SalesInvoice;
use App\Models\SalesInvoicePayment;
use App\Models\PaymentMode;
use App\Models\User;
use App\Services\AccountMigrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SalesPaymentController extends Controller
{
    protected $accountMigrationService;

    public function __construct(AccountMigrationService $accountMigrationService)
    {
        $this->middleware('permission:sales.payments.view')->only(['index', 'show']);
        $this->middleware('permission:sales.payments.create')->only(['create', 'store']);
        $this->middleware('permission:sales.payments.edit')->only(['edit', 'update']);
        $this->middleware('permission:sales.payments.delete')->only('destroy');
        
        $this->accountMigrationService = $accountMigrationService;
    }

    /**
     * Display a listing of payments for an invoice
     */
    public function index(SalesInvoice $invoice)
    {
        $payments = $invoice->payments()
                           ->with(['paymentMode', 'receivedBy', 'createdBy'])
                           ->orderBy('payment_date', 'desc')
                           ->paginate(15);

        return view('sales.payments.index', compact('invoice', 'payments'));
    }

    /**
     * Show the form for creating a new payment
     */
    public function create(SalesInvoice $invoice)
    {
        // Check if invoice can receive payments
        if ($invoice->status === 'paid' || $invoice->status === 'cancelled') {
            return redirect()->route('sales.invoices.show', $invoice)
                           ->with('error', 'Cannot add payment to invoice with current status.');
        }

        if ($invoice->balance_amount <= 0) {
            return redirect()->route('sales.invoices.show', $invoice)
                           ->with('error', 'Invoice is already fully paid.');
        }

        $paymentModes = PaymentMode::where('status', 1)
                                  ->orderBy('name')
                                  ->get();

        $users = User::where('is_active', 1)->orderBy('name')->get();

        return view('sales.payments.create', compact('invoice', 'paymentModes', 'users'));
    }

    /**
     * Store a newly created payment
     */
    public function store(Request $request, SalesInvoice $invoice)
    {
        $validated = $request->validate([
            'payment_date' => 'required|date|before_or_equal:today',
            'paid_amount' => 'required|numeric|min:0.01|max:' . $invoice->balance_amount,
            'payment_mode_id' => 'required|exists:payment_modes,id',
            'received_by' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:1000',
            'file_upload' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        DB::beginTransaction();
        try {
            // Handle file upload
            $filePath = null;
            if ($request->hasFile('file_upload')) {
                $file = $request->file('file_upload');
                $filename = 'payment_' . time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('payments/sales', $filename, 'public');
            }

            // Create payment record
            $payment = SalesInvoicePayment::create([
                'invoice_id' => $invoice->id,
                'payment_date' => $validated['payment_date'],
                'paid_amount' => $validated['paid_amount'],
                'payment_mode_id' => $validated['payment_mode_id'],
                'received_by' => $validated['received_by'],
                'notes' => $validated['notes'],
                'file_upload' => $filePath,
                'created_by' => Auth::id(),
            ]);

            // Migrate to accounting system
            try {
                $this->accountMigrationService->migrateSalesInvoicePayment($payment);
            } catch (\Exception $e) {
                // Log error but don't fail the payment creation
                Log::error("Failed to migrate sales payment {$payment->id} to accounting: " . $e->getMessage());
            }

            DB::commit();

            return redirect()->route('sales.invoices.show', $invoice)
                           ->with('success', 'Payment recorded successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            
            // Delete uploaded file if payment creation failed
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            
            return redirect()->back()
                           ->with('error', 'Error recording payment: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Display the specified payment
     */
    public function show(SalesInvoice $invoice, SalesInvoicePayment $payment)
    {
        $payment->load(['paymentMode', 'receivedBy', 'createdBy']);
        
        return view('sales.payments.show', compact('invoice', 'payment'));
    }

    /**
     * Update the specified payment
     */
    public function update(Request $request, SalesInvoice $invoice, SalesInvoicePayment $payment)
    {
        // Only allow updating if not migrated to accounting
        if ($payment->account_migration) {
            return redirect()->route('sales.payments.show', [$invoice, $payment])
                           ->with('error', 'Cannot update payment that has been migrated to accounting.');
        }

        $maxAmount = $invoice->balance_amount + $payment->paid_amount;
        
        $validated = $request->validate([
            'payment_date' => 'required|date|before_or_equal:today',
            'paid_amount' => 'required|numeric|min:0.01|max:' . $maxAmount,
            'payment_mode_id' => 'required|exists:payment_modes,id',
            'received_by' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:1000',
            'file_upload' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        DB::beginTransaction();
        try {
            // Handle file upload
            $filePath = $payment->file_upload;
            if ($request->hasFile('file_upload')) {
                // Delete old file
                if ($filePath && Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
                
                $file = $request->file('file_upload');
                $filename = 'payment_' . time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('payments/sales', $filename, 'public');
            }

            // Update payment
            $payment->update([
                'payment_date' => $validated['payment_date'],
                'paid_amount' => $validated['paid_amount'],
                'payment_mode_id' => $validated['payment_mode_id'],
                'received_by' => $validated['received_by'],
                'notes' => $validated['notes'],
                'file_upload' => $filePath,
            ]);

            // Re-migrate to accounting system if needed
            try {
                $this->accountMigrationService->migrateSalesInvoicePayment($payment);
            } catch (\Exception $e) {
                Log::error("Failed to re-migrate sales payment {$payment->id} to accounting: " . $e->getMessage());
            }

            DB::commit();

            return redirect()->route('sales.payments.show', [$invoice, $payment])
                           ->with('success', 'Payment updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                           ->with('error', 'Error updating payment: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Remove the specified payment
     */
    public function destroy(SalesInvoice $invoice, SalesInvoicePayment $payment)
    {
        // Only allow deleting if not migrated to accounting
        if ($payment->account_migration) {
            return redirect()->route('sales.payments.index', $invoice)
                           ->with('error', 'Cannot delete payment that has been migrated to accounting.');
        }

        DB::beginTransaction();
        try {
            // Delete file if exists
            if ($payment->file_upload && Storage::disk('public')->exists($payment->file_upload)) {
                Storage::disk('public')->delete($payment->file_upload);
            }

            $payment->delete();

            DB::commit();

            return redirect()->route('sales.invoices.show', $invoice)
                           ->with('success', 'Payment deleted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('sales.payments.index', $invoice)
                           ->with('error', 'Error deleting payment: ' . $e->getMessage());
        }
    }
}