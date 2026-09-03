@extends('layouts.auth')

@section('title', 'Forgot Password')
@section('meta_description', 'Reset your admin account password')

@section('content')
    <div class="login-wrapper">
        <div class="login-bg-shape login-bg-shape-1"></div>
        <div class="login-bg-shape login-bg-shape-2"></div>

        <div class="login-card">

            <a href="{{ route('home') }}" class="login-brand text-decoration-none">
                <i class="bi bi-asterisk"></i>
                <span>{{ config('app.name') }}</span>
            </a>

            <p class="login-subtitle">Enter your email and we will send you a password reset link</p>

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" id="forgotPasswordForm" class="needs-validation" novalidate>
                @csrf

                <div class="login-form-group">
                    <label for="email" class="login-form-label">Email Address</label>
                    <div class="login-input-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" name="email" id="email" class="login-input" placeholder="name@company.com" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="btn-submit">
                    <span>Send Reset Link</span>
                    <i class="bi bi-arrow-right"></i>
                </button>

            </form>

            <p class="login-footer-text">
                Remembered your password? <a href="{{ route('login') }}">Back to Sign In</a>
            </p>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/auth.js') }}"></script>
@endpush
