@extends('layouts.app')
@section('title', 'Edit Product')
@section('content')
<div class="card">
    <div class="card-header">
        <h5>Edit Product</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('product.update', $product) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
            </div>

            <div class="mb-3">
                <label for="product_code" class="form-label">Product Code <span class="text-danger">*</span></label>
                <input type="text" name="product_code" id="product_code" class="form-control" value="{{ old('product_code', $product->product_code) }}" required>
            </div>
            <div class="mb-3">
                <label>Ledger</label>
                <select name="ledger_id" class="form-control" required>
                    <option value="">-- Select Ledger --</option>
                    @foreach($ledgers as $ledger)
                    <option value="{{ $ledger->id }}"
                        @if(old('ledger_id', $product->ledger_id ?? '') == $ledger->id) selected @endif>
                        {{ $ledger->left_code }} / {{ $ledger->right_code }} - {{ $ledger->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            @php
            function renderCategoryTreeEdit($categories, $prefix = '', $selectedId = null) {
            foreach ($categories as $category) {
            $selected = $selectedId == $category->id ? 'selected' : '';
            echo "<option value='{$category->id}' {$selected}>{$prefix}{$category->name}</option>";
            if ($category->childrenCategories && $category->childrenCategories->count()) {
            renderCategoryTreeEdit($category->childrenCategories, $prefix . '-- ', $selectedId);
            }
            }
            }
            @endphp

            @if (!empty($categories) && $categories->count())
            <div class="mb-3">
                <label class="form-label">Category <span class="text-danger">*</span></label>
                <select name="category_id" class="form-select" required>
                    <option value="">Select a category</option>
                    @php renderCategoryTreeEdit($categories, '', old('category_id', $product->category_id)); @endphp
                </select>
            </div>
            @endif

            <div class="mb-3">
                <label>Brand <span class="text-danger">*</span></label>
                <select name="brand_id" class="form-control" required>
                    <option value="">-- Select Brand --</option>
                    @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                        {{ $brand->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Model <span class="text-danger">*</span></label>
                <select name="model_id" class="form-control" required>
                    <option value="">-- Select Model --</option>
                    @foreach($models as $model)
                    <option value="{{ $model->id }}" {{ old('model_id', $product->model_id) == $model->id ? 'selected' : '' }}>
                        {{ $model->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>UOM <span class="text-danger">*</span></label>
                <select name="uom_id" class="form-control" required>
                    <option value="">-- Select UOM --</option>
                    @foreach($uoms as $uom)
                    <option value="{{ $uom->id }}" {{ old('uom_id', $product->uom_id) == $uom->id ? 'selected' : '' }}>
                        {{ $uom->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="cost_price" class="form-label">Cost Price</label>
                <input type="number" name="cost_price" id="cost_price" class="form-control" step="0.01" value="{{ old('cost_price', $product->cost_price) }}">
            </div>
            <div class="mb-3">
                <label for="min_stock_level" class="form-label">Minimum Stock Level</label>
                <input type="number" name="min_stock_level" id="min_stock_level" class="form-control"
                    value="{{ old('min_stock_level', $product->min_stock_level ?? 0) }}">
            </div>

            <div class="mb-3">
                <label for="reorder_level" class="form-label">Re-order Level</label>
                <input type="number" name="reorder_level" id="reorder_level" class="form-control"
                    value="{{ old('reorder_level', $product->reorder_level ?? 0) }}">
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
            </div>


            <div class="mb-3 form-check">
                <input type="checkbox" name="is_active" class="form-check-input" id="activeCheck" {{ old('is_active', $product->status) ? 'checked' : '' }}>
                <label class="form-check-label" for="activeCheck">Active</label>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('product.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection