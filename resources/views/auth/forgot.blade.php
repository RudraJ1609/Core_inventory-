@extends('layouts.auth')

@section('content')
<div style="max-width: 420px; margin: auto;">
    <div class="section-title">Reset password</div>
    <p style="color: var(--ink-500);">We will send a one-time code to verify your account.</p>
    <form class="mt-4" method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" placeholder="name@company.com" required />
        </div>
        <button class="btn btn-primary-custom w-100">Send OTP</button>
    </form>
    <div class="mt-3">
        <a href="{{ route('login') }}" style="text-decoration: none;">Back to login</a>
    </div>
</div>
@endsection
