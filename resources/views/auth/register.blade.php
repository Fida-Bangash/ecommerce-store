@extends('layouts.auth')

@section('title', 'Create Account')
@section('meta_description', 'Create a new admin account')

@section('content')
    <div class="login-wrapper">
        <div class="login-bg-shape login-bg-shape-1"></div>
        <div class="login-bg-shape login-bg-shape-2"></div>

        <div class="login-card">

            <a href="{{ route('home') }}" class="login-brand text-decoration-none">
                <i class="bi bi-asterisk"></i>
                <span>{{ config('app.name') }}</span>
            </a>

            <p class="login-subtitle">Create your admin account</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" id="registerForm" class="needs-validation" novalidate>
                @csrf

                <div class="login-form-group">
                    <label for="name" class="login-form-label">Full Name</label>
                    <div class="login-input-group">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" name="name" id="name" class="login-input" placeholder="John Doe" value="{{ old('name') }}" required autofocus>
                    </div>
                </div>

                <div class="login-form-group">
                    <label for="email" class="login-form-label">Email Address</label>
                    <div class="login-input-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" name="email" id="email" class="login-input" placeholder="name@company.com" value="{{ old('email') }}" required>
                    </div>
                </div>

                <div class="login-form-group">
                    <label for="password" class="login-form-label">Password</label>
                    <div class="login-input-group">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="password" name="password" id="password" class="login-input login-input-password" placeholder="••••••••" required>
                        <button type="button" class="password-toggle-btn" id="toggle-password" aria-label="Show password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="login-form-group">
                    <label for="password_confirmation" class="login-form-label">Confirm Password</label>
                    <div class="login-input-group">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="login-input login-input-password" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="btn-submit">
                    <span>Create Account</span>
                    <i class="bi bi-arrow-right"></i>
                </button>

            </form>

            <p class="login-footer-text">
                Already have an account? <a href="{{ route('login') }}">Sign In</a>
            </p>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/auth.js') }}"></script>
@endpush
