@extends('layouts.app')

@section('title', 'Edit Follow-up')

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Follow-up</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('followups.index') }}">Follow-ups</a></li>
            <li class="breadcrumb-item"><a href="{{ route('followups.show', $followup) }}">{{ $followup->subject }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Follow-up Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('followups.update', $followup) }}" method="POST" id="followupForm">
                    @csrf
                    @method('PUT')

                    <!-- Entity Information (Read-only) -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Entity Type</label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-{{ $followup->entity_type === 'lead' ? 'primary' : 'success' }}">
                                    {{ ucfirst($followup->entity_type) }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Entity</label>
                            <div class="form-control-plaintext">
                                @if($followup->lead)
                                    <a href="{{ route('leads.show', $followup->lead) }}">
                                        {{ $followup->lead->lead_no }} - {{ $followup->entity_name }}
                                    </a>
                                @elseif($followup->customer)
                                    <a href="{{ route('customers.show', $followup->customer) }}">
                                        {{ $followup->customer->customer_code }} - {{ $followup->entity_name }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Follow-up Type and Priority -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="follow_up_type" class="form-label">Follow-up Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('follow_up_type') is-invalid @enderror" 
                                    name="follow_up_type" id="follow_up_type" required>
                                <option value="">Select Type</option>
                                <option value="phone_call" {{ old('follow_up_type', $followup->follow_up_type) == 'phone_call' ? 'selected' : '' }}>
                                    Phone Call
                                </option>
                                <option value="email" {{ old('follow_up_type', $followup->follow_up_type) == 'email' ? 'selected' : '' }}>
                                    Email
                                </option>
                                <option value="whatsapp" {{ old('follow_up_type', $followup->follow_up_type) == 'whatsapp' ? 'selected' : '' }}>
                                    WhatsApp
                                </option>
                                <option value="in_person_meeting" {{ old('follow_up_type', $followup->follow_up_type) == 'in_person_meeting' ? 'selected' : '' }}>
                                    In-Person Meeting
                                </option>
                                <option value="video_call" {{ old('follow_up_type', $followup->follow_up_type) == 'video_call' ? 'selected' : '' }}>
                                    Video Call
                                </option>
                                <option value="other" {{ old('follow_up_type', $followup->follow_up_type) == 'other' ? 'selected' : '' }}>
                                    Other
                                </option>
                            </select>
                            @error('follow_up_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                            <select class="form-select @error('priority') is-invalid @enderror" 
                                    name="priority" id="priority" required>
                                <option value="">Select Priority</option>
                                <option value="low" {{ old('priority', $followup->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority', $followup->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority', $followup->priority) == 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ old('priority', $followup->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Scheduled Date and Assigned To -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="scheduled_date" class="form-label">Scheduled Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" 
                                   class="form-control @error('scheduled_date') is-invalid @enderror" 
                                   name="scheduled_date" 
                                   id="scheduled_date" 
                                   value="{{ old('scheduled_date', $followup->scheduled_date->format('Y-m-d\TH:i')) }}"
                                   min="{{ now()->format('Y-m-d\TH:i') }}" 
                                   required>
                            @error('scheduled_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($followup->scheduled_date->isPast())
                                <small class="text-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Current date is in the past. Please update to a future date.
                                </small>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <label for="assigned_to" class="form-label">Assigned To <span class="text-danger">*</span></label>
                            <select class="form-select @error('assigned_to') is-invalid @enderror" 
                                    name="assigned_to" id="assigned_to" required>
                                <option value="">Select Staff Member</option>
                                @foreach($staff as $staffMember)
                                    <option value="{{ $staffMember->id }}" 
                                            {{ old('assigned_to', $followup->assigned_to) == $staffMember->id ? 'selected' : '' }}>
                                        {{ $staffMember->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Subject -->
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('subject') is-invalid @enderror" 
                               name="subject" 
                               id="subject" 
                               value="{{ old('subject', $followup->subject) }}" 
                               maxlength="255" 
                               required>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Template Selection -->
                    <div class="mb-3">
                        <label for="template_id" class="form-label">Replace with Template (Optional)</label>
                        <select class="form-select" name="template_id" id="template_id">
                            <option value="">Select Template</option>
                            @foreach($templates->groupBy('category') as $category => $categoryTemplates)
                                <optgroup label="{{ ucfirst($category) }}">
                                    @foreach($categoryTemplates as $template)
                                        <option value="{{ $template->id }}" 
                                                data-content="{{ $template->content }}"
                                                data-subject="{{ $template->subject }}">
                                            {{ $template->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            Selecting a template will replace the current description when applied.
                        </small>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description/Notes</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" 
                                  id="description" 
                                  rows="4">{{ old('description', $followup->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Recurring Options -->
                    <div class="card border">
                        <div class="card-header">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="is_recurring" id="is_recurring" value="1"
                                       {{ old('is_recurring', $followup->is_recurring) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_recurring">
                                    <i class="fas fa-redo me-1"></i> Make this a recurring follow-up
                                </label>
                            </div>
                        </div>
                        <div class="card-body" id="recurring_options" style="{{ old('is_recurring', $followup->is_recurring) ? '' : 'display: none;' }}">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="recurring_pattern" class="form-label">Pattern</label>
                                    <select class="form-select @error('recurring_pattern') is-invalid @enderror" 
                                            name="recurring_pattern" id="recurring_pattern">
                                        <option value="daily" {{ old('recurring_pattern', $followup->recurring_pattern) == 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="weekly" {{ old('recurring_pattern', $followup->recurring_pattern) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="monthly" {{ old('recurring_pattern', $followup->recurring_pattern) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="custom" {{ old('recurring_pattern', $followup->recurring_pattern) == 'custom' ? 'selected' : '' }}>Custom (Days)</option>
                                    </select>
                                    @error('recurring_pattern')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="recurring_interval" class="form-label">Interval</label>
                                    <input type="number" 
                                           class="form-control @error('recurring_interval') is-invalid @enderror" 
                                           name="recurring_interval" 
                                           id="recurring_interval" 
                                           value="{{ old('recurring_interval', $followup->recurring_interval ?? 1) }}" 
                                           min="1">
                                    <small class="form-text text-muted">How often to repeat (e.g., every 2 weeks)</small>
                                    @error('recurring_interval')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mt-3">
                                <label for="recurring_end_date" class="form-label">End Date (Optional)</label>
                                <input type="date" 
                                       class="form-control @error('recurring_end_date') is-invalid @enderror" 
                                       name="recurring_end_date" 
                                       id="recurring_end_date" 
                                       value="{{ old('recurring_end_date', $followup->recurring_end_date ? $followup->recurring_end_date->format('Y-m-d') : '') }}">
                                @error('recurring_end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('followups.show', $followup) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Follow-up
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Current Status -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Current Status</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Status:</span>
                    @if($followup->status === 'overdue' || ($followup->status === 'scheduled' && $followup->scheduled_date->isPast()))
                        <span class="badge bg-danger">Overdue</span>
                    @elseif($followup->scheduled_date->isToday())
                        <span class="badge bg-warning">Today</span>
                    @else
                        <span class="badge bg-info">{{ ucfirst($followup->status) }}</span>
                    @endif
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Priority:</span>
                    <span class="badge bg-{{ $followup->priority_color }}">
                        {{ ucfirst($followup->priority) }}
                    </span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Original Date:</span>
                    <span>{{ $followup->scheduled_date->format('d/m/Y H:i') }}</span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <span>Created:</span>
                    <span>{{ $followup->created_at->format('d/m/Y') }}</span>
                </div>

                @if($followup->is_recurring)
                <hr>
                <div class="text-center">
                    <i class="fas fa-redo text-info me-1"></i>
                    <small class="text-muted">
                        This is a recurring follow-up
                        <br>{{ ucfirst($followup->recurring_pattern) }} (Every {{ $followup->recurring_interval }} 
                        {{ $followup->recurring_pattern === 'custom' ? 'days' : rtrim($followup->recurring_pattern, 'ly') . '(s)' }})
                    </small>
                </div>
                @endif
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

        <!-- Template Preview -->
        <div class="card mt-4" id="template_preview" style="display: none;">
            <div class="card-header">
                <h5 class="mb-0">Template Preview</h5>
            </div>
            <div class="card-body">
                <div id="template_subject" class="mb-2"></div>
                <div id="template_content"></div>
                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="apply_template">
                    Apply Template
                </button>
            </div>
        </div>

        <!-- Help -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-1"></i> Edit Tips
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-clock text-warning me-2"></i>
                        Update the date if the follow-up is overdue
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-user text-info me-2"></i>
                        You can reassign the follow-up to another team member
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-file-alt text-success me-2"></i>
                        Use templates to replace content with standard messages
                    </li>
                    <li>
                        <i class="fas fa-flag text-danger me-2"></i>
                        Adjust priority based on urgency changes
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Template handling
    const templateSelect = document.getElementById('template_id');
    const templatePreview = document.getElementById('template_preview');
    const templateSubject = document.getElementById('template_subject');
    const templateContent = document.getElementById('template_content');
    const applyTemplateBtn = document.getElementById('apply_template');
    const subjectInput = document.getElementById('subject');
    const descriptionTextarea = document.getElementById('description');

    templateSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (selectedOption.value) {
            const subject = selectedOption.dataset.subject;
            const content = selectedOption.dataset.content;
            
            templateSubject.innerHTML = '<strong>Subject:</strong> ' + subject;
            templateContent.innerHTML = '<strong>Content:</strong><br>' + content.replace(/\n/g, '<br>');
            templatePreview.style.display = 'block';
        } else {
            templatePreview.style.display = 'none';
        }
    });

    applyTemplateBtn.addEventListener('click', function() {
        const selectedOption = templateSelect.options[templateSelect.selectedIndex];
        if (selectedOption.value) {
            if (confirm('This will replace the current subject and description. Continue?')) {
                subjectInput.value = selectedOption.dataset.subject;
                descriptionTextarea.value = selectedOption.dataset.content;
                templateSelect.value = '';
                templatePreview.style.display = 'none';
            }
        }
    });

    // Recurring options toggle
    const isRecurringCheckbox = document.getElementById('is_recurring');
    const recurringOptions = document.getElementById('recurring_options');

    isRecurringCheckbox.addEventListener('change', function() {
        recurringOptions.style.display = this.checked ? 'block' : 'none';
    });

    // Form validation
    const form = document.getElementById('followupForm');
    form.addEventListener('submit', function(e) {
        const scheduledDate = new Date(document.getElementById('scheduled_date').value);
        const now = new Date();
        
        if (scheduledDate <= now) {
            if (!confirm('The scheduled date is in the past or very soon. Are you sure you want to continue?')) {
                e.preventDefault();
                return false;
            }
        }

        if (isRecurringCheckbox.checked) {
            const endDate = document.getElementById('recurring_end_date');
            if (endDate.value) {
                const endDateTime = new Date(endDate.value);
                if (endDateTime <= scheduledDate) {
                    e.preventDefault();
                    alert('Recurring end date must be after the scheduled date.');
                    return false;
                }
            }
        }
    });

    // Warning for overdue items
    const scheduledDateInput = document.getElementById('scheduled_date');
    const originalDate = new Date('{{ $followup->scheduled_date->toISOString() }}');
    
    if (originalDate < new Date()) {
        scheduledDateInput.addEventListener('focus', function() {
            if (!this.dataset.warningShown) {
                alert('This follow-up is overdue. Please update to a future date and time.');
                this.dataset.warningShown = 'true';
            }
        });
    }
});
</script>

<style>
.form-label {
    font-weight: 600;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.text-danger {
    color: #dc3545 !important;
}

.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    color: #dc3545;
    font-size: 0.875em;
}

.form-control-plaintext {
    padding: 0.375rem 0;
    margin-bottom: 0;
    font-size: 1rem;
    line-height: 1.5;
}

#template_preview .card-body {
    font-size: 0.9em;
}

.list-unstyled li {
    display: flex;
    align-items: flex-start;
}

.badge {
    font-size: 0.75em;
}

.table th {
    font-weight: 600;
    width: 35%;
}

.table td {
    word-break: break-word;
}
</style>
@endsection