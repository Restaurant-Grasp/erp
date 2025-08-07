@extends('layouts.app')

@section('title', 'Follow-up Templates')

@section('content')
<div class="page-header">
    <h1 class="page-title">Follow-up Templates</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('followups.index') }}">Follow-ups</a></li>
            <li class="breadcrumb-item active">Templates</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-4">
        <!-- Add/Edit Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0" id="form-title">Add New Template</h5>
            </div>
            <div class="card-body">
                <form id="templateForm" action="{{ route('followup-templates.store') }}" method="POST">
                    @csrf
                    <div id="methodField"></div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Template Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" required value="{{ old('name') }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select @error('category') is-invalid @enderror" 
                                id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="initial_contact" {{ old('category') == 'initial_contact' ? 'selected' : '' }}>Initial Contact</option>
                            <option value="demo_followup" {{ old('category') == 'demo_followup' ? 'selected' : '' }}>Demo Follow-up</option>
                            <option value="proposal_followup" {{ old('category') == 'proposal_followup' ? 'selected' : '' }}>Proposal Follow-up</option>
                            <option value="general" {{ old('category') == 'general' ? 'selected' : '' }}>General</option>
                            <option value="closing" {{ old('category') == 'closing' ? 'selected' : '' }}>Closing</option>
                        </select>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="follow_up_type" class="form-label">Follow-up Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('follow_up_type') is-invalid @enderror" 
                                id="follow_up_type" name="follow_up_type" required>
                            <option value="">Select Type</option>
                            <option value="phone_call" {{ old('follow_up_type') == 'phone_call' ? 'selected' : '' }}>Phone Call</option>
                            <option value="email" {{ old('follow_up_type') == 'email' ? 'selected' : '' }}>Email</option>
                            <option value="whatsapp" {{ old('follow_up_type') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                            <option value="in_person_meeting" {{ old('follow_up_type') == 'in_person_meeting' ? 'selected' : '' }}>In-Person Meeting</option>
                            <option value="video_call" {{ old('follow_up_type') == 'video_call' ? 'selected' : '' }}>Video Call</option>
                            <option value="other" {{ old('follow_up_type') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('follow_up_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject (Optional)</label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                               id="subject" name="subject" value="{{ old('subject') }}"
                               placeholder="Default subject line">
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Template Content <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('content') is-invalid @enderror" 
                                  id="content" name="content" rows="6" required>{{ old('content') }}</textarea>
                        <small class="form-text text-muted">
                            Available variables: {{contact_person}}, {{staff_name}}, {{company_name}}
                        </small>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3" id="statusField" style="display: none;">
                        <label for="is_active" class="form-label">Status</label>
                        <select class="form-select @error('is_active') is-invalid @enderror" 
                                id="is_active" name="is_active">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        @error('is_active')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save me-2"></i> Save Template
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="cancelBtn" style="display: none;">
                            <i class="fas fa-times me-2"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Templates List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Templates</h5>
            </div>
            <div class="card-body">
                @foreach($templates->groupBy('category') as $category => $categoryTemplates)
                <h6 class="text-uppercase text-muted mb-3">
                    {{ str_replace('_', ' ', $category) }}
                </h6>
                <div class="accordion mb-4" id="accordion{{ ucfirst($category) }}">
                    @foreach($categoryTemplates as $template)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading{{ $template->id }}">
                            <button class="accordion-button collapsed" type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapse{{ $template->id }}">
                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                    <div>
                                        <strong>{{ $template->name }}</strong>
                                        <span class="badge bg-info ms-2">
                                            {{ ucwords(str_replace('_', ' ', $template->follow_up_type)) }}
                                        </span>
                                        @if(!$template->is_active)
                                        <span class="badge bg-danger ms-1">Inactive</span>
                                        @endif
                                    </div>
                                    <div class="btn-group" role="group" onclick="event.stopPropagation();">
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-btn" 
                                                data-id="{{ $template->id }}"
                                                data-name="{{ $template->name }}"
                                                data-category="{{ $template->category }}"
                                                data-type="{{ $template->follow_up_type }}"
                                                data-subject="{{ $template->subject }}"
                                                data-content="{{ $template->content }}"
                                                data-active="{{ $template->is_active }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if($template->followUps->count() == 0)
                                        <form action="{{ route('followup-templates.destroy', $template) }}" 
                                              method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse{{ $template->id }}" 
                             class="accordion-collapse collapse" 
                             data-bs-parent="#accordion{{ ucfirst($category) }}">
                            <div class="accordion-body">
                                @if($template->subject)
                                <p><strong>Subject:</strong> {{ $template->subject }}</p>
                                @endif
                                <p><strong>Content:</strong></p>
                                <div class="bg-light p-3 rounded">
                                    {!! nl2br(e($template->content)) !!}
                                </div>
                                <small class="text-muted">
                                    Used in {{ $template->followUps->count() }} follow-ups
                                </small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endforeach

                @if($templates->count() == 0)
                <div class="text-center py-4">
                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No templates found. Create your first template!</p>
                </div>
                @endif

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $templates->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    // Edit button click
    $('.edit-btn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const id = $(this).data('id');
        const name = $(this).data('name');
        const category = $(this).data('category');
        const type = $(this).data('type');
        const subject = $(this).data('subject');
        const content = $(this).data('content');
        const isActive = $(this).data('active');
        
        // Update form
        $('#form-title').text('Edit Template');
        $('#templateForm').attr('action', '/followup-templates/' + id);
        $('#methodField').html('<input type="hidden" name="_method" value="PUT">');
        $('#name').val(name);
        $('#category').val(category);
        $('#follow_up_type').val(type);
        $('#subject').val(subject);
        $('#content').val(content);
        $('#is_active').val(isActive);
        $('#statusField').show();
        $('#submitBtn').html('<i class="fas fa-save me-2"></i> Update Template');
        $('#cancelBtn').show();
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: $('#templateForm').offset().top - 100
        }, 500);
    });
    
    // Cancel button click
    $('#cancelBtn').on('click', function() {
        $('#form-title').text('Add New Template');
        $('#templateForm').attr('action', '{{ route('followup-templates.store') }}');
        $('#methodField').html('');
        $('#templateForm')[0].reset();
        $('#statusField').hide();
        $('#submitBtn').html('<i class="fas fa-save me-2"></i> Save Template');
        $('#cancelBtn').hide();
    });
    
    // Delete confirmation
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const form = this;
        
        if (confirm('Are you sure you want to delete this template?')) {
            form.submit();
        }
    });
    
    // Prevent accordion toggle when clicking buttons
    $('.btn-group').on('click', function(e) {
        e.stopPropagation();
    });
});
</script>
@endsection

