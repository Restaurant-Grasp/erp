@extends('layouts.app')
@section('title', 'Department List')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Department List</h5>
        <a href="{{ route('department.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Department</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>

                    @forelse($departmentList as $index => $department)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $department->name }}</td>
                        <td>{{ $department->code }}</td>

                        <td>

                            <a href="{{ route('department.edit', $department) }}" class="btn btn-sm btn-outline-primary"
                                title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('department.destroy', $department) }}" style="display:inline-block">
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
                                <p>No Department found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div>
                Showing {{ $departmentList->firstItem() ?? 0 }} to {{ $departmentList->lastItem() ?? 0 }}
                of {{ $departmentList->total() }} entries
            </div>

            {{ $departmentList->links('pagination::bootstrap-4') }}
        </div>

    </div>
</div>
@endsection