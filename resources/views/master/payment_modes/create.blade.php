@extends('layouts.app')

@section('title', 'Create Payment Mode')

@section('content')
<div class="page-header">
    <h1 class="page-title">Create Payment Mode</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('payment-modes.index') }}">Payment Modes</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Payment Mode Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('payment-modes.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ledger Account <span class="text-danger">*</span></label>
                            <select name="ledger_id" class="form-select @error('ledger_id') is-invalid @enderror">
                                <option value="">Select Ledger Account</option>
                                @foreach($ledgers as $ledger)
                                <option value="{{ $ledger->id }}" {{ old('ledger_id') == $ledger->id ? 'selected' : '' }}>
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
                                      placeholder="Payment mode description...">{{ old('description') }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input type="checkbox" name="status" class="form-check-input" value="1" 
                                       {{ old('status', true) ? 'checked' : '' }}>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-start mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Payment Mode
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
                <h5 class="mb-0">Information</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Payment modes are linked to ledger accounts for proper accounting integration.
                </div>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i>Cash payments</li>
                    <li><i class="fas fa-check text-success me-2"></i>Bank transfers</li>
                    <li><i class="fas fa-check text-success me-2"></i>Credit card payments</li>
                    <li><i class="fas fa-check text-success me-2"></i>Online payments</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection