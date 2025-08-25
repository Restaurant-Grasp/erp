@extends('layouts.app')

@section('title', 'Edit Lead')

<style>
    .contact-item {
        transition: all 0.3s ease;
    }

    .contact-item.removing {
        opacity: 0.5;
        transform: scale(0.95);
    }

    .form-check {
        margin-bottom: 0;
    }

    .form-check-input:checked+.form-check-label {
        font-weight: 500;
    }
</style>

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Lead</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form action="{{ route('leads.update', $lead->id) }}" method="POST" enctype="multipart/form-data" id="leadForm">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-8">
            {{-- Business Information --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Business Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Client Name</label>
                            <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $lead->company_name) }}">
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Details Section -->
            <div class="card mt-4" id="contact-details-section">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Contact Details</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-contact">
                        <i class="fas fa-plus me-1"></i> Add Contact
                    </button>
                </div>
                <div class="card-body">
                    <div id="contacts-container">
                        <!-- Contacts will be injected here -->
                    </div>

                    <!-- Contact Template (Hidden + Disabled so browser ignores it) -->
                    <div id="contact-template" style="display:none;">
                        <div class="contact-item border rounded p-3 mb-3" data-contact-index="">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Contact <span class="contact-number"></span></h6>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-contact">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="contacts[][name]" class="form-control contact-name" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="contacts[][email]" class="form-control contact-email" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="contacts[][phone]" class="form-control contact-phone" disabled>
                                </div>
                                <div class="col-md-6" style="display: none;">
                                    <label class="form-label">Contact Types</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input contact-primary" type="checkbox" name="contacts[][is_primary]" value="1" disabled>
                                            <label class="form-check-label">Primary</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input contact-billing" type="checkbox" name="contacts[][is_billing_contact]" value="1" disabled>
                                            <label class="form-check-label">Billing</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input contact-technical" type="checkbox" name="contacts[][is_technical_contact]" value="1" disabled>
                                            <label class="form-check-label">Technical</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden field for existing contact ID (for edit mode) -->
                        <input type="hidden" name="contacts[][id]" class="contact-id" disabled>
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Address Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $lead->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $lead->city) }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" value="{{ old('state', $lead->state) }}">
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Country</label>
                            <input type="text" class="form-control" value="Malaysia">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Business Details --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Business Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Business Category</label>
                            <select name="temple_category_id" class="form-select @error('temple_category_id') is-invalid @enderror">
                                <option value="">Select Category</option>
                                @foreach($templeCategories as $category)
                                <option value="{{ $category->id }}" {{ old('temple_category_id', $lead->temple_category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('temple_category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Size</label>
                            <select name="temple_size" class="form-select @error('temple_size') is-invalid @enderror">
                                <option value="">Select Size</option>
                                <option value="small" {{ old('temple_size', $lead->temple_size) == 'small' ? 'selected' : '' }}>Small</option>
                                <option value="medium" {{ old('temple_size', $lead->temple_size) == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="large" {{ old('temple_size', $lead->temple_size) == 'large' ? 'selected' : '' }}>Large</option>
                                <option value="very_large" {{ old('temple_size', $lead->temple_size) == 'very_large' ? 'selected' : '' }}>Very Large</option>
                            </select>
                            @error('temple_size')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Interested In</label>
                            <textarea name="interested_in" class="form-control @error('interested_in') is-invalid @enderror" rows="2">{{ old('interested_in', $lead->interested_in) }}</textarea>
                            @error('interested_in')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Documents (Optional Section) --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Documents</h5>
                </div>
                <div class="card-body">
                    @if(isset($lead) && $lead->documents && $lead->documents->count() > 0)
                    <div class="mb-3">
                        <h6>Existing Documents</h6>
                        <div class="list-group">
                            @foreach($lead->documents as $document)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    @php
                                    $iconClass = 'fa-file';
                                    $extension = strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION));
                                    if (in_array($extension, ['pdf'])) $iconClass = 'fa-file-pdf text-danger';
                                    elseif (in_array($extension, ['doc', 'docx'])) $iconClass = 'fa-file-word text-primary';
                                    elseif (in_array($extension, ['xls', 'xlsx'])) $iconClass = 'fa-file-excel text-success';
                                    elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) $iconClass = 'fa-file-image text-info';
                                    @endphp
                                    <i class="fas {{ $iconClass }} me-2"></i>
                                    {{ $document->document_name }}
                                    <small class="text-muted">({{ $document->file_size_formatted }})</small>
                                </div>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('leads.documents.download', [$lead, $document]) }}"
                                        class="btn btn-sm btn-outline-primary" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    @can('leads.edit')
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-document"
                                        data-lead-id="{{ $lead->id }}"
                                        data-document-id="{{ $document->id }}"
                                        data-document-name="{{ $document->document_name }}"
                                        title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div>
                        <label for="documents" class="form-label">Upload Documents</label>
                        <input type="file" class="form-control @error('documents.*') is-invalid @enderror"
                            id="documents" name="documents[]" multiple
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <div class="form-text">Allowed formats: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max: 10MB each)</div>
                        @error('documents.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            {{-- Lead Info --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Lead Info</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Lead Source *</label>
                        <select name="source" class="form-select @error('source') is-invalid @enderror">
                            <option value="">Select Source</option>
                            <option value="online" {{ old('source', $lead->source) == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="reference" {{ old('source', $lead->source) == 'reference' ? 'selected' : '' }}>Reference</option>
                            <option value="cold_call" {{ old('source', $lead->source) == 'cold_call' ? 'selected' : '' }}>Cold Call</option>
                            <option value="exhibition" {{ old('source', $lead->source) == 'exhibition' ? 'selected' : '' }}>Exhibition</option>
                            <option value="advertisement" {{ old('source', $lead->source) == 'advertisement' ? 'selected' : '' }}>Advertisement</option>
                            <option value="other" {{ old('source', $lead->source) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('source')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Source Details</label>
                        <input type="text" name="source_details" class="form-control @error('source_details') is-invalid @enderror" value="{{ old('source_details', $lead->source_details) }}">
                        @error('source_details')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lead Status</label>
                        <select name="lead_status" class="form-select @error('lead_status') is-invalid @enderror" id="lead_status">
                            <option value="new" {{ old('lead_status', $lead->lead_status) == 'new' ? 'selected' : '' }}>New</option>
                            <option value="contacted" {{ old('lead_status', $lead->lead_status) == 'contacted' ? 'selected' : '' }}>Contacted</option>
                            <option value="qualified" {{ old('lead_status', $lead->lead_status) == 'qualified' ? 'selected' : '' }}>Qualified</option>
                            <option value="proposal" {{ old('lead_status', $lead->lead_status) == 'proposal' ? 'selected' : '' }}>Proposal</option>
                            <option value="negotiation" {{ old('lead_status', $lead->lead_status) == 'negotiation' ? 'selected' : '' }}>Negotiation</option>
                            <option value="won" {{ old('lead_status', $lead->lead_status) == 'won' ? 'selected' : '' }}>Won</option>
                            <option value="lost" {{ old('lead_status', $lead->lead_status) == 'lost' ? 'selected' : '' }}>Lost</option>
                        </select>
                        @error('lead_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3" id="lost_reason_div" style="display: none;">
                        <label class="form-label">Lost Reason</label>
                        <textarea name="lost_reason" id="lost_reason" class="form-control @error('lost_reason') is-invalid @enderror" rows="2">{{ old('lost_reason', $lead->lost_reason) }}</textarea>
                        @error('lost_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Next Follow-up Date</label>
                        <input type="date" name="next_followup_date" class="form-control @error('next_followup_date') is-invalid @enderror" value="{{ old('next_followup_date', $lead->next_followup_date) }}">
                        @error('next_followup_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Assigned To</label>
                        <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                            <option value="">Select Staff</option>
                            @foreach($staff as $member)
                            <option value="{{ $member->id }}" {{ old('assigned_to', $lead->assigned_to) == $member->id ? 'selected' : '' }}>
                                {{ $member->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $lead->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update Lead
                        </button>
                        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary mt-2">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    (function() {
        // Handle form submit + optional lost reason logic safely
        $(document).ready(function() {
            $('#leadForm').on('submit', function(e) {
                if (!window.validateContacts || !window.validateContacts()) {
                    e.preventDefault();
                    return false;
                }
            });

            // Lost reason toggle
            function toggleLostReason() {
                var statusEl = document.getElementById('lead_status');
                var reasonDiv = document.getElementById('lost_reason_div');
                var reason = document.getElementById('lost_reason');
                if (!statusEl || !reasonDiv || !reason) return;

                if (statusEl.value === 'lost') {
                    reasonDiv.style.display = '';
                    reason.setAttribute('required', 'required');
                } else {
                    reasonDiv.style.display = 'none';
                    reason.removeAttribute('required');
                }
            }
            $('#lead_status').on('change', toggleLostReason);
            toggleLostReason();
        });

        document.addEventListener('DOMContentLoaded', function() {
            let contactIndex = 0;
            const contactsContainer = document.getElementById('contacts-container');
            const contactTemplate = document.getElementById('contact-template');
            const addContactBtn = document.getElementById('add-contact');

            addContactBtn.addEventListener('click', function() {
                addNewContact();
            });

            function addNewContact(contactData = null) {
                contactIndex++;
                const newContact = contactTemplate.firstElementChild.cloneNode(true);
                newContact.style.display = 'block';
                newContact.id = '';
                newContact.setAttribute('data-contact-index', contactIndex);

                // Enable all fields (remove disabled from template)
                newContact.querySelectorAll('input, textarea, select, button').forEach(el => {
                    el.removeAttribute('disabled');
                });

                // Update contact label (1-based)
                newContact.querySelector('.contact-number').textContent = contactIndex;

                // Update names with proper index
                updateContactInputNames(newContact, contactIndex);

                // Ensure required only on visible instances
                const nameField = newContact.querySelector('.contact-name');
                if (nameField) nameField.setAttribute('required', 'required');

                // Populate when editing
                if (contactData) {
                    populateContactData(newContact, contactData);
                }

                // Remove contact handler
                const removeBtn = newContact.querySelector('.remove-contact');
                removeBtn.addEventListener('click', function() {
                    removeContact(newContact);
                });

                // Only one primary contact
                const primaryCheckbox = newContact.querySelector('.contact-primary');
                if (primaryCheckbox) {
                    primaryCheckbox.addEventListener('change', function() {
                        if (this.checked) {
                            document.querySelectorAll('.contact-primary').forEach(cb => {
                                if (cb !== this) cb.checked = false;
                            });
                        }
                    });
                }

                contactsContainer.appendChild(newContact);

                // Smooth scroll to new contact
                newContact.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }

            function removeContact(contactElement) {
                if (confirm('Are you sure you want to remove this contact?')) {
                    contactElement.classList.add('removing');
                    setTimeout(() => {
                        contactElement.remove();
                        updateContactNumbers();
                    }, 300);
                }
            }

            // Robust re-indexing: handles both 'contacts[]' and 'contacts[0]' formats
            function updateContactInputNames(contactElement, index) {
                const inputs = contactElement.querySelectorAll(
                    'input[name^="contacts["], textarea[name^="contacts["], select[name^="contacts["],' +
                    'input[name^="contacts[]"], textarea[name^="contacts[]"], select[name^="contacts[]"]'
                );

                inputs.forEach(input => {
                    const current = input.getAttribute('name');
                    const replaced = current
                        .replace(/contacts\[\d+\]/, `contacts[${index - 1}]`)
                        .replace(/contacts\[\]/, `contacts[${index - 1}]`);
                    input.setAttribute('name', replaced);
                });
            }

            function populateContactData(contactElement, data) {
                const sel = (cls) => contactElement.querySelector(cls);
                if (sel('.contact-name')) sel('.contact-name').value = data.name || '';
                if (sel('.contact-email')) sel('.contact-email').value = data.email || '';
                if (sel('.contact-phone')) sel('.contact-phone').value = data.phone || '';
                if (sel('.contact-id')) sel('.contact-id').value = data.id || '';

                if (data.is_primary && sel('.contact-primary')) sel('.contact-primary').checked = true;
                if (data.is_billing_contact && sel('.contact-billing')) sel('.contact-billing').checked = true;
                if (data.is_technical_contact && sel('.contact-technical')) sel('.contact-technical').checked = true;
            }

            function updateContactNumbers() {
                const contacts = contactsContainer.querySelectorAll('.contact-item[data-contact-index]');
                contacts.forEach((contact, idx) => {
                    const number = idx + 1;
                    contact.querySelector('.contact-number').textContent = number;
                    contact.setAttribute('data-contact-index', number);
                    updateContactInputNames(contact, number);
                });
                contactIndex = contacts.length;
            }

            // Public loader for existing contacts from server
            window.loadExistingContacts = function(contacts) {
                contacts.forEach(contact => addNewContact(contact));
            };
   @if(isset($lead) && $lead->contacts)
    const existingContacts = {!! json_encode(
        $lead->contacts->map(function($contact) {
            return array(
                'id' => $contact->id,
                'name' => $contact->name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'is_primary' => (bool) $contact->is_primary,
                'is_billing_contact' => (bool) $contact->is_billing_contact,
                'is_technical_contact' => (bool) $contact->is_technical_contact,
            );
        })->values()
    ) !!};
    
    if (existingContacts && existingContacts.length > 0) {
        existingContacts.forEach(contact => addNewContact(contact));
    } else {
        addNewContact(); // Add one empty contact if no existing contacts
    }
@else
    // Add one empty contact for new leads
    addNewContact();
@endif

            // Client-side validation for visible contacts only
            window.validateContacts = function() {
                const contacts = contactsContainer.querySelectorAll('.contact-item[data-contact-index]');
                if (contacts.length === 0) {
                    alert('Please add at least one contact.');
                    return false;
                }
                let isValid = true;
                contacts.forEach(contact => {
                    const nameField = contact.querySelector('.contact-name');
                    if (!nameField || !nameField.value.trim()) {
                        if (nameField) nameField.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        nameField.classList.remove('is-invalid');
                    }
                });
                if (!isValid) alert('Please fill in all required contact fields.');
                return isValid;
            };
        });
    })();
</script>
@endsection