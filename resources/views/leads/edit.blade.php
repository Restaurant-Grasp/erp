@extends('layouts.app')

@section('title', 'Edit Lead')

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Lead</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form action="{{ route('leads.update', $lead->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-8">
            {{-- Temple Information --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Temple Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Temple Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $lead->company_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Person *</label>
                            <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $lead->contact_person) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $lead->email) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $lead->phone) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $lead->mobile) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Address Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address', $lead->address) }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $lead->city) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state', $lead->state) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Country</label>
                            <input type="text" class="form-control" value="India" readonly>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Temple Details --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Temple Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Temple Category</label>
                            <select name="temple_category_id" class="form-select">
                                <option value="">Select Category</option>
                                @foreach($templeCategories as $category)
                                <option value="{{ $category->id }}" {{ old('temple_category_id', $lead->temple_category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Temple Size</label>
                            <select name="temple_size" class="form-select">
                                <option value="">Select Size</option>
                                <option value="small" {{ old('temple_size', $lead->temple_size) == 'small' ? 'selected' : '' }}>Small</option>
                                <option value="medium" {{ old('temple_size', $lead->temple_size) == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="large" {{ old('temple_size', $lead->temple_size) == 'large' ? 'selected' : '' }}>Large</option>
                                <option value="very_large" {{ old('temple_size', $lead->temple_size) == 'very_large' ? 'selected' : '' }}>Very Large</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Interested In</label>
                            <textarea name="interested_in" class="form-control" rows="2">{{ old('interested_in', $lead->interested_in) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Documents (Optional Section) --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Documents</h5>
                </div>
                <div class="card-body">
                    @if(isset($lead) && $lead->documents && $lead->documents->count() > 0)
                    <div class="mb-3">
                        <h6>Existing Documents</h6>
                        <div class="list-group">
                            @foreach($lead->documents as $document)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    @php
                                    $iconClass = 'fa-file';
                                    $extension = strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION));
                                    if (in_array($extension, ['pdf'])) $iconClass = 'fa-file-pdf text-danger';
                                    elseif (in_array($extension, ['doc', 'docx'])) $iconClass = 'fa-file-word text-primary';
                                    elseif (in_array($extension, ['xls', 'xlsx'])) $iconClass = 'fa-file-excel text-success';
                                    elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) $iconClass = 'fa-file-image text-info';
                                    @endphp
                                    <i class="fas {{ $iconClass }} me-2"></i>
                                    {{ $document->document_name }}
                                    <small class="text-muted">({{ $document->file_size_formatted }})</small>
                                </div>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('leads.documents.download', [$lead, $document]) }}"
                                        class="btn btn-sm btn-outline-primary" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    @can('leads.edit')
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-document"
                                        data-lead-id="{{ $lead->id }}"
                                        data-document-id="{{ $document->id }}"
                                        data-document-name="{{ $document->document_name }}"
                                        title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div>
                        <label for="documents" class="form-label">Upload Documents</label>
                        <input type="file" class="form-control @error('documents.*') is-invalid @enderror"
                            id="documents" name="documents[]" multiple
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <div class="form-text">Allowed formats: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max: 10MB each)</div>
                        @error('documents.*')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>


    <div class="col-md-4">
        {{-- Lead Info --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Lead Info</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Lead Source *</label>
                    <select name="source" class="form-select" required>
                        <option value="">Select Source</option>
                        <option value="online" {{ old('source', $lead->source) == 'online' ? 'selected' : '' }}>Online</option>
                        <option value="reference" {{ old('source', $lead->source) == 'reference' ? 'selected' : '' }}>Reference</option>
                        <option value="cold_call" {{ old('source', $lead->source) == 'cold_call' ? 'selected' : '' }}>Cold Call</option>
                        <option value="exhibition" {{ old('source', $lead->source) == 'exhibition' ? 'selected' : '' }}>Exhibition</option>
                        <option value="advertisement" {{ old('source', $lead->source) == 'advertisement' ? 'selected' : '' }}>Advertisement</option>
                        <option value="other" {{ old('source', $lead->source) == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Source Details</label>
                    <input type="text" name="source_details" class="form-control" value="{{ old('source_details', $lead->source_details) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Lead Status</label>
                    <select name="lead_status" class="form-select" id="lead_status">
                        <option value="new" {{ old('lead_status', $lead->lead_status) == 'new' ? 'selected' : '' }}>New</option>
                        <option value="contacted" {{ old('lead_status', $lead->lead_status) == 'contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="qualified" {{ old('lead_status', $lead->lead_status) == 'qualified' ? 'selected' : '' }}>Qualified</option>
                        <option value="proposal" {{ old('lead_status', $lead->lead_status) == 'proposal' ? 'selected' : '' }}>Proposal</option>
                        <option value="negotiation" {{ old('lead_status', $lead->lead_status) == 'negotiation' ? 'selected' : '' }}>Negotiation</option>
                        <option value="won" {{ old('lead_status', $lead->lead_status) == 'won' ? 'selected' : '' }}>Won</option>
                        <option value="lost" {{ old('lead_status', $lead->lead_status) == 'lost' ? 'selected' : '' }}>Lost</option>
                    </select>
                </div>

                <div class="mb-3" id="lost_reason_div" style="display: none;">
                    <label class="form-label">Lost Reason</label>
                    <textarea name="lost_reason" class="form-control" rows="2">{{ old('lost_reason', $lead->lost_reason) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Next Follow-up Date</label>
                    <input type="date" name="next_followup_date" class="form-control" value="{{ old('next_followup_date', $lead->next_followup_date) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Assigned To</label>
                    <select name="assigned_to" class="form-select">
                        <option value="">Select Staff</option>
                        @foreach($staff as $member)
                        <option value="{{ $member->id }}" {{ old('assigned_to', $lead->assigned_to) == $member->id ? 'selected' : '' }}>
                            {{ $member->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $lead->notes) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Update Lead
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
@endsection

@push('scripts')
<script>
    function toggleLostReason() {
        if ($('#lead_status').val() === 'lost') {
            $('#lost_reason_div').show();
        } else {
            $('#lost_reason_div').hide();
        }
    }

    $(document).ready(function() {
        toggleLostReason();
        $('#lead_status').on('change', toggleLostReason);
    });
</script>
@endpush