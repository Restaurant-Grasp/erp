@extends('layouts.app')

@section('title', 'Create Lead')

@section('content')
<div class="page-header">
    <h1 class="page-title">Create New Lead</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<form action="{{ route('leads.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-md-8">
            {{-- Temple Information --}}
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Temple Information</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Temple Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Person *</label>
                            <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" class="form-control" value="{{ old('mobile') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Address Information</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
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
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control" value="India" readonly>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Temple Details --}}
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Temple Details</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Temple Category</label>
                            <select name="temple_category_id" class="form-select">
                                <option value="">Select Category</option>
                                @foreach($templeCategories as $category)
                                    <option value="{{ $category->id }}" {{ old('temple_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Temple Size</label>
                            <select name="temple_size" class="form-select">
                                <option value="">Select Size</option>
                                <option value="small" {{ old('temple_size') == 'small' ? 'selected' : '' }}>Small</option>
                                <option value="medium" {{ old('temple_size') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="large" {{ old('temple_size') == 'large' ? 'selected' : '' }}>Large</option>
                                <option value="very_large" {{ old('temple_size') == 'very_large' ? 'selected' : '' }}>Very Large</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Interested In</label>
                            <textarea name="interested_in" class="form-control" rows="2">{{ old('interested_in') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Documents --}}
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Documents</h5></div>
                <div class="card-body">
                    <label class="form-label">Upload Documents</label>
                    <input type="file" name="documents[]" class="form-control" multiple>
                    <div class="form-text">Allowed: PDF, DOC, XLS, JPG, PNG (Max: 10MB)</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            {{-- Lead Info --}}
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Lead Info</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Lead Source *</label>
                        <select name="source" class="form-select" required>
                            <option value="">Select Source</option>
                            <option value="online" {{ old('source') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="reference" {{ old('source') == 'reference' ? 'selected' : '' }}>Reference</option>
                            <option value="cold_call" {{ old('source') == 'cold_call' ? 'selected' : '' }}>Cold Call</option>
                            <option value="exhibition" {{ old('source') == 'exhibition' ? 'selected' : '' }}>Exhibition</option>
                            <option value="advertisement" {{ old('source') == 'advertisement' ? 'selected' : '' }}>Advertisement</option>
                            <option value="other" {{ old('source') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Source Details</label>
                        <input type="text" name="source_details" class="form-control" value="{{ old('source_details') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Assigned To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Select Staff</option>
                            @foreach($staff as $member)
                                <option value="{{ $member->id }}" {{ old('assigned_to') == $member->id ? 'selected' : '' }}>
                                    {{ $member->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Actions</h5></div>
                <div class="card-body">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Create Lead
                        </button>
                        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary mt-2">
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
    // Show/hide lost reason based on status
    function toggleLostReason() {
        if ($('#lead_status').val() === 'lost') {
            $('#lost_reason_div').show();
            $('#lost_reason').prop('required', true);
        } else {
            $('#lost_reason_div').hide();
            $('#lost_reason').prop('required', false);
        }
    }
    
    $('#lead_status').on('change', toggleLostReason);
    toggleLostReason(); // Initial check
});
</script>
@endsection
