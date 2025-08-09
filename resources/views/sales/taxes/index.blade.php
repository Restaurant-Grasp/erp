
@extends('layouts.app')

@section('title', 'Tax Management')

@section('content')
<div class="page-header">
    <h1 class="page-title">Tax Management</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Sales Taxes</li>
        </ol>
    </nav>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Search & Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('sales.taxes.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Search by name or rate..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="applicable_for">
                        <option value="">All Types</option>
                        <option value="product" {{ request('applicable_for') == 'product' ? 'selected' : '' }}>Product</option>
                        <option value="service" {{ request('applicable_for') == 'service' ? 'selected' : '' }}>Service</option>
                        <option value="both" {{ request('applicable_for') == 'both' ? 'selected' : '' }}>Both</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Main Content -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Taxes</h5>
        <div>
            @can('sales.taxes.create')
            <a href="{{ route('sales.taxes.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Add New Tax
            </a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tax Name</th>
                        <th>Rate (%)</th>
                        <th>Applicable For</th>
                        <th>Ledger Account</th>
                        <th width="100">Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taxes as $tax)
                    <tr>
                        <td>
                            <strong>{{ $tax->name }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $tax->percent }}%</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                {{ ucfirst(str_replace('_', ' ', $tax->applicable_for)) }}
                            </span>
                        </td>
                        <td>{{ $tax->ledger->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-{{ $tax->status ? 'success' : 'danger' }}">
                                {{ $tax->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                @can('sales.taxes.view')
                                <a href="{{ route('sales.taxes.show', $tax) }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                
                                @can('sales.taxes.edit')
                                <a href="{{ route('sales.taxes.edit', $tax) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                
                                @can('sales.taxes.delete')
                                <form action="{{ route('sales.taxes.destroy', $tax) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-percentage fa-3x mb-3"></i>
                                <p>No taxes found</p>
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
                Showing {{ $taxes->firstItem() ?? 0 }} to {{ $taxes->lastItem() ?? 0 }} 
                of {{ $taxes->total() }} entries
            </div>
            
            {{ $taxes->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<!-- Tax Statistics -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-percentage fa-2x text-primary mb-2"></i>
                <h5 class="card-title">Total Taxes</h5>
                <h3>{{ $taxes->total() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h5 class="card-title">Active</h5>
                <h3>{{ \App\Models\Tax::where('status', 1)->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-box fa-2x text-info mb-2"></i>
                <h5 class="card-title">Product Taxes</h5>
                <h3>{{ \App\Models\Tax::whereIn('applicable_for', ['product', 'both'])->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-cog fa-2x text-warning mb-2"></i>
                <h5 class="card-title">Service Taxes</h5>
                <h3>{{ \App\Models\Tax::whereIn('applicable_for', ['service', 'both'])->count() }}</h3>
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
        
        if (confirm('Are you sure you want to delete this tax? This action cannot be undone.')) {
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

