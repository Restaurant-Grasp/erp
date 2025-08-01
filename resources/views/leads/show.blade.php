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
            <dt class="col-sm-5">Temple Category</dt>
            <dd class="col-sm-7">{{ $lead->templeCategory->name ?? '-' }}</dd>

            <dt class="col-sm-5">Temple Size</dt>
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
                      <form action="{{ route('leads.document.delete', $doc->id) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
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
@endsection
