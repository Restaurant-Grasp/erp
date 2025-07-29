@extends('layouts.app')
@section('title', 'Create Model')
@section('content')
<div class="card">
    <div class="card-header"><h5>Create Model</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('model.store') }}">
            @csrf
            <div class="mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Code</label>
                <input type="text" name="code" class="form-control">
            </div>
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
                <label>Specifications</label>
                <input type="text" name="specifications" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('model.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
