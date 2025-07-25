@extends('layouts.app')

@section('title', 'Create Permission')

@section('content')
<div class="page-header">
    <h1 class="page-title">Create New Permission</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('permissions.index') }}">Permissions</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Permission Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('permissions.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="module" class="form-label">Module <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('module') is-invalid @enderror" 
                               id="module" name="module" value="{{ old('module') }}" 
                               list="modules" required>
                        <datalist id="modules">
                            @foreach($modules as $module)
                            <option value="{{ $module }}">
                            @endforeach
                        </datalist>
                        <div class="form-text">Select from existing modules or create a new one</div>
                        @error('module')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="permission" class="form-label">Permission <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('permission') is-invalid @enderror" 
                               id="permission" name="permission" value="{{ old('permission') }}" 
                               list="permissions" required>
                        <datalist id="permissions">
                            <option value="view">
                            <option value="create">
                            <option value="edit">
                            <option value="delete">
                            <option value="export">
                            <option value="import">
                            <option value="approve">
                            <option value="process">
                            <option value="manage">
                        </datalist>
                        <div class="form-text">Common permissions: view, create, edit, delete</div>
                        @error('permission')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        <div class="form-text">Optional description of what this permission allows</div>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Permission Name Format:</strong><br>
                        The system will automatically create the permission name as: <br>
                        <code><span id="permission-preview">module.permission</span></code>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Create Permission
                        </button>
                        <a href="{{ route('permissions.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Guidelines</h5>
            </div>
            <div class="card-body">
                <h6>Module Naming:</h6>
                <ul>
                    <li>Use lowercase letters</li>
                    <li>Use underscores for spaces (e.g., <code>sales_orders</code>)</li>
                    <li>Keep it concise and descriptive</li>
                </ul>
                
                <h6>Permission Naming:</h6>
                <ul>
                    <li>Use standard actions when possible</li>
                    <li>Be specific about the action</li>
                    <li>Avoid redundancy with module name</li>
                </ul>
                
                <h6>Examples:</h6>
                <ul class="mb-0">
                    <li><code>customers.view</code> - View customer list</li>
                    <li><code>invoices.create</code> - Create new invoices</li>
                    <li><code>reports.export</code> - Export reports</li>
                    <li><code>settings.manage</code> - Manage system settings</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Update permission preview
    function updatePreview() {
        const module = $('#module').val() || 'module';
        const permission = $('#permission').val() || 'permission';
        $('#permission-preview').text(`${module}.${permission}`);
    }
    
    $('#module, #permission').on('input', updatePreview);
    
    // Initial preview
    updatePreview();
    
    // Convert module and permission to lowercase
    $('#module, #permission').on('blur', function() {
        $(this).val($(this).val().toLowerCase().replace(/\s+/g, '_'));
        updatePreview();
    });
});
</script>
@endpush