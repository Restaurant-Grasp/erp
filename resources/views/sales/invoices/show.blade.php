@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
<div class="page-header">
    <h1 class="page-title">Invoice Details</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sales.invoices.index') }}">Invoices</a></li>
            <li class="breadcrumb-item active">{{ $invoice->invoice_no }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Invoice Details -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Invoice: {{ $invoice->invoice_no }}</h5>
                <div>
                    <span class="badge bg-{{ $invoice->status_badge }} fs-6 me-2">
                        {{ ucfirst($invoice->status) }}
                    </span>
                    <span class="badge bg-{{ $invoice->delivery_status_badge }} fs-6">
                        {{ ucfirst(str_replace('_', ' ', $invoice->delivery_status)) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6>Customer Information</h6>
                        <address>
                            <strong>{{ $invoice->customer->company_name }}</strong><br>
                            @if($invoice->customer->contact_person)
                                {{ $invoice->customer->contact_person }}<br>
                            @endif
                            @if($invoice->customer->address_line1)
                                {{ $invoice->customer->address_line1 }}<br>
                            @endif
                            @if($invoice->customer->address_line2)
                                {{ $invoice->customer->address_line2 }}<br>
                            @endif
                            @if($invoice->customer->city)
                                {{ $invoice->customer->city }}, {{ $invoice->customer->state }} {{ $invoice->customer->postcode }}<br>
                            @endif
                            @if($invoice->customer->email)
                                <strong>Email:</strong> {{ $invoice->customer->email }}<br>
                            @endif
                            @if($invoice->customer->phone)
                                <strong>Phone:</strong> {{ $invoice->customer->phone }}
                            @endif
                        </address>
                    </div>
                    <div class="col-md-6">
                        <h6>Invoice Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Invoice Date:</strong></td>
                                <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Due Date:</strong></td>
                                <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                            </tr>
                            @if($invoice->reference_no)
                            <tr>
                                <td><strong>Reference:</strong></td>
                                <td>{{ $invoice->reference_no }}</td>
                            </tr>
                            @endif
                            @if($invoice->po_no)
                            <tr>
                                <td><strong>PO Number:</strong></td>
                                <td>{{ $invoice->po_no }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Payment Terms:</strong></td>
                                <td>{{ $invoice->payment_terms }} days</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Items -->
                <div class="mt-4">
                    <h6>Invoice Items</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th width="80">Qty</th>
                                    <th width="80">Delivered</th>
                                    <th width="120">Unit Price</th>
                                    <th width="80">Discount</th>
                                    <th width="120">Tax</th>
                                    <th width="120">Total</th>
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
                                        <br><span class="badge bg-{{ $item->delivery_status_badge }}">
                                            {{ ucfirst(str_replace('_', ' ', $item->delivery_status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->delivered_quantity }}</td>
                                    <td>₹{{ number_format($item->unit_price, 2) }}</td>
                                    <td>{{ $item->discount_value }}%</td>
                                    <td>{{ $item->tax ? $item->tax->display_name : 'No Tax' }}</td>
                                    <td>₹{{ number_format($item->total_amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Subtotal:</strong></td>
                                    <td><strong>₹{{ number_format($invoice->subtotal, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Tax Amount:</strong></td>
                                    <td><strong>₹{{ number_format($invoice->tax_amount, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Total Amount:</strong></td>
                                    <td><strong>₹{{ number_format($invoice->total_amount, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Paid Amount:</strong></td>
                                    <td><strong>₹{{ number_format($invoice->paid_amount, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Balance Amount:</strong></td>
                                    <td><strong class="{{ $invoice->balance_amount > 0 ? 'text-danger' : 'text-success' }}">
                                        ₹{{ number_format($invoice->balance_amount, 2) }}
                                    </strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                @if($invoice->notes)
                <div class="mt-4">
                    <h6>Notes</h6>
                    <p>{{ $invoice->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Delivery Orders -->
        @if($invoice->deliveryOrders->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Delivery Orders</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>DO Number</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->deliveryOrders as $do)
                            <tr>
                                <td>{{ $do->do_no }}</td>
                                <td>{{ $do->do_date->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $do->status_badge }}">
                                        {{ ucfirst($do->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('sales.delivery-orders.show', $do) }}" 
                                       class="btn btn-sm btn-outline-primary">
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
    </div>

    <div class="col-md-4">
        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                        @can('sales.invoices.edit')
                        <a href="{{ route('sales.invoices.edit', $invoice) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i> Edit Invoice
                        </a>
                        @endcan
                    @endif

                    @if($invoice->delivery_status !== 'delivered')
                        <a href="{{ route('sales.delivery-orders.create', ['invoice_id' => $invoice->id]) }}" 
                           class="btn btn-outline-info">
                            <i class="fas fa-truck me-2"></i> Create Delivery Order
                        </a>
                    @endif

                    @can('sales.invoices.pdf')
                    <a href="{{ route('sales.invoices.pdf', $invoice) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-file-pdf me-2"></i> Download PDF
                    </a>
                    @endcan

                    @can('sales.invoices.duplicate')
                    <form action="{{ route('sales.invoices.duplicate', $invoice) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-copy me-2"></i> Duplicate Invoice
                        </button>
                    </form>
                    @endcan

                    @if($invoice->e_invoice_status === 'not_submitted')
                        @can('sales.invoices.einvoice')
                        <form action="{{ route('sales.invoices.submit-einvoice', $invoice) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning w-100">
                                <i class="fas fa-paper-plane me-2"></i> Submit E-Invoice
                            </button>
                        </form>
                        @endcan
                    @endif

                    @if($invoice->can_be_cancelled)
                        @can('sales.invoices.cancel')
                        <button type="button" class="btn btn-outline-danger w-100" 
                                onclick="cancelInvoice({{ $invoice->id }})">
                            <i class="fas fa-ban me-2"></i> Cancel Invoice
                        </button>
                        @endcan
                    @endif

                    <a href="{{ route('sales.invoices.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Invoice Summary -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Total Amount:</strong></td>
                        <td class="text-end">₹{{ number_format($invoice->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Paid Amount:</strong></td>
                        <td class="text-end">₹{{ number_format($invoice->paid_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Balance:</strong></td>
                        <td class="text-end {{ $invoice->balance_amount > 0 ? 'text-danger' : 'text-success' }}">
                            ₹{{ number_format($invoice->balance_amount, 2) }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- E-Invoice Status -->
        @if($invoice->e_invoice_status !== 'not_submitted')
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">E-Invoice Status</h5>
            </div>
            <div class="card-body">
                <span class="badge bg-{{ $invoice->e_invoice_status_badge }} fs-6">
                    {{ ucfirst(str_replace('_', ' ', $invoice->e_invoice_status)) }}
                </span>
                @if($invoice->e_invoice_submission_date)
                    <br><small class="text-muted">
                        Submitted: {{ $invoice->e_invoice_submission_date->format('d/m/Y H:i') }}
                    </small>
                @endif
                @if($invoice->e_invoice_uuid)
                    <br><small class="text-muted">UUID: {{ $invoice->e_invoice_uuid }}</small>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection