@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Customer</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customers.show', $customer) }}">{{ $customer->customer_code }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form action="{{ route('customers.update', $customer) }}" method="POST" id="customerForm">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-8">
            {{-- Company Information --}}
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Company Information</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Customer Code</label>
                            <input type="text" class="form-control" value="{{ $customer->customer_code }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" 
                                   value="{{ old('company_name', $customer->company_name) }}" required>
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror" 
                                   value="{{ old('contact_person', $customer->contact_person) }}">
                            @error('contact_person')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $customer->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone', $customer->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror" 
                                   value="{{ old('mobile', $customer->mobile) }}">
                            @error('mobile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fax</label>
                            <input type="text" name="fax" class="form-control" value="{{ old('fax', $customer->fax) }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Website</label>
                            <input type="url" name="website" class="form-control" value="{{ old('website', $customer->website) }}" placeholder="https://">
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
                            <input type="text" name="address_line1" class="form-control" value="{{ old('address_line1', $customer->address_line1) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="address_line2" class="form-control" value="{{ old('address_line2', $customer->address_line2) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $customer->city) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state', $customer->state) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Postcode</label>
                            <input type="text" name="postcode" class="form-control" value="{{ old('postcode', $customer->postcode) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control" value="{{ old('country', $customer->country) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Registration No</label>
                            <input type="text" name="registration_no" class="form-control" value="{{ old('registration_no', $customer->registration_no) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tax No (GST)</label>
                            <input type="text" name="tax_no" class="form-control" value="{{ old('tax_no', $customer->tax_no) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Service Types --}}
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Service Types</h5></div>
                <div class="card-body">
                    <div class="row">
                        @foreach($serviceTypes as $serviceType)
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="service_types[]" 
                                       value="{{ $serviceType->id }}" id="service_{{ $serviceType->id }}"
                                       {{ in_array($serviceType->id, old('service_types', $selectedServiceTypes)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="service_{{ $serviceType->id }}">
                                    {{ $serviceType->name }}
                                </label>
                            </div>
                        </div>
                        @endforeach
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
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $customer->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Credit Limit (₹)</label>
                        <input type="number" name="credit_limit" class="form-control" 
                               value="{{ old('credit_limit', $customer->credit_limit) }}" min="0" step="0.01">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Credit Days</label>
                        <input type="number" name="credit_days" class="form-control" 
                               value="{{ old('credit_days', $customer->credit_days) }}" min="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Discount (%)</label>
                        <input type="number" name="discount_percentage" class="form-control" 
                               value="{{ old('discount_percentage', $customer->discount_percentage) }}" min="0" max="100" step="0.01">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="active" {{ old('status', $customer->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $customer->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="blocked" {{ old('status', $customer->status) == 'blocked' ? 'selected' : '' }}>Blocked</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Source Information --}}
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Source Information</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Source <span class="text-danger">*</span></label>
                        <select name="source" class="form-select" required>
                            <option value="">Select Source</option>
                            <option value="online" {{ old('source', $customer->source) == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="reference" {{ old('source', $customer->source) == 'reference' ? 'selected' : '' }}>Reference</option>
                            <option value="direct" {{ old('source', $customer->source) == 'direct' ? 'selected' : '' }}>Direct</option>
                            <option value="other" {{ old('source', $customer->source) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reference By</label>
                        <input type="text" name="reference_by" class="form-control" value="{{ old('reference_by', $customer->reference_by) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Assigned To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Select Staff</option>
                            @foreach($staff as $member)
                                <option value="{{ $member->id }}" {{ old('assigned_to', $customer->assigned_to) == $member->id ? 'selected' : '' }}>
                                    {{ $member->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $customer->notes) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Actions</h5></div>
                <div class="card-body">
                    @if($customer->ledger)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Ledger Account: <strong>{{ $customer->ledger->name }}</strong>
                    </div>
                    @endif
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update Customer
                        </button>
                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary mt-2">
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
    $('#customerForm').on('submit', function(e) {
        const companyName = $('input[name="company_name"]').val();
        const source = $('select[name="source"]').val();
        const status = $('select[name="status"]').val();
        
        if (!companyName || !source || !status) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }
    });
});
</script>
@endsection