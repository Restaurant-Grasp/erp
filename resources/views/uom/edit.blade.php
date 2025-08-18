@extends('layouts.app')
@section('title', 'Edit UOM')
@section('content')
<div class="card">
    <div class="card-header">
        <h5>Edit UOM</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('uom.update', $uom) }}">
            @csrf 
            @method('PUT')

            <div class="mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input 
                    type="text" 
                    name="name" 
                    class="form-control @error('name') is-invalid @enderror" 
                    value="{{ old('name', $uom->name) }}">
                @error('name')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 form-check">
                <input 
                    type="checkbox" 
                    name="is_active" 
                    class="form-check-input" 
                    id="activeCheck"
                    {{ old('is_active', $uom->is_active) ? 'checked' : '' }}
                >
                <label class="form-check-label" for="activeCheck">Active</label>
            </div>

            <button type="submit" class="btn btn-success">Update</button>
            <a href="{{ route('uom.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
@endsection
