<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Meoxstore Admin') | Meoxstore</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        :root {
            --ink: #111827;
            --muted: #6b7280;
            --line: #e5e7eb;
            --canvas: #f9fafb;
            --accent: #6366f1;
            --accent-dark: #4f46e5;
            --rail: #ffffff;
            --rail-soft: #f3f4f6;
            --panel-shadow: 0 1px 3px rgba(17, 24, 39, .05), 0 1px 2px rgba(17, 24, 39, .04);
        }

        body {
            background: var(--canvas);
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
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
        .btn-outline-secondary { --bs-btn-color: #4b5563; --bs-btn-border-color: #d1d5db; --bs-btn-hover-bg: #f9fafb; --bs-btn-hover-border-color: #9ca3af; --bs-btn-hover-color: var(--ink); }
        .btn-light { --bs-btn-bg: #f9fafb; --bs-btn-border-color: #e5e7eb; --bs-btn-hover-bg: #f3f4f6; --bs-btn-hover-border-color: #d1d5db; }
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
        .product-thumb { background: #f3f4f6; border-radius: 10px; height: 48px; object-fit: cover; width: 48px; }
        .admin-product-image { aspect-ratio: 1 / 1; object-fit: cover; width: 100%; }
        .admin-product-placeholder { aspect-ratio: 1 / 1; }
        .variant-row { background: #f9fafb; border: 1px solid var(--line); border-radius: 12px; padding: 16px; }
        .badge-status { font-size: 11px; font-weight: 600; }
        .category-inline-create summary,
        .price-editor summary { color: var(--accent-dark); cursor: pointer; font-size: 12px; font-weight: 600; }
        .category-inline-message.error { color: #b42318; }
        .category-inline-message.success { color: #157347; }
        .currency-preview { color: var(--muted); display: flex; flex-wrap: wrap; font-size: 11px; gap: 2px 10px; margin-top: 4px; }
        .currency-summary { display: grid; gap: 0; }
        .price-editor { min-width: 105px; position: relative; }
        .price-editor-form { background: #fff; border-top: 1px solid var(--line); margin-top: 8px; min-width: 220px; padding: 10px 0 0; }
        .order-row { cursor: pointer; }
        .order-row:focus { outline: 2px solid #a5b4fc; outline-offset: -2px; }
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

        .admin-shell { display: flex; min-height: 100vh; }
        .admin-rail {
            align-self: stretch;
            background: #fff;
            border-right: 1px solid var(--line);
            color: var(--ink);
            flex: 0 0 252px;
            inset: 0 auto 0 0;
            overflow-y: auto;
            padding: 18px 12px;
            position: fixed;
            transition: width .2s ease, transform .2s ease;
            width: 252px;
            z-index: 50;
        }
        .admin-brand {
            color: var(--ink);
            font-size: 19px;
            letter-spacing: -.045em;
        }
        .admin-brand:hover { color: var(--ink); }
        .admin-brand span { color: var(--accent); }
        .admin-brand .admin-brand-word { color: var(--ink); }
        .admin-brand .admin-brand-accent,
        .admin-brand .admin-brand-mark { color: var(--accent); }
        .admin-brand-mark { display: none; }
        .admin-brand-caption {
            color: var(--muted);
            font-size: 9px;
            letter-spacing: .13em;
            margin: 3px 0 0 1px;
        }
        .admin-nav { display: grid; gap: 20px; margin-top: 28px; }
        .admin-nav-group { display: grid; gap: 3px; }
        .admin-nav-label {
            color: #9ca3af;
            font-size: 10px;
            letter-spacing: .13em;
            margin: 0 10px 6px;
        }
        .admin-nav a {
            border-radius: 8px;
            color: #4b5563;
            font-size: 13px;
            gap: 11px;
            padding: 8px 10px;
        }
        .admin-nav a::before { display: none; }
        .admin-nav a:hover,
        .admin-nav a.active {
            background: #eef2ff;
            border-color: transparent;
            color: var(--accent-dark);
        }
        .admin-nav a.active { font-weight: 600; }
        .admin-nav-icon { color: #9ca3af; flex: 0 0 17px; height: 17px; width: 17px; }
        .admin-nav a:hover .admin-nav-icon,
        .admin-nav a.active .admin-nav-icon { color: var(--accent); }
        .admin-rail-footer { border-top-color: var(--line); }
        .admin-storefront-link { color: #4b5563; }
        .admin-storefront-link:hover { background: #f3f4f6; color: var(--ink); }
        .admin-main { flex: 1; margin-left: 252px; min-width: 0; transition: margin-left .2s ease; }
        .admin-topbar {
            background: rgba(255, 255, 255, .94);
            border-bottom-color: var(--line);
            min-height: 68px;
            padding: 10px 24px;
        }
        .admin-topbar-context { gap: 1px; }
        .admin-eyebrow { color: var(--muted); font-size: 10px; }
        .admin-context-title { font-size: 13px; font-weight: 600; }
        .admin-topbar-actions { gap: 18px; }
        .admin-account { gap: 10px; }
        .admin-avatar {
            background: #eef2ff;
            border-color: #e0e7ff;
            color: var(--accent-dark);
        }
        .admin-content { max-width: 1600px; padding: 28px 30px 34px; }
        .page-heading { margin-bottom: 22px; }
        .page-heading h1 { font-size: 24px; font-weight: 650; letter-spacing: -.04em; }
        .page-heading p { color: var(--muted); font-size: 13px; }
        .panel {
            border-color: var(--line);
            border-radius: 12px;
            box-shadow: var(--panel-shadow);
        }
        .btn { border-radius: 8px; font-size: 12px; }
        .btn-accent,
        .btn-primary {
            --bs-btn-bg: var(--accent);
            --bs-btn-border-color: var(--accent);
            --bs-btn-hover-bg: var(--accent-dark);
            --bs-btn-hover-border-color: var(--accent-dark);
            --bs-btn-active-bg: var(--accent-dark);
            --bs-btn-active-border-color: var(--accent-dark);
            background-color: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }
        .btn-accent:hover,
        .btn-primary:hover { background: var(--accent-dark); border-color: var(--accent-dark); color: #fff; }
        .btn:focus-visible,
        .admin-nav a:focus-visible,
        .admin-storefront-link:focus-visible,
        .admin-sidebar-toggle:focus-visible {
            box-shadow: 0 0 0 .2rem rgba(99, 102, 241, .18);
            outline: 0;
        }
        .table { --bs-table-color: var(--ink); --bs-table-hover-bg: #f9fafb; }
        .table thead th { background: #f9fafb; border-bottom-color: var(--line); color: #6b7280; }
        .table tbody td { border-color: #f3f4f6; }
        .form-label { color: #374151; }
        .form-control,
        .form-select { border-color: #e5e7eb; border-radius: 8px; }
        .form-control:focus,
        .form-select:focus {
            border-color: #a5b4fc;
            box-shadow: 0 0 0 .2rem rgba(99, 102, 241, .12);
        }
        .form-check-input:checked { background-color: var(--accent); border-color: var(--accent); }
        .alert { border: 1px solid var(--line); box-shadow: var(--panel-shadow); }
        .admin-icon { display: inline-block; flex: 0 0 auto; height: 18px; width: 18px; }
        .admin-sidebar-toggle {
            align-items: center;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            color: var(--muted);
            display: inline-flex;
            height: 36px;
            justify-content: center;
            padding: 0;
            width: 36px;
        }
        .admin-sidebar-toggle:hover { background: #f9fafb; color: var(--accent); }
        .admin-sidebar-backdrop { display: none; }
        .admin-shell.sidebar-collapsed .admin-rail {
            flex-basis: 64px;
            padding-left: 8px;
            padding-right: 8px;
            width: 64px;
        }
        .admin-shell.sidebar-collapsed .admin-main { margin-left: 64px; }
        .admin-shell.sidebar-collapsed .admin-brand-caption,
        .admin-shell.sidebar-collapsed .admin-nav-label,
        .admin-shell.sidebar-collapsed .admin-nav-text,
        .admin-shell.sidebar-collapsed .admin-rail-footer { display: none; }
        .admin-shell.sidebar-collapsed .admin-brand-word,
        .admin-shell.sidebar-collapsed .admin-brand-accent { display: none; }
        .admin-shell.sidebar-collapsed .admin-brand-mark {
            display: block;
            font-size: 18px;
            margin: 0 auto;
            text-align: center;
        }
        .admin-shell.sidebar-collapsed .admin-nav a { justify-content: center; padding: 9px 5px; }
        .admin-shell.sidebar-collapsed .admin-nav-icon { flex-basis: 18px; }
        .admin-sidebar-toggle .admin-icon { height: 17px; width: 17px; }
        .admin-credit {
            border-top: 1px solid var(--line);
            color: var(--muted);
            font-size: 11px;
            margin-top: 28px;
            padding: 14px 0 0;
        }
        .admin-credit a { color: var(--accent-dark); font-weight: 600; text-decoration: none; }
        .admin-credit a:hover { text-decoration: underline; }
        .gradient-primary { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); }
        .gradient-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .gradient-ocean { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
        .gradient-sunset { background: linear-gradient(135deg, #f97316 0%, #ef4444 100%); }
        .performance-period {
            align-items: center;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            color: #4b5563;
            display: inline-flex;
            font-size: 12px;
            font-weight: 600;
            gap: 8px;
            padding: 8px 11px;
        }
        .performance-period .admin-icon { color: var(--muted); height: 16px; width: 16px; }
        .performance-stat-card {
            align-items: flex-start;
            border-radius: 12px;
            color: #fff;
            display: flex;
            justify-content: space-between;
            min-height: 140px;
            overflow: hidden;
            padding: 18px;
            position: relative;
        }
        .performance-stat-card::after {
            background: rgba(255, 255, 255, .08);
            border-radius: 50%;
            content: '';
            height: 100px;
            position: absolute;
            right: -32px;
            top: -40px;
            width: 100px;
        }
        .performance-stat-card > div { min-width: 0; position: relative; z-index: 1; }
        .performance-stat-card strong { display: block; font-size: 22px; letter-spacing: -.04em; line-height: 1.25; overflow-wrap: anywhere; }
        .performance-stat-card span:not(.performance-stat-icon) { color: rgba(255, 255, 255, .78); display: block; font-size: 11px; margin-top: 8px; }
        .performance-stat-label { color: rgba(255, 255, 255, .82); font-size: 12px; margin: 0 0 8px; }
        .performance-stat-icon {
            align-items: center;
            background: rgba(255, 255, 255, .17);
            border-radius: 9px;
            display: inline-flex;
            flex: 0 0 34px;
            height: 34px;
            justify-content: center;
            position: relative;
            width: 34px;
            z-index: 1;
        }
        .performance-stat-icon .admin-icon { height: 17px; width: 17px; }
        .performance-panel-heading {
            align-items: center;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            padding: 20px 20px 14px;
        }
        .performance-panel-heading h2 { font-size: 16px; font-weight: 650; margin: 0; }
        .performance-panel-heading p { color: var(--muted); font-size: 12px; margin: 5px 0 0; }
        .performance-count {
            background: #eef2ff;
            border-radius: 20px;
            color: var(--accent-dark);
            flex: 0 0 auto;
            font-size: 11px;
            font-weight: 600;
            padding: 5px 9px;
        }
        .performance-products .table { margin-bottom: 0; }
        .performance-products .table thead th { padding: 12px 18px; }
        .performance-products .table tbody td { padding: 13px 18px; }
        .performance-rank {
            align-items: center;
            background: #f3f4f6;
            border-radius: 7px;
            color: #6b7280;
            display: inline-flex;
            font-size: 11px;
            font-weight: 700;
            height: 28px;
            justify-content: center;
            width: 28px;
        }
        .performance-product { align-items: center; display: flex; gap: 11px; min-width: 220px; }
        .performance-product .product-thumb { flex: 0 0 42px; height: 42px; width: 42px; }
        .performance-product-placeholder {
            align-items: center;
            color: #6b7280;
            display: inline-flex;
            font-size: 14px;
            font-weight: 700;
            justify-content: center;
        }
        .performance-product-copy { min-width: 0; width: 100%; }
        .performance-product-copy strong { display: block; font-size: 12px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .performance-bar { background: #eef2ff; border-radius: 6px; height: 5px; margin-top: 7px; overflow: hidden; width: 100%; }
        .performance-bar span { background: linear-gradient(90deg, #6366f1, #8b5cf6); border-radius: inherit; display: block; height: 100%; }
        .performance-empty-state { align-items: center; color: var(--muted); display: flex; flex-direction: column; gap: 8px; }
        .performance-empty-state .admin-icon { color: #9ca3af; height: 28px; width: 28px; }
        .performance-empty-state strong { color: var(--ink); font-size: 13px; }
        .performance-empty-state span { font-size: 12px; }
        @media (max-width: 767.98px) {
            .admin-shell { display: flex !important; }
            .admin-rail {
                height: 100vh;
                min-height: 0;
                padding: 16px 12px;
                position: fixed;
                transform: translateX(-100%);
                width: 252px;
            }
            .admin-shell.sidebar-open .admin-rail { transform: translateX(0); }
            .admin-sidebar-backdrop {
                background: rgba(17, 24, 39, .42);
                border: 0;
                inset: 0;
                position: fixed;
                z-index: 40;
            }
            .admin-shell.sidebar-open .admin-sidebar-backdrop { display: block; }
            .admin-main { margin-left: 0; width: 100%; }
            .admin-topbar { align-items: center; gap: 10px; padding: 10px 14px; }
            .admin-topbar-context { display: none; }
            .admin-topbar-actions { flex: 1; flex-wrap: wrap; gap: 8px; justify-content: flex-end; }
            .admin-store-form { flex-wrap: wrap; gap: 6px; }
            .admin-store-form .btn { display: none; }
            .admin-account { gap: 6px; }
            .admin-account-copy { display: none; }
            .admin-content { padding: 22px 15px 28px; }
            .page-heading { align-items: flex-start; }
            .performance-stat-card { min-height: 128px; padding: 15px; }
            .performance-stat-card strong { font-size: 19px; }
            .performance-panel-heading { align-items: flex-start; padding: 17px 15px 12px; }
            .performance-products .table thead th,
            .performance-products .table tbody td { padding-left: 12px; padding-right: 12px; }
        }
        @media (min-width: 768px) {
            .admin-sidebar-toggle { display: inline-flex; }
        }
    </style>
</head>
<body>
<div class="admin-shell d-flex" data-admin-shell>
    <aside class="admin-rail">
        <a class="admin-brand" href="{{ route('admin.performance.index') }}" aria-label="MeoXStore admin">
            <span class="admin-brand-mark">M</span><span class="admin-brand-word">Meo</span><span class="admin-brand-accent">X</span><span class="admin-brand-word">Store</span>
        </a>
        <p class="admin-brand-caption">Store administration</p>
        <nav class="admin-nav" id="admin-navigation" aria-label="Admin navigation">
            <div class="admin-nav-group">
                <p class="admin-nav-label">Overview</p>
                <a href="{{ route('admin.performance.index') }}" class="{{ request()->routeIs('admin.performance.*') ? 'active' : '' }}">
                    <x-admin-icon name="dashboard" class="admin-nav-icon" /><span class="admin-nav-text">Performance</span>
                </a>
            </div>
            <div class="admin-nav-group">
                <p class="admin-nav-label">Store</p>
                <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <x-admin-icon name="products" class="admin-nav-icon" /><span class="admin-nav-text">Products</span>
                </a>
                <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.index', 'admin.orders.show') ? 'active' : '' }}">
                    <x-admin-icon name="orders" class="admin-nav-icon" /><span class="admin-nav-text">Orders</span>
                </a>
                <a href="{{ route('admin.orders.queue') }}" class="{{ request()->routeIs('admin.orders.queue') ? 'active' : '' }}">
                    <x-admin-icon name="orders" class="admin-nav-icon" /><span class="admin-nav-text">In Queue</span>
                </a>
                <a href="{{ route('admin.reviews.index') }}" class="{{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                    <x-admin-icon name="reviews" class="admin-nav-icon" /><span class="admin-nav-text">Reviews</span>
                </a>
            </div>
            <div class="admin-nav-group">
                <p class="admin-nav-label">Catalog</p>
                <a href="{{ route('admin.game-categories.index') }}" class="{{ request()->routeIs('admin.game-categories.*') ? 'active' : '' }}">
                    <x-admin-icon name="categories" class="admin-nav-icon" /><span class="admin-nav-text">Game Categories</span>
                </a>
                <a href="{{ route('admin.item-categories.index') }}" class="{{ request()->routeIs('admin.item-categories.*') ? 'active' : '' }}">
                    <x-admin-icon name="categories" class="admin-nav-icon" /><span class="admin-nav-text">Item Categories</span>
                </a>
            </div>
            <div class="admin-nav-group">
                <p class="admin-nav-label">Content</p>
                <a href="{{ route('admin.blog-posts.index') }}" class="{{ request()->routeIs('admin.blog-posts.*') ? 'active' : '' }}">
                    <x-admin-icon name="blog" class="admin-nav-icon" /><span class="admin-nav-text">Blog posts</span>
                </a>
                <a href="{{ route('admin.blog-categories.index') }}" class="{{ request()->routeIs('admin.blog-categories.*') ? 'active' : '' }}">
                    <x-admin-icon name="categories" class="admin-nav-icon" /><span class="admin-nav-text">Blog categories</span>
                </a>
                <a href="{{ route('admin.blog-sidebar-blocks.index') }}" class="{{ request()->routeIs('admin.blog-sidebar-blocks.*') ? 'active' : '' }}">
                    <x-admin-icon name="blog" class="admin-nav-icon" /><span class="admin-nav-text">Blog sidebar</span>
                </a>
            </div>
            <div class="admin-nav-group">
                <p class="admin-nav-label">Settings</p>
                <a href="{{ route('admin.rates.index') }}" class="{{ request()->routeIs('admin.rates.*') ? 'active' : '' }}">
                    <x-admin-icon name="settings" class="admin-nav-icon" /><span class="admin-nav-text">Rates</span>
                </a>
                <a href="{{ route('admin.payment-settings.index') }}" class="{{ request()->routeIs('admin.payment-settings.*') ? 'active' : '' }}">
                    <x-admin-icon name="payment" class="admin-nav-icon" /><span class="admin-nav-text">Payment Methods</span>
                </a>
                <a href="{{ route('admin.social-links.index') }}" class="{{ request()->routeIs('admin.social-links.*') ? 'active' : '' }}">
                    <x-admin-icon name="social" class="admin-nav-icon" /><span class="admin-nav-text">Social Media</span>
                </a>
                <a href="{{ route('admin.google-business-profile.index') }}" class="{{ request()->routeIs('admin.google-business-profile.*') ? 'active' : '' }}">
                    <x-admin-icon name="reviews" class="admin-nav-icon" /><span class="admin-nav-text">Google Reviews</span>
                </a>
            </div>
        </nav>
        <div class="admin-rail-footer">
            <a class="admin-storefront-link" href="{{ route('home') }}">
                <span class="admin-nav-text">Visit storefront</span>
                <span aria-hidden="true">↗</span>
            </a>
        </div>
    </aside>
    <button class="admin-sidebar-backdrop" type="button" data-admin-sidebar-close aria-label="Close admin navigation"></button>
    <div class="admin-main">
        <header class="admin-topbar">
            <button class="admin-sidebar-toggle" type="button" data-admin-sidebar-toggle aria-label="Toggle admin navigation" aria-controls="admin-navigation">
                <x-admin-icon name="dashboard" />
            </button>
            <div class="admin-topbar-context">
                <span class="admin-eyebrow">Meoxstore</span>
                <span class="admin-context-title">@yield('title', 'Admin workspace')</span>
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
            <footer class="admin-credit">
                &copy; {{ now()->year }} Meoxstore. Designed &amp; developed by
                <a href="https://datadropx.net" target="_blank" rel="noopener noreferrer">DataDropX</a>.
            </footer>
        </main>
    </div>
</div>
<script>
    (() => {
        const shell = document.querySelector('[data-admin-shell]');
        const toggle = document.querySelector('[data-admin-sidebar-toggle]');
        const close = document.querySelector('[data-admin-sidebar-close]');
        const isMobile = () => window.matchMedia('(max-width: 767.98px)').matches;

        toggle.addEventListener('click', () => {
            shell.classList.toggle(isMobile() ? 'sidebar-open' : 'sidebar-collapsed');
        });
        close.addEventListener('click', () => shell.classList.remove('sidebar-open'));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                shell.classList.remove('sidebar-open');
            }
        });
        window.addEventListener('resize', () => {
            if (!isMobile()) {
                shell.classList.remove('sidebar-open');
            }
        });
    })();
</script>
</body>
</html>