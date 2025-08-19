
@extends('layouts.app')

@section('title', 'Edit Payment Mode')

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
/* Custom Select2 styling to match your theme */
.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    padding: 6px 12px;
    display: flex;
    align-items: center;
}
.select2-selection__placeholder {
    color: black !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 24px;
    padding-left: 0;
    color: #495057;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
    right: 10px;
}

.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 0.2rem rgba(0, 165, 81, 0.25);
}

.select2-dropdown {
    border: 1px solid var(--primary-green);
    border-radius: 0.375rem;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: var(--primary-green);
}

.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    padding: 6px 12px;
}

.select2-container--default .select2-search--dropdown .select2-search__field:focus {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 0.2rem rgba(0, 165, 81, 0.25);
}

/* Error state styling */
.is-invalid + .select2-container--default .select2-selection--single {
    border-color: #dc3545;
}

.is-invalid + .select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}
</style>
@endpush
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
                                   value="{{ old('name', $paymentMode->name) }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-2">Ledger Account <span class="text-danger">*</span></label>
                            <select name="ledger_id" class="form-select ledger-select2 @error('ledger_id') is-invalid @enderror">
                            <option value="">-- Search and Select Ledger Account--</option>

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

@push('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 for all dropdowns
    $('.ledger-select2').select2({
        placeholder: '-- Search and Select Ledger Account --',
        allowClear: true,
        width: '100%',
        theme: 'default'
    });
    
});
</script>
@endpush