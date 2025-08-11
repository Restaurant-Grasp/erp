@extends('layouts.app')

@section('title', 'GRN Details')

@section('content')
<div class="page-header">
    <h1 class="page-title">Goods Receipt Note Details</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.grn.index') }}">Goods Receipt Notes</a></li>
            <li class="breadcrumb-item active">{{ $grn->grn_no }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- GRN Information -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">GRN Information</h5>
                <div>
                    <span class="badge bg-{{ $grn->status_badge }} fs-6">{{ ucfirst($grn->status) }}</span>
                    @can('purchases.grn.edit')
                    @if($grn->status === 'draft')
                    <a href="{{ route('purchase.grn.edit', $grn) }}" class="btn btn-sm btn-outline-primary ms-2">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    @endif
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <strong>GRN No:</strong><br>
                        <span class="text-primary">{{ $grn->grn_no }}</span>
                    </div>
                    <div class="col-md-3">
                        <strong>GRN Date:</strong><br>
                        {{ $grn->grn_date->format('d/m/Y') }}
                    </div>
                    <div class="col-md-3">
                        <strong>Received By:</strong><br>
                        {{ $grn->receivedBy ? $grn->receivedBy->name : 'Not specified' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Created By:</strong><br>
                        {{ $grn->createdBy ? $grn->createdBy->name : 'System' }}
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <strong>Vendor:</strong><br>
                        <a href="{{ route('vendors.show', $grn->vendor) }}" class="text-decoration-none">
                            {{ $grn->vendor->company_name }}
                        </a><br>
                        <small class="text-muted">{{ $grn->vendor->vendor_code }}</small>
                    </div>
                    <div class="col-md-6">
                        <strong>References:</strong><br>
                        @if($grn->purchaseOrder)
                            <span class="badge bg-info me-1">PO</span>
                            <a href="{{ route('purchase.orders.show', $grn->purchaseOrder) }}" class="text-decoration-none">
                                {{ $grn->purchaseOrder->po_no }}
                            </a><br>
                        @endif
                        @if($grn->purchaseInvoice)
                            <span class="badge bg-success me-1">Invoice</span>
                            <a href="{{ route('purchase.invoices.show', $grn->purchaseInvoice) }}" class="text-decoration-none">
                                {{ $grn->purchaseInvoice->invoice_no }}
                            </a><br>
                        @endif
                        @if(!$grn->purchaseOrder && !$grn->purchaseInvoice)
                            <span class="text-muted">Direct GRN</span>
                        @endif
                    </div>
                </div>

                @if($grn->notes)
                <div class="row g-3 mt-2">
                    <div class="col-md-12">
                        <strong>Notes:</strong><br>
                        <div class="alert alert-light">{{ $grn->notes }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Items Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Received Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th width="100">Received Qty</th>
                                <th width="100">Accepted Qty</th>
                                <th width="100">Damaged Qty</th>
                                <th width="80">UOM</th>
                                <th width="120">Batch No</th>
                                <th width="120">Expiry Date</th>
                                <th width="100">Serial Numbers</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($grn->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product->name }}</strong><br>
                                    <small class="text-muted">{{ $item->product->product_code }}</small>
                                    @if($item->damage_reason)
                                    <br><small class="text-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i>{{ $item->damage_reason }}
                                    </small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ number_format($item->quantity, 2) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ number_format($item->accepted_quantity, 2) }}</span>
                                </td>
                                <td class="text-center">
                                    @if($item->damaged_quantity > 0)
                                        <span class="badge bg-danger">{{ number_format($item->damaged_quantity, 2) }}</span>
                                        @if($item->replacement_required)
                                        <br><small class="text-warning">
                                            <i class="fas fa-exchange-alt me-1"></i>Replacement Required
                                        </small>
                                        @endif
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ $item->uom ? $item->uom->name : '-' }}
                                </td>
                                <td class="text-center">
                                    {{ $item->batch_no ?: '-' }}
                                </td>
                                <td class="text-center">
                                    {{ $item->expiry_date ? $item->expiry_date->format('d/m/Y') : '-' }}
                                </td>
                                <td class="text-center">
                                    @if(isset($serialNumbers[$item->id]) && $serialNumbers[$item->id]->count() > 0)
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="showSerialNumbers({{ $item->id }}, '{{ $item->product->name }}')">
                                            <i class="fas fa-barcode me-1"></i>{{ $serialNumbers[$item->id]->count() }}
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No items found in this GRN</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Returns Section (if any) -->
        @if($returns->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-undo me-2"></i>Related Returns
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Return No</th>
                                <th>Return Date</th>
                                <th>Type</th>
                                <th>Total Items</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($returns as $return)
                            <tr>
                                <td>
                                    <a href="{{ route('purchase.returns.show', $return) }}" class="text-decoration-none">
                                        {{ $return->return_no }}
                                    </a>
                                </td>
                                <td>{{ $return->return_date->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $return->return_type_badge }}">
                                        {{ ucfirst($return->return_type) }}
                                    </span>
                                </td>
                                <td>{{ $return->total_items }}</td>
                                <td>
                                    <span class="badge bg-{{ $return->status_badge }}">
                                        {{ ucfirst($return->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('purchase.returns.show', $return) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <!-- Summary Statistics -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Receipt Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary">{{ $grn->total_items }}</h4>
                            <small class="text-muted">Total Items</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-info">{{ number_format($grn->total_quantity, 2) }}</h4>
                        <small class="text-muted">Total Quantity</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-success">{{ number_format($grn->total_accepted_quantity, 2) }}</h4>
                            <small class="text-muted">Accepted</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-danger">{{ number_format($grn->total_damaged_quantity, 2) }}</h4>
                        <small class="text-muted">Damaged</small>
                    </div>
                </div>
                
                @if($grn->total_quantity > 0)
                <div class="mt-3">
                    <label class="form-label">Acceptance Rate</label>
                    @php $acceptanceRate = ($grn->total_accepted_quantity / $grn->total_quantity) * 100; @endphp
                    <div class="progress">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $acceptanceRate }}%">
                            {{ number_format($acceptanceRate, 1) }}%
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @can('purchases.grn.edit')
                    @if($grn->status === 'draft')
                    <a href="{{ route('purchase.grn.edit', $grn) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i> Edit GRN
                    </a>
                    @endif
                    @endcan
                    
                    @if($grn->total_damaged_quantity > 0)
                    @can('purchases.returns.create')
                    <a href="{{ route('purchase.returns.create', ['grn_id' => $grn->id]) }}" class="btn btn-warning">
                        <i class="fas fa-undo me-2"></i> Create Return
                    </a>
                    @endcan
                    @endif
                    
                    <button type="button" class="btn btn-outline-info" onclick="printGRN()">
                        <i class="fas fa-print me-2"></i> Print GRN
                    </button>
                    
                    <a href="{{ route('purchase.grn.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Related Documents -->
        @if($grn->purchaseOrder || $grn->purchaseInvoice)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Related Documents</h5>
            </div>
            <div class="card-body">
                @if($grn->purchaseOrder)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <i class="fas fa-file-alt text-info me-2"></i>
                        <strong>Purchase Order</strong><br>
                        <small class="text-muted">{{ $grn->purchaseOrder->po_no }}</small>
                    </div>
                    <a href="{{ route('purchase.orders.show', $grn->purchaseOrder) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
                @endif
                
                @if($grn->purchaseInvoice)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <i class="fas fa-file-invoice text-success me-2"></i>
                        <strong>Purchase Invoice</strong><br>
                        <small class="text-muted">{{ $grn->purchaseInvoice->invoice_no }}</small>
                    </div>
                    <a href="{{ route('purchase.invoices.show', $grn->purchaseInvoice) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Audit Trail -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Audit Trail</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6>GRN Created</h6>
                            <p class="text-muted mb-0">{{ $grn->created_at->format('d/m/Y H:i') }}</p>
                            <small class="text-muted">by {{ $grn->createdBy ? $grn->createdBy->name : 'System' }}</small>
                        </div>
                    </div>
                    
                    @if($grn->updated_at != $grn->created_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6>GRN Updated</h6>
                            <p class="text-muted mb-0">{{ $grn->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Serial Numbers Modal -->
<div class="modal fade" id="serialNumbersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Serial Numbers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6>Product: <span id="serialProductName"></span></h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Serial Number</th>
                                <th>Warranty Start</th>
                                <th>Warranty End</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="serialNumbersList">
                            <!-- Serial numbers will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const serialNumbersData = @json($serialNumbers);

function showSerialNumbers(itemId, productName) {
    $('#serialProductName').text(productName);
    
    const serials = serialNumbersData[itemId] || [];
    const tbody = $('#serialNumbersList');
    tbody.empty();
    
    serials.forEach(serial => {
        const warrantyStart = serial.warranty_start_date ? new Date(serial.warranty_start_date).toLocaleDateString() : '-';
        const warrantyEnd = serial.warranty_end_date ? new Date(serial.warranty_end_date).toLocaleDateString() : '-';
        
        let statusBadge = 'secondary';
        if (serial.warranty_status === 'active') statusBadge = 'success';
        else if (serial.warranty_status === 'expired') statusBadge = 'warning';
        else if (serial.warranty_status === 'void') statusBadge = 'danger';
        
        tbody.append(`
            <tr>
                <td><strong>${serial.serial_number}</strong></td>
                <td>${warrantyStart}</td>
                <td>${warrantyEnd}</td>
                <td><span class="badge bg-${statusBadge}">${serial.warranty_status}</span></td>
            </tr>
        `);
    });
    
    $('#serialNumbersModal').modal('show');
}

function printGRN() {
    window.print();
}
</script>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.timeline::before {
    content: '';
    position: absolute;
    left: -30px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #dee2e6;
}

@media print {
    .btn, .card-header .badge, .timeline { display: none !important; }
    .card { border: 1px solid #000 !important; }
}
</style>
@endsection