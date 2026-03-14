<div class="topbar">
    <div>
        <div style="font-size: 24px; font-weight: 700;">@yield('page_title')</div>
        <div style="color: var(--ink-500);">@yield('page_subtitle')</div>
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="input-group" style="max-width: 360px;">
            <span class="input-group-text" style="border-radius: 14px 0 0 14px; border-right: none;"><i class="bi bi-search"></i></span>
            <input class="form-control" placeholder="Search SKU, receipt, delivery" style="border-radius: 0 14px 14px 0;" />
        </div>
        <div class="dropdown">
            <button class="btn btn-primary-custom dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-plus-lg"></i> Create
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('products.index') }}" data-target="create-form-products">Product</a></li>
                <li><a class="dropdown-item" href="{{ route('receipts.index') }}" data-target="create-form-receipts">Receipt</a></li>
                <li><a class="dropdown-item" href="{{ route('deliveries.index') }}" data-target="create-form-deliveries">Delivery Order</a></li>
                <li><a class="dropdown-item" href="{{ route('moves.index') }}" data-target="create-form-moves">Internal Transfer</a></li>
                <li><a class="dropdown-item" href="{{ route('adjustments.index') }}" data-target="create-form-adjustments">Stock Adjustment</a></li>
                <li><a class="dropdown-item" href="{{ route('settings.warehouses') }}" data-target="create-form-warehouses">Warehouse</a></li>
            </ul>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="brand-badge" style="width: 36px; height: 36px;">{{ strtoupper(substr(auth()->user()->name ?? 'CI', 0, 2)) }}</div>
            <div>
                <div style="font-weight: 600;">{{ auth()->user()->name ?? 'Inventory Manager' }}</div>
                <div style="font-size: 12px; color: var(--ink-500);">Logged in</div>
            </div>
        </div>
    </div>
</div>
