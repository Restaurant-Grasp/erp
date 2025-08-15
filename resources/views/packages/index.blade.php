@extends('layouts.app')

@section('title', 'Packages')

@section('content')
<div class="page-header">
    <h1 class="page-title">Package Management</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Packages</li>
        </ol>
    </nav>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-box fa-2x text-primary mb-2"></i>
                <h5 class="card-title">Total Packages</h5>
                <h3>{{ $packages->total() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h5 class="card-title">Active Packages</h5>
                <h3>{{ $packages->where('status', 1)->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-sync-alt fa-2x text-info mb-2"></i>
                <h5 class="card-title">Subscription Packages</h5>
                <h3>{{ $packages->where('is_subscription', 1)->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-percentage fa-2x text-warning mb-2"></i>
                <h5 class="card-title">Avg. Discount</h5>
                <h3>{{ number_format($packages->avg('discount_percentage'), 1) }}%</h3>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Search & Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('packages.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Search packages..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="subscription">
                        <option value="">All Types</option>
                        <option value="1" {{ request('subscription') == '1' ? 'selected' : '' }}>Subscription</option>
                        <option value="0" {{ request('subscription') == '0' ? 'selected' : '' }}>One-time</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="cycle">
                        <option value="">All Cycles</option>
                        <option value="monthly" {{ request('cycle') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarterly" {{ request('cycle') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                        <option value="yearly" {{ request('cycle') == 'yearly' ? 'selected' : '' }}>Yearly</option>
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
        <h5 class="mb-0">All Packages</h5>
        <div>
            <a href="{{ route('packages.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Create Package
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Package Info</th>
                        <th>Items</th>
                        <th>Pricing</th>
                        <th>Type</th>
                        <th>Validity</th>
                        <th>Status</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packages as $package)
                    <tr>
                        <td>
                            <div>
                                <strong>{{ $package->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $package->code }}</small>
                                @if($package->description)
                                <br>
                                <small class="text-muted">{{ Str::limit($package->description, 50) }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $package->total_items }} Items</span>
                            @if($package->packageItems->where('item_type', 'service')->count() > 0)
                            <br><small class="text-muted">{{ $package->packageItems->where('item_type', 'service')->count() }} Services</small>
                            @endif
                            @if($package->packageItems->where('item_type', 'product')->count() > 0)
                            <br><small class="text-muted">{{ $package->packageItems->where('item_type', 'product')->count() }} Products</small>
                            @endif
                        </td>
                        <td>
                            <div>
                                <strong>{{ $package->formatted_price }}</strong>
                                @if($package->discount_percentage > 0)
                                <br>
                                <small class="text-success">
                                    {{ $package->discount_percentage }}% off
                                    (Save {{ $package->formatted_discount }})
                                </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($package->is_subscription)
                            <span class="badge bg-primary">{{ $package->subscription_cycle_label }}</span>
                            @else
                            <span class="badge bg-secondary">One Time</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $package->validity_label }}</span>
                        </td>
                        <td>
                            <span class="{{ $package->status_badge_class }}">
                                {{ $package->status_label }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('packages.show', $package) }}"
                                    class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('packages.edit', $package) }}"
                                    class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                              
                                @if($package->canBeDeleted())
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="deletePackage({{ $package->id }})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-box fa-3x mb-3"></i>
                                <p>No packages found</p>
                                <a href="{{ route('packages.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Create Your First Package
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        {{ $packages->withQueryString()->links() }}
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deletePackageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Package</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this package? This action cannot be undone.</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> Deleting this package may affect existing quotations and invoices that reference it.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deletePackageForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Package</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Auto-submit filter form on change
    $('#filterForm select').on('change', function() {
        $('#filterForm').submit();
    });
});

function deletePackage(packageId) {
    const modal = new bootstrap.Modal(document.getElementById('deletePackageModal'));
    const form = document.getElementById('deletePackageForm');
    form.action = `/packages/${packageId}`;
    modal.show();
}

function duplicatePackage(packageId) {
    if (confirm('Do you want to create a copy of this package?')) {
        fetch(`/packages/${packageId}/duplicate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = `/packages/${data.package_id}/edit`;
            } else {
                alert('Error duplicating package: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while duplicating the package.');
        });
    }
}
</script>
@endsection