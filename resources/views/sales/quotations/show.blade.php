{{-- resources/views/sales/quotations/show.blade.php --}}
@extends('layouts.app')

@section('title', 'View Quotation')

@section('content')
<div class="page-header">
    <h1 class="page-title">Quotation {{ $quotation->quotation_no }}</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sales.quotations.index') }}">Quotations</a></li>
            <li class="breadcrumb-item active">{{ $quotation->quotation_no }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Quotation Details -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Quotation Details</h5>
                <div>
                    <span class="badge bg-{{ $quotation->status_badge }} me-2">{{ ucfirst($quotation->status) }}</span>
                    <span class="badge bg-{{ $quotation->approval_status_badge }}">{{ ucfirst(str_replace('_', ' ', $quotation->approval_status)) }}</span>
                    @if($quotation->is_expired)
                    <span class="badge bg-danger ms-1">Expired</span>
                    @endif
                    @if($quotation->is_revised)
                    <span class="badge bg-info ms-1">Revised</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>Quotation Date:</strong> {{ $quotation->quotation_date->format('d/m/Y') }}
                    </div>
                    <div class="col-md-6">
                        <strong>Valid Until:</strong> {{ $quotation->valid_until->format('d/m/Y') }}
                        @if($quotation->is_expired)
                        <span class="text-danger">(Expired)</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong>Customer/Lead:</strong>
                        @if($quotation->customer)
                        <span class="text-success">{{ $quotation->customer->company_name }}</span>
                        <br><small class="text-muted">Customer: {{ $quotation->customer->customer_code }}</small>
                        @elseif($quotation->lead)
                        <span class="text-warning">{{ $quotation->lead->company_name ?: $quotation->lead->contact_person }}</span>
                        <br><small class="text-muted">Lead: {{ $quotation->lead->lead_no }}</small>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong>Reference No:</strong> {{ $quotation->reference_no ?: 'N/A' }}
                    </div>
                    @if($quotation->subject)
                    <div class="col-md-12">
                        <strong>Subject:</strong> {{ $quotation->subject }}
                    </div>
                    @endif
                    @if($quotation->approved_by)
                    <div class="col-md-6">
                        <strong>Approved By:</strong> {{ $quotation->approvedBy->name }}
                    </div>
                    <div class="col-md-6">
                        <strong>Approved Date:</strong> {{ $quotation->approved_date->format('d/m/Y H:i') }}
                    </div>
                    @endif
                    @if($quotation->convertedInvoice)
                    <div class="col-md-12">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Converted to Invoice:</strong>
                            <a href="{{ route('sales.invoices.show', $quotation->convertedInvoice) }}" class="alert-link">
                                {{ $quotation->convertedInvoice->invoice_no }}
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Items -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th width="80">Qty</th>
                                <th width="100">Unit Price</th>
                                <th width="80">Discount</th>
                                <th width="100">Tax</th>
                                <th width="120">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quotation->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->item_name }}</strong>
                                    @if($item->description)
                                    <br><small class="text-muted">{{ $item->description }}</small>
                                    @endif
                                </td>
                                <td>{{ number_format($item->quantity, 2) }} {{ $item->uom->name ?? '' }}</td>
                                <td>₹{{ number_format($item->unit_price, 2) }}</td>
                                <td>{{ $item->discount_value }}%</td>
                                <td>
                                    @if($item->tax)
                                    {{ $item->tax->name }} ({{ $item->tax_rate }}%)
                                    <br>₹{{ number_format($item->tax_amount, 2) }}
                                    @else
                                    No Tax
                                    @endif
                                </td>
                                <td>₹{{ number_format($item->total_amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end"><strong>Subtotal:</strong></td>
                                <td><strong>₹{{ number_format($quotation->subtotal, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end"><strong>Tax Amount:</strong></td>
                                <td><strong>₹{{ number_format($quotation->tax_amount, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end"><strong>Total Amount:</strong></td>
                                <td><strong>₹{{ number_format($quotation->total_amount, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Terms & Conditions -->
        @if($quotation->terms_conditions)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Terms & Conditions</h5>
            </div>
            <div class="card-body">
                <p>{{ $quotation->terms_conditions }}</p>
            </div>
        </div>
        @endif

        <!-- Internal Notes -->
        @if($quotation->internal_notes)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Internal Notes</h5>
            </div>
            <div class="card-body">
                <p>{{ $quotation->internal_notes }}</p>
            </div>
        </div>
        @endif
    </div>
    @php
    $role = auth()->user()->getRoleNames()->first();
    $permissions = getCurrentRolePermissions($role);
    @endphp


    <div class="col-md-4">
        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">

                    @if($quotation->can_be_edited)
                    @if ($permissions->contains('name', 'sales.quotations.edit'))
                    <a href="{{ route('sales.quotations.edit', $quotation) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Quotation
                    </a>
                    @endif
                    @endif

                    <!-- Lead to Customer Conversion (only for lead quotations) -->
                    @if($quotation->lead_id && !$quotation->customer_id)
                    @if ($permissions->contains('name', 'sales.quotations.edit'))

                    <form action="{{ route('sales.quotations.convert-lead-to-customer', $quotation) }}" method="POST" class="convert-lead-form">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-user-plus me-2"></i>Convert Lead to Customer
                        </button>
                    </form>
                    <small class="text-muted">Convert lead to customer before approval</small>
                    @endif
                    @endif

                    @if($quotation->can_be_approved)
                    @if ($permissions->contains('name', 'sales.quotations.approve'))


                    <form action="{{ route('sales.quotations.approve', $quotation) }}" method="POST" class="approve-form">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check me-2"></i>Approve & Convert to Invoice
                        </button>
                    </form>
                    @if($quotation->lead_id && !$quotation->customer_id)
                    <small class="text-info">Note: Lead will be automatically converted to customer upon approval</small>
                    @endif
                    @endif
                    @endif

                    @if($quotation->approval_status === 'approved' && $quotation->status !== 'converted')
                    @if ($permissions->contains('name', 'sales.quotations.edit'))
                    <form action="{{ route('sales.quotations.convert-to-invoice', $quotation) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-info w-100">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Convert to Invoice
                        </button>
                    </form>
                    @endif
                    @endif

                    @if($quotation->status === 'draft' || $quotation->status === 'sent')
                    @if ($permissions->contains('name', 'sales.quotations.edit'))

                    <form action="{{ route('sales.quotations.revision', $quotation) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="fas fa-copy me-2"></i>Create Revision
                        </button>
                    </form>
                    @endif
                    @endif

                    @if ($permissions->contains('name', 'sales.quotations.view'))
                    <a href="{{ route('sales.quotations.pdf', $quotation) }}"
                        class="btn btn-outline-danger" target="_blank">
                        <i class="fas fa-file-pdf me-2"></i>Download PDF
                    </a>
                    <a href="{{ route('sales.quotations.print', $quotation) }}" class="btn btn-info btn-sm" target="_blank">
                        <i class="fas fa-print me-1"></i>Print
                    </a>
                    @endif

                    @if($quotation->status === 'draft')
                    @if ($permissions->contains('name', 'sales.quotations.edit'))
                    <form action="{{ route('sales.quotations.send', $quotation) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-info w-100">
                            <i class="fas fa-paper-plane me-2"></i>Send Quotation
                        </button>
                    </form>
                    @endif
                    @endif

                    @if ($permissions->contains('name', 'sales.quotations.edit'))
                    <form action="{{ route('sales.quotations.duplicate', $quotation) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-clone me-2"></i>Duplicate
                        </button>
                    </form>
                    @endif

                    @if($quotation->can_be_deleted)
                    @if ($permissions->contains('name', 'sales.quotations.delete'))
                    <form action="{{ route('sales.quotations.destroy', $quotation) }}" method="POST" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-2"></i>Delete
                        </button>
                    </form>
                    @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Revision History -->
        @if($quotation->parentQuotation || $quotation->revisions->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Revision History</h5>
            </div>
            <div class="card-body">
                @if($quotation->parentQuotation)
                <div class="mb-2">
                    <strong>Original:</strong>
                    <a href="{{ route('sales.quotations.show', $quotation->parentQuotation) }}">
                        {{ $quotation->parentQuotation->quotation_no }}
                    </a>
                </div>
                @endif

                @if($quotation->revisions->count() > 0)
                <div>
                    <strong>Revisions:</strong>
                    <ul class="list-unstyled">
                        @foreach($quotation->revisions as $revision)
                        <li>
                            <a href="{{ route('sales.quotations.show', $revision) }}">
                                {{ $revision->quotation_no }}
                            </a>
                            <span class="badge bg-{{ $revision->status_badge }} ms-1">{{ ucfirst($revision->status) }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Quotation Summary -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Summary</h5>
            </div>
            <div class="card-body">
                <div class="row g-2 text-sm">
                    <div class="col-6">Items:</div>
                    <div class="col-6 text-end">{{ $quotation->items->count() }}</div>

                    <div class="col-6">Subtotal:</div>
                    <div class="col-6 text-end">₹{{ number_format($quotation->subtotal, 2) }}</div>

                    <div class="col-6">Tax:</div>
                    <div class="col-6 text-end">₹{{ number_format($quotation->tax_amount, 2) }}</div>

                    <div class="col-6"><strong>Total:</strong></div>
                    <div class="col-6 text-end"><strong>₹{{ number_format($quotation->total_amount, 2) }}</strong></div>

                    <div class="col-6">Created:</div>
                    <div class="col-6 text-end">{{ $quotation->created_at->format('d/m/Y') }}</div>

                    <div class="col-6">Created By:</div>
                    <div class="col-6 text-end">{{ $quotation->createdBy->name }}</div>
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

            if (confirm('Are you sure you want to delete this quotation? This action cannot be undone.')) {
                form.submit();
            }
        });

        // Approve confirmation
        $('.approve-form').on('submit', function(e) {
            e.preventDefault();
            const form = this;

            let message = 'Are you sure you want to approve this quotation? It will be automatically converted to an invoice.';

            @if($quotation->lead_id && !$quotation->customer_id)
            message += '\n\nNote: The associated lead will also be automatically converted to a customer.';
            @endif

            if (confirm(message)) {
                form.submit();
            }
        });

        // Convert lead confirmation
        $('.convert-lead-form').on('submit', function(e) {
            e.preventDefault();
            const form = this;

            if (confirm('Are you sure you want to convert this lead to a customer? This will create a new customer record and update this quotation.')) {
                form.submit();
            }
        });
    });
</script>
@endsection