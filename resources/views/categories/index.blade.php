@extends('layouts.app')
@section('title', 'Categories List')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Categories List</h5>
        
        <a href="{{ route('categories.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Category</a>
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
                @forelse($categoriesList as $index => $categories)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $categories->name }}</td>
                    <td>{{ $categories->code }}</td>

                    <td>
                      
                        <a href="{{ route('categories.edit', $categories) }}" class="btn btn-sm btn-outline-primary" 
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('categories.destroy', $categories) }}" style="display:inline-block" onsubmit="return confirm('Are you sure you want to delete this category?');">
    @csrf
    @method('DELETE')
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
                                <p>No Categories found</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-4">
    <div>
        Showing {{ $categoriesList->firstItem() ?? 0 }} to {{ $categoriesList->lastItem() ?? 0 }} 
        of {{ $categoriesList->total() }} entries
    </div>
    
    {{ $categoriesList->links('pagination::bootstrap-4') }}
</div>

    </div>
</div>
@endsection
