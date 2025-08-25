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

<form action="{{ route('sales.quotations.store') }}" method="POST" id="quotationForm" novalidate>
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
                            <input type="hidden" name="customer_id" id="customer_id" value="{{ $customer->id ?? '' }}" class="customer_lead">
                            <input type="hidden" name="lead_id" id="lead_id" value="{{ $lead->id ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label for="entity_select" class="form-label">Customer<span class="text-danger">*</span></label>
                            <select class="form-select" name="entity_select" id="entity_select" required>
                                <option value="">Select Customer</option>
                                @if(isset($lead))
                                <option value="{{ $lead->id }}"
                                    {{ old('lead_id') == $lead->id ? 'selected' : 'selected' }}>
                                    {{ $lead->lead_no }} - {{ $lead->entity_name }}
                                </option>
                                @endif

                                @if(isset($customer))
                                <option value="{{ $customer->id }}"
                                    {{ old('customer_id') == $customer->id ? 'selected' : 'selected' }}>
                                    {{ $customer->customer_code }} - {{ $customer->company_name }}
                                </option>
                                @endif
                            </select>

                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Reference No</label>
                            <input type="text" name="reference_no" class="form-control @error('reference_no') is-invalid @enderror"
                                value="{{ old('reference_no') }}" maxlength="100">
                            @error('reference_no')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                                value="{{ old('subject') }}" maxlength="500">
                            @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2" style="text-align: center; padding-top: 33px; padding-left: 28px;">
                                 <div class="form-check">
                <input type="checkbox" name="cloud_server_hosting" class="form-check-input @error('cloud_server_hosting') is-invalid @enderror" id="activeCheck" {{ old('cloud_server_hosting') ? 'checked' : '' }}>
                <label class="form-check-label" for="activeCheck">Cloud Server & Hosting</label>
                @error('cloud_server_hosting')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>
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
                            <select name="discount_type" id="discount_type" class="form-select @error('discount_type') is-invalid @enderror">
                                <option value="amount" {{ old('discount_type', 'amount') == 'amount' ? 'selected' : '' }}>Fixed Amount</option>
                                <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                            </select>
                            @error('discount_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Discount Value</label>
                            <input type="number" name="discount_value" id="discount_value"
                                class="form-control @error('discount_value') is-invalid @enderror"
                                value="{{ old('discount_value', '0') }}" min="0" step="0.01">
                            @error('discount_value')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Package Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Select Package (Optional)</h5>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="clearPackageSelection()">
                        <i class="fas fa-times me-1"></i>Clear Selection
                    </button>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Select Package</label>
                            <select class="form-select" id="packageSelect" onchange="selectPackage(this)" name="packageSelect">
                                <option value="">Choose a package...</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="packageInfo" style="display: none;">
                            <div class="package-details">
                                <h6 class="text-primary" id="packageName"></h6>
                                <p class="text-muted mb-1" id="packageDescription"></p>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Package Price:</small><br>
                                        <strong class="text-success" id="packagePrice"></strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Items Count:</small><br>
                                        <strong id="packageItemsCount"></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Package Items Preview -->
                    <div id="packageItemsPreview" style="display: none;" class="mt-3">
                        <h6 class="text-muted">Package includes:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th width="80">Qty</th>
                                        <th width="100">Unit Price</th>
                                        <th width="100">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="packageItemsBody">
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-2" style="display: none;">
                            <button type="button" class="btn btn-success" style="display: none;" onclick="addPackageToQuotation()">
                                <i class="fas fa-plus me-2"></i>Add Package to Quotation
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Items <span class="text-danger">*</span></h5>
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
                            <textarea name="terms_conditions" class="form-control @error('terms_conditions') is-invalid @enderror"
                                rows="4">{{ old('terms_conditions') }}</textarea>
                            @error('terms_conditions')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Internal Notes</h5>
                        </div>
                        <div class="card-body">
                            <textarea name="internal_notes" class="form-control @error('internal_notes') is-invalid @enderror"
                                rows="4">{{ old('internal_notes') }}</textarea>
                            @error('internal_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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

    .is-invalid {
        border-color: #dc3545;
    }

    .invalid-feedback {
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 0.25rem;
    }

    .package-details {
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 0.375rem;
        border: 1px solid #dee2e6;
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
    let selectedPackage = null;

    // Load packages on page load
    $(document).ready(function() {
        loadPackages();
        addItem();
    });

    // Load packages into dropdown
    function loadPackages() {
        fetch('/quotations/get-items?type=package')
            .then(response => response.json())
            .then(data => {
                const packageSelect = document.getElementById('packageSelect');
                packageSelect.innerHTML = '<option value="">Choose a package...</option>';
                data.forEach(package => {
                    packageSelect.innerHTML += `<option value="${package.id}">${package.name} - ${package.formatted_price}</option>`;
                });
            })
            .catch(error => {
                console.error('Error loading packages:', error);
            });
    }

    // Handle package selection
    function selectPackage(selectElement) {
        const packageId = selectElement.value;
        const packageInfo = document.getElementById('packageInfo');
        const packageItemsPreview = document.getElementById('packageItemsPreview');

        if (!packageId) {
            packageInfo.style.display = 'none';
            packageItemsPreview.style.display = 'none';
            selectedPackage = null;
            return;
        }

        // Fetch package details
        fetch(`/quotations/get-package-details/${packageId}`)
            .then(response => response.json())
            .then(data => {
                selectedPackage = data;

                // Update package info display
                document.getElementById('packageName').textContent = data.name;
                document.getElementById('packageDescription').textContent = data.description || 'No description available';
                document.getElementById('packagePrice').textContent = `RM ${parseFloat(data.package_price).toFixed(2)}`;
                document.getElementById('packageItemsCount').textContent = `${data.items.length} items`;
                packageInfo.style.display = 'none';

                // Update package items preview
                const tbody = document.getElementById('packageItemsBody');
                tbody.innerHTML = '';
                data.items.forEach(item => {
                    const row = document.createElement('tr');
                    const total = parseFloat(item.amount) * parseFloat(item.quantity);
                    row.innerHTML = `
                        <td>${item.item_name}</td>
                        <td>${item.quantity}</td>
                        <td>RM ${parseFloat(item.amount).toFixed(2)}</td>
                        <td>RM ${total.toFixed(2)}</td>
                    `;
                    tbody.appendChild(row);
                });
                packageItemsPreview.style.display = 'block';
            })
            .catch(error => {
                console.error('Error loading package details:', error);
                alert('Error loading package details. Please try again.');
            });
    }

    // Add package to quotation
    function addPackageToQuotation() {
        if (!selectedPackage) {
            alert('Please select a package first.');
            return;
        }

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
                <option value="${selectedPackage.id}" selected data-price="${selectedPackage.package_price}">${selectedPackage.name}</option>
            </select>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity" required min="1" step="1" value="1" onchange="calculateRowTotal(${itemIndex})">
        </td>
        <td style="display: none;">
            <select name="items[${itemIndex}][uom_id]" class="form-select uom-select">
                <option value="">-</option>
            </select>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][unit_price]" class="form-control unit-price" required min="0" step="0.01" value="${selectedPackage.package_price}" onchange="calculateRowTotal(${itemIndex})">
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][discount_value]" class="form-control discount-value" min="0" step="0.01" value="0" onchange="calculateRowTotal(${itemIndex})">
            <input type="hidden" name="items[${itemIndex}][discount_type]" value="percentage">
        </td>
        <td class="tax-cell">
            <!-- Tax Dropdown (default) -->
            <select name="items[${itemIndex}][tax_id]" class="form-select tax-select" style="display: none;" onchange="calculateRowTotal(${itemIndex})">
                <option value="">No Tax</option>
            </select>
            <!-- Tax Input Fields (for Package type) -->
            <div class="tax-inputs">
                <input type="number" name="items[${itemIndex}][tax_rate]" class="form-control tax-rate mb-1" placeholder="Tax Rate %" min="0" step="0.01" onchange="calculateRowTotal(${itemIndex})">
            </div>
        </td>
        <td>
            <span class="row-total">RM ${parseFloat(selectedPackage.package_price).toFixed(2)}</span>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
        tbody.appendChild(row);
        itemIndex++;

        calculateRowTotal(itemIndex - 1);
        clearPackageSelection();
    }

    // Clear package selection
    function clearPackageSelection() {
        document.getElementById('packageSelect').value = '';
        document.getElementById('packageInfo').style.display = 'none';
        document.getElementById('packageItemsPreview').style.display = 'none';
        selectedPackage = null;
    }

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
            <input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity" required min="1" step="1" value="1" onchange="calculateRowTotal(${itemIndex})">
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
        // Form validation
        $('#quotationForm').on('submit', function(e) {
            let valid = true;
            $('.error').remove(); // clear old errors

            // Quotation Date validation
            if (!$('input[name="quotation_date"]').val()) {
                $('input[name="quotation_date"]').after('<span class="text-danger error">Quotation Date is required</span>');
                valid = false;
            }
            // Valid Until Date validation
            if (!$('input[name="valid_until"]').val()) {
                $('input[name="valid_until"]').after('<span class="text-danger error">Valid Until Date is required</span>');
                valid = false;
            }
            // Check customer or lead
            const hasCustomerOrLead = $('#customer_id').val() || $('#lead_id').val();
            if (!hasCustomerOrLead) {
                $('.customer_lead').after('<span class="text-danger error">Please select a customer or lead</span>');
                valid = false;
            }

            // Check if at least one item exists
            const hasItems = $('#itemsTableBody tr').length > 0;
            if (!hasItems) {
                $('#itemsTableBody').after('<span class="text-danger error d-block mt-2">Please add at least one item</span>');
                valid = false;
            }

            // Row-wise validation
            $('#itemsTableBody tr').each(function() {
                const itemType = $(this).find('.item-type').val();
                const itemId = $(this).find('.item-select').val();
                const qty = parseFloat($(this).find('.quantity').val());
                const price = parseFloat($(this).find('.unit-price').val());

                if (!itemType) {
                    $(this).find('.item-type')
                        .after('<span class="text-danger error">Item type is required</span>');
                    valid = false;
                }
                if (!itemId) {
                    $(this).find('.item-select')
                        .after('<span class="text-danger error">Please select an item</span>');
                    valid = false;
                }
                if (!qty || qty <= 0) {
                    $(this).find('.quantity')
                        .after('<span class="text-danger error">Quantity must be greater than 0</span>');
                    valid = false;
                }
                if (!price || price <= 0) {
                    $(this).find('.unit-price')
                        .after('<span class="text-danger error">Unit price must be greater than 0</span>');
                    valid = false;
                }
            });

            if (!valid) e.preventDefault();
        });
    });
</script>

@endsection