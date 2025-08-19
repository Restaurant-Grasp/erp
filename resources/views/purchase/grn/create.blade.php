@extends('layouts.app')

@section('title', 'Create Goods Receipt Note')

@section('content')
<div class="page-header">
    <h1 class="page-title">Create Goods Receipt Note</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.grn.index') }}">Goods Receipt Notes</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<form action="{{ route('purchase.grn.store') }}" method="POST" id="grnForm">
    @csrf
    <div class="row">
        <div class="col-md-8">
            <!-- GRN Information -->
            <div class="card">
                <div class="card-header"><h5 class="mb-0">GRN Information</h5></div>
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
                        <div class="col-md-3">
                            <label class="form-label">GRN Date <span class="text-danger">*</span></label>
                            <input type="date" name="grn_date" class="form-control @error('grn_date') is-invalid @enderror" 
                                   value="{{ old('grn_date', date('Y-m-d')) }}" required>
                            @error('grn_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Received By <span class="text-danger">*</span></label>
                            <select name="received_by" class="form-select @error('received_by') is-invalid @enderror" required>
                                <option value="">Select Staff</option>
                                @foreach($staff as $member)
                                <option value="{{ $member->id }}" {{ old('received_by') == $member->id ? 'selected' : '' }}>
                                    {{ $member->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('received_by')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Purchase Order (Optional)</label>
                            <select name="po_id" class="form-select" onchange="loadPoItems()">
                                <option value="">Select Purchase Order</option>
                                <!-- Will be populated via AJAX based on vendor -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Purchase Invoice (Optional)</label>
                            <select name="invoice_id" class="form-select" onchange="loadInvoiceItems()">
                                <option value="">Select Purchase Invoice</option>
                                <!-- Will be populated via AJAX based on vendor -->
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Items Received</h5>
                    <button type="button" class="btn btn-success btn-sm" onclick="addNewItem()">
                        <i class="fas fa-plus me-2"></i> Add Item
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="itemsTable">
                            <thead>
                                <tr>
                                    <th width="200">Product</th>
                                    <th width="100">Ordered Qty</th>
                                    <th width="100">Received Qty</th>
                                    <th width="100">Damaged Qty</th>
                                    <th width="80">UOM</th>
                                    <th width="120">Batch No</th>
                                    <th width="120">Expiry Date</th>
                                    <th width="150">Damage Reason</th>
                                    <th width="50">Replacement</th>
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
                              placeholder="Enter any notes about the goods receipt...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Summary -->
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Receipt Summary</h5></div>
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
                        <tr>
                            <td>Accepted Quantity:</td>
                            <td class="text-end text-success" id="acceptedQuantityDisplay">0</td>
                        </tr>
                        <tr>
                            <td>Damaged Quantity:</td>
                            <td class="text-end text-danger" id="damagedQuantityDisplay">0</td>
                        </tr>
                        <tr>
                            <td>Items with Serial Numbers:</td>
                            <td class="text-end" id="serialItemsDisplay">0</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Quick Actions</h5></div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="loadFromPO()">
                            <i class="fas fa-file-alt me-2"></i> Load from PO
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="loadFromInvoice()">
                            <i class="fas fa-file-invoice me-2"></i> Load from Invoice
                        </button>
                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="markAllAsReceived()">
                            <i class="fas fa-check-double me-2"></i> Mark All as Received
                        </button>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Create GRN
                        </button>
                        <a href="{{ route('purchase.grn.index') }}" class="btn btn-outline-secondary">
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
                @foreach($products as $product)
                <option value="{{ $product->id }}" data-has-serial="{{ $product->has_serial_number }}" data-has-warranty="{{ $product->has_warranty }}" data-warranty-months="{{ $product->warranty_period_months }}">
                    {{ $product->name }} ({{ $product->product_code }})
                </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm text-end ordered-quantity" 
                   value="0" step="0.01" min="0" readonly>
        </td>
        <td>
            <input type="number" name="items[INDEX][quantity]" class="form-control form-control-sm text-end received-quantity" 
                   value="1" step="0.01" min="0.01" required onchange="calculateAcceptedQuantity(this)">
        </td>
        <td>
            <input type="number" name="items[INDEX][damaged_quantity]" class="form-control form-control-sm text-end damaged-quantity" 
                   value="0" step="0.01" min="0" onchange="calculateAcceptedQuantity(this)">
        </td>
        <td>
            <select name="items[INDEX][uom_id]" class="form-select form-select-sm">
                <option value="">UOM</option>
                @foreach($uoms as $uom)
                <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" name="items[INDEX][batch_no]" class="form-control form-control-sm batch-no" placeholder="Batch">
        </td>
        <td>
            <input type="date" name="items[INDEX][expiry_date]" class="form-control form-control-sm expiry-date">
        </td>
        <td>
            <input type="text" name="items[INDEX][damage_reason]" class="form-control form-control-sm damage-reason" placeholder="Damage reason">
        </td>
        <td class="text-center">
            <div class="form-check">
                <input type="checkbox" name="items[INDEX][replacement_required]" class="form-check-input replacement-required" value="1">
            </div>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                <i class="fas fa-trash"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-info serial-btn" onclick="manageSerialNumbers(this)" style="display: none;">
                <i class="fas fa-barcode"></i>
            </button>
        </td>
    </tr>
</template>

<!-- Serial Numbers Modal -->
<div class="modal fade" id="serialNumbersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Serial Numbers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Product: <span id="serialProductName"></span></label>
                    <p class="text-muted">Received Quantity: <span id="serialReceivedQty"></span></p>
                </div>
                <div id="serialNumbersList">
                    <!-- Serial numbers will be added here -->
                </div>
                <button type="button" class="btn btn-success btn-sm" onclick="addSerialNumber()">
                    <i class="fas fa-plus me-2"></i> Add Serial Number
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveSerialNumbers()">Save Serial Numbers</button>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let itemIndex = 0;
let currentSerialItemIndex = 0;
let serialNumbers = {};

$(document).ready(function() {
    // Load vendor-related data when vendor is selected
    $('select[name="vendor_id"]').on('change', function() {
        loadVendorPOs();
        loadVendorInvoices();
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
    updateSummary();
}

function removeItem(button) {
    const row = $(button).closest('tr');
    const index = row.index();
    
    // Remove serial numbers for this item
    delete serialNumbers[index];
    
    row.remove();
    updateSummary();
}

function loadProductDetails(select) {
    const row = $(select).closest('tr');
    const selectedOption = $(select).find('option:selected');
    const hasSerial = selectedOption.data('has-serial') == 1;
    
    // Show/hide serial number button
    if (hasSerial) {
        row.find('.serial-btn').show();
    } else {
        row.find('.serial-btn').hide();
    }
    
    updateSummary();
}

function calculateAcceptedQuantity(input) {
    const row = $(input).closest('tr');
    const receivedQty = parseFloat(row.find('.received-quantity').val()) || 0;
    const damagedQty = parseFloat(row.find('.damaged-quantity').val()) || 0;
    
    // Ensure damaged quantity doesn't exceed received quantity
    if (damagedQty > receivedQty) {
        row.find('.damaged-quantity').val(receivedQty);
    }
    
    updateSummary();
}

function loadVendorPOs() {
    const vendorId = $('select[name="vendor_id"]').val();
    if (!vendorId) return;
    
    // Load approved POs for this vendor
    // This would be an AJAX call in real implementation
    console.log('Loading POs for vendor:', vendorId);
}

function loadVendorInvoices() {
    const vendorId = $('select[name="vendor_id"]').val();
    if (!vendorId) return;
    
    // Load invoices for this vendor
    // This would be an AJAX call in real implementation
    console.log('Loading invoices for vendor:', vendorId);
}

function loadFromPO() {
    const poId = $('select[name="po_id"]').val();
    if (!poId) {
        alert('Please select a Purchase Order first.');
        return;
    }
    
    // Load items from PO
    // This would be an AJAX call in real implementation
    console.log('Loading items from PO:', poId);
}

function loadFromInvoice() {
    const invoiceId = $('select[name="invoice_id"]').val();
    if (!invoiceId) {
        alert('Please select a Purchase Invoice first.');
        return;
    }
    
    // Load items from invoice
    // This would be an AJAX call in real implementation
    console.log('Loading items from invoice:', invoiceId);
}

function markAllAsReceived() {
    $('.received-quantity').each(function() {
        const orderedQty = parseFloat($(this).closest('tr').find('.ordered-quantity').val()) || 0;
        if (orderedQty > 0) {
            $(this).val(orderedQty);
        }
    });
    updateSummary();
}

function manageSerialNumbers(button) {
    const row = $(button).closest('tr');
    const itemIndex = row.index();
    const productName = row.find('.product-select option:selected').text();
    const receivedQty = parseInt(row.find('.received-quantity').val()) || 0;
    
    currentSerialItemIndex = itemIndex;
    
    $('#serialProductName').text(productName);
    $('#serialReceivedQty').text(receivedQty);
    
    // Load existing serial numbers
    loadExistingSerialNumbers(itemIndex, receivedQty);
    
    $('#serialNumbersModal').modal('show');
}

function loadExistingSerialNumbers(itemIndex, requiredQty) {
    const container = $('#serialNumbersList');
    container.empty();
    
    const existingSerials = serialNumbers[itemIndex] || [];
    
    // Ensure we have the right number of serial number inputs
    for (let i = 0; i < requiredQty; i++) {
        const serial = existingSerials[i] || { serial_number: '', warranty_start_date: '', warranty_end_date: '' };
        addSerialNumberInput(serial, i);
    }
}

function addSerialNumberInput(serial, index) {
    const container = $('#serialNumbersList');
    const html = `
        <div class="row mb-2 serial-row">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm serial-number" 
                       placeholder="Serial Number ${index + 1}" value="${serial.serial_number}" required>
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control form-control-sm warranty-start" 
                       placeholder="Warranty Start" value="${serial.warranty_start_date}">
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control form-control-sm warranty-end" 
                       placeholder="Warranty End" value="${serial.warranty_end_date}">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSerialNumber(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.append(html);
}

function addSerialNumber() {
    const count = $('#serialNumbersList .serial-row').length;
    addSerialNumberInput({ serial_number: '', warranty_start_date: '', warranty_end_date: '' }, count);
}

function removeSerialNumber(button) {
    $(button).closest('.serial-row').remove();
}

function saveSerialNumbers() {
    const serials = [];
    
    $('#serialNumbersList .serial-row').each(function() {
        const serialNumber = $(this).find('.serial-number').val();
        const warrantyStart = $(this).find('.warranty-start').val();
        const warrantyEnd = $(this).find('.warranty-end').val();
        
        if (serialNumber) {
            serials.push({
                serial_number: serialNumber,
                warranty_start_date: warrantyStart,
                warranty_end_date: warrantyEnd
            });
        }
    });
    
    serialNumbers[currentSerialItemIndex] = serials;
    
    // Add hidden inputs for serial numbers
    const row = $('#itemsTableBody tr').eq(currentSerialItemIndex);
    row.find('.serial-inputs').remove(); // Remove existing inputs
    
    serials.forEach((serial, index) => {
        row.append(`
            <input type="hidden" name="items[${currentSerialItemIndex}][serial_numbers][${index}][serial_number]" value="${serial.serial_number}" class="serial-inputs">
            <input type="hidden" name="items[${currentSerialItemIndex}][serial_numbers][${index}][warranty_start_date]" value="${serial.warranty_start_date}" class="serial-inputs">
            <input type="hidden" name="items[${currentSerialItemIndex}][serial_numbers][${index}][warranty_end_date]" value="${serial.warranty_end_date}" class="serial-inputs">
        `);
    });
    
    $('#serialNumbersModal').modal('hide');
    updateSummary();
}

function updateSummary() {
    let totalItems = 0;
    let totalQuantity = 0;
    let acceptedQuantity = 0;
    let damagedQuantity = 0;
    let serialItems = 0;
    
    $('.item-row').each(function() {
        const receivedQty = parseFloat($(this).find('.received-quantity').val()) || 0;
        const damagedQty = parseFloat($(this).find('.damaged-quantity').val()) || 0;
        const hasSerial = $(this).find('.serial-btn').is(':visible');
        
        if (receivedQty > 0) {
            totalItems++;
            totalQuantity += receivedQty;
            acceptedQuantity += (receivedQty - damagedQty);
            damagedQuantity += damagedQty;
            
            if (hasSerial) {
                serialItems++;
            }
        }
    });
    
    $('#totalItemsDisplay').text(totalItems);
    $('#totalQuantityDisplay').text(totalQuantity.toFixed(2));
    $('#acceptedQuantityDisplay').text(acceptedQuantity.toFixed(2));
    $('#damagedQuantityDisplay').text(damagedQuantity.toFixed(2));
    $('#serialItemsDisplay').text(serialItems);
}

// Form validation
$('#grnForm').on('submit', function(e) {
    if ($('.item-row').length === 0) {
        e.preventDefault();
        alert('Please add at least one item to the GRN.');
        return false;
    }
    
    let hasValidItems = false;
    $('.item-row').each(function() {
        const productId = $(this).find('.product-select').val();
        const quantity = parseFloat($(this).find('.received-quantity').val()) || 0;
        
        if (productId && quantity > 0) {
            hasValidItems = true;
        }
    });
    
    if (!hasValidItems) {
        e.preventDefault();
        alert('Please ensure all items have valid product selection and received quantity.');
        return false;
    }
});
</script>
@endsection