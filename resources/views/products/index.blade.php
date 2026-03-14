@extends('layouts.app')

@section('page_title', 'Products')
@section('page_subtitle', 'Create, categorize, and monitor stock availability per location')

@section('content')
<form class="filter-bar mb-4" method="GET" action="{{ route('products.index') }}">
    <select class="form-select" name="category" style="max-width: 200px;">
        <option value="all">Category</option>
        @foreach($categoryCounts as $category => $count)
            <option value="{{ $category }}" {{ ($filters['category'] ?? 'all') === $category ? 'selected' : '' }}>{{ $category }}</option>
        @endforeach
    </select>
    <select class="form-select" name="warehouse_id" style="max-width: 200px;">
        <option value="all">Warehouse</option>
        @foreach($warehouses as $warehouse)
            <option value="{{ $warehouse->id }}" {{ ($filters['warehouse_id'] ?? 'all') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
        @endforeach
    </select>
    <select class="form-select" name="stock_status" style="max-width: 180px;">
        <option value="all">Stock Status</option>
        <option value="low" {{ ($filters['stock_status'] ?? '') === 'low' ? 'selected' : '' }}>Low Stock</option>
        <option value="out" {{ ($filters['stock_status'] ?? '') === 'out' ? 'selected' : '' }}>Out of Stock</option>
        <option value="healthy" {{ ($filters['stock_status'] ?? '') === 'healthy' ? 'selected' : '' }}>Healthy</option>
    </select>
    <input class="form-control" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search by SKU or name" style="max-width: 220px;" />
    <button class="btn btn-outline-custom" type="submit">Apply</button>
    <a class="btn btn-outline-custom" href="{{ route('products.index') }}">Reset</a>
</form>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-soft p-4">
            <div class="section-title">Product Catalog</div>
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>On Hand</th>
                            <th>Reorder Rule</th>
                            <th>By Location</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->sku }}</td>
                                <td>{{ $product->category ?? 'Uncategorized' }}</td>
                                <td>{{ $product->total_stock ?? 0 }} {{ $product->unit_of_measure ?? 'units' }}</td>
                                <td>Reorder below {{ $product->reorder_point }}</td>
                                <td>
                                    @forelse($product->inventories as $inventory)
                                        <div>{{ $inventory->warehouse?->name }}: {{ $inventory->quantity }}</div>
                                    @empty
                                        <div style="color: var(--ink-500);">No stock yet</div>
                                    @endforelse
                                </td>
                                <td>
                                    <a class="btn btn-outline-custom btn-sm" href="{{ route('products.edit', $product) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="color: var(--ink-500);">No products yet. Create the first one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-soft form-card mb-4">
            <div class="section-title">Create Product</div>
            <form method="POST" action="{{ route('products.store') }}" id="create-form-products">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input class="form-control" name="name" placeholder="Steel Rods" required />
                </div>
                <div class="mb-3">
                    <label class="form-label">SKU / Code</label>
                    <input class="form-control" name="sku" placeholder="STL-204" required />
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <input class="form-control" name="category" placeholder="Raw Materials" />
                </div>
                <div class="mb-3">
                    <label class="form-label">Unit of Measure</label>
                    <input class="form-control" name="unit_of_measure" placeholder="kg / units" />
                </div>
                <div class="mb-3">
                    <label class="form-label">Initial Stock (optional)</label>
                    <input class="form-control" name="initial_stock" placeholder="0" type="number" min="0" />
                </div>
                <div class="mb-3">
                    <label class="form-label">Reorder Point</label>
                    <input class="form-control" name="reorder_point" placeholder="20" type="number" min="0" />
                </div>
                <button class="btn btn-primary-custom w-100">Save Product</button>
            </form>
        </div>
        <div class="card-soft p-4">
            <div class="section-title">Categories</div>
            <ul class="list-unstyled mb-0">
                @forelse($categoryCounts as $category => $count)
                    <li class="d-flex justify-content-between py-2">
                        <span>{{ $category }}</span>
                        <span class="tag">{{ $count }}</span>
                    </li>
                @empty
                    <li style="color: var(--ink-500);">No categories yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
