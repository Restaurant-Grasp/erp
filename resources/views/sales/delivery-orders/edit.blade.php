@extends('layouts.app')

@section('title', 'Edit Delivery Order')

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Delivery Order</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sales.delivery-orders.index') }}">Delivery Orders</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sales.delivery-orders.show', $deliveryOrder) }}">{{ $deliveryOrder->do_no }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form action="{{ route('sales.delivery-orders.update', $deliveryOrder) }}" method="POST" id="deliveryOrderForm">
    @csrf
    @method('PUT')
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
                            <label class="form-label">DO Number</label>
                            <input type="text" class="form-control" value="{{ $deliveryOrder->do_no }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">DO Date <span class="text-danger">*</span></label>
                            <input type="date" name="do_date" class="form-control @error('do_date') is-invalid @enderror" 
                                   value="{{ old('do_date', $deliveryOrder->do_date->format('Y-m-d')) }}" required>
                            @error('do_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $deliveryOrder->customer_id) == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->company_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Delivery Date</label>
                            <input type="date" name="delivery_date" class="form-control" 
                                   value="{{ old('delivery_date', $deliveryOrder->delivery_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Delivery Address <span class="text-danger">*</span></label>
                            <textarea name="delivery_address" class="form-control @error('delivery_address') is-invalid @enderror" 
                                      rows="3" required>{{ old('delivery_address', $deliveryOrder->delivery_address) }}</textarea>
                            @error('delivery_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Delivered By</label>
                                    <input type="text" name="delivered_by" class="form-control" 
                                           value="{{ old('delivered_by', $deliveryOrder->delivered_by) }}">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Received By</label>
                                    <input type="text" name="received_by" class="form-control" 
                                           value="{{ old('received_by', $deliveryOrder->received_by) }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $deliveryOrder->notes) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Delivery Items</h5>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addItem()">
                        <i class="fas fa-plus me-2"></i>Add Item
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="itemsTable">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th width="80">Quantity</th>
                                    <th width="80">Delivered</th>
                                    <th width="80">Damaged</th>
                                    <th width="80">Replacement</th>
                                    <th width="120">Warranty Start</th>
                                    <th width="120">Warranty End</th>
                                    <th>Notes</th>
                                    <th width="50">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                @foreach($deliveryOrder->items as $index => $item)
                                <tr>
                                    <td>
                                        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                        <strong>{{ $item->product->name }}</strong>
                                        <br><small class="text-muted">{{ $item->product->product_code }}</small>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity" 
                                               value="{{ $item->quantity }}" required min="0.01" step="0.01">
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][delivered_quantity]" class="form-control delivered-quantity" 
                                               value="{{ $item->delivered_quantity }}" required min="0" step="0.01">
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][damaged_quantity]" class="form-control damaged-quantity" 
                                               value="{{ $item->damaged_quantity }}" min="0" step="0.01">
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][replacement_quantity]" class="form-control replacement-quantity" 
                                               value="{{ $item->replacement_quantity }}" min="0" step="0.01">
                                    </td>
                                    <td>
                                        <input type="date" name="items[{{ $index }}][warranty_start_date]" class="form-control warranty-start" 
                                               value="{{ $item->warranty_start_date?->format('Y-m-d') }}">
                                    </td>
                                    <td>
                                        <input type="date" name="items[{{ $index }}][warranty_end_date]" class="form-control warranty-end" 
                                               value="{{ $item->warranty_end_date?->format('Y-m-d') }}">
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][notes]" class="form-control" 
                                               value="{{ $item->notes }}">
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

            <!-- Serial Numbers Section (if any items have serial tracking) -->
            @php
            $hasSerialItems = $deliveryOrder->items->some(function($item) {
                return $item->product->has_serial_number;
            });
            @endphp

            @if($hasSerialItems)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Serial Numbers</h5>
                </div>
                <div class="card-body">
                    @foreach($deliveryOrder->items->where('product.has_serial_number', true) as $item)
                    <div class="mb-4">
                        <h6>{{ $item->product->name }}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Serial Number</th>
                                        <th>Warranty Start</th>
                                        <th>Warranty End</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item->serialNumbers as $serial)
                                    <tr>
                                        <td><code>{{ $serial->serialNumber->serial_number }}</code></td>
                                        <td>{{ $serial->warranty_start_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td>{{ $serial->warranty_end_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $serial->status_badge }}">
                                                {{ ucfirst($serial->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $serial->notes ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="card">
                <div class="card-body text-end">
                    <a href="{{ route('sales.delivery-orders.show', $deliveryOrder) }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Back to View
                    </a>
                    <a href="{{ route('sales.delivery-orders.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-list me-2"></i>Back to List
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Delivery Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
let itemIndex = {{ $deliveryOrder->items->count() }};

function addItem() {
    const tbody = document.getElementById('itemsTableBody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <select name="items[${itemIndex}][product_id]" class="form-select" required>
                <option value="">Select Product</option>
                @foreach(\App\Models\Product::where('is_active', 1)->get() as $product)
                <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity" 
                   required min="0.01" step="0.01" value="1">
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][delivered_quantity]" class="form-control delivered-quantity" 
                   required min="0" step="0.01" value="1">
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][damaged_quantity]" class="form-control damaged-quantity" 
                   value="0" min="0" step="0.01">
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][replacement_quantity]" class="form-control replacement-quantity" 
                   value="0" min="0" step="0.01">
        </td>
        <td>
            <input type="date" name="items[${itemIndex}][warranty_start_date]" class="form-control warranty-start" 
                   value="${new Date().toISOString().split('T')[0]}">
        </td>
        <td>
            <input type="date" name="items[${itemIndex}][warranty_end_date]" class="form-control warranty-end">
        </td>
        <td>
            <input type="text" name="items[${itemIndex}][notes]" class="form-control">
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

$(document).ready(function() {
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

    // Validate quantities
    $(document).on('change', '.delivered-quantity, .damaged-quantity, .replacement-quantity', function() {
        const row = $(this).closest('tr');
        const quantity = parseFloat(row.find('.quantity').val()) || 0;
        const delivered = parseFloat(row.find('.delivered-quantity').val()) || 0;
        const damaged = parseFloat(row.find('.damaged-quantity').val()) || 0;
        const replacement = parseFloat(row.find('.replacement-quantity').val()) || 0;

        if (delivered + damaged > quantity) {
            alert('Delivered + Damaged quantities cannot exceed total quantity.');
            $(this).val(0);
        }
    });
});
</script>
@endsection