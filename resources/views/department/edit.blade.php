@extends('layouts.app')
@section('title', 'Edit Department')
@section('content')
<div class="card">
    <div class="card-header">
        <h5>Edit Department</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('department.update', $department) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ $department->name }}" required>
            </div>
            <div class="mb-3">
                <label>Code <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control" value="{{ $department->code }}" required>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3">{{ $department->description }}</textarea>
            </div>
            <button type="submit" class="btn btn-success">Update</button>
            <a href="{{ route('department.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
@endsection