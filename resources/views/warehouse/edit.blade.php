@extends('layouts.app')
@section('title', 'Edit Warehouse')
@section('content')
<div class="card">
    <div class="card-header">
        <h5>Edit Warehouse</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('warehouse.update', $warehouse) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $warehouse->name) }}">
                                @error('name')
                <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $warehouse->description) }}</textarea>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="is_active" class="form-check-input" id="activeCheck"
                    {{ old('is_active', $warehouse->status) ? 'checked' : '' }}>
                <label class="form-check-label" for="activeCheck">Active</label>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('warehouse.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection