<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CoreInventory Auth</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/coreinventory.css') }}" rel="stylesheet">
</head>
<body>
    <div class="auth-shell">
        <div class="auth-hero">
            <div class="auth-badge">
                <div class="brand-badge">CI</div>
                <div>
                    <div style="font-size: 18px; font-weight: 700;">CoreInventory</div>
                    <div style="font-size: 12px; opacity: 0.8;">Smart Stock Operations</div>
                </div>
            </div>
            <h1>Operate inventory with absolute clarity.</h1>
            <p class="mt-3" style="font-size: 18px; max-width: 420px;">
                Receive, move, deliver, and adjust stock with reliable workflows, accurate ledgers, and live KPIs.
            </p>
            <div class="mt-3">
                <div style="font-weight: 600;">Target users</div>
                <ul class="list-unstyled" style="opacity: 0.9;">
                    <li>Inventory Managers – manage incoming & outgoing stock</li>
                    <li>Warehouse Staff – perform transfers, picking, shelving, and counting</li>
                </ul>
            </div>
            <div class="mt-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge-status badge-ready">Live KPIs</span>
                    <span class="badge-status badge-waiting">Smart Alerts</span>
                    <span class="badge-status badge-done">Secure Access</span>
                </div>
            </div>
        </div>
        <div class="auth-panel">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-warning">{{ $errors->first() }}</div>
            @endif
            @yield('content')
            <div class="auth-footer">Need help? contact@coreinventory.demo</div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
