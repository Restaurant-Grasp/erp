@extends('layouts.app')

@section('title', 'Edit Goods Receipt Note')
<style>
#itemsTable{
	width: 130%;
}
.card-body .table-responsive{
	overflow-x: scroll;
}
.input-group-sm>.form-select{
	padding-right: 0rem;
}
</style>
@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Goods Receipt Note</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.grn.index') }}">Goods Receipt Notes</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.grn.show', $grn) }}">{{ $grn->grn_no }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form action="{{ route('purchase.grn.update', $grn) }}" method="POST" id="grnForm" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-8">
            <!-- GRN Information -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">GRN Information - {{ $grn->grn_no }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Vendor <span class="text-danger">*</span></label>
                            <select name="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror" required>
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ old('vendor_id', $grn->vendor_id) == $vendor->id ? 'selected' : '' }}>
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
                                   value="{{ old('grn_date', $grn->grn_date->format('Y-m-d')) }}" required>
                            @error('grn_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Received By <span class="text-danger">*</span></label>
                            <select name="received_by" class="form-select @error('received_by') is-invalid @enderror" required>
                                <option value="">Select Staff</option>
                                @foreach($staff as $member)
                                <option value="{{ $member->id }}" {{ old('received_by', $grn->received_by) == $member->id ? 'selected' : '' }}>
                                    {{ $member->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('received_by')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        @if($grn->purchaseOrder)
                        <div class="col-md-6">
                            <label class="form-label">Purchase Order</label>
                            <div class="form-control-plaintext">
                                <a href="{{ route('purchase.orders.show', $grn->purchaseOrder) }}" class="text-decoration-none">
                                    <span class="badge bg-info">{{ $grn->purchaseOrder->po_no }}</span>
                                </a>
                            </div>
                        </div>
                        @endif
                        
                        @if($grn->purchaseInvoice)
                        <div class="col-md-6">
                            <label class="form-label">Purchase Invoice</label>
                            <div class="form-control-plaintext">
                                <a href="{{ route('purchase.invoices.show', $grn->purchaseInvoice) }}" class="text-decoration-none">
                                    <span class="badge bg-success">{{ $grn->purchaseInvoice->invoice_no }}</span>
                                </a>
                            </div>
                        </div>
                        @endif
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
                                    <th width="100">Received Qty</th>
                                    <th width="100">Damaged Qty</th>
                                    <th width="80">UOM</th>
                                    <th width="120">Batch No</th>
                                    <th width="120">Expiry Date</th>
                                    <th width="150">Damage Reason</th>
                                    <th width="50">Replacement</th>
                                    <th width="50">Serial</th>
                                    <th width="50">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                @foreach($grn->items as $index => $item)
                                <tr class="item-row" data-index="{{ $index }}">
                                    <td>
                                        <select name="items[{{ $index }}][product_id]" class="form-select form-select-sm product-select" required onchange="loadProductDetails(this)">
                                            <option value="">Select Product</option>
                                            @foreach($products as $product)
                                            <option value="{{ $product->id }}" 
                                                    data-has-serial="{{ $product->has_serial_number }}" 
                                                    data-has-warranty="{{ $product->has_warranty }}" 
                                                    data-warranty-months="{{ $product->warranty_period_months }}"
                                                    {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }} ({{ $product->product_code }})
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm text-end received-quantity" 
                                               value="{{ $item->quantity }}" step="1" min="1" required onchange="calculateAcceptedQuantity(this)">
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][damaged_quantity]" class="form-control form-control-sm text-end damaged-quantity" 
                                               value="{{ $item->damaged_quantity }}" step="1" min="0" onchange="calculateAcceptedQuantity(this)">
                                    </td>
                                    <td>
                                        <select name="items[{{ $index }}][uom_id]" class="form-select form-select-sm">
                                            <option value="">UOM</option>
                                            @foreach($uoms as $uom)
                                            <option value="{{ $uom->id }}" {{ $item->uom_id == $uom->id ? 'selected' : '' }}>
                                                {{ $uom->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][batch_no]" class="form-control form-control-sm batch-no" 
                                               placeholder="Batch" value="{{ $item->batch_no }}">
                                    </td>
                                    <td>
                                        <input type="date" name="items[{{ $index }}][expiry_date]" class="form-control form-control-sm expiry-date" 
                                               value="{{ $item->expiry_date ? $item->expiry_date->format('Y-m-d') : '' }}">
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][damage_reason]" class="form-control form-control-sm damage-reason" 
                                               placeholder="Damage reason" value="{{ $item->damage_reason }}">
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check">
                                            <input type="checkbox" name="items[{{ $index }}][replacement_required]" 
                                                   class="form-check-input replacement-required" value="1" 
                                                   {{ $item->replacement_required ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($item->product->has_serial_number)
                                        <button type="button" class="btn btn-sm btn-outline-info serial-btn" 
                                                onclick="manageSerialNumbers({{ $index }}, '{{ $item->product->name }}')">
                                            <i class="fas fa-barcode"></i>
                                        </button>
                                        @endif
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

            <!-- File Attachments -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-paperclip me-2"></i>Delivery Order Documents
                    </h5>
                    <button type="button" class="btn btn-success btn-sm" onclick="addFileUpload()">
                        <i class="fas fa-plus me-2"></i> Add File
                    </button>
                </div>
                <div class="card-body">
                    <!-- Existing Files -->
                    @if($grn->documents->count() > 0)
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Existing Files:</h6>
                        <div class="row g-3">
                            @foreach($grn->documents as $document)
                            <div class="col-md-6">
                                <div class="card border" id="existing-file-{{ $document->id }}">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <i class="{{ $document->icon_class }} me-2"></i>
                                                    {{ $document->original_name }}
                                                </h6>
                                                @if($document->description)
                                                <p class="mb-1 text-muted small">{{ $document->description }}</p>
                                                @endif
                                                <small class="text-muted">
                                                    {{ $document->formatted_size }} • 
                                                    {{ $document->extension }} • 
                                                    {{ $document->created_at->format('d/m/Y H:i') }}
                                                </small>
                                            </div>
                                            <div class="ms-2">
                                                <a href="{{ route('purchase.grn.documents.download', $document) }}" 
                                                   class="btn btn-sm btn-outline-primary me-1" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteExistingFile({{ $document->id }})" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- New File Uploads -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Supported file types:</strong> PDF, Images (JPG, PNG, GIF), Documents (DOC, DOCX, XLS, XLSX)
                        <br><strong>Maximum file size:</strong> 10MB per file
                    </div>
                    <div id="fileUploadsContainer">
                        <!-- File upload fields will be added here -->
                    </div>
                    @error('documents.*')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Notes -->
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Notes</h5></div>
                <div class="card-body">
                    <textarea name="notes" class="form-control" rows="4" 
                              placeholder="Enter any notes about the goods receipt...">{{ old('notes', $grn->notes) }}</textarea>
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
                            <td class="text-end" id="totalItemsDisplay">{{ $grn->items->count() }}</td>
                        </tr>
                        <tr>
                            <td>Total Quantity:</td>
                            <td class="text-end" id="totalQuantityDisplay">{{ number_format($grn->items->sum('quantity'), 2) }}</td>
                        </tr>
                        <tr>
                            <td>Accepted Quantity:</td>
                            <td class="text-end text-success" id="acceptedQuantityDisplay">{{ number_format($grn->items->sum('accepted_quantity'), 2) }}</td>
                        </tr>
                        <tr>
                            <td>Damaged Quantity:</td>
                            <td class="text-end text-danger" id="damagedQuantityDisplay">{{ number_format($grn->items->sum('damaged_quantity'), 2) }}</td>
                        </tr>
                        <tr>
                            <td>Items with Serial Numbers:</td>
                            <td class="text-end" id="serialItemsDisplay">0</td>
                        </tr>
                        <tr>
                            <td>New Documents:</td>
                            <td class="text-end" id="newDocumentsDisplay">0</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Warning -->
            <div class="card mt-4">
                <div class="card-header bg-warning">
                    <h5 class="mb-0 text-dark"><i class="fas fa-exclamation-triangle me-2"></i>Important</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <p class="mb-2"><strong>Note:</strong> Editing this GRN will:</p>
                        <ul class="mb-0">
                            <li>Update inventory transactions</li>
                            <li>Regenerate serial numbers</li>
                            <li>Update related PO/Invoice status</li>
                            <li>Affect any existing returns</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update GRN
                        </button>
                        <a href="{{ route('purchase.grn.show', $grn) }}" class="btn btn-outline-secondary">
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
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-info serial-btn" onclick="manageSerialNumbers(INDEX)" style="display: none;">
                <i class="fas fa-barcode"></i>
            </button>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

<!-- File Upload Template -->
<template id="fileUploadTemplate">
    <div class="file-upload-row mb-3">
        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Document File</label>
                <input type="file" name="documents[]" class="form-control" 
                       accept=".pdf,.jpg,.jpeg,.png,.gif,.bmp,.doc,.docx,.xls,.xlsx">
            </div>
            <div class="col-md-5">
                <label class="form-label">Description (Optional)</label>
                <input type="text" name="document_descriptions[]" class="form-control" 
                       placeholder="Brief description of the document">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeFileUpload(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
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
let itemIndex = {{ $grn->items->count() }};
let currentSerialItemIndex = 0;
let serialNumbers = {};

// Load existing serial numbers
@if(isset($serialNumbers))
const existingSerialNumbers = @json($serialNumbers);
Object.keys(existingSerialNumbers).forEach(itemId => {
    const itemIndex = $('.item-row').find(`select[value="${itemId}"]`).closest('tr').index();
    if (itemIndex >= 0) {
        serialNumbers[itemIndex] = existingSerialNumbers[itemId].map(serial => ({
            serial_number: serial.serial_number,
            warranty_start_date: serial.warranty_start_date,
            warranty_end_date: serial.warranty_end_date
        }));
    }
});
@endif

$(document).ready(function() {
    updateSummary();
    
    // Initialize serial buttons for existing items
    $('.item-row').each(function(index) {
        const hasSerial = $(this).find('.product-select option:selected').data('has-serial') == 1;
        if (hasSerial) {
            $(this).find('.serial-btn').show();
        }
    });
    
    // Add first file upload field by default
    addFileUpload();
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

function manageSerialNumbers(itemIndex, productName) {
    const row = $('.item-row').eq(itemIndex);
    const receivedQty = parseInt(row.find('.received-quantity').val()) || 0;
    
    currentSerialItemIndex = itemIndex;
    
    $('#serialProductName').text(productName || 'Unknown Product');
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
    const row = $('.item-row').eq(currentSerialItemIndex);
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

function addFileUpload() {
    const template = document.getElementById('fileUploadTemplate');
    if (template) {
        const clone = template.content.cloneNode(true);
        $('#fileUploadsContainer').append(clone);
        updateSummary();
    }
}

function removeFileUpload(button) {
    $(button).closest('.file-upload-row').remove();
    updateSummary();
}

function deleteExistingFile(fileId) {
    if (confirm('Are you sure you want to delete this file?')) {
        $.ajax({
            url: `/purchase/grn/documents/${fileId}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $(`#existing-file-${fileId}`).remove();
                alert('File deleted successfully.');
            },
            error: function(xhr) {
                alert('Error deleting file: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Unknown error'));
            }
        });
    }
}

function updateSummary() {
    let totalItems = 0;
    let totalQuantity = 0;
    let acceptedQuantity = 0;
    let damagedQuantity = 0;
    let serialItems = 0;
    let newDocumentsCount = 0;
    
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
    
    // Count new documents with files selected
    $('.file-upload-row input[type="file"]').each(function() {
        if (this.files && this.files.length > 0) {
            newDocumentsCount++;
        }
    });
    
    $('#totalItemsDisplay').text(totalItems);
    $('#totalQuantityDisplay').text(totalQuantity.toFixed(2));
    $('#acceptedQuantityDisplay').text(acceptedQuantity.toFixed(2));
    $('#damagedQuantityDisplay').text(damagedQuantity.toFixed(2));
    $('#serialItemsDisplay').text(serialItems);
    $('#newDocumentsDisplay').text(newDocumentsCount);
}

// Update summary when files are selected
$(document).on('change', 'input[type="file"]', function() {
    updateSummary();
});

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