@extends('layouts.app')

@section('title', 'Vendor Management')

@section('content')
<div class="page-header">
    <h1 class="page-title">Vendor Management</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Vendors</li>
        </ol>
    </nav>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Search & Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('vendors.index') }}" id="filterForm">
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
        <h5 class="mb-0">All Vendors</h5>
        <div>
            @can('vendors.create')
            <a href="{{ route('vendors.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Add New Vendor
            </a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="120">Vendor Code</th>
                        <th>Company Name</th>
                        <th>Contact Person</th>
                        <th>Contact Info</th>
                        <th width="120">Credit Limit</th>
                        <th width="120">Payment Terms</th>
                        <th width="100">Status</th>
                        <th width="150">Balance</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendors as $vendor)
                    <tr>
                        <td>
                            <a href="{{ route('vendors.show', $vendor) }}" class="text-decoration-none">
                                {{ $vendor->vendor_code }}
                            </a>
                        </td>
                        <td>
                            <strong>{{ $vendor->company_name }}</strong>
                            @if($vendor->city)
                            <br><small class="text-muted">{{ $vendor->city }}, {{ $vendor->state }}</small>
                            @endif
                        </td>
                        <td>{{ $vendor->contact_person ?: '-' }}</td>
                        <td>
                            @if($vendor->email)
                            <i class="fas fa-envelope text-muted me-1"></i> {{ $vendor->email }}<br>
                            @endif
                            @if($vendor->mobile)
                            <i class="fas fa-mobile text-muted me-1"></i> {{ $vendor->mobile }}
                            @elseif($vendor->phone)
                            <i class="fas fa-phone text-muted me-1"></i> {{ $vendor->phone }}
                            @endif
                        </td>
                        <td>
                            @if($vendor->credit_limit > 0)
                                ₹{{ number_format($vendor->credit_limit, 2) }}
                            @else
                                <span class="text-muted">No limit</span>
                            @endif
                        </td>
                        <td>{{ $vendor->payment_terms }} days</td>
                        <td>
                            <span class="badge bg-{{ $vendor->status_badge }}">
                                {{ ucfirst($vendor->status) }}
                            </span>
                        </td>
                        <td>
                            @if($vendor->outstanding_balance > 0)
                                <span class="text-danger">₹{{ number_format($vendor->outstanding_balance, 2) }}</span>
                            @else
                                <span class="text-success">₹0.00</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                @can('vendors.view')
                                <a href="{{ route('vendors.show', $vendor) }}" class="btn btn-sm btn-outline-primary" 
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                
                                @can('vendors.edit')
                                <a href="{{ route('vendors.edit', $vendor) }}" class="btn btn-sm btn-outline-primary" 
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                
                                @can('vendors.delete')
                                @if($vendor->products->count() == 0)
                                <form action="{{ route('vendors.destroy', $vendor) }}" method="POST" 
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
                                <i class="fas fa-truck fa-3x mb-3"></i>
                                <p>No vendors found</p>
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
                Showing {{ $vendors->firstItem() ?? 0 }} to {{ $vendors->lastItem() ?? 0 }} 
                of {{ $vendors->total() }} entries
            </div>
            
            {{ $vendors->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<!-- Vendor Statistics -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-truck fa-2x text-primary mb-2"></i>
                <h5 class="card-title">Total Vendors</h5>
                <h3>{{ $vendors->total() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h5 class="card-title">Active</h5>
                <h3>{{ \App\Models\Vendor::where('status', 'active')->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-pause-circle fa-2x text-warning mb-2"></i>
                <h5 class="card-title">Inactive</h5>
                <h3>{{ \App\Models\Vendor::where('status', 'inactive')->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-rupee-sign fa-2x text-info mb-2"></i>
                <h5 class="card-title">Total Outstanding</h5>
                <h3>₹0.00</h3>
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
        
        if (confirm('Are you sure you want to delete this vendor? This action cannot be undone.')) {
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