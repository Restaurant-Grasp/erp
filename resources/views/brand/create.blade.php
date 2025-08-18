@extends('layouts.app')
@section('title', 'Create Brand')
@section('content')
<div class="card">
    <div class="card-header">
        <h5>Create Brand</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('brand.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                @error('name')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Code</label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}">
                @error('code')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label>Logo</label>
                <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror">
                <small class="text-muted">Supported formats: jpg, png, svg, gif.</small>
                @error('logo')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>
            
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('brand.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection