@extends('layouts.app')

@section('title', 'Create Quotation')

@section('content')
<div class="page-header">
    <h1 class="page-title">Create New Quotation</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sales.quotations.index') }}">Quotations</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<form action="{{ route('sales.quotations.store') }}" method="POST" id="quotationForm">
    @csrf
    <div class="row">
        <div class="col-md-12">
            <!-- Header Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Quotation Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Quotation Date <span class="text-danger">*</span></label>
                            <input type="date" name="quotation_date" class="form-control @error('quotation_date') is-invalid @enderror"
                                value="{{ old('quotation_date', date('Y-m-d')) }}" required>
                            @error('quotation_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Valid Until <span class="text-danger">*</span></label>
                            <input type="date" name="valid_until" class="form-control @error('valid_until') is-invalid @enderror"
                                value="{{ old('valid_until', date('Y-m-d', strtotime('+30 days'))) }}" required>
                            @error('valid_until')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
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
                        <div class="col-md-3">
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
                        <div class="col-md-4">
                            <label class="form-label">Reference No</label>
                            <input type="text" name="reference_no" class="form-control" value="{{ old('reference_no') }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" value="{{ old('subject') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                            <select name="currency" class="form-select" required>
                                <option value="INR" {{ old('currency', 'INR') == 'INR' ? 'selected' : '' }}>INR</option>
                                <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Exchange Rate <span class="text-danger">*</span></label>
                            <input type="number" name="exchange_rate" class="form-control" value="{{ old('exchange_rate', '1.0000') }}"
                                required min="0" step="0.0001">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Discount Type</label>
                            <select name="discount_type" class="form-select">
                                <option value="amount" {{ old('discount_type', 'amount') == 'amount' ? 'selected' : '' }}>Fixed Amount</option>
                                <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Discount Value</label>
                            <input type="number" name="discount_value" class="form-control" value="{{ old('discount_value', '0') }}"
                                min="0" step="0.01">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Items</h5>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addItem()">
                        <i class="fas fa-plus me-2"></i>Add Item
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="itemsTable">
                            <thead>
                                <tr>
                                    <th width="100">Type</th>
                                    <th>Item</th>
                                    <th width="80">Qty</th>
                                    <th width="80">UOM</th>
                                    <th width="120">Unit Price</th>
                                    <th width="80">Disc %</th>
                                    <th width="120">Tax</th>
                                    <th width="120">Total</th>
                                    <th width="50">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                <!-- Items will be added here dynamically -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7" class="text-end"><strong>Subtotal:</strong></td>
                                    <td><strong id="subtotalAmount">₹0.00</strong></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="7" class="text-end"><strong>Tax Amount:</strong></td>
                                    <td><strong id="taxAmount">₹0.00</strong></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="7" class="text-end"><strong>Total Amount:</strong></td>
                                    <td><strong id="totalAmount">₹0.00</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Terms and Notes -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Terms & Conditions</h5>
                        </div>
                        <div class="card-body">
                            <textarea name="terms_conditions" class="form-control" rows="4">{{ old('terms_conditions') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Internal Notes</h5>
                        </div>
                        <div class="card-body">
                            <textarea name="internal_notes" class="form-control" rows="4">{{ old('internal_notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->

            <div class="text-start mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Create Quotation
                </button>
                <a href="{{ route('sales.quotations.index') }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>

            </div>

        </div>
    </div>
</form>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let itemIndex = 0;

    function addItem() {
        const tbody = document.getElementById('itemsTableBody');
        const row = document.createElement('tr');
        row.innerHTML = `
        <td>
            <select name="items[${itemIndex}][item_type]" class="form-select item-type" required onchange="loadItems(this, ${itemIndex})">
                <option value="">Select</option>
                <option value="product">Product</option>
                <option value="service">Service</option>
                <option value="package">Package</option>
            </select>
        </td>
        <td>
            <select name="items[${itemIndex}][item_id]" class="form-select item-select" required onchange="updateItemDetails(this, ${itemIndex})">
                <option value="">Select Item</option>
            </select>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity" required min="0.01" step="0.01" value="1" onchange="calculateRowTotal(${itemIndex})">
        </td>
        <td>
            <select name="items[${itemIndex}][uom_id]" class="form-select uom-select">
                <option value="">-</option>
            </select>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][unit_price]" class="form-control unit-price" required min="0" step="0.01" value="0" onchange="calculateRowTotal(${itemIndex})">
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][discount_value]" class="form-control discount-value" min="0" step="0.01" value="0" onchange="calculateRowTotal(${itemIndex})">
            <input type="hidden" name="items[${itemIndex}][discount_type]" value="percentage">
        </td>
        <td>
            <select name="items[${itemIndex}][tax_id]" class="form-select tax-select" onchange="calculateRowTotal(${itemIndex})">
                <option value="">No Tax</option>
            </select>
        </td>
        <td>
            <span class="row-total">₹0.00</span>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
        tbody.appendChild(row);
        itemIndex++;
        loadTaxes(row.querySelector('.tax-select'));
    }

    function removeItem(button) {
        button.closest('tr').remove();
        calculateTotals();
    }

    function loadItems(selectElement, index) {
        const itemType = selectElement.value;
        const itemSelect = selectElement.closest('tr').querySelector('.item-select');

        itemSelect.innerHTML = '<option value="">Loading...</option>';

        if (itemType) {
            // Fix: Use the correct route
            fetch(`/quotations/get-items?type=${itemType}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    itemSelect.innerHTML = '<option value="">Select Item</option>';
                    data.forEach(item => {
                        itemSelect.innerHTML += `<option value="${item.id}" data-price="${item.price}" data-uom="${item.uom_id || ''}">${item.name}</option>`;
                    });
                })
                .catch(error => {
                    console.error('Error loading items:', error);
                    itemSelect.innerHTML = '<option value="">Error loading items</option>';
                });
        } else {
            itemSelect.innerHTML = '<option value="">Select Item</option>';
        }
    }


    function updateItemDetails(selectElement, index) {
        const option = selectElement.selectedOptions[0];
        if (option && option.dataset.price) {
            const row = selectElement.closest('tr');
            row.querySelector('.unit-price').value = option.dataset.price;

            // Update UOM if available
            const uomSelect = row.querySelector('.uom-select');
            if (option.dataset.uom) {
                // Load UOMs and select the one from the product
            }

            calculateRowTotal(index);
        }
    }

    function loadTaxes(selectElement) {
        // Fix: Use the correct route name
        fetch('/taxes/for-dropdown')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                selectElement.innerHTML = '<option value="">No Tax</option>';
                data.forEach(tax => {
                    selectElement.innerHTML += `<option value="${tax.id}" data-rate="${tax.percent}">${tax.name} (${tax.percent}%)</option>`;
                });
            })
            .catch(error => {
                console.error('Error loading taxes:', error);
                selectElement.innerHTML = '<option value="">Error loading taxes</option>';
            });
    }

    function calculateRowTotal(index) {
        const rows = document.querySelectorAll('#itemsTableBody tr');
        const row = rows[index];

        const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
        const discountValue = parseFloat(row.querySelector('.discount-value').value) || 0;
        const taxSelect = row.querySelector('.tax-select');
        const taxRate = taxSelect.selectedOptions[0]?.dataset.rate || 0;

        const lineTotal = quantity * unitPrice;
        const discountAmount = (lineTotal * discountValue) / 100;
        const afterDiscount = lineTotal - discountAmount;
        const taxAmount = (afterDiscount * taxRate) / 100;
        const rowTotal = afterDiscount + taxAmount;

        row.querySelector('.row-total').textContent = `₹${rowTotal.toFixed(2)}`;

        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;
        let totalTax = 0;

        document.querySelectorAll('#itemsTableBody tr').forEach((row, index) => {
            const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
            const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
            const discountValue = parseFloat(row.querySelector('.discount-value').value) || 0;
            const taxSelect = row.querySelector('.tax-select');
            const taxRate = parseFloat(taxSelect.selectedOptions[0]?.dataset.rate || 0);

            const lineTotal = quantity * unitPrice;
            const discountAmount = (lineTotal * discountValue) / 100;
            const afterDiscount = lineTotal - discountAmount;
            const taxAmount = (afterDiscount * taxRate) / 100;

            subtotal += afterDiscount;
            totalTax += taxAmount;
        });

        const total = subtotal + totalTax;

        document.getElementById('subtotalAmount').textContent = `₹${subtotal.toFixed(2)}`;
        document.getElementById('taxAmount').textContent = `₹${totalTax.toFixed(2)}`;
        document.getElementById('totalAmount').textContent = `₹${total.toFixed(2)}`;
    }

    $(document).ready(function() {
        // Add initial item
        addItem();

        // Customer/Lead selection validation
        $('#customer_id, #lead_id').on('change', function() {
            if (this.value) {
                if (this.id === 'customer_id') {
                    $('#lead_id').val('');
                } else {
                    $('#customer_id').val('');
                }
            }
        });

        // Form validation
        $('#quotationForm').on('submit', function(e) {
            const hasCustomerOrLead = $('#customer_id').val() || $('#lead_id').val();
            const hasItems = $('#itemsTableBody tr').length > 0;

            if (!hasCustomerOrLead) {
                e.preventDefault();

                return false;
            }

            if (!hasItems) {
                e.preventDefault();
                alert('Please add at least one item.');
                return false;
            }
        });
    });
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
    });
</script>
@endsection