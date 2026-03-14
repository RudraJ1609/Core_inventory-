@extends('layouts.auth')

@section('content')
<div style="max-width: 420px; margin: auto;">
    <div class="section-title">Enter verification code</div>
    <p style="color: var(--ink-500);">Check your email for a 6-digit OTP to reset your password.</p>
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <form class="mt-4" method="POST" action="{{ route('password.verify') }}">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}" />
        <div class="mb-3">
            <label class="form-label">OTP Code</label>
            <input class="form-control" name="otp" placeholder="123456" required />
        </div>
        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" class="form-control" name="password" placeholder="Create a new password" required />
        </div>
        <button class="btn btn-primary-custom w-100">Update password</button>
    </form>
    <div class="mt-3">
        <a href="{{ route('login') }}" style="text-decoration: none;">Back to login</a>
    </div>
</div>
@endsection
