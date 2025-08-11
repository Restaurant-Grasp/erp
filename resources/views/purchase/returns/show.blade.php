@extends('layouts.app')

@section('title', 'Purchase Return Details')

@section('content')
<div class="page-header">
    <h1 class="page-title">Purchase Return Details</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.returns.index') }}">Purchase Returns</a></li>
            <li class="breadcrumb-item active">{{ $return->return_no }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Return Information -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Return Information</h5>
                <div>
                    <span class="badge bg-{{ $return->status_badge }} fs-6">{{ ucfirst($return->status) }}</span>
                    <span class="badge bg-{{ $return->return_type_badge }} fs-6 ms-2">{{ ucwords(str_replace('_', ' ', $return->return_type)) }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="150"><strong>Return No:</strong></td>
                                <td>{{ $return->return_no }}</td>
                            </tr>
                            <tr>
                                <td><strong>Return Date:</strong></td>
                                <td>{{ $return->return_date->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Return Type:</strong></td>
                                <td>
                                    <span class="badge bg-{{ $return->return_type_badge }}">
                                        {{ ucwords(str_replace('_', ' ', $return->return_type)) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Vendor:</strong></td>
                                <td>
                                    <strong>{{ $return->vendor->company_name }}</strong><br>
                                    <small class="text-muted">{{ $return->vendor->vendor_code }}</small>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="150"><strong>GRN Reference:</strong></td>
                                <td>
                                    @if($return->grn)
                                        <a href="{{ route('purchase.grn.show', $return->grn) }}" class="text-decoration-none">
                                            {{ $return->grn->grn_no }}
                                        </a>
                                    @else
                                        <span class="text-muted">Direct Return</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Invoice Reference:</strong></td>
                                <td>
                                    @if($return->invoice)
                                        <a href="{{ route('purchase.invoices.show', $return->invoice) }}" class="text-decoration-none">
                                            {{ $return->invoice->invoice_no }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Created By:</strong></td>
                                <td>{{ $return->createdBy->name ?? 'System' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Created Date:</strong></td>
                                <td>{{ $return->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($return->notes)
                <div class="row mt-3">
                    <div class="col-12">
                        <strong>Notes:</strong>
                        <p class="mt-2 p-3 bg-light rounded">{{ $return->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Return Items -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Return Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th width="150">Serial Number</th>
                                <th width="100">Quantity</th>
                                <th width="120">Unit Price</th>
                                <th>Reason</th>
                                <th width="100">Replacement</th>
                                <th width="120">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($return->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product->name }}</strong>
                                    @if($item->product->product_code)
                                    <br><small class="text-muted">Code: {{ $item->product->product_code }}</small>
                                    @endif
                                    @if($item->grnItem)
                                    <br><small class="text-info">From GRN: {{ $item->grnItem->grn->grn_no }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($item->serialNumber)
                                        <span class="badge bg-info">{{ $item->serialNumber->serial_number }}</span>
                                        @if($item->serialNumber->warranty_end_date)
                                        <br><small class="text-muted">Warranty: {{ $item->serialNumber->warranty_end_date->format('d/m/Y') }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-end">
                                    @if($item->unit_price > 0)
                                        ₹{{ number_format($item->unit_price, 2) }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $item->reason }}</td>
                                <td class="text-center">
                                    @if($item->replacement_required)
                                        <span class="badge bg-warning">Required</span>
                                        @if($item->replacement_po_no)
                                        <br><small class="text-muted">PO: {{ $item->replacement_po_no }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">No</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($item->total_amount > 0)
                                        ₹{{ number_format($item->total_amount, 2) }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-box-open fa-2x mb-2"></i>
                                        <p>No items found</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($return->items->count() > 0)
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2">Total</th>
                                <th class="text-center">{{ number_format($return->total_quantity, 2) }}</th>
                                <th></th>
                                <th></th>
                                <th class="text-center">
                                    {{ $return->items->where('replacement_required', true)->count() }} replacements
                                </th>
                                <th class="text-end">₹{{ number_format($return->total_amount, 2) }}</th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Status Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Status Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($return->status === 'pending')
                        <button type="button" class="btn btn-success" onclick="approveReturn()">
                            <i class="fas fa-check me-2"></i> Approve Return
                        </button>
                    @endif
                    
                    @if($return->status === 'approved')
                        <button type="button" class="btn btn-warning" onclick="markReturned()">
                            <i class="fas fa-truck me-2"></i> Mark as Returned
                        </button>
                    @endif
                    
                    @if($return->status === 'returned')
                        <button type="button" class="btn btn-info" onclick="markCredited()">
                            <i class="fas fa-dollar-sign me-2"></i> Mark as Credited
                        </button>
                    @endif
                    
                    <a href="{{ route('purchase.returns.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Return Summary -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Return Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td>Total Items:</td>
                        <td class="text-end"><strong>{{ $return->total_items }}</strong></td>
                    </tr>
                    <tr>
                        <td>Total Quantity:</td>
                        <td class="text-end"><strong>{{ number_format($return->total_quantity, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Replacements Required:</td>
                        <td class="text-end">
                            <strong>{{ $return->items->where('replacement_required', true)->count() }}</strong>
                        </td>
                    </tr>
                    <tr class="table-primary">
                        <td><strong>Total Amount:</strong></td>
                        <td class="text-end"><strong>₹{{ number_format($return->total_amount, 2) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Related Documents -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Related Documents</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @if($return->grn)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <strong>GRN</strong>
                            <br><small class="text-muted">{{ $return->grn->grn_no }}</small>
                        </div>
                        <a href="{{ route('purchase.grn.show', $return->grn) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </li>
                    @endif
                    
                    @if($return->invoice)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <strong>Purchase Invoice</strong>
                            <br><small class="text-muted">{{ $return->invoice->invoice_no }}</small>
                        </div>
                        <a href="{{ route('purchase.invoices.show', $return->invoice) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </li>
                    @endif
                    
                    @if(!$return->grn && !$return->invoice)
                    <li class="list-group-item px-0">
                        <span class="text-muted">No related documents</span>
                    </li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- Replacement Tracking -->
        @if($return->items->where('replacement_required', true)->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Replacement Tracking</h5>
            </div>
            <div class="card-body">
                @foreach($return->items->where('replacement_required', true) as $item)
                <div class="mb-3 p-3 border rounded">
                    <strong>{{ $item->product->name }}</strong>
                    <br><small class="text-muted">Qty: {{ number_format($item->quantity, 2) }}</small>
                    
                    @if($item->replacement_po_no)
                    <br><span class="badge bg-success mt-1">Replacement PO: {{ $item->replacement_po_no }}</span>
                    @else
                    <br><span class="badge bg-warning mt-1">Pending Replacement</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function approveReturn() {
    if (confirm('Are you sure you want to approve this return?')) {
        $.ajax({
            url: '{{ route("purchase.returns.approve", $return) }}',
            method: 'PATCH',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error approving return: ' + (xhr.responseJSON?.message || 'Unknown error'));
            }
        });
    }
}

function markReturned() {
    if (confirm('Confirm that all items have been physically returned to the vendor?')) {
        $.ajax({
            url: '{{ route("purchase.returns.mark-returned", $return) }}',
            method: 'PATCH',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error updating return status: ' + (xhr.responseJSON?.message || 'Unknown error'));
            }
        });
    }
}

function markCredited() {
    if (confirm('Confirm that credit has been received from the vendor?')) {
        $.ajax({
            url: '{{ route("purchase.returns.mark-credited", $return) }}',
            method: 'PATCH',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error updating return status: ' + (xhr.responseJSON?.message || 'Unknown error'));
            }
        });
    }
}
</script>
@endsection