@extends('layouts.app')
@section('title', 'Create UOM')
@section('content')
<div class="card">
    <div class="card-header">
        <h5>Create UOM</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('uom.store') }}">
            @csrf
            <div class="mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name') }}"
                    required>
                @error('name')
                <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="is_active" class="form-check-input" id="activeCheck" {{ old('is_active') ? 'checked' : '' }}>
                <label class="form-check-label" for="activeCheck">Active</label>
            </div>

            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('uom.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection