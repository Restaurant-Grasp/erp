@extends('layouts.app')

@section('title', 'Edit Purchase Order')

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Purchase Order</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.orders.index') }}">Purchase Orders</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.orders.show', $order) }}">{{ $order->po_no }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form action="{{ route('purchase.orders.update', $order) }}" method="POST" id="poForm">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-8">
            <!-- PO Information -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Purchase Order Information - {{ $order->po_no }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Vendor <span class="text-danger">*</span></label>
                            <select name="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror" required>
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ old('vendor_id', $order->vendor_id) == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->company_name }} ({{ $vendor->vendor_code }})
                                </option>
                                @endforeach
                            </select>
                            @error('vendor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">PO Date <span class="text-danger">*</span></label>
                            <input type="date" name="po_date" class="form-control @error('po_date') is-invalid @enderror" 
                                   value="{{ old('po_date', $order->po_date->format('Y-m-d')) }}" required>
                            @error('po_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Delivery Date</label>
                            <input type="date" name="delivery_date" class="form-control @error('delivery_date') is-invalid @enderror" 
                                   value="{{ old('delivery_date', $order->delivery_date?->format('Y-m-d')) }}">
                            @error('delivery_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference No</label>
                            <input type="text" name="reference_no" class="form-control @error('reference_no') is-invalid @enderror" 
                                   value="{{ old('reference_no', $order->reference_no) }}" placeholder="External reference number">
                            @error('reference_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                            <select name="currency" class="form-select @error('currency') is-invalid @enderror" required>
                                <option value="MYR" {{ old('currency', $order->currency) == 'MYR' ? 'selected' : '' }}>MYR</option>
                                <option value="USD" {{ old('currency', $order->currency) == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="SGD" {{ old('currency', $order->currency) == 'SGD' ? 'selected' : '' }}>SGD</option>
                                <option value="EUR" {{ old('currency', $order->currency) == 'EUR' ? 'selected' : '' }}>EUR</option>
                            </select>
                            @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Exchange Rate <span class="text-danger">*</span></label>
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
                    <h5 class="mb-0">Items</h5>
                    <button type="button" class="btn btn-success btn-sm" onclick="addNewItem()">
                        <i class="fas fa-plus me-2"></i> Add Item
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="itemsTable">
                            <thead>
                                <tr>
                                    <th width="120">Type</th>
                                    <th width="200">Item</th>
                                    <th>Description</th>
                                    <th width="100">Qty</th>
                                    <th width="80">UOM</th>
                                    <th width="120">Unit Price</th>
                                    <th width="120">Discount</th>
                                    <th width="80">Tax %</th>
                                    <th width="120">Total</th>
                                    <th width="50">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                @foreach($order->items as $index => $item)
                                <tr class="item-row">
                                    <td>
                                        <select name="items[{{ $index }}][item_type]" class="form-select form-select-sm item-type" required onchange="loadItems(this)">
                                            <option value="">Select Type</option>
                                            <option value="product" {{ $item->item_type == 'product' ? 'selected' : '' }}>Product</option>
                                            <option value="service" {{ $item->item_type == 'service' ? 'selected' : '' }}>Service</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="items[{{ $index }}][item_id]" class="form-select form-select-sm item-id" required onchange="loadItemDetails(this)">
                                            <option value="">Select Item</option>
                                            @if($item->item_type == 'product')
                                                @foreach($products as $product)
                                                <option value="{{ $product->id }}" {{ $item->item_id == $product->id ? 'selected' : '' }}
                                                        data-price="{{ $product->cost_price }}" data-uom="{{ $product->uom_id }}">
                                                    {{ $product->name }}
                                                </option>
                                                @endforeach
                                            @elseif($item->item_type == 'service')
                                                @foreach($services as $service)
                                                <option value="{{ $service->id }}" {{ $item->item_id == $service->id ? 'selected' : '' }}
                                                        data-price="{{ $service->base_price }}">
                                                    {{ $service->name }}
                                                </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][description]" class="form-control form-control-sm item-description" 
                                               value="{{ $item->description }}">
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm text-end item-quantity" 
                                               value="{{ $item->quantity }}" step="0.01" min="0.01" required onchange="calculateItemTotal(this)">
                                    </td>
                                    <td>
                                        <select name="items[{{ $index }}][uom_id]" class="form-select form-select-sm item-uom">
                                            <option value="">UOM</option>
                                            @foreach($uoms as $uom)
                                            <option value="{{ $uom->id }}" {{ $item->uom_id == $uom->id ? 'selected' : '' }}>{{ $uom->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][unit_price]" class="form-control form-control-sm text-end item-price" 
                                               value="{{ $item->unit_price }}" step="0.01" min="0" required onchange="calculateItemTotal(this)">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="items[{{ $index }}][discount_value]" class="form-control text-end item-discount" 
                                                   value="{{ $item->discount_value }}" step="0.01" min="0" onchange="calculateItemTotal(this)">
                                            <select name="items[{{ $index }}][discount_type]" class="form-select" onchange="calculateItemTotal(this)">
                                                <option value="amount" {{ $item->discount_type == 'amount' ? 'selected' : '' }}>Amt</option>
                                                <option value="percentage" {{ $item->discount_type == 'percentage' ? 'selected' : '' }}>%</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][tax_rate]" class="form-control form-control-sm text-end item-tax" 
                                               value="{{ $item->tax_rate }}" step="0.01" min="0" max="100" onchange="calculateItemTotal(this)">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm text-end item-total" readonly value="{{ number_format($item->total_amount, 2) }}">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Terms & Conditions -->
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Terms & Conditions</h5></div>
                <div class="card-body">
                    <textarea name="terms_conditions" class="form-control" rows="5" 
                              placeholder="Enter terms and conditions...">{{ old('terms_conditions', $order->terms_conditions) }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Summary -->
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Order Summary</h5></div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>Subtotal:</td>
                            <td class="text-end" id="subtotalDisplay">{{ number_format($order->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td>
                                <select name="discount_type" class="form-select form-select-sm" onchange="calculateTotals()">
                                    <option value="amount" {{ $order->discount_type == 'amount' ? 'selected' : '' }}>Discount (Amount)</option>
                                    <option value="percentage" {{ $order->discount_type == 'percentage' ? 'selected' : '' }}>Discount (%)</option>
                                </select>
                            </td>
                            <td class="text-end">
                                <input type="number" name="discount_value" class="form-control form-control-sm text-end" 
                                       value="{{ $order->discount_value }}" step="0.01" min="0" onchange="calculateTotals()">
                            </td>
                        </tr>
                        <tr>
                            <td>Discount Amount:</td>
                            <td class="text-end" id="discountDisplay">{{ number_format($order->discount_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Tax Amount:</td>
                            <td class="text-end" id="taxDisplay">{{ number_format($order->tax_amount, 2) }}</td>
                        </tr>
                        <tr class="table-primary">
                            <td><strong>Total Amount:</strong></td>
                            <td class="text-end"><strong id="totalDisplay">{{ number_format($order->total_amount, 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Notes -->
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Notes</h5></div>
                <div class="card-body">
                    <textarea name="notes" class="form-control" rows="4" 
                              placeholder="Internal notes...">{{ old('notes', $order->notes) }}</textarea>
                </div>
            </div>

            <!-- Actions -->
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update Purchase Order
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

<!-- Item Template -->
<template id="itemTemplate">
    <tr class="item-row">
        <td>
            <select name="items[INDEX][item_type]" class="form-select form-select-sm item-type" required onchange="loadItems(this)">
                <option value="">Select Type</option>
                <option value="product">Product</option>
                <option value="service">Service</option>
            </select>
        </td>
        <td>
            <select name="items[INDEX][item_id]" class="form-select form-select-sm item-id" required onchange="loadItemDetails(this)">
                <option value="">Select Item</option>
            </select>
        </td>
        <td>
            <input type="text" name="items[INDEX][description]" class="form-control form-control-sm item-description">
        </td>
        <td>
            <input type="number" name="items[INDEX][quantity]" class="form-control form-control-sm text-end item-quantity" 
                   value="1" step="0.01" min="0.01" required onchange="calculateItemTotal(this)">
        </td>
        <td>
            <select name="items[INDEX][uom_id]" class="form-select form-select-sm item-uom">
                <option value="">UOM</option>
                @foreach($uoms as $uom)
                <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" name="items[INDEX][unit_price]" class="form-control form-control-sm text-end item-price" 
                   value="0" step="0.01" min="0" required onchange="calculateItemTotal(this)">
        </td>
        <td>
            <div class="input-group input-group-sm">
                <input type="number" name="items[INDEX][discount_value]" class="form-control text-end item-discount" 
                       value="0" step="0.01" min="0" onchange="calculateItemTotal(this)">
                <select name="items[INDEX][discount_type]" class="form-select" onchange="calculateItemTotal(this)">
                    <option value="amount">Amt</option>
                    <option value="percentage">%</option>
                </select>
            </div>
        </td>
        <td>
            <input type="number" name="items[INDEX][tax_rate]" class="form-control form-control-sm text-end item-tax" 
                   value="0" step="0.01" min="0" max="100" onchange="calculateItemTotal(this)">
        </td>
        <td>
            <input type="text" class="form-control form-control-sm text-end item-total" readonly value="0.00">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let itemIndex = {{ $order->items->count() }};
let vendorProducts = [];
let products = @json($products);
let services = @json($services);

$(document).ready(function() {
    // Load vendor products when vendor is selected
    $('select[name="vendor_id"]').on('change', function() {
        const vendorId = $(this).val();
        if (vendorId) {
            loadVendorProducts(vendorId);
        }
    });

    // Load vendor products for current vendor
    const currentVendorId = $('select[name="vendor_id"]').val();
    if (currentVendorId) {
        loadVendorProducts(currentVendorId);
    }

    // Calculate initial totals
    calculateTotals();
});

function addNewItem() {
    const template = document.getElementById('itemTemplate');
    const clone = template.content.cloneNode(true);
    
    // Replace INDEX with actual index
    const htmlString = clone.querySelector('.item-row').outerHTML.replace(/INDEX/g, itemIndex);
    
    $('#itemsTableBody').append(htmlString);
    itemIndex++;
}

function removeItem(button) {
    $(button).closest('tr').remove();
    calculateTotals();
}

function loadVendorProducts(vendorId) {
    $.get(`/purchase/orders/vendor-products?vendor_id=${vendorId}`)
        .done(function(data) {
            vendorProducts = data;
        })
        .fail(function() {
            console.error('Failed to load vendor products');
        });
}

function loadItems(typeSelect) {
    const row = $(typeSelect).closest('tr');
    const itemSelect = row.find('.item-id');
    const itemType = $(typeSelect).val();
    
    itemSelect.empty().append('<option value="">Select Item</option>');
    
    if (itemType === 'product') {
        products.forEach(product => {
            const vendorProduct = vendorProducts.find(vp => vp.id === product.id);
            const price = vendorProduct ? vendorProduct.vendor_price : product.cost_price;
            const preferred = vendorProduct ? (vendorProduct.is_preferred ? ' (Preferred)' : '') : '';
            
            itemSelect.append(`<option value="${product.id}" data-price="${price}" data-uom="${product.uom_id}">${product.name}${preferred}</option>`);
        });
    } else if (itemType === 'service') {
        services.forEach(service => {
            itemSelect.append(`<option value="${service.id}" data-price="${service.base_price}">${service.name}</option>`);
        });
    }
}

function loadItemDetails(itemSelect) {
    const row = $(itemSelect).closest('tr');
    const selectedOption = $(itemSelect).find('option:selected');
    const price = selectedOption.data('price') || 0;
    const uomId = selectedOption.data('uom');
    
    row.find('.item-price').val(price);
    if (uomId) {
        row.find('.item-uom').val(uomId);
    }
    
    calculateItemTotal(itemSelect);
}

function calculateItemTotal(element) {
    const row = $(element).closest('tr');
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
    
    // Calculate order level discount
    const discountValue = parseFloat($('input[name="discount_value"]').val()) || 0;
    const discountType = $('select[name="discount_type"]').val();
    
    let orderDiscount = 0;
    if (discountValue > 0) {
        if (discountType === 'percentage') {
            orderDiscount = (subtotal * discountValue) / 100;
        } else {
            orderDiscount = discountValue;
        }
    }
    
    const total = subtotal - orderDiscount + totalTax;
    
    $('#subtotalDisplay').text(subtotal.toFixed(2));
    $('#discountDisplay').text(orderDiscount.toFixed(2));
    $('#taxDisplay').text(totalTax.toFixed(2));
    $('#totalDisplay').text(total.toFixed(2));
}

// Form validation
$('#poForm').on('submit', function(e) {
    if ($('.item-row').length === 0) {
        e.preventDefault();
        alert('Please add at least one item to the purchase order.');
        return false;
    }
    
    let hasValidItems = false;
    $('.item-row').each(function() {
        const itemType = $(this).find('.item-type').val();
        const itemId = $(this).find('.item-id').val();
        const quantity = parseFloat($(this).find('.item-quantity').val()) || 0;
        
        if (itemType && itemId && quantity > 0) {
            hasValidItems = true;
        }
    });
    
    if (!hasValidItems) {
        e.preventDefault();
        alert('Please ensure all items have valid type, item selection, and quantity.');
        return false;
    }
});
</script>
@endsection