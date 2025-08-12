@extends('layouts.app')

@section('title', 'Goods Receipt Notes')

@section('content')
<div class="page-header">
    <h1 class="page-title">Goods Receipt Notes</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Goods Receipt Notes</li>
        </ol>
    </nav>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Search & Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('purchase.grn.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Search GRN No, PO No, Invoice No..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
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
        <h5 class="mb-0">All Goods Receipt Notes</h5>
        <div>
                   @php
            $role = auth()->user()->getRoleNames()->first();
            $permissions = getCurrentRolePermissions($role);
            @endphp
            @if ($permissions->contains('name', 'purchases.grn.create'))
            <div class="btn-group">
                <a href="{{ route('purchase.grn.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> Create GRN
                </a>
                <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                    <span class="visually-hidden">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('purchase.grn.create') }}">
                        <i class="fas fa-plus me-2"></i> Direct GRN
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="showInvoiceSelectionModal()">
                        <i class="fas fa-file-invoice me-2"></i> GRN from Invoice
                    </a></li>
                </ul>
            </div>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="120">GRN No</th>
                        <th>Vendor</th>
                        <th>Reference</th>
                        <th width="100">GRN Date</th>
                        <th width="100">Total Items</th>
                        <th width="120">Accepted Qty</th>
                        <th width="120">Damaged Qty</th>
                        <th width="100">Status</th>
                        <th width="120">Received By</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($grns as $grn)
                    <tr>
                        <td>
                            <a href="{{ route('purchase.grn.show', $grn) }}" class="text-decoration-none">
                                {{ $grn->grn_no }}
                            </a>
                        </td>
                        <td>
                            <strong>{{ $grn->vendor->company_name }}</strong>
                            <br><small class="text-muted">{{ $grn->vendor->vendor_code }}</small>
                        </td>
                        <td>
                            @if($grn->purchaseOrder)
                                <span class="badge bg-info">PO</span> {{ $grn->purchaseOrder->po_no }}
                            @endif
                            @if($grn->purchaseInvoice)
                                <br><span class="badge bg-success">Invoice</span> {{ $grn->purchaseInvoice->invoice_no }}
                            @endif
                            @if(!$grn->purchaseOrder && !$grn->purchaseInvoice)
                                <span class="text-muted">Direct GRN</span>
                            @endif
                        </td>
                    <td>{{ \Carbon\Carbon::parse($grn->grn_date)->format('d/m/Y') }}</td>

                        <td>
                            <span class="badge bg-primary">{{ $grn->total_items }}</span>
                        </td>
                        <td>
                            <span class="text-success">{{ number_format($grn->total_accepted_quantity, 2) }}</span>
                        </td>
                        <td>
                            @if($grn->total_damaged_quantity > 0)
                                <span class="text-danger">{{ number_format($grn->total_damaged_quantity, 2) }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $grn->status_badge }}">
                                {{ ucfirst($grn->status) }}
                            </span>
                        </td>
                        <td>
                            @if($grn->receivedBy)
                                {{ $grn->receivedBy->name }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                    
                        @if ($permissions->contains('name', 'purchases.grn.view'))
                            
                                <a href="{{ route('purchase.grn.show', $grn) }}" class="btn btn-sm btn-outline-primary" 
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endif
                                  @if ($permissions->contains('name', 'purchases.grn.edit'))
                          
                                @if($grn->status === 'draft')
                                <a href="{{ route('purchase.grn.edit', $grn) }}" class="btn btn-sm btn-outline-primary" 
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                @endif
                                
                                @if($grn->total_damaged_quantity > 0)
                                <a href="{{ route('purchase.returns.index', ['grn' => $grn->id]) }}" 
                                   class="btn btn-sm btn-outline-warning" title="View Returns">
                                    <i class="fas fa-undo"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-truck-loading fa-3x mb-3"></i>
                                <p>No goods receipt notes found</p>
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
                Showing {{ $grns->firstItem() ?? 0 }} to {{ $grns->lastItem() ?? 0 }} 
                of {{ $grns->total() }} entries
            </div>
            
            {{ $grns->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<!-- GRN Statistics -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-truck-loading fa-2x text-primary mb-2"></i>
                <h5 class="card-title">Total GRNs</h5>
                <h3>{{ $grns->total() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-edit fa-2x text-warning mb-2"></i>
                <h5 class="card-title">Draft</h5>
                <h3>{{ \App\Models\GoodsReceiptNote::where('status', 'draft')->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-clock fa-2x text-info mb-2"></i>
                <h5 class="card-title">Partial</h5>
                <h3>{{ \App\Models\GoodsReceiptNote::where('status', 'partial')->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h5 class="card-title">Completed</h5>
                <h3>{{ \App\Models\GoodsReceiptNote::where('status', 'completed')->count() }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Invoice Selection Modal -->
<div class="modal fade" id="invoiceSelectionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Invoice for GRN</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Invoice No</th>
                                <th>Vendor</th>
                                <th>Date</th>
                                <th>Total Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\PurchaseInvoice::with('vendor')->where('status', '!=', 'cancelled')->latest()->take(10)->get() as $invoice)
                            <tr>
                                <td>{{ $invoice->invoice_no }}</td>
                                <td>{{ $invoice->vendor->company_name }}</td>
                                <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                                <td>{{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}</td>
                                <td>
                                    <a href="{{ route('purchase.grn.create-from-invoice', $invoice) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus me-1"></i> Create GRN
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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

function showInvoiceSelectionModal() {
    $('#invoiceSelectionModal').modal('show');
}
</script>
@endsection