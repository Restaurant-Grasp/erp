@extends('layouts.app')
@section('title', 'Staff List')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Staff List</h5>
        <a href="{{ route('staff.create') }}" class="btn btn-primary">Add Staff</a>
    </div>
    <div class="card-body">
    <div class="table-responsive">
            <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Department</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                
                @forelse($staffList as $index => $staff)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $staff->name }}</td>
                    <td>{{ $staff->email }}</td>
                    <td>{{ $staff->phone }}</td>
                    <td>{{ $staff->department->name ?? '-' }}</td>
                    <td>{{ $staff->user?->roles->pluck('name')->first() }}</td>

                    <td>
                        <!-- <a href="{{ route('staff.edit', $staff) }}" class="btn btn-sm btn-warning">Edit</a> -->
                        <a href="{{ route('staff.edit', $staff) }}" class="btn btn-sm btn-outline-primary" 
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                        <form method="POST" action="{{ route('staff.destroy', $staff) }}" style="display:inline-block">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                        </form>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-user-friends fa-3x mb-3"></i>
                                <p>No Staff found</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-4">
    <div>
        Showing {{ $staffList->firstItem() ?? 0 }} to {{ $staffList->lastItem() ?? 0 }} 
        of {{ $staffList->total() }} entries
    </div>
    
    {{ $staffList->links('pagination::bootstrap-4') }}
</div>

    </div>
</div>
@endsection
