@extends('layouts.app')
@section('title', 'Edit Staff')
@section('content')
<div class="card">
    <div class="card-header">
        <h5>Edit Staff</h5>
    </div>
    <div class="card-body">

        {{-- Show all errors --}}
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('staff.update', $staff) }}">
            @csrf @method('PUT')

            {{-- Name --}}
            <div class="mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" 
                       name="name" 
                       value="{{ old('name', $staff->name) }}" 
                       class="form-control @error('name') is-invalid @enderror" 
                       >
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Employee ID --}}
            <div class="mb-3">
                <label>Employee ID <span class="text-danger">*</span></label>
                <input type="text" 
                       name="employee_id" 
                       value="{{ old('employee_id', $staff->employee_id) }}" 
                       class="form-control @error('employee_id') is-invalid @enderror" 
                       >
                @error('employee_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-3">
                <label>Email <span class="text-danger">*</span></label>
                <input type="email" 
                       name="email" 
                       value="{{ old('email', $staff->email) }}" 
                       class="form-control @error('email') is-invalid @enderror" 
                       >
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Phone --}}
            <div class="mb-3">
                <label>Phone</label>
                <input type="text" 
                       name="phone" 
                       value="{{ old('phone', $staff->phone) }}" 
                       class="form-control @error('phone') is-invalid @enderror">
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Address --}}
            <div class="mb-3">
                <label>Address</label>
                <textarea name="address" 
                          class="form-control @error('address') is-invalid @enderror" 
                          rows="3">{{ old('address', $staff->address) }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Department --}}
            <div class="mb-3">
                <label>Department <span class="text-danger">*</span></label>
                <select name="department_id" 
                        class="form-control @error('department_id') is-invalid @enderror" 
                        >
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id', $staff->department_id) == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Designation --}}
            <div class="mb-3">
                <label>Designation</label>
                <input type="text" 
                       name="designation" 
                       value="{{ old('designation', $staff->designation) }}" 
                       class="form-control @error('designation') is-invalid @enderror">
                @error('designation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Role --}}
            <div class="mb-3">
                <label>Role <span class="text-danger">*</span></label>
                <select name="role" 
                        class="form-control @error('role') is-invalid @enderror" 
                        >
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" 
                            {{ old('role', ($staff->user && $staff->user->roles->first()?->id)) == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Buttons --}}
            <button type="submit" class="btn btn-success">Update</button>
            <a href="{{ route('staff.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>

<script>
$(document).ready(function () {
    $("form").validate({
        rules: {
            name: {
                required: true,
                maxlength: 255
            },
            employee_id: {
                required: true,
                maxlength: 255
            },
            email: {
                required: true,
                email: true,
                maxlength: 255
            },
            department_id: {
                required: true
            },
            role: {
                required: true
            },
            phone: {
                maxlength: 20
            },
            designation: {
                maxlength: 255
            }
        },
        messages: {
            name: {
                required: "Name is required",
                maxlength: "Name cannot exceed 255 characters"
            },
            employee_id: {
                required: "Employee ID is required",
                maxlength: "Employee ID cannot exceed 255 characters"
            },
            email: {
                required: "Email is required",
                email: "Enter a valid email",
                maxlength: "Email cannot exceed 255 characters"
            },
            department_id: {
                required: "Please select a department"
            },
            role: {
                required: "Please select a role"
            },
            phone: {
                maxlength: "Phone number cannot exceed 20 characters"
            },
            designation: {
                maxlength: "Designation cannot exceed 255 characters"
            }
        },
        errorElement: "span",
        errorClass: "text-danger",
        highlight: function (element) {
            $(element).addClass("is-invalid");
        },
        unhighlight: function (element) {
            $(element).removeClass("is-invalid");
        }
    });
});
</script>
@endpush
