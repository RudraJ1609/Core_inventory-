@extends('layouts.app')

@section('page_title', 'My Profile')
@section('page_subtitle', 'Manage your account settings and access')

@section('content')
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card-soft p-4">
            <div class="section-title">Profile Summary</div>
            <div class="d-flex align-items-center gap-3">
                <div class="brand-badge" style="width: 60px; height: 60px;">AM</div>
                <div>
                    <div style="font-weight: 700; font-size: 20px;">Asha Mehra</div>
                    <div style="color: var(--ink-500);">Inventory Manager</div>
                </div>
            </div>
            <div class="mt-4">
                <div class="d-flex justify-content-between py-2">
                    <span>Email</span>
                    <span>asha@coreinventory.com</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span>Warehouse</span>
                    <span>Main Warehouse</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span>Last Active</span>
                    <span>Today, 11:30</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card-soft form-card">
            <div class="section-title">Update Details</div>
            <form>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <input class="form-control" value="Asha" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <input class="form-control" value="Mehra" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input class="form-control" value="asha@coreinventory.com" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <input class="form-control" value="Inventory Manager" />
                    </div>
                    <div class="col-12">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" value="password" />
                    </div>
                </div>
                <button class="btn btn-primary-custom w-100 mt-3">Save Changes</button>
            </form>
        </div>
    </div>
</div>
@endsection
