@extends('layouts.app')
@section('title', 'Service List')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Service List</h5>
        @php
        $role = auth()->user()->getRoleNames()->first();
        $permissions = getCurrentRolePermissions($role);
        @endphp
        @if ($permissions->contains('name', 'service.create'))
        <a href="{{ route('service.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add Service
        </a>
        @endif
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Service Type</th>
                        <th>Item Type</th>
                        <th>Base Price</th>
                        <th>Billing Cycle</th>
                        <th>Recurring</th>
                        <th>Status</th>
                        @if ($permissions->contains('name', 'service.edit') || $permissions->contains('name', 'service.delete'))
                        <th>Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($serviceList as $index => $service)
                    <tr>
                        <td>{{ $serviceList->firstItem() + $index }}</td>
                        <td>
                            <strong>{{ $service->name }}</strong>
                            @if($service->description)
                                <br><small class="text-muted">{{ Str::limit($service->description, 50) }}</small>
                            @endif
                        </td>
                        <td>{{ $service->code ?: 'N/A' }}</td>
                        <td>{{ $service->serviceType->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge {{ $service->item_type === 'service' ? 'bg-info' : 'bg-warning' }}">
                                {{ ucfirst($service->item_type) }}
                            </span>
                        </td>
                        <td>{{ $service->formatted_price }}</td>
                        <td>{{ $service->billing_cycle_label }}</td>
                        <td>
                            <span class="badge {{ $service->is_recurring ? 'bg-success' : 'bg-secondary' }}">
                                {{ $service->recurring_label }}
                            </span>
                        </td>
                        <td>
                            <span class="{{ $service->status_badge_class }}">
                                {{ $service->status_label }}
                            </span>
                        </td>
                        @if ($permissions->contains('name', 'service.edit') || $permissions->contains('name', 'service.delete'))
                        <td>
                            @if ($permissions->contains('name', 'service.edit'))
                            <a href="{{ route('service.edit', $service) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif
                            @if ($permissions->contains('name', 'service.delete'))
                            <form method="POST" action="{{ route('service.destroy', $service) }}" style="display:inline-block">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Are you sure you want to delete this service?')" 
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-cogs fa-3x mb-3"></i>
                                <p>No Services found</p>
                                @if ($permissions->contains('name', 'service.create'))
                                    <a href="{{ route('service.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Create First Service
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($serviceList->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div>
                Showing {{ $serviceList->firstItem() ?? 0 }} to {{ $serviceList->lastItem() ?? 0 }}
                of {{ $serviceList->total() }} entries
            </div>
            {{ $serviceList->links('pagination::bootstrap-4') }}
        </div>
        @endif
    </div>
</div>
@endsection