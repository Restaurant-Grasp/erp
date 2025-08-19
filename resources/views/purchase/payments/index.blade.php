
@extends('layouts.app')

@section('title', 'Payment History')

@section('content')
<div class="page-header">
    <h1 class="page-title">Payment History</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.invoices.index') }}">Purchase Invoices</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.invoices.show', $invoice) }}">{{ $invoice->invoice_no }}</a></li>
            <li class="breadcrumb-item active">Payments</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- Invoice Summary -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <h6>Invoice No:</h6>
                        <p class="fw-bold">{{ $invoice->invoice_no }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6>Vendor:</h6>
                        <p class="fw-bold">{{ $invoice->vendor->company_name }}</p>
                    </div>
                    <div class="col-md-2">
                        <h6>Total Amount:</h6>
                        <p class="fw-bold text-primary">{{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}</p>
                    </div>
                    <div class="col-md-2">
                        <h6>Paid Amount:</h6>
                        <p class="fw-bold text-success">{{ $invoice->currency }} {{ number_format($invoice->paid_amount, 2) }}</p>
                    </div>
                    <div class="col-md-2">
                        <h6>Balance:</h6>
                        <p class="fw-bold text-danger">{{ $invoice->currency }} {{ number_format($invoice->balance_amount, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payments List -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Payments</h5>
                @php
                $role = auth()->user()->getRoleNames()->first();
                $permissions = getCurrentRolePermissions($role);
                @endphp
                @if($invoice->balance_amount > 0 && in_array($invoice->status, ['pending', 'partial', 'overdue']) && $permissions->contains('name', 'purchases.payments.create'))
                <a href="{{ route('purchases.payments.create', $invoice) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add Payment
                </a>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Payment Date</th>
                                <th>Amount</th>
                                <th>Payment Mode</th>
                                <th>Received By</th>
                                <th>Notes</th>
                                <th>File</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                <td>
                                    <span class="fw-bold text-success">
                                        {{ $invoice->currency }} {{ number_format($payment->paid_amount, 2) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $payment->paymentMode->name }}
                                    <br><small class="text-muted">{{ $payment->paymentMode->ledger->name ?? 'No Ledger' }}</small>
                                </td>
                                <td>{{ $payment->receivedBy->name }}</td>
                                <td>{{ $payment->notes ?? '-' }}</td>
                                <td>
                                    @if($payment->file_upload)
                                    <a href="{{ $payment->file_url }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    @else
                                    -
                                    @endif
                                </td>
                                <td>{{ $payment->createdBy->name }}</td>
                                <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @if ($permissions->contains('name', 'purchases.payments.edit'))
                                        <a href="{{ route('purchases.payments.edit', [$invoice, $payment]) }}" 
                                           class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                        
                                        @if ($permissions->contains('name', 'purchases.payments.delete'))
                                        <form action="{{ route('purchases.payments.destroy', [$invoice, $payment]) }}" method="POST" 
                                              class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-credit-card fa-3x mb-3"></i>
                                        <p>No payments recorded for this invoice</p>
                                        @if($invoice->balance_amount > 0 && in_array($invoice->status, ['pending', 'partial', 'overdue']) && $permissions->contains('name', 'purchases.payments.create'))
                                        <a href="{{ route('purchases.payments.create', $invoice) }}" class="btn btn-primary">
                                            <i class="fas fa-plus me-2"></i>Add First Payment
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        
        if (confirm('Are you sure you want to delete this payment? This action cannot be undone.')) {
            form.submit();
        }
    });
});
</script>
@endsection