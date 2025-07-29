@extends('layouts.app')
@section('title', 'Edit Model')
@section('content')
<div class="card">
    <div class="card-header"><h5>Edit Model</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('model.update', $model) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ $model->name }}" required>
            </div>
            <div class="mb-3">
                <label>Code</label>
                <input type="text" name="code" class="form-control" value="{{ $model->code }}">
            </div>
            <div class="mb-3">
                <label>Brand <span class="text-danger">*</span></label>
                <select name="brand_id" class="form-control" required>
                    <option value="">-- Select Brand --</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ $brand->id == old('brand_id', $model->brand_id) ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Specifications</label>
                <input type="text" name="specifications" class="form-control" value="{{ old('specifications', $model->specifications) }}">
            </div>
            <button type="submit" class="btn btn-success">Update</button>
            <a href="{{ route('model.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
@endsection