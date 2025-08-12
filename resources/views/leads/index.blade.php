@extends('layouts.app')

@section('title', 'Lead Management')

@section('content')
<div class="page-header">
    <h1 class="page-title">Lead Management</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Leads</li>
        </ol>
    </nav>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Search & Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('leads.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Search by name, email, phone..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                        <option value="contacted" {{ request('status') == 'contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="qualified" {{ request('status') == 'qualified' ? 'selected' : '' }}>Qualified</option>
                        <option value="proposal" {{ request('status') == 'proposal' ? 'selected' : '' }}>Proposal</option>
                        <option value="negotiation" {{ request('status') == 'negotiation' ? 'selected' : '' }}>Negotiation</option>
                        <option value="won" {{ request('status') == 'won' ? 'selected' : '' }}>Won</option>
                        <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Lost</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="category">
                        <option value="">All Categories</option>
                        @foreach($templeCategories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_from" placeholder="From Date" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_to" placeholder="To Date" 
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Main Content -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Leads</h5>
        <div>
            @can('temple_categories.manage')
            <a href="{{ route('temple-categories.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-tags me-2"></i> Temple Categories
            </a>
            @endcan
            @can('leads.create')
            <a href="{{ route('leads.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Add New Lead
            </a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="100">Lead No</th>
                        <th>Temple Name</th>
                        <th>Contact Person</th>
                        <th>Contact Info</th>
                        <th width="120">Category</th>
                        <th width="100">Size</th>
                        <th width="100">Status</th>
                        <th width="120">Assigned To</th>
                        <th width="100">Created</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                    <tr>
                        <td>
                            <a href="{{ route('leads.show', $lead) }}" class="text-decoration-none">
                                {{ $lead->lead_no }}
                            </a>
                        </td>
                        <td>
                            <strong>{{ $lead->company_name ?: '-' }}</strong>
                            @if($lead->city)
                            <br><small class="text-muted">{{ $lead->city }}</small>
                            @endif
                        </td>
                        <td>{{ $lead->contact_person }}</td>
                        <td>
                            @if($lead->email)
                            <i class="fas fa-envelope text-muted me-1"></i> {{ $lead->email }}<br>
                            @endif
                            @if($lead->mobile)
                            <i class="fas fa-mobile text-muted me-1"></i> {{ $lead->mobile }}
                            @elseif($lead->phone)
                            <i class="fas fa-phone text-muted me-1"></i> {{ $lead->phone }}
                            @endif
                        </td>
                        <td>
                            @if($lead->templeCategory)
                            <span class="badge bg-info">{{ $lead->templeCategory->name }}</span>
                            @else
                            -
                            @endif
                        </td>
                        <td>
                            @if($lead->temple_size)
                            <span class="badge bg-secondary">{{ ucfirst($lead->temple_size) }}</span>
                            @else
                            -
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $lead->status_badge }}">
                                {{ ucfirst(str_replace('_', ' ', $lead->lead_status)) }}
                            </span>
                            @if($lead->is_converted)
                            <br><small class="text-success"><i class="fas fa-check-circle"></i> Converted</small>
                            @endif
                        </td>
                        <td>
                            {{ $lead->assignedTo->name ?? '-' }}
                        </td>
                        <td>
                            {{ $lead->created_at->format('d/m/Y') }}
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                @can('leads.view')
                                <a href="{{ route('leads.show', $lead) }}" class="btn btn-sm btn-outline-primary" 
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                
                                   @if(!$lead->is_converted)
                                    @can('leads.edit')
                                    <a href="{{ route('leads.edit', $lead) }}" class="btn btn-sm btn-outline-primary" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('leads.convert')
                                    @if(in_array($lead->lead_status, ['qualified', 'proposal', 'negotiation']))
                                    <a href="{{ route('leads.convert', $lead) }}" class="btn btn-sm btn-outline-success" 
                                       title="Convert to Customer">
                                        <i class="fas fa-exchange-alt"></i>
                                    </a>
                                    @endif
                                    @endcan
                                    
                                    @can('leads.delete')
                                    @if($lead->quotations->count() == 0)
                                    <form action="{{ route('leads.destroy', $lead) }}" method="POST" 
                                          class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <p>No leads found</p>
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
                Showing {{ $leads->firstItem() ?? 0 }} to {{ $leads->lastItem() ?? 0 }} 
                of {{ $leads->total() }} entries
            </div>
            
            {{ $leads->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<!-- Lead Statistics -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                <h5 class="card-title">Total Leads</h5>
                <h3>{{ $leads->total() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-user-plus fa-2x text-info mb-2"></i>
                <h5 class="card-title">New Leads</h5>
                <h3>{{ \App\Models\Lead::where('lead_status', 'new')->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-handshake fa-2x text-warning mb-2"></i>
                <h5 class="card-title">In Progress</h5>
                <h3>{{ \App\Models\Lead::whereIn('lead_status', ['contacted', 'qualified', 'proposal', 'negotiation'])->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-trophy fa-2x text-success mb-2"></i>
                <h5 class="card-title">Converted</h5>
                <h3>{{ \App\Models\Lead::whereNotNull('converted_to_customer_id')->count() }}</h3>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    // Delete confirmation
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        
        if (confirm('Are you sure you want to delete this lead? This action cannot be undone.')) {
            form.submit();
        }
    });

    // Auto-submit filter form on change
    $('#filterForm select').on('change', function() {
        $('#filterForm').submit();
    });
});
</script>
@endsection

