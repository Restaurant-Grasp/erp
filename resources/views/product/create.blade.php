@extends('layouts.app')
@section('title', 'Create Product')

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
/* Custom Select2 styling to match your theme */
.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    padding: 6px 12px;
    display: flex;
    align-items: center;
}
.select2-selection__placeholder {
    color: black !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 24px;
    padding-left: 0;
    color: #495057;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
    right: 10px;
}

.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 0.2rem rgba(0, 165, 81, 0.25);
}

.select2-dropdown {
    border: 1px solid var(--primary-green);
    border-radius: 0.375rem;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: var(--primary-green);
}

.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    padding: 6px 12px;
}

.select2-container--default .select2-search--dropdown .select2-search__field:focus {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 0.2rem rgba(0, 165, 81, 0.25);
}

/* Error state styling */
.is-invalid + .select2-container--default .select2-selection--single {
    border-color: #dc3545;
}

.is-invalid + .select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Create Product</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('product.store') }}">
            @csrf
            <div class="mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name') }}">
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="mb-2">Ledger <span class="text-danger">*</span></label>
                <select name="ledger_id" class="form-control ledger-select2  @error('ledger_id') is-invalid @enderror" style="width: 100%;">
                    <option value="">-- Search and Select Ledger --</option>
                    @foreach($ledgers as $ledger)
                    <option value="{{ $ledger->id }}" {{ old('ledger_id') == $ledger->id ? 'selected' : '' }}>
                        {{ $ledger->left_code }} / {{ $ledger->right_code }} - {{ $ledger->name }}
                    </option>
                    @endforeach
                </select>
                @error('ledger_id')
                <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>


            @php
            function renderCategoryTree($categories, $prefix = '')
            {
            foreach ($categories as $category) {
            $selected = old('category_id') == $category->id ? 'selected' : '';
            echo "<option value='{$category->id}' {$selected}>{$prefix}{$category->name}</option>";
            if ($category->childrenCategories && $category->childrenCategories->count()) {
            renderCategoryTree($category->childrenCategories, $prefix . '-- ');
            }
            }
            }
            @endphp

            @if (!empty($categories) && $categories->count())
            <div class="mb-3">
                <label class="form-label">Category <span class="text-danger">*</span></label>
                <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                    <option value="">Select a category</option>
                    @php renderCategoryTree($categories); @endphp
                </select>
                @error('category_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            @endif

            <div class="mb-3">
                <label>Brand <span class="text-danger">*</span></label>
                <select name="brand_id" class="form-control @error('brand_id') is-invalid @enderror">
                    <option value="">-- Select Brand --</option>
                    @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                        {{ $brand->name }}
                    </option>
                    @endforeach
                </select>
                @error('brand_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Model <span class="text-danger">*</span></label>
                <select name="model_id" class="form-control @error('model_id') is-invalid @enderror">
                    <option value="">-- Select Model --</option>
                    @foreach($models as $model)
                    <option value="{{ $model->id }}" {{ old('model_id') == $model->id ? 'selected' : '' }}>
                        {{ $model->name }}
                    </option>
                    @endforeach
                </select>
                @error('model_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>UOM <span class="text-danger">*</span></label>
                <select name="uom_id" class="form-control @error('uom_id') is-invalid @enderror">
                    <option value="">-- Select UOM --</option>
                    @foreach($uoms as $uom)
                    <option value="{{ $uom->id }}" {{ old('uom_id') == $uom->id ? 'selected' : '' }}>
                        {{ $uom->name }}
                    </option>
                    @endforeach
                </select>
                @error('uom_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="cost_price" class="form-label">Cost Price</label>
                <input type="number" name="cost_price" id="cost_price" step="0.01"
                    class="form-control @error('cost_price') is-invalid @enderror"
                    value="{{ old('cost_price') }}">
                @error('cost_price')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="min_stock_level" class="form-label">Minimum Stock Level</label>
                <input type="number" name="min_stock_level" id="min_stock_level"
                    class="form-control @error('min_stock_level') is-invalid @enderror"
                    value="{{ old('min_stock_level') }}">
                @error('min_stock_level')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="reorder_level" class="form-label">Re-order Level</label>
                <input type="number" name="reorder_level" id="reorder_level"
                    class="form-control @error('reorder_level') is-invalid @enderror"
                    value="{{ old('reorder_level') }}">
                @error('reorder_level')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description"
                    class="form-control @error('description') is-invalid @enderror"
                    rows="3">{{ old('description') }}</textarea>
                @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="is_active" class="form-check-input" id="activeCheck"
                    {{ old('is_active') ? 'checked' : '' }}>
                <label class="form-check-label" for="activeCheck">Active</label>
            </div>

            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('product.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 for all dropdowns
    $('.ledger-select2').select2({
        placeholder: '-- Search and Select Ledger --',
        allowClear: true,
        width: '100%',
        theme: 'default'
    });
    
});
</script>
@endpush