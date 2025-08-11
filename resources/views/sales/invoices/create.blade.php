@extends('layouts.app')

@section('title', 'Create Invoice')

@section('content')
<div class="page-header">
    <h1 class="page-title">Create New Invoice</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sales.invoices.index') }}">Invoices</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<form action="{{ route('sales.invoices.store') }}" method="POST" id="invoiceForm">
    @csrf
    <div class="row">
        <div class="col-md-12">
            <!-- Header Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Invoice Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                            <input type="date" name="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror"
                                value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                            @error('invoice_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->company_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reference No</label>
                            <input type="text" name="reference_no" class="form-control" value="{{ old('reference_no') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">PO Number</label>
                            <input type="text" name="po_no" class="form-control" value="{{ old('po_no') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Currency</label>
                            <input type="text"
                                name="currency"
                                class="form-control"
                                value="{{ app(\App\Helpers\SettingsHelper::class)->getSettingCurrency('currency') }}" disabled>
                        </div>


                        <input type="hidden" name="exchange_rate" class="form-control" value="1"
                            required min="0" step="0.0001">

                        <div class="col-md-3">
                            <label class="form-label">Payment Terms (Days) <span class="text-danger">*</span></label>
                            <input type="number" name="payment_terms" class="form-control" value="{{ old('payment_terms', '30') }}"
                                required min="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Discount Type</label>
                            <select name="discount_type" class="form-select">
                                <option value="amount" {{ old('discount_type', 'amount') == 'amount' ? 'selected' : '' }}>Fixed Amount</option>
                                <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                            </select>
                        </div>
                    </div>

                    @if($quotation)
                    <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        This invoice is being created from Quotation: <strong>{{ $quotation->quotation_no }}</strong>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Items Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Items</h5>
                    @if(!$quotation)
                    <button type="button" class="btn btn-primary btn-sm" onclick="addItem()">
                        <i class="fas fa-plus me-2"></i>Add Item
                    </button>
                    @endif
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
                                    @if(!$quotation)
                                    <th width="50">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                @if($quotation && $quotation->items)
                                @foreach($quotation->items as $index => $item)
                                <tr>
                                    <td>
                                        <input type="hidden" name="items[{{ $index }}][item_type]" value="{{ $item->item_type }}">
                                        <span class="badge bg-secondary">{{ ucfirst($item->item_type) }}</span>
                                    </td>
                                    <td>
                                        <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $item->item_id }}">
                                        <strong>{{ $item->item_name }}</strong><br>
                                        <small class="text-muted">{{ $item->description }}</small>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity"
                                            value="{{ $item->quantity }}" required min="0.01" step="0.01" onchange="calculateRowTotal({{ $index }})">
                                    </td>
                                    <td style="display: none;">
                                        <input type="hidden" name="items[{{ $index }}][uom_id]" value="{{ $item->uom_id }}">
                                        {{ $item->uom->name ?? '-' }}
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][unit_price]" class="form-control unit-price"
                                            value="{{ $item->unit_price }}" required min="0" step="0.01" onchange="calculateRowTotal({{ $index }})">
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][discount_value]" class="form-control discount-value"
                                            value="{{ $item->discount_value }}" min="0" step="0.01" onchange="calculateRowTotal({{ $index }})">
                                        <input type="hidden" name="items[{{ $index }}][discount_type]" value="{{ $item->discount_type }}">
                                    </td>
                                    <td>
                                        <input type="hidden" name="items[{{ $index }}][tax_id]" value="{{ $item->tax_id }}">
                                        {{ $item->tax ? $item->tax->display_name : 'No Tax' }}
                                    </td>
                                    <td>
                                        <span class="row-total">{{ app(\App\Helpers\SettingsHelper::class)->getSettingCurrency('country') }} {{ number_format($item->total_amount, 2) }}</span>
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="{{ $quotation ? '5' : '6' }}" class="text-end"><strong>Subtotal:</strong></td>
                                    <td><strong id="subtotalAmount">{{ app(\App\Helpers\SettingsHelper::class)->getSettingCurrency('country') }} {{ $quotation ? number_format($quotation->subtotal, 2) : '0.00' }}</strong></td>
                                    @if(!$quotation)<td></td>@endif
                                </tr>
                                <tr>
                                    <td colspan="{{ $quotation ? '5' : '6' }}" class="text-end"><strong>Tax Amount:</strong></td>
                                    <td><strong id="taxAmount">{{ app(\App\Helpers\SettingsHelper::class)->getSettingCurrency('country') }} {{ $quotation ? number_format($quotation->tax_amount, 2) : '0.00' }}</strong></td>
                                    @if(!$quotation)<td></td>@endif
                                </tr>
                                <tr>
                                    <td colspan="{{ $quotation ? '5' : '6' }}" class="text-end"><strong>Total Amount:</strong></td>
                                    <td><strong id="totalAmount">{{ app(\App\Helpers\SettingsHelper::class)->getSettingCurrency('country') }} {{ $quotation ? number_format($quotation->total_amount, 2) : '0.00' }}</strong></td>
                                    @if(!$quotation)<td></td>@endif
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Notes</h5>
                </div>
                <div class="card-body">
                    <textarea name="notes" class="form-control" rows="3" placeholder="Invoice notes...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Actions -->

            <div class="text-start">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Create Invoice
                </button>
                <a href="{{ route('sales.invoices.index') }}" class="btn btn-outline-secondary me-2">
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
            <span class="row-total">{{ app(\App\Helpers\SettingsHelper::class)->getSettingCurrency('country') }} 0.00</span>
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
    const taxSelect = taxCell.querySelector('.tax-select');
    const taxInputs = taxCell.querySelector('.tax-inputs');

    itemSelect.innerHTML = '<option value="">Loading...</option>';
    
    // Toggle tax display based on item type
    if (itemType === 'package') {
        // Show tax input fields, hide dropdown
        taxSelect.style.display = 'none';
        taxInputs.style.display = 'block';
        // Clear dropdown selection
        taxSelect.value = '';
    } else {
        // Show tax dropdown, hide input fields
        taxSelect.style.display = 'block';
        taxInputs.style.display = 'none';
        // Clear input fields
        taxInputs.querySelector('.tax-rate').value = '';
        taxInputs.querySelector('.tax-name').value = '';
        
        // Load taxes if not already loaded
        if (taxSelect.children.length <= 1) {
            loadTaxes(taxSelect);
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

function loadTaxes(selectElement, itemType = 'both') {
    // Build the URL with the item type parameter
    const url = `/sales/invoices/taxes/for-dropdown?type=${itemType}`;
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Keep existing selection if any
            const currentValue = selectElement.value;
            selectElement.innerHTML = '<option value="">No Tax</option>';
            
            data.forEach(tax => {
                const selected = tax.id == currentValue ? 'selected' : '';
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

    const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
    const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
    const discountValue = parseFloat(row.querySelector('.discount-value').value) || 0;
    
    // Check if using tax dropdown or input fields
    const taxSelect = row.querySelector('.tax-select');
    const taxRateInput = row.querySelector('.tax-rate');
    
    let taxRate = 0;
    if (taxSelect.style.display !== 'none' && taxSelect.value) {
        // Using tax dropdown
        taxRate = parseFloat(taxSelect.selectedOptions[0]?.dataset.rate || 0);
    } else if (taxRateInput.style.display !== 'none') {
        // Using tax input field
        taxRate = parseFloat(taxRateInput.value || 0);
    }

    const lineTotal = quantity * unitPrice;
    const discountAmount = (lineTotal * discountValue) / 100;
    const afterDiscount = lineTotal - discountAmount;
    const taxAmount = (afterDiscount * taxRate) / 100;
    const rowTotal = afterDiscount + taxAmount;

    row.querySelector('.row-total').textContent = `RM ${rowTotal.toFixed(2)}`;

    calculateTotals();
}

// Modified calculateTotals function to handle both tax types
function calculateTotals() {
    let subtotal = 0;
    let totalTax = 0;

    document.querySelectorAll('#itemsTableBody tr').forEach((row, index) => {
        const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
        const discountValue = parseFloat(row.querySelector('.discount-value').value) || 0;
        
        // Check if using tax dropdown or input fields
        const taxSelect = row.querySelector('.tax-select');
        const taxRateInput = row.querySelector('.tax-rate');
        
        let taxRate = 0;
        if (taxSelect.style.display !== 'none' && taxSelect.value) {
            // Using tax dropdown
            taxRate = parseFloat(taxSelect.selectedOptions[0]?.dataset.rate || 0);
        } else if (taxRateInput && taxRateInput.style.display !== 'none') {
            // Using tax input field
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

    document.getElementById('subtotalAmount').textContent = `RM ${subtotal.toFixed(2)}`;
    document.getElementById('taxAmount').textContent = `RM ${totalTax.toFixed(2)}`;
    document.getElementById('totalAmount').textContent = `RM ${total.toFixed(2)}`;
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