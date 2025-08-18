@extends('layouts.app')
@section('title', 'Edit Model')
@section('content')
<div class="card">
    <div class="card-header">
        <h5>Edit Model</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('model.update', $model) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $model->name) }}">
                @error('name')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Code</label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $model->code) }}">
                @error('code')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Brand <span class="text-danger">*</span></label>
                <select name="brand_id" class="form-control @error('brand_id') is-invalid @enderror">
                    <option value="">-- Select Brand --</option>
                    @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" {{ old('brand_id', $model->brand_id) == $brand->id ? 'selected' : '' }}>
                        {{ $brand->name }}
                    </option>
                    @endforeach
                </select>
                @error('brand_id')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Specifications</label>
                <input type="text" name="specifications" class="form-control @error('specifications') is-invalid @enderror" value="{{ old('specifications', $model->specifications) }}">
                @error('specifications')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-success">Update</button>
            <a href="{{ route('model.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
@endsection