@extends('layouts.app')

@section('title', 'Edit Quotation')

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Quotation #{{ $quotation->quotation_no }}</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sales.quotations.index') }}">Quotations</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sales.quotations.show', $quotation) }}">{{ $quotation->quotation_no }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form action="{{ route('sales.quotations.update', $quotation) }}" method="POST" id="quotationForm" novalidate>
    @csrf
    @method('PUT')
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
                                value="{{ old('quotation_date', $quotation->quotation_date) }}" required>
                            @error('quotation_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Valid Until <span class="text-danger">*</span></label>
                            <input type="date" name="valid_until" class="form-control @error('valid_until') is-invalid @enderror"
                                value="{{ old('valid_until', $quotation->valid_until) }}" required>
                            @error('valid_until')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="entity_type" class="form-label">Customer Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('entity_type') is-invalid @enderror"
                                name="entity_type" id="entity_type" required>
                                <option value="">Select Customer Type</option>
                                <option value="lead" {{ old('entity_type', $quotation->lead_id ? 'lead' : '') == 'lead' ? 'selected' : '' }}>Lead</option>
                                <option value="customer" {{ old('entity_type', $quotation->customer_id ? 'customer' : '') == 'customer' ? 'selected' : '' }}>Customer</option>
                            </select>
                            @error('entity_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="entity_select" class="form-label">Customer <span class="text-danger">*</span></label>
                            <select class="form-select" name="entity_select" id="entity_select" required>
                                <option value="">Select Customer</option>
                                @if($quotation->customer)
                                <option value="{{ $quotation->customer->id }}" selected>{{ $quotation->customer->customer_code }} - {{ $quotation->customer->company_name }}</option>
                                @endif
                                @if($quotation->lead)
                                <option value="{{ $quotation->lead->id }}" selected>{{ $quotation->lead->lead_no }} - {{ $quotation->lead->company_name ?? $quotation->lead->contact_person }}</option>
                                @endif
                            </select>

                            <!-- Hidden fields that will be populated based on selection -->
                            <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id', $quotation->customer_id) }}" class="customer_lead">
                            <input type="hidden" name="lead_id" id="lead_id" value="{{ old('lead_id', $quotation->lead_id) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Reference No</label>
                            <input type="text" name="reference_no" class="form-control @error('reference_no') is-invalid @enderror"
                                value="{{ old('reference_no', $quotation->reference_no) }}" maxlength="100">
                            @error('reference_no')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                                value="{{ old('subject', $quotation->subject) }}" maxlength="500">
                            @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
   <div class="form-check">
    <input class="form-check-input" type="checkbox" name="cloud_server_hosting" id="cloud_server_hosting" value="1"
        {{ isset($quotation) ? ($quotation->cloud_server_hosting == 1 ? 'checked' : '') : 'checked' }}>
    <label class="form-check-label" for="cloud_server_hosting">Cloud Server & Hosting</label>
</div>

                        <div class="col-md-3">
                            <label class="form-label">Currency</label>
                            <input type="text"
                                name="currency_display"
                                class="form-control"
                                value="{{ $quotation->currency }}" disabled>
                            <input type="hidden" name="currency" value="{{ $quotation->currency }}">
                        </div>

                        <input type="hidden" name="exchange_rate" value="{{ $quotation->exchange_rate }}">

                        <div class="col-md-3">
                            <label class="form-label">Discount Type</label>
                            <select name="discount_type" class="form-select @error('discount_type') is-invalid @enderror" id="discount_type">
                                <option value="amount" {{ old('discount_type', $quotation->discount_type) == 'amount' ? 'selected' : '' }}>Fixed Amount</option>
                                <option value="percentage" {{ old('discount_type', $quotation->discount_type) == 'percentage' ? 'selected' : '' }}>Percentage</option>
                            </select>
                            @error('discount_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Discount Value</label>
                            <input type="number" name="discount_value" class="form-control @error('discount_value') is-invalid @enderror"
                                value="{{ old('discount_value', $quotation->discount_value) }}" min="0" step="0.01" id="discount_value">
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
                    <h5 class="mb-0">Add Package (Optional)</h5>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="clearPackageSelection()">
                        <i class="fas fa-times me-1"></i>Clear Selection
                    </button>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Select Package</label>
                            <select class="form-select" id="packageSelect" onchange="selectPackage(this)">
                                <option value="">Choose a package...</option>
                                @if($quotation->items->where('item_type','package')->first())
                                @php $pkgItem = $quotation->items->where('item_type','package')->first(); @endphp
                                <option value="{{ $pkgItem->package->id }}" selected>
                                    {{ $pkgItem->package->name }} - {{ $quotation->currency }} {{ number_format($pkgItem->package->package_price,2) }}
                                </option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6" id="packageInfo" style="display: none;">
                            <div class="package-details" style="display: none;">
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
                    <div id="packageItemsPreview"  class="mt-3">
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
                                <tbody id="packageItemsBody"></tbody>
                            </table>
                        </div>
                        <div class="text-end mt-2" style="display: none;">
                            <button type="button" class="btn btn-success" onclick="addPackageToQuotation()">
                                <i class="fas fa-plus me-2"></i>Add Package to Quotation
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Section -->
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
                                    <th width="130">Type</th>
                                    <th>Item</th>
                                    <th width="80">Qty</th>
                                    <th width="80" style="display:none;">UOM</th>
                                    <th width="120">Unit Price</th>
                                    <th width="80">Disc %</th>
                                    <th width="160">Tax</th>
                                    <th width="120">Total</th>
                                    <th width="50">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                @foreach($quotation->items as $index => $item)
                                <tr>
                                    <td>
                                        @if($item->item_type === 'product' || $item->item_type === 'service')
                                        <select name="items[{{ $index }}][item_type]" class="form-select item-type" required onchange="loadItems(this, {{ $index }})">
                                            <option value="">Select</option>
                                            <option value="product" {{ $item->item_type == 'product' ? 'selected' : '' }}>Product</option>
                                            <option value="service" {{ $item->item_type == 'service' ? 'selected' : '' }}>Service</option>
                                        </select>

                                        @else
                                        <select name="items[{{ $index }}][item_type]" class="form-select item-type" required onchange="loadItems(this, {{ $index }})">
                                            <option value="">Select</option>
                                            <option value="product" {{ $item->item_type == 'product' ? 'selected' : '' }}>Product</option>
                                            <option value="service" {{ $item->item_type == 'service' ? 'selected' : '' }}>Service</option>
                                        </select>

                                        @endif
                                        <div class="invalid-feedback item-type-error" style="display:none;"></div>
                                    </td>

                                    <td>
                                        @if($item->item_type == 'product' && $item->product)
                                        <select name="items[{{ $index }}][item_id]" class="form-select item-select" required onchange="updateItemDetails(this, {{ $index }})">
                                            <option value="">Select Item</option>
                                            <option value="{{ $item->product->id }}" selected
                                                data-price="{{ $item->product->selling_price }}"
                                                data-uom="{{ $item->product->uom_id }}">
                                                {{ $item->product->name }}
                                            </option>
                                        </select>
                                        @elseif($item->item_type == 'service' && $item->service)
                                        <select name="items[{{ $index }}][item_id]" class="form-select item-select" required onchange="updateItemDetails(this, {{ $index }})">
                                            <option value="">Select Item</option>
                                            <option value="{{ $item->service->id }}" selected
                                                data-price="{{ $item->service->base_price }}">
                                                {{ $item->service->name }}
                                            </option>
                                        </select>

                                        @else
                                        {{-- Fallback: empty select for product/service only --}}
                                        <select name="items[{{ $index }}][item_id]" class="form-select item-select" required onchange="updateItemDetails(this, {{ $index }})">
                                            <option value="">Select Item</option>
                                        </select>
                                        @endif
                                        <div class="invalid-feedback item-id-error" style="display:none;"></div>
                                    </td>

                                    <td>
                                        <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity"
                                            required min="1" step="1" value="{{ $item->quantity }}"
                                            onchange="calculateRowTotal({{ $index }})">
                                        <div class="invalid-feedback quantity-error" style="display:none;"></div>
                                    </td>

                                    <td style="display:none;">
                                        <select name="items[{{ $index }}][uom_id]" class="form-select uom-select">
                                            <option value="">-</option>
                                            @if($item->uom)
                                            <option value="{{ $item->uom->id }}" selected>{{ $item->uom->name }}</option>
                                            @endif
                                        </select>
                                    </td>

                                    <td>
                                        <input type="number" name="items[{{ $index }}][unit_price]" class="form-control unit-price"
                                            required min="0" step="0.01" value="{{ $item->unit_price }}"
                                            onchange="calculateRowTotal({{ $index }})">
                                        <div class="invalid-feedback unit-price-error" style="display:none;"></div>
                                    </td>

                                    <td>
                                        <input type="number" name="items[{{ $index }}][discount_value]" class="form-control discount-value"
                                            min="0" step="0.01" value="{{ $item->discount_value }}"
                                            onchange="calculateRowTotal({{ $index }})">
                                        <input type="hidden" name="items[{{ $index }}][discount_type]" value="{{ $item->discount_type }}">
                                        <div class="invalid-feedback discount-error" style="display:none;"></div>
                                    </td>

                                    <td class="tax-cell">

                                        {{-- Product/Service: dropdown --}}
                                        <select name="items[{{ $index }}][tax_id]" class="form-select tax-select" onchange="calculateRowTotal({{ $index }})">
                                            <option value="">No Tax</option>
                                            @if($item->tax)
                                            <option value="{{ $item->tax->id }}" selected data-rate="{{ $item->tax->percent }}">
                                                {{ $item->tax->name }} ({{ $item->tax->percent }}%)
                                            </option>
                                            @endif
                                        </select>
                                        <div class="tax-inputs" style="display:none;">
                                            <input type="number" name="items[{{ $index }}][tax_rate]" class="form-control tax-rate mb-1"
                                                placeholder="Tax Rate %" min="0" step="0.01" onchange="calculateRowTotal({{ $index }})">
                                        </div>

                                        <div class="invalid-feedback tax-error" style="display:none;"></div>
                                    </td>

                                    <td>
                                        <span class="row-total">{{ $quotation->currency }} {{ number_format($item->total_amount, 2) }}</span>
                                    </td>

                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Subtotal:</strong></td>
                                    <td><strong id="subtotalAmount">{{ $quotation->currency }} {{ number_format($quotation->subtotal_amount, 2) }}</strong></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Tax Amount:</strong></td>
                                    <td><strong id="taxAmount">{{ $quotation->currency }} {{ number_format($quotation->tax_amount, 2) }}</strong></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Total Amount:</strong></td>
                                    <td><strong id="totalAmount">{{ $quotation->currency }} {{ number_format($quotation->total_amount, 2) }}</strong></td>
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
                                rows="4">{{ old('terms_conditions', $quotation->terms_conditions) }}</textarea>
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
                                rows="4">{{ old('internal_notes', $quotation->internal_notes) }}</textarea>
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
                    <i class="fas fa-save me-2"></i>Update Quotation
                </button>
                <a href="{{ route('sales.quotations.show', $quotation) }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </div>
    </div>
</form>

<style>
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
    let itemIndex = {{count($quotation->items)}};
    let selectedPackage = null;

    $(document).ready(function() {
        loadPackages();

        // Load taxes for existing tax selects
        document.querySelectorAll('.tax-select').forEach(select => {
            if (select.style.display !== 'none') {
                loadTaxes(select);
            }
        });

        // Calculate initial totals
        calculateTotals();

        // Entity type and selection handling
        const entityTypeSelect = document.getElementById('entity_type');
        const entityIdSelect = document.getElementById('entity_select');
        const customerIdField = document.getElementById('customer_id');
        const leadIdField = document.getElementById('lead_id');

        entityTypeSelect.addEventListener('change', function() {
            const entityType = this.value;
            entityIdSelect.innerHTML = '<option value="">Loading...</option>';
            customerIdField.value = '';
            leadIdField.value = '';

            if (entityType === 'customer') {
                fetch('/api/customers')
                    .then(response => response.json())
                    .then(data => {
                        entityIdSelect.innerHTML = '<option value="">Select Customer</option>';
                        data.forEach(customer => {
                            entityIdSelect.innerHTML += `<option value="${customer.id}">${customer.customer_code} - ${customer.company_name}</option>`;
                        });
                    });
            } else if (entityType === 'lead') {
                fetch('/api/leads')
                    .then(response => response.json())
                    .then(data => {
                        entityIdSelect.innerHTML = '<option value="">Select Lead</option>';
                        data.forEach(lead => {
                            entityIdSelect.innerHTML += `<option value="${lead.id}">${lead.lead_no} - ${lead.company_name || lead.contact_person}</option>`;
                        });
                    });
            }
        });

        // Handle entity selection
        entityIdSelect.addEventListener('change', function() {
            const selectedValue = this.value;
            const entityType = entityTypeSelect.value;
            if (entityType === 'customer') {
                customerIdField.value = selectedValue;
                leadIdField.value = '';
            } else if (entityType === 'lead') {
                leadIdField.value = selectedValue;
                customerIdField.value = '';
            }
        });

        // Form validation
        $('#quotationForm').on('submit', function(e) {
            let valid = true;
            $('.error').remove();

            if (!$('input[name="quotation_date"]').val()) {
                $('input[name="quotation_date"]').after('<span class="text-danger error">Quotation Date is required</span>');
                valid = false;
            }
            if (!$('input[name="valid_until"]').val()) {
                $('input[name="valid_until"]').after('<span class="text-danger error">Valid Until Date is required</span>');
                valid = false;
            }
            const hasCustomerOrLead = $('#customer_id').val() || $('#lead_id').val();
            if (!hasCustomerOrLead) {
                $('.customer_lead').after('<span class="text-danger error">Please select a customer or lead</span>');
                valid = false;
            }

            const hasItems = $('#itemsTableBody tr').length > 0;
            if (!hasItems) {
                $('#itemsTableBody').after('<span class="text-danger error d-block mt-2">Please add at least one item</span>');
                valid = false;
            }

            $('#itemsTableBody tr').each(function() {
                const isPackage = $(this).find('input[name$="[item_type]"][value="package"]').length > 0;
                const itemType = isPackage ? 'package' : $(this).find('.item-type').val();
                const itemId = $(this).find('select.item-select').prop('disabled') ? $(this).find('input[name$="[item_id]"]').val() : $(this).find('.item-select').val();
                const qty = parseFloat($(this).find('.quantity').val());
                const price = parseFloat($(this).find('.unit-price').val());

                if (!itemType) {
                    (isPackage ? $(this).find('span.badge') : $(this).find('.item-type'))
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

        // If Blade preselected a package at top, auto-show its details
        const preselectedPkg = document.getElementById('packageSelect')?.value;
        if (preselectedPkg) {
            selectPackage(document.getElementById('packageSelect'));
        }
    });

    // Load packages into dropdown
    function loadPackages() {
        fetch('/quotations/get-items?type=package')
            .then(response => response.json())
            .then(data => {
                const packageSelect = document.getElementById('packageSelect');
                const current = packageSelect.value || (packageSelect.options[1]?.selected ? packageSelect.options[1].value : '');
                const hasBladeSelected = packageSelect.options.length > 1 && packageSelect.options[1].selected;

                // Keep placeholder and possibly pre-rendered selected option
                packageSelect.innerHTML = '<option value="">Choose a package...</option>' + (hasBladeSelected ? packageSelect.options[1].outerHTML : '');

                data.forEach(p => {
                    if (hasBladeSelected && String(packageSelect.options[1].value) === String(p.id)) return;
                    const opt = document.createElement('option');
                    const priceText = p.formatted_price || ('{{ $quotation->currency }} ' + parseFloat(p.package_price || p.price || 0).toFixed(2));
                    opt.value = p.id;
                    opt.textContent = `${p.name} - ${priceText}`;
                    if (String(p.id) === String(current)) opt.selected = true;
                    packageSelect.appendChild(opt);
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
        }

        fetch(`/quotations/get-package-details/${packageId}`)
            .then(response => response.json())
            .then(data => {
                selectedPackage = data;
               
                document.getElementById('packageName').textContent = data.name;
                document.getElementById('packageDescription').textContent = data.description || 'No description available';
                document.getElementById('packagePrice').textContent = `{{ $quotation->currency }} ${parseFloat(data.package_price).toFixed(2)}`;
                document.getElementById('packageItemsCount').textContent = `${(data.items || []).length} items`;
                packageInfo.style.display = 'block';

                const tbody = document.getElementById('packageItemsBody');
                tbody.innerHTML = '';
                
                (data.items || []).forEach(item => {
                    const row = document.createElement('tr');
                    const total = parseFloat(item.amount) * parseFloat(item.quantity);
                    row.innerHTML = `
                        <td>${item.item_name}</td>
                        <td>${item.quantity}</td>
                        <td>{{ $quotation->currency }} ${parseFloat(item.amount).toFixed(2)}</td>
                        <td>{{ $quotation->currency }} ${total.toFixed(2)}</td>
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

    // Add package to quotation (creates a locked "package" row)
    function addPackageToQuotation() {
        if (!selectedPackage) {
            alert('Please select a package first.');
            return;
        }

        const tbody = document.getElementById('itemsTableBody');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <input type="hidden" name="items[${itemIndex}][item_type]" value="package">
                <span class="badge bg-secondary">Package</span>
            </td>
            <td>
                <select class="form-select item-select" disabled>
                    <option selected>${selectedPackage.name}</option>
                </select>
                <input type="hidden" name="items[${itemIndex}][item_id]" value="${selectedPackage.id}">
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity" required min="1" step="1" value="1" onchange="calculateRowTotal(${itemIndex})">
                <div class="invalid-feedback quantity-error" style="display: none;"></div>
            </td>
            <td style="display: none;">
                <select name="items[${itemIndex}][uom_id]" class="form-select uom-select">
                    <option value="">-</option>
                </select>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][unit_price]" class="form-control unit-price" required min="0" step="0.01" value="${parseFloat(selectedPackage.package_price).toFixed(2)}" onchange="calculateRowTotal(${itemIndex})">
                <div class="invalid-feedback unit-price-error" style="display: none;"></div>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][discount_value]" class="form-control discount-value" min="0" step="0.01" value="0" onchange="calculateRowTotal(${itemIndex})">
                <input type="hidden" name="items[${itemIndex}][discount_type]" value="percentage">
                <div class="invalid-feedback discount-error" style="display: none;"></div>
            </td>
            <td class="tax-cell">
                <select name="items[${itemIndex}][tax_id]" class="form-select tax-select" style="display: none;" onchange="calculateRowTotal(${itemIndex})">
                    <option value="">No Tax</option>
                </select>
                <div class="tax-inputs">
                    <input type="number" name="items[${itemIndex}][tax_rate]" class="form-control tax-rate mb-1" placeholder="Tax Rate %" min="0" step="0.01" onchange="calculateRowTotal(${itemIndex})">
                </div>
                <div class="invalid-feedback tax-error" style="display: none;"></div>
            </td>
            <td>
                <span class="row-total">{{ $quotation->currency }} ${parseFloat(selectedPackage.package_price).toFixed(2)}</span>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
        itemIndex++;
        calculateTotals();
        clearPackageSelection();
    }

    // Clear package selection UI (does not remove already-added rows)
    function clearPackageSelection() {
        document.getElementById('packageSelect').value = '';
        document.getElementById('packageInfo').style.display = 'none';
        document.getElementById('packageItemsPreview').style.display = 'none';
        selectedPackage = null;
    }

    // Add new product/service row (no package here)
    // Add new product/service row (no package here)
    function addItem() {
        const tbody = document.getElementById('itemsTableBody');
        const row = document.createElement('tr');
        row.innerHTML = `
        <td>
            <select name="items[${itemIndex}][item_type]" class="form-select item-type" required onchange="loadItems(this, ${itemIndex})">
                <option value="">Select</option>
                <option value="product">Product</option>
                <option value="service">Service</option>
            </select>
            <div class="invalid-feedback item-type-error" style="display:none;"></div>
        </td>
        <td>
            <select name="items[${itemIndex}][item_id]" class="form-select item-select" required onchange="updateItemDetails(this, ${itemIndex})">
                <option value="">Select Item</option>
            </select>
            <div class="invalid-feedback item-id-error" style="display:none;"></div>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity" required min="1" step="1" value="1" onchange="calculateRowTotal(${itemIndex})">
            <div class="invalid-feedback quantity-error" style="display:none;"></div>
        </td>
        <td style="display:none;">
            <select name="items[${itemIndex}][uom_id]" class="form-select uom-select">
                <option value="">-</option>
            </select>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][unit_price]" class="form-control unit-price" required min="0" step="0.01" value="0" onchange="calculateRowTotal(${itemIndex})">
            <div class="invalid-feedback unit-price-error" style="display:none;"></div>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][discount_value]" class="form-control discount-value" min="0" step="0.01" value="0" onchange="calculateRowTotal(${itemIndex})">
            <input type="hidden" name="items[${itemIndex}][discount_type]" value="percentage">
            <div class="invalid-feedback discount-error" style="display:none;"></div>
        </td>
        <td class="tax-cell">
            <select name="items[${itemIndex}][tax_id]" class="form-select tax-select" onchange="calculateRowTotal(${itemIndex})">
                <option value="">No Tax</option>
            </select>
            <div class="tax-inputs" style="display:none;">
                <input type="number" name="items[${itemIndex}][tax_rate]" class="form-control tax-rate mb-1" placeholder="Tax Rate %" min="0" step="0.01" onchange="calculateRowTotal(${itemIndex})">
            </div>
            <div class="invalid-feedback tax-error" style="display:none;"></div>
        </td>
        <td>
            <span class="row-total">{{ $quotation->currency }} 0.00</span>
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

    // Load items based on type (product/service only)
    function loadItems(selectElement, index) {
        const itemType = selectElement.value;
        const itemSelect = selectElement.closest('tr').querySelector('.item-select');
        const taxCell = selectElement.closest('tr').querySelector('.tax-cell');
        const taxSelect = taxCell.querySelector('.tax-select');
        const taxInputs = taxCell.querySelector('.tax-inputs');

        itemSelect.innerHTML = '<option value="">Loading...</option>';

        // Always dropdown for product/service
        taxSelect.style.display = 'block';
        taxInputs.style.display = 'none';
        const freeRate = taxInputs.querySelector('.tax-rate');
        if (freeRate) freeRate.value = '';

        loadTaxes(taxSelect, itemType);

        if (itemType === 'product' || itemType === 'service') {
            fetch(`/quotations/get-items?type=${itemType}`)
                .then(r => r.json())
                .then(data => {
                    itemSelect.innerHTML = '<option value="">Select Item</option>';
                    data.forEach(it => {
                        itemSelect.innerHTML += `<option value="${it.id}" data-price="${it.price}" data-uom="${it.uom_id || ''}">${it.name}</option>`;
                    });
                })
                .catch(() => itemSelect.innerHTML = '<option value="">Error loading items</option>');
        } else {
            // No package here — keep empty
            itemSelect.innerHTML = '<option value="">Select Item</option>';
        }
    }

    function updateItemDetails(selectElement, index) {
        const option = selectElement.selectedOptions[0];
        if (option && option.dataset.price) {
            const row = selectElement.closest('tr');
            row.querySelector('.unit-price').value = option.dataset.price;
            calculateRowTotal(index);
        }
    }

    function loadTaxes(selectElement, itemType = 'both') {
        fetch(`/taxes/for-dropdown?type=${itemType}`)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                const currentValue = selectElement.value;
                selectElement.innerHTML = '<option value="">No Tax</option>';
                data.forEach(tax => {
                    const selected = currentValue == tax.id ? 'selected' : '';
                    selectElement.innerHTML += `<option value="${tax.id}" data-rate="${tax.percent}" ${selected}>${tax.name} (${tax.percent}%)</option>`;
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
        if (!row) {
            calculateTotals();
            return;
        }

        const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
        const discountValue = parseFloat(row.querySelector('.discount-value').value) || 0;

        const taxSelect = row.querySelector('.tax-select');
        const taxRateInput = row.querySelector('.tax-rate');

        let taxRate = 0;
        if (taxSelect && taxSelect.style.display !== 'none' && taxSelect.value) {
            taxRate = parseFloat(taxSelect.selectedOptions[0]?.dataset.rate || 0);
        } else if (taxRateInput && taxRateInput.style.display !== 'none') {
            taxRate = parseFloat(taxRateInput.value || 0);
        }

        const lineTotal = quantity * unitPrice;
        const discountAmount = (lineTotal * discountValue) / 100;
        const afterDiscount = lineTotal - discountAmount;
        const taxAmount = (afterDiscount * taxRate) / 100;
        const rowTotal = afterDiscount + taxAmount;

        row.querySelector('.row-total').textContent = `{{ $quotation->currency }} ${rowTotal.toFixed(2)}`;

        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;
        let totalTax = 0;

        document.querySelectorAll('#itemsTableBody tr').forEach((row) => {
            const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
            const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
            const discountValue = parseFloat(row.querySelector('.discount-value').value) || 0;

            const taxSelect = row.querySelector('.tax-select');
            const taxRateInput = row.querySelector('.tax-rate');
            const typeSel = $(this).find('.item-type');
            if (typeSel.length) {
                const t = typeSel.val();
                if (!['product', 'service'].includes(t)) {
                    typeSel.after('<span class="text-danger error">Invalid item type</span>');
                    valid = false;
                }
            }
            let taxRate = 0;
            if (taxSelect && taxSelect.style.display !== 'none' && taxSelect.value) {
                taxRate = parseFloat(taxSelect.selectedOptions[0]?.dataset.rate || 0);
            } else if (taxRateInput && taxRateInput.style.display !== 'none') {
                taxRate = parseFloat(taxRateInput.value || 0);
            }

            const lineTotal = quantity * unitPrice;
            const discountAmount = (lineTotal * discountValue) / 100;
            const afterDiscount = lineTotal - discountAmount;
            const taxAmount = (afterDiscount * taxRate) / 100;

            subtotal += afterDiscount;
            totalTax += taxAmount;
        });

        const total = subtotal + totalTax;

        document.getElementById('subtotalAmount').textContent = `{{ $quotation->currency }} ${subtotal.toFixed(2)}`;
        document.getElementById('taxAmount').textContent = `{{ $quotation->currency }} ${totalTax.toFixed(2)}`;
        document.getElementById('totalAmount').textContent = `{{ $quotation->currency }} ${total.toFixed(2)}`;
    }
</script>
@endsection