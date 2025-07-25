@extends('layouts.app')

@section('title', 'Roles Management')

@section('content')
<div class="page-header">
    <h1 class="page-title">Roles Management</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Roles</li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Roles</h5>
        @can('roles.create')
        <a href="{{ route('roles.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Add New Role
        </a>
        @endcan
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th>Role Name</th>
                        <th>Description</th>
                        <th width="100">Users</th>
                        <th width="120">Permissions</th>
                        <th width="100">Type</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                    <tr>
                        <td>{{ $role->id }}</td>
                        <td>
                            <strong>{{ ucwords(str_replace('_', ' ', $role->name)) }}</strong>
                        </td>
                        <td>{{ $role->description ?? '-' }}</td>
                        <td>
                            <span class="badge bg-info">{{ $role->users_count }} users</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $role->permissions_count }} permissions</span>
                        </td>
                        <td>
                            @if(in_array($role->name, ['super_admin', 'admin']))
                                <span class="badge bg-warning text-dark">System</span>
                            @else
                                <span class="badge bg-success">Custom</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                @can('roles.view')
                                <a href="{{ route('roles.show', $role) }}" class="btn btn-sm btn-outline-primary" 
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                
                                @can('roles.edit')
                                @if(!in_array($role->name, ['super_admin', 'admin']))
                                <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-primary" 
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                @endcan
                                
                                @can('roles.create')
                                <form action="{{ route('roles.duplicate', $role) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Duplicate">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </form>
                                @endcan
                                
                                @can('roles.delete')
                                @if(!in_array($role->name, ['super_admin', 'admin', 'user']) && $role->users_count == 0)
                                <form action="{{ route('roles.destroy', $role) }}" method="POST" 
                                      class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-user-shield fa-3x mb-3"></i>
                                <p>No roles found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div>
                Showing {{ $roles->firstItem() ?? 0 }} to {{ $roles->lastItem() ?? 0 }} 
                of {{ $roles->total() }} entries
            </div>
            
            {{ $roles->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<!-- Role Information Card -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Role Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="fas fa-shield-alt fa-2x text-warning"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Super Admin</h6>
                                <small class="text-muted">Full system access, cannot be modified</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="fas fa-user-cog fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Admin</h6>
                                <small class="text-muted">Administrative access, system role</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="fas fa-users fa-2x text-success"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Custom Roles</h6>
                                <small class="text-muted">User-defined roles with specific permissions</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="fas fa-key fa-2x text-info"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Permissions</h6>
                                <small class="text-muted">Granular access control for modules</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Delete confirmation
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        
        if (confirm('Are you sure you want to delete this role? This action cannot be undone.')) {
            form.submit();
        }
    });
});
</script>
@endpush