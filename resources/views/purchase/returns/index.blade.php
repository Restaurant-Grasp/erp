@extends('layouts.app')

@section('title', 'Purchase Returns')

@section('content')
<div class="page-header">
    <h1 class="page-title">Purchase Returns</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Purchase Returns</li>
        </ol>
    </nav>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Search & Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('purchase.returns.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Search return no, vendor, GRN..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                        <option value="credited" {{ request('status') == 'credited' ? 'selected' : '' }}>Credited</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="return_type">
                        <option value="">All Types</option>
                        <option value="damaged" {{ request('return_type') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                        <option value="defective" {{ request('return_type') == 'defective' ? 'selected' : '' }}>Defective</option>
                        <option value="wrong_item" {{ request('return_type') == 'wrong_item' ? 'selected' : '' }}>Wrong Item</option>
                        <option value="excess" {{ request('return_type') == 'excess' ? 'selected' : '' }}>Excess</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="vendor">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ request('vendor') == $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->company_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_from" placeholder="From Date" 
                           value="{{ request('date_from') }}">
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
        <h5 class="mb-0">All Purchase Returns</h5>
        <div>
            @can('purchases.returns.create')
            <a href="{{ route('purchase.returns.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Create Return
            </a>
            @endcan
            <a href="{{ route('purchase.returns.replacement-report') }}" class="btn btn-outline-info">
                <i class="fas fa-exchange-alt me-2"></i> Replacement Report
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="120">Return No</th>
                        <th>Vendor</th>
                        <th width="100">Return Date</th>
                        <th width="100">Return Type</th>
                        <th width="100">Total Items</th>
                        <th width="120">Total Amount</th>
                        <th width="100">Status</th>
                        <th width="100">GRN</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                    <tr>
                        <td>
                            <a href="{{ route('purchase.returns.show', $return) }}" class="text-decoration-none">
                                {{ $return->return_no }}
                            </a>
                        </td>
                        <td>
                            <strong>{{ $return->vendor->company_name }}</strong>
                            <br><small class="text-muted">{{ $return->vendor->vendor_code }}</small>
                        </td>
                        <td>{{ $return->return_date->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge bg-{{ $return->return_type_badge }}">
                                {{ ucwords(str_replace('_', ' ', $return->return_type)) }}
                            </span>
                        </td>
                        <td>
                            {{ $return->total_items }} items
                            <br><small class="text-muted">{{ number_format($return->total_quantity, 2) }} qty</small>
                        </td>
                        <td>
                            @if($return->total_amount > 0)
                                ₹{{ number_format($return->total_amount, 2) }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $return->status_badge }}">
                                {{ ucfirst($return->status) }}
                            </span>
                        </td>
                        <td>
                            @if($return->grn)
                                <a href="{{ route('purchase.grn.show', $return->grn) }}" class="text-decoration-none">
                                    {{ $return->grn->grn_no }}
                                </a>
                            @else
                                <span class="text-muted">Direct</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                @can('purchases.returns.view')
                                <a href="{{ route('purchase.returns.show', $return) }}" class="btn btn-sm btn-outline-primary" 
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                
                                @if($return->status === 'pending')
                                <button type="button" class="btn btn-sm btn-outline-success" 
                                        onclick="approveReturn({{ $return->id }})" title="Approve">
                                    <i class="fas fa-check"></i>
                                </button>
                                @endif
                                
                                @if($return->status === 'approved')
                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                        onclick="markReturned({{ $return->id }})" title="Mark as Returned">
                                    <i class="fas fa-truck"></i>
                                </button>
                                @endif
                                
                                @if($return->status === 'returned')
                                <button type="button" class="btn btn-sm btn-outline-info" 
                                        onclick="markCredited({{ $return->id }})" title="Mark as Credited">
                                    <i class="fas fa-dollar-sign"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-undo fa-3x mb-3"></i>
                                <p>No purchase returns found</p>
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
                Showing {{ $returns->firstItem() ?? 0 }} to {{ $returns->lastItem() ?? 0 }} 
                of {{ $returns->total() }} entries
            </div>
            
            {{ $returns->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<!-- Purchase Return Statistics -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-undo fa-2x text-primary mb-2"></i>
                <h5 class="card-title">Total Returns</h5>
                <h3>{{ $returns->total() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                <h5 class="card-title">Pending</h5>
                <h3>{{ \App\Models\PurchaseReturn::where('status', 'pending')->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-truck fa-2x text-info mb-2"></i>
                <h5 class="card-title">Returned</h5>
                <h3>{{ \App\Models\PurchaseReturn::where('status', 'returned')->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h5 class="card-title">Credited</h5>
                <h3>{{ \App\Models\PurchaseReturn::where('status', 'credited')->count() }}</h3>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Auto-submit filter form on change
    $('#filterForm select').on('change', function() {
        $('#filterForm').submit();
    });
});

function approveReturn(returnId) {
    if (confirm('Are you sure you want to approve this return?')) {
        $.ajax({
            url: `/purchase/returns/${returnId}/approve`,
            method: 'PATCH',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error approving return: ' + xhr.responseJSON.message);
            }
        });
    }
}

function markReturned(returnId) {
    if (confirm('Confirm that items have been returned to vendor?')) {
        $.ajax({
            url: `/purchase/returns/${returnId}/mark-returned`,
            method: 'PATCH',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error updating return status: ' + xhr.responseJSON.message);
            }
        });
    }
}

function markCredited(returnId) {
    if (confirm('Confirm that credit has been received from vendor?')) {
        $.ajax({
            url: `/purchase/returns/${returnId}/mark-credited`,
            method: 'PATCH',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error updating return status: ' + xhr.responseJSON.message);
            }
        });
    }
}
</script>
@endsection