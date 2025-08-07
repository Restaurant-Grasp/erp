@extends('layouts.app')
@section('title', 'Create Categories')
@section('content')
<div class="card">
    <div class="card-header">
        <h5>Create Categories</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('categories.store') }}">
            @csrf
            <div class="mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required>
            </div>

            @php
            function renderCategoryTree($categories, $prefix = '')
            {
            foreach ($categories as $category) {
            echo "<option value='{$category->id}'>{$prefix}{$category->name}</option>";
            if ($category->childrenCategories && $category->childrenCategories->count()) {
            renderCategoryTree($category->childrenCategories, $prefix . '-- ');
            }
            }
            }
            @endphp

            @if (!empty($categories) && $categories->count())
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="parent_id" class="form-select">
                    <option value="">Select a category</option>
                    @php renderCategoryTree($categories); @endphp
                </select>

            </div>
            @endif
            <div class="mb-3">
                <label>Code <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="is_active" class="form-check-input" id="activeCheck">
                <label class="form-check-label" for="activeCheck">Active</label>
            </div>

            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection