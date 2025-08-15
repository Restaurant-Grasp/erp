<?php

namespace App\Http\Controllers;

use App\Models\PaymentMode;
use App\Models\Ledger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentModeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:payment_modes.view')->only(['index']);
        $this->middleware('permission:payment_modes.create')->only(['create', 'store']);
        $this->middleware('permission:payment_modes.edit')->only(['edit', 'update']);
        $this->middleware('permission:payment_modes.delete')->only('destroy');
    }

    /**
     * Display a listing of payment modes
     */
    public function index()
    {
        $paymentModes = PaymentMode::with(['ledger', 'createdBy'])
                                  ->orderBy('created_at', 'desc')
                                  ->get();

        return view('master.payment_modes.index', compact('paymentModes'));
    }

    /**
     * Show the form for creating a new payment mode
     */
    public function create()
    {
        // Get ledgers with type = 1 (Bank/Cash accounts)
        $ledgers = Ledger::where('type', 1)
                        ->orderBy('name')
                        ->get();

        return view('master.payment_modes.create', compact('ledgers'));
    }

    /**
     * Store a newly created payment mode
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:payment_modes,name',
            'ledger_id' => 'required|exists:ledgers,id',
            'description' => 'nullable|string',
            'status' => 'boolean'
        ]);

        $validated['created_by'] = Auth::id();
        $validated['status'] = $request->has('status') ? 1 : 0;

        PaymentMode::create($validated);

        return redirect()->route('payment-modes.index')
                        ->with('success', 'Payment mode created successfully.');
    }

    /**
     * Show the form for editing the payment mode
     */
    public function edit(PaymentMode $paymentMode)
    {
        // Get ledgers with type = 1 (Bank/Cash accounts)
        $ledgers = Ledger::where('type', 1)
                        ->orderBy('name')
                        ->get();

        return view('master.payment_modes.edit', compact('paymentMode', 'ledgers'));
    }

    /**
     * Update the specified payment mode
     */
    public function update(Request $request, PaymentMode $paymentMode)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:payment_modes,name,' . $paymentMode->id,
            'ledger_id' => 'required|exists:ledgers,id',
            'description' => 'nullable|string',
            'status' => 'boolean'
        ]);

        $validated['status'] = $request->has('status') ? 1 : 0;

        $paymentMode->update($validated);

        return redirect()->route('payment-modes.index')
                        ->with('success', 'Payment mode updated successfully.');
    }

    /**
     * Remove the specified payment mode
     */
    public function destroy(PaymentMode $paymentMode)
    {
        // Check if payment mode is being used
        $salesPayments = $paymentMode->salesPayments()->count();
        $purchasePayments = $paymentMode->purchasePayments()->count();

        if ($salesPayments > 0 || $purchasePayments > 0) {
            return redirect()->route('payment-modes.index')
                           ->with('error', 'Cannot delete payment mode as it is being used in payments.');
        }

        $paymentMode->delete();

        return redirect()->route('payment-modes.index')
                        ->with('success', 'Payment mode deleted successfully.');
    }

    /**
     * Get active payment modes for dropdown
     */
    public function getActivePaymentModes()
    {
        $paymentModes = PaymentMode::with('ledger')
                                  ->where('status', 1)
                                  ->orderBy('name')
                                  ->get();

        return response()->json($paymentModes->map(function ($mode) {
            return [
                'id' => $mode->id,
                'name' => $mode->name,
                'ledger_name' => $mode->ledger->name ?? '',
                'display_name' => $mode->display_name
            ];
        }));
    }
}