@extends('layouts.app')
@section('title', 'Warehouse List')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Warehouse List</h5>
        <a href="{{ route('warehouse.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Warehouse</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>

                    @forelse($warehouseList as $index => $warehouse)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $warehouse->name }}</td>

                        <td>

                            <a href="{{ route('warehouse.edit', $warehouse) }}" class="btn btn-sm btn-outline-primary"
                                title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('warehouse.destroy', $warehouse) }}" style="display:inline-block">
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
                                <p>No Warehouse found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div>
                Showing {{ $warehouseList->firstItem() ?? 0 }} to {{ $warehouseList->lastItem() ?? 0 }}
                of {{ $warehouseList->total() }} entries
            </div>

            {{ $warehouseList->links('pagination::bootstrap-4') }}
        </div>

    </div>
</div>
@endsection