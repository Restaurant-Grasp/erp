
@extends('layouts.app')

@section('title', 'Create Delivery Order')
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
    <h1 class="page-title">Create New Delivery Order</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sales.delivery-orders.index') }}">Delivery Orders</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<form action="{{ route('sales.delivery-orders.store') }}" method="POST" id="deliveryOrderForm">
    @csrf
    <div class="row">
        <div class="col-md-12">
            <!-- Header Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Delivery Order Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">DO Date <span class="text-danger">*</span></label>
                            <input type="date" name="do_date" class="form-control @error('do_date') is-invalid @enderror" 
                                   value="{{ old('do_date', date('Y-m-d')) }}" required>
                            @error('do_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $invoice?->customer_id) == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->company_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Invoice</label>
                            <select name="invoice_id" id="invoice_id" class="form-select">
                                <option value="">Select Invoice (Optional)</option>
                                @if($invoice)
                                <option value="{{ $invoice->id }}" selected>{{ $invoice->invoice_no }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Delivery Date</label>
                            <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Delivery Address <span class="text-danger">*</span></label>
                            <textarea name="delivery_address" class="form-control @error('delivery_address') is-invalid @enderror" 
                                      rows="3" required placeholder="Enter delivery address...">{{ old('delivery_address') }}</textarea>
                            @error('delivery_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Delivered By</label>
                                    <input type="text" name="delivered_by" class="form-control" value="{{ old('delivered_by') }}" 
                                           placeholder="Driver/Delivery person name">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="Delivery notes...">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($invoice)
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        This delivery order is being created for Invoice: <strong>{{ $invoice->invoice_no }}</strong>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Items Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Delivery Items</h5>
                    @if(!$invoice)
                    <button type="button" class="btn btn-primary btn-sm" onclick="addItem()">
                        <i class="fas fa-plus me-2"></i>Add Item
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="itemsTable">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th width="80">Ordered</th>
                                    <th width="80">Delivering</th>
                                    <th width="80">Damaged</th>
                                    <th width="80">Replacement</th>
                                    <th width="120">Warranty Start</th>
                                    <th width="120">Warranty End</th>
                                    <th>Serial Numbers</th>
                                    @if(!$invoice)
                                    <th width="50">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                @if($invoice && $invoice->items)
                                    @foreach($invoice->items->where('delivery_status', '!=', 'delivered') as $index => $item)
                                    <tr>
                                        <td>
                                            <input type="hidden" name="items[{{ $index }}][invoice_item_id]" value="{{ $item->id }}">
                                            <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product->id }}">
                                            <strong>{{ $item->product->name }}</strong>
                                            @if($item->description)
                                                <br><small class="text-muted">{{ $item->description }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity" 
                                                   value="{{ $item->remaining_quantity }}" readonly>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][delivered_quantity]" class="form-control delivered-quantity" 
                                                   value="{{ $item->remaining_quantity }}" required min="0" max="{{ $item->remaining_quantity }}" step="1">
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][damaged_quantity]" class="form-control damaged-quantity" 
                                                   value="0" min="0" step="1">
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][replacement_quantity]" class="form-control replacement-quantity" 
                                                   value="0" min="0" step="1">
                                        </td>
                                        <td>
                                            <input type="date" name="items[{{ $index }}][warranty_start_date]" class="form-control warranty-start" 
                                                   value="{{ date('Y-m-d') }}">
                                        </td>
                                        <td>
                                            <input type="date" name="items[{{ $index }}][warranty_end_date]" class="form-control warranty-end">
                                        </td>
                                        <td>
                                            @if($item->product->has_serial_number)
                                            <textarea name="items[{{ $index }}][serial_numbers]" class="form-control" rows="2" 
                                                      placeholder="Enter serial numbers (one per line)"></textarea>
                                            @else
                                            <span class="text-muted">No serial tracking</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Actions -->
           
                <div class="text-start">
                    <a href="{{ route('sales.delivery-orders.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Delivery Order
                    </button>
                </div>
        
        </div>
    </div>
</form>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Customer change event
    $('#customer_id').on('change', function() {
        const customerId = $(this).val();
        if (customerId) {
            loadPendingInvoices(customerId);
        } else {
            $('#invoice_id').html('<option value="">Select Invoice (Optional)</option>');
        }
    });

    // Invoice change event
    $('#invoice_id').on('change', function() {
        const invoiceId = $(this).val();
        if (invoiceId) {
            loadInvoiceItems(invoiceId);
        } else {
            $('#itemsTableBody').empty();
        }
    });

    // Auto-calculate warranty end date
    $(document).on('change', '.warranty-start', function() {
        const startDate = new Date($(this).val());
        if (startDate) {
            // Add default warranty period (e.g., 1 year)
            const endDate = new Date(startDate);
            endDate.setFullYear(endDate.getFullYear() + 1);
            
            const endDateString = endDate.toISOString().split('T')[0];
            $(this).closest('tr').find('.warranty-end').val(endDateString);
        }
    });
    addItem();
});

function loadPendingInvoices(customerId) {
    fetch(`/sales/delivery-orders/get-pending-invoices?customer_id=${customerId}`)
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">Select Invoice (Optional)</option>';
            data.forEach(invoice => {
                options += `<option value="${invoice.id}">${invoice.invoice_no} - ₹${invoice.total_amount}</option>`;
            });
            $('#invoice_id').html(options);
        })
        .catch(error => {
            console.error('Error loading invoices:', error);
        });
}

function loadInvoiceItems(invoiceId) {
    // This would load items from the selected invoice
    // Implementation depends on your specific requirements
}

let itemIndex = 0;

function addItem() {
    const tbody = document.getElementById('itemsTableBody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <select name="items[${itemIndex}][product_id]" class="form-select" required>
                <option value="">Select Product</option>
                <!-- Load products dynamically -->
            </select>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity" 
                   required min="1" step="1" value="1">
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][delivered_quantity]" class="form-control delivered-quantity" 
                   required min="0" step="1" value="1">
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][damaged_quantity]" class="form-control damaged-quantity" 
                   value="0" min="0" step="1">
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][replacement_quantity]" class="form-control replacement-quantity" 
                   value="0" min="0" step="1">
        </td>
        <td>
            <input type="date" name="items[${itemIndex}][warranty_start_date]" class="form-control warranty-start" 
                   value="${new Date().toISOString().split('T')[0]}">
        </td>
        <td>
            <input type="date" name="items[${itemIndex}][warranty_end_date]" class="form-control warranty-end">
        </td>
        <td>
            <textarea name="items[${itemIndex}][serial_numbers]" class="form-control" rows="2" 
                      placeholder="Enter serial numbers (one per line)"></textarea>
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
}
</script>
@endsection

