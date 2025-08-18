@extends('layouts.app')
@section('title', 'Create Service')
@section('content')
<div class="card">
    <div class="card-header">
        <h5>Create Service</h5>
    </div>
    <div class="card-body">
      

        <form method="POST" action="{{ route('service.store') }}">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                               value="{{ old('code') }}" placeholder="e.g., SRV001">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Leave blank for auto-generation</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Service Type <span class="text-danger">*</span></label>
                        <select name="service_type_id" class="form-select @error('service_type_id') is-invalid @enderror">
                            <option value="">-- Select Service Type --</option>
                            @foreach($serviceTypes as $type)
                            <option value="{{ $type->id }}" {{ old('service_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('service_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Revenue Ledger <span class="text-danger">*</span></label>
                        <select name="ledger_id" class="form-select @error('ledger_id') is-invalid @enderror">
                            <option value="">-- Select Revenue Ledger --</option>
                            @foreach($ledgers as $ledger)
                            <option value="{{ $ledger->id }}" {{ old('ledger_id') == $ledger->id ? 'selected' : '' }}>
                                {{ $ledger->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('ledger_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Select the ledger account for revenue posting</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Item Type <span class="text-danger">*</span></label>
                        <select name="item_type" class="form-select @error('item_type') is-invalid @enderror">
                            <option value="">-- Select Item Type --</option>
                            <option value="service" {{ old('item_type') == 'service' ? 'selected' : '' }}>Service</option>
                            <option value="product" {{ old('item_type') == 'product' ? 'selected' : '' }}>Product</option>
                        </select>
                        @error('item_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Base Price <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" name="base_price" class="form-control @error('base_price') is-invalid @enderror" 
                                   value="{{ old('base_price') }}" step="0.01" min="0">
                        </div>
                        @error('base_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Billing Cycle <span class="text-danger">*</span></label>
                        <select name="billing_cycle" class="form-select @error('billing_cycle') is-invalid @enderror">
                            <option value="">-- Select Billing Cycle --</option>
                            <option value="one-time" {{ old('billing_cycle') == 'one-time' ? 'selected' : '' }}>One Time</option>
                            <option value="monthly" {{ old('billing_cycle') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ old('billing_cycle') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="yearly" {{ old('billing_cycle') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                        </select>
                        @error('billing_cycle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="is_recurring" id="is_recurring" 
                                   {{ old('is_recurring') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_recurring">
                                Recurring Service
                            </label>
                        </div>
                        <small class="form-text text-muted">Check if this service is recurring</small>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                          rows="3" placeholder="Enter service description">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="status" id="status" checked>
                    <label class="form-check-label" for="status">
                        Active Status
                    </label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Create Service
                </button>
                <a href="{{ route('service.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-enable recurring when certain billing cycles are selected
    $('select[name="billing_cycle"]').change(function() {
        const value = $(this).val();
        const recurringCheckbox = $('#is_recurring');
        
        if (value === 'monthly' || value === 'quarterly' || value === 'yearly') {
            recurringCheckbox.prop('checked', true);
        } else if (value === 'one-time') {
            recurringCheckbox.prop('checked', false);
        }
    });
});
</script>
@endpush
@endsection