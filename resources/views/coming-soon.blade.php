@extends('layouts.app')

@section('title', $module ?? 'Coming Soon')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-rocket fa-5x text-primary opacity-50"></i>
                    </div>
                    <h2 class="mb-3">{{ $module ?? 'This Module' }} - Coming Soon!</h2>
                    <p class="lead text-muted mb-4">
                        We're working hard to bring you this feature. It will be available soon.
                    </p>
                    <div class="progress mb-4" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                             role="progressbar" style="width: 65%">
                            65% Complete
                        </div>
                    </div>
                    <p class="text-muted mb-4">
                        In the meantime, you can explore other available features of the system.
                    </p>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-users fa-3x text-primary mb-3"></i>
                            <h5>User Management</h5>
                            <p class="text-muted">Manage users and permissions</p>
                            <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-primary">Go to Users</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-user-shield fa-3x text-success mb-3"></i>
                            <h5>Role Management</h5>
                            <p class="text-muted">Configure roles and access</p>
                            <a href="{{ route('roles.index') }}" class="btn btn-sm btn-outline-success">Go to Roles</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-cog fa-3x text-info mb-3"></i>
                            <h5>Settings</h5>
                            <p class="text-muted">Configure system settings</p>
                            <a href="#" class="btn btn-sm btn-outline-info">Coming Soon</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.opacity-50 {
    opacity: 0.5;
}
</style>
@endsection