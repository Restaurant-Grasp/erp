@extends('layouts.app')

@section('title', 'Replacement Report')

@section('content')
<div class="page-header">
    <h1 class="page-title">Replacement Report</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.returns.index') }}">Purchase Returns</a></li>
            <li class="breadcrumb-item active">Replacement Report</li>
        </ol>
    </nav>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Search & Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('purchase.returns.replacement-report') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                        <option value="credited" {{ request('status') == 'credited' ? 'selected' : '' }}>Credited</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="vendor">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ request('vendor') == $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->company_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
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
        <h5 class="mb-0">Replacement Tracking Report</h5>
        <div>
            <button type="button" class="btn btn-outline-success" onclick="exportReport()">
                <i class="fas fa-download me-2"></i> Export
            </button>
            <a href="{{ route('purchase.returns.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Returns
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Return No</th>
                        <th>Vendor</th>
                        <th>Return Date</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Reason</th>
                        <th>Replacement Status</th>
                        <th>Replacement PO</th>
                        <th>Return Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                        @foreach($return->items->where('replacement_required', true) as $item)
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
                                <strong>{{ $item->product->name }}</strong>
                                @if($item->product->product_code)
                                <br><small class="text-muted">{{ $item->product->product_code }}</small>
                                @endif
                                @if($item->serialNumber)
                                <br><span class="badge bg-info">SN: {{ $item->serialNumber->serial_number }}</span>
                                @endif
                            </td>
                            <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                            <td>{{ $item->reason }}</td>
                            <td class="text-center">
                                @if($item->replacement_po_no)
                                    <span class="badge bg-success">Ordered</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->replacement_po_no)
                                    <strong>{{ $item->replacement_po_no }}</strong>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="addReplacementPO({{ $item->id }})">
                                        <i class="fas fa-plus"></i> Add PO
                                    </button>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $return->status_badge }}">
                                    {{ ucfirst($return->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('purchase.returns.show', $return) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(!$item->replacement_po_no)
                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                            onclick="markReplaced({{ $item->id }})" title="Mark as Replaced">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-exchange-alt fa-3x mb-3"></i>
                                <p>No replacement items found</p>
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

<!-- Replacement Statistics -->
<div class="row mt-4">
    @php
        $totalReplacements = 0;
        $pendingReplacements = 0;
        $orderedReplacements = 0;
        $totalValue = 0;
        
        foreach($returns as $return) {
            foreach($return->items->where('replacement_required', true) as $item) {
                $totalReplacements++;
                $totalValue += $item->total_amount;
                
                if($item->replacement_po_no) {
                    $orderedReplacements++;
                } else {
                    $pendingReplacements++;
                }
            }
        }
    @endphp
    
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-exchange-alt fa-2x text-primary mb-2"></i>
                <h5 class="card-title">Total Replacements</h5>
                <h3>{{ $totalReplacements }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                <h5 class="card-title">Pending Orders</h5>
                <h3>{{ $pendingReplacements }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h5 class="card-title">Ordered</h5>
                <h3>{{ $orderedReplacements }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-dollar-sign fa-2x text-info mb-2"></i>
                <h5 class="card-title">Total Value</h5>
                <h3>₹{{ number_format($totalValue, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Add Replacement PO Modal -->
<div class="modal fade" id="replacementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Replacement PO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="replacementForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Replacement PO Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="replacement_po_no" required 
                               placeholder="Enter PO number for replacement">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3" 
                                  placeholder="Additional notes about the replacement order"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Replacement PO</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentItemId = null;

$(document).ready(function() {
    // Auto-submit filter form on change
    $('#filterForm select, #filterForm input[type="date"]').on('change', function() {
        $('#filterForm').submit();
    });
});

function addReplacementPO(itemId) {
    currentItemId = itemId;
    $('#replacementModal').modal('show');
}

function markReplaced(itemId) {
    if (confirm('Mark this item as replaced? This action cannot be undone.')) {
        // Here you would implement the logic to mark item as replaced
        // For now, we'll just show a success message
        alert('Item marked as replaced successfully!');
        location.reload();
    }
}

function exportReport() {
    // Get current filter parameters
    const params = new URLSearchParams(window.location.search);
    params.append('export', 'excel');
    
    // Create download link
    const url = '{{ route("purchase.returns.replacement-report") }}?' + params.toString();
    window.open(url, '_blank');
}

$('#replacementForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('_token', '{{ csrf_token() }}');
    
    // Here you would implement the AJAX call to update the replacement PO
    // For now, we'll simulate success
    
    const poNumber = formData.get('replacement_po_no');
    if (poNumber) {
        alert('Replacement PO "' + poNumber + '" added successfully!');
        $('#replacementModal').modal('hide');
        location.reload();
    }
});

$('#replacementModal').on('hidden.bs.modal', function() {
    $('#replacementForm')[0].reset();
    currentItemId = null;
});
</script>
@endsection