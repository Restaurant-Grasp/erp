@extends('layouts.app')

@section('title', 'Sales Invoices')

@section('content')
<div class="page-header">
    <h1 class="page-title">Sales Invoices</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Sales Invoices</li>
        </ol>
    </nav>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-file-invoice-dollar fa-2x text-primary mb-2"></i>
                <h5 class="card-title">Total Invoices</h5>
                <h3>{{ $statistics['total_invoices'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-rupee-sign fa-2x text-success mb-2"></i>
                <h5 class="card-title">Total Revenue</h5>
                <h3>₹{{ number_format($statistics['total_amount'] ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                <h5 class="card-title">Outstanding</h5>
                <h3>₹{{ number_format($statistics['outstanding_amount'] ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                <h5 class="card-title">Overdue</h5>
                <h3>₹{{ number_format($statistics['overdue_amount'] ?? 0, 2) }}</h3>
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
        <form method="GET" action="{{ route('sales.invoices.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Search invoices..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="due_status">
                        <option value="">All Due Status</option>
                        <option value="overdue" {{ request('due_status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="due_today" {{ request('due_status') == 'due_today' ? 'selected' : '' }}>Due Today</option>
                        <option value="due_this_week" {{ request('due_status') == 'due_this_week' ? 'selected' : '' }}>Due This Week</option>
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
        <h5 class="mb-0">All Invoices</h5>
        <div>
            @can('sales.invoices.create')
            <a href="{{ route('sales.invoices.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Create Invoice
            </a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Delivery</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr class="{{ $invoice->is_overdue ? 'table-danger' : '' }}">
                        <td>
                            <a href="{{ route('sales.invoices.show', $invoice) }}" class="text-decoration-none">
                                {{ $invoice->invoice_no }}
                            </a>
                            @if($invoice->quotation_id)
                                <br><small class="text-muted">From Quotation</small>
                            @endif
                        </td>
                        <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                        <td>
                            <strong>{{ $invoice->customer->company_name }}</strong>
                            @if($invoice->customer->city)
                                <br><small class="text-muted">{{ $invoice->customer->city }}</small>
                            @endif
                        </td>
                        <td>
                            {{ $invoice->due_date->format('d/m/Y') }}
                            @if($invoice->is_overdue)
                                <br><small class="text-danger">
                                    {{ $invoice->due_date->diffForHumans() }}
                                </small>
                            @endif
                        </td>
                        <td>₹{{ number_format($invoice->total_amount, 2) }}</td>
                        <td>₹{{ number_format($invoice->paid_amount, 2) }}</td>
                        <td>
                            @if($invoice->balance_amount > 0)
                                <span class="text-danger">₹{{ number_format($invoice->balance_amount, 2) }}</span>
                            @else
                                <span class="text-success">₹0.00</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $invoice->status_badge }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $invoice->delivery_status_badge }}">
                                {{ ucfirst(str_replace('_', ' ', $invoice->delivery_status)) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                @can('sales.invoices.view')
                                <a href="{{ route('sales.invoices.show', $invoice) }}" 
                                   class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan

                                @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                                    @can('sales.invoices.edit')
                                    <a href="{{ route('sales.invoices.edit', $invoice) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                @endif

                                @if($invoice->delivery_status !== 'delivered')
                                    <a href="{{ route('sales.delivery-orders.create', ['invoice_id' => $invoice->id]) }}" 
                                       class="btn btn-sm btn-outline-info" title="Create Delivery Order">
                                        <i class="fas fa-truck"></i>
                                    </a>
                                @endif

                                @can('sales.invoices.pdf')
                                <a href="{{ route('sales.invoices.pdf', $invoice) }}" 
                                   class="btn btn-sm btn-outline-secondary" title="PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                @endcan

                                @if($invoice->can_be_cancelled)
                                    @can('sales.invoices.cancel')
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="cancelInvoice({{ $invoice->id }})" title="Cancel">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-file-invoice-dollar fa-3x mb-3"></i>
                                <p>No invoices found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        {{ $invoices->withQueryString()->links() }}
    </div>
</div>

<!-- Cancel Invoice Modal -->
<div class="modal fade" id="cancelInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelInvoiceForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Cancellation Reason <span class="text-danger">*</span></label>
                        <textarea name="cancellation_reason" class="form-control" rows="3" required 
                                  placeholder="Please provide reason for cancellation..."></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This action cannot be undone. The invoice will be marked as cancelled.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Cancel Invoice</button>
                </div>
            </form>
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

function cancelInvoice(invoiceId) {
    const modal = new bootstrap.Modal(document.getElementById('cancelInvoiceModal'));
    const form = document.getElementById('cancelInvoiceForm');
    form.action = `/sales/invoices/${invoiceId}/cancel`;
    modal.show();
}
</script>
@endsection




