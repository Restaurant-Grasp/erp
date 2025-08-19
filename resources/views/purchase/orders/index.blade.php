@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content')
<div class="page-header">
    <h1 class="page-title">Purchase Orders</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Purchase Orders</li>
        </ol>
    </nav>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Search & Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('purchase.orders.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Search PO No, Reference, Vendor..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="approval_status">
                        <option value="">All Approval Status</option>
                        <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('approval_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
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
        <h5 class="mb-0">All Purchase Orders</h5>
        <div>
                        @php
            $role = auth()->user()->getRoleNames()->first();
            $permissions = getCurrentRolePermissions($role);
            @endphp
            @if ($permissions->contains('name', 'purchases.po.create'))
   
            <a href="{{ route('purchase.orders.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Create Purchase Order
            </a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="120">PO No</th>
                        <th>Vendor</th>
                        <th width="100">PO Date</th>
                        <th width="120">Total Amount</th>
                        <th width="100">Status</th>
                        <th width="120">Approval Status</th>
                        <th width="100">Received %</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $po)
                    <tr>
                        <td>
                            <a href="{{ route('purchase.orders.show', $po) }}" class="text-decoration-none">
                                {{ $po->po_no }}
                            </a>
                            @if($po->reference_no)
                            <br><small class="text-muted">Ref: {{ $po->reference_no }}</small>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $po->vendor->company_name }}</strong>
                            <br><small class="text-muted">{{ $po->vendor->vendor_code }}</small>
                        </td>
                        <td>{{ $po->po_date->format('d/m/Y') }}</td>
                        <td>
                            {{ $po->currency }} {{ number_format($po->total_amount, 2) }}
                            @if($po->delivery_date)
                            <br><small class="text-muted">Due: {{ $po->delivery_date->format('d/m/Y') }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $po->status_badge }}">
                                {{ ucwords(str_replace('_', ' ', $po->status)) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $po->approval_status_badge }}">
                                {{ ucfirst($po->approval_status) }}
                            </span>
                            @if($po->approved_by && $po->approval_status === 'approved')
                            <br><small class="text-muted">by {{ $po->approvedBy->name }}</small>
                            @endif
                        </td>
                        <td>
                            @if($po->received_percentage > 0)
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ $po->received_percentage }}%"
                                         aria-valuenow="{{ $po->received_percentage }}" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        {{ number_format($po->received_percentage, 1) }}%
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">0%</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                 @if ($permissions->contains('name', 'purchases.po.view'))
                             
                                <a href="{{ route('purchase.orders.show', $po) }}" class="btn btn-sm btn-outline-primary" 
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endif
                                  @if ($permissions->contains('name', 'purchases.po.edit'))
                          
                                @if($po->status === 'draft')
                                <a href="{{ route('purchase.orders.edit', $po) }}" class="btn btn-sm btn-outline-primary" 
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                @endif
                                     @if ($permissions->contains('name', 'purchases.po.approve'))
                            
                                @if($po->approval_status === 'pending')
                                <button type="button" class="btn btn-sm btn-outline-success" 
                                        onclick="showApprovalModal({{ $po->id }}, '{{ $po->po_no }}')" title="Approve">
                                    <i class="fas fa-check"></i>
                                </button>
                                @endif
                                @endif
                                      @if ($permissions->contains('name', 'purchases.po.delete'))
                      
                                @if($po->status === 'draft')
                                <form action="{{ route('purchase.orders.destroy', $po) }}" method="POST" 
                                      class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-file-alt fa-3x mb-3"></i>
                                <p>No purchase orders found</p>
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
                Showing {{ $purchaseOrders->firstItem() ?? 0 }} to {{ $purchaseOrders->lastItem() ?? 0 }} 
                of {{ $purchaseOrders->total() }} entries
            </div>
            
            {{ $purchaseOrders->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<!-- Purchase Order Statistics -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-file-alt fa-2x text-primary mb-2"></i>
                <h5 class="card-title">Total POs</h5>
                <h3>{{ $purchaseOrders->total() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                <h5 class="card-title">Pending Approval</h5>
                <h3>{{ \App\Models\PurchaseOrder::where('approval_status', 'pending')->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h5 class="card-title">Approved</h5>
                <h3>{{ \App\Models\PurchaseOrder::where('approval_status', 'approved')->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-truck fa-2x text-info mb-2"></i>
                <h5 class="card-title">Partially Received</h5>
                <h3>{{ \App\Models\PurchaseOrder::where('status', 'partial')->count() }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approvalForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <p>Are you sure you want to approve PO <strong id="poNumber"></strong>?</p>
                    <div class="mb-3">
                        <label class="form-label">Approval Notes (Optional)</label>
                        <textarea name="approval_notes" class="form-control" rows="3" 
                                  placeholder="Enter any notes for approval..."></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        After approval, this PO will be automatically converted to a purchase invoice.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i> Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    // Delete confirmation
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        
        if (confirm('Are you sure you want to delete this purchase order? This action cannot be undone.')) {
            form.submit();
        }
    });

    // Auto-submit filter form on change
    $('#filterForm select').on('change', function() {
        $('#filterForm').submit();
    });
});

function showApprovalModal(poId, poNumber) {
    $('#poNumber').text(poNumber);
    $('#approvalForm').attr('action', '/purchase/orders/' + poId + '/approve');
    $('#approvalModal').modal('show');
}
</script>
@endsection