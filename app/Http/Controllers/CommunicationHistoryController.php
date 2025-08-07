<?php

namespace App\Http\Controllers;


use App\Models\CommunicationHistory;
use App\Models\Lead;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunicationHistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:communication_history.view')->only(['index', 'show']);
        $this->middleware('permission:communication_history.create')->only(['store']);
    }

    /**
     * Display communication history
     */
    public function index(Request $request)
    {
        $query = CommunicationHistory::with(['lead', 'customer', 'followUp', 'recordedBy'])
            ->orderBy('communication_date', 'desc');

        // Filter by entity
        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        } elseif ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('communication_type', $request->type);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('communication_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('communication_date', '<=', $request->date_to);
        }

        $communications = $query->paginate(50);

        return view('communication-history.index', compact('communications'));
    }

    /**
     * Store new communication log
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'nullable|exists:leads,id',
            'customer_id' => 'nullable|exists:customers,id',
            'follow_up_id' => 'nullable|exists:follow_ups,id',
            'communication_type' => 'required|in:phone_call,email,whatsapp,sms,meeting,other',
            'direction' => 'required|in:incoming,outgoing',
            'contact_person' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'duration_minutes' => 'nullable|integer|min:1',
            'subject' => 'nullable|string|max:500',
            'content' => 'nullable|string',
            'outcome' => 'nullable|string|max:255'
        ]);

        // Ensure at least one entity is selected
        if (empty($validated['lead_id']) && empty($validated['customer_id'])) {
            return redirect()->back()
                ->with('error', 'Please select either a lead or customer.')
                ->withInput();
        }

        $validated['recorded_by'] = Auth::id();
        $validated['communication_date'] = now();

        // Auto-fill contact person if not provided
        if (empty($validated['contact_person'])) {
            if (!empty($validated['lead_id'])) {
                $lead = Lead::find($validated['lead_id']);
                $validated['contact_person'] = $lead->contact_person;
                $validated['contact_number'] = $validated['contact_number'] ?? $lead->mobile ?? $lead->phone;
            } elseif (!empty($validated['customer_id'])) {
                $customer = Customer::find($validated['customer_id']);
                $validated['contact_person'] = $customer->contact_person;
                $validated['contact_number'] = $validated['contact_number'] ?? $customer->mobile ?? $customer->phone;
            }
        }

        CommunicationHistory::create($validated);

        return redirect()->back()
            ->with('success', 'Communication logged successfully.');
    }
}
