@extends('layouts.app')
@section('title', 'Edit Fund')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb pl-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('funds.index') }}">Funds</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Fund</li>
            </ol>
        </nav>
        <br>
        <div class="card shadow-sm">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">

                        <div>
                            <h5 class="mb-0">Edit Fund</h5>


                        </div>
                    </div>

                </div>
            </div>

            <div class="card-body">


                <!-- Fund Usage Warning -->
                @php
                $hasTransactions = isset($fund->entry_count) && $fund->entry_count > 0;
                $isDefaultFund = $fund->id == 1;
                @endphp



                <form method="POST" action="{{ route('funds.update', $fund->id) }}" id="fundForm" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="code" class="form-label">
                                    Fund Code <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control @error('code') is-invalid @enderror"
                                        id="code"
                                        name="code"
                                        value="{{ old('code', $fund->code) }}"
                                        required
                                        maxlength="250">
                                </div>
                                @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">

                                    Unique code for the fund
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="name" class="form-label">
                                    Fund Name <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                   
                                    <input type="text"
                                        class="form-control @error('name') is-invalid @enderror"
                                        id="name"
                                        name="name"
                                        value="{{ old('name', $fund->name) }}"
                                        required
                                        maxlength="350">
                                </div>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-4">
                                <label for="description" class="form-label">Description</label>
                                <div class="position-relative">
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description"
                                        name="description"
                                        rows="4"
                                        maxlength="1000">{{ old('description', $fund->description) }}</textarea>
                                    <div class="position-absolute bottom-0 end-0 p-2">
                                        <small class="text-muted" id="charCount">{{ strlen($fund->description ?? '') }}/1000</small>
                                    </div>
                                </div>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>
                        </div>
                    </div>
                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-2"></i>Update Fund
                                </button>
                                <a href="{{ route('funds.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                @endsection