<!-- Contact Details Section Component -->
<div class="card mt-4" id="contact-details-section">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Contact Details</h5>
        <button type="button" class="btn btn-sm btn-outline-primary" id="add-contact">
            <i class="fas fa-plus me-1"></i> Add Contact
        </button>
    </div>
    <div class="card-body">
        <div id="contacts-container">
            <!-- Existing contacts will be loaded here -->
            <!-- Template for new contacts will be added here -->
        </div>
        
        <!-- Contact Template (Hidden) -->
        <div id="contact-template" style="display: none;">
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
                        <input type="text" name="contacts[][name]" class="form-control contact-name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="contacts[][email]" class="form-control contact-email">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="contacts[][phone]" class="form-control contact-phone">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Types</label>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input contact-primary" type="checkbox" name="contacts[][is_primary]" value="1">
                                <label class="form-check-label">Primary</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input contact-billing" type="checkbox" name="contacts[][is_billing_contact]" value="1">
                                <label class="form-check-label">Billing</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input contact-technical" type="checkbox" name="contacts[][is_technical_contact]" value="1">
                                <label class="form-check-label">Technical</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Hidden field for existing contact ID (for edit mode) -->
                <input type="hidden" name="contacts[][id]" class="contact-id">
            </div>
        </div>
    </div>
</div>

<style>


.contact-item.removing {
    opacity: 0.5;
    transform: scale(0.95);
}

.form-check {
    margin-bottom: 0;
}

.form-check-input:checked + .form-check-label {
    font-weight: 500;
}
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let contactIndex = 0;
    const contactsContainer = document.getElementById('contacts-container');
    const contactTemplate = document.getElementById('contact-template');
    const addContactBtn = document.getElementById('add-contact');

    // Add contact functionality
    addContactBtn.addEventListener('click', function() {
        addNewContact();
    });

    // Function to add new contact
    function addNewContact(contactData = null) {
        contactIndex++;
        const newContact = contactTemplate.cloneNode(true);
        newContact.style.display = 'block';
        newContact.id = '';
        newContact.setAttribute('data-contact-index', contactIndex);
        
        // Update contact number
        newContact.querySelector('.contact-number').textContent = contactIndex;
        
        // Update input names with proper index
        updateContactInputNames(newContact, contactIndex);
        
        // If contact data is provided (edit mode), populate the fields
        if (contactData) {
            populateContactData(newContact, contactData);
        }
        
        // Add remove functionality
        const removeBtn = newContact.querySelector('.remove-contact');
        removeBtn.addEventListener('click', function() {
            removeContact(newContact);
        });
        
        // Add primary contact restriction
        const primaryCheckbox = newContact.querySelector('.contact-primary');
        primaryCheckbox.addEventListener('change', function() {
            if (this.checked) {
                // Uncheck other primary contacts
                document.querySelectorAll('.contact-primary').forEach(cb => {
                    if (cb !== this) cb.checked = false;
                });
            }
        });
        
        contactsContainer.appendChild(newContact);
        
        // Scroll to new contact
        newContact.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Function to remove contact
    function removeContact(contactElement) {
        if (confirm('Are you sure you want to remove this contact?')) {
            contactElement.classList.add('removing');
            setTimeout(() => {
                contactElement.remove();
                updateContactNumbers();
            }, 300);
        }
    }

    // Function to update input names with proper indexing
    function updateContactInputNames(contactElement, index) {
        const inputs = contactElement.querySelectorAll('input[name*="contacts[]"]');
        inputs.forEach(input => {
            const currentName = input.getAttribute('name');
            const newName = currentName.replace('contacts[]', `contacts[${index - 1}]`);
            input.setAttribute('name', newName);
        });
    }

    // Function to populate contact data (for edit mode)
    function populateContactData(contactElement, data) {
        contactElement.querySelector('.contact-name').value = data.name || '';
        contactElement.querySelector('.contact-email').value = data.email || '';
        contactElement.querySelector('.contact-phone').value = data.phone || '';
        contactElement.querySelector('.contact-id').value = data.id || '';
        
        if (data.is_primary) contactElement.querySelector('.contact-primary').checked = true;
        if (data.is_billing_contact) contactElement.querySelector('.contact-billing').checked = true;
        if (data.is_technical_contact) contactElement.querySelector('.contact-technical').checked = true;
    }

    // Function to update contact numbers after removal
    function updateContactNumbers() {
        const contacts = document.querySelectorAll('.contact-item[data-contact-index]');
        contacts.forEach((contact, index) => {
            const number = index + 1;
            contact.querySelector('.contact-number').textContent = number;
            contact.setAttribute('data-contact-index', number);
            updateContactInputNames(contact, number);
        });
        contactIndex = contacts.length;
    }

    // Function to load existing contacts (call this from your blade template)
    window.loadExistingContacts = function(contacts) {
        contacts.forEach(contact => {
            addNewContact(contact);
        });
    };

    // Add at least one contact by default if none exist
    if (contactsContainer.children.length === 0) {
        addNewContact();
    }

    // Form validation
    window.validateContacts = function() {
        const contacts = document.querySelectorAll('.contact-item[data-contact-index]');
        let isValid = true;
        let hasAtLeastOneContact = contacts.length > 0;
        
        if (!hasAtLeastOneContact) {
            alert('Please add at least one contact.');
            return false;
        }

        contacts.forEach(contact => {
            const nameField = contact.querySelector('.contact-name');
            if (!nameField.value.trim()) {
                nameField.classList.add('is-invalid');
                isValid = false;
            } else {
                nameField.classList.remove('is-invalid');
            }
        });

        if (!isValid) {
            alert('Please fill in all required contact fields.');
        }

        return isValid;
    };
});
</script>