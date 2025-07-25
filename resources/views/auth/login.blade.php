@extends('layouts.guest')

@section('content')
<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-md-5">
            <div class="text-center mb-4">
                <div class="mb-4 mt-5">
                    <img src="{{ asset('assets/images/grasp_logo.png') }}" alt="Logo" />
                </div>
                <h2 class="fw-bold">ERP System</h2>
                <p class="text-muted">Sign in to your account</p>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="login" class="form-label">{{ __('Username or Email') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input id="login" type="text" class="form-control @error('login') is-invalid @enderror" 
                                       name="login" value="{{ old('login') }}" required autocomplete="username" autofocus
                                       placeholder="Enter username or email">
                            </div>
                            @error('login')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('Password') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                       name="password" required autocomplete="current-password"
                                       placeholder="Enter password">
                            </div>
                            @error('password')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" 
                                       {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>
                        </div>

                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                {{ __('Login') }}
                            </button>
                        </div>

                        @if (Route::has('password.request'))
                            <div class="text-center mt-3">
                                <a class="btn btn-link text-decoration-none" href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            <div class="text-center mt-4 mb-5">
                <div class="text-muted small">
                    &copy; {{ date('Y') }} ERP System. All rights reserved.
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    body {
        background-color: #f5f7fa;
    }
    .btn-primary {
        background-color: #00A551;
        border-color: #00A551;
    }
    .btn-primary:hover {
        background-color: #008741;
        border-color: #008741;
    }
    .btn-link {
        color: #00A551;
    }
    .btn-link:hover {
        color: #008741;
    }
    .form-control:focus {
        border-color: #00A551;
        box-shadow: 0 0 0 0.2rem rgba(0, 165, 81, 0.25);
    }
    .form-check-input:checked {
        background-color: #00A551;
        border-color: #00A551;
    }
</style>
@endsection