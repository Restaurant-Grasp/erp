@extends('layouts.app')

@section('title', 'Purchase Invoice Details')

@section('content')
<div class="page-header">
    <h1 class="page-title">Purchase Invoice Details</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.invoices.index') }}">Purchase Invoices</a></li>
            <li class="breadcrumb-item active">{{ $invoice->invoice_no }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Invoice Header -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Invoice Information</h5>
                <div class="d-flex gap-2">
                    <span class="badge bg-{{ $invoice->status_badge }} fs-6">{{ ucfirst($invoice->status) }}</span>
                    <span class="badge bg-{{ $invoice->invoice_type_badge }} fs-6">
                        {{ $invoice->invoice_type === 'po_conversion' ? 'PO Conversion' : 'Direct Invoice' }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="150"><strong>Invoice No:</strong></td>
                                <td>{{ $invoice->invoice_no }}</td>
                            </tr>
                            @if($invoice->vendor_invoice_no)
                            <tr>
                                <td><strong>Vendor Invoice No:</strong></td>
                                <td>{{ $invoice->vendor_invoice_no }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Invoice Date:</strong></td>
                                <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Due Date:</strong></td>
                                <td>
                                    {{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}
                                    @if($invoice->status === 'overdue')
                                        <span class="text-danger ms-2">({{ $invoice->days_overdue }} days overdue)</span>
                                    @endif
                                </td>
                            </tr>
                            @if($invoice->purchaseOrder)
                            <tr>
                                <td><strong>Purchase Order:</strong></td>
                                <td>
                                    <a href="{{ route('purchase.orders.show', $invoice->purchaseOrder) }}" class="text-decoration-none">
                                        {{ $invoice->purchaseOrder->po_no }}
                                    </a>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="150"><strong>Vendor:</strong></td>
                                <td>
                                    <a href="{{ route('vendors.show', $invoice->vendor) }}" class="text-decoration-none">
                                        {{ $invoice->vendor->company_name }}
                                    </a>
                                    <br><small class="text-muted">{{ $invoice->vendor->vendor_code }}</small>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Currency:</strong></td>
                                <td>{{ $invoice->currency }} (Rate: {{ $invoice->exchange_rate }})</td>
                            </tr>
                            <tr>
                                <td><strong>Payment Terms:</strong></td>
                                <td>{{ $invoice->payment_terms }} days</td>
                            </tr>
                            <tr>
                                <td><strong>Created By:</strong></td>
                                <td>{{ $invoice->createdBy->name ?? 'System' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Created Date:</strong></td>
                                <td>{{ $invoice->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Invoice Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th width="100">Qty</th>
                                <th width="80">UOM</th>
                                <th width="120">Unit Price</th>
                                <th width="100">Discount</th>
                                <th width="80">Tax %</th>
                                <th width="120">Total</th>
                                <th width="100">Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->item_name }}</strong>
                                    @if($item->description)
                                        <br><small class="text-muted">{{ $item->description }}</small>
                                    @endif
                                    @if($item->poItem)
                                        <br><small class="text-info">From PO: {{ $item->poItem->purchaseOrder->po_no }}</small>
                                    @endif
                                </td>
                                <td>{{ number_format($item->quantity, 2) }}</td>
                                <td>{{ $item->uom->name ?? '-' }}</td>
                                <td>{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                                <td>
                                    @if($item->discount_value > 0)
                                        {{ number_format($item->discount_value, 2) }}
                                        {{ $item->discount_type === 'percentage' ? '%' : $invoice->currency }}
                                        <br><small class="text-muted">{{ $invoice->currency }} {{ number_format($item->discount_amount, 2) }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ number_format($item->tax_rate, 2) }}%</td>
                                <td>{{ $invoice->currency }} {{ number_format($item->total_amount, 2) }}</td>
                                <td>
                                    @if($item->item_type === 'product')
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-info" role="progressbar" 
                                                 style="width: {{ $item->received_percentage }}%"
                                                 aria-valuenow="{{ $item->received_percentage }}" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                                {{ number_format($item->received_percentage, 1) }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ number_format($item->received_quantity, 2) }} / {{ number_format($item->quantity, 2) }}</small>
                                    @else
                                        <span class="text-muted">Service</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
@if($invoice->files->count() > 0)
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-paperclip me-2"></i>Vendor Invoice Documents
            <span class="badge bg-primary ms-2">{{ $invoice->files->count() }}</span>
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($invoice->files as $file)
            <div class="col-md-6 col-lg-4">
                <div class="card border h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start mb-2">
                            <div class="me-3">
                                @if($file->is_image)
                                    <div class="bg-info bg-opacity-10 p-2 rounded">
                                        <i class="fas fa-image text-info fa-2x"></i>
                                    </div>
                                @elseif($file->is_pdf)
                                    <div class="bg-danger bg-opacity-10 p-2 rounded">
                                        <i class="fas fa-file-pdf text-danger fa-2x"></i>
                                    </div>
                                @else
                                    <div class="bg-primary bg-opacity-10 p-2 rounded">
                                        <i class="fas fa-file text-primary fa-2x"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <h6 class="mb-1 text-truncate" title="{{ $file->file_name }}">
                                    {{ $file->file_name }}
                                </h6>
                                <span class="badge bg-secondary mb-2">{{ $file->file_extension }}</span>
                                <p class="text-muted small mb-2">{{ $file->formatted_file_size }}</p>
                                @if($file->description)
                                <p class="text-muted small mb-2">{{ $file->description }}</p>
                                @endif
                                <p class="text-muted small mb-0">
                                    <i class="fas fa-user me-1"></i>{{ $file->uploadedBy->name }}
                                    <br>
                                    <i class="fas fa-clock me-1"></i>{{ $file->uploaded_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="{{ route('purchase.invoices.files.download', $file) }}" 
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-download me-2"></i>Download
                            </a>
                            @if($file->is_image)
                            <button type="button" class="btn btn-outline-info btn-sm" 
                                    onclick="previewImage('{{ $file->file_url }}', '{{ $file->file_name }}')">
                                <i class="fas fa-eye me-2"></i>Preview
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
<div class="modal fade" id="imagePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imagePreviewTitle">Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="imagePreviewImg" src="" alt="" class="img-fluid" style="max-height: 500px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
        <!-- GRNs -->
        @if($grns->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Related Goods Receipt Notes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>GRN No</th>
                                <th>GRN Date</th>
                                <th>Received By</th>
                                <th>Status</th>
                                <th>Items</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grns as $grn)
                            <tr>
                                <td>{{ $grn->grn_no }}</td>
                                <td>{{ $grn->grn_date->format('d/m/Y') }}</td>
                                <td>{{ $grn->receivedBy->name ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $grn->status_badge }}">
                                        {{ ucfirst($grn->status) }}
                                    </span>
                                </td>
                                <td>{{ $grn->items->count() }} items</td>
                                <td>
                                    <a href="{{ route('purchase.grn.show', $grn) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
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

        <!-- Notes -->
        @if($invoice->notes)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Notes</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $invoice->notes }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <!-- Summary -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Invoice Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td>Subtotal:</td>
                        <td class="text-end">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    @if($invoice->discount_amount > 0)
                    <tr>
                        <td>Discount:</td>
                        <td class="text-end text-success">-{{ $invoice->currency }} {{ number_format($invoice->discount_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Tax Amount:</td>
                        <td class="text-end">{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</td>
                    </tr>
                    <tr class="table-primary">
                        <td><strong>Total Amount:</strong></td>
                        <td class="text-end"><strong>{{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}</strong></td>
                    </tr>
                    @if($invoice->paid_amount > 0)
                    <tr>
                        <td>Paid Amount:</td>
                        <td class="text-end text-success">{{ $invoice->currency }} {{ number_format($invoice->paid_amount, 2) }}</td>
                    </tr>
                    <tr class="{{ $invoice->balance_amount > 0 ? 'table-warning' : 'table-success' }}">
                        <td><strong>Balance:</strong></td>
                        <td class="text-end"><strong>{{ $invoice->currency }} {{ number_format($invoice->balance_amount, 2) }}</strong></td>
                    </tr>
                    @endif
                </table>
                
                @if($invoice->received_percentage > 0)
                <div class="mt-3">
                    <label class="form-label">Goods Received Progress</label>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-info" role="progressbar" 
                             style="width: {{ $invoice->received_percentage }}%"
                             aria-valuenow="{{ $invoice->received_percentage }}" 
                             aria-valuemin="0" aria-valuemax="100">
                            {{ number_format($invoice->received_percentage, 1) }}%
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
                    @can('purchases.invoices.edit')
                    @if(in_array($invoice->status, ['draft', 'pending']))
                    <a href="{{ route('purchase.invoices.edit', $invoice) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i> Edit Invoice
                    </a>
                    @endif
                    @endcan

                    @can('purchases.grn.create')
                    @if($invoice->received_percentage < 100)
                    <a href="{{ route('purchase.grn.create-from-invoice', $invoice) }}" class="btn btn-success">
                        <i class="fas fa-truck-loading me-2"></i> Create GRN
                    </a>
                    @endif
                    @endcan

                    <button type="button" class="btn btn-info" onclick="printInvoice()">
                        <i class="fas fa-print me-2"></i> Print Invoice
                    </button>

                    <button type="button" class="btn btn-secondary" onclick="exportPDF()">
                        <i class="fas fa-file-pdf me-2"></i> Export PDF
                    </button>

                    <!-- E-Invoice placeholder -->
                    <button type="button" class="btn btn-warning" onclick="submitEInvoice()" disabled>
                        <i class="fas fa-cloud-upload-alt me-2"></i> Submit E-Invoice
                        <small class="d-block">Coming Soon</small>
                    </button>

                    <hr>

                    @can('purchases.invoices.delete')
                    @if(in_array($invoice->status, ['draft', 'pending']))
                    <form action="{{ route('purchase.invoices.destroy', $invoice) }}" method="POST" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-2"></i> Delete Invoice
                        </button>
                    </form>
                    @endif
                    @endcan
                </div>
            </div>
        </div>

        <!-- Payment History (Placeholder) -->
        @if($invoice->paid_amount > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Payment History</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Payment history will be shown here when payment module is integrated.
                </div>
            </div>
        </div>
        @endif

        <!-- Related Documents -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Related Documents</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @if($invoice->purchaseOrder)
                    <a href="{{ route('purchase.orders.show', $invoice->purchaseOrder) }}" 
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-file-alt me-2"></i>
                            Purchase Order
                        </div>
                        <span class="badge bg-primary">{{ $invoice->purchaseOrder->po_no }}</span>
                    </a>
                    @endif
                    
                    @foreach($grns as $grn)
                    <a href="{{ route('purchase.grn.show', $grn) }}" 
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-truck-loading me-2"></i>
                            Goods Receipt Note
                        </div>
                        <span class="badge bg-success">{{ $grn->grn_no }}</span>
                    </a>
                    @endforeach
                    
                    @if($grns->count() === 0 && !$invoice->purchaseOrder)
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-file-o fa-2x mb-2"></i>
                        <p>No related documents</p>
                    </div>
                    @endif
                </div>
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
});
function previewImage(imageUrl, fileName) {
    $('#imagePreviewTitle').text(fileName);
    $('#imagePreviewImg').attr('src', imageUrl);
    $('#imagePreviewModal').modal('show');
}
function printInvoice() {
    window.print();
}

function exportPDF() {
    // Placeholder for PDF export functionality
    alert('PDF export functionality will be implemented soon.');
}

function submitEInvoice() {
    // Placeholder for e-invoice submission
    alert('E-Invoice submission functionality will be implemented soon.');
}
</script>

<style>
    .min-w-0 {
    min-width: 0;
}
@media print {
    .btn, .card-header, .breadcrumb, .page-header {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .col-md-4 {
        display: none !important;
    }
    
    .col-md-8 {
        width: 100% !important;
    }
}
</style>
@endsection