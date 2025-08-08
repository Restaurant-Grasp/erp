@extends('layouts.app')

@section('title', 'Customer Management')

@section('content')
<div class="page-header">
    <h1 class="page-title">Customer Management</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Customers</li>
        </ol>
    </nav>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Search & Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('customers.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Search by code, name, email..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="category">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_from" placeholder="From Date" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_to" placeholder="To Date" 
                           value="{{ request('date_to') }}">
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
        <h5 class="mb-0">All Customers</h5>
        <div>
            @can('customers.create')
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Add New Customer
            </a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="120">Customer Code</th>
                        <th>Company Name</th>
                        <th>Contact Person</th>
                        <th>Contact Info</th>
                        <th width="120">Credit Limit</th>
                        <th width="100">Status</th>
                        <th width="120">Assigned To</th>
                        <th width="150">Balance</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td>
                            <a href="{{ route('customers.show', $customer) }}" class="text-decoration-none">
                                {{ $customer->customer_code }}
                            </a>
                        </td>
                        <td>
                            <strong>{{ $customer->company_name }}</strong>
                            @if($customer->city)
                            <br><small class="text-muted">{{ $customer->city }}, {{ $customer->state }}</small>
                            @endif
                        </td>
                        <td>{{ $customer->contact_person ?: '-' }}</td>
                        <td>
                            @if($customer->email)
                            <i class="fas fa-envelope text-muted me-1"></i> {{ $customer->email }}<br>
                            @endif
                            @if($customer->mobile)
                            <i class="fas fa-mobile text-muted me-1"></i> {{ $customer->mobile }}
                            @elseif($customer->phone)
                            <i class="fas fa-phone text-muted me-1"></i> {{ $customer->phone }}
                            @endif
                        </td>
                        <td>
                            @if($customer->credit_limit > 0)
                                ₹{{ number_format($customer->credit_limit, 2) }}
                            @else
                                <span class="text-muted">No limit</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $customer->status_badge }}">
                                {{ ucfirst($customer->status) }}
                            </span>
                        </td>
                        <td>
                            {{ $customer->assignedTo->name ?? '-' }}
                        </td>
                        <td>
                            @if($customer->outstanding_balance > 0)
                                <span class="text-danger">₹{{ number_format($customer->outstanding_balance, 2) }}</span>
                            @else
                                <span class="text-success">₹0.00</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                @can('customers.view')
                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-outline-primary" 
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                
                                @can('customers.edit')
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-outline-primary" 
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                
                                @can('customers.statement')
                                <a href="{{ route('customers.statement', $customer) }}" class="btn btn-sm btn-outline-info" 
                                   title="Statement">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                                @endcan
                                
                                @can('customers.delete')
                                @if($customer->invoices->count() == 0 && $customer->quotations->count() == 0)
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" 
                                      class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <p>No customers found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div>
                Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} 
                of {{ $customers->total() }} entries
            </div>
            
            {{ $customers->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<!-- Customer Statistics -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                <h5 class="card-title">Total Customers</h5>
                <h3>{{ $customers->total() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h5 class="card-title">Active</h5>
                <h3>{{ \App\Models\Customer::where('status', 'active')->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-exchange-alt fa-2x text-info mb-2"></i>
                <h5 class="card-title">Converted from Leads</h5>
                <h3>{{ \App\Models\Customer::whereNotNull('lead_id')->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-rupee-sign fa-2x text-warning mb-2"></i>
                <h5 class="card-title">Total Outstanding</h5>
                <h3>₹{{ number_format(\App\Models\Customer::join('sales_invoices', 'customers.id', '=', 'sales_invoices.customer_id')
                    ->whereIn('sales_invoices.status', ['pending', 'partial'])
                    ->sum('sales_invoices.balance_amount'), 2) }}</h3>
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
        
        if (confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
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