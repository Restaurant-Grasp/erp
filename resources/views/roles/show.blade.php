@extends('layouts.app')

@section('title', 'View Role')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Role Details</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
                <li class="breadcrumb-item active">{{ ucwords(str_replace('_', ' ', $role->name)) }}</li>
            </ol>
        </nav>
    </div>
    <div>
        @can('roles.edit')
        @if(!in_array($role->name, ['super_admin', 'admin']))
        <a href="{{ route('roles.edit', $role) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i> Edit Role
        </a>
        @endif
        @endcan
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ ucwords(str_replace('_', ' ', $role->name)) }}</h5>
                <p class="text-muted">{{ $role->description ?? 'No description available' }}</p>
                
                <hr>
                
                <dl class="row">
                    <dt class="col-sm-5">Role Type:</dt>
                    <dd class="col-sm-7">
                        @if(in_array($role->name, ['super_admin', 'admin']))
                            <span class="badge bg-warning text-dark">System Role</span>
                        @else
                            <span class="badge bg-success">Custom Role</span>
                        @endif
                    </dd>
                    
                    <dt class="col-sm-5">Created:</dt>
                    <dd class="col-sm-7">{{ $role->created_at->format('d M Y, h:i A') }}</dd>
                    
                    <dt class="col-sm-5">Updated:</dt>
                    <dd class="col-sm-7">{{ $role->updated_at->format('d M Y, h:i A') }}</dd>
                    
                    <dt class="col-sm-5">Total Users:</dt>
                    <dd class="col-sm-7">
                        <span class="badge bg-info">{{ $role->users()->count() }} users</span>
                    </dd>
                    
                    <dt class="col-sm-5">Permissions:</dt>
                    <dd class="col-sm-7">
                        <span class="badge bg-secondary">{{ $role->permissions()->count() }} permissions</span>
                    </dd>
                </dl>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i> Users with this Role</h5>
            </div>
            <div class="card-body">
                @if($role->users->count() > 0)
                    <div class="list-group">
                        @foreach($role->users->take(5) as $user)
                        <a href="{{ route('users.show', $user) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-3" style="width: 40px; height: 40px; font-size: 16px;">
                                    @if($user->profile_photo)
                                        <img src="{{ Storage::url($user->profile_photo) }}" alt="" 
                                             class="rounded-circle" width="40" height="40">
                                    @else
                                        {{ substr($user->name, 0, 1) }}
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $user->name }}</div>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    @if($role->users->count() > 5)
                        <p class="text-center mt-3 mb-0">
                            <a href="{{ route('users.index', ['role' => $role->id]) }}" class="text-primary">
                                View all {{ $role->users->count() }} users →
                            </a>
                        </p>
                    @endif
                @else
                    <p class="text-muted text-center mb-0">No users assigned to this role</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-key me-2"></i> Permissions</h5>
            </div>
            <div class="card-body">
                @if($permissions->count() > 0)
                    @foreach($permissions as $module => $modulePermissions)
                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted mb-3">
                            <i class="fas fa-folder me-2"></i> {{ str_replace('_', ' ', $module) }} Module
                        </h6>
                        <div class="row g-2">
                            @foreach($modulePermissions as $permission)
                            <div class="col-md-4">
                                <div class="d-flex align-items-center p-2 border rounded bg-light">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <div>
                                        <div class="fw-semibold small">{{ ucfirst($permission->permission) }}</div>
                                        @if($permission->description)
                                        <div class="text-muted small">{{ $permission->description }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @if(!$loop->last)
                    <hr>
                    @endif
                    @endforeach
                @else
                    <p class="text-muted text-center py-4 mb-0">No permissions assigned to this role</p>
                @endif
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i> Permission Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-eye fa-2x text-info mb-2"></i>
                            <h6>View Permissions</h6>
                            <p class="mb-0 h4">{{ $role->permissions->where('permission', 'view')->count() }}</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-plus fa-2x text-success mb-2"></i>
                            <h6>Create Permissions</h6>
                            <p class="mb-0 h4">{{ $role->permissions->where('permission', 'create')->count() }}</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-edit fa-2x text-warning mb-2"></i>
                            <h6>Edit Permissions</h6>
                            <p class="mb-0 h4">{{ $role->permissions->where('permission', 'edit')->count() }}</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-trash fa-2x text-danger mb-2"></i>
                            <h6>Delete Permissions</h6>
                            <p class="mb-0 h4">{{ $role->permissions->where('permission', 'delete')->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection