@extends('layouts.app')

@section('title', 'Delivery Order Details')

@section('content')
<div class="page-header">
    <h1 class="page-title">Delivery Order Details</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sales.delivery-orders.index') }}">Delivery Orders</a></li>
            <li class="breadcrumb-item active">{{ $deliveryOrder->do_no }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Delivery Order Details -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">DO: {{ $deliveryOrder->do_no }}</h5>
                <span class="badge bg-{{ $deliveryOrder->status_badge }} fs-6">
                    {{ ucfirst(str_replace('_', ' ', $deliveryOrder->status)) }}
                </span>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6>Customer Information</h6>
                        <address>
                            <strong>{{ $deliveryOrder->customer->company_name }}</strong><br>
                            @if($deliveryOrder->customer->contact_person)
                                {{ $deliveryOrder->customer->contact_person }}<br>
                            @endif
                            @if($deliveryOrder->customer->email)
                                <strong>Email:</strong> {{ $deliveryOrder->customer->email }}<br>
                            @endif
                            @if($deliveryOrder->customer->phone)
                                <strong>Phone:</strong> {{ $deliveryOrder->customer->phone }}
                            @endif
                        </address>
                    </div>
                    <div class="col-md-6">
                        <h6>Delivery Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>DO Date:</strong></td>
                                <td>{{ $deliveryOrder->do_date->format('d/m/Y') }}</td>
                            </tr>
                            @if($deliveryOrder->delivery_date)
                            <tr>
                                <td><strong>Delivery Date:</strong></td>
                                <td>{{ $deliveryOrder->delivery_date->format('d/m/Y') }}</td>
                            </tr>
                            @endif
                            @if($deliveryOrder->invoice)
                            <tr>
                                <td><strong>Invoice:</strong></td>
                                <td>
                                    <a href="{{ route('sales.invoices.show', $deliveryOrder->invoice) }}">
                                        {{ $deliveryOrder->invoice->invoice_no }}
                                    </a>
                                </td>
                            </tr>
                            @endif
                            @if($deliveryOrder->delivered_by)
                            <tr>
                                <td><strong>Delivered By:</strong></td>
                                <td>{{ $deliveryOrder->delivered_by }}</td>
                            </tr>
                            @endif
                            @if($deliveryOrder->received_by)
                            <tr>
                                <td><strong>Received By:</strong></td>
                                <td>{{ $deliveryOrder->received_by }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- Delivery Address -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h6>Delivery Address</h6>
                        <address class="border p-3 rounded bg-light">
                            {{ $deliveryOrder->delivery_address }}
                        </address>
                    </div>
                </div>

                <!-- Items -->
                <div class="mt-4">
                    <h6>Delivery Items</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th width="80">Quantity</th>
                                    <th width="80">Delivered</th>
                                    <th width="80">Damaged</th>
                                    <th width="80">Replacement</th>
                                    <th width="120">Warranty Period</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deliveryOrder->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product->name }}</strong>
                                        @if($item->notes)
                                            <br><small class="text-muted">{{ $item->notes }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->delivered_quantity }}</td>
                                    <td>{{ $item->damaged_quantity }}</td>
                                    <td>{{ $item->replacement_quantity }}</td>
                                    <td>
                                        @if($item->warranty_start_date && $item->warranty_end_date)
                                            {{ $item->warranty_start_date->format('d/m/Y') }} - 
                                            {{ $item->warranty_end_date->format('d/m/Y') }}
                                        @else
                                            <span class="text-muted">No warranty</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $item->delivery_status_badge }}">
                                            {{ ucfirst(str_replace('_', ' ', $item->delivery_status)) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Serial Numbers -->
                @php
                $hasSerialNumbers = $deliveryOrder->items->some(function($item) {
                    return $item->serialNumbers->count() > 0;
                });
                @endphp

                @if($hasSerialNumbers)
                <div class="mt-4">
                    <h6>Serial Numbers</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Serial Number</th>
                                    <th>Warranty Period</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deliveryOrder->items as $item)
                                    @foreach($item->serialNumbers as $serial)
                                    <tr>
                                        <td>{{ $item->product->name }}</td>
                                        <td><code>{{ $serial->serialNumber->serial_number }}</code></td>
                                        <td>
                                            @if($serial->warranty_start_date && $serial->warranty_end_date)
                                                {{ $serial->warranty_start_date->format('d/m/Y') }} - 
                                                {{ $serial->warranty_end_date->format('d/m/Y') }}
                                            @else
                                                <span class="text-muted">No warranty</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $serial->status_badge }}">
                                                {{ ucfirst($serial->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                @if($deliveryOrder->notes)
                <div class="mt-4">
                    <h6>Notes</h6>
                    <p>{{ $deliveryOrder->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($deliveryOrder->status !== 'delivered')
                        @can('sales.delivery_orders.edit')
                        <a href="{{ route('sales.delivery-orders.edit', $deliveryOrder) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i> Edit Delivery Order
                        </a>
                        @endcan

                        <form action="{{ route('sales.delivery-orders.mark-delivered', $deliveryOrder) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-success w-100">
                                <i class="fas fa-check me-2"></i> Mark as Delivered
                            </button>
                        </form>
                    @endif

                    @can('sales.delivery_orders.pdf')
                    <a href="{{ route('sales.delivery-orders.pdf', $deliveryOrder) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-file-pdf me-2"></i> Download PDF
                    </a>
                    @endcan

                    @if($deliveryOrder->status !== 'delivered')
                        @can('sales.delivery_orders.delete')
                        <form action="{{ route('sales.delivery-orders.destroy', $deliveryOrder) }}" method="POST" class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-trash me-2"></i> Delete Delivery Order
                            </button>
                        </form>
                        @endcan
                    @endif

                    <a href="{{ route('sales.delivery-orders.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Delivery Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Total Items:</strong></td>
                        <td class="text-end">{{ $deliveryOrder->total_items }}</td>
                    </tr>
                    <tr>
                        <td><strong>Delivered:</strong></td>
                        <td class="text-end text-success">{{ $deliveryOrder->total_delivered }}</td>
                    </tr>
                    @if($deliveryOrder->total_damaged > 0)
                    <tr>
                        <td><strong>Damaged:</strong></td>
                        <td class="text-end text-danger">{{ $deliveryOrder->total_damaged }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Audit Information -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Audit Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Created:</strong></td>
                        <td>{{ $deliveryOrder->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @if($deliveryOrder->createdBy)
                    <tr>
                        <td><strong>Created by:</strong></td>
                        <td>{{ $deliveryOrder->createdBy->name }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Last Updated:</strong></td>
                        <td>{{ $deliveryOrder->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Delete confirmation
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        
        if (confirm('Are you sure you want to delete this delivery order? This action cannot be undone.')) {
            form.submit();
        }
    });
});
</script>
@endsection