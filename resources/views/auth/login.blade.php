@extends('layouts.auth')

@section('content')
<div style="max-width: 420px; margin: auto;">
    <div class="section-title">Welcome back</div>
    <p style="color: var(--ink-500);">Sign in to your CoreInventory workspace.</p>
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <form class="mt-4" method="POST" action="{{ route('login.post') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" placeholder="name@company.com" required />
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password" placeholder="••••••••" required />
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember" />
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <a href="{{ route('password.request') }}" style="text-decoration: none;">Forgot password?</a>
        </div>
        <button class="btn btn-primary-custom w-100">Login</button>
    </form>
    <div class="mt-3">
        <span>New to CoreInventory?</span>
        <a href="{{ route('register') }}" style="text-decoration: none;">Create account</a>
    </div>
</div>
@endsection
