
@extends('layouts.app')

@section('title', 'Edit Payment Mode')

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Payment Mode</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('payment-modes.index') }}">Payment Modes</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Payment Mode Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('payment-modes.update', $paymentMode) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $paymentMode->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ledger Account <span class="text-danger">*</span></label>
                            <select name="ledger_id" class="form-select @error('ledger_id') is-invalid @enderror" required>
                                <option value="">Select Ledger Account</option>
                                @foreach($ledgers as $ledger)
                                <option value="{{ $ledger->id }}" 
                                        {{ old('ledger_id', $paymentMode->ledger_id) == $ledger->id ? 'selected' : '' }}>
                                    {{ $ledger->name }} ({{ $ledger->code ?? 'No Code' }})
                                </option>
                                @endforeach
                            </select>
                            @error('ledger_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Only Bank/Cash accounts (Type 1) are shown</small>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" 
                                      placeholder="Payment mode description...">{{ old('description', $paymentMode->description) }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input type="checkbox" name="status" class="form-check-input" value="1" 
                                       {{ old('status', $paymentMode->status) ? 'checked' : '' }}>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-start mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Payment Mode
                        </button>
                        <a href="{{ route('payment-modes.index') }}" class="btn btn-outline-secondary">
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
                <h5 class="mb-0">Usage Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-primary">{{ $paymentMode->salesPayments()->count() }}</h4>
                            <small class="text-muted">Sales Payments</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-success">{{ $paymentMode->purchasePayments()->count() }}</h4>
                            <small class="text-muted">Purchase Payments</small>
                        </div>
                    </div>
                </div>
                
                @if($paymentMode->salesPayments()->count() > 0 || $paymentMode->purchasePayments()->count() > 0)
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This payment mode is being used in payments. Changes should be made carefully.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection