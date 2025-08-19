@extends('layouts.app')

@section('title', 'Add Payment')

@section('content')
<div class="page-header">
    <h1 class="page-title">Add Payment</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.invoices.index') }}">Purchase Invoices</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.invoices.show', $invoice) }}">{{ $invoice->invoice_no }}</a></li>
            <li class="breadcrumb-item active">Add Payment</li>
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
                <form action="{{ route('purchases.payments.store', $invoice) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
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
                            <label class="form-label">Paid Amount:</label>
                            <span class="fw-bold text-success">{{ $invoice->currency }} {{ number_format($invoice->paid_amount, 2) }}</span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Balance Amount:</label>
                            <span class="fw-bold text-danger">{{ $invoice->currency }} {{ number_format($invoice->balance_amount, 2) }}</span>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Payment Form -->
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
                                   step="0.01" min="0.01" max="{{ $invoice->balance_amount }}" 
                                   value="{{ old('paid_amount') }}" required>
                            @error('paid_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maximum: {{ $invoice->currency }} {{ number_format($invoice->balance_amount, 2) }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_mode_id" class="form-select @error('payment_mode_id') is-invalid @enderror" required>
                                <option value="">Select Payment Mode</option>
                                @foreach($paymentModes as $paymentMode)
                                <option value="{{ $paymentMode->id }}" {{ old('payment_mode_id') == $paymentMode->id ? 'selected' : '' }}>
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
                        <a href="{{ route('purchase.invoices.show', $invoice) }}" class="btn btn-outline-secondary">
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
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Recording a payment will automatically update the invoice status and balance.
                </div>
                
                <div class="mb-3">
                    <h6>Invoice Status:</h6>
                    <span class="badge bg-{{ $invoice->status_badge }} fs-6">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>
                
                @if($invoice->due_date)
                <div class="mb-3">
                    <h6>Due Date:</h6>
                    <span class="{{ $invoice->is_overdue ? 'text-danger' : 'text-muted' }}">
                        {{ $invoice->due_date->format('d/m/Y') }}
                        @if($invoice->is_overdue)
                        <br><small>({{ $invoice->due_date->diffForHumans() }})</small>
                        @endif
                    </span>
                </div>
                @endif
                
                @if($invoice->payments()->count() > 0)
                <div class="mb-3">
                    <h6>Previous Payments:</h6>
                    <p class="text-success">{{ $invoice->payments()->count() }} payment(s) recorded</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

