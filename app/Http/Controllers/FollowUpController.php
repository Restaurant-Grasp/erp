<?php

namespace App\Http\Controllers;

use App\Models\FollowUp;
use App\Models\FollowUpTemplate;
use App\Models\CommunicationHistory;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FollowUpController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:followups.view')->only(['index', 'show', 'calendar']);
        $this->middleware('permission:followups.create')->only(['create', 'store']);
        $this->middleware('permission:followups.edit')->only(['edit', 'update', 'reschedule']);
        $this->middleware('permission:followups.delete')->only('destroy');
        $this->middleware('permission:followups.complete')->only(['complete', 'markComplete']);
    }

    /**
     * Display follow-up dashboard
     */
    public function index(Request $request)
    {
        $query = FollowUp::with(['lead', 'customer', 'assignedTo', 'template']);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->overdue();
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('follow_up_type', $request->type);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->date_to);
        }

        // Filter by assigned user
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        } elseif (!Auth::user()->hasRole(['super_admin', 'admin'])) {
            // Non-admin users see only their follow-ups
            $query->where('assigned_to', Auth::user()->staff_id);
        }

        // Get statistics
        $stats = [
            'total' => (clone $query)->count(),
            'overdue' => (clone $query)->overdue()->count(),
            'today' => (clone $query)->whereDate('scheduled_date', today())->where('status', 'scheduled')->count(),
            'upcoming' => (clone $query)->upcoming(7)->count(),
            'completed' => FollowUp::where('status', 'completed')
                ->when($request->filled('assigned_to'), function ($q) use ($request) {
                    $q->where('assigned_to', $request->assigned_to);
                })
                ->whereMonth('completed_date', now()->month)
                ->count()
        ];

        $followUps = $query->orderBy('scheduled_date', 'asc')->paginate(20);
        $staff = Staff::where('status', 'active')->orderBy('name')->get();

        return view('followups.index', compact('followUps', 'stats', 'staff'));
    }

    /**
     * Show calendar view
     */
    public function calendar(Request $request)
    {
        $query = FollowUp::with(['lead', 'customer', 'assignedTo']);

        if (!Auth::user()->hasRole(['super_admin', 'admin'])) {
            $query->where('assigned_to', Auth::user()->staff_id);
        }

        $followUps = $query->whereIn('status', ['scheduled', 'overdue'])->get();

        // Format for calendar
        $events = $followUps->map(function ($followUp) {
            return [
                'id' => $followUp->id,
                'title' => $followUp->subject,
                'start' => $followUp->scheduled_date->toIso8601String(),
                'end' => $followUp->scheduled_date->addHour()->toIso8601String(),
                'color' => $this->getPriorityColor($followUp->priority),
                'extendedProps' => [
					'followup_id' => $followUp->id,  // Add this for the view button
					'type' => $followUp->follow_up_type,
					'entity' => $followUp->entity_name,
					'entity_type' => $followUp->lead_id ? 'Lead' : 'Customer',
					'status' => $followUp->status,
					'priority' => $followUp->priority,
					'assigned_to' => $followUp->assignedTo ? $followUp->assignedTo->name : null
				]
            ];
        });

        return view('followups.calendar', compact('events'));
    }

    /**
     * Create new follow-up
     */
    public function create(Request $request)
    {
        $templates = FollowUpTemplate::active()->orderBy('category')->orderBy('name')->get();
        $staff = Staff::where('status', 'active')->orderBy('name')->get();
        
        $lead = null;
        $customer = null;
        
        if ($request->has('lead_id')) {
            $lead = Lead::findOrFail($request->lead_id);
        } elseif ($request->has('customer_id')) {
            $customer = Customer::findOrFail($request->customer_id);
        }

        return view('followups.create', compact('templates', 'staff', 'lead', 'customer'));
    }

    /**
     * Store new follow-up
     */
    public function store(Request $request)
    {
       
        $validated = $request->validate([
            'entity_type' => 'required|in:lead,customer',
            'entity_id' => 'required|integer',
            'follow_up_type' => 'required|in:phone_call,email,whatsapp,in_person_meeting,video_call,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'scheduled_date' => 'required|date|after:now',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:staff,id',
            'template_id' => 'nullable|exists:follow_up_templates,id',
            'is_recurring' => 'boolean',
            'recurring_pattern' => 'required_if:is_recurring,1|in:daily,weekly,monthly,custom',
            'recurring_interval' => 'required_if:is_recurring,1|integer|min:1',
            'recurring_end_date' => 'nullable|required_if:is_recurring,1|date|after:scheduled_date'
        ]);

        // Validate entity exists
        if ($validated['entity_type'] === 'lead') {
           
            Lead::findOrFail($validated['entity_id']);
            $validated['lead_id'] = $validated['entity_id'];
            $validated['customer_id'] = null;
             
        } else {
            Customer::findOrFail($validated['entity_id']);
            $validated['customer_id'] = $validated['entity_id'];
            $validated['lead_id'] = null;
        }

        unset($validated['entity_type'], $validated['entity_id']);

        DB::beginTransaction();
        try {
            $validated['created_by'] = Auth::id();
            $validated['status'] = 'scheduled';
            
            // Apply template if selected
            if (!empty($validated['template_id'])) {
                $template = FollowUpTemplate::find($validated['template_id']);
                if ($template) {
                    $processedTemplate = $template->processTemplate([
                        'contact_person' => $validated['lead_id'] 
                            ? Lead::find($validated['lead_id'])->contact_person 
                            : Customer::find($validated['customer_id'])->contact_person,
                        'staff_name' => Auth::user()->name
                    ]);
                    
                    if (empty($validated['description'])) {
                        $validated['description'] = $processedTemplate['content'];
                    }
                }
            }

            $followUp = FollowUp::create($validated);

            // Update lead/customer next follow-up date
            if ($followUp->lead_id) {
                $followUp->lead->update([
                    'next_followup_date' => $followUp->scheduled_date->toDateString()
                ]);
            }

            DB::commit();
            return redirect()->route('followups.show', $followUp)
                ->with('success', 'Follow-up scheduled successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Error creating follow-up: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display follow-up details
     */
    public function show(FollowUp $followup)
    {
        $followup->load([
            'lead.templeCategory',
            'customer',
            'assignedTo',
            'createdBy',
            'template',
            'communicationHistory.recordedBy',
            'reminderLogs'
        ]);

        return view('followups.show', compact('followup'));
    }

    /**
     * Edit follow-up
     */
    public function edit(FollowUp $followup)
    {
        if ($followup->status === 'completed') {
            return redirect()->route('followups.show', $followup)
                ->with('error', 'Completed follow-ups cannot be edited.');
        }

        $templates = FollowUpTemplate::active()->orderBy('category')->orderBy('name')->get();
        $staff = Staff::where('status', 'active')->orderBy('name')->get();

        return view('followups.edit', compact('followup', 'templates', 'staff'));
    }

    /**
     * Update follow-up
     */
    public function update(Request $request, FollowUp $followup)
    {
        if ($followup->status === 'completed') {
            return redirect()->route('followups.show', $followup)
                ->with('error', 'Completed follow-ups cannot be edited.');
        }

        $validated = $request->validate([
            'follow_up_type' => 'required|in:phone_call,email,whatsapp,in_person_meeting,video_call,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'scheduled_date' => 'required|date|after:now',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:staff,id',
            'is_recurring' => 'boolean',
            'recurring_pattern' => 'required_if:is_recurring,1|in:daily,weekly,monthly,custom',
            'recurring_interval' => 'required_if:is_recurring,1|integer|min:1',
            'recurring_end_date' => 'nullable|required_if:is_recurring,1|date|after:scheduled_date'
        ]);

        $followup->update($validated);

        return redirect()->route('followups.show', $followup)
            ->with('success', 'Follow-up updated successfully.');
    }

    /**
     * Complete follow-up with outcome
     */
    public function complete(FollowUp $followup)
    {
        if ($followup->status === 'completed') {
            return redirect()->route('followups.show', $followup)
                ->with('error', 'This follow-up is already completed.');
        }

        return view('followups.complete', compact('followup'));
    }

    /**
     * Mark follow-up as complete
     */
    public function markComplete(Request $request, FollowUp $followup)
    {
        $validated = $request->validate([
            'outcome' => 'required|in:interested,not_interested,callback_later,no_response,meeting_scheduled,demo_scheduled,quotation_requested,other',
            'notes' => 'nullable|string',
            'communication_type' => 'required|in:phone_call,email,whatsapp,sms,meeting,other',
            'duration_minutes' => 'nullable|integer|min:1',
            'next_follow_up' => 'boolean',
            'next_follow_up_date' => 'required_if:next_follow_up,1|date|after:now',
            'next_follow_up_subject' => 'required_if:next_follow_up,1|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            // Update follow-up
            $followup->update([
                'status' => 'completed',
                'completed_date' => now(),
                'outcome' => $validated['outcome'],
                'notes' => $validated['notes']
            ]);

            // Create communication history
            CommunicationHistory::create([
                'lead_id' => $followup->lead_id,
                'customer_id' => $followup->customer_id,
                'follow_up_id' => $followup->id,
                'communication_type' => $validated['communication_type'],
                'direction' => 'outgoing',
                'contact_person' => $followup->entity->contact_person ?? null,
                'duration_minutes' => $validated['duration_minutes'] ?? null,
                'subject' => $followup->subject,
                'content' => $validated['notes'],
                'outcome' => $validated['outcome'],
                'recorded_by' => Auth::id(),
                'communication_date' => now()
            ]);

            // Update lead/customer last follow-up date
            if ($followup->lead_id) {
                $followup->lead->update(['last_follow_up_date' => now()]);
            }

            // Create next follow-up if requested
            if ($request->next_follow_up) {
                FollowUp::create([
                    'lead_id' => $followup->lead_id,
                    'customer_id' => $followup->customer_id,
                    'follow_up_type' => $followup->follow_up_type,
                    'priority' => 'medium',
                    'scheduled_date' => $validated['next_follow_up_date'],
                    'subject' => $validated['next_follow_up_subject'],
                    'assigned_to' => $followup->assigned_to,
                    'created_by' => Auth::id()
                ]);
            }

            DB::commit();
            return redirect()->route('followups.index')
                ->with('success', 'Follow-up completed successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Error completing follow-up: ' . $e->getMessage());
        }
    }

    /**
     * Reschedule follow-up
     */
    public function reschedule(Request $request, FollowUp $followup)
    {
        if ($followup->status === 'completed') {
            return redirect()->back()
                ->with('error', 'Completed follow-ups cannot be rescheduled.');
        }

        $validated = $request->validate([
            'scheduled_date' => 'required|date|after:now',
            'reason' => 'nullable|string|max:255'
        ]);

        $oldDate = $followup->scheduled_date;

        $followup->update([
            'scheduled_date' => $validated['scheduled_date'],
            'status' => 'rescheduled',
            'notes' => ($followup->notes ? $followup->notes . "\n\n" : '') . 
                      "Rescheduled from {$oldDate->format('d/m/Y H:i')} to {$validated['scheduled_date']}" .
                      ($validated['reason'] ? "\nReason: {$validated['reason']}" : '')
        ]);

        // Reset status back to scheduled
        $followup->update(['status' => 'scheduled']);

        return redirect()->back()
            ->with('success', 'Follow-up rescheduled successfully.');
    }

    /**
     * Delete follow-up
     */
    public function destroy(FollowUp $followup)
    {
        if ($followup->status === 'completed') {
            return redirect()->route('followups.index')
                ->with('error', 'Completed follow-ups cannot be deleted.');
        }

        $followup->delete();
        return redirect()->route('followups.index')
            ->with('success', 'Follow-up deleted successfully.');
    }

    /**
     * Get priority color for calendar
     */
    private function getPriorityColor($priority)
    {
        return [
            'low' => '#6c757d',
            'medium' => '#007bff',
            'high' => '#ffc107',
            'urgent' => '#dc3545'
        ][$priority] ?? '#6c757d';
    }
}