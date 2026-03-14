@extends('layouts.app')

@section('page_title', 'Stock Adjustments')
@section('page_subtitle', 'Resolve mismatches between recorded and physical stock')

@section('content')
<form class="filter-bar mb-4" method="GET" action="{{ route('adjustments.index') }}">
    <select class="form-select" name="warehouse_id" style="max-width: 200px;">
        <option value="all">Warehouse</option>
        @foreach($warehouses as $warehouse)
            <option value="{{ $warehouse->id }}" {{ ($filters['warehouse_id'] ?? 'all') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
        @endforeach
    </select>
    <select class="form-select" name="product_id" style="max-width: 200px;">
        <option value="all">Product</option>
        @foreach($products as $product)
            <option value="{{ $product->id }}" {{ ($filters['product_id'] ?? 'all') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
        @endforeach
    </select>
    <input class="form-control" name="reason" value="{{ $filters['reason'] ?? '' }}" placeholder="Reason" style="max-width: 200px;" />
    <input class="form-control" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" />
    <input class="form-control" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" />
    <button class="btn btn-outline-custom" type="submit">Apply</button>
    <a class="btn btn-outline-custom" href="{{ route('adjustments.index') }}">Reset</a>
</form>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-soft p-4">
            <div class="section-title">Adjustment Ledger</div>
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>Adjustment</th>
                            <th>Product</th>
                            <th>Location</th>
                            <th>Delta</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adjustments as $adjustment)
                            <tr>
                                <td>{{ $adjustment->number }}</td>
                                <td>{{ $adjustment->product?->name }}</td>
                                <td>{{ $adjustment->warehouse?->name }}</td>
                                <td>{{ $adjustment->delta_quantity }}</td>
                                <td>{{ $adjustment->reason ?? 'Adjustment' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="color: var(--ink-500);">No adjustments logged.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card-soft form-card">
            <div class="section-title">Record Adjustment</div>
            <form method="POST" action="{{ route('adjustments.store') }}" id="create-form-adjustments">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Product</label>
                    <select class="form-select" name="product_id" required>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Location</label>
                    <select class="form-select" name="warehouse_id" required>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Counted Quantity</label>
                    <input class="form-control" type="number" min="0" name="counted_quantity" placeholder="97" required />
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason</label>
                    <input class="form-control" name="reason" placeholder="Damaged items" />
                </div>
                <button class="btn btn-primary-custom w-100">Post Adjustment</button>
            </form>
        </div>
    </div>
</div>
@endsection
