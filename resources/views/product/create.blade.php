@extends('layouts.app')
@section('title', 'Create Product')
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
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Ledger</label>
                <select name="ledger_id" class="form-control" required>
                    <option value="">-- Select Ledger --</option>
                    @foreach($ledgers as $ledger)
                    <option value="{{ $ledger->id }}"> {{ $ledger->left_code }} / {{ $ledger->right_code }} - {{ $ledger->name }}</option>
                    @endforeach
                </select>
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
                <label class="form-label">Category <span class="text-danger">*</span></label>
                <select name="category_id" class="form-select" required>
                    <option value="">Select a category</option>
                    @php renderCategoryTree($categories); @endphp
                </select>

            </div>
            @endif

            <div class="mb-3">
                <label>Brand <span class="text-danger">*</span></label>
                <select name="brand_id" class="form-control" required>
                    <option value="">-- Select Brand --</option>
                    @foreach($brands as $brand)
                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label>Model <span class="text-danger">*</span></label>
                <select name="model_id" class="form-control" required>
                    <option value="">-- Select Model --</option>
                    @foreach($models as $model)
                    <option value="{{ $model->id }}">{{ $model->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label>UOM <span class="text-danger">*</span></label>
                <select name="uom_id" class="form-control" required>
                    <option value="">-- Select UOM --</option>
                    @foreach($uoms as $uom)
                    <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="cost_price" class="form-label">Cost Price</label>
                <input type="number" name="cost_price" id="cost_price" class="form-control">
            </div>

            <div class="mb-3">
                <label for="min_stock_level" class="form-label">Minimum Stock Level</label>
                <input type="number" name="min_stock_level" id="min_stock_level" class="form-control">
            </div>

            <div class="mb-3">
                <label for="reorder_level" class="form-label">Re-order Level</label>
                <input type="number" name="reorder_level" id="reorder_level" class="form-control">
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="3"></textarea>
            </div>


            <div class="mb-3 form-check">
                <input type="checkbox" name="is_active" class="form-check-input" id="activeCheck">
                <label class="form-check-label" for="activeCheck">Active</label>
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('product.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection