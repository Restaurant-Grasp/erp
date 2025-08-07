@extends('layouts.app')

@section('title', 'Follow-up Details')

@section('content')
<div class="page-header">
    <h1 class="page-title">Follow-up Details</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('followups.index') }}">Follow-ups</a></li>
            <li class="breadcrumb-item active">Details</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Follow-up Information -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Follow-up Information</h5>
                <div>
                    <span class="badge bg-{{ $followup->priority_color }} me-2">
                        {{ ucfirst($followup->priority) }} Priority
                    </span>
                    @if($followup->status === 'completed')
                        <span class="badge bg-success">Completed</span>
                    @elseif($followup->status === 'overdue' || ($followup->status === 'scheduled' && $followup->scheduled_date->isPast()))
                        <span class="badge bg-danger">Overdue</span>
                    @elseif($followup->scheduled_date->isToday())
                        <span class="badge bg-warning">Today</span>
                    @else
                        <span class="badge bg-info">{{ ucfirst($followup->status) }}</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Type:</th>
                                <td>
                                    <i class="fas {{ $followup->type_icon }} me-1"></i>
                                    {{ ucwords(str_replace('_', ' ', $followup->follow_up_type)) }}
                                </td>
                            </tr>
                            <tr>
                                <th>Subject:</th>
                                <td><strong>{{ $followup->subject }}</strong></td>
                            </tr>
                            <tr>
                                <th>Entity:</th>
                                <td>
                                    @if($followup->lead)
                                        <span class="badge bg-primary">Lead</span>
                                        <a href="{{ route('leads.show', $followup->lead) }}">
                                            {{ $followup->lead->lead_no }} - {{ $followup->entity_name }}
                                        </a>
                                    @elseif($followup->customer)
                                        <span class="badge bg-success">Customer</span>
                                        <a href="{{ route('customers.show', $followup->customer) }}">
                                            {{ $followup->customer->customer_code }} - {{ $followup->entity_name }}
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Assigned To:</th>
                                <td>{{ $followup->assignedTo->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Created By:</th>
                                <td>{{ $followup->createdBy->name ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Scheduled:</th>
                                <td>
                                    {{ $followup->scheduled_date->format('d/m/Y') }}
                                    at {{ $followup->scheduled_date->format('h:i A') }}
                                    @if(!$followup->completed_date)
                                    <br><small class="text-muted">({{ $followup->scheduled_date->diffForHumans() }})</small>
                                    @endif
                                </td>
                            </tr>
                            @if($followup->completed_date)
                            <tr>
                                <th>Completed:</th>
                                <td>
                                    {{ $followup->completed_date->format('d/m/Y h:i A') }}
                                </td>
                            </tr>
                            <tr>
                                <th>Outcome:</th>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ ucwords(str_replace('_', ' ', $followup->outcome ?? 'N/A')) }}
                                    </span>
                                </td>
                            </tr>
                            @endif
                            @if($followup->is_recurring)
                            <tr>
                                <th>Recurring:</th>
                                <td>
                                    <i class="fas fa-redo text-info me-1"></i>
                                    {{ ucfirst($followup->recurring_pattern) }}
                                    (Every {{ $followup->recurring_interval }} 
                                    {{ $followup->recurring_pattern === 'custom' ? 'days' : rtrim($followup->recurring_pattern, 'ly') . '(s)' }})
                                    @if($followup->recurring_end_date)
                                    <br><small>Until: {{ $followup->recurring_end_date->format('d/m/Y') }}</small>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <th>Created:</th>
                                <td>{{ $followup->created_at->format('d/m/Y h:i A') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($followup->description)
                <div class="mt-3">
                    <h6 class="fw-bold">Description/Notes</h6>
                    <div class="bg-light p-3 rounded">
                        {!! nl2br(e($followup->description)) !!}
                    </div>
                </div>
                @endif

                @if($followup->notes && $followup->notes !== $followup->description)
                <div class="mt-3">
                    <h6 class="fw-bold">Completion Notes</h6>
                    <div class="bg-light p-3 rounded">
                        {!! nl2br(e($followup->notes)) !!}
                    </div>
                </div>
                @endif

                @if($followup->template)
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-file-alt me-1"></i>
                        Template used: {{ $followup->template->name }}
                    </small>
                </div>
                @endif
            </div>
        </div>

        <!-- Communication History -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Communication History</h5>
                @if($followup->status !== 'completed')
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#logCommunicationModal">
                    <i class="fas fa-plus me-1"></i> Log Communication
                </button>
                @endif
            </div>
            <div class="card-body">
                @if($followup->communicationHistory->count() > 0)
                <div class="timeline">
                    @foreach($followup->communicationHistory->sortByDesc('communication_date') as $comm)
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">
                                <i class="fas {{ $comm->type_icon }} me-1"></i>
                                {{ ucwords(str_replace('_', ' ', $comm->communication_type)) }}
                                @if($comm->direction)
                                <span class="badge bg-{{ $comm->direction_color }} ms-2">
                                    <i class="fas {{ $comm->direction_icon }}"></i> {{ ucfirst($comm->direction) }}
                                </span>
                                @endif
                            </h6>
                            @if($comm->subject)
                            <p class="mb-1"><strong>{{ $comm->subject }}</strong></p>
                            @endif
                            @if($comm->content)
                            <p class="mb-1">{{ $comm->content }}</p>
                            @endif
                            @if($comm->duration_minutes)
                            <p class="mb-1"><small>Duration: {{ $comm->duration_minutes }} minutes</small></p>
                            @endif
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i> {{ $comm->communication_date->format('d/m/Y h:i A') }}
                                <i class="fas fa-user ms-2 me-1"></i> {{ $comm->recordedBy->name ?? 'System' }}
                            </small>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted text-center">No communication history recorded yet.</p>
                @endif
            </div>
        </div>

        <!-- Reminder Logs -->
        @if($followup->reminderLogs->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Reminder History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Sent To</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($followup->reminderLogs as $log)
                            <tr>
                                <td>{{ ucfirst($log->reminder_type) }}</td>
                                <td>{{ $log->sent_to }}</td>
                                <td>{{ $log->sent_date->format('d/m/Y h:i A') }}</td>
                                <td>
                                    @if($log->status === 'sent')
                                        <span class="badge bg-success">Sent</span>
                                    @else
                                        <span class="badge bg-danger">Failed</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($followup->status !== 'completed')
                        @can('followups.complete')
                        <a href="{{ route('followups.complete', $followup) }}" class="btn btn-success">
                            <i class="fas fa-check me-2"></i> Complete Follow-up
                        </a>
                        @endcan
                        
                        @can('followups.edit')
                        <a href="{{ route('followups.edit', $followup) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i> Edit Follow-up
                        </a>
                        
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
                            <i class="fas fa-clock me-2"></i> Reschedule
                        </button>
                        @endcan
                    @endif
                    
                    <a href="{{ route('followups.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Follow-ups
                    </a>
                </div>
            </div>
        </div>

        <!-- Entity Information -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">{{ $followup->entity_type === 'lead' ? 'Lead' : 'Customer' }} Information</h5>
            </div>
            <div class="card-body">
                @if($followup->lead)
                <table class="table table-sm">
                    <tr>
                        <th>Lead No:</th>
                        <td>{{ $followup->lead->lead_no }}</td>
                    </tr>
                    <tr>
                        <th>Temple:</th>
                        <td>{{ $followup->lead->company_name ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Contact:</th>
                        <td>{{ $followup->lead->contact_person }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $followup->lead->email ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td>{{ $followup->lead->mobile ?: $followup->lead->phone ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge bg-{{ $followup->lead->status_badge }}">
                                {{ ucfirst($followup->lead->lead_status) }}
                            </span>
                        </td>
                    </tr>
                </table>
                @elseif($followup->customer)
                <table class="table table-sm">
                    <tr>
                        <th>Code:</th>
                        <td>{{ $followup->customer->customer_code }}</td>
                    </tr>
                    <tr>
                        <th>Temple:</th>
                        <td>{{ $followup->customer->company_name }}</td>
                    </tr>
                    <tr>
                        <th>Contact:</th>
                        <td>{{ $followup->customer->contact_person ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $followup->customer->email ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td>{{ $followup->customer->mobile ?: $followup->customer->phone ?: '-' }}</td>
                    </tr>
                </table>
                @endif
            </div>
        </div>

        <!-- Related Follow-ups -->
        @if($followup->is_recurring && ($followup->parentFollowUp || $followup->childFollowUps->count() > 0))
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Related Follow-ups</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @if($followup->parentFollowUp)
                    <a href="{{ route('followups.show', $followup->parentFollowUp) }}" 
                       class="list-group-item list-group-item-action">
                        <small class="text-muted">Parent:</small><br>
                        {{ $followup->parentFollowUp->subject }}<br>
                        <small>{{ $followup->parentFollowUp->scheduled_date->format('d/m/Y') }}</small>
                    </a>
                    @endif
                    
                    @foreach($followup->childFollowUps as $child)
                    <a href="{{ route('followups.show', $child) }}" 
                       class="list-group-item list-group-item-action">
                        {{ $child->subject }}<br>
                        <small>{{ $child->scheduled_date->format('d/m/Y') }} - 
                        <span class="badge bg-{{ $child->status === 'completed' ? 'success' : 'info' }}">
                            {{ ucfirst($child->status) }}
                        </span></small>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Log Communication Modal -->
<div class="modal fade" id="logCommunicationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('communication-history.store') }}" method="POST">
                @csrf
                <input type="hidden" name="follow_up_id" value="{{ $followup->id }}">
                <input type="hidden" name="lead_id" value="{{ $followup->lead_id }}">
                <input type="hidden" name="customer_id" value="{{ $followup->customer_id }}">
                
                <div class="modal-header">
                    <h5 class="modal-title">Log Communication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="communication_type" class="form-label">Type</label>
                        <select class="form-select" name="communication_type" required>
                            <option value="phone_call">Phone Call</option>
                            <option value="email">Email</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="sms">SMS</option>
                            <option value="meeting">Meeting</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="direction" class="form-label">Direction</label>
                        <select class="form-select" name="direction" required>
                            <option value="outgoing">Outgoing</option>
                            <option value="incoming">Incoming</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="duration_minutes" class="form-label">Duration (minutes)</label>
                        <input type="number" class="form-control" name="duration_minutes" min="1">
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Notes</label>
                        <textarea class="form-control" name="content" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Communication</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reschedule Modal -->
@if($followup->status !== 'completed')
<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('followups.reschedule', $followup) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Reschedule Follow-up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="scheduled_date" class="form-label">New Date & Time</label>
                        <input type="datetime-local" class="form-control" name="scheduled_date" required
                               value="{{ $followup->scheduled_date->format('Y-m-d\TH:i') }}"
                               min="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason (Optional)</label>
                        <input type="text" class="form-control" name="reason" placeholder="Reason for rescheduling">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Reschedule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline-item {
    position: relative;
    padding-bottom: 20px;
}
.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -21px;
    top: 20px;
    height: calc(100% - 20px);
    width: 2px;
    background-color: #e9ecef;
}
.timeline-marker {
    position: absolute;
    left: -25px;
    top: 5px;
    width: 10px;
    height: 10px;
    background-color: #007bff;
    border-radius: 50%;
}
</style>
@endsection

