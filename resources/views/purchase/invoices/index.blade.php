@extends('layouts.app')

@section('title', 'Purchase Invoices')

@section('content')
<div class="page-header">
    <h1 class="page-title">Purchase Invoices</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Purchase Invoices</li>
        </ol>
    </nav>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Search & Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('purchase.invoices.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Search Invoice No, Vendor, PO..." 
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
                    <select class="form-select" name="invoice_type">
                        <option value="">All Types</option>
                        <option value="direct" {{ request('invoice_type') == 'direct' ? 'selected' : '' }}>Direct</option>
                        <option value="po_conversion" {{ request('invoice_type') == 'po_conversion' ? 'selected' : '' }}>PO Conversion</option>
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
        <h5 class="mb-0">All Purchase Invoices</h5>
        <div>
                 @php
            $role = auth()->user()->getRoleNames()->first();
            $permissions = getCurrentRolePermissions($role);
            @endphp
            @if ($permissions->contains('name', 'purchases.invoices.create'))
        
            <a href="{{ route('purchase.invoices.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Create Direct Invoice
            </a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="120">Invoice No</th>
                        <th>Vendor</th>
                        <th width="100">Invoice Date</th>
                        <th width="100">Due Date</th>
                        <th width="120">Total Amount</th>
                        <th width="100">Status</th>
                        <th width="80">Type</th>
                        <th width="100">Received %</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td>
                            <a href="{{ route('purchase.invoices.show', $invoice) }}" class="text-decoration-none">
                                {{ $invoice->invoice_no }}
                            </a>
                            @if($invoice->vendor_invoice_no)
                            <br><small class="text-muted">Vendor: {{ $invoice->vendor_invoice_no }}</small>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $invoice->vendor->company_name }}</strong>
                            <br><small class="text-muted">{{ $invoice->vendor->vendor_code }}</small>
                            @if($invoice->purchaseOrder)
                            <br><small class="text-info">PO: {{ $invoice->purchaseOrder->po_no }}</small>
                            @endif
                        </td>
                        <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                        <td>
                            {{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}
                            @if($invoice->status === 'overdue')
                            <br><small class="text-danger">{{ $invoice->days_overdue }} days overdue</small>
                            @endif
                        </td>
                        <td>
                            {{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}
                            @if($invoice->balance_amount > 0)
                            <br><small class="text-warning">Bal: {{ number_format($invoice->balance_amount, 2) }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $invoice->status_badge }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $invoice->invoice_type_badge }}">
                                {{ $invoice->invoice_type === 'po_conversion' ? 'PO Conv' : 'Direct' }}
                            </span>
                        </td>
                        <td>
                            @if($invoice->received_percentage > 0)
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-info" role="progressbar" 
                                         style="width: {{ $invoice->received_percentage }}%"
                                         aria-valuenow="{{ $invoice->received_percentage }}" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        {{ number_format($invoice->received_percentage, 1) }}%
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">0%</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                 @if ($permissions->contains('name', 'purchases.invoices.view'))
                          
                                <a href="{{ route('purchase.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary" 
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endif
                                 @if ($permissions->contains('name', 'purchases.invoices.edit'))

                            
                                @if(in_array($invoice->status, ['draft', 'pending']))
                                <a href="{{ route('purchase.invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-primary" 
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                @endif
                               @if ($permissions->contains('name', 'purchases.grn.create'))

                            
                                @if($invoice->received_percentage < 100)
                                <a href="{{ route('purchase.grn.create-from-invoice', $invoice) }}" class="btn btn-sm btn-outline-success" 
                                   title="Create GRN">
                                    <i class="fas fa-truck-loading"></i>
                                </a>
                                @endif
                                @endif
                                       @if ($permissions->contains('name', 'purchases.invoices.delete'))
                         
                                @if(in_array($invoice->status, ['draft', 'pending']))
                                <form action="{{ route('purchase.invoices.destroy', $invoice) }}" method="POST" 
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
                        <td colspan="9" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-file-invoice fa-3x mb-3"></i>
                                <p>No purchase invoices found</p>
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
                Showing {{ $invoices->firstItem() ?? 0 }} to {{ $invoices->lastItem() ?? 0 }} 
                of {{ $invoices->total() }} entries
            </div>
            
            {{ $invoices->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<!-- Purchase Invoice Statistics -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-file-invoice fa-2x text-primary mb-2"></i>
                <h5 class="card-title">Total Invoices</h5>
                <h3>{{ $invoices->total() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                <h5 class="card-title">Pending</h5>
                <h3>{{ \App\Models\PurchaseInvoice::where('status', 'pending')->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                <h5 class="card-title">Overdue</h5>
                <h3>{{ \App\Models\PurchaseInvoice::where('status', 'overdue')->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h5 class="card-title">Paid</h5>
                <h3>{{ \App\Models\PurchaseInvoice::where('status', 'paid')->count() }}</h3>
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
        
        if (confirm('Are you sure you want to delete this purchase invoice? This action cannot be undone.')) {
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