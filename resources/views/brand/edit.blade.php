@extends('layouts.app')
@section('title', 'Edit Brand')
@section('content')
<div class="card">
    <div class="card-header"><h5>Edit Brand</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('brand.update', $brand) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ $brand->name }}" required>
            </div>
            <div class="mb-3">
                <label>Code</label>
                <input type="text" name="code" class="form-control" value="{{ $brand->code }}">
            </div>
      
            <div class="mb-3">
                <label>Logo</label><br>
                <input type="file" name="logo" class="form-control">
                <small class="text-muted">Supported formats: jpg, png, svg, gif.</small><br>
                @if ($brand->logo)
                    <img src="{{ asset('assets/' . $brand->logo) }}" width="100" class="mb-2"><br>
                @endif
            </div>

            <button type="submit" class="btn btn-success">Update</button>
            <a href="{{ route('brand.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
@endsection