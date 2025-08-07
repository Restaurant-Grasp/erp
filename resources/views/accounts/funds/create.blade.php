@extends('layouts.app')
@section('title', 'Create Fund')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb pl-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('funds.index') }}">Funds</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create Fund</li>
            </ol>
        </nav>
        <br>
        <div class="card shadow-sm">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <div>
                        <h5 class="mb-0">Create New Fund</h5>

                    </div>
                </div>
            </div>

            <div class="card-body">

                <form method="POST" action="{{ route('funds.store') }}" id="fundForm" novalidate>
                    @csrf
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
                                        value="{{ old('code') }}"
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
                                        value="{{ old('name') }}"
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
                                        maxlength="1000">{{ old('description') }}</textarea>


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
                                    <i class="fas fa-save me-2"></i>Create Fund
                                </button>
                                <a href="{{ route('funds.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>


    </div>
</div>

<style>
    .section-title {
        color: #495057;
        font-weight: 600;
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 8px;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
    }

    .card {
        border: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        transition: box-shadow 0.3s ease;
    }

    .input-group-text {
        background-color: #f8f9fa;
        border-color: #ced4da;
    }

    .form-check-input:checked {
        background-color: #28a745;
        border-color: #28a745;
    }

    .badge {
        font-size: 0.75em;
    }

    textarea {
        resize: vertical;
        min-height: 100px;
    }

    #charCount {
        font-size: 0.75rem;
        background: rgba(255, 255, 255, 0.8);
        padding: 2px 6px;
        border-radius: 4px;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #004085;
    }

    @media (max-width: 768px) {
        .d-flex.gap-2.justify-content-end {
            flex-direction: column;
        }

        .d-flex.gap-2.justify-content-end .btn {
            margin-bottom: 0.5rem;
        }
    }
</style>
@endsection