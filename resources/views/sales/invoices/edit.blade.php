{{-- resources/views/sales/invoices/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Invoice')

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Invoice</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sales.invoices.index') }}">Invoices</a></li>
            <li class="breadcrumb-item active">Edit {{ $invoice->invoice_no }}</li>
        </ol>
    </nav>
</div>

@if($invoice->status === 'paid' || $invoice->status === 'cancelled')
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Warning:</strong> This invoice cannot be edited because it is {{ $invoice->status }}.
</div>
@endif

<form action="{{ route('sales.invoices.update', $invoice) }}" method="POST" id="invoiceForm">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-12">
            <!-- Header Information -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Invoice Details</h5>
                    <div>
                        <span class="badge bg-{{ $invoice->status_badge }} fs-6">{{ ucfirst($invoice->status) }}</span>
                        @if($invoice->quotation)
                        <span class="badge bg-info fs-6 ms-2">From Quotation: {{ $invoice->quotation->quotation_no }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Invoice No</label>
                            <input type="text" class="form-control" value="{{ $invoice->invoice_no }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                            <input type="date" name="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror"
                                value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" required>
                            @error('invoice_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $invoice->customer_id) == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->company_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Payment Terms (Days) <span class="text-danger">*</span></label>
                            <input type="number" name="payment_terms" class="form-control @error('payment_terms') is-invalid @enderror"
                                value="{{ old('payment_terms', $invoice->payment_terms) }}" required min="1">
                            @error('payment_terms')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Reference No</label>
                            <input type="text" name="reference_no" class="form-control" value="{{ old('reference_no', $invoice->reference_no) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">PO No</label>
                            <input type="text" name="po_no" class="form-control" value="{{ old('po_no', $invoice->po_no) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" value="{{ $invoice->due_date->format('Y-m-d') }}" readonly>
                        </div>
                        <div class="col-md-3">

                            <label class="form-label">Currency</label>
                            <input type="text"
                                name="currency"
                                class="form-control"
                                value="{{ app(\App\Helpers\SettingsHelper::class)->getSettingCurrency('currency') . ' ' . $invoice->currency }}"
                                disabled>

                        </div>


                        <input type="hidden" name="exchange_rate" class="form-control" value="1"
                             min="0" step="0.0001">

                        <div class="col-md-3">
                            <label class="form-label">Discount Type</label>
                            <select name="discount_type" class="form-select">
                                <option value="amount" {{ old('discount_type', $invoice->discount_type) == 'amount' ? 'selected' : '' }}>Fixed Amount</option>
                                <option value="percentage" {{ old('discount_type', $invoice->discount_type) == 'percentage' ? 'selected' : '' }}>Percentage</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Discount Value</label>
                            <input type="number" name="discount_value" class="form-control" value="{{ old('discount_value', $invoice->discount_value) }}"
                                min="0" step="0.01">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Invoice Items</h5>
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
                                    <th width="80" style="display: none;">Delivered</th>
                                    <th width="80" style="display: none;">UOM</th>
                                    <th width="120">Unit Price</th>
                                    <th width="80">Disc %</th>
                                    <th width="120">Tax</th>
                                    <th width="120">Total</th>
                                    <th width="50">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                @foreach($invoice->items as $index => $item)
                                <tr data-index="{{ $index }}">
                                    <td>
                                        <select name="items[{{ $index }}][item_type]" class="form-select item-type" required onchange="loadItems(this, {{ $index }})">
                                            <option value="">Select</option>
                                            <option value="product" {{ $item->item_type == 'product' ? 'selected' : '' }}>Product</option>
                                            <option value="service" {{ $item->item_type == 'service' ? 'selected' : '' }}>Service</option>
                                           
                                        </select>
                                    </td>
                                    <td>
                                    
                                        <select name="items[{{ $index }}][item_id]" class="form-select item-select" required onchange="updateItemDetails(this, {{ $index }})">
                                            <option value="{{ $item->item_id }}" selected>@if($item->item_type === 'product')
    {{ $item->product->name }}
@elseif($item->item_type === 'service')
    {{ $item->service->name }}
@elseif($item->item_type === 'package')
    {{ $item->package->name }}
@endif</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity" required
                                            min="1" step="1" value="{{ $item->quantity }}" onchange="calculateRowTotal({{ $index }})">
                                    </td>
                                    <td style="display: none;">
                                        <span class="badge bg-{{ $item->delivery_status_badge }}">{{ $item->delivered_quantity }}</span>
                                    </td>
                                    <td style="display: none;">
                                        <select name="items[{{ $index }}][uom_id]" class="form-select uom-select">
                                            @if($item->uom)
                                            <option value="{{ $item->uom->id }}" selected>{{ $item->uom->name }}</option>
                                            @else
                                            <option value="">-</option>
                                            @endif
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][unit_price]" class="form-control unit-price" required
                                            min="0" step="0.01" value="{{ $item->unit_price }}" onchange="calculateRowTotal({{ $index }})">
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][discount_value]" class="form-control discount-value"
                                            min="0" step="0.01" value="{{ $item->discount_value }}" onchange="calculateRowTotal({{ $index }})">
                                        <input type="hidden" name="items[{{ $index }}][discount_type]" value="{{ $item->discount_type }}">
                                    </td>
                                    <td>
                                        <select name="items[{{ $index }}][tax_id]" class="form-select tax-select" onchange="calculateRowTotal({{ $index }})">
                                            <option value="">No Tax</option>
                                            @if($item->tax)
                                            <option value="{{ $item->tax->id }}" data-rate="{{ $item->tax->percent }}" selected>
                                                {{ $item->tax->name }} ({{ $item->tax->percent }}%)
                                            </option>
                                            @endif
                                        </select>
                                    </td>
                                    <td>
                                        <span class="row-total">{{ app(\App\Helpers\SettingsHelper::class)->getSettingCurrency('country') }} {{ number_format($item->total_amount, 2) }}</span>
                                    </td>
                                    <td>
                                        @if($item->delivered_quantity == 0)
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @else
                                        <span class="text-muted" title="Cannot delete - partially delivered">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Subtotal:</strong></td>
                                    <td><strong id="subtotalAmount">{{ app(\App\Helpers\SettingsHelper::class)->getSettingCurrency('country') }} {{ number_format($invoice->subtotal, 2) }}</strong></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Discount:</strong></td>
                                    <td><strong id="discountAmount"> {{ app(\App\Helpers\SettingsHelper::class)->getSettingCurrency('country') }} {{ number_format($invoice->discount_amount, 2) }}</strong></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Tax Amount:</strong></td>
                                    <td><strong id="taxAmount">{{ app(\App\Helpers\SettingsHelper::class)->getSettingCurrency('country') }} {{ number_format($invoice->tax_amount, 2) }}</strong></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Total Amount:</strong></td>
                                    <td><strong id="totalAmount">{{ app(\App\Helpers\SettingsHelper::class)->getSettingCurrency('country') }} {{ number_format($invoice->total_amount, 2) }}</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Notes Section -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Notes</h5>
                        </div>
                        <div class="card-body">
                            <textarea name="notes" class="form-control" rows="4" placeholder="Enter any additional notes...">{{ old('notes', $invoice->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
<br>
            <!-- Actions -->
                <div class="text-start">
                   @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Invoice
                    </button>
                    @endif
                <a href="{{ route('sales.invoices.index') }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
                
        </div>
    </div>
</form>

<!-- Warning Modal for Delivered Items -->
<div class="modal fade" id="deliveryWarningModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Warning: Delivered Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This invoice contains items that have been partially or fully delivered.
                    Modifying these items may affect delivery records.
                </div>
                <p>Items with deliveries:</p>
                <ul id="deliveredItemsList"></ul>
                <p>Are you sure you want to continue?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="proceedWithUpdate()">Continue Update</button>
            </div>
        </div>
    </div>
</div>

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

    .badge {
        font-size: 0.75em;
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
    let itemIndex = {{ $invoice->items->count() }};
    let hasDeliveredItems = {{ $invoice->items->where('delivered_quantity', '>', 0)->count() > 0 ?'true':'false' }};

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
        const row = button.closest('tr');
        const deliveredQty = row.querySelector('.badge');

        if (deliveredQty && deliveredQty.textContent !== '0') {
            alert('Cannot remove item that has been delivered.');
            return;
        }

        row.remove();
        calculateTotals();
    }

    // Modified loadItems function to preserve existing selections
    function loadItems(selectElement, index, preserveSelection = true) {
        const itemType = selectElement.value;
        const itemSelect = selectElement.closest('tr').querySelector('.item-select');
        const row = selectElement.closest('tr');
        const taxCell = row.querySelector('.tax-cell');
        const taxSelect = taxCell ? taxCell.querySelector('.tax-select') : null;
        const taxInputs = taxCell ? taxCell.querySelector('.tax-inputs') : null;

        // Store current selection to preserve it
        const currentItemId = preserveSelection ? itemSelect.value : '';
        const currentItemText = preserveSelection && itemSelect.selectedOptions[0] ? itemSelect.selectedOptions[0].text : '';

        // Only show loading if we're actually going to load new items
        if (!preserveSelection || !currentItemId) {
            itemSelect.innerHTML = '<option value="">Loading...</option>';
        }
        
        // Toggle tax display based on item type
        if (taxCell) {
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
                
                // Load taxes if needed
                if (taxSelect) {
                    loadTaxes(taxSelect, itemType);
                }
            }
        }

        if (itemType === 'service' || itemType === 'product' ||  itemType === 'package') {
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

                    // Restore previous selection if preserving and item exists
                    if (preserveSelection && currentItemId) {
                        const existingOption = itemSelect.querySelector(`option[value="${currentItemId}"]`);
                        if (existingOption) {
                            existingOption.selected = true;
                        } else if (currentItemText) {
                            // Add the previous item back if it's not in the list
                            const preservedOption = document.createElement('option');
                            preservedOption.value = currentItemId;
                            preservedOption.text = currentItemText;
                            preservedOption.selected = true;
                            itemSelect.appendChild(preservedOption);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading items:', error);
                    itemSelect.innerHTML = '<option value="">Error loading items</option>';
                    
                    // If there was an error but we had a selected item, preserve it
                    if (preserveSelection && currentItemId && currentItemText) {
                        const preservedOption = document.createElement('option');
                        preservedOption.value = currentItemId;
                        preservedOption.text = currentItemText;
                        preservedOption.selected = true;
                        itemSelect.appendChild(preservedOption);
                    }
                });
        } else if (itemType === 'package') {
            // For packages, you might need a different endpoint
            itemSelect.innerHTML = '<option value="">Select Package</option>';
            
            // Restore selected package if it was a package
            if (preserveSelection && currentItemId && currentItemText) {
                const preservedOption = document.createElement('option');
                preservedOption.value = currentItemId;
                preservedOption.text = currentItemText;
                preservedOption.selected = true;
                itemSelect.appendChild(preservedOption);
            }
        } else {
            if (!preserveSelection || !currentItemId) {
                itemSelect.innerHTML = '<option value="">Select Item</option>';
            }
        }
    }

    function updateItemDetails(selectElement, index) {
        const option = selectElement.selectedOptions[0];
        if (option && option.dataset.price) {
            const row = selectElement.closest('tr');
            const unitPriceInput = row.querySelector('.unit-price');
            if (unitPriceInput) {
                unitPriceInput.value = option.dataset.price;
                calculateRowTotal(index);
            }
        }
    }

    function loadTaxes(selectElement, itemType = 'both') {
        const currentValue = selectElement.value;
        
        fetch(`/taxes/for-dropdown?type=${itemType}`)
            .then(response => response.json())
            .then(data => {
                selectElement.innerHTML = '<option value="">No Tax</option>';
                data.forEach(tax => {
                    const selected = tax.id == currentValue ? 'selected' : '';
                    selectElement.innerHTML += `<option value="${tax.id}" data-rate="${tax.percent}" ${selected}>${tax.percent} (${tax.percent}%)</option>`;
                });
            })
            .catch(error => {
                console.error('Error loading taxes:', error);
            });
    }

    function calculateRowTotal(index) {
        const rows = document.querySelectorAll('#itemsTableBody tr');
        const row = rows[index];

        if (!row) return;

        const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
        const discountValue = parseFloat(row.querySelector('.discount-value').value) || 0;
        
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

        const lineTotal = quantity * unitPrice;
        const discountAmount = (lineTotal * discountValue) / 100;
        const afterDiscount = lineTotal - discountAmount;
        const taxAmount = (afterDiscount * taxRate) / 100;
        const rowTotal = afterDiscount + taxAmount;

        const rowTotalElement = row.querySelector('.row-total');
        if (rowTotalElement) {
            rowTotalElement.textContent = `RM ${rowTotal.toFixed(2)}`;
        }

        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;
        let totalDiscount = 0;
        let totalTax = 0;

        document.querySelectorAll('#itemsTableBody tr').forEach((row, index) => {
            const quantity = parseFloat(row.querySelector('.quantity')?.value) || 0;
            const unitPrice = parseFloat(row.querySelector('.unit-price')?.value) || 0;
            const discountValue = parseFloat(row.querySelector('.discount-value')?.value) || 0;
            
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

            const lineTotal = quantity * unitPrice;
            const discountAmount = (lineTotal * discountValue) / 100;
            const afterDiscount = lineTotal - discountAmount;
            const taxAmount = (afterDiscount * taxRate) / 100;

            subtotal += afterDiscount;
            totalDiscount += discountAmount;
            totalTax += taxAmount;
        });

        const total = subtotal - totalDiscount + totalTax;

        const subtotalElement = document.getElementById('subtotalAmount');
        const discountElement = document.getElementById('discountAmount');
        const taxElement = document.getElementById('taxAmount');
        const totalElement = document.getElementById('totalAmount');

        if (subtotalElement) subtotalElement.textContent = `RM ${subtotal.toFixed(2)}`;
        if (discountElement) discountElement.textContent = `RM ${totalDiscount.toFixed(2)}`;
        if (taxElement) taxElement.textContent = `RM ${totalTax.toFixed(2)}`;
        if (totalElement) totalElement.textContent = `RM ${total.toFixed(2)}`;
    }

    function proceedWithUpdate() {
        $('#deliveryWarningModal').modal('hide');
        document.getElementById('invoiceForm').submit();
    }

    $(document).ready(function() {
        // Load taxes for existing items first
        document.querySelectorAll('.tax-select').forEach(select => {
            const hasSelectedOption = select.querySelector('option[selected]');
            if (!hasSelectedOption) {
                // Determine item type from the row
                const row = select.closest('tr');
                const itemTypeSelect = row ? row.querySelector('.item-type') : null;
                const itemType = itemTypeSelect ? itemTypeSelect.value : 'both';
                loadTaxes(select, itemType);
            }
        });

        // DON'T automatically load items for existing rows on page load
        // The existing items are already properly set in the blade template
        // Only load items when user actually changes the item type

        // Payment terms change updates due date
        $('input[name="payment_terms"]').on('change', function() {
            const invoiceDate = $('input[name="invoice_date"]').val();
            const paymentTerms = parseInt($(this).val()) || 0;

            if (invoiceDate && paymentTerms > 0) {
                const dueDate = new Date(invoiceDate);
                dueDate.setDate(dueDate.getDate() + paymentTerms);

                const dueDateString = dueDate.toISOString().split('T')[0];
                const dueDateInput = $('input[readonly][type="date"]');
                if (dueDateInput.length) {
                    dueDateInput.val(dueDateString);
                }
            }
        });

        // Form validation with delivery warning
        $('#invoiceForm').on('submit', function(e) {
            if (hasDeliveredItems) {
                e.preventDefault();

                // Show delivered items
                const deliveredItems = [];
                document.querySelectorAll('#itemsTableBody tr').forEach(row => {
                    const itemNameElement = row.querySelector('.item-select option:checked');
                    const deliveredQtyElement = row.querySelector('.badge');
                    
                    if (itemNameElement && deliveredQtyElement) {
                        const itemName = itemNameElement.textContent;
                        const deliveredQty = deliveredQtyElement.textContent;
                        if (deliveredQty !== '0') {
                            deliveredItems.push(`${itemName} (Delivered: ${deliveredQty})`);
                        }
                    }
                });

                const listElement = document.getElementById('deliveredItemsList');
                if (listElement) {
                    listElement.innerHTML = deliveredItems.map(item => `<li>${item}</li>`).join('');
                }

                $('#deliveryWarningModal').modal('show');
            }
        });

        // Calculate initial totals
        calculateTotals();
    });
</script>

@endsection