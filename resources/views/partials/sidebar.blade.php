@php
    $routeName = request()->route() ? request()->route()->getName() : '';
@endphp
<div class="sidebar">
    <div class="brand">
        <div class="brand-badge">CI</div>
        <div>
            <div style="font-size: 18px;">CoreInventory</div>
            <div style="font-size: 12px; color: rgba(234, 241, 255, 0.7);">IMS Control Center</div>
        </div>
    </div>

    <nav>
        <div class="nav-section">Overview</div>
        <a class="nav-link-custom {{ $routeName === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <span><i class="bi bi-speedometer2" style="margin-right: 8px;"></i>Dashboard</span>
            <span class="nav-pill">Live</span>
        </a>

        <div class="nav-section">Inventory</div>
        <a class="nav-link-custom {{ str_starts_with($routeName ?? '', 'products') ? 'active' : '' }}" href="{{ route('products.index') }}"><span><i class="bi bi-box" style="margin-right: 8px;"></i>Products</span></a>
        <a class="nav-link-custom {{ $routeName === 'receipts.index' ? 'active' : '' }}" href="{{ route('receipts.index') }}"><span><i class="bi bi-truck" style="margin-right: 8px;"></i>Receipts</span></a>
        <a class="nav-link-custom {{ $routeName === 'deliveries.index' ? 'active' : '' }}" href="{{ route('deliveries.index') }}"><span><i class="bi bi-send" style="margin-right: 8px;"></i>Delivery Orders</span></a>
        <a class="nav-link-custom {{ $routeName === 'adjustments.index' ? 'active' : '' }}" href="{{ route('adjustments.index') }}"><span><i class="bi bi-sliders" style="margin-right: 8px;"></i>Stock Adjustments</span></a>
        <a class="nav-link-custom {{ $routeName === 'moves.index' ? 'active' : '' }}" href="{{ route('moves.index') }}"><span><i class="bi bi-arrow-left-right" style="margin-right: 8px;"></i>Move History</span></a>
        <a class="nav-link-custom {{ $routeName === 'reports.index' ? 'active' : '' }}" href="{{ route('reports.index') }}"><span><i class="bi bi-graph-up" style="margin-right: 8px;"></i>Reports</span></a>

        <div class="nav-section">Admin</div>
        <a class="nav-link-custom {{ $routeName === 'settings.warehouses' ? 'active' : '' }}" href="{{ route('settings.warehouses') }}"><span><i class="bi bi-geo" style="margin-right: 8px;"></i>Warehouses</span></a>
        <a class="nav-link-custom {{ $routeName === 'profile' ? 'active' : '' }}" href="{{ route('profile') }}"><span><i class="bi bi-person" style="margin-right: 8px;"></i>My Profile</span></a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-link-custom" style="border: none; background: none; width: 100%; text-align: left;">
                <span><i class="bi bi-box-arrow-right" style="margin-right: 8px;"></i>Logout</span>
            </button>
        </form>
    </nav>
</div>
