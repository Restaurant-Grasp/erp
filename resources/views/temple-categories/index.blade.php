@extends('layouts.app')

@section('title', 'Business Categories')

@section('content')
<div class="page-header">
    <h1 class="page-title">Business Categories</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
            <li class="breadcrumb-item active">Business Categories</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-4">
        <!-- Add/Edit Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0" id="form-title">Add New Category</h5>
            </div>
            <div class="card-body">
                <form id="categoryForm" action="{{ route('temple-categories.store') }}" method="POST">
                    @csrf
                    <div id="methodField"></div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" required value="{{ old('name') }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3" id="statusField" style="display: none;">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" name="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save me-2"></i> Save Category
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="cancelBtn" style="display: none;">
                            <i class="fas fa-times me-2"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Categories List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Business Categories</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th width="100">Leads</th>
                                <th width="100">Status</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                            <tr>
                                <td>{{ $category->id }}</td>
                                <td><strong>{{ $category->name }}</strong></td>
                                <td>{{ $category->description ?: '-' }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $category->leads_count }} leads</span>
                                </td>
                                <td>
                                    @if($category->status)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-btn" 
                                            data-id="{{ $category->id }}"
                                            data-name="{{ $category->name }}"
                                            data-description="{{ $category->description }}"
                                            data-status="{{ $category->status }}"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    @if($category->leads_count == 0)
                                    <form action="{{ route('temple-categories.destroy', $category) }}" 
                                          method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-tags fa-3x mb-3"></i>
                                        <p>No business categories found</p>
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
                        Showing {{ $categories->firstItem() ?? 0 }} to {{ $categories->lastItem() ?? 0 }} 
                        of {{ $categories->total() }} entries
                    </div>
                    
                    {{ $categories->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Edit button click
    $('.edit-btn').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const description = $(this).data('description');
        const status = $(this).data('status');
        
        // Update form
        $('#form-title').text('Edit Category');
        $('#categoryForm').attr('action', '/temple-categories/' + id);
        $('#methodField').html('<input type="hidden" name="_method" value="PUT">');
        $('#name').val(name);
        $('#description').val(description);
        $('#status').val(status);
        $('#statusField').show();
        $('#submitBtn').html('<i class="fas fa-save me-2"></i> Update Category');
        $('#cancelBtn').show();
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: $('#categoryForm').offset().top - 100
        }, 500);
    });
    
    // Cancel button click
    $('#cancelBtn').on('click', function() {
        $('#form-title').text('Add New Category');
        $('#categoryForm').attr('action', '{{ route('temple-categories.store') }}');
        $('#methodField').html('');
        $('#name').val('');
        $('#description').val('');
        $('#statusField').hide();
        $('#submitBtn').html('<i class="fas fa-save me-2"></i> Save Category');
        $('#cancelBtn').hide();
    });
    
    // Delete confirmation
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        
        if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
            form.submit();
        }
    });
});
</script>
@endsection

