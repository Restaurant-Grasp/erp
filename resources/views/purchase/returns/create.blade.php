@extends('layouts.app')

@section('title', 'Create Purchase Return')

@section('content')
<div class="page-header">
    <h1 class="page-title">Create Purchase Return</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.returns.index') }}">Purchase Returns</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<form action="{{ route('purchase.returns.store') }}" method="POST" id="returnForm">
    @csrf
    <div class="row">
        <div class="col-md-8">
            <!-- Return Information -->
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Return Information</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Vendor <span class="text-danger">*</span></label>
                            <select name="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror" required>
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->company_name }} ({{ $vendor->vendor_code }})
                                </option>
                                @endforeach
                            </select>
                            @error('vendor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">GRN Reference</label>
                            <select name="grn_id" class="form-select @error('grn_id') is-invalid @enderror" onchange="loadGrnItems()">
                                <option value="">Select GRN (Optional)</option>
                                @foreach($grns as $grn)
                                <option value="{{ $grn->id }}" {{ old('grn_id') == $grn->id ? 'selected' : '' }}>
                                    {{ $grn->grn_no }} - {{ $grn->vendor->company_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('grn_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Return Date <span class="text-danger">*</span></label>
                            <input type="date" name="return_date" class="form-control @error('return_date') is-invalid @enderror" 
                                   value="{{ old('return_date', date('Y-m-d')) }}" required>
                            @error('return_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Return Type <span class="text-danger">*</span></label>
                            <select name="return_type" class="form-select @error('return_type') is-invalid @enderror" required>
                                <option value="">Select Return Type</option>
                                <option value="damaged" {{ old('return_type') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                <option value="defective" {{ old('return_type') == 'defective' ? 'selected' : '' }}>Defective</option>
                                <option value="wrong_item" {{ old('return_type') == 'wrong_item' ? 'selected' : '' }}>Wrong Item</option>
                                <option value="excess" {{ old('return_type') == 'excess' ? 'selected' : '' }}>Excess</option>
                            </select>
                            @error('return_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Invoice Reference</label>
                            <input type="text" name="invoice_reference" class="form-control" 
                                   value="{{ old('invoice_reference') }}" placeholder="Invoice number">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Return Items</h5>
                    <button type="button" class="btn btn-success btn-sm" onclick="addNewItem()">
                        <i class="fas fa-plus me-2"></i> Add Item
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Select a GRN above to auto-populate items, or manually add items for direct returns.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="itemsTable">
                            <thead>
                                <tr>
                                    <th width="200">Product</th>
                                    <th width="150">Serial Number</th>
                                    <th width="100">Quantity</th>
                                    <th width="120">Unit Price</th>
                                    <th>Reason</th>
                                    <th width="100">Replacement</th>
                                    <th width="120">Total</th>
                                    <th width="50">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                <!-- Items will be added here -->
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
                              placeholder="Enter return notes and additional details...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Summary -->
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Return Summary</h5></div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>Total Items:</td>
                            <td class="text-end" id="totalItemsDisplay">0</td>
                        </tr>
                        <tr>
                            <td>Total Quantity:</td>
                            <td class="text-end" id="totalQuantityDisplay">0</td>
                        </tr>
                        <tr class="table-primary">
                            <td><strong>Total Amount:</strong></td>
                            <td class="text-end"><strong id="totalAmountDisplay">₹0.00</strong></td>
                        </tr>
                    </table>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Amount calculation is optional for returns
                        </small>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Create Return
                        </button>
                        <a href="{{ route('purchase.returns.index') }}" class="btn btn-outline-secondary">
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
            <select name="items[INDEX][product_id]" class="form-select form-select-sm product-select" required onchange="loadProductDetails(this)">
                <option value="">Select Product</option>
            </select>
            <input type="hidden" name="items[INDEX][grn_item_id]" class="grn-item-id">
        </td>
        <td>
            <select name="items[INDEX][serial_number_id]" class="form-select form-select-sm serial-select">
                <option value="">No Serial Number</option>
            </select>
        </td>
        <td>
            <input type="number" name="items[INDEX][quantity]" class="form-control form-control-sm text-end item-quantity" 
                   value="1" step="0.01" min="0.01" required onchange="calculateItemTotal(this)">
        </td>
        <td>
            <input type="number" name="items[INDEX][unit_price]" class="form-control form-control-sm text-end item-price" 
                   value="0" step="0.01" min="0" onchange="calculateItemTotal(this)">
        </td>
        <td>
            <input type="text" name="items[INDEX][reason]" class="form-control form-control-sm item-reason" 
                   placeholder="Reason for return" required>
        </td>
        <td>
            <div class="form-check">
                <input type="checkbox" name="items[INDEX][replacement_required]" class="form-check-input replacement-check" value="1">
                <label class="form-check-label">Required</label>
            </div>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm text-end item-total" readonly value="₹0.00">
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
let itemIndex = 0;
let grnItems = [];
let grnSerialNumbers = [];

$(document).ready(function() {
    // Load vendor items when vendor is selected
    $('select[name="vendor_id"]').on('change', function() {
        const vendorId = $(this).val();
        if (vendorId) {
            loadVendorItems(vendorId);
        }
    });

    // Add first item by default
    addNewItem();
});

function addNewItem() {
    const template = document.getElementById('itemTemplate');
    const clone = template.content.cloneNode(true);
    
    // Replace INDEX with actual index
    const htmlString = clone.querySelector('.item-row').outerHTML.replace(/INDEX/g, itemIndex);
    
    $('#itemsTableBody').append(htmlString);
    itemIndex++;
    
    calculateTotals();
}

function removeItem(button) {
    $(button).closest('tr').remove();
    calculateTotals();
}

function loadGrnItems() {
    const grnId = $('select[name="grn_id"]').val();
    
    if (!grnId) {
        grnItems = [];
        grnSerialNumbers = [];
        return;
    }
    
    $.get(`/purchase/returns/grn-items?grn_id=${grnId}`)
        .done(function(data) {
            grnItems = data.items;
            grnSerialNumbers = data.serial_numbers;
            
            // Update vendor selection
            $('select[name="vendor_id"]').val(data.grn.vendor_id);
            
            // Clear existing items and populate with GRN items
            $('#itemsTableBody').empty();
            itemIndex = 0;
            
            grnItems.forEach(function(item) {
                addNewItem();
                const row = $('.item-row').last();
                
                // Add product option and select it
                const productSelect = row.find('.product-select');
                productSelect.append(`<option value="${item.product_id}" selected>${item.product_name} (${item.product_code})</option>`);
                
                // Set GRN item ID
                row.find('.grn-item-id').val(item.id);
                
                // Set quantities
                row.find('.item-quantity').val(item.damaged_quantity || 1);
                
                // Load serial numbers if product has them
                if (item.has_serial_number && grnSerialNumbers[item.id]) {
                    const serialSelect = row.find('.serial-select');
                    grnSerialNumbers[item.id].forEach(function(serial) {
                        serialSelect.append(`<option value="${serial.id}">${serial.serial_number}</option>`);
                    });
                }
                
                // Set default reason based on GRN
                if (item.damaged_quantity > 0) {
                    row.find('.item-reason').val('Damaged during delivery');
                }
            });
            
            calculateTotals();
        })
        .fail(function() {
            alert('Failed to load GRN items');
        });
}

function loadVendorItems(vendorId) {
    // This would load all products for the vendor for manual selection
    // Implementation similar to PO creation
}

function loadProductDetails(select) {
    const row = $(select).closest('tr');
    const productId = $(select).val();
    
    // You can add logic here to auto-populate price from product cost
    // For now, leave it for manual entry
}

function calculateItemTotal(element) {
    const row = $(element).closest('tr');
    const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
    const unitPrice = parseFloat(row.find('.item-price').val()) || 0;
    
    const total = quantity * unitPrice;
    
    row.find('.item-total').val('₹' + total.toFixed(2));
    
    calculateTotals();
}

function calculateTotals() {
    let totalItems = 0;
    let totalQuantity = 0;
    let totalAmount = 0;
    
    $('.item-row').each(function() {
        const quantity = parseFloat($(this).find('.item-quantity').val()) || 0;
        const unitPrice = parseFloat($(this).find('.item-price').val()) || 0;
        
        if (quantity > 0) {
            totalItems++;
            totalQuantity += quantity;
            totalAmount += quantity * unitPrice;
        }
    });
    
    $('#totalItemsDisplay').text(totalItems);
    $('#totalQuantityDisplay').text(totalQuantity.toFixed(2));
    $('#totalAmountDisplay').text('₹' + totalAmount.toFixed(2));
}

// Form validation
$('#returnForm').on('submit', function(e) {
    if ($('.item-row').length === 0) {
        e.preventDefault();
        alert('Please add at least one item to the return.');
        return false;
    }
    
    let hasValidItems = false;
    $('.item-row').each(function() {
        const productId = $(this).find('.product-select').val();
        const quantity = parseFloat($(this).find('.item-quantity').val()) || 0;
        const reason = $(this).find('.item-reason').val().trim();
        
        if (productId && quantity > 0 && reason) {
            hasValidItems = true;
        }
    });
    
    if (!hasValidItems) {
        e.preventDefault();
        alert('Please ensure all items have valid product selection, quantity, and reason.');
        return false;
    }
});
</script>
@endsection