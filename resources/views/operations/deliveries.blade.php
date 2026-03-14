@extends('layouts.app')

@section('page_title', 'Delivery Orders')
@section('page_subtitle', 'Pick, pack, and ship outgoing goods')

@section('content')
@php
    $badgeMap = [
        'draft' => 'badge-waiting',
        'waiting' => 'badge-waiting',
        'ready' => 'badge-ready',
        'done' => 'badge-done',
        'canceled' => 'badge-canceled',
    ];
@endphp

<form class="filter-bar mb-4" method="GET" action="{{ route('deliveries.index') }}">
    <select class="form-select" name="status" style="max-width: 160px;">
        <option value="all">Status</option>
        @foreach(['draft','waiting','ready','done','canceled'] as $status)
            <option value="{{ $status }}" {{ ($filters['status'] ?? 'all') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
        @endforeach
    </select>
    <select class="form-select" name="warehouse_id" style="max-width: 200px;">
        <option value="all">Warehouse</option>
        @foreach($warehouses as $warehouse)
            <option value="{{ $warehouse->id }}" {{ ($filters['warehouse_id'] ?? 'all') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
        @endforeach
    </select>
    <input class="form-control" name="customer" value="{{ $filters['customer'] ?? '' }}" placeholder="Customer" style="max-width: 200px;" />
    <input class="form-control" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" />
    <input class="form-control" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" />
    <button class="btn btn-outline-custom" type="submit">Apply</button>
    <a class="btn btn-outline-custom" href="{{ route('deliveries.index') }}">Reset</a>
</form>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-soft p-4">
            <div class="section-title">Outgoing Deliveries</div>
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>Delivery</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Dispatch</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deliveries as $delivery)
                            <tr>
                                <td>{{ $delivery->number }}</td>
                                <td>{{ $delivery->customer ?? 'Customer' }}</td>
                                <td><span class="badge-status {{ $badgeMap[$delivery->status] ?? 'badge-ready' }}">{{ ucfirst($delivery->status) }}</span></td>
                                <td>
                                    @foreach($delivery->items as $item)
                                        <div>{{ $item->product?->name }} ({{ $item->quantity }})</div>
                                    @endforeach
                                </td>
                                <td>{{ optional($delivery->scheduled_at)->format('M d, H:i') ?? 'N/A' }}</td>
                                <td>
                                    @if($delivery->status !== 'done')
                                        <form method="POST" action="{{ route('deliveries.validate', $delivery) }}">
                                            @csrf
                                            <button class="btn btn-outline-custom btn-sm">Validate</button>
                                        </form>
                                    @else
                                        <span class="tag">Done</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="color: var(--ink-500);">No deliveries yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card-soft form-card">
            <div class="section-title">Create Delivery</div>
            <form method="POST" action="{{ route('deliveries.store') }}" id="create-form-deliveries">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Customer</label>
                    <input class="form-control" name="customer" placeholder="Alto Designs" />
                </div>
                <div class="mb-3">
                    <label class="form-label">Warehouse</label>
                    <select class="form-select" name="warehouse_id" required>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="delivery-items">
                    <div class="row g-2 align-items-end mb-2 delivery-item">
                        <div class="col-7">
                            <label class="form-label">Product</label>
                            <select class="form-select" name="product_id[]" required>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-3">
                            <label class="form-label">Qty</label>
                            <input class="form-control" type="number" min="1" name="quantity[]" placeholder="10" required />
                        </div>
                        <div class="col-2">
                            <button class="btn btn-outline-custom w-100 remove-item" type="button">Remove</button>
                        </div>
                    </div>
                </div>
                <button class="btn btn-outline-custom w-100 mb-3" type="button" id="add-delivery-item">Add Item</button>
                <div class="mb-3">
                    <label class="form-label">Dispatch Date</label>
                    <input type="datetime-local" class="form-control" name="scheduled_at" />
                </div>
                <button class="btn btn-primary-custom w-100">Validate Delivery</button>
            </form>
        </div>
    </div>
</div>

<template id="delivery-item-template">
    <div class="row g-2 align-items-end mb-2 delivery-item">
        <div class="col-7">
            <label class="form-label">Product</label>
            <select class="form-select" name="product_id[]" required>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-3">
            <label class="form-label">Qty</label>
            <input class="form-control" type="number" min="1" name="quantity[]" placeholder="10" required />
        </div>
        <div class="col-2">
            <button class="btn btn-outline-custom w-100 remove-item" type="button">Remove</button>
        </div>
    </div>
</template>

<script>
    const deliveryContainer = document.getElementById('delivery-items');
    const deliveryTemplate = document.getElementById('delivery-item-template');
    document.getElementById('add-delivery-item').addEventListener('click', () => {
        const clone = deliveryTemplate.content.cloneNode(true);
        deliveryContainer.appendChild(clone);
    });
    deliveryContainer.addEventListener('click', (event) => {
        if (event.target.classList.contains('remove-item')) {
            const item = event.target.closest('.delivery-item');
            if (item && deliveryContainer.querySelectorAll('.delivery-item').length > 1) {
                item.remove();
            }
        }
    });
</script>
@endsection
