<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Meoxstore Admin') | Meoxstore</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --ink: #1c3035; --muted: #718186; --line: #dce4e4; --canvas: #f3f6f5; --accent: #d66d4d; --rail: #18343a; }
        body { background: var(--canvas); color: var(--ink); font-family: 'Poppins', sans-serif; font-size: 14px; }
        body :is(h1, h2, h3, h4, h5, h6) { line-height: 1.3; }
        body h1 { font-size: 24px; }
        body h2 { font-size: 21px; }
        body h3 { font-size: 18px; }
        body h4 { font-size: 16px; }
        body h5 { font-size: 15px; }
        body h6 { font-size: 14px; }
        .admin-shell { min-height: 100vh; }
        .admin-rail { background: var(--rail); color: #fff; min-height: 100vh; padding: 28px 18px; width: 238px; }
        .admin-brand { color: #fff; font-size: 19px; font-weight: 700; text-decoration: none; }
        .admin-brand span { color: #eea07d; }
        .admin-nav { display: grid; gap: 5px; margin-top: 38px; }
        .admin-nav a { border-radius: 5px; color: #d1dedd; padding: 10px 12px; text-decoration: none; }
        .admin-nav a:hover, .admin-nav a.active { background: #29484e; color: #fff; }
        .admin-main { min-width: 0; width: calc(100% - 238px); }
        .admin-topbar { align-items: center; background: #fff; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; min-height: 72px; padding: 16px 32px; }
        .admin-account { align-items: center; display: flex; gap: 16px; }
        .admin-content { margin: 0 auto; max-width: 1480px; padding: 30px 32px 48px; }
        .page-heading { align-items: center; display: flex; justify-content: space-between; margin-bottom: 22px; }
        .page-heading h1 { font-size: 24px; font-weight: 600; margin: 0; }
        .panel { background: #fff; border: 1px solid var(--line); border-radius: 6px; }
        .btn-accent { background: var(--accent); border-color: var(--accent); color: #fff; }
        .btn-accent:hover { background: #bd583a; border-color: #bd583a; color: #fff; }
        .text-muted, .form-text { color: var(--muted) !important; }
        .table thead th { background: #f7f9f8; border-bottom-color: var(--line); color: #647579; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .table td { vertical-align: middle; }
        .form-control, .form-select { border-color: #ccd7d6; min-height: 42px; }
        .form-control:focus, .form-select:focus { border-color: #80a5a2; box-shadow: 0 0 0 .2rem rgba(65, 126, 120, .13); }
        .product-thumb { background: #e8eeee; border-radius: 4px; height: 48px; object-fit: cover; width: 48px; }
        .variant-row { border: 1px solid var(--line); border-radius: 5px; padding: 16px; }
        .badge-status { font-size: 11px; font-weight: 600; }
        .category-inline-create summary, .price-editor summary { color: #386d68; cursor: pointer; font-size: 12px; font-weight: 500; }
        .category-inline-message.error { color: #b42318; }
        .category-inline-message.success { color: #157347; }
        .currency-preview { color: var(--muted); display: flex; flex-wrap: wrap; font-size: 11px; gap: 2px 10px; margin-top: 4px; }
        .currency-summary { display: grid; gap: 0; }
        .form-control[readonly] { background: #f3f6f5; }
        .price-editor { min-width: 105px; position: relative; }
        .price-editor-form { background: #fff; border-top: 1px solid var(--line); margin-top: 8px; min-width: 220px; padding: 10px 0 0; }
        @media (max-width: 767.98px) {
            .admin-shell { display: block !important; }
            .admin-rail { min-height: 0; padding: 14px 16px; width: 100%; }
            .admin-nav { display: flex; gap: 2px; margin: 12px -6px 0; overflow-x: auto; }
            .admin-nav a { flex: 0 0 auto; font-size: 12px; padding: 8px; }
            .admin-main { width: 100%; }
            .admin-topbar { min-height: 54px; padding: 12px 16px; }
            .admin-account { gap: 8px; }
            .admin-content { padding: 22px 16px 36px; }
            .page-heading { align-items: flex-start; gap: 12px; }
            .page-heading h1 { font-size: 20px; }
        }
    </style>
</head>
<body>
<div class="admin-shell d-flex">
    <aside class="admin-rail">
        <a class="admin-brand" href="{{ route('admin.products.index') }}">Meo<span>X</span>Store</a>
        <nav class="admin-nav" aria-label="Admin navigation">
            <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">Products</a>
            <a href="{{ route('admin.rates.index') }}" class="{{ request()->routeIs('admin.rates.*') ? 'active' : '' }}">Rates</a>
            <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.index', 'admin.orders.show') ? 'active' : '' }}">Orders</a>
            <a href="{{ route('admin.orders.queue') }}" class="{{ request()->routeIs('admin.orders.queue') ? 'active' : '' }}">In Queue</a>
            <a href="{{ route('admin.reviews.index') }}" class="{{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">Reviews</a>
            <a href="{{ route('admin.payment-settings.index') }}" class="{{ request()->routeIs('admin.payment-settings.*') ? 'active' : '' }}">Payment Methods</a>
            <a href="{{ route('admin.game-categories.index') }}" class="{{ request()->routeIs('admin.game-categories.*') ? 'active' : '' }}">Game Categories</a>
            <a href="{{ route('admin.item-categories.index') }}" class="{{ request()->routeIs('admin.item-categories.*') ? 'active' : '' }}">Item Categories</a>
            <a href="{{ route('home') }}">Storefront</a>
        </nav>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="align-items-center d-flex flex-wrap gap-3">
                <span class="text-muted">Product Management</span>
                <form class="align-items-center d-flex gap-2" method="POST" action="{{ route('admin.store-status.update') }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="is_online" value="0">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" id="store-online-toggle" name="is_online" type="checkbox" value="1" role="switch" @checked($adminStoreSetting->is_online)>
                        <label class="form-check-label small fw-semibold" for="store-online-toggle">
                            {{ $adminStoreSetting->is_online ? __('store.store_status_online') : __('store.store_status_closed') }}
                        </label>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" type="submit">{{ __('store.save_store_status') }}</button>
                </form>
            </div>
            <div class="admin-account">
                <a class="fw-semibold text-decoration-none" href="{{ route('profile.edit') }}">{{ auth()->user()->name }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary" type="submit">Log out</button>
                </form>
            </div>
        </header>
        <main class="admin-content">
            @if (session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <div class="fw-semibold mb-1">Please check the highlighted fields.</div>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>