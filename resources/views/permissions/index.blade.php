@extends('layouts.app')

@section('title', 'Permissions Management')

@section('content')
<div class="page-header">
    <h1 class="page-title">Permissions Management</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Permissions</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Permissions</h5>
                @can('permissions.create')
                <a href="{{ route('permissions.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-2"></i> Add Permission
                </a>
                @endcan
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Module</th>
                                <th>Permission</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th width="100">Roles</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permissions as $permission)
                            <tr>
                                <td>{{ $permission->id }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ strtoupper($permission->module ?? 'GENERAL') }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $permission->permission ?? '-' }}</span>
                                </td>
                                <td>
                                    <code>{{ $permission->name }}</code>
                                </td>
                                <td>{{ $permission->description ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $permission->roles->count() }} roles</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-key fa-3x mb-3"></i>
                                        <p>No permissions found</p>
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
                        Showing {{ $permissions->firstItem() ?? 0 }} to {{ $permissions->lastItem() ?? 0 }} 
                        of {{ $permissions->total() }} entries
                    </div>
                    {{ $permissions->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Permission Statistics</h5>
            </div>
            <div class="card-body">
                <h6>Permissions by Module</h6>
                <div class="mb-3">
                    @foreach($groupedPermissions as $module => $modulePermissions)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase small">{{ str_replace('_', ' ', $module) }}</span>
                        <span class="badge bg-primary">{{ $modulePermissions->count() }}</span>
                    </div>
                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ ($modulePermissions->count() / $permissions->total()) * 100 }}%"></div>
                    </div>
                    @endforeach
                </div>

                <hr>

                <h6>Total Summary</h6>
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="text-muted small">Total Permissions</div>
                        <div class="h4 mb-0">{{ \Spatie\Permission\Models\Permission::count() }}</div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="text-muted small">Total Modules</div>
                        <div class="h4 mb-0">{{ $groupedPermissions->count() }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Total Roles</div>
                        <div class="h4 mb-0">{{ \Spatie\Permission\Models\Role::count() }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Assigned Permissions</div>
                        <div class="h4 mb-0">{{ \DB::table('role_has_permissions')->count() }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> About Permissions</h5>
            </div>
            <div class="card-body">
                <p class="mb-3">Permissions control access to different features and modules within the system.</p>
                
                <h6>Permission Types:</h6>
                <ul class="mb-0">
                    <li><strong>View:</strong> Read-only access</li>
                    <li><strong>Create:</strong> Add new records</li>
                    <li><strong>Edit:</strong> Modify existing records</li>
                    <li><strong>Delete:</strong> Remove records</li>
                    <li><strong>Custom:</strong> Special actions (approve, export, etc.)</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection