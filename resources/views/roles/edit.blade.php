@extends('layouts.app')

@section('title', 'Edit Role')

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Role</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form action="{{ route('roles.update', $role) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Role Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $role->name) }}" required>
                        <div class="form-text">Use lowercase with underscores (e.g., sales_manager)</div>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description', $role->description) }}</textarea>
                        <div class="form-text">Brief description of this role's purpose</div>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Permissions</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-outline-primary me-2" id="selectAll">Select All</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">Deselect All</button>
                    </div>

                    @foreach($permissions as $module => $modulePermissions)
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <div class="form-check">
                                <input class="form-check-input module-checkbox" type="checkbox" 
                                       id="module_{{ $module }}" data-module="{{ $module }}">
                                <label class="form-check-label fw-bold text-uppercase" for="module_{{ $module }}">
                                    {{ str_replace('_', ' ', $module) }} Module
                                </label>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($modulePermissions as $permission)
                                <div class="col-md-3 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" 
                                               type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $permission->id }}" 
                                               id="permission_{{ $permission->id }}"
                                               data-module="{{ $module }}"
                                               {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                            {{ ucfirst($permission->permission) }}
                                            @if($permission->description)
                                            <i class="fas fa-info-circle text-muted small" 
                                               data-bs-toggle="tooltip" 
                                               title="{{ $permission->description }}"></i>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update Role
                        </button>
                        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Role Information</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Created:</dt>
                        <dd class="col-sm-7">{{ $role->created_at->format('d M Y') }}</dd>
                        
                        <dt class="col-sm-5">Updated:</dt>
                        <dd class="col-sm-7">{{ $role->updated_at->format('d M Y') }}</dd>
                        
                        <dt class="col-sm-5">Users:</dt>
                        <dd class="col-sm-7">{{ $role->users()->count() }} users</dd>
                        
                        <dt class="col-sm-5">Permissions:</dt>
                        <dd class="col-sm-7">{{ $role->permissions()->count() }} permissions</dd>
                    </dl>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Modify permissions carefully</li>
                        <li>Changes affect all users with this role</li>
                        <li>Consider creating a new role instead of major changes</li>
                        <li>Test changes with a test user first</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Select/Deselect all permissions
    $('#selectAll').click(function() {
        $('.permission-checkbox').prop('checked', true);
        $('.module-checkbox').prop('checked', true);
    });

    $('#deselectAll').click(function() {
        $('.permission-checkbox').prop('checked', false);
        $('.module-checkbox').prop('checked', false);
    });

    // Module checkbox logic
    $('.module-checkbox').change(function() {
        const module = $(this).data('module');
        const isChecked = $(this).is(':checked');
        $(`.permission-checkbox[data-module="${module}"]`).prop('checked', isChecked);
    });

    // Update module checkbox when individual permissions change
    $('.permission-checkbox').change(function() {
        const module = $(this).data('module');
        const totalPermissions = $(`.permission-checkbox[data-module="${module}"]`).length;
        const checkedPermissions = $(`.permission-checkbox[data-module="${module}"]:checked`).length;
        
        if (checkedPermissions === 0) {
            $(`#module_${module}`).prop('checked', false);
        } else if (checkedPermissions === totalPermissions) {
            $(`#module_${module}`).prop('checked', true);
        }
    });

    // Check module checkboxes on page load
    $('.module-checkbox').each(function() {
        const module = $(this).data('module');
        const totalPermissions = $(`.permission-checkbox[data-module="${module}"]`).length;
        const checkedPermissions = $(`.permission-checkbox[data-module="${module}"]:checked`).length;
        
        if (checkedPermissions === totalPermissions && totalPermissions > 0) {
            $(this).prop('checked', true);
        }
    });
});
</script>
@endpush