@extends('layouts.app')

@section('title', 'Delivery Orders')

@section('content')
<div class="page-header">
    <h1 class="page-title">Delivery Orders</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Delivery Orders</li>
        </ol>
    </nav>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Search & Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('sales.delivery-orders.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Search DO No, Customer..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Main Content -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Delivery Orders</h5>
        <div>
            @can('sales.delivery_orders.create')
            <a href="{{ route('sales.delivery-orders.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Create Delivery Order
            </a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>DO No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Invoice</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Delivery Date</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveryOrders as $deliveryOrder)
                    <tr>
                        <td>
                            <a href="{{ route('sales.delivery-orders.show', $deliveryOrder) }}" class="text-decoration-none">
                                {{ $deliveryOrder->do_no }}
                            </a>
                        </td>
                        <td>{{ $deliveryOrder->do_date->format('d/m/Y') }}</td>
                        <td>
                            <strong>{{ $deliveryOrder->customer->company_name }}</strong>
                            @if($deliveryOrder->customer->city)
                            <br><small class="text-muted">{{ $deliveryOrder->customer->city }}</small>
                            @endif
                        </td>
                        <td>
                            @if($deliveryOrder->invoice)
                                <a href="{{ route('sales.invoices.show', $deliveryOrder->invoice) }}">
                                    {{ $deliveryOrder->invoice->invoice_no }}
                                </a>
                            @else
                                <span class="text-muted">Direct DO</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $deliveryOrder->items->count() }} items</span>
                            <br><small class="text-muted">{{ $deliveryOrder->total_items }} total qty</small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $deliveryOrder->status_badge }}">
                                {{ ucfirst(str_replace('_', ' ', $deliveryOrder->status)) }}
                            </span>
                        </td>
                        <td>
                            {{ $deliveryOrder->delivery_date ? $deliveryOrder->delivery_date->format('d/m/Y') : '-' }}
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                @can('sales.delivery_orders.view')
                                <a href="{{ route('sales.delivery-orders.show', $deliveryOrder) }}" 
                                   class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan

                                @if($deliveryOrder->status !== 'delivered')
                                    @can('sales.delivery_orders.edit')
                                    <a href="{{ route('sales.delivery-orders.edit', $deliveryOrder) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                @endif

                                @if($deliveryOrder->status === 'pending' || $deliveryOrder->status === 'in_transit')
                                <form action="{{ route('sales.delivery-orders.mark-delivered', $deliveryOrder) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success mark-delivered-btn" title="Mark as Delivered">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                @endif

                                @if($deliveryOrder->status !== 'delivered')
                                    @can('sales.delivery_orders.delete')
                                    <form action="{{ route('sales.delivery-orders.destroy', $deliveryOrder) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-truck fa-3x mb-3"></i>
                                <p>No delivery orders found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        {{ $deliveryOrders->withQueryString()->links() }}
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

    // Mark delivered confirmation
    $('.mark-delivered-btn').on('click', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        
        if (confirm('Are you sure you want to mark this delivery order as delivered?')) {
            form.submit();
        }
    });

    // Auto-submit filter form on change
    $('#filterForm select').on('change', function() {
        $('#filterForm').submit();
    });
});
</script>
@endsection