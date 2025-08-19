@extends('layouts.app')

@section('title', 'Payment Details')

@section('content')
<div class="page-header">
    <h1 class="page-title">Payment Details</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.invoices.index') }}">Purchase Invoices</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.invoices.show', $invoice) }}">{{ $invoice->invoice_no }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchases.payments.index', $invoice) }}">Payments</a></li>
            <li class="breadcrumb-item active">Payment Details</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Payment Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Invoice No:</label>
                        <p>{{ $invoice->invoice_no }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Vendor:</label>
                        <p>{{ $invoice->vendor->company_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Payment Date:</label>
                        <p>{{ $payment->payment_date->format('d/m/Y') }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Paid Amount:</label>
                        <p class="text-success fs-5 fw-bold">{{ $invoice->currency }} {{ number_format($payment->paid_amount, 2) }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Payment Mode:</label>
                        <p>
                            {{ $payment->paymentMode->name }}
                            <br><small class="text-muted">Account: {{ $payment->paymentMode->ledger->name ?? 'No Ledger' }}</small>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Received By:</label>
                        <p>{{ $payment->receivedBy->name }}</p>
                    </div>
                    @if($payment->notes)
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Notes:</label>
                        <p class="border p-3 bg-light">{{ $payment->notes }}</p>
                    </div>
                    @endif
                    @if($payment->file_upload)
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Uploaded File:</label>
                        <p>
                            <a href="{{ $payment->file_url }}" target="_blank" class="btn btn-outline-primary">
                                <i class="fas fa-download me-2"></i>Download {{ $payment->file_upload }}
                            </a>
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Invoice Summary</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Total Amount:</h6>
                    <p class="text-primary fs-6 fw-bold">{{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}</p>
                </div>
                
                <div class="mb-3">
                    <h6>Total Paid:</h6>
                    <p class="text-success fs-6 fw-bold">{{ $invoice->currency }} {{ number_format($invoice->paid_amount, 2) }}</p>
                </div>
                
                <div class="mb-3">
                    <h6>Balance Amount:</h6>
                    <p class="text-danger fs-6 fw-bold">{{ $invoice->currency }} {{ number_format($invoice->balance_amount, 2) }}</p>
                </div>
                
                <div class="mb-3">
                    <h6>Invoice Status:</h6>
                    <span class="badge bg-{{ $invoice->status_badge }} fs-6">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <h6>Created By:</h6>
                    <p class="text-muted">{{ $payment->createdBy->name }}</p>
                </div>
                
                <div class="mb-3">
                    <h6>Created At:</h6>
                    <p class="text-muted">{{ $payment->created_at->format('d/m/Y H:i:s') }}</p>
                </div>
                
                @if($payment->updated_at != $payment->created_at)
                <div class="mb-3">
                    <h6>Last Updated:</h6>
                    <p class="text-muted">{{ $payment->updated_at->format('d/m/Y H:i:s') }}</p>
                </div>
                @endif
                
                <div class="mb-3">
                    <h6>Account Migration:</h6>
                    <span class="badge bg-{{ $payment->account_migration ? 'success' : 'warning' }}">
                        {{ $payment->account_migration ? 'Migrated' : 'Pending' }}
                    </span>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                @php
                $role = auth()->user()->getRoleNames()->first();
                $permissions = getCurrentRolePermissions($role);
                @endphp
                
                @if ($permissions->contains('name', 'purchases.payments.edit'))
                <a href="{{ route('purchases.payments.edit', [$invoice, $payment]) }}" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-edit me-2"></i>Edit Payment
                </a>
                @endif
                
                <a href="{{ route('purchases.payments.index', $invoice) }}" class="btn btn-outline-secondary w-100 mb-2">
                    <i class="fas fa-list me-2"></i>All Payments
                </a>
                
                <a href="{{ route('purchase.invoices.show', $invoice) }}" class="btn btn-outline-info w-100">
                    <i class="fas fa-file-invoice me-2"></i>View Invoice
                </a>
            </div>
        </div>
    </div>
</div>
@endsection