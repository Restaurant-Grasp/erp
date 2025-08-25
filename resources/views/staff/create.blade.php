@extends('layouts.app')
@section('title', 'Create Staff')
@section('content')
<div class="card">
    <div class="card-header">
        <h5>Create Staff</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('staff.store') }}">
            @csrf

            {{-- Name --}}
            <div class="mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text"
                    name="name"
                    value="{{ old('name') }}"
                    class="form-control @error('name') is-invalid @enderror">
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-3">
                <label>Password <span class="text-danger">*</span></label>
                <input type="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror">
                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Employee ID --}}
            <div class="mb-3">
                <label>Employee ID <span class="text-danger">*</span></label>
                <input type="text"
                    name="employee_id"
                    value="{{ old('employee_id') }}"
                    class="form-control @error('employee_id') is-invalid @enderror">
                @error('employee_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-3">
                <label>Email <span class="text-danger">*</span></label>
                <input type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror">
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Phone --}}
            <div class="mb-3">
                <label>Phone</label>
                <input type="text"
                    name="phone"
                    value="{{ old('phone') }}"
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
                    rows="3">{{ old('address') }}</textarea>
                @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Department --}}
            <div class="mb-3">
                <label>Department <span class="text-danger">*</span></label>
                <select name="department_id"
                    class="form-control @error('department_id') is-invalid @enderror">
                    <option value="">-- Select Department --</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
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
                    value="{{ old('designation') }}"
                    class="form-control @error('designation') is-invalid @enderror">
                @error('designation')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Role --}}
            <div class="mb-3">
                <label>Role <span class="text-danger">*</span></label>
                <select name="role"
                    class="form-control @error('role') is-invalid @enderror">
                    <option value="">-- Select Role --</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role') == $role->id ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                    @endforeach
                </select>
                @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Buttons --}}
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('staff.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>

<script>
    $(document).ready(function() {
        $("form").validate({
            rules: {
                name: {
                    required: true,
                    maxlength: 255
                },
                password: {
                    required: true,
                    minlength: 6
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
                }
            },
            messages: {
                name: {
                    required: "Name is required",
                    maxlength: "Name cannot exceed 255 characters"
                },
                password: {
                    required: "Password is required",
                    minlength: "Password must be at least 6 characters"
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
                }
            },
            errorElement: "span",
            errorClass: "text-danger",
            highlight: function(element) {
                $(element).addClass("is-invalid");
            },
            unhighlight: function(element) {
                $(element).removeClass("is-invalid");
            }
        });
    });
</script>
@endsection