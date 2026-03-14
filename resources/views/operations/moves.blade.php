@extends('layouts.app')

@section('page_title', 'Move History')
@section('page_subtitle', 'Track internal transfers and location changes')

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

<form class="filter-bar mb-4" method="GET" action="{{ route('moves.index') }}">
    <select class="form-select" name="status" style="max-width: 160px;">
        <option value="all">Status</option>
        @foreach(['draft','waiting','ready','done','canceled'] as $status)
            <option value="{{ $status }}" {{ ($filters['status'] ?? 'all') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
        @endforeach
    </select>
    <select class="form-select" name="from_warehouse_id" style="max-width: 200px;">
        <option value="all">From Warehouse</option>
        @foreach($warehouses as $warehouse)
            <option value="{{ $warehouse->id }}" {{ ($filters['from_warehouse_id'] ?? 'all') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
        @endforeach
    </select>
    <select class="form-select" name="to_warehouse_id" style="max-width: 200px;">
        <option value="all">To Warehouse</option>
        @foreach($warehouses as $warehouse)
            <option value="{{ $warehouse->id }}" {{ ($filters['to_warehouse_id'] ?? 'all') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
        @endforeach
    </select>
    <input class="form-control" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" />
    <input class="form-control" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" />
    <button class="btn btn-outline-custom" type="submit">Apply</button>
    <a class="btn btn-outline-custom" href="{{ route('moves.index') }}">Reset</a>
</form>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-soft p-4">
            <div class="section-title">Internal Transfers</div>
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>Transfer</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($moves as $move)
                            <tr>
                                <td>{{ $move->number }}</td>
                                <td>{{ $move->fromWarehouse?->name }}</td>
                                <td>{{ $move->toWarehouse?->name }}</td>
                                <td>
                                    @foreach($move->items as $item)
                                        <div>{{ $item->product?->name }} ({{ $item->quantity }})</div>
                                    @endforeach
                                </td>
                                <td><span class="badge-status {{ $badgeMap[$move->status] ?? 'badge-ready' }}">{{ ucfirst($move->status) }}</span></td>
                                <td>
                                    @if($move->status !== 'done')
                                        <form method="POST" action="{{ route('moves.complete', $move) }}">
                                            @csrf
                                            <button class="btn btn-outline-custom btn-sm">Complete</button>
                                        </form>
                                    @else
                                        <span class="tag">Done</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="color: var(--ink-500);">No transfers yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card-soft form-card">
            <div class="section-title">Schedule Transfer</div>
            <form method="POST" action="{{ route('moves.store') }}" id="create-form-moves">
                @csrf
                <div class="mb-3">
                    <label class="form-label">From Location</label>
                    <select class="form-select" name="from_warehouse_id" required>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">To Location</label>
                    <select class="form-select" name="to_warehouse_id" required>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="move-items">
                    <div class="row g-2 align-items-end mb-2 move-item">
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
                            <input class="form-control" type="number" min="1" name="quantity[]" placeholder="100" required />
                        </div>
                        <div class="col-2">
                            <button class="btn btn-outline-custom w-100 remove-item" type="button">Remove</button>
                        </div>
                    </div>
                </div>
                <button class="btn btn-outline-custom w-100 mb-3" type="button" id="add-move-item">Add Item</button>
                <div class="mb-3">
                    <label class="form-label">Scheduled Date</label>
                    <input type="datetime-local" class="form-control" name="scheduled_at" />
                </div>
                <button class="btn btn-primary-custom w-100">Create Transfer</button>
            </form>
        </div>
    </div>
</div>

<template id="move-item-template">
    <div class="row g-2 align-items-end mb-2 move-item">
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
            <input class="form-control" type="number" min="1" name="quantity[]" placeholder="100" required />
        </div>
        <div class="col-2">
            <button class="btn btn-outline-custom w-100 remove-item" type="button">Remove</button>
        </div>
    </div>
</template>

<script>
    const moveContainer = document.getElementById('move-items');
    const moveTemplate = document.getElementById('move-item-template');
    document.getElementById('add-move-item').addEventListener('click', () => {
        const clone = moveTemplate.content.cloneNode(true);
        moveContainer.appendChild(clone);
    });
    moveContainer.addEventListener('click', (event) => {
        if (event.target.classList.contains('remove-item')) {
            const item = event.target.closest('.move-item');
            if (item && moveContainer.querySelectorAll('.move-item').length > 1) {
                item.remove();
            }
        }
    });
</script>
@endsection
