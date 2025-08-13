<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoicePayment;
use App\Models\PaymentMode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PurchasePaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:purchases.payments.view')->only(['index', 'show']);
        $this->middleware('permission:purchases.payments.create')->only(['create', 'store']);
        $this->middleware('permission:purchases.payments.edit')->only(['edit', 'update']);
        $this->middleware('permission:purchases.payments.delete')->only('destroy');
    }

    /**
     * Display payments for a specific invoice
     */
    public function index(PurchaseInvoice $invoice)
    {
        $payments = $invoice->payments()
                           ->with(['paymentMode', 'receivedBy', 'createdBy'])
                           ->orderBy('payment_date', 'desc')
                           ->get();

        return response()->json([
            'invoice' => $invoice,
            'payments' => $payments,
            'total_paid' => $payments->sum('paid_amount'),
            'remaining_balance' => $invoice->balance_amount
        ]);
    }

    /**
     * Show payment form
     */
    public function create(PurchaseInvoice $invoice)
    {
        // Check if invoice can receive payments
        if (!in_array($invoice->status, ['pending', 'partial', 'overdue'])) {
            return response()->json(['error' => 'Invoice cannot receive payments in current status.'], 400);
        }

        $paymentModes = PaymentMode::with('ledger')->where('status', 1)->orderBy('name')->get();
        $users = User::where('status', 'active')->orderBy('name')->get();

        return response()->json([
            'invoice' => [
                'id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'vendor_name' => $invoice->vendor->company_name,
                'total_amount' => $invoice->total_amount,
                'paid_amount' => $invoice->paid_amount,
                'balance_amount' => $invoice->balance_amount
            ],
            'payment_modes' => $paymentModes->map(function ($mode) {
                return [
                    'id' => $mode->id,
                    'name' => $mode->name,
                    'ledger_name' => $mode->ledger->name ?? ''
                ];
            }),
            'users' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name
                ];
            })
        ]);
    }

    /**
     * Store a new payment
     */
    public function store(Request $request, PurchaseInvoice $invoice)
    {
        // Check if invoice can receive payments
        if (!in_array($invoice->status, ['pending', 'partial', 'overdue'])) {
            return response()->json(['error' => 'Invoice cannot receive payments in current status.'], 400);
        }

        $validated = $request->validate([
            'payment_date' => 'required|date|before_or_equal:today',
            'paid_amount' => 'required|numeric|min:0.01|max:' . $invoice->balance_amount,
            'payment_mode_id' => 'required|exists:payment_modes,id',
            'received_by' => 'required|exists:users,id',
            'file_upload' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf,docx,doc|max:10240', // 10MB max
            'notes' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();
        try {
            // Handle file upload
            $fileName = null;
            if ($request->hasFile('file_upload')) {
                $file = $request->file('file_upload');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('payments', $fileName, 'public');
            }

            // Create payment record
            $payment = PurchaseInvoicePayment::create([
                'invoice_id' => $invoice->id,
                'payment_date' => $validated['payment_date'],
                'paid_amount' => $validated['paid_amount'],
                'payment_mode_id' => $validated['payment_mode_id'],
                'received_by' => $validated['received_by'],
                'file_upload' => $fileName,
                'notes' => $validated['notes'],
                'account_migration' => 0, // Will be updated after account migration
                'created_by' => Auth::id()
            ]);

            // Account migration (empty method for now)
            $this->accountMigration($payment);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully.',
                'payment' => $payment->load(['paymentMode', 'receivedBy', 'createdBy'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            // Delete uploaded file if exists
            if ($fileName) {
                Storage::disk('public')->delete('payments/' . $fileName);
            }

            return response()->json(['error' => 'Error recording payment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified payment
     */
    public function show(PurchaseInvoice $invoice, PurchaseInvoicePayment $payment)
    {
        if ($payment->invoice_id !== $invoice->id) {
            return response()->json(['error' => 'Payment not found for this invoice.'], 404);
        }

        $payment->load(['paymentMode.ledger', 'receivedBy', 'createdBy']);

        return response()->json($payment);
    }

    /**
     * Update the specified payment
     */
    public function update(Request $request, PurchaseInvoice $invoice, PurchaseInvoicePayment $payment)
    {
        if ($payment->invoice_id !== $invoice->id) {
            return response()->json(['error' => 'Payment not found for this invoice.'], 404);
        }

        // Calculate max amount (current balance + this payment amount)
        $maxAmount = $invoice->balance_amount + $payment->paid_amount;

        $validated = $request->validate([
            'payment_date' => 'required|date|before_or_equal:today',
            'paid_amount' => 'required|numeric|min:0.01|max:' . $maxAmount,
            'payment_mode_id' => 'required|exists:payment_modes,id',
            'received_by' => 'required|exists:users,id',
            'file_upload' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf,docx,doc|max:10240',
            'notes' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();
        try {
            $oldFileName = $payment->file_upload;

            // Handle file upload
            $fileName = $oldFileName;
            if ($request->hasFile('file_upload')) {
                $file = $request->file('file_upload');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('payments', $fileName, 'public');

                // Delete old file
                if ($oldFileName) {
                    Storage::disk('public')->delete('payments/' . $oldFileName);
                }
            }

            // Update payment
            $payment->update([
                'payment_date' => $validated['payment_date'],
                'paid_amount' => $validated['paid_amount'],
                'payment_mode_id' => $validated['payment_mode_id'],
                'received_by' => $validated['received_by'],
                'file_upload' => $fileName,
                'notes' => $validated['notes']
            ]);

            // Recalculate invoice totals
            $this->updateInvoiceStatus($invoice);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment updated successfully.',
                'payment' => $payment->load(['paymentMode', 'receivedBy', 'createdBy'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Error updating payment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified payment
     */
    public function destroy(PurchaseInvoice $invoice, PurchaseInvoicePayment $payment)
    {
        if ($payment->invoice_id !== $invoice->id) {
            return response()->json(['error' => 'Payment not found for this invoice.'], 404);
        }

        DB::beginTransaction();
        try {
            // Delete file if exists
            if ($payment->file_upload) {
                Storage::disk('public')->delete('payments/' . $payment->file_upload);
            }

            $payment->delete();

            // Recalculate invoice totals
            $this->updateInvoiceStatus($invoice);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Error deleting payment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update invoice status and amounts
     */
    private function updateInvoiceStatus(PurchaseInvoice $invoice)
    {
        $totalPaid = $invoice->payments()->sum('paid_amount');
        $balanceAmount = $invoice->total_amount - $totalPaid;

        $status = 'pending';
        if ($totalPaid >= $invoice->total_amount) {
            $status = 'paid';
        } elseif ($totalPaid > 0) {
            $status = 'partial';
        }

        $invoice->update([
            'paid_amount' => $totalPaid,
            'balance_amount' => $balanceAmount,
            'status' => $status
        ]);
    }

    /**
     * Account migration placeholder
     */
    private function accountMigration(PurchaseInvoicePayment $payment)
    {
        // Empty method for future account integration
        // Once implemented, update payment record: account_migration = 1
        
        // Example of what will be implemented later:
        // 1. Create credit entry for payment mode ledger (bank/cash account)
        // 2. Create debit entry for vendor ledger
        // 3. Update payment record with account_migration = 1
    }
}