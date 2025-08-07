@extends('layouts.app')

@section('title', 'Create Follow-up')

@section('content')
<div class="page-header">
    <h1 class="page-title">Create New Follow-up</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('followups.index') }}">Follow-ups</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Follow-up Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('followups.store') }}" method="POST" id="followupForm">
                    @csrf
                    
                    <!-- Entity Selection -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="entity_type" class="form-label">Entity Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('entity_type') is-invalid @enderror" 
                                    name="entity_type" id="entity_type" required>
                                <option value="">Select Entity Type</option>
                                <option value="lead" {{ old('entity_type') == 'lead' || $lead ? 'selected' : '' }}>Lead</option>
                                <option value="customer" {{ old('entity_type') == 'customer' || $customer ? 'selected' : '' }}>Customer</option>
                            </select>
                            @error('entity_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="entity_id" class="form-label">Select Entity <span class="text-danger">*</span></label>
                            <select class="form-select @error('entity_id') is-invalid @enderror" 
                                    name="entity_id" id="entity_id" required>
                                <option value="">Select Entity</option>
                                @if($lead)
                                    <option value="{{ $lead->id }}" selected>{{ $lead->lead_no }} - {{ $lead->entity_name }}</option>
                                @endif
                                @if($customer)
                                    <option value="{{ $customer->id }}" selected>{{ $customer->customer_code }} - {{ $customer->company_name }}</option>
                                @endif
                            </select>
                            @error('entity_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Follow-up Type and Priority -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="follow_up_type" class="form-label">Follow-up Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('follow_up_type') is-invalid @enderror" 
                                    name="follow_up_type" id="follow_up_type" required>
                                <option value="">Select Type</option>
                                <option value="phone_call" {{ old('follow_up_type') == 'phone_call' ? 'selected' : '' }}>
                                    <i class="fas fa-phone"></i> Phone Call
                                </option>
                                <option value="email" {{ old('follow_up_type') == 'email' ? 'selected' : '' }}>
                                    <i class="fas fa-envelope"></i> Email
                                </option>
                                <option value="whatsapp" {{ old('follow_up_type') == 'whatsapp' ? 'selected' : '' }}>
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </option>
                                <option value="in_person_meeting" {{ old('follow_up_type') == 'in_person_meeting' ? 'selected' : '' }}>
                                    <i class="fas fa-handshake"></i> In-Person Meeting
                                </option>
                                <option value="video_call" {{ old('follow_up_type') == 'video_call' ? 'selected' : '' }}>
                                    <i class="fas fa-video"></i> Video Call
                                </option>
                                <option value="other" {{ old('follow_up_type') == 'other' ? 'selected' : '' }}>
                                    <i class="fas fa-comment-dots"></i> Other
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
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
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
                                   value="{{ old('scheduled_date') }}"
                                   min="{{ now()->format('Y-m-d\TH:i') }}" 
                                   required>
                            @error('scheduled_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="assigned_to" class="form-label">Assigned To <span class="text-danger">*</span></label>
                            <select class="form-select @error('assigned_to') is-invalid @enderror" 
                                    name="assigned_to" id="assigned_to" required>
                                <option value="">Select Staff Member</option>
                                @foreach($staff as $staffMember)
                                    <option value="{{ $staffMember->id }}" 
                                            {{ old('assigned_to', auth()->user()->staff_id) == $staffMember->id ? 'selected' : '' }}>
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
                               value="{{ old('subject') }}" 
                               maxlength="255" 
                               required>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Template Selection -->
                    <div class="mb-3">
                        <label for="template_id" class="form-label">Use Template (Optional)</label>
                        <select class="form-select @error('template_id') is-invalid @enderror" 
                                name="template_id" id="template_id">
                            <option value="">Select Template</option>
                            @foreach($templates->groupBy('category') as $category => $categoryTemplates)
                                <optgroup label="{{ ucfirst($category) }}">
                                    @foreach($categoryTemplates as $template)
                                        <option value="{{ $template->id }}" 
                                                data-content="{{ $template->content }}"
                                                data-subject="{{ $template->subject }}"
                                                {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                            {{ $template->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('template_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description/Notes</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" 
                                  id="description" 
                                  rows="4">{{ old('description') }}</textarea>
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
                                       {{ old('is_recurring') ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_recurring">
                                    <i class="fas fa-redo me-1"></i> Make this a recurring follow-up
                                </label>
                            </div>
                        </div>
                        <div class="card-body" id="recurring_options" style="{{ old('is_recurring') ? '' : 'display: none;' }}">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="recurring_pattern" class="form-label">Pattern</label>
                                    <select class="form-select @error('recurring_pattern') is-invalid @enderror" 
                                            name="recurring_pattern" id="recurring_pattern">
                                        <option value="daily" {{ old('recurring_pattern') == 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="weekly" {{ old('recurring_pattern') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="monthly" {{ old('recurring_pattern', 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="custom" {{ old('recurring_pattern') == 'custom' ? 'selected' : '' }}>Custom (Days)</option>
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
                                           value="{{ old('recurring_interval', 1) }}" 
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
                                       value="{{ old('recurring_end_date') }}">
                                @error('recurring_end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('followups.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Schedule Follow-up
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Entity Preview -->
        <div class="card" id="entity_preview" style="display: none;">
            <div class="card-header">
                <h5 class="mb-0">Entity Information</h5>
            </div>
            <div class="card-body" id="entity_details">
                <!-- Entity details will be loaded here -->
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
                    <i class="fas fa-info-circle me-1"></i> Tips
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-lightbulb text-warning me-2"></i>
                        Use templates to save time on common follow-up types
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-clock text-info me-2"></i>
                        Schedule follow-ups at appropriate times for better response rates
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-redo text-success me-2"></i>
                        Use recurring follow-ups for regular check-ins
                    </li>
                    <li>
                        <i class="fas fa-flag text-danger me-2"></i>
                        Set priority levels to help organize your workflow
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Entity type and selection handling
    const entityTypeSelect = document.getElementById('entity_type');
    const entityIdSelect = document.getElementById('entity_id');
    const entityPreview = document.getElementById('entity_preview');
    const entityDetails = document.getElementById('entity_details');

    entityTypeSelect.addEventListener('change', function() {
        const entityType = this.value;
        entityIdSelect.innerHTML = '<option value="">Loading...</option>';
        
        if (entityType) {
            // Fetch entities based on type
            fetch(`/api/${entityType}s`)
                .then(response => response.json())
                .then(data => {
                    entityIdSelect.innerHTML = '<option value="">Select ' + entityType.charAt(0).toUpperCase() + entityType.slice(1) + '</option>';
                    data.forEach(entity => {
                        const option = document.createElement('option');
                        option.value = entity.id;
                        if (entityType === 'lead') {
                            option.textContent = entity.lead_no + ' - ' + (entity.company_name || entity.contact_person);
                        } else {
                            option.textContent = entity.customer_code + ' - ' + entity.company_name;
                        }
                        entityIdSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    entityIdSelect.innerHTML = '<option value="">Error loading data</option>';
                });
        } else {
            entityIdSelect.innerHTML = '<option value="">Select Entity</option>';
            entityPreview.style.display = 'none';
        }
    });

    entityIdSelect.addEventListener('change', function() {
        const entityId = this.value;
        const entityType = entityTypeSelect.value;
        
        if (entityId && entityType) {
            // Fetch entity details
            fetch(`/api/${entityType}s/${entityId}`)
                .then(response => response.json())
                .then(entity => {
                    let html = '<table class="table table-sm">';
                    
                    if (entityType === 'lead') {
                        html += `
                            <tr><th>Lead No:</th><td>${entity.lead_no}</td></tr>
                            <tr><th>Temple:</th><td>${entity.company_name || '-'}</td></tr>
                            <tr><th>Contact:</th><td>${entity.contact_person}</td></tr>
                            <tr><th>Email:</th><td>${entity.email || '-'}</td></tr>
                            <tr><th>Phone:</th><td>${entity.mobile || entity.phone || '-'}</td></tr>
                            <tr><th>Status:</th><td><span class="badge bg-primary">${entity.lead_status}</span></td></tr>
                        `;
                    } else {
                        html += `
                            <tr><th>Code:</th><td>${entity.customer_code}</td></tr>
                            <tr><th>Temple:</th><td>${entity.company_name}</td></tr>
                            <tr><th>Contact:</th><td>${entity.contact_person || '-'}</td></tr>
                            <tr><th>Email:</th><td>${entity.email || '-'}</td></tr>
                            <tr><th>Phone:</th><td>${entity.mobile || entity.phone || '-'}</td></tr>
                        `;
                    }
                    
                    html += '</table>';
                    entityDetails.innerHTML = html;
                    entityPreview.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        } else {
            entityPreview.style.display = 'none';
        }
    });

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
            if (!subjectInput.value) {
                subjectInput.value = selectedOption.dataset.subject;
            }
            if (!descriptionTextarea.value) {
                descriptionTextarea.value = selectedOption.dataset.content;
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
            e.preventDefault();
            alert('Please select a future date and time for the follow-up.');
            return false;
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

#template_preview .card-body {
    font-size: 0.9em;
}

.list-unstyled li {
    display: flex;
    align-items: flex-start;
}
</style>
@endsection