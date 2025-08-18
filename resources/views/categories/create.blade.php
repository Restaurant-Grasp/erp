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
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                @error('name')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
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
                <label class="form-label">Parent Category</label>
                <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                    <option value="">Select a category</option>
                    @php renderCategoryTree($categories); @endphp
                </select>
                @error('parent_id')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>
            @endif

            <div class="mb-3">
                <label>Code <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}">
                @error('code')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                @error('description')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="is_active" class="form-check-input @error('is_active') is-invalid @enderror" id="activeCheck" {{ old('is_active') ? 'checked' : '' }}>
                <label class="form-check-label" for="activeCheck">Active</label>
                @error('is_active')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection