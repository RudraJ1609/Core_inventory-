@extends('layouts.auth')

@section('content')
<div style="max-width: 440px; margin: auto;">
    <div class="section-title">Create your account</div>
    <p style="color: var(--ink-500);">Start managing inventory with clear, real-time visibility.</p>
    <div class="card-soft p-3 mb-3" style="border-radius: 14px;">
        <div style="font-weight: 600;">Target users</div>
        <ul class="list-unstyled mb-0" style="color: var(--ink-500);">
            <li>Inventory Managers – manage incoming & outgoing stock</li>
            <li>Warehouse Staff – perform transfers, picking, shelving, and counting</li>
        </ul>
    </div>
    <form class="mt-3" method="POST" action="{{ route('register.post') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">First Name</label>
                <input class="form-control" name="first_name" placeholder="Asha" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Last Name</label>
                <input class="form-control" name="last_name" placeholder="Mehra" required />
            </div>
            <div class="col-12">
                <label class="form-label">Company Email</label>
                <input type="email" class="form-control" name="email" placeholder="name@company.com" required />
            </div>
            <div class="col-12">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" placeholder="Create a strong password" required />
            </div>
        </div>
        <button class="btn btn-primary-custom w-100 mt-4">Create account</button>
    </form>
    <div class="mt-3">
        <span>Already have access?</span>
        <a href="{{ route('login') }}" style="text-decoration: none;">Sign in</a>
    </div>
</div>
@endsection
