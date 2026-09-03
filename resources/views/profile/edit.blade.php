@extends('layouts.admin')

@section('title', 'My Account')
@section('meta_description', 'Manage your account information and password')

@section('page_title', 'My Account')
@section('page_subtitle', 'Update your profile information and security settings.')
@section('page_header_actions')
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted-green">Home</a></li>
      <li class="breadcrumb-item active text-main" aria-current="page">My Account</li>
    </ol>
  </nav>
@endsection

@section('content')

    <div class="row g-4">

        {{-- Profile Information --}}
        <div class="col-lg-6">
            <div class="card p-4 border-light shadow-sm h-100">
                <h5 class="mb-3">Profile Information</h5>
                <p class="text-muted-green mb-4">Update your account's name and email address.</p>

                @if (session('status') === 'profile-updated')
                    <div class="alert alert-success">Your profile has been updated.</div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('patch')

                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="alert alert-warning">
                            Your email address is unverified.
                            <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link p-0 align-baseline">Click here to re-send the verification email.</button>
                            </form>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>

        {{-- Update Password --}}
        <div class="col-lg-6">
            <div class="card p-4 border-light shadow-sm h-100">
                <h5 class="mb-3">Update Password</h5>
                <p class="text-muted-green mb-4">Use a long, random password to keep your account secure.</p>

                @if (session('status') === 'password-updated')
                    <div class="alert alert-success">Your password has been updated.</div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" name="current_password" id="current_password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password">
                        @error('current_password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" name="password" id="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
                        @error('password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
        </div>

        {{-- Delete Account --}}
        <div class="col-lg-12">
            <div class="card p-4 border-light shadow-sm">
                <h5 class="mb-3 text-danger">Delete Account</h5>
                <p class="text-muted-green mb-4">
                    Once your account is deleted, all of its resources and data will be permanently removed.
                    Please enter your password to confirm you would like to permanently delete your account.
                </p>

                <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                    @csrf
                    @method('delete')

                    <div class="mb-3 col-md-6">
                        <label for="delete_password" class="form-label">Password</label>
                        <input type="password" name="password" id="delete_password" class="form-control @error('password', 'userDeletion') is-invalid @enderror">
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-danger">Delete Account</button>
                </form>
            </div>
        </div>

    </div>

@endsection
