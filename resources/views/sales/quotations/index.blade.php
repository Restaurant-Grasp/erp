
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
        <h5 class="mb-0">All Quotations</h5>
        <div>
            @can('sales.quotations.create')
            <a href="{{ route('sales.quotations.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Create Quotation
            </a>
            @endcan
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
                            <a href="{{ route('sales.quotations.show', $quotation) }}" class="text-decoration-none">
                                {{ $quotation->quotation_no }}
                            </a>
                            @if($quotation->is_revised)
                                <span class="badge bg-info ms-1">Revised</span>
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
                                <br><small class="text-danger">Expired</small>
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
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                @can('sales.quotations.view')
                                <a href="{{ route('sales.quotations.show', $quotation) }}" 
                                   class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan

                                @if($quotation->can_be_edited)
                                    @can('sales.quotations.edit')
                                    <a href="{{ route('sales.quotations.edit', $quotation) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                @endif

                                @if($quotation->can_be_approved)
                                    @can('sales.quotations.approve')
                                    <form action="{{ route('sales.quotations.approve', $quotation) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success approve-btn" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    @endcan
                                @endif

                                @if($quotation->can_be_deleted)
                                    @can('sales.quotations.delete')
                                    <form action="{{ route('sales.quotations.destroy', $quotation) }}" method="POST" class="d-inline delete-form">
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
                                <i class="fas fa-file-invoice fa-3x mb-3"></i>
                                <p>No quotations found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        {{ $quotations->withQueryString()->links() }}
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
