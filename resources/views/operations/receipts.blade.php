@extends('layouts.app')

@section('page_title', 'Receipts')
@section('page_subtitle', 'Capture incoming stock and validate vendor deliveries')

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

<form class="filter-bar mb-4" method="GET" action="{{ route('receipts.index') }}">
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
    <input class="form-control" name="supplier" value="{{ $filters['supplier'] ?? '' }}" placeholder="Supplier" style="max-width: 200px;" />
    <input class="form-control" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" />
    <input class="form-control" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" />
    <button class="btn btn-outline-custom" type="submit">Apply</button>
    <a class="btn btn-outline-custom" href="{{ route('receipts.index') }}">Reset</a>
</form>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-soft p-4">
            <div class="section-title">Incoming Receipts</div>
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>Receipt</th>
                            <th>Supplier</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>ETA</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receipts as $receipt)
                            <tr>
                                <td>{{ $receipt->number }}</td>
                                <td>{{ $receipt->supplier ?? 'Vendor' }}</td>
                                <td><span class="badge-status {{ $badgeMap[$receipt->status] ?? 'badge-ready' }}">{{ ucfirst($receipt->status) }}</span></td>
                                <td>
                                    @foreach($receipt->items as $item)
                                        <div>{{ $item->product?->name }} ({{ $item->quantity }})</div>
                                    @endforeach
                                </td>
                                <td>{{ optional($receipt->scheduled_at)->format('M d, H:i') ?? 'N/A' }}</td>
                                <td>
                                    @if($receipt->status !== 'done')
                                        <form method="POST" action="{{ route('receipts.validate', $receipt) }}">
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
                                <td colspan="6" style="color: var(--ink-500);">No receipts yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card-soft form-card">
            <div class="section-title">Create Receipt</div>
            <form method="POST" action="{{ route('receipts.store') }}" id="create-form-receipts">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Supplier</label>
                    <input class="form-control" name="supplier" placeholder="Zenith Metals" />
                </div>
                <div class="mb-3">
                    <label class="form-label">Warehouse</label>
                    <select class="form-select" name="warehouse_id" required>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="receipt-items">
                    <div class="row g-2 align-items-end mb-2 receipt-item">
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
                            <input class="form-control" type="number" min="1" name="quantity[]" placeholder="50" required />
                        </div>
                        <div class="col-2">
                            <button class="btn btn-outline-custom w-100 remove-item" type="button">Remove</button>
                        </div>
                    </div>
                </div>
                <button class="btn btn-outline-custom w-100 mb-3" type="button" id="add-receipt-item">Add Item</button>
                <div class="mb-3">
                    <label class="form-label">Expected Date</label>
                    <input type="datetime-local" class="form-control" name="scheduled_at" />
                </div>
                <button class="btn btn-primary-custom w-100">Validate Receipt</button>
            </form>
        </div>
    </div>
</div>

<template id="receipt-item-template">
    <div class="row g-2 align-items-end mb-2 receipt-item">
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
            <input class="form-control" type="number" min="1" name="quantity[]" placeholder="50" required />
        </div>
        <div class="col-2">
            <button class="btn btn-outline-custom w-100 remove-item" type="button">Remove</button>
        </div>
    </div>
</template>

<script>
    const receiptContainer = document.getElementById('receipt-items');
    const receiptTemplate = document.getElementById('receipt-item-template');
    document.getElementById('add-receipt-item').addEventListener('click', () => {
        const clone = receiptTemplate.content.cloneNode(true);
        receiptContainer.appendChild(clone);
    });
    receiptContainer.addEventListener('click', (event) => {
        if (event.target.classList.contains('remove-item')) {
            const item = event.target.closest('.receipt-item');
            if (item && receiptContainer.querySelectorAll('.receipt-item').length > 1) {
                item.remove();
            }
        }
    });
</script>
@endsection
