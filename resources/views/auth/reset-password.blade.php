@extends('layouts.auth')

@section('title', 'Reset Password')
@section('meta_description', 'Set a new password for your admin account')

@section('content')
    <div class="login-wrapper">
        <div class="login-bg-shape login-bg-shape-1"></div>
        <div class="login-bg-shape login-bg-shape-2"></div>

        <div class="login-card">

            <a href="{{ route('home') }}" class="login-brand text-decoration-none">
                <i class="bi bi-asterisk"></i>
                <span>{{ config('app.name') }}</span>
            </a>

            <p class="login-subtitle">Choose a new password for your account</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('password.store') }}" method="POST" id="resetPasswordForm" class="needs-validation" novalidate>
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="login-form-group">
                    <label for="email" class="login-form-label">Email Address</label>
                    <div class="login-input-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" name="email" id="email" class="login-input" placeholder="name@company.com" value="{{ old('email', $request->email) }}" required autofocus>
                    </div>
                </div>

                <div class="login-form-group">
                    <label for="password" class="login-form-label">New Password</label>
                    <div class="login-input-group">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="password" name="password" id="password" class="login-input login-input-password" placeholder="••••••••" required>
                        <button type="button" class="password-toggle-btn" id="toggle-password" aria-label="Show password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="login-form-group">
                    <label for="password_confirmation" class="login-form-label">Confirm New Password</label>
                    <div class="login-input-group">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="login-input login-input-password" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="btn-submit">
                    <span>Reset Password</span>
                    <i class="bi bi-arrow-right"></i>
                </button>

            </form>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/auth.js') }}"></script>
@endpush
