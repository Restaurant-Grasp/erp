{{-- resources/views/sales/quotations/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Quotation Management')

@section('content')
<div class="page-header">
    <h1 class="page-title">Quotation Management</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Quotations</li>
        </ol>
    </nav>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-file-invoice fa-2x text-primary mb-2"></i>
                <h5 class="card-title">Total Quotations</h5>
                <h3>{{ \App\Models\Quotation::count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                <h5 class="card-title">Pending Approval</h5>
                <h3>{{ \App\Models\Quotation::where('approval_status', 'pending')->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h5 class="card-title">Approved</h5>
                <h3>{{ \App\Models\Quotation::where('approval_status', 'approved')->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-rupee-sign fa-2x text-info mb-2"></i>
                <h5 class="card-title">Total Value</h5>
                <h3>₹{{ number_format(\App\Models\Quotation::sum('total_amount'), 2) }}</h3>
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
        <form method="GET" action="{{ route('sales.quotations.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Search quotations..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="converted" {{ request('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="approval_status">
                        <option value="">All Approvals</option>
                        <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('approval_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}" placeholder="From Date">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}" placeholder="To Date">
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
        <h5 class="mb-0">All Quotations</h5>
        <div>
                @php
                $role = auth()->user()->getRoleNames()->first();
                $permissions = getCurrentRolePermissions($role);
                @endphp

           @if ($permissions->contains('name', 'sales.quotations.create'))
         
            <a href="{{ route('sales.quotations.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Create Quotation
            </a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Quotation No</th>
                        <th>Date</th>
                        <th>Customer/Lead</th>
                        <th>Valid Until</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Approval</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotations as $quotation)
                    <tr class="{{ $quotation->is_expired ? 'table-warning' : '' }}">
                        <td>
                            <a href="{{ route('sales.quotations.show', $quotation) }}" class="text-decoration-none fw-bold">
                                {{ $quotation->quotation_no }}
                            </a>
                            @if($quotation->is_revised)
                                <span class="badge bg-info ms-1">Revised</span>
                            @endif
                            @if($quotation->parent_quotation_id)
                                <small class="text-muted d-block">Revision of {{ $quotation->parentQuotation->quotation_no ?? 'N/A' }}</small>
                            @endif
                        </td>
                        <td>{{ $quotation->quotation_date->format('d/m/Y') }}</td>
                        <td>
                            @if($quotation->customer)
                                <strong>{{ $quotation->customer->company_name }}</strong>
                                <br><small class="text-muted">Customer</small>
                            @elseif($quotation->lead)
                                <strong>{{ $quotation->lead->company_name ?: $quotation->lead->contact_person }}</strong>
                                <br><small class="text-muted">Lead</small>
                            @endif
                        </td>
                        <td>
                            {{ $quotation->valid_until->format('d/m/Y') }}
                            @if($quotation->is_expired)
                                <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Expired</small>
                            @endif
                        </td>
                        <td>₹{{ number_format($quotation->total_amount, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ $quotation->status_badge }}">
                                {{ ucfirst($quotation->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $quotation->approval_status_badge }}">
                                {{ ucfirst(str_replace('_', ' ', $quotation->approval_status)) }}
                            </span>
                            @if($quotation->approval_status === 'approved' && $quotation->approvedBy)
                                <br><small class="text-muted">by {{ $quotation->approvedBy->name }}</small>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                     @if ($permissions->contains('name', 'sales.quotations.view'))
                            
                                <a href="{{ route('sales.quotations.show', $quotation) }}" 
                                   class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endif

                                @if($quotation->can_be_edited)
                                      @if ($permissions->contains('name', 'sales.quotations.edit'))
                                    <a href="{{ route('sales.quotations.edit', $quotation) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                @endif

                                @if($quotation->can_be_approved)
                                
                                         @if ($permissions->contains('name', 'sales.quotations.approve'))
                       
                                    <form action="{{ route('sales.quotations.approve', $quotation) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success approve-btn" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    @endif
                                @endif

                                <!-- Dropdown for more actions -->
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                    
                                         @if ($permissions->contains('name', 'sales.quotations.create'))

                                        <li>
                                            <form action="{{ route('sales.quotations.duplicate', $quotation) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fas fa-copy me-2"></i>Duplicate
                                                </button>
                                            </form>
                                        </li>
                                        @endif
                                        
                                        @if($quotation->can_be_edited)
                                        <li>
                                            <form action="{{ route('sales.quotations.revision', $quotation) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fas fa-code-branch me-2"></i>Create Revision
                                                </button>
                                            </form>
                                        </li>
                                        @endif

                                        @if($quotation->status === 'draft')
                                        <li>
                                            <form action="{{ route('sales.quotations.send', $quotation) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fas fa-paper-plane me-2"></i>Send
                                                </button>
                                            </form>
                                        </li>
                                        @endif

                                  
                                <li>
                                    <a href="{{ route('sales.quotations.pdf', $quotation) }}" class="dropdown-item">
                                        <i class="fas fa-file-pdf me-2"></i>Download PDF
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('sales.quotations.print', $quotation) }}" class="dropdown-item" target="_blank">
                                        <i class="fas fa-print me-2"></i>Print Quotation
                                    </a>
                                </li>
                                        @if($quotation->can_be_deleted)
                                         @if ($permissions->contains('name', 'sales.quotations.delete'))

                                      
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('sales.quotations.destroy', $quotation) }}" method="POST" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash me-2"></i>Delete
                                                    </button>
                                                </form>
                                            </li>
                                            @endif
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-file-invoice fa-3x mb-3"></i>
                                <p>No quotations found</p>
                                         @if ($permissions->contains('name', 'sales.quotations.create'))

                                <a href="{{ route('sales.quotations.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Create First Quotation
                                </a>
                                @endif
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
                Showing {{ $quotations->firstItem() ?? 0 }} to {{ $quotations->lastItem() ?? 0 }} 
                of {{ $quotations->total() }} entries
            </div>
            
            {{ $quotations->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Delete confirmation
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        
        if (confirm('Are you sure you want to delete this quotation? This action cannot be undone.')) {
            form.submit();
        }
    });

    // Approve confirmation
    $('.approve-btn').on('click', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        
        if (confirm('Are you sure you want to approve this quotation? It will be automatically converted to an invoice.')) {
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