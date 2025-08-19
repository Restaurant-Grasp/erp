@extends('layouts.app')

@section('title', 'Create Invoice from Purchase Order')

@section('content')
<div class="page-header">
    <h1 class="page-title">Create Invoice from Purchase Order</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.invoices.index') }}">Purchase Invoices</a></li>
            <li class="breadcrumb-item active">Create from PO</li>
        </ol>
    </nav>
</div>

<form action="{{ route('purchase.invoices.store') }}" method="POST" id="invoiceForm">
    @csrf
    <input type="hidden" name="po_id" value="{{ $order->id }}">
    <input type="hidden" name="vendor_id" value="{{ $order->vendor_id }}">
    
    <div class="row">
        <div class="col-md-8">
            <!-- PO Information -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Purchase Order Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="150"><strong>PO No:</strong></td>
                                    <td>{{ $order->po_no }}</td>
                                </tr>
                                <tr>
                                    <td><strong>PO Date:</strong></td>
                                    <td>{{ $order->po_date->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Vendor:</strong></td>
                                    <td>{{ $order->vendor->company_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Currency:</strong></td>
                                    <td>{{ $order->currency }} (Rate: {{ $order->exchange_rate }})</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="150"><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $order->status_badge }}">
                                            {{ ucwords(str_replace('_', ' ', $order->status)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Total Amount:</strong></td>
                                    <td>{{ $order->currency }} {{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Received:</strong></td>
                                    <td>{{ number_format($order->received_percentage, 1) }}%</td>
                                </tr>
                                @if($order->delivery_date)
                                <tr>
                                    <td><strong>Delivery Date:</strong></td>
                                    <td>{{ $order->delivery_date->format('d/m/Y') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Information -->
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Invoice Information</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Vendor Invoice No</label>
                            <input type="text" name="vendor_invoice_no" class="form-control @error('vendor_invoice_no') is-invalid @enderror" 
                                   value="{{ old('vendor_invoice_no') }}" placeholder="Vendor's invoice number">
                            @error('vendor_invoice_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                            <input type="date" name="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror" 
                                   value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                            @error('invoice_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Payment Terms (Days) <span class="text-danger">*</span></label>
                            <input type="number" name="payment_terms" class="form-control @error('payment_terms') is-invalid @enderror" 
                                   value="{{ old('payment_terms', '30') }}" min="0" required>
                            @error('payment_terms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Currency</label>
                            <input type="text" class="form-control" value="{{ $order->currency }}" readonly>
                            <input type="hidden" name="currency" value="{{ $order->currency }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Exchange Rate</label>
                            <input type="number" name="exchange_rate" class="form-control @error('exchange_rate') is-invalid @enderror" 
                                   value="{{ old('exchange_rate', $order->exchange_rate) }}" step="0.0001" min="0.0001" required>
                            @error('exchange_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Items from Purchase Order</h5>
                    <div>
                        <button type="button" class="btn btn-info btn-sm me-2" onclick="selectAllItems()">
                            <i class="fas fa-check-square me-2"></i> Select All
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="clearAllItems()">
                            <i class="fas fa-square me-2"></i> Clear All
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="itemsTable">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAllCheckbox" onchange="toggleAllItems()">
                                    </th>
                                    <th>Item</th>
                                    <th width="100">Ordered</th>
                                    <th width="100">Received</th>
                                    <th width="100">Remaining</th>
                                    <th width="100">Invoice Qty</th>
                                    <th width="80">UOM</th>
                                    <th width="120">Unit Price</th>
                                    <th width="120">Discount</th>
                                    <th width="80">Tax %</th>
                                    <th width="120">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $index => $item)
                                <tr class="item-row">
                                    <td class="text-center">
                                        <input type="checkbox" class="item-checkbox" name="items[{{ $index }}][selected]" 
                                               value="1" onchange="toggleItem(this)" 
                                               {{ $item->remaining_quantity > 0 ? 'checked' : 'disabled' }}>
                                    </td>
                                    <td>
                                        <strong>{{ $item->item_name }}</strong>
                                        @if($item->description)
                                            <br><small class="text-muted">{{ $item->description }}</small>
                                        @endif
                                        <input type="hidden" name="items[{{ $index }}][item_type]" value="{{ $item->item_type }}">
                                        <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $item->item_id }}">
                                        <input type="hidden" name="items[{{ $index }}][po_item_id]" value="{{ $item->id }}">
                                        <input type="hidden" name="items[{{ $index }}][description]" value="{{ $item->description }}">
                                    </td>
                                    <td>{{ number_format($item->quantity, 2) }}</td>
                                    <td>{{ number_format($item->received_quantity, 2) }}</td>
                                    <td>
                                        <span class="{{ $item->remaining_quantity > 0 ? 'text-warning' : 'text-success' }}">
                                            {{ number_format($item->remaining_quantity, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][quantity]" 
                                               class="form-control form-control-sm text-end item-quantity" 
                                               value="{{ $item->remaining_quantity }}" 
                                               step="1" min="0" max="{{ $item->remaining_quantity }}" 
                                               onchange="calculateItemTotal(this)" 
                                               {{ $item->remaining_quantity > 0 ? '' : 'disabled' }}>
                                    </td>
                                    <td>
                                        <select name="items[{{ $index }}][uom_id]" class="form-select form-select-sm">
                                            <option value="">UOM</option>
                                            @foreach($uoms as $uom)
                                            <option value="{{ $uom->id }}" {{ $item->uom_id == $uom->id ? 'selected' : '' }}>{{ $uom->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][unit_price]" 
                                               class="form-control form-control-sm text-end item-price" 
                                               value="{{ $item->unit_price }}" step="0.01" min="0" 
                                               onchange="calculateItemTotal(this)">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="items[{{ $index }}][discount_value]" 
                                                   class="form-control text-end item-discount" 
                                                   value="{{ $item->discount_value }}" step="0.01" min="0" 
                                                   onchange="calculateItemTotal(this)">
                                            <select name="items[{{ $index }}][discount_type]" class="form-select" onchange="calculateItemTotal(this)">
                                                <option value="amount" {{ $item->discount_type == 'amount' ? 'selected' : '' }}>Amt</option>
                                                <option value="percentage" {{ $item->discount_type == 'percentage' ? 'selected' : '' }}>%</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][tax_rate]" 
                                               class="form-control form-control-sm text-end item-tax" 
                                               value="{{ $item->tax_rate }}" step="0.01" min="0" max="100" 
                                               onchange="calculateItemTotal(this)">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm text-end item-total" 
                                               readonly value="0.00">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Notes</h5></div>
                <div class="card-body">
                    <textarea name="notes" class="form-control" rows="4" 
                              placeholder="Internal notes...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Summary -->
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Invoice Summary</h5></div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>Subtotal:</td>
                            <td class="text-end" id="subtotalDisplay">0.00</td>
                        </tr>
                        <tr>
                            <td>
                                <select name="discount_type" class="form-select form-select-sm" onchange="calculateTotals()">
                                    <option value="amount">Discount (Amount)</option>
                                    <option value="percentage">Discount (%)</option>
                                </select>
                            </td>
                            <td class="text-end">
                                <input type="number" name="discount_value" class="form-control form-control-sm text-end" 
                                       value="0" step="0.01" min="0" onchange="calculateTotals()">
                            </td>
                        </tr>
                        <tr>
                            <td>Discount Amount:</td>
                            <td class="text-end" id="discountDisplay">0.00</td>
                        </tr>
                        <tr>
                            <td>Tax Amount:</td>
                            <td class="text-end" id="taxDisplay">0.00</td>
                        </tr>
                        <tr class="table-primary">
                            <td><strong>Total Amount:</strong></td>
                            <td class="text-end"><strong id="totalDisplay">0.00</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- PO Summary -->
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">PO Summary</h5></div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>PO Total:</td>
                            <td class="text-end">{{ $order->currency }} {{ number_format($order->total_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Already Invoiced:</td>
                            <td class="text-end">{{ $order->currency }} {{ number_format($order->total_received_amount ?? 0, 2) }}</td>
                        </tr>
                        <tr class="table-info">
                            <td><strong>Remaining:</strong></td>
                            <td class="text-end">
                                <strong>{{ $order->currency }} {{ number_format($order->total_amount - ($order->total_received_amount ?? 0), 2) }}</strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Actions -->
            <div class="card mt-4">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Select items from the PO to include in this invoice. You can create multiple invoices from the same PO.
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Create Invoice
                        </button>
                        <a href="{{ route('purchase.orders.show', $order) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
$(document).ready(function() {
    // Calculate initial totals
    calculateTotals();
});

function toggleAllItems() {
    const selectAll = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.item-checkbox:not(:disabled)');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
        toggleItem(checkbox);
    });
}

function selectAllItems() {
    const checkboxes = document.querySelectorAll('.item-checkbox:not(:disabled)');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
        toggleItem(checkbox);
    });
    document.getElementById('selectAllCheckbox').checked = true;
}

function clearAllItems() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
        toggleItem(checkbox);
    });
    document.getElementById('selectAllCheckbox').checked = false;
}

function toggleItem(checkbox) {
    const row = $(checkbox).closest('tr');
    const inputs = row.find('input:not(.item-checkbox), select');
    
    if (checkbox.checked) {
        inputs.prop('disabled', false);
        row.removeClass('table-secondary');
    } else {
        inputs.prop('disabled', true);
        row.addClass('table-secondary');
        row.find('.item-total').val('0.00');
    }
    
    calculateTotals();
}

function calculateItemTotal(element) {
    const row = $(element).closest('tr');
    const checkbox = row.find('.item-checkbox');
    
    if (!checkbox.is(':checked')) return;
    
    const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
    const unitPrice = parseFloat(row.find('.item-price').val()) || 0;
    const discountValue = parseFloat(row.find('.item-discount').val()) || 0;
    const discountType = row.find('select[name*="discount_type"]').val();
    const taxRate = parseFloat(row.find('.item-tax').val()) || 0;
    
    let lineTotal = quantity * unitPrice;
    
    // Calculate discount
    let discountAmount = 0;
    if (discountValue > 0) {
        if (discountType === 'percentage') {
            discountAmount = (lineTotal * discountValue) / 100;
        } else {
            discountAmount = discountValue;
        }
    }
    
    const afterDiscount = lineTotal - discountAmount;
    const taxAmount = (afterDiscount * taxRate) / 100;
    const total = afterDiscount + taxAmount;
    
    row.find('.item-total').val(total.toFixed(2));
    
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    let totalTax = 0;
    
    $('.item-row').each(function() {
        const checkbox = $(this).find('.item-checkbox');
        if (!checkbox.is(':checked')) return;
        
        const quantity = parseFloat($(this).find('.item-quantity').val()) || 0;
        const unitPrice = parseFloat($(this).find('.item-price').val()) || 0;
        const taxRate = parseFloat($(this).find('.item-tax').val()) || 0;
        const discountValue = parseFloat($(this).find('.item-discount').val()) || 0;
        const discountType = $(this).find('select[name*="discount_type"]').val();
        
        const lineTotal = quantity * unitPrice;
        subtotal += lineTotal;
        
        // Calculate line discount and tax
        let discountAmount = 0;
        if (discountValue > 0) {
            if (discountType === 'percentage') {
                discountAmount = (lineTotal * discountValue) / 100;
            } else {
                discountAmount = discountValue;
            }
        }
        
        const afterDiscount = lineTotal - discountAmount;
        totalTax += (afterDiscount * taxRate) / 100;
    });
    
    // Calculate invoice level discount
    const discountValue = parseFloat($('input[name="discount_value"]').val()) || 0;
    const discountType = $('select[name="discount_type"]').val();
    
    let invoiceDiscount = 0;
    if (discountValue > 0) {
        if (discountType === 'percentage') {
            invoiceDiscount = (subtotal * discountValue) / 100;
        } else {
            invoiceDiscount = discountValue;
        }
    }
    
    const total = subtotal - invoiceDiscount + totalTax;
    
    $('#subtotalDisplay').text(subtotal.toFixed(2));
    $('#discountDisplay').text(invoiceDiscount.toFixed(2));
    $('#taxDisplay').text(totalTax.toFixed(2));
    $('#totalDisplay').text(total.toFixed(2));
}

// Initialize item calculations
$(document).ready(function() {
    $('.item-checkbox:checked').each(function() {
        calculateItemTotal($(this).closest('tr').find('.item-quantity')[0]);
    });
});

// Form validation
$('#invoiceForm').on('submit', function(e) {
    const selectedItems = $('.item-checkbox:checked').length;
    
    if (selectedItems === 0) {
        e.preventDefault();
        alert('Please select at least one item to create the invoice.');
        return false;
    }
    
    let hasValidQuantities = true;
    $('.item-checkbox:checked').each(function() {
        const row = $(this).closest('tr');
        const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
        
        if (quantity <= 0) {
            hasValidQuantities = false;
        }
    });
    
    if (!hasValidQuantities) {
        e.preventDefault();
        alert('Please ensure all selected items have valid quantities greater than 0.');
        return false;
    }
});
</script>
@endsection