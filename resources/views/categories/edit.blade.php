@extends('layouts.app')
@section('title', 'Edit Categories')
@section('content')
<div class="card">
    <div class="card-header">
        <h5>Edit Category</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('categories.update', $category->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}">
                @error('name')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            @php
            function renderCategoryTree($categories, $prefix = '', $selectedId = null, $currentId = null, &$rendered = [])
            {
                foreach ($categories as $cat) {
                    if ($cat->id == $currentId || in_array($cat->id, $rendered)) continue; // Skip self or already rendered
                    $rendered[] = $cat->id; // Track rendered ID
                    $selected = $selectedId == $cat->id ? 'selected' : '';
                    echo "<option value='{$cat->id}' $selected>{$prefix}{$cat->name}</option>";
                    if ($cat->childrenCategories && $cat->childrenCategories->count()) {
                        renderCategoryTree($cat->childrenCategories, $prefix . '-- ', $selectedId, $currentId, $rendered);
                    }
                }
            }
            @endphp

            @if (!empty($allCategories) && $allCategories->count())
            <div class="mb-3">
                <label class="form-label">Parent Category</label>
                <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                    <option value="">Select a category</option>
                    @php
                    $rendered = [];
                    renderCategoryTree($allCategories, '', old('parent_id', $category->parent_id), $category->id, $rendered);
                    @endphp
                </select>
                @error('parent_id')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>
            @endif

            <div class="mb-3">
                <label>Code </label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $category->code) }}">
                @error('code')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $category->description) }}</textarea>
                @error('description')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="is_active" class="form-check-input @error('is_active') is-invalid @enderror" id="activeCheck"
                    {{ old('is_active', $category->status) ? 'checked' : '' }}>
                <label class="form-check-label" for="activeCheck">Active</label>
                @error('is_active')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection