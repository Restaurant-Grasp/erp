@extends('layouts.app')

@section('title', 'Edit Purchase Invoice')
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
    <h1 class="page-title">Edit Purchase Invoice</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.invoices.index') }}">Purchase Invoices</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.invoices.show', $invoice) }}">{{ $invoice->invoice_no }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form action="{{ route('purchase.invoices.update', $invoice) }}" method="POST" id="invoiceForm" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-8">
            <!-- Invoice Information -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Invoice Information</h5>
                    <div>
                        <span class="badge bg-{{ $invoice->status_badge }} fs-6">{{ ucfirst($invoice->status) }}</span>
                        <span class="badge bg-{{ $invoice->invoice_type_badge }} fs-6">
                            {{ $invoice->invoice_type === 'po_conversion' ? 'PO Conversion' : 'Direct Invoice' }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Vendor <span class="text-danger">*</span></label>
                            <select name="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror" required>
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ old('vendor_id', $invoice->vendor_id) == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->company_name }} ({{ $vendor->vendor_code }})
                                </option>
                                @endforeach
                            </select>
                            @error('vendor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Invoice No</label>
                            <input type="text" class="form-control" value="{{ $invoice->invoice_no }}" readonly>
                            <small class="text-muted">System generated number</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Vendor Invoice No</label>
                            <input type="text" name="vendor_invoice_no" class="form-control @error('vendor_invoice_no') is-invalid @enderror" 
                                   value="{{ old('vendor_invoice_no', $invoice->vendor_invoice_no) }}" placeholder="Vendor's invoice number">
                            @error('vendor_invoice_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                            <label class="form-label">Payment Terms (Days) <span class="text-danger">*</span></label>
                            <input type="number" name="payment_terms" class="form-control @error('payment_terms') is-invalid @enderror" 
                                   value="{{ old('payment_terms', $invoice->payment_terms) }}" min="0" required>
                            @error('payment_terms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                            <select name="currency" class="form-select @error('currency') is-invalid @enderror" required>
                                <option value="MYR" {{ old('currency', $invoice->currency) == 'MYR' ? 'selected' : '' }}>MYR</option>
                                <option value="USD" {{ old('currency', $invoice->currency) == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="SGD" {{ old('currency', $invoice->currency) == 'SGD' ? 'selected' : '' }}>SGD</option>
                                <option value="EUR" {{ old('currency', $invoice->currency) == 'EUR' ? 'selected' : '' }}>EUR</option>
                            </select>
                            @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Exchange Rate <span class="text-danger">*</span></label>
                            <input type="number" name="exchange_rate" class="form-control @error('exchange_rate') is-invalid @enderror" 
                                   value="{{ old('exchange_rate', $invoice->exchange_rate) }}" step="0.0001" min="0.0001" required>
                            @error('exchange_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @if($invoice->purchaseOrder)
                        <div class="col-md-6">
                            <label class="form-label">Purchase Order</label>
                            <div class="form-control-plaintext">
                                <a href="{{ route('purchase.orders.show', $invoice->purchaseOrder) }}" class="text-decoration-none">
                                    {{ $invoice->purchaseOrder->po_no }}
                                </a>
                                <small class="text-muted d-block">This invoice was created from a Purchase Order</small>
                            </div>
                        </div>
                        @endif
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
                                @foreach($invoice->items as $index => $item)
                                <tr class="item-row">
                                    <td>
                                        <select name="items[{{ $index }}][item_type]" class="form-select form-select-sm item-type" required onchange="loadItems(this)">
                                            <option value="">Select Type</option>
                                            <option value="product" {{ $item->item_type == 'product' ? 'selected' : '' }}>Product</option>
                                            <option value="service" {{ $item->item_type == 'service' ? 'selected' : '' }}>Service</option>
                                        </select>
                                        <input type="hidden" name="items[{{ $index }}][po_item_id]" class="po-item-id" value="{{ $item->po_item_id }}">
                                    </td>
                                    <td>
                                        <select name="items[{{ $index }}][item_id]" class="form-select form-select-sm item-id" required onchange="loadItemDetails(this)">
                                            <option value="">Select Item</option>
                                            @if($item->item_type === 'product')
                                                @foreach($products as $product)
                                                <option value="{{ $product->id }}" {{ $item->item_id == $product->id ? 'selected' : '' }} 
                                                        data-price="{{ $product->cost_price }}" data-uom="{{ $product->uom_id }}">
                                                    {{ $product->name }}
                                                </option>
                                                @endforeach
                                            @else
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
                                               value="{{ $item->quantity }}" step="1" min="1" required onchange="calculateItemTotal(this)">
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

            <!-- File Attachments -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-paperclip me-2"></i>Vendor Invoice Documents
                    </h5>
                    <button type="button" class="btn btn-success btn-sm" onclick="addFileUpload()">
                        <i class="fas fa-plus me-2"></i> Add File
                    </button>
                </div>
                <div class="card-body">
                    <!-- Existing Files -->
                    @if($invoice->files->count() > 0)
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Existing Files:</h6>
                        <div class="row g-3">
                            @foreach($invoice->files as $file)
                            <div class="col-md-6">
                                <div class="card border" id="existing-file-{{ $file->id }}">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    @if($file->is_image)
                                                        <i class="fas fa-image text-info me-2"></i>
                                                    @elseif($file->is_pdf)
                                                        <i class="fas fa-file-pdf text-danger me-2"></i>
                                                    @else
                                                        <i class="fas fa-file text-primary me-2"></i>
                                                    @endif
                                                    {{ $file->file_name }}
                                                </h6>
                                                @if($file->description)
                                                <p class="mb-1 text-muted small">{{ $file->description }}</p>
                                                @endif
                                                <small class="text-muted">
                                                    {{ $file->formatted_file_size }} • 
                                                    {{ $file->file_extension }} • 
                                                    {{ $file->uploaded_at->format('d/m/Y H:i') }}
                                                </small>
                                            </div>
                                            <div class="ms-2">
                                                <a href="{{ route('purchase.invoices.files.download', $file) }}" 
                                                   class="btn btn-sm btn-outline-primary me-1" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteExistingFile({{ $file->id }})" title="Delete">
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
                        <br><strong>Maximum file size:</strong> 20MB per file
                    </div>
                    <div id="fileUploadsContainer">
                        <!-- File upload fields will be added here -->
                    </div>
                    @error('files.*')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Notes -->
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Notes</h5></div>
                <div class="card-body">
                    <textarea name="notes" class="form-control" rows="4" 
                              placeholder="Internal notes...">{{ old('notes', $invoice->notes) }}</textarea>
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
                            <td class="text-end" id="subtotalDisplay">{{ number_format($invoice->subtotal, 2) }}</td>
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
                                       value="{{ $invoice->discount_value ?? 0 }}" step="0.01" min="0" onchange="calculateTotals()">
                            </td>
                        </tr>
                        <tr>
                            <td>Discount Amount:</td>
                            <td class="text-end" id="discountDisplay">{{ number_format($invoice->discount_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Tax Amount:</td>
                            <td class="text-end" id="taxDisplay">{{ number_format($invoice->tax_amount, 2) }}</td>
                        </tr>
                        <tr class="table-primary">
                            <td><strong>Total Amount:</strong></td>
                            <td class="text-end"><strong id="totalDisplay">{{ number_format($invoice->total_amount, 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Current Status -->
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Current Status</h5></div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Status:</strong> {{ ucfirst($invoice->status) }}
                        <br><strong>Created:</strong> {{ $invoice->created_at->format('d/m/Y H:i') }}
                        @if($invoice->received_percentage > 0)
                        <br><strong>Received:</strong> {{ number_format($invoice->received_percentage, 1) }}%
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update Invoice
                        </button>
                        <a href="{{ route('purchase.invoices.show', $invoice) }}" class="btn btn-outline-secondary">
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
            <input type="hidden" name="items[INDEX][po_item_id]" class="po-item-id">
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
                   value="1" step="1" min="1" required onchange="calculateItemTotal(this)">
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

<!-- File Upload Template -->
<template id="fileUploadTemplate">
    <div class="file-upload-row border rounded p-3 mb-3">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Select File</label>
                <input type="file" name="files[]" class="form-control" 
                       accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx">
            </div>
            <div class="col-md-5">
                <label class="form-label">Description (Optional)</label>
                <input type="text" name="file_descriptions[]" class="form-control" 
                       placeholder="Brief description of the file">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeFileUpload(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let itemIndex = {{ $invoice->items->count() }};
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

    // Initialize calculations
    calculateTotals();
        addNewItem();
    addFileUpload();
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

function addFileUpload() {
    const template = document.getElementById('fileUploadTemplate');
    const clone = template.content.cloneNode(true);
    $('#fileUploadsContainer').append(clone);
}

function removeFileUpload(button) {
    $(button).closest('.file-upload-row').remove();
}

function deleteExistingFile(fileId) {
    if (confirm('Are you sure you want to delete this file?')) {
        $.ajax({
            url: `/purchase/invoices/files/${fileId}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $(`#existing-file-${fileId}`).remove();
                alert('File deleted successfully.');
            },
            error: function(xhr) {
                alert('Error deleting file: ' + xhr.responseJSON.error);
            }
        });
    }
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
    const currentItemId = itemSelect.val();
    
    itemSelect.empty().append('<option value="">Select Item</option>');
    
    if (itemType === 'product') {
        products.forEach(product => {
            const vendorProduct = vendorProducts.find(vp => vp.id === product.id);
            const price = vendorProduct ? vendorProduct.vendor_price : product.cost_price;
            const preferred = vendorProduct ? (vendorProduct.is_preferred ? ' (Preferred)' : '') : '';
            const selected = currentItemId == product.id ? 'selected' : '';
            
            itemSelect.append(`<option value="${product.id}" data-price="${price}" data-uom="${product.uom_id}" ${selected}>${product.name}${preferred}</option>`);
        });
    } else if (itemType === 'service') {
        services.forEach(service => {
            const selected = currentItemId == service.id ? 'selected' : '';
            itemSelect.append(`<option value="${service.id}" data-price="${service.base_price}" ${selected}>${service.name}</option>`);
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

// Initialize item dropdowns on page load
$(document).ready(function() {
    $('.item-type').each(function() {
        if ($(this).val()) {
            loadItems(this);
        }
    });
});

// Form validation
$('#invoiceForm').on('submit', function(e) {
    if ($('.item-row').length === 0) {
        e.preventDefault();
        alert('Please add at least one item to the purchase invoice.');
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