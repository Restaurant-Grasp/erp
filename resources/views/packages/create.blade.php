@extends('layouts.app')

@section('title', 'Create Package')

@section('content')
<div class="page-header">
    <h1 class="page-title">Create New Package</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('packages.index') }}">Packages</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<form action="{{ route('packages.store') }}" method="POST" id="packageForm">
    @csrf
    <div class="row">
        <div class="col-md-12">
            <!-- Package Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Package Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Package Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Package Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                value="{{ old('code') }}" required>
                            @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Validity (Days)</label>
                            <input type="number" name="validity_days" class="form-control" 
                                value="{{ old('validity_days') }}" min="1">
                            <small class="text-muted">Leave empty for lifetime validity</small>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                        </div>
                        
                   
                        <div class="col-md-3">
                            <div class="form-check">
                                <input type="checkbox" name="status" id="status" 
                                    class="form-check-input" value="1" {{ old('status', '1') ? 'checked' : '' }}>
                                <label class="form-check-label" for="status">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Package Items -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Package Items</h5>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addItem()">
                        <i class="fas fa-plus me-2"></i>Add Item
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="itemsTable">
                            <thead>
                                <tr>
                                    <th width="120">Type</th>
                                    <th>Item</th>
                                    <th width="100">Quantity</th>
                                    <th width="150">Unit Price</th>
                                    <th width="150">Total</th>
                                    <th width="80">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                <!-- Items will be added here dynamically -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                    <td><strong id="subtotalAmount">RM 0.00</strong></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end">
                                        <label for="discount_percentage"><strong>Package Discount (%):</strong></label>
                                    </td>
                                    <td>
                                        <input type="number" name="discount_percentage" id="discount_percentage" 
                                            class="form-control" value="{{ old('discount_percentage', '0') }}" 
                                            min="0" max="100" step="0.01" onchange="calculateTotals()">
                                    </td>
                                    <td>
                                        <span id="discountAmount">RM 0.00</span>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr class="table-success">
                                    <td colspan="4" class="text-end"><strong>Final Total:</strong></td>
                                    <td><strong id="finalTotal">RM 0.00</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="text-start">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Create Package
                </button>
                <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary me-2">
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
                <option value="">Select Type</option>
                <option value="service">Service</option>
                <option value="product">Product</option>
            </select>
        </td>
        <td>
            <select name="items[${itemIndex}][item_id]" class="form-select item-select" required onchange="updateItemDetails(this, ${itemIndex})">
                <option value="">Select Item</option>
            </select>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity" 
                required min="1" value="1" onchange="calculateRowTotal(${itemIndex})">
        </td>
        <td>
             <input type="number" name="items[${itemIndex}][unit_price]" class="form-control unit-price-input" 
                required min="0" step="0.01" value="0" onchange="calculateRowTotal(${itemIndex})">
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
}

function removeItem(button) {
    button.closest('tr').remove();
    calculateTotals();
}

function loadItems(selectElement, index) {
    const itemType = selectElement.value;
    const itemSelect = selectElement.closest('tr').querySelector('.item-select');
    
    itemSelect.innerHTML = '<option value="">Loading...</option>';
    
    if (itemType) {
  fetch(`/packages/get-services?type=${itemType}`)
            .then(response => response.json())
            .then(data => {
                itemSelect.innerHTML = '<option value="">Select Item</option>';
                data.forEach(item => {
                    itemSelect.innerHTML += `<option value="${item.id}" data-price="${item.price}" data-code="${item.code}">${item.name}</option>`;
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
    const row = selectElement.closest('tr');
    
    if (option && option.dataset.price) {
        const unitPrice = parseFloat(option.dataset.price);
        row.querySelector('.unit-price').textContent = `RM ${unitPrice.toFixed(2)}`;
        row.querySelector('.unit-price-input').value = unitPrice;
        calculateRowTotal(index);
    }
}

function calculateRowTotal(index) {
    const rows = document.querySelectorAll('#itemsTableBody tr');
    const row = rows[index];
    
    if (!row) return;
    
    const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
    const unitPrice = parseFloat(row.querySelector('.unit-price-input').value) || 0;
    const rowTotal = quantity * unitPrice;
    
    row.querySelector('.row-total').textContent = `RM ${rowTotal.toFixed(2)}`;
    
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    
    // Calculate subtotal from all rows
    document.querySelectorAll('#itemsTableBody tr').forEach(row => {
        const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.unit-price-input').value) || 0;
        subtotal += quantity * unitPrice;
    });
    
    // Apply package discount
    const discountPercentage = parseFloat(document.getElementById('discount_percentage').value) || 0;
    const discountAmount = (subtotal * discountPercentage) / 100;
    const finalTotal = subtotal - discountAmount;
    
    // Update display
    document.getElementById('subtotalAmount').textContent = `RM ${subtotal.toFixed(2)}`;
    document.getElementById('discountAmount').textContent = `RM ${discountAmount.toFixed(2)}`;
    document.getElementById('finalTotal').textContent = `RM ${finalTotal.toFixed(2)}`;
}

$(document).ready(function() {
    // Add initial item
    addItem();
    
    // Form validation
    $('#packageForm').on('submit', function(e) {
        const hasItems = $('#itemsTableBody tr').length > 0;
        
        if (!hasItems) {
            e.preventDefault();
            alert('Please add at least one item to the package.');
            return false;
        }
        
        // Check if all items are properly filled
        let hasValidItems = true;
        $('#itemsTableBody tr').each(function() {
            const itemType = $(this).find('.item-type').val();
            const itemId = $(this).find('.item-select').val();
            const quantity = $(this).find('.quantity').val();
            const unitPrice = $(this).find('.unit-price-input').val();
            if (!itemType || !itemId || !quantity || quantity <= 0 || !unitPrice || unitPrice <= 0) {
                hasValidItems = false;
                return false;
            }
        });
        
        if (!hasValidItems) {
            e.preventDefault();
            alert('Please complete all item details and ensure quantities are greater than 0.');
            return false;
        }
    });
});
</script>
@endsection