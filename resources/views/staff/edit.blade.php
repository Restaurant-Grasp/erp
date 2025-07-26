@extends('layouts.app')
@section('title', 'Edit Staff')
@section('content')
<div class="card">
    <div class="card-header"><h5>Edit Staff</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('staff.update', $staff) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ $staff->name }}" required>
            </div>
            <div class="mb-3">
                <label>Employee ID <span class="text-danger">*</span></label>
                <input type="text" name="employee_id" class="form-control" value="{{ $staff->employee_id }}" required>
            </div>
            <div class="mb-3">
                <label>Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="{{ $staff->email }}" required>
            </div>
            <div class="mb-3">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ $staff->phone }}">
            </div>
            <div class="mb-3">
                <label>Address</label>
                <textarea name="address" class="form-control" rows="3" >{{ $staff->address }}</textarea>

            </div>
            <div class="mb-3">
                <label>Department <span class="text-danger">*</span></label>
                <select name="department_id" class="form-control" required>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $dept->id == $staff->department_id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label>Designation </label>
                <input type="text" name="designation" class="form-control" value="{{ $staff->designation }}">
            </div>
            <div class="mb-3">
                <label>Role <span class="text-danger">*</span></label>
                <select name="role" class="form-control" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ $staff->user && $staff->user->hasRole($role->name) ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-success">Update</button>
            <a href="{{ route('staff.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
@endsection