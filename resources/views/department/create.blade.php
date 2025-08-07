@extends('layouts.app')
@section('title', 'Create Department')
@section('content')
<div class="card">
    <div class="card-header">
        <h5>Create Department</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('department.store') }}">
            @csrf
            <div class="mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Code <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('department.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection