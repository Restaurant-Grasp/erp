@extends('layouts.app')

@section('title', 'Complete Follow-up')

@section('content')
<div class="page-header">
    <h1 class="page-title">Complete Follow-up</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('followups.index') }}">Follow-ups</a></li>
            <li class="breadcrumb-item"><a href="{{ route('followups.show', $followup) }}">Details</a></li>
            <li class="breadcrumb-item active">Complete</li>
        </ol>
    </nav>
</div>

<form action="{{ route('followups.mark-complete', $followup) }}" method="POST">
    @csrf
    
    <div class="row">
        <div class="col-md-8">
            <!-- Follow-up Summary -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Follow-up Summary</h5>
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
                                    <th>Priority:</th>
                                    <td>
                                        <span class="badge bg-{{ $followup->priority_color }}">
                                            {{ ucfirst($followup->priority) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Entity:</th>
                                    <td>
                                        @if($followup->lead)
                                            <span class="badge bg-primary">Lead</span>
                                            {{ $followup->entity_name }}
                                        @elseif($followup->customer)
                                            <span class="badge bg-success">Customer</span>
                                            {{ $followup->entity_name }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Scheduled:</th>
                                    <td>{{ $followup->scheduled_date->format('d/m/Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Assigned To:</th>
                                    <td>{{ $followup->assignedTo->name ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($followup->description)
                    <div class="mt-3">
                        <h6 class="fw-bold">Original Notes:</h6>
                        <div class="bg-light p-3 rounded">
                            {!! nl2br(e($followup->description)) !!}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Completion Details -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Completion Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="communication_type" class="form-label">Communication Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('communication_type') is-invalid @enderror" 
                                    id="communication_type" name="communication_type" required>
                                <option value="">Select Type</option>
                                <option value="phone_call" {{ old('communication_type', $followup->follow_up_type == 'phone_call' ? 'phone_call' : '') == 'phone_call' ? 'selected' : '' }}>Phone Call</option>
                                <option value="email" {{ old('communication_type', $followup->follow_up_type == 'email' ? 'email' : '') == 'email' ? 'selected' : '' }}>Email</option>
                                <option value="whatsapp" {{ old('communication_type', $followup->follow_up_type == 'whatsapp' ? 'whatsapp' : '') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                <option value="sms" {{ old('communication_type') == 'sms' ? 'selected' : '' }}>SMS</option>
                                <option value="meeting" {{ old('communication_type', $followup->follow_up_type == 'in_person_meeting' ? 'meeting' : '') == 'meeting' ? 'selected' : '' }}>Meeting</option>
                                <option value="other" {{ old('communication_type') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('communication_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="outcome" class="form-label">Outcome <span class="text-danger">*</span></label>
                            <select class="form-select @error('outcome') is-invalid @enderror" 
                                    id="outcome" name="outcome" required>
                                <option value="">Select Outcome</option>
                                <option value="interested" {{ old('outcome') == 'interested' ? 'selected' : '' }}>Interested</option>
                                <option value="not_interested" {{ old('outcome') == 'not_interested' ? 'selected' : '' }}>Not Interested</option>
                                <option value="callback_later" {{ old('outcome') == 'callback_later' ? 'selected' : '' }}>Callback Later</option>
                                <option value="no_response" {{ old('outcome') == 'no_response' ? 'selected' : '' }}>No Response</option>
                                <option value="meeting_scheduled" {{ old('outcome') == 'meeting_scheduled' ? 'selected' : '' }}>Meeting Scheduled</option>
                                <option value="demo_scheduled" {{ old('outcome') == 'demo_scheduled' ? 'selected' : '' }}>Demo Scheduled</option>
                                <option value="quotation_requested" {{ old('outcome') == 'quotation_requested' ? 'selected' : '' }}>Quotation Requested</option>
                                <option value="other" {{ old('outcome') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('outcome')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6" id="durationField" style="display: none;">
                            <label for="duration_minutes" class="form-label">Duration (minutes)</label>
                            <input type="number" class="form-control @error('duration_minutes') is-invalid @enderror" 
                                   id="duration_minutes" name="duration_minutes" min="1"
                                   value="{{ old('duration_minutes') }}" placeholder="Call/Meeting duration">
                            @error('duration_minutes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12">
                            <label for="notes" class="form-label">Notes/Summary</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="4"
                                      placeholder="Summary of the conversation, key points discussed, next steps...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next Follow-up -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Schedule Next Follow-up</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="next_follow_up" 
                               name="next_follow_up" value="1" {{ old('next_follow_up') ? 'checked' : '' }}>
                        <label class="form-check-label" for="next_follow_up">
                            Create a new follow-up
                        </label>
                    </div>
                    
                    <div id="nextFollowUpFields" style="display: none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="next_follow_up_date" class="form-label">Next Follow-up Date</label>
                                <input type="datetime-local" class="form-control @error('next_follow_up_date') is-invalid @enderror" 
                                       id="next_follow_up_date" name="next_follow_up_date"
                                       value="{{ old('next_follow_up_date') }}"
                                       min="{{ now()->format('Y-m-d\TH:i') }}">
                                @error('next_follow_up_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="next_follow_up_subject" class="form-label">Subject</label>
                                <input type="text" class="form-control @error('next_follow_up_subject') is-invalid @enderror" 
                                       id="next_follow_up_subject" name="next_follow_up_subject"
                                       value="{{ old('next_follow_up_subject') }}"
                                       placeholder="Subject for next follow-up">
                                @error('next_follow_up_subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-check me-2"></i> Complete Follow-up
                        </button>
                        <a href="{{ route('followups.show', $followup) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tips -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Completion Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">Select the appropriate outcome based on the conversation</li>
                        <li class="mb-2">Provide detailed notes for future reference</li>
                        <li class="mb-2">Schedule immediate next follow-up if needed</li>
                        <li class="mb-2">Update lead status if significant progress was made</li>
                    </ul>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions After Completion</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($followup->lead)
                        
                        <a href="{{ route('leads.edit', $followup->lead) }}" 
                           class="btn btn-sm btn-outline-info">
                            <i class="fas fa-edit me-1"></i> Update Lead Status
                        </a>
                      
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<script>
$(document).ready(function() {
    // Show/hide duration field based on communication type
    $('#communication_type').on('change', function() {
        const type = $(this).val();
        if (type === 'phone_call' || type === 'meeting') {
            $('#durationField').show();
            $('#duration_minutes').prop('required', true);
        } else {
            $('#durationField').hide();
            $('#duration_minutes').prop('required', false);
        }
    });

    // Trigger initial check
    $('#communication_type').trigger('change');

    // Show/hide next follow-up fields
    $('#next_follow_up').on('change', function() {
        if ($(this).is(':checked')) {
            $('#nextFollowUpFields').show();
            $('#next_follow_up_date').prop('required', true);
            $('#next_follow_up_subject').prop('required', true);
        } else {
            $('#nextFollowUpFields').hide();
            $('#next_follow_up_date').prop('required', false);
            $('#next_follow_up_subject').prop('required', false);
        }
    });

    // Trigger initial check
    if ($('#next_follow_up').is(':checked')) {
        $('#nextFollowUpFields').show();
    }

    // Handle outcome-based suggestions
    $('#outcome').on('change', function() {
        const outcome = $(this).val();
        
        // Auto-check next follow-up for certain outcomes
        if (['callback_later', 'interested', 'meeting_scheduled', 'demo_scheduled'].includes(outcome)) {
            $('#next_follow_up').prop('checked', true).trigger('change');
            
            // Set default follow-up dates based on outcome
            const nextDate = new Date();
            switch(outcome) {
                case 'callback_later':
                    nextDate.setDate(nextDate.getDate() + 3);
                    $('#next_follow_up_subject').val('Follow-up call as requested');
                    break;
                case 'meeting_scheduled':
                    nextDate.setDate(nextDate.getDate() + 1);
                    $('#next_follow_up_subject').val('Meeting reminder');
                    break;
                case 'demo_scheduled':
                    nextDate.setDate(nextDate.getDate() + 1);
                    $('#next_follow_up_subject').val('Demo reminder');
                    break;
                case 'interested':
                    nextDate.setDate(nextDate.getDate() + 7);
                    $('#next_follow_up_subject').val('Follow-up on interest');
                    break;
            }
            
            // Format date for datetime-local input
            nextDate.setMinutes(nextDate.getMinutes() - nextDate.getTimezoneOffset());
            $('#next_follow_up_date').val(nextDate.toISOString().slice(0, 16));
        }
    });
});
</script>
@endsection

