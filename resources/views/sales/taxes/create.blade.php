@extends('layouts.app')

@section('title', 'Create Tax')

@section('content')
<div class="page-header">
    <h1 class="page-title">Create New Tax</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sales.taxes.index') }}">Taxes</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<form action="{{ route('sales.taxes.store') }}" method="POST" id="taxForm">
    @csrf
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tax Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tax Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" required placeholder="e.g., GST, VAT, Service Tax">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tax Rate (%) <span class="text-danger">*</span></label>
                            <input type="number" name="percent" class="form-control @error('percent') is-invalid @enderror" 
                                   value="{{ old('percent') }}" required min="0" max="100" step="0.01">
                            @error('percent')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Applicable For <span class="text-danger">*</span></label>
                            <select name="applicable_for" class="form-select @error('applicable_for') is-invalid @enderror" required>
                                <option value="">Select Type</option>
                                <option value="product" {{ old('applicable_for') == 'product' ? 'selected' : '' }}>Product Only</option>
                                <option value="service" {{ old('applicable_for') == 'service' ? 'selected' : '' }}>Service Only</option>
                                <option value="both" {{ old('applicable_for') == 'both' ? 'selected' : '' }}>Both Product & Service</option>
                            </select>
                            @error('applicable_for')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tax Liability Account <span class="text-danger">*</span></label>
                            <select name="ledger_id" class="form-select @error('ledger_id') is-invalid @enderror" required>
                                <option value="">Select Ledger Account</option>
                              
                                @foreach($ledgers as $ledger)
                                <option value="{{ $ledger->id }}" {{ old('ledger_id') == $ledger->id ? 'selected' : '' }}>
                                    {{ $ledger->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('ledger_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                This ledger will be credited when tax is collected from customers.
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Tax Calculation Example</h6>
                            <div class="border rounded p-3 bg-light">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Exclusive Tax Calculation (Default):</strong><br>
                                        Product Price: ₹1000<br>
                                        Tax (18%): ₹180<br>
                                        <strong>Total: ₹1180</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            Tax is calculated separately from the base amount. 
                                            Customer pays the tax amount in addition to the product/service price.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <div class="form-text">
                            Only active taxes will be available for selection in quotations and invoices.
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Tax calculations are always exclusive by default. The tax amount will be calculated separately from the base amount.
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Create Tax
                        </button>
                        <a href="{{ route('sales.taxes.index') }}" class="btn btn-outline-secondary mt-2">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
$(document).ready(function() {
    // Form validation
    $('#taxForm').on('submit', function(e) {
        const name = $('input[name="name"]').val();
        const percent = $('input[name="percent"]').val();
        const applicableFor = $('select[name="applicable_for"]').val();
        const ledgerId = $('select[name="ledger_id"]').val();
        
        if (!name || !percent || !applicableFor || !ledgerId) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }

        if (parseFloat(percent) < 0 || parseFloat(percent) > 100) {
            e.preventDefault();
            alert('Tax rate must be between 0 and 100.');
            return false;
        }
    });

    // Tax rate preview
    $('input[name="percent"]').on('input', function() {
        const rate = parseFloat($(this).val()) || 0;
        updateTaxExample(rate);
    });

    function updateTaxExample(rate) {
        const baseAmount = 1000;
        const taxAmount = (baseAmount * rate) / 100;
        const total = baseAmount + taxAmount;

        $('.bg-light').html(`
            <div class="row">
                <div class="col-md-6">
                    <strong>Exclusive Tax Calculation (Default):</strong><br>
                    Product Price: ₹${baseAmount.toFixed(2)}<br>
                    Tax (${rate}%): ₹${taxAmount.toFixed(2)}<br>
                    <strong>Total: ₹${total.toFixed(2)}</strong>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">
                        Tax is calculated separately from the base amount. 
                        Customer pays the tax amount in addition to the product/service price.
                    </small>
                </div>
            </div>
        `);
    }
});
</script>
@endsection