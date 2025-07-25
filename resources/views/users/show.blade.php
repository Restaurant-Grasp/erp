@extends('layouts.app')

@section('title', 'View User')

@section('content')
<div class="page-header">
    <h1 class="page-title">User Details</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
            <li class="breadcrumb-item active">View</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    @if($user->profile_photo)
                        <img src="{{ Storage::url($user->profile_photo) }}" alt="{{ $user->name }}" 
                             class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                        <div class="user-avatar mx-auto" style="width: 150px; height: 150px; font-size: 60px;">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <h4>{{ $user->name }}</h4>
                <p class="text-muted mb-3">{{ $user->designation ?? 'No designation' }}</p>
                {!! $user->status_badge !!}
                
                <hr>
                
                <div class="text-start">
                    <p class="mb-2"><strong>Employee ID:</strong> {{ $user->employee_id ?? '-' }}</p>
                    <p class="mb-2"><strong>Email:</strong> <a href="mailto:{{ $user->email }}">{{ $user->email }}</a></p>
                    <p class="mb-2"><strong>Phone:</strong> {{ $user->phone ?? '-' }}</p>
                    <p class="mb-2"><strong>Department:</strong> {{ $user->department ?? '-' }}</p>
                </div>
                
                @can('users.edit')
                <div class="d-grid gap-2 mt-4">
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i> Edit User
                    </a>
                </div>
                @endcan
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i> Roles & Permissions</h5>
            </div>
            <div class="card-body">
                <h6>Assigned Roles:</h6>
                <div class="mb-3">
                    @forelse($user->roles as $role)
                        <span class="badge bg-info me-2 mb-2">{{ ucfirst($role->name) }}</span>
                    @empty
                        <span class="text-muted">No roles assigned</span>
                    @endforelse
                </div>
                
                <h6>Permissions:</h6>
                <div class="accordion" id="permissionsAccordion">
                    @foreach($user->roles as $role)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading{{ $role->id }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapse{{ $role->id }}" aria-expanded="false">
                                {{ ucfirst($role->name) }} Role Permissions
                            </button>
                        </h2>
                        <div id="collapse{{ $role->id }}" class="accordion-collapse collapse" 
                             data-bs-parent="#permissionsAccordion">
                            <div class="accordion-body">
                                @php
                                    $groupedPermissions = $role->permissions->groupBy('module');
                                @endphp
                                @forelse($groupedPermissions as $module => $permissions)
                                    <h6 class="text-uppercase small text-muted mb-2">{{ $module }}</h6>
                                    <div class="mb-3">
                                        @foreach($permissions as $permission)
                                            <span class="badge bg-secondary me-2 mb-1">{{ $permission->permission }}</span>
                                        @endforeach
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">No permissions assigned to this role</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Additional Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Address:</strong></p>
                        <p class="text-muted">{{ $user->address ?? 'No address provided' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Account Timestamps:</strong></p>
                        <ul class="list-unstyled text-muted">
                            <li>Created: {{ $user->created_at->format('d M Y, h:i A') }}</li>
                            <li>Updated: {{ $user->updated_at->format('d M Y, h:i A') }}</li>
                            <li>Last Login: {{ $user->last_login_at ? $user->last_login_at->format('d M Y, h:i A') : 'Never' }}</li>
                            @if($user->last_login_ip)
                            <li>Last Login IP: {{ $user->last_login_ip }}</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i> Recent Activity</h5>
            </div>
            <div class="card-body">
                <p class="text-muted text-center py-3">No recent activity to display</p>
            </div>
        </div>
    </div>
</div>
@endsection