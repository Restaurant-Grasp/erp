@extends('layouts.app')

@section('title', 'Edit Payment')

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Payment</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.invoices.index') }}">Purchase Invoices</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.invoices.show', $invoice) }}">{{ $invoice->invoice_no }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchases.payments.index', $invoice) }}">Payments</a></li>
            <li class="breadcrumb-item active">Edit Payment</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Payment Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('purchases.payments.update', [$invoice, $payment]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <!-- Invoice Details -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Invoice No:</label>
                            <span class="fw-bold">{{ $invoice->invoice_no }}</span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Vendor:</label>
                            <span class="fw-bold">{{ $invoice->vendor->company_name }}</span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total Amount:</label>
                            <span class="fw-bold text-primary">{{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}</span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Current Paid:</label>
                            <span class="fw-bold text-success">{{ $invoice->currency }} {{ number_format($invoice->paid_amount, 2) }}</span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Available Balance:</label>
                            <span class="fw-bold text-info">{{ $invoice->currency }} {{ number_format($invoice->balance_amount + $payment->paid_amount, 2) }}</span>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Payment Form -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror" 
                                   value="{{ old('payment_date', $payment->payment_date->format('Y-m-d')) }}" required>
                            @error('payment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Paid Amount <span class="text-danger">*</span></label>
                            <input type="number" name="paid_amount" class="form-control @error('paid_amount') is-invalid @enderror" 
                                   step="0.01" min="0.01" max="{{ $invoice->balance_amount + $payment->paid_amount }}" 
                                   value="{{ old('paid_amount', $payment->paid_amount) }}" required>
                            @error('paid_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maximum: {{ $invoice->currency }} {{ number_format($invoice->balance_amount + $payment->paid_amount, 2) }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_mode_id" class="form-select @error('payment_mode_id') is-invalid @enderror" required>
                                <option value="">Select Payment Mode</option>
                                @foreach($paymentModes as $paymentMode)
                                <option value="{{ $paymentMode->id }}" 
                                        {{ old('payment_mode_id', $payment->payment_mode_id) == $paymentMode->id ? 'selected' : '' }}>
                                    {{ $paymentMode->name }} ({{ $paymentMode->ledger->name ?? 'No Ledger' }})
                                </option>
                                @endforeach
                            </select>
                            @error('payment_mode_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Received By <span class="text-danger">*</span></label>
                            <select name="received_by" class="form-select @error('received_by') is-invalid @enderror" required>
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" 
                                        {{ old('received_by', $payment->received_by) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('received_by')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Upload File (Optional)</label>
                            @if($payment->file_upload)
                            <div class="mb-2">
                                <small class="text-muted">Current file: 
                                    <a href="{{ $payment->file_url }}" target="_blank">{{ $payment->file_upload }}</a>
                                </small>
                            </div>
                            @endif
                            <input type="file" name="file_upload" class="form-control @error('file_upload') is-invalid @enderror" 
                                   accept=".jpeg,.png,.jpg,.gif,.pdf,.docx,.doc">
                            @error('file_upload')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Supported formats: Images, PDF, DOCX, DOC (Max: 10MB)</small>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" 
                                      placeholder="Payment notes...">{{ old('notes', $payment->notes) }}</textarea>
                        </div>
                    </div>
                    
                    <div class="text-start mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Payment
                        </button>
                        <a href="{{ route('purchases.payments.index', $invoice) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Payment Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Original Payment:</h6>
                    <p class="text-muted">{{ $invoice->currency }} {{ number_format($payment->paid_amount, 2) }}</p>
                </div>
                
                <div class="mb-3">
                    <h6>Payment Mode:</h6>
                    <p class="text-muted">{{ $payment->paymentMode->name }}</p>
                </div>
                
                <div class="mb-3">
                    <h6>Payment Date:</h6>
                    <p class="text-muted">{{ $payment->payment_date->format('d/m/Y') }}</p>
                </div>
                
                <div class="mb-3">
                    <h6>Created By:</h6>
                    <p class="text-muted">{{ $payment->createdBy->name }}</p>
                </div>
                
                <div class="mb-3">
                    <h6>Created At:</h6>
                    <p class="text-muted">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                </div>
                
                @if($payment->account_migration)
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This payment has been migrated to accounts. Changes may affect accounting records.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection