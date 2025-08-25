@extends('layouts.app')

@section('title', 'Edit Customer')
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
    <h1 class="page-title">Edit Customer</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customers.show', $customer) }}">{{ $customer->customer_code }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form action="{{ route('customers.update', $customer) }}" method="POST" id="customerForm">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-8">
            {{-- Company Information --}}
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Company Information</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Customer Code</label>
                            <input type="text" class="form-control" value="{{ $customer->customer_code }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" 
                                   value="{{ old('company_name', $customer->company_name) }}">
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Fax</label>
                            <input type="text" name="fax" class="form-control" value="{{ old('fax', $customer->fax) }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Website</label>
                            <input type="url" name="website" class="form-control" value="{{ old('website', $customer->website) }}" placeholder="https://">
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

            {{-- Address Information --}}
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Address Information</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" name="address_line1" class="form-control" value="{{ old('address_line1', $customer->address_line1) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="address_line2" class="form-control" value="{{ old('address_line2', $customer->address_line2) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $customer->city) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state', $customer->state) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Postcode</label>
                            <input type="text" name="postcode" class="form-control" value="{{ old('postcode', $customer->postcode) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control" value="{{ old('country', $customer->country) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Registration No</label>
                            <input type="text" name="registration_no" class="form-control" value="{{ old('registration_no', $customer->registration_no) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tax No (GST)</label>
                            <input type="text" name="tax_no" class="form-control" value="{{ old('tax_no', $customer->tax_no) }}">
                        </div>
                    </div>
                </div>
            </div>

          
        </div>

        <div class="col-md-4">
            {{-- Business Information --}}
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Business Information</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Service Type</label>
                        @foreach($serviceTypes as $serviceType)
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="service_types[]" 
                                       value="{{ $serviceType->id }}" id="service_{{ $serviceType->id }}"
                                       {{ in_array($serviceType->id, old('service_types', $selectedServiceTypes)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="service_{{ $serviceType->id }}">
                                    {{ $serviceType->name }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Credit Limit (₹)</label>
                        <input type="number" name="credit_limit" class="form-control" 
                               value="{{ old('credit_limit', $customer->credit_limit) }}" min="0" step="0.01">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Credit Days</label>
                        <input type="number" name="credit_days" class="form-control" 
                               value="{{ old('credit_days', $customer->credit_days) }}" min="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Discount (%)</label>
                        <input type="number" name="discount_percentage" class="form-control" 
                               value="{{ old('discount_percentage', $customer->discount_percentage) }}" min="0" max="100" step="0.01">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('source') is-invalid @enderror">
                            <option value="active" {{ old('status', $customer->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $customer->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="blocked" {{ old('status', $customer->status) == 'blocked' ? 'selected' : '' }}>Blocked</option>
                        </select>
                           @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Source Information --}}
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Source Information</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Source <span class="text-danger">*</span></label>
                        <select name="source" class="form-select @error('source') is-invalid @enderror">
                            <option value="">Select Source</option>
                            <option value="online" {{ old('source', $customer->source) == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="reference" {{ old('source', $customer->source) == 'reference' ? 'selected' : '' }}>Reference</option>
                            <option value="direct" {{ old('source', $customer->source) == 'direct' ? 'selected' : '' }}>Direct</option>
                            <option value="other" {{ old('source', $customer->source) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                           @error('source')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reference By</label>
                        <input type="text" name="reference_by" class="form-control" value="{{ old('reference_by', $customer->reference_by) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Assigned To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Select Staff</option>
                            @foreach($staff as $member)
                                <option value="{{ $member->id }}" {{ old('assigned_to', $customer->assigned_to) == $member->id ? 'selected' : '' }}>
                                    {{ $member->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $customer->notes) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Actions</h5></div>
                <div class="card-body">
                    @if($customer->ledger)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Ledger Account: <strong>{{ $customer->ledger->name }}</strong>
                    </div>
                    @endif
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update Customer
                        </button>
                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary mt-2">
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
