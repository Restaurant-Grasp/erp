@extends('layouts.app')

@section('title', 'Purchase Order Details')

@section('content')
<div class="page-header">
    <h1 class="page-title">Purchase Order Details</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.orders.index') }}">Purchase Orders</a></li>
            <li class="breadcrumb-item active">{{ $order->po_no }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- PO Details -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Purchase Order {{ $order->po_no }}</h5>
                <div>
                    <span class="badge bg-{{ $order->status_badge }} me-2">
                        {{ ucwords(str_replace('_', ' ', $order->status)) }}
                    </span>
                    <span class="badge bg-{{ $order->approval_status_badge }}">
                        {{ ucfirst($order->approval_status) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <h6>Vendor Information</h6>
                        <p class="mb-1"><strong>{{ $order->vendor->company_name }}</strong></p>
                        <p class="mb-1">{{ $order->vendor->vendor_code }}</p>
                        @if($order->vendor->contact_person)
                        <p class="mb-1">Contact: {{ $order->vendor->contact_person }}</p>
                        @endif
                        @if($order->vendor->email)
                        <p class="mb-1">Email: {{ $order->vendor->email }}</p>
                        @endif
                        @if($order->vendor->phone)
                        <p class="mb-0">Phone: {{ $order->vendor->phone }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6>Order Information</h6>
                        <p class="mb-1"><strong>PO Date:</strong> {{ $order->po_date->format('d/m/Y') }}</p>
                        @if($order->delivery_date)
                        <p class="mb-1"><strong>Delivery Date:</strong> {{ $order->delivery_date->format('d/m/Y') }}</p>
                        @endif
                        @if($order->reference_no)
                        <p class="mb-1"><strong>Reference:</strong> {{ $order->reference_no }}</p>
                        @endif
                        <p class="mb-1"><strong>Currency:</strong> {{ $order->currency }} (Rate: {{ $order->exchange_rate }})</p>
                        <p class="mb-0"><strong>Created by:</strong> {{ $order->createdBy->name }}</p>
                    </div>
                </div>

                @if($order->approval_status === 'approved' && $order->approvedBy)
                <div class="alert alert-success mt-3">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Approved by {{ $order->approvedBy->name }}</strong> on {{ $order->approved_date->format('d/m/Y H:i') }}
                    @if($order->approval_notes)
                    <br>Notes: {{ $order->approval_notes }}
                    @endif
                </div>
                @elseif($order->approval_status === 'rejected' && $order->approvedBy)
                <div class="alert alert-danger mt-3">
                    <i class="fas fa-times-circle me-2"></i>
                    <strong>Rejected by {{ $order->approvedBy->name }}</strong> on {{ $order->approved_date->format('d/m/Y H:i') }}
                    @if($order->approval_notes)
                    <br>Reason: {{ $order->approval_notes }}
                    @endif
                </div>
                @endif
            </div>
        </div>

        <!-- PO Items -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Order Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Description</th>
                                <th class="text-end">Qty</th>
                                <th>UOM</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Tax</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->item_name }}</strong>
                                    <br><small class="text-muted">{{ ucfirst($item->item_type) }}</small>
                                </td>
                                <td>{{ $item->description }}</td>
                                <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                <td>{{ $item->uom ? $item->uom->name : '-' }}</td>
                                <td class="text-end">{{ $order->currency }} {{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">
                                    @if($item->discount_amount > 0)
                                    {{ $order->currency }} {{ number_format($item->discount_amount, 2) }}
                                    @else
                                    -
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($item->tax_amount > 0)
                                    {{ $order->currency }} {{ number_format($item->tax_amount, 2) }}
                                    @else
                                    -
                                    @endif
                                </td>
                                <td class="text-end">{{ $order->currency }} {{ number_format($item->total_amount, 2) }}</td>
                                <td class="text-end">
                                    {{ number_format($item->received_quantity, 2) }}
                                    @if($item->quantity > 0)
                                    <br><small class="text-muted">({{ number_format($item->received_percentage, 1) }}%)</small>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="7" class="text-end">Subtotal:</th>
                                <th class="text-end">{{ $order->currency }} {{ number_format($order->subtotal, 2) }}</th>
                                <th></th>
                            </tr>
                            @if($order->discount_amount > 0)
                            <tr>
                                <th colspan="7" class="text-end">Discount:</th>
                                <th class="text-end">{{ $order->currency }} {{ number_format($order->discount_amount, 2) }}</th>
                                <th></th>
                            </tr>
                            @endif
                            @if($order->tax_amount > 0)
                            <tr>
                                <th colspan="7" class="text-end">Tax:</th>
                                <th class="text-end">{{ $order->currency }} {{ number_format($order->tax_amount, 2) }}</th>
                                <th></th>
                            </tr>
                            @endif
                            <tr class="table-primary">
                                <th colspan="7" class="text-end">Total Amount:</th>
                                <th class="text-end">{{ $order->currency }} {{ number_format($order->total_amount, 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Terms & Conditions -->
        @if($order->terms_conditions)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Terms & Conditions</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{!! nl2br(e($order->terms_conditions)) !!}</p>
            </div>
        </div>
        @endif

        <!-- Notes -->
        @if($order->notes)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Internal Notes</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{!! nl2br(e($order->notes)) !!}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @can('purchases.po.edit')
                    @if($order->status === 'draft')
                    <a href="{{ route('purchase.orders.edit', $order) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i> Edit Purchase Order
                    </a>
                    @endif
                    @endcan

                    @can('purchases.po.approve')
                    @if($order->approval_status === 'pending')
                    <button type="button" class="btn btn-success" 
                            onclick="showApprovalModal({{ $order->id }}, '{{ $order->po_no }}')">
                        <i class="fas fa-check me-2"></i> Approve Order
                    </button>
                    <button type="button" class="btn btn-outline-danger" 
                            onclick="showRejectModal({{ $order->id }}, '{{ $order->po_no }}')">
                        <i class="fas fa-times me-2"></i> Reject Order
                    </button>
                    @endif
                    @endcan

                    @can('purchases.invoices.create')
                    @if($order->approval_status === 'approved')
                    <a href="{{ route('purchase.invoices.create-from-po', $order) }}" class="btn btn-outline-primary">
                        <i class="fas fa-file-invoice me-2"></i> Create Invoice
                    </a>
                    @endif
                    @endcan

                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i> Print
                    </button>

                    <a href="{{ route('purchase.orders.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Order Progress -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Order Progress</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Received Progress</label>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $order->received_percentage }}%"
                             aria-valuenow="{{ $order->received_percentage }}" 
                             aria-valuemin="0" aria-valuemax="100">
                            {{ number_format($order->received_percentage, 1) }}%
                        </div>
                    </div>
                </div>

                <div class="row text-center">
                    <div class="col-6">
                        <h6 class="mb-0">{{ $order->items->sum('quantity') }}</h6>
                        <small class="text-muted">Ordered</small>
                    </div>
                    <div class="col-6">
                        <h6 class="mb-0">{{ $order->items->sum('received_quantity') }}</h6>
                        <small class="text-muted">Received</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Documents -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Related Documents</h5>
            </div>
            <div class="card-body">
                <!-- Invoices -->
                @if($invoices->count() > 0)
                <h6>Purchase Invoices</h6>
                @foreach($invoices as $invoice)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <a href="{{ route('purchase.invoices.show', $invoice) }}" class="text-decoration-none">
                            {{ $invoice->invoice_no }}
                        </a>
                        <br><small class="text-muted">{{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}</small>
                    </div>
                    <span class="badge bg-{{ $invoice->status_badge }}">{{ ucfirst($invoice->status) }}</span>
                </div>
                @endforeach
                <hr>
                @endif

                <!-- GRNs -->
                @if($grns->count() > 0)
                <h6>Goods Receipt Notes</h6>
                @foreach($grns as $grn)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <a href="{{ route('purchase.grn.show', $grn) }}" class="text-decoration-none">
                            {{ $grn->grn_no }}
                        </a>
                        <br><small class="text-muted">{{ $grn->grn_date->format('d/m/Y') }}</small>
                    </div>
                    <span class="badge bg-{{ $grn->status_badge }}">{{ ucfirst($grn->status) }}</span>
                </div>
                @endforeach
                @else
                <p class="text-muted mb-0">No related documents yet.</p>
                @endif
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

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <p>Are you sure you want to reject PO <strong id="rejectPoNumber"></strong>?</p>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="approval_notes" class="form-control" rows="3" 
                                  placeholder="Enter reason for rejection..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showApprovalModal(poId, poNumber) {
    $('#poNumber').text(poNumber);
    $('#approvalForm').attr('action', '/purchase/orders/' + poId + '/approve');
    $('#approvalModal').modal('show');
}

function showRejectModal(poId, poNumber) {
    $('#rejectPoNumber').text(poNumber);
    $('#rejectForm').attr('action', '/purchase/orders/' + poId + '/reject');
    $('#rejectModal').modal('show');
}
</script>
@endsection