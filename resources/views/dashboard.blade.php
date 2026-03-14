@extends('layouts.app')

@section('page_title', 'Inventory Dashboard')
@section('page_subtitle', 'Real-time snapshot of stock operations across all warehouses')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card-soft metric-card">
            <div class="kpi-row">
                <div>
                    <div class="metric-label">Total Products in Stock</div>
                    <div class="metric-value">{{ number_format($totalStock ?? 0) }}</div>
                </div>
                <div class="kpi-icon"><i class="bi bi-box-seam"></i></div>
            </div>
            <div class="metric-delta">Across all warehouses</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-soft metric-card">
            <div class="kpi-row">
                <div>
                    <div class="metric-label">Low / Out of Stock</div>
                    <div class="metric-value">{{ $lowStockCount ?? 0 }}</div>
                </div>
                <div class="kpi-icon" style="background: rgba(240, 162, 2, 0.15); color: #b87400;"><i class="bi bi-exclamation-triangle"></i></div>
            </div>
            <div class="metric-delta">Reorder attention</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-soft metric-card">
            <div class="kpi-row">
                <div>
                    <div class="metric-label">Pending Receipts</div>
                    <div class="metric-value">{{ $pendingReceipts ?? 0 }}</div>
                </div>
                <div class="kpi-icon" style="background: rgba(45, 140, 255, 0.12); color: var(--sky-500);"><i class="bi bi-truck"></i></div>
            </div>
            <div class="metric-delta">Awaiting validation</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-soft metric-card">
            <div class="kpi-row">
                <div>
                    <div class="metric-label">Pending Deliveries</div>
                    <div class="metric-value">{{ $pendingDeliveries ?? 0 }}</div>
                </div>
                <div class="kpi-icon" style="background: rgba(124, 198, 58, 0.15); color: #4d8f2a;"><i class="bi bi-send"></i></div>
            </div>
            <div class="metric-delta">Queued for dispatch</div>
        </div>
    </div>
</div>

<div class="filter-bar mb-4">
    <select class="form-select" style="max-width: 180px;">
        <option>Document Type</option>
        <option>Receipts</option>
        <option>Delivery</option>
        <option>Internal</option>
        <option>Adjustments</option>
    </select>
    <select class="form-select" style="max-width: 160px;">
        <option>Status</option>
        <option>Draft</option>
        <option>Waiting</option>
        <option>Ready</option>
        <option>Done</option>
        <option>Canceled</option>
    </select>
    <select class="form-select" style="max-width: 180px;">
        <option>Warehouse</option>
        <option>Main Warehouse</option>
        <option>Production Floor</option>
        <option>Warehouse 2</option>
    </select>
    <select class="form-select" style="max-width: 180px;">
        <option>Category</option>
        <option>Raw Materials</option>
        <option>Finished Goods</option>
        <option>Packaging</option>
    </select>
    <input type="date" class="form-control" style="max-width: 170px;">
    <button class="btn btn-outline-custom">Apply</button>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-soft section-card">
            <div class="section-title">Operational Timeline</div>
            <div class="d-flex flex-column gap-3">
                @forelse($recentOperations ?? [] as $entry)
                    <div class="card-soft p-3 border-0" style="background: #f7fbff;">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div style="font-weight: 600;">{{ ucfirst(str_replace('_', ' ', $entry->type)) }}</div>
                                <div style="color: var(--ink-500);">{{ $entry->product?->name }} · {{ $entry->warehouse?->name }}</div>
                            </div>
                            <span class="badge-status badge-done">Logged</span>
                        </div>
                        <div class="mt-2 d-flex gap-2">
                            <span class="tag">Change: {{ $entry->quantity_change }}</span>
                            <span class="tag">Balance: {{ $entry->balance_after }}</span>
                            <span class="tag">{{ optional($entry->occurred_at)->format('M d, H:i') }}</span>
                        </div>
                    </div>
                @empty
                    <div style="color: var(--ink-500);">No operations yet.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card-soft section-card mb-4">
            <div class="section-title">Low Stock Alerts</div>
            <ul class="list-unstyled mb-0 list-compact">
                @forelse($lowStockItems ?? [] as $product)
                    <li class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-weight: 600;">{{ $product->name }}</div>
                            <div style="color: var(--ink-500);">SKU: {{ $product->sku }}</div>
                        </div>
                        <span class="badge-status badge-waiting">{{ $product->total_stock ?? 0 }} {{ $product->unit_of_measure ?? 'units' }}</span>
                    </li>
                @empty
                    <li style="color: var(--ink-500);">No low stock alerts right now.</li>
                @endforelse
            </ul>
        </div>
        <div class="card-soft section-card">
            <div class="section-title">Internal Transfers Scheduled</div>
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div style="font-weight: 600;">Transfers in queue</div>
                    <div style="color: var(--ink-500);">{{ $internalTransfersScheduled ?? 0 }} awaiting completion</div>
                </div>
                <span class="badge-status badge-ready">Today</span>
            </div>
        </div>
    </div>
</div>

<div class="card-soft section-card mt-4">
    <div class="section-title">Recent Operations</div>
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Warehouse</th>
                    <th>Product</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOperations ?? [] as $entry)
                    <tr>
                        <td>#{{ $entry->reference_id ?? $entry->id }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $entry->type)) }}</td>
                        <td><span class="badge-status badge-done">Logged</span></td>
                        <td>{{ $entry->warehouse?->name ?? 'N/A' }}</td>
                        <td>{{ $entry->product?->name ?? 'Product' }}</td>
                        <td>{{ optional($entry->occurred_at)->format('M d, H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="color: var(--ink-500);">No operations yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
