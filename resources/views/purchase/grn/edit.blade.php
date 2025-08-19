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
                                <tr class="item-row" data-index="{{ $index }}" data-item-id="{{ $item->id }}">
                                    <td>
                                        <select name="items[{{ $index }}][product_id]" class="form-select form-select-sm product-select" required onchange="loadProductDetails(this, {{ $index }})">
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
                                            @if(isset($serialNumbers[$item->id]) && $serialNumbers[$item->id]->count() > 0)
                                           {{ $serialNumbers[$item->id]->count() }}
                                            @endif
                                        </button>
                                        @else
                                        <button type="button" class="btn btn-sm btn-outline-info serial-btn" 
                                                onclick="manageSerialNumbers({{ $index }}, '{{ $item->product->name }}')" style="display: none;">
                                            <i class="fas fa-barcode"></i>
                                            <span class="badge bg-success ms-1 serial-count">0</span>
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
                            <td class="text-end" id="serialItemsDisplay">{{ $serialNumbers->count() }}</td>
                        </tr>
                        <tr>
                            <td>Total Serial Numbers:</td>
                            <td class="text-end" id="totalSerialsDisplay">{{ $serialNumbers->sum(fn($serials) => $serials->count()) }}</td>
                        </tr>
                        <tr>
                            <td>Documents Attached:</td>
                            <td class="text-end">{{ $grn->documents->count() }}</td>
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
    <tr class="item-row" data-index="INDEX">
        <td>
            <select name="items[INDEX][product_id]" class="form-select form-select-sm product-select" required onchange="loadProductDetails(this, INDEX)">
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
                   value="1" step="1" min="1" required onchange="calculateAcceptedQuantity(this)">
        </td>
        <td>
            <input type="number" name="items[INDEX][damaged_quantity]" class="form-control form-control-sm text-end damaged-quantity" 
                   value="0" step="1" min="0" onchange="calculateAcceptedQuantity(this)">
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
            <button type="button" class="btn btn-sm btn-outline-info serial-btn" onclick="manageSerialNumbers(INDEX, '')" style="display: none;">
                <i class="fas fa-barcode"></i>
                <span class="badge bg-success ms-1 serial-count">0</span>
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

// Load existing serial numbers from server
@if(isset($serialNumbers))
const existingSerialNumbers = @json($serialNumbers);
console.log('Existing serials from server:', existingSerialNumbers);

// Convert the server data to our format
Object.keys(existingSerialNumbers).forEach(itemId => {
    // Find the row index by item ID
    const row = $(`tr[data-item-id="${itemId}"]`);
    if (row.length > 0) {
        const rowIndex = row.data('index');
        serialNumbers[rowIndex] = existingSerialNumbers[itemId].map(serial => ({
            serial_number: serial.serial_number || '',
            warranty_start_date: serial.warranty_start_date || '',
            warranty_end_date: serial.warranty_end_date || ''
        }));
        console.log(`Loaded ${serialNumbers[rowIndex].length} serials for item ${itemId} at row ${rowIndex}`);
    }
});
@endif

console.log('Processed serial numbers:', serialNumbers);

$(document).ready(function() {
    updateSummary();
    
    // Initialize serial buttons for existing items
    $('.item-row').each(function(index) {
        const hasSerial = $(this).find('.product-select option:selected').data('has-serial') == 1;
        if (hasSerial) {
            $(this).find('.serial-btn').show();
            updateSerialCount(index);
        }
    });
    
    // Add one file upload field by default
    addFileUpload();
});

function addNewItem() {
    const template = document.getElementById('itemTemplate');
    const clone = template.content.cloneNode(true);
    
    // Replace INDEX with actual index
    const htmlString = clone.querySelector('.item-row').outerHTML.replace(/INDEX/g, itemIndex);
    
    $('#itemsTableBody').append(htmlString);
    
    // Update the data-index attribute
    $(`#itemsTableBody tr:last`).attr('data-index', itemIndex);
    
    itemIndex++;
    updateSummary();
}

function removeItem(button) {
    const row = $(button).closest('tr');
    const index = row.data('index');
    
    // Remove serial numbers for this item
    delete serialNumbers[index];
    
    row.remove();
    updateSummary();
}

function loadProductDetails(select, index) {
    const row = $(select).closest('tr');
    const selectedOption = $(select).find('option:selected');
    const hasSerial = selectedOption.data('has-serial') == 1;
    const productName = selectedOption.text();
    
    // Show/hide serial number button
    if (hasSerial) {
        row.find('.serial-btn').show();
        // Update the onclick to include product name
        row.find('.serial-btn').attr('onclick', `manageSerialNumbers(${index}, '${productName}')`);
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
    const row = $(`.item-row[data-index="${itemIndex}"]`);
    const receivedQty = parseInt(row.find('.received-quantity').val()) || 0;
    
    currentSerialItemIndex = itemIndex;
    
    $('#serialProductName').text(productName || 'Unknown Product');
    $('#serialReceivedQty').text(receivedQty);
    
    console.log(`Managing serials for item ${itemIndex}, received qty: ${receivedQty}`);
    
    // Load existing serial numbers
    loadExistingSerialNumbers(itemIndex, receivedQty);
    
    $('#serialNumbersModal').modal('show');
}

function loadExistingSerialNumbers(itemIndex, requiredQty) {
    const container = $('#serialNumbersList');
    container.empty();
    
    const existingSerials = serialNumbers[itemIndex] || [];
    console.log(`Loading serials for item ${itemIndex}:`, existingSerials);
    
    // Create inputs for existing serials
    existingSerials.forEach((serial, i) => {
        addSerialNumberInput(serial, i);
    });
    
    // If we have fewer serials than required quantity, add empty ones
    if (existingSerials.length < requiredQty) {
        for (let i = existingSerials.length; i < requiredQty; i++) {
            addSerialNumberInput({ serial_number: '', warranty_start_date: '', warranty_end_date: '' }, i);
        }
    }
}

function addSerialNumberInput(serial, index) {
    const container = $('#serialNumbersList');
    const html = `
        <div class="row mb-2 serial-row" data-serial-index="${index}">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm serial-number" 
                       placeholder="Serial Number ${index + 1}" value="${serial.serial_number || ''}" required>
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control form-control-sm warranty-start" 
                       placeholder="Warranty Start" value="${serial.warranty_start_date || ''}">
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control form-control-sm warranty-end" 
                       placeholder="Warranty End" value="${serial.warranty_end_date || ''}">
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
    
    // Reindex the remaining serial numbers
    $('#serialNumbersList .serial-row').each(function(index) {
        $(this).attr('data-serial-index', index);
        $(this).find('.serial-number').attr('placeholder', `Serial Number ${index + 1}`);
    });
}

function autoFillSerials() {
    const receivedQty = parseInt($('#serialReceivedQty').text()) || 0;
    const currentSerials = $('#serialNumbersList .serial-row').length;
    
    if (currentSerials < receivedQty) {
        for (let i = currentSerials; i < receivedQty; i++) {
            addSerialNumberInput({ serial_number: '', warranty_start_date: '', warranty_end_date: '' }, i);
        }
    }
}

function saveSerialNumbers() {
    const serials = [];
    
    $('#serialNumbersList .serial-row').each(function() {
        const serialNumber = $(this).find('.serial-number').val().trim();
        const warrantyStart = $(this).find('.warranty-start').val();
        const warrantyEnd = $(this).find('.warranty-end').val();
        
        // Only save serials that have a serial number
        if (serialNumber) {
            serials.push({
                serial_number: serialNumber,
                warranty_start_date: warrantyStart,
                warranty_end_date: warrantyEnd
            });
        }
    });
    
    console.log(`Saving ${serials.length} serials for item ${currentSerialItemIndex}:`, serials);
    
    // Store in our global object
    serialNumbers[currentSerialItemIndex] = serials;
    
    // Remove existing hidden inputs for this item
    const row = $(`.item-row[data-index="${currentSerialItemIndex}"]`);
    row.find('.serial-inputs').remove();
    
    // Add new hidden inputs
    serials.forEach((serial, index) => {
        row.append(`
            <input type="hidden" name="items[${currentSerialItemIndex}][serial_numbers][${index}][serial_number]" value="${serial.serial_number}" class="serial-inputs">
            <input type="hidden" name="items[${currentSerialItemIndex}][serial_numbers][${index}][warranty_start_date]" value="${serial.warranty_start_date}" class="serial-inputs">
            <input type="hidden" name="items[${currentSerialItemIndex}][serial_numbers][${index}][warranty_end_date]" value="${serial.warranty_end_date}" class="serial-inputs">
        `);
    });
    
    // Update the serial count badge
    updateSerialCount(currentSerialItemIndex);
    
    $('#serialNumbersModal').modal('hide');
    updateSummary();
}

function updateSerialCount(itemIndex) {
    const row = $(`.item-row[data-index="${itemIndex}"]`);
    const serialCount = (serialNumbers[itemIndex] || []).length;
    const badge = row.find('.serial-count');
    
    badge.text(serialCount);
    
    if (serialCount > 0) {
        badge.removeClass('bg-secondary').addClass('bg-success');
    } else {
        badge.removeClass('bg-success').addClass('bg-secondary');
    }
}

function updateSummary() {
    let totalItems = 0;
    let totalQuantity = 0;
    let acceptedQuantity = 0;
    let damagedQuantity = 0;
    let serialItems = 0;
    let newDocumentsCount = 0;
    let totalSerials = 0;
    
    $('.item-row').each(function() {
        const receivedQty = parseFloat($(this).find('.received-quantity').val()) || 0;
        const damagedQty = parseFloat($(this).find('.damaged-quantity').val()) || 0;
        const hasSerial = $(this).find('.serial-btn').is(':visible');
        const itemIndex = $(this).data('index');
        
        if (receivedQty > 0) {
            totalItems++;
            totalQuantity += receivedQty;
            acceptedQuantity += (receivedQty - damagedQty);
            damagedQuantity += damagedQty;
            
            if (hasSerial) {
                serialItems++;
                const itemSerials = serialNumbers[itemIndex] || [];
                totalSerials += itemSerials.length;
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
     $('#totalSerialsDisplay').text(totalSerials);
}

// Update summary when files are selected
$(document).on('change', 'input[type="file"]', function() {
    updateSummary();
});
function addFileUpload() {
    const template = document.getElementById('fileUploadTemplate');
    const clone = template.content.cloneNode(true);
    $('#fileUploadsContainer').append(clone);
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
                $(`#existing-file-${fileId}`).fadeOut(() => {
                    $(`#existing-file-${fileId}`).remove();
                });
                alert('File deleted successfully.');
            },
            error: function(xhr) {
                console.error('Delete error:', xhr);
                alert('Error deleting file: ' + (xhr.responseJSON?.error || 'Unknown error'));
            }
        });
    }
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