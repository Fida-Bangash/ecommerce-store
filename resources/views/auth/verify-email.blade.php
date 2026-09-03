@extends('layouts.auth')

@section('title', 'Verify Email')
@section('meta_description', 'Verify your admin account email address')

@section('content')
    <div class="login-wrapper">
        <div class="login-bg-shape login-bg-shape-1"></div>
        <div class="login-bg-shape login-bg-shape-2"></div>

        <div class="login-card">

            <a href="{{ route('home') }}" class="login-brand text-decoration-none">
                <i class="bi bi-asterisk"></i>
                <span>{{ config('app.name') }}</span>
            </a>

            <p class="login-subtitle">
                Thanks for signing up! Before getting started, please verify your email address by
                clicking the link we just emailed to you. If you didn't receive the email, we can send you another.
            </p>

            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success">
                    A new verification link has been sent to the email address you provided during registration.
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn-login">
                    <span>Resend Verification Email</span>
                    <i class="bi bi-arrow-repeat"></i>
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="login-footer-text" style="background:none;border:0;">
                    Log Out
                </button>
            </form>

        </div>
    </div>
@endsection
