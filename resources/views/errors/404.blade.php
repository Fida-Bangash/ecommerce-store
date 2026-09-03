@extends('layouts.auth')

@section('title', '404 Page Not Found')
@section('meta_description', '404 Page Not Found')

@section('content')
    {{-- ==========================================
         START: 404 Error Page Container & Card
         ========================================== --}}
    <div class="login-wrapper">
        <div class="login-bg-shape login-bg-shape-1"></div>
        <div class="login-bg-shape login-bg-shape-2"></div>

        <div class="login-card text-center">

            <a href="{{ route('dashboard') }}" class="login-brand text-decoration-none">
                <i class="bi bi-asterisk"></i>
                <span>{{ config('app.name') }}</span>
            </a>

            <div class="error-title-huge">
                <span>4</span>
                <i class="bi bi-asterisk"></i>
                <span>4</span>
            </div>

            <h2 class="error-subtitle">Page Not Found</h2>
            <p class="error-desc">
                The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
            </p>

            <div class="error-actions-group">
                <a href="{{ route('dashboard') }}" class="btn-custom btn-custom-primary">
                    <i class="bi bi-house"></i> Back to Dashboard
                </a>
            </div>

        </div>
    </div>
    {{-- END: 404 Error Page Container --}}
@endsection
