
@extends('layouts.app')

@section('title', 'Record Payment')

@section('content')
<div class="page-header">
    <h1 class="page-title">Record Sales Payment</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sales.invoices.index') }}">Sales Invoices</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sales.invoices.show', $invoice) }}">{{ $invoice->invoice_no }}</a></li>
            <li class="breadcrumb-item active">Record Payment</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Payment Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('sales.payments.store', $invoice) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Invoice Information -->
                    <div class="card mb-3">
                        <div class="card-body bg-light">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Invoice No:</strong> {{ $invoice->invoice_no }}<br>
                                    <strong>Customer:</strong> {{ $invoice->customer->company_name }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Total Amount:</strong> <span class="text-primary">₹{{ number_format($invoice->total_amount, 2) }}</span><br>
                                    <strong>Balance:</strong> <span class="text-danger">₹{{ number_format($invoice->balance_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror" 
                                   value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            @error('payment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Paid Amount <span class="text-danger">*</span></label>
                            <input type="number" name="paid_amount" class="form-control @error('paid_amount') is-invalid @enderror" 
                                   value="{{ old('paid_amount') }}" step="0.01" min="0.01" max="{{ $invoice->balance_amount }}" required>
                            @error('paid_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maximum: ₹{{ number_format($invoice->balance_amount, 2) }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_mode_id" class="form-select @error('payment_mode_id') is-invalid @enderror" required>
                                <option value="">Select Payment Mode</option>
                                @foreach($paymentModes as $mode)
                                <option value="{{ $mode->id }}" {{ old('payment_mode_id') == $mode->id ? 'selected' : '' }}>
                                    {{ $mode->name }} ({{ $mode->ledger->name }})
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
                                <option value="{{ $user->id }}" {{ old('received_by') == $user->id ? 'selected' : '' }}>
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
                                      placeholder="Payment notes...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    
                    <div class="text-start mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Record Payment
                        </button>
                        <a href="{{ route('sales.invoices.show', $invoice) }}" class="btn btn-outline-secondary">
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
                <h5 class="mb-0">Payment History</h5>
            </div>
            <div class="card-body">
                @if($invoice->payments->count() > 0)
                    @foreach($invoice->payments as $payment)
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                        <div>
                            <strong>₹{{ number_format($payment->paid_amount, 2) }}</strong><br>
                            <small class="text-muted">{{ $payment->payment_date->format('d/m/Y') }} - {{ $payment->paymentMode->name }}</small>
                        </div>
                        <div class="text-end">
                            <small class="text-muted">{{ $payment->receivedBy->name }}</small>
                        </div>
                    </div>
                    @endforeach
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total Paid:</strong>
                        <strong class="text-success">₹{{ number_format($invoice->paid_amount, 2) }}</strong>
                    </div>
                @else
                    <p class="text-muted">No payments recorded yet.</p>
                @endif
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('sales.invoices.show', $invoice) }}" class="btn btn-outline-primary">
                        <i class="fas fa-eye me-2"></i>View Invoice
                    </a>
                    <a href="{{ route('sales.invoices.pdf', $invoice) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-file-pdf me-2"></i>Download PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection