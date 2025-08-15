

{{-- resources/views/sales/payments/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Payment Details')

@section('content')
<div class="page-header">
    <h1 class="page-title">Payment Details</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sales.invoices.index') }}">Sales Invoices</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sales.invoices.show', $payment->invoice) }}">{{ $payment->invoice->invoice_no }}</a></li>
            <li class="breadcrumb-item active">Payment #{{ $payment->id }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Payment Information</h5>
                <div>
                    @if($payment->account_migration)
                        <span class="badge bg-success">Migrated</span>
                    @else
                        <span class="badge bg-warning">Pending Migration</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Invoice No:</label>
                        <div class="fw-bold">
                            <a href="{{ route('sales.invoices.show', $payment->invoice) }}" class="text-decoration-none">
                                {{ $payment->invoice->invoice_no }}
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Customer:</label>
                        <div class="fw-bold">{{ $payment->invoice->customer->company_name }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted">Payment Date:</label>
                        <div>{{ $payment->payment_date->format('d/m/Y') }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted">Amount:</label>
                        <div class="fw-bold text-success fs-5">₹{{ number_format($payment->paid_amount, 2) }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted">Payment ID:</label>
                        <div><code>#{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</code></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Payment Mode:</label>
                        <div>
                            <strong>{{ $payment->paymentMode->name }}</strong><br>
                            <small class="text-muted">Account: {{ $payment->paymentMode->ledger->name }}</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Received By:</label>
                        <div>{{ $payment->receivedBy->name }}</div>
                    </div>
                    
                    @if($payment->file_upload)
                    <div class="col-md-12">
                        <label class="form-label text-muted">Uploaded File:</label>
                        <div>
                            <a href="{{ $payment->file_url }}" target="_blank" class="btn btn-outline-info">
                                <i class="fas fa-download me-2"></i>Download File
                            </a>
                            <small class="text-muted d-block mt-1">{{ $payment->file_upload }}</small>
                        </div>
                    </div>
                    @endif
                    
                    <div class="col-md-12">
                        <label class="form-label text-muted">Notes:</label>
                        <div class="bg-light p-3 rounded">
                            {{ $payment->notes ?: 'No notes provided' }}
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label text-muted">Created By:</label>
                        <div>{{ $payment->createdBy->name }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Created At:</label>
                        <div>{{ $payment->created_at->format('d/m/Y H:i:s') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Invoice Summary -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Invoice Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td>Total Amount:</td>
                        <td class="text-end"><strong>₹{{ number_format($payment->invoice->total_amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Total Paid:</td>
                        <td class="text-end text-success"><strong>₹{{ number_format($payment->invoice->paid_amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Balance:</td>
                        <td class="text-end text-danger"><strong>₹{{ number_format($payment->invoice->balance_amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td class="text-end">
                            <span class="badge bg-{{ $payment->invoice->status_badge }}">
                                {{ ucfirst($payment->invoice->status) }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @php
                    $role = auth()->user()->getRoleNames()->first();
                    $permissions = getCurrentRolePermissions($role);
                    @endphp
                    
                    @if ($permissions->contains('name', 'sales.payments.edit'))
                    <a href="{{ route('sales.payments.edit', [$payment->invoice, $payment]) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Payment
                    </a>
                    @endif
                    
                    <a href="{{ route('sales.invoices.show', $payment->invoice) }}" class="btn btn-outline-primary">
                        <i class="fas fa-eye me-2"></i>View Invoice
                    </a>
                    
                    <a href="{{ route('sales.invoices.pdf', $payment->invoice) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-file-pdf me-2"></i>Invoice PDF
                    </a>
                    
                    @if ($permissions->contains('name', 'sales.payments.delete'))
                    <form action="{{ route('sales.payments.destroy', [$payment->invoice, $payment]) }}" method="POST" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-2"></i>Delete Payment
                        </button>
                    </form>
                    @endif
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
        
        if (confirm('Are you sure you want to delete this payment? This action cannot be undone and will affect the invoice balance.')) {
            form.submit();
        }
    });
});
</script>
@endsection