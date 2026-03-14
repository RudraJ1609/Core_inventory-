@extends('layouts.app')

@section('page_title', 'Warehouses')
@section('page_subtitle', 'Manage warehouse locations and storage capacity')

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-soft p-4">
            <div class="section-title">Active Warehouses</div>
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>Warehouse</th>
                            <th>Code</th>
                            <th>Location</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warehouses as $warehouse)
                            <tr>
                                <td>{{ $warehouse->name }}</td>
                                <td>{{ $warehouse->code ?? 'N/A' }}</td>
                                <td>{{ $warehouse->location ?? 'N/A' }}</td>
                                <td><span class="badge-status badge-done">{{ $warehouse->is_active ? 'Active' : 'Inactive' }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="color: var(--ink-500);">No warehouses yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-soft form-card">
            <div class="section-title">Add Warehouse</div>
            <form method="POST" action="{{ route('settings.warehouses.store') }}" id="create-form-warehouses">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Warehouse Name</label>
                    <input class="form-control" name="name" placeholder="Warehouse 3" required />
                </div>
                <div class="mb-3">
                    <label class="form-label">Code</label>
                    <input class="form-control" name="code" placeholder="WH-03" />
                </div>
                <div class="mb-3">
                    <label class="form-label">Location</label>
                    <input class="form-control" name="location" placeholder="Navi Mumbai" />
                </div>
                <button class="btn btn-primary-custom w-100">Save Warehouse</button>
            </form>
        </div>
    </div>
</div>
@endsection
