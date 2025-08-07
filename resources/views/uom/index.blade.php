@extends('layouts.app')
@section('title', 'UOM List')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>UOM List</h5>
        @php
        $role = auth()->user()->getRoleNames()->first();
        $permissions = getCurrentRolePermissions($role);
        @endphp
        @if ($permissions->contains('name', 'uom.create'))
        <a href="{{ route('uom.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add UOM</a>
        @endif
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        @if ($permissions->contains('name', 'uom.edit') || $permissions->contains('name', 'uom.delete'))
                        <th>Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($uomList as $index => $uom)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $uom->name }}</td>
                        @if ($permissions->contains('name', 'uom.edit') || $permissions->contains('name', 'uom.delete'))
                        <td>
                            @if ($permissions->contains('name', 'uom.edit'))
                            <a href="{{ route('uom.edit', $uom) }}" class="btn btn-sm btn-outline-primary"
                                title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif
                            @if ($permissions->contains('name', 'uom.edit'))
                            <form method="POST" action="{{ route('uom.destroy', $uom) }}" style="display:inline-block" onsubmit="return confirm('Are you sure you want to delete this UOM?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-user-friends fa-3x mb-3"></i>
                                <p>No UOM found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div>
                Showing {{ $uomList->firstItem() ?? 0 }} to {{ $uomList->lastItem() ?? 0 }}
                of {{ $uomList->total() }} entries
            </div>

            {{ $uomList->links('pagination::bootstrap-4') }}
        </div>

    </div>
</div>
@endsection