@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>
</div>

<!-- Welcome Section -->
<div class="alert alert-success d-flex align-items-center mb-4" role="alert">
    <i class="fas fa-info-circle fa-2x me-3"></i>
    <div>
        <h5 class="alert-heading mb-1">Welcome back, {{ Auth::user()->name }}!</h5>
        <p class="mb-0">You are logged in as <strong>{{ Auth::user()->roles->first()->name ?? 'User' }}</strong>. 
        Last login: {{ Auth::user()->last_login_at ? Auth::user()->last_login_at->format('d M Y, h:i A') : 'First time login' }}</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    @can('users.view')
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ \App\Models\User::count() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('customers.view')
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Customers</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('vendors.view')
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Vendors</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-truck fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('products.view')
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Products</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @can('customers.create')
                    <div class="col-md-3 mb-3">
                        <a href="#" class="btn btn-outline-primary w-100">
                            <i class="fas fa-user-plus fa-2x mb-2"></i><br>
                            Add Customer
                        </a>
                    </div>
                    @endcan
                    
                    @can('quotations.create')
                    <div class="col-md-3 mb-3">
                        <a href="#" class="btn btn-outline-success w-100">
                            <i class="fas fa-file-alt fa-2x mb-2"></i><br>
                            Create Quotation
                        </a>
                    </div>
                    @endcan
                    
                    @can('invoices.create')
                    <div class="col-md-3 mb-3">
                        <a href="#" class="btn btn-outline-info w-100">
                            <i class="fas fa-file-invoice fa-2x mb-2"></i><br>
                            Create Invoice
                        </a>
                    </div>
                    @endcan
                    
                    @can('service_tickets.create')
                    <div class="col-md-3 mb-3">
                        <a href="#" class="btn btn-outline-warning w-100">
                            <i class="fas fa-tools fa-2x mb-2"></i><br>
                            New Service Ticket
                        </a>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i> Recent Activities</h5>
            </div>
            <div class="card-body">
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-clock fa-3x mb-3"></i>
                    <p>No recent activities to display</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bell me-2"></i> Notifications</h5>
            </div>
            <div class="card-body">
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-bell-slash fa-3x mb-3"></i>
                    <p>No new notifications</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 4px solid #007bff !important;
}
.border-left-success {
    border-left: 4px solid var(--primary-green) !important;
}
.border-left-info {
    border-left: 4px solid #17a2b8 !important;
}
.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}
.shadow {
    box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15) !important;
}
.text-gray-300 {
    color: #dddfeb !important;
}
.text-gray-800 {
    color: #5a5c69 !important;
}
.text-xs {
    font-size: .875rem;
}
</style>
@endsection