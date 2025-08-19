@extends('layouts.app')

@section('title', 'Create GRN from Invoice')

@section('content')
<div class="page-header">
    <h1 class="page-title">Create GRN from Invoice</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.grn.index') }}">Goods Receipt Notes</a></li>
            <li class="breadcrumb-item active">Create from Invoice</li>
        </ol>
    </nav>
</div>

<!-- Invoice Information -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Invoice Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <strong>Invoice No:</strong><br>
                <span class="text-primary">{{ $invoice->invoice_no }}</span>
            </div>
            <div class="col-md-3">
                <strong>Vendor:</strong><br>
                {{ $invoice->vendor->company_name }}
            </div>
            <div class="col-md-3">
                <strong>Invoice Date:</strong><br>
                {{ $invoice->invoice_date->format('d/m/Y') }}
            </div>
            <div class="col-md-3">
                <strong>Total Amount:</strong><br>
                {{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}
            </div>
        </div>
        @if($invoice->purchaseOrder)
        <div class="row mt-2">
            <div class="col-md-12">
                <strong>Related PO:</strong> 
                <a href="{{ route('purchase.orders.show', $invoice->purchaseOrder) }}" class="text-decoration-none">
                    {{ $invoice->purchaseOrder->po_no }}
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

<form action="{{ route('purchase.grn.store') }}" method="POST" id="grnForm">
    @csrf
    <input type="hidden" name="vendor_id" value="{{ $invoice->vendor_id }}">
    <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
    @if($invoice->purchaseOrder)
    <input type="hidden" name="po_id" value="{{ $invoice->purchaseOrder->id }}">
    @endif

    <div class="row">
        <div class="col-md-8">
            <!-- GRN Information -->
            <div class="card">
                <div class="card-header"><h5 class="mb-0">GRN Information</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">GRN Date <span class="text-danger">*</span></label>
                            <input type="date" name="grn_date" class="form-control @error('grn_date') is-invalid @enderror" 
                                   value="{{ old('grn_date', date('Y-m-d')) }}" required>
                            @error('grn_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
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
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Invoice Items to Receive</h5>
                    <div>
                        <button type="button" class="btn btn-success btn-sm" onclick="selectAllItems()">
                            <i class="fas fa-check-double me-2"></i> Select All
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="markAllAsReceived()">
                            <i class="fas fa-truck me-2"></i> Mark All as Received
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="50">Select</th>
                                    <th>Product</th>
                                    <th width="100">Invoiced Qty</th>
                                    <th width="100">Already Received</th>
                                    <th width="100">Remaining</th>
                                    <th width="100">Receive Now</th>
                                    <th width="100">Damaged</th>
                                    <th width="80">UOM</th>
                                    <th width="120">Batch No</th>
                                    <th width="120">Expiry Date</th>
                                    <th width="50">Serial</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $index => $item)
                                @if($item->item_type === 'product')
                                <tr class="item-row">
                                    <td class="text-center">
                                        <div class="form-check">
                                            <input class="form-check-input item-checkbox" type="checkbox" 
                                                   value="{{ $item->id }}" id="item_{{ $item->id }}"
                                                   onchange="toggleItem(this, {{ $index }})">
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ @$item->product->name }}</strong><br>
                                        <small class="text-muted">{{ @$item->product->product_code }}</small>
                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}" disabled>
                                        <input type="hidden" name="items[{{ $index }}][invoice_item_id]" value="{{ $item->id }}" disabled>
                                        @if($item->poItem)
                                        <input type="hidden" name="items[{{ $index }}][po_item_id]" value="{{ $item->poItem->id }}" disabled>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ number_format($item->quantity, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ number_format($item->received_quantity, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        @php $remaining = $item->quantity - $item->received_quantity; @endphp
                                        <span class="badge bg-{{ $remaining > 0 ? 'warning' : 'secondary' }}">
                                            {{ number_format($remaining, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][quantity]" 
                                               class="form-control form-control-sm text-end receive-quantity" 
                                               value="{{ $remaining }}" step="1" min="0" max="{{ $remaining }}" 
                                               onchange="calculateAcceptedQuantity(this, {{ $index }})" disabled>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][damaged_quantity]" 
                                               class="form-control form-control-sm text-end damaged-quantity" 
                                               value="0" step="1" min="0" 
                                               onchange="calculateAcceptedQuantity(this, {{ $index }})" disabled>
                                    </td>
                                    <td>
                                        <select name="items[{{ $index }}][uom_id]" class="form-select form-select-sm" disabled>
                                            <option value="">UOM</option>
                                            @foreach($uoms as $uom)
                                            <option value="{{ $uom->id }}" {{ $item->uom_id == $uom->id ? 'selected' : '' }}>
                                                {{ $uom->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][batch_no]" 
                                               class="form-control form-control-sm" placeholder="Batch" disabled>
                                    </td>
                                    <td>
                                        <input type="date" name="items[{{ $index }}][expiry_date]" 
                                               class="form-control form-control-sm" disabled>
                                    </td>
                                    <td class="text-center">
                                        @if(@$item->product->has_serial_number)
                                        <button type="button" class="btn btn-sm btn-outline-info serial-btn" 
                                                onclick="manageSerialNumbers({{ $index }}, '{{ $item->product->name }}')" 
                                                style="display: none;" disabled>
                                            <i class="fas fa-barcode"></i>
                                        </button>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Damage Reason Section -->
                    <div class="row mt-3" id="damageReasonSection" style="display: none;">
                        <div class="col-md-12">
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Damage Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Damage Reason</label>
                                        <textarea class="form-control" id="globalDamageReason" rows="2" 
                                                  placeholder="Describe the reason for damaged items..."></textarea>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Replacement Required?</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="globalReplacementRequired">
                                            <label class="form-check-label" for="globalReplacementRequired">
                                                Yes, require replacement
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-warning d-block" onclick="applyDamageReasonToAll()">
                                            <i class="fas fa-copy me-2"></i> Apply to All Damaged Items
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                            <td>Selected Items:</td>
                            <td class="text-end" id="selectedItemsDisplay">0</td>
                        </tr>
                        <tr>
                            <td>Total Receiving:</td>
                            <td class="text-end" id="totalReceivingDisplay">0.00</td>
                        </tr>
                        <tr>
                            <td>Accepted Quantity:</td>
                            <td class="text-end text-success" id="acceptedQuantityDisplay">0.00</td>
                        </tr>
                        <tr>
                            <td>Damaged Quantity:</td>
                            <td class="text-end text-danger" id="damagedQuantityDisplay">0.00</td>
                        </tr>
                        <tr>
                            <td>Items with Serial Numbers:</td>
                            <td class="text-end" id="serialItemsDisplay">0</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Invoice Progress -->
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Invoice Progress</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Overall Completion</label>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: {{ $invoice->received_percentage }}%">
                                {{ number_format($invoice->received_percentage, 1) }}%
                            </div>
                        </div>
                    </div>
                    <small class="text-muted">
                        This shows how much of the invoice has been received through all GRNs.
                    </small>
                </div>
            </div>

            <!-- Actions -->
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
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
                    <p class="text-muted">Receiving Quantity: <span id="serialReceivingQty"></span></p>
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

<script>
let currentSerialItemIndex = 0;
let serialNumbers = {};

$(document).ready(function() {
    updateSummary();
});

function toggleItem(checkbox, index) {
    const row = $(checkbox).closest('tr');
    const isChecked = checkbox.checked;
    
    // Enable/disable all inputs in this row
    row.find('input, select, button').not('.item-checkbox').prop('disabled', !isChecked);
    
    // Update item name attributes
    if (isChecked) {
        row.find('input[type="hidden"]').prop('disabled', false);
    } else {
        row.find('input[type="hidden"]').prop('disabled', true);
    }
    
    updateSummary();
    updateSubmitButton();
}

function selectAllItems() {
    $('.item-checkbox').each(function() {
        if (!this.checked) {
            this.checked = true;
            toggleItem(this, $(this).closest('tr').index());
        }
    });
}

function markAllAsReceived() {
    $('.item-checkbox:checked').each(function() {
        const row = $(this).closest('tr');
        const remainingQty = parseFloat(row.find('.badge.bg-warning').text()) || 0;
        row.find('.receive-quantity').val(remainingQty);
    });
    updateSummary();
}

function calculateAcceptedQuantity(input, index) {
    const row = $(input).closest('tr');
    const receivingQty = parseFloat(row.find('.receive-quantity').val()) || 0;
    const damagedQty = parseFloat(row.find('.damaged-quantity').val()) || 0;
    
    // Ensure damaged quantity doesn't exceed receiving quantity
    if (damagedQty > receivingQty) {
        row.find('.damaged-quantity').val(receivingQty);
    }
    
    // Show/hide damage reason section
    const hasDamage = $('.damaged-quantity').toArray().some(el => parseFloat(el.value) > 0);
    if (hasDamage) {
        $('#damageReasonSection').show();
    } else {
        $('#damageReasonSection').hide();
    }
    
    updateSummary();
}

function manageSerialNumbers(itemIndex, productName) {
    const row = $('.item-row').eq(itemIndex);
    const receivingQty = parseInt(row.find('.receive-quantity').val()) || 0;
    
    currentSerialItemIndex = itemIndex;
    
    $('#serialProductName').text(productName);
    $('#serialReceivingQty').text(receivingQty);
    
    // Load existing serial numbers
    loadExistingSerialNumbers(itemIndex, receivingQty);
    
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

function applyDamageReasonToAll() {
    const reason = $('#globalDamageReason').val();
    const replacement = $('#globalReplacementRequired').is(':checked');
    
    $('.item-checkbox:checked').each(function() {
        const row = $(this).closest('tr');
        const index = row.index();
        
        // Add hidden inputs for damage reason and replacement
        row.find('.damage-inputs').remove();
        if (reason) {
            row.append(`<input type="hidden" name="items[${index}][damage_reason]" value="${reason}" class="damage-inputs">`);
        }
        if (replacement) {
            row.append(`<input type="hidden" name="items[${index}][replacement_required]" value="1" class="damage-inputs">`);
        }
    });
    
    alert('Damage information applied to all selected items.');
}

function updateSummary() {
    let selectedItems = 0;
    let totalReceiving = 0;
    let acceptedQuantity = 0;
    let damagedQuantity = 0;
    let serialItems = 0;
    
    $('.item-checkbox:checked').each(function() {
        const row = $(this).closest('tr');
        const receivingQty = parseFloat(row.find('.receive-quantity').val()) || 0;
        const damagedQty = parseFloat(row.find('.damaged-quantity').val()) || 0;
        const hasSerial = row.find('.serial-btn').length > 0;
        
        selectedItems++;
        totalReceiving += receivingQty;
        acceptedQuantity += (receivingQty - damagedQty);
        damagedQuantity += damagedQty;
        
        if (hasSerial) {
            serialItems++;
        }
    });
    
    $('#selectedItemsDisplay').text(selectedItems);
    $('#totalReceivingDisplay').text(totalReceiving.toFixed(2));
    $('#acceptedQuantityDisplay').text(acceptedQuantity.toFixed(2));
    $('#damagedQuantityDisplay').text(damagedQuantity.toFixed(2));
    $('#serialItemsDisplay').text(serialItems);
}

function updateSubmitButton() {
    const hasSelectedItems = $('.item-checkbox:checked').length > 0;
    $('#submitBtn').prop('disabled', !hasSelectedItems);
}

// Form validation
$('#grnForm').on('submit', function(e) {
    const selectedItems = $('.item-checkbox:checked').length;
    
    if (selectedItems === 0) {
        e.preventDefault();
        alert('Please select at least one item to receive.');
        return false;
    }
    
    let hasValidQuantities = true;
    $('.item-checkbox:checked').each(function() {
        const row = $(this).closest('tr');
        const receivingQty = parseFloat(row.find('.receive-quantity').val()) || 0;
        
        if (receivingQty <= 0) {
            hasValidQuantities = false;
        }
    });
    
    if (!hasValidQuantities) {
        e.preventDefault();
        alert('Please ensure all selected items have valid receiving quantities.');
        return false;
    }
});
</script>
@endsection