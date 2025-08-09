@extends('layouts.app')

@section('title', 'Create Vendor')

@section('content')
<div class="page-header">
    <h1 class="page-title">Create New Vendor</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('vendors.index') }}">Vendors</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<form action="{{ route('vendors.store') }}" method="POST" id="vendorForm">
    @csrf
    <div class="row">
        <div class="col-md-8">
            {{-- Company Information --}}
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Company Information</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" 
                                   value="{{ old('company_name') }}" required>
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror" 
                                   value="{{ old('contact_person') }}">
                            @error('contact_person')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror" 
                                   value="{{ old('mobile') }}">
                            @error('mobile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fax</label>
                            <input type="text" name="fax" class="form-control" value="{{ old('fax') }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Website</label>
                            <input type="url" name="website" class="form-control" value="{{ old('website') }}" placeholder="https://">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Address Information --}}
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Address Information</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" name="address_line1" class="form-control" value="{{ old('address_line1') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="address_line2" class="form-control" value="{{ old('address_line2') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Postcode</label>
                            <input type="text" name="postcode" class="form-control" value="{{ old('postcode') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control" value="{{ old('country', 'Malaysia') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Registration No</label>
                            <input type="text" name="registration_no" class="form-control" value="{{ old('registration_no') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tax No (GST)</label>
                            <input type="text" name="tax_no" class="form-control" value="{{ old('tax_no') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Banking Information --}}
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Banking Information</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bank Account No</label>
                            <input type="text" name="bank_account_no" class="form-control" value="{{ old('bank_account_no') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            {{-- Business Information --}}
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Business Information</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Service Types</label>
                            @foreach($serviceTypes as $serviceType)
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="service_types[]" 
                                       value="{{ $serviceType->id }}" id="service_{{ $serviceType->id }}"
                                       {{ in_array($serviceType->id, old('service_types', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="service_{{ $serviceType->id }}">
                                    {{ $serviceType->name }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Terms (Days)</label>
                        <input type="number" name="payment_terms" class="form-control" 
                               value="{{ old('payment_terms', 30) }}" min="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Credit Limit (₹)</label>
                        <input type="number" name="credit_limit" class="form-control" 
                               value="{{ old('credit_limit', 0) }}" min="0" step="0.01">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Actions</h5></div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        A ledger account will be automatically created for this vendor under Trade Creditors.
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Create Vendor
                        </button>
                        <a href="{{ route('vendors.index') }}" class="btn btn-outline-secondary mt-2">
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
    $('#vendorForm').on('submit', function(e) {
        const companyName = $('input[name="company_name"]').val();
        
        if (!companyName) {
            e.preventDefault();
            alert('Please fill in the company name.');
            return false;
        }
    });
});
</script>
@endsection