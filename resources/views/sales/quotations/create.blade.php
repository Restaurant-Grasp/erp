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
                            <label for="entity_type" class="form-label">Customer Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('entity_type') is-invalid @enderror"
                                name="entity_type" id="entity_type" required>
                                <option value="">Select Customer Type</option>
                                <option value="lead" {{ old('entity_type') == 'lead' || $lead ?? false ? 'selected' : '' }}>Lead</option>
                                <option value="customer" {{ old('entity_type') == 'customer' || $customer ?? false ? 'selected' : '' }}>Customer</option>
                            </select>
                            @error('entity_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="entity_select" class="form-label">Customer<span class="text-danger">*</span></label>
                            <select class="form-select" name="entity_select" id="entity_select" required>
                                <option value="">Select Customer</option>
                                @if(isset($lead))
                                <option value="{{ $lead->id }}" selected>{{ $lead->lead_no }} - {{ $lead->entity_name }}</option>
                                @endif
                                @if(isset($customer))
                                <option value="{{ $customer->id }}" selected>{{ $customer->customer_code }} - {{ $customer->company_name }}</option>
                                @endif
                            </select>
                            <input type="hidden" name="customer_id" id="customer_id" value="{{ $customer->id ?? '' }}">
                            <input type="hidden" name="lead_id" id="lead_id" value="{{ $lead->id ?? '' }}">
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
                            <label class="form-label">Currency</label>
                            <input type="text"
                                name="currency"
                                class="form-control"
                                value="{{ app(\App\Helpers\SettingsHelper::class)->getSettingCurrency('currency') }}" disabled>
                        </div>
                        <input type="hidden" name="exchange_rate" value="1">
                        <div class="col-md-3">
                            <label class="form-label">Discount Type</label>
                            <select name="discount_type" id="discount_type" class="form-select">
                                <option value="amount" {{ old('discount_type', 'amount') == 'amount' ? 'selected' : '' }}>Fixed Amount</option>
                                <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Discount Value</label>
                            <input type="number" name="discount_value" id="discount_value" class="form-control" value="{{ old('discount_value', '0') }}"
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
                                    <th width="80" style="display: none;">UOM</th>
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
                                    <td colspan="6" class="text-end"><strong>Subtotal:</strong></td>
                                    <td><strong id="subtotalAmount">RM 0.00</strong></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Tax Amount:</strong></td>
                                    <td><strong id="taxAmount">RM 0.00</strong></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Total Amount:</strong></td>
                                    <td><strong id="totalAmount">RM 0.00</strong></td>
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

<style>
    .table td {
        vertical-align: middle;
    }

    .item-select,
    .tax-select,
    .uom-select {
        min-width: 150px;
    }

    .quantity,
    .unit-price,
    .discount-value {
        min-width: 80px;
    }

    .row-total {
        font-weight: bold;
        color: #198754;
    }

    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.875rem;
        }

        .item-select,
        .tax-select,
        .uom-select {
            min-width: 120px;
        }
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let itemIndex = 0;

    function addItem() {
        const tbody = document.getElementById('itemsTableBody');
        const row = document.createElement('tr');
        row.setAttribute('data-index', itemIndex);
        row.innerHTML = `
        <td>
            <select name="items[${itemIndex}][item_type]" class="form-select item-type" required onchange="loadItems(this, ${itemIndex})">
                <option value="">Select</option>
                <option value="product">Product</option>
                <option value="service">Service</option>
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
        <td style="display: none;">
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
        <td class="tax-cell">
            <!-- Tax Dropdown (default) -->
            <select name="items[${itemIndex}][tax_id]" class="form-select tax-select" onchange="calculateRowTotal(${itemIndex})">
                <option value="">No Tax</option>
            </select>
            <!-- Tax Input Fields (for Package type) -->
            <div class="tax-inputs" style="display: none;">
                <input type="number" name="items[${itemIndex}][tax_rate]" class="form-control tax-rate mb-1" placeholder="Tax Rate %" min="0" step="0.01" onchange="calculateRowTotal(${itemIndex})">
            </div>
        </td>
        <td>
            <span class="row-total">RM 0.00</span>
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
        const row = selectElement.closest('tr');
        const taxCell = row.querySelector('.tax-cell');
        const taxSelect = taxCell ? taxCell.querySelector('.tax-select') : null;
        const taxInputs = taxCell ? taxCell.querySelector('.tax-inputs') : null;

        itemSelect.innerHTML = '<option value="">Loading...</option>';

        // Toggle tax display based on item type
        if (itemType === 'package') {
            // Show tax input fields, hide dropdown
            if (taxSelect) taxSelect.style.display = 'none';
            if (taxInputs) taxInputs.style.display = 'block';
            // Clear dropdown selection
            if (taxSelect) taxSelect.value = '';
        } else {
            // Show tax dropdown, hide input fields
            if (taxSelect) taxSelect.style.display = 'block';
            if (taxInputs) taxInputs.style.display = 'none';
            // Clear input fields
            if (taxInputs && taxInputs.querySelector('.tax-rate')) {
                taxInputs.querySelector('.tax-rate').value = '';
            }

            // Load taxes for this item type
            if (taxSelect) {
                loadTaxes(taxSelect, itemType);
            }
        }

        if (itemType === 'service' || itemType === 'product') {
            // Load items based on type
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
        } else if (itemType === 'package') {
            // For packages, you might need a different endpoint
            fetch(`/quotations/get-items?type=package`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    itemSelect.innerHTML = '<option value="">Select Package</option>';
                    data.forEach(item => {
                        itemSelect.innerHTML += `<option value="${item.id}" data-price="${item.price}">${item.name}</option>`;
                    });
                })
                .catch(error => {
                    console.error('Error loading packages:', error);
                    itemSelect.innerHTML = '<option value="">Error loading packages</option>';
                });
        } else {
            itemSelect.innerHTML = '<option value="">Select Item</option>';
        }
    }

    function updateItemDetails(selectElement, index) {
        const option = selectElement.selectedOptions[0];
        if (option && option.dataset.price) {
            const row = selectElement.closest('tr');
            const unitPriceInput = row.querySelector('.unit-price');
            if (unitPriceInput) {
                unitPriceInput.value = option.dataset.price;
            }

            // Update UOM if available
            const uomSelect = row.querySelector('.uom-select');
            if (option.dataset.uom && uomSelect) {
                // You can load UOMs here if needed
                // For now, just set the value if it exists
                uomSelect.value = option.dataset.uom;
            }

            calculateRowTotal(index);
        }
    }

    function loadTaxes(selectElement, itemType = 'both') {
        const currentValue = selectElement.value;
        
        fetch(`/taxes/for-dropdown?type=${itemType}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                selectElement.innerHTML = '<option value="">No Tax</option>';
                data.forEach(tax => {
                    const selected = tax.id == currentValue ? 'selected' : '';
                    selectElement.innerHTML += `<option value="${tax.id}" data-rate="${tax.percent}" ${selected}>${tax.percent} (${tax.percent}%)</option>`;
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

        if (!row) return;

        const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
        const itemDiscountValue = parseFloat(row.querySelector('.discount-value').value) || 0;

        // Check if using tax dropdown or input fields
        const taxSelect = row.querySelector('.tax-select');
        const taxRateInput = row.querySelector('.tax-rate');

        let taxRate = 0;
        if (taxSelect && taxSelect.style.display !== 'none' && taxSelect.value) {
            // Using tax dropdown
            taxRate = parseFloat(taxSelect.selectedOptions[0]?.dataset.rate || 0);
        } else if (taxRateInput && taxRateInput.style.display !== 'none') {
            // Using tax input field
            taxRate = parseFloat(taxRateInput.value || 0);
        }

        // Calculate line total
        const lineTotal = quantity * unitPrice;

        // Apply item-level discount (percentage)
        const itemDiscountAmount = (lineTotal * itemDiscountValue) / 100;
        const afterItemDiscount = lineTotal - itemDiscountAmount;

        // Calculate tax on the amount after item discount
        const taxAmount = (afterItemDiscount * taxRate) / 100;

        // Row total (after item discount + tax)
        const rowTotal = afterItemDiscount + taxAmount;

        // Update row display
        const rowTotalElement = row.querySelector('.row-total');
        if (rowTotalElement) {
            rowTotalElement.textContent = `RM ${rowTotal.toFixed(2)}`;
        }

        // Recalculate overall totals
        calculateTotals();
    }

    function calculateTotals() {
        let subtotalAfterItemDiscounts = 0;
        let totalTax = 0;

        // Calculate totals from all line items
        document.querySelectorAll('#itemsTableBody tr').forEach((row, index) => {
            const quantity = parseFloat(row.querySelector('.quantity')?.value) || 0;
            const unitPrice = parseFloat(row.querySelector('.unit-price')?.value) || 0;
            const itemDiscountValue = parseFloat(row.querySelector('.discount-value')?.value) || 0;

            // Check if using tax dropdown or input fields
            const taxSelect = row.querySelector('.tax-select');
            const taxRateInput = row.querySelector('.tax-rate');

            let taxRate = 0;
            if (taxSelect && taxSelect.style.display !== 'none' && taxSelect.value) {
                taxRate = parseFloat(taxSelect.selectedOptions[0]?.dataset.rate || 0);
            } else if (taxRateInput && taxRateInput.style.display !== 'none') {
                taxRate = parseFloat(taxRateInput.value || 0);
            }

            // Calculate for this line
            const lineTotal = quantity * unitPrice;
            const itemDiscountAmount = (lineTotal * itemDiscountValue) / 100;
            const afterItemDiscount = lineTotal - itemDiscountAmount;
            const taxAmount = (afterItemDiscount * taxRate) / 100;

            // Add to running totals
            subtotalAfterItemDiscounts += afterItemDiscount;
            totalTax += taxAmount;
        });

        // Apply invoice-level discount
        const invoiceDiscountType = document.querySelector('#discount_type')?.value || 'amount';
        const invoiceDiscountValue = parseFloat(document.querySelector('#discount_value')?.value || 0);

        let invoiceDiscountAmount = 0;
        if (invoiceDiscountValue > 0) {
            if (invoiceDiscountType === 'percentage') {
                invoiceDiscountAmount = (subtotalAfterItemDiscounts * invoiceDiscountValue) / 100;
            } else {
                invoiceDiscountAmount = invoiceDiscountValue;
            }
        }

        // Calculate final amounts
        const finalSubtotal = subtotalAfterItemDiscounts - invoiceDiscountAmount;
        const grandTotal = finalSubtotal + totalTax;

        // Update display
        const subtotalElement = document.getElementById('subtotalAmount');
        const taxElement = document.getElementById('taxAmount');
        const totalElement = document.getElementById('totalAmount');

        if (subtotalElement) subtotalElement.textContent = `RM ${finalSubtotal.toFixed(2)}`;
        if (taxElement) taxElement.textContent = `RM ${totalTax.toFixed(2)}`;
        if (totalElement) totalElement.textContent = `RM ${grandTotal.toFixed(2)}`;
    }

    // Entity type change handler
    document.getElementById('entity_type').addEventListener('change', function() {
        const entityType = this.value;
        const entitySelect = document.getElementById('entity_select');
        const customerIdField = document.getElementById('customer_id');
        const leadIdField = document.getElementById('lead_id');

        // Clear selections
        entitySelect.innerHTML = '<option value="">Loading...</option>';
        customerIdField.value = '';
        leadIdField.value = '';

        if (entityType === 'customer') {
            // Load customers
            fetch('/api/customers')
                .then(response => response.json())
                .then(data => {
                    entitySelect.innerHTML = '<option value="">Select Customer</option>';
                    data.forEach(customer => {
                        entitySelect.innerHTML += `<option value="${customer.id}">${customer.customer_code} - ${customer.company_name}</option>`;
                    });
                })
                .catch(error => {
                    console.error('Error loading customers:', error);
                    entitySelect.innerHTML = '<option value="">Error loading customers</option>';
                });
        } else if (entityType === 'lead') {
            // Load leads
            fetch('/api/leads')
                .then(response => response.json())
                .then(data => {
                    entitySelect.innerHTML = '<option value="">Select Lead</option>';
                    data.forEach(lead => {
                        entitySelect.innerHTML += `<option value="${lead.id}">${lead.lead_no} - ${lead.company_name || lead.contact_person}</option>`;
                    });
                })
                .catch(error => {
                    console.error('Error loading leads:', error);
                    entitySelect.innerHTML = '<option value="">Error loading leads</option>';
                });
        } else {
            entitySelect.innerHTML = '<option value="">Select Customer</option>';
        }
    });

    // Entity selection handler
    document.getElementById('entity_select').addEventListener('change', function() {
        const selectedValue = this.value;
        const entityType = document.getElementById('entity_type').value;
        const customerIdField = document.getElementById('customer_id');
        const leadIdField = document.getElementById('lead_id');

        if (entityType === 'customer') {
            customerIdField.value = selectedValue;
            leadIdField.value = '';
        } else if (entityType === 'lead') {
            leadIdField.value = selectedValue;
            customerIdField.value = '';
        }
    });

    $(document).ready(function() {
        // Add initial item
        addItem();

        // Initialize discount change listeners
        $('#discount_value, #discount_type').on('change input', function() {
            calculateTotals();
        });

        // Form validation
        $('#quotationForm').on('submit', function(e) {
            const hasCustomerOrLead = $('#customer_id').val() || $('#lead_id').val();
            const hasItems = $('#itemsTableBody tr').length > 0;

            if (!hasCustomerOrLead) {
                e.preventDefault();
                alert('Please select a customer or lead.');
                return false;
            }

            if (!hasItems) {
                e.preventDefault();
                alert('Please add at least one item.');
                return false;
            }

            // Validate that all items have required fields
            let hasInvalidItems = false;
            $('#itemsTableBody tr').each(function() {
                const itemType = $(this).find('.item-type').val();
                const itemId = $(this).find('.item-select').val();
                const quantity = $(this).find('.quantity').val();
                const unitPrice = $(this).find('.unit-price').val();

                if (!itemType || !itemId || !quantity || !unitPrice) {
                    hasInvalidItems = true;
                    return false;
                }
            });

            if (hasInvalidItems) {
                e.preventDefault();
                alert('Please fill in all required fields for each item.');
                return false;
            }
        });
    });
</script>

@endsection