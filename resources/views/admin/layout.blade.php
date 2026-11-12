<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Meoxstore Admin') | Meoxstore</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #203238;
            --muted: #718186;
            --line: #e3e9e8;
            --canvas: #f4f7f6;
            --accent: #d66d4d;
            --accent-dark: #bd583a;
            --rail: #18343a;
            --rail-soft: #24484c;
            --panel-shadow: 0 8px 24px rgba(27, 52, 55, .045);
        }

        body {
            background: var(--canvas);
            color: var(--ink);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }

        body :is(h1, h2, h3, h4, h5, h6) { line-height: 1.3; }
        body h1 { font-size: 24px; }
        body h2 { font-size: 21px; }
        body h3 { font-size: 18px; }
        body h4 { font-size: 16px; }
        body h5 { font-size: 15px; }
        body h6 { font-size: 14px; }

        .admin-shell { min-height: 100vh; }
        .admin-rail {
            align-self: flex-start;
            background: linear-gradient(180deg, #1a3b3e 0%, var(--rail) 100%);
            color: #fff;
            display: flex;
            flex: 0 0 252px;
            flex-direction: column;
            height: 100vh;
            padding: 26px 16px 18px;
            position: sticky;
            top: 0;
            width: 252px;
        }
        .admin-brand {
            color: #fff;
            display: inline-block;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -.04em;
            text-decoration: none;
        }
        .admin-brand span { color: #eea07d; }
        .admin-brand-caption {
            color: #9ab0b0;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .14em;
            margin: 4px 0 0;
            text-transform: uppercase;
        }
        .admin-nav { display: grid; gap: 22px; margin-top: 34px; }
        .admin-nav-group { display: grid; gap: 4px; }
        .admin-nav-label {
            color: #90a9a9;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .13em;
            margin: 0 10px 6px;
            text-transform: uppercase;
        }
        .admin-nav a {
            align-items: center;
            border: 1px solid transparent;
            border-radius: 10px;
            color: #d1dedd;
            display: flex;
            gap: 11px;
            padding: 10px 12px;
            text-decoration: none;
            transition: background-color .18s ease, border-color .18s ease, color .18s ease;
        }
        .admin-nav a::before {
            border: 1.5px solid currentColor;
            border-radius: 3px;
            content: '';
            height: 8px;
            opacity: .7;
            width: 8px;
        }
        .admin-nav a:hover,
        .admin-nav a.active {
            background: rgba(255, 255, 255, .09);
            border-color: rgba(255, 255, 255, .07);
            color: #fff;
        }
        .admin-nav a.active { font-weight: 600; }
        .admin-nav a.active::before {
            background: #eea07d;
            border-color: #eea07d;
            opacity: 1;
        }
        .admin-rail-footer { border-top: 1px solid rgba(255, 255, 255, .12); margin-top: auto; padding-top: 16px; }
        .admin-storefront-link {
            align-items: center;
            border-radius: 10px;
            color: #d1dedd;
            display: flex;
            justify-content: space-between;
            padding: 10px 12px;
            text-decoration: none;
        }
        .admin-storefront-link:hover { background: rgba(255, 255, 255, .08); color: #fff; }

        .admin-main { flex: 1; min-width: 0; }
        .admin-topbar {
            align-items: center;
            backdrop-filter: blur(12px);
            background: rgba(255, 255, 255, .94);
            border-bottom: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            min-height: 78px;
            padding: 13px 34px;
            position: sticky;
            top: 0;
            z-index: 20;
        }
        .admin-topbar-context { display: grid; gap: 2px; }
        .admin-eyebrow {
            color: var(--muted);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        .admin-context-title { font-size: 14px; font-weight: 600; }
        .admin-topbar-actions { align-items: center; display: flex; gap: 24px; }
        .admin-store-form {
            align-items: center;
            display: flex;
            gap: 12px;
            margin: 0;
        }
        .admin-store-form .form-check { align-items: center; display: flex; gap: 8px; padding-left: 0; }
        .admin-store-form .form-check-input { float: none; margin: 0; }
        .admin-store-form .form-check-label { color: var(--ink); font-size: 12px; font-weight: 600; }
        .admin-store-form.is-online .form-check-label::before,
        .admin-store-form:not(.is-online) .form-check-label::before {
            background: #20a36b;
            border-radius: 50%;
            content: '';
            display: inline-block;
            height: 7px;
            margin: 0 7px 1px 0;
            width: 7px;
        }
        .admin-store-form:not(.is-online) .form-check-label::before { background: #d66d4d; }
        .admin-account { align-items: center; display: flex; gap: 12px; }
        .admin-account-profile {
            align-items: center;
            color: var(--ink);
            display: flex;
            gap: 10px;
            text-decoration: none;
        }
        .admin-account-profile:hover { color: var(--accent-dark); }
        .admin-avatar {
            align-items: center;
            background: #f7e9e3;
            border: 1px solid #f0d8ce;
            border-radius: 50%;
            color: #a44f35;
            display: inline-flex;
            font-size: 12px;
            font-weight: 700;
            height: 36px;
            justify-content: center;
            width: 36px;
        }
        .admin-account-copy { display: grid; line-height: 1.3; }
        .admin-account-copy strong { font-size: 12px; font-weight: 600; }
        .admin-account-copy small { color: var(--muted); font-size: 10px; }
        .admin-content { margin: 0 auto; max-width: 1600px; padding: 34px 36px 56px; }
        .page-heading {
            align-items: center;
            display: flex;
            justify-content: space-between;
            margin-bottom: 24px;
            min-height: 42px;
        }
        .page-heading h1 { font-size: 25px; font-weight: 600; letter-spacing: -.035em; margin: 0; }
        .page-heading p { color: var(--muted); font-size: 12px; margin: 6px 0 0; }
        .panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 15px;
            box-shadow: var(--panel-shadow);
        }
        .btn {
            border-radius: 9px;
            font-size: 12px;
            font-weight: 600;
            padding: 9px 13px;
            transition: background-color .18s ease, border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        }
        .btn.btn-sm { padding: 7px 10px; }
        .btn:hover { transform: translateY(-1px); }
        .btn:focus-visible,
        .admin-nav a:focus-visible,
        .admin-storefront-link:focus-visible {
            box-shadow: 0 0 0 .2rem rgba(214, 109, 77, .2);
            outline: 0;
        }
        .btn-accent { background: var(--accent); border-color: var(--accent); color: #fff; }
        .btn-accent:hover { background: var(--accent-dark); border-color: var(--accent-dark); color: #fff; }
        .btn-outline-secondary { --bs-btn-color: #52666a; --bs-btn-border-color: #d3dddb; --bs-btn-hover-bg: #edf2f1; --bs-btn-hover-border-color: #c5d1cf; --bs-btn-hover-color: var(--ink); }
        .btn-light { --bs-btn-bg: #f6f8f7; --bs-btn-border-color: #e3e9e8; --bs-btn-hover-bg: #edf2f1; --bs-btn-hover-border-color: #d7e0de; }
        .text-muted,
        .form-text { color: var(--muted) !important; }
        .table { --bs-table-color: var(--ink); --bs-table-hover-bg: #f8faf9; }
        .table thead th {
            background: #f7f9f8;
            border-bottom-color: var(--line);
            color: #647579;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .06em;
            padding: 13px 15px;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .table tbody td { border-color: #edf1f0; padding: 13px 15px; vertical-align: middle; }
        .table tbody tr:last-child td { border-bottom: 0; }
        .form-label { color: #33484d; font-size: 12px; font-weight: 600; }
        .form-control,
        .form-select {
            border-color: #d7e0de;
            border-radius: 9px;
            min-height: 42px;
        }
        .form-control.form-control-sm,
        .form-select.form-select-sm { min-height: 34px; }
        .form-control:focus,
        .form-select:focus {
            border-color: #80a5a2;
            box-shadow: 0 0 0 .2rem rgba(65, 126, 120, .13);
        }
        .form-control[readonly] { background: #f3f6f5; }
        .form-check-input { border-color: #c5d1cf; }
        .form-check-input:checked { background-color: #32766e; border-color: #32766e; }
        .alert { border: 0; border-radius: 12px; box-shadow: var(--panel-shadow); font-size: 12px; }
        .product-thumb { background: #e8eeee; border-radius: 10px; height: 48px; object-fit: cover; width: 48px; }
        .variant-row { background: #fbfcfc; border: 1px solid var(--line); border-radius: 12px; padding: 16px; }
        .badge-status { font-size: 11px; font-weight: 600; }
        .category-inline-create summary,
        .price-editor summary { color: #386d68; cursor: pointer; font-size: 12px; font-weight: 600; }
        .category-inline-message.error { color: #b42318; }
        .category-inline-message.success { color: #157347; }
        .currency-preview { color: var(--muted); display: flex; flex-wrap: wrap; font-size: 11px; gap: 2px 10px; margin-top: 4px; }
        .currency-summary { display: grid; gap: 0; }
        .price-editor { min-width: 105px; position: relative; }
        .price-editor-form { background: #fff; border-top: 1px solid var(--line); margin-top: 8px; min-width: 220px; padding: 10px 0 0; }
        .order-row { cursor: pointer; }
        .order-row:focus { outline: 2px solid #80a5a2; outline-offset: -2px; }
        @media (max-width: 767.98px) {
            .admin-shell { display: block !important; }
            .admin-rail { height: auto; min-height: 0; padding: 14px 16px 12px; position: static; width: 100%; }
            .admin-brand { font-size: 18px; }
            .admin-brand-caption { display: none; }
            .admin-nav { display: flex; gap: 18px; margin: 12px -4px 0; overflow-x: auto; padding-bottom: 2px; }
            .admin-nav-group { display: flex; flex: 0 0 auto; gap: 3px; }
            .admin-nav-label { display: none; }
            .admin-nav a { flex: 0 0 auto; font-size: 12px; padding: 8px 10px; white-space: nowrap; }
            .admin-nav a::before { height: 6px; width: 6px; }
            .admin-rail-footer { display: none; }
            .admin-topbar { align-items: flex-start; gap: 12px; min-height: 0; padding: 12px 16px; }
            .admin-topbar-context { display: none; }
            .admin-topbar-actions { flex: 1; flex-wrap: wrap; gap: 10px 16px; justify-content: space-between; }
            .admin-store-form { flex-wrap: wrap; gap: 8px; }
            .admin-store-form .form-check-label { font-size: 11px; }
            .admin-account { gap: 8px; }
            .admin-account-copy { display: none; }
            .admin-content { padding: 22px 16px 36px; }
            .page-heading { align-items: flex-start; gap: 12px; }
            .page-heading h1 { font-size: 21px; }
            .page-heading .btn { flex: 0 0 auto; }
            .panel.p-4 { padding: 1rem !important; }
        }
        @media (min-width: 768px) and (max-width: 1100px) {
            .admin-rail { flex-basis: 224px; width: 224px; }
            .admin-topbar { padding-right: 22px; padding-left: 22px; }
            .admin-content { padding-right: 22px; padding-left: 22px; }
        }
    </style>
</head>
<body>
<div class="admin-shell d-flex">
    <aside class="admin-rail">
        <a class="admin-brand" href="{{ route('admin.products.index') }}">Meo<span>X</span>Store</a>
        <p class="admin-brand-caption">Store administration</p>
        <nav class="admin-nav" aria-label="Admin navigation">
            <div class="admin-nav-group">
                <p class="admin-nav-label">Workspace</p>
                <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">Products</a>
                <a href="{{ route('admin.blog-posts.index') }}" class="{{ request()->routeIs('admin.blog-posts.*') ? 'active' : '' }}">Blog posts</a>
                <a href="{{ route('admin.blog-categories.index') }}" class="{{ request()->routeIs('admin.blog-categories.*') ? 'active' : '' }}">Blog categories</a>
                <a href="{{ route('admin.blog-sidebar-blocks.index') }}" class="{{ request()->routeIs('admin.blog-sidebar-blocks.*') ? 'active' : '' }}">Blog sidebar</a>
                <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.index', 'admin.orders.show') ? 'active' : '' }}">Orders</a>
                <a href="{{ route('admin.orders.queue') }}" class="{{ request()->routeIs('admin.orders.queue') ? 'active' : '' }}">In Queue</a>
                <a href="{{ route('admin.reviews.index') }}" class="{{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">Reviews</a>
            </div>
            <div class="admin-nav-group">
                <p class="admin-nav-label">Catalog</p>
                <a href="{{ route('admin.game-categories.index') }}" class="{{ request()->routeIs('admin.game-categories.*') ? 'active' : '' }}">Game Categories</a>
                <a href="{{ route('admin.item-categories.index') }}" class="{{ request()->routeIs('admin.item-categories.*') ? 'active' : '' }}">Item Categories</a>
            </div>
            <div class="admin-nav-group">
                <p class="admin-nav-label">Settings</p>
                <a href="{{ route('admin.rates.index') }}" class="{{ request()->routeIs('admin.rates.*') ? 'active' : '' }}">Rates</a>
                <a href="{{ route('admin.payment-settings.index') }}" class="{{ request()->routeIs('admin.payment-settings.*') ? 'active' : '' }}">Payment Methods</a>
            </div>
        </nav>
        <div class="admin-rail-footer">
            <a class="admin-storefront-link" href="{{ route('home') }}">
                <span>Visit storefront</span>
                <span aria-hidden="true">↗</span>
            </a>
        </div>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="admin-topbar-context">
                <span class="admin-eyebrow">Meoxstore</span>
                <span class="admin-context-title">Admin workspace</span>
            </div>
            <div class="admin-topbar-actions">
                <form class="admin-store-form {{ $adminStoreSetting->is_online ? 'is-online' : '' }}" method="POST" action="{{ route('admin.store-status.update') }}">
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
                <div class="admin-account">
                    <a class="admin-account-profile" href="{{ route('profile.edit') }}">
                        <span class="admin-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="admin-account-copy">
                            <strong>{{ auth()->user()->name }}</strong>
                            <small>Administrator</small>
                        </span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary" type="submit">Log out</button>
                    </form>
                </div>
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