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
                <input type="text" name="name" class="form-control" required value="{{ old('name', $category->name) }}">
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
                <select name="parent_id" class="form-select">
                    <option value="">Select a category</option>
                    @php
                    $rendered = [];
                    renderCategoryTree($allCategories, '', old('parent_id', $category->parent_id), $category->id, $rendered);
                    @endphp
                </select>
            </div>
            @endif

            <div class="mb-3">
                <label>Code <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control" required value="{{ old('code', $category->code) }}">
            </div>

            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="is_active" class="form-check-input" id="activeCheck"
                    {{ old('status', $category->status) ? 'checked' : '' }}>
                <label class="form-check-label" for="activeCheck">Active</label>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
