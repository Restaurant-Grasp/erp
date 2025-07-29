@extends('layouts.app')
@section('title', 'Create Staff')
@section('content')
<div class="card">
    <div class="card-header"><h5>Create Staff</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('staff.store') }}">
            @csrf
            <div class="mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
             <label>Password <span class="text-danger">*</span></label>
              <input type="password" name="password" class="form-control" required>
             </div>
            <div class="mb-3">
                <label>Employee ID <span class="text-danger">*</span></label>
                <input type="text" name="employee_id" class="form-control" required>
            </div>
           <div class="mb-3">
                <label>Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" required>
            </div>
          <div class="mb-3">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control">
            </div>
               <div class="mb-3">
                <label>Address</label>
                <textarea name="address" class="form-control" rows="3"></textarea>

            </div>
            <div class="mb-3">
                <label>Department <span class="text-danger">*</span></label>
                <select name="department_id" class="form-control" required>
                    <option value="">-- Select Department --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label>Designation</label>
                <input type="text" name="designation" class="form-control">
            </div>
            <div class="mb-3">
                <label>Role <span class="text-danger">*</span></label>
                <select name="role" class="form-control" required>
                    <option value="">-- Select Role --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div> 
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('staff.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
