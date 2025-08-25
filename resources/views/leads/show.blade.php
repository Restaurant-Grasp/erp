@extends('layouts.app')
@section('title', 'Lead Details')

@section('content')
<div class="container py-4">

  {{-- Header --}}
  <div class="card shadow-sm mb-4">
    <div class="card-body d-flex justify-content-between align-items-center">
      <div>

        <h4 class="mb-1">{{ $lead->lead_no }} — {{ $lead->company_name ?: '-' }}</h4>
        <small class="text-muted">Created on {{ $lead->created_at->format('d M Y, H:i') }}</small>
      </div>
      <span class="badge bg-info text-uppercase">{{ ucfirst(str_replace('_',' ', $lead->lead_status)) }}</span>
    </div>
  </div>

  {{-- Lead Info --}}
  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card shadow-sm h-100">
        <div class="card-header fw-bold">Lead Information</div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-5">Contact Person</dt>
            <dd class="col-sm-7">{{ $lead->contact_person }}</dd>

            <dt class="col-sm-5">Email</dt>
            <dd class="col-sm-7">{{ $lead->email ?: '-' }}</dd>

            <dt class="col-sm-5">Phone / Mobile</dt>
            <dd class="col-sm-7">{{ $lead->phone ?: '-' }} / {{ $lead->mobile ?: '-' }}</dd>

            <dt class="col-sm-5">Lead Source</dt>
            <dd class="col-sm-7">{{ ucfirst(str_replace('_',' ', $lead->source)) }}{{ $lead->source_details ? ' — '.$lead->source_details : '' }}</dd>

            <dt class="col-sm-5">Assigned To</dt>
            <dd class="col-sm-7">{{ $lead->assignedTo->name ?? '-' }}</dd>
          </dl>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card shadow-sm h-100">
        <div class="card-header fw-bold">Additional Information</div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-5">Business Category</dt>
            <dd class="col-sm-7">{{ $lead->templeCategory->name ?? '-' }}</dd>

            <dt class="col-sm-5">Size</dt>
            <dd class="col-sm-7">{{ ucfirst(str_replace('_',' ', $lead->temple_size ?? '-')) }}</dd>

            <dt class="col-sm-5">Interested In</dt>
            <dd class="col-sm-7">{{ $lead->interested_in ?: '-' }}</dd>
          </dl>
        </div>
      </div>
    </div>
  </div>

  {{-- Notes & Address --}}
  @if($lead->address || $lead->notes)
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        @if($lead->address)
          <h6 class="fw-bold">Address</h6>
          <p class="mb-3">{{ implode(', ', array_filter([$lead->address, $lead->city, $lead->state, $lead->country])) }}</p>
        @endif

        @if($lead->notes)
          <h6 class="fw-bold">Notes</h6>
          <p class="mb-0">{{ $lead->notes }}</p>
        @endif
      </div>
    </div>
  @endif

  {{-- Activities --}}
  <div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Activities & Follow-ups</h5>
      @if(!$lead->is_converted)
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addActivityModal">+ Add Activity</button>
      @endif
    </div>
    <div class="card-body">
      @if($lead->activities->isNotEmpty())
        <ul class="list-group list-group-flush">
          @foreach($lead->activities->sortByDesc('activity_date') as $act)
            <li class="list-group-item">
              <strong>{{ $act->subject }} ({{ ucfirst($act->activity_type) }})</strong><br>
              <small class="text-muted">{{ \Carbon\Carbon::parse($act->activity_date)->format('d/m/Y H:i') }} by {{ $act->createdBy->name ?? 'System' }}</small>
              <p class="mt-1 mb-0">{{ $act->description }}</p>
            </li>
          @endforeach
        </ul>
      @else
        <p class="text-muted">No activities recorded yet.</p>
      @endif
    </div>
  </div>

  {{-- Documents --}}
  <div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Documents</h5>
      @if(!$lead->is_converted)
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">+ Upload Document</button>
      @endif
    </div>
    <div class="card-body">
      @if($lead->documents->isNotEmpty())
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th>Name</th><th>Size</th><th>Uploaded By</th><th>Uploaded At</th><th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($lead->documents as $doc)
                <tr>
                  <td>{{ $doc->document_name }}</td>
                  <td>{{ $doc->file_size_formatted }}</td>
                  <td>{{ $doc->uploadedBy->name ?? '-' }}</td>
                  <td>{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                  <td>
                    @if(!$lead->is_converted && auth()->user()->can('leads.edit'))
                       <form action="{{ route('leads.documents.delete', [$lead, $doc]) }}" 
                                          method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <p class="text-muted">No documents uploaded yet.</p>
      @endif
    </div>
  </div>

  {{-- Statistics --}}
  <div class="row mb-4">
    <div class="col-md-4"><div class="alert alert-info">Follow-ups: <strong>{{ $lead->follow_up_count }}</strong></div></div>
    <div class="col-md-4"><div class="alert alert-info">Quotations: <strong>{{ $lead->quotation_count }}</strong></div></div>
    @if($lead->total_quoted_value > 0)
      <div class="col-md-4"><div class="alert alert-success">Total Quoted: ₹ <strong>{{ number_format($lead->total_quoted_value,2) }}</strong></div></div>
    @endif
  </div>

  {{-- Quotations --}}
  @if($lead->quotations->isNotEmpty())
    <div class="card shadow-sm mb-5">
      <div class="card-header">
        <h5 class="mb-0">Related Quotations</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="table-light">
              <tr><th>No</th><th>Date</th><th>Total</th><th>Status</th></tr>
            </thead>
            <tbody>
              @foreach($lead->quotations as $q)
                <tr>
                  <td>{{ $q->quotation_no }}</td>
                  <td>{{ $q->quotation_date->format('d/m/Y') }}</td>
                  <td>₹ {{ number_format($q->total_amount,2) }}</td>
                  <td>{{ ucfirst($q->status) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  @endif

</div>

<!-- Add Activity Modal -->
<div class="modal fade" id="addActivityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('leads.activities.store', $lead) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="activity_type" class="form-label">Activity Type</label>
                        <select class="form-select" name="activity_type" required>
                            <option value="call">Call</option>
                            <option value="email">Email</option>
                            <option value="meeting">Meeting</option>
                            <option value="note">Note</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
<form action="{{ route('leads.documents.upload', $lead) }}" method="POST" enctype="multipart/form-data" id="uploadDocumentForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload Documents</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="documents" class="form-label">Select Files</label>
                        <input type="file" class="form-control" id="documents" name="documents[]" multiple required
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <div class="form-text">Allowed formats: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max: 10MB each)</div>
                    </div>
                    <div class="mb-3">
                        <label for="document_names" class="form-label">Document Names (Optional)</label>
                        <div id="documentNamesContainer">
                            <!-- Document name inputs will be added here -->
                        </div>
                    </div>
                    <div id="uploadProgress" style="display: none;">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                                 style="width: 0%"></div>
                        </div>
                        <small class="text-muted mt-2">Uploading documents...</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="uploadBtn">
                        <i class="fas fa-upload me-1"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline-item {
    position: relative;
    padding-bottom: 20px;
}
.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -21px;
    top: 20px;
    height: calc(100% - 20px);
    width: 2px;
    background-color: #e9ecef;
}
.timeline-marker {
    position: absolute;
    left: -25px;
    top: 5px;
    width: 10px;
    height: 10px;
    background-color: #007bff;
    border-radius: 50%;
}
.progress {
    margin-top: 10px;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Delete confirmation
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        
        if (confirm('Are you sure you want to delete this document?')) {
            form.submit();
        }
    });
    
    // Handle file selection for document upload
    $('#documents').on('change', function() {
        const files = this.files;
        const container = $('#documentNamesContainer');
        container.empty();
        
        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                const fileName = files[i].name;
                const nameWithoutExt = fileName.substring(0, fileName.lastIndexOf('.')) || fileName;
                
                container.append(`
                    <div class="input-group mb-2">
                        <span class="input-group-text" style="font-size: 0.875rem;">${fileName}</span>
                        <input type="text" class="form-control" name="document_names[]" 
                               placeholder="Custom name (optional)" value="${nameWithoutExt}">
                    </div>
                `);
            }
        }
    });
    
    // Handle document upload form submission
    $('#uploadDocumentForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const formData = new FormData(form);
        const progressBar = $('#uploadProgress .progress-bar');
        const uploadBtn = $('#uploadBtn');
        
        // Show progress bar
        $('#uploadProgress').show();
        uploadBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Uploading...');
        
        $.ajax({
            url: form.action,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(evt) {
                    if (evt.lengthComputable) {
                        const percentComplete = (evt.loaded / evt.total) * 100;
                        progressBar.css('width', percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                // Success - reload the page to show new documents
                if (response.success) {
                    location.reload();
                } else {
                    // If response doesn't have success flag, still reload
                    location.reload();
                }
            },
            error: function(xhr) {
                // Hide progress bar
                $('#uploadProgress').hide();
                uploadBtn.prop('disabled', false).html('<i class="fas fa-upload me-1"></i> Upload');
                progressBar.css('width', '0%');
                
                // Show error message
                let errorMessage = 'Error uploading documents. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                
                alert(errorMessage);
            }
        });
    });
    
    // Reset form when modal is closed
    $('#uploadDocumentModal').on('hidden.bs.modal', function() {
        $('#uploadDocumentForm')[0].reset();
        $('#documentNamesContainer').empty();
        $('#uploadProgress').hide();
        $('#uploadProgress .progress-bar').css('width', '0%');
        $('#uploadBtn').prop('disabled', false).html('<i class="fas fa-upload me-1"></i> Upload');
    });
});
</script>
@endpush
