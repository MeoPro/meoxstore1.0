<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('store.profile_title') }} | Meoxstore</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    @vite([
        'resources/css/app.css',
        'resources/css/fontawesome.css',
        'resources/css/templatemo-lugx-gaming.css',
        'resources/css/owl.css',
        'resources/css/animate.css',
        'resources/js/app.js',
    ])
    <style>
        body { background: #eef3f9; }
        .profile-page { margin: 0 auto 56px; max-width: 1000px; padding-top: 132px; }
        .profile-hero { align-items: center; background: linear-gradient(125deg, #194779, #2877c9); border-radius: 12px 12px 0 0; color: #fff; display: flex; gap: 16px; min-height: 148px; padding: 28px; }
        .profile-avatar { align-items: center; background: rgba(255,255,255,.2); border: 2px solid #fff; border-radius: 50%; display: flex; flex: 0 0 68px; font-size: 24px; font-weight: 700; height: 68px; justify-content: center; width: 68px; }
        .profile-identity { min-width: 0; }
        .profile-identity h1 { font-size: 22px; font-weight: 700; margin: 0 0 4px; overflow-wrap: anywhere; }
        .profile-identity p { color: rgba(255,255,255,.82); margin: 0; }
        .profile-settings-link { align-items: center; background: rgba(255,255,255,.14); border-radius: 50%; color: #fff; display: inline-flex; height: 42px; justify-content: center; margin-left: auto; text-decoration: none; width: 42px; }
        .profile-settings-link:hover { background: rgba(255,255,255,.25); color: #fff; }
        .profile-header-actions { align-items: center; display: flex; gap: 10px; margin-left: auto; }
        .profile-header-actions form { margin: 0; }
        .profile-logout { background: #ee626b; border: 0; border-radius: 22px; color: #fff; font-weight: 600; min-height: 42px; padding: 8px 18px; }
        .profile-logout:hover { background: #da515b; color: #fff; }
        .profile-header-actions .profile-settings-link { margin-left: 0; }
        .profile-body { background: #fff; border: 1px solid #e2e8f0; border-radius: 0 0 16px 16px; box-shadow: 0 18px 48px rgba(20, 40, 70, .08); overflow: hidden; }
        .profile-statuses { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); padding: 18px 8px; }
        .profile-status { align-items: center; border-right: 1px solid #e7ebef; color: #344054; display: flex; flex-direction: column; gap: 5px; padding: 8px 10px; text-align: center; }
        .profile-status:last-child { border-right: 0; }
        .profile-status { border-radius: 7px; text-decoration: none; }
        .profile-status:hover, .profile-status.is-active { background: #f1f7fd; }
        .profile-status i { color: #2877c9; font-size: 19px; }
        .profile-status strong { font-size: 18px; }
        .profile-status span { font-size: 12px; }
        .profile-orders { border-top: 1px solid #e7ebef; padding: 22px 24px; }
        .profile-orders-heading { align-items: center; display: flex; justify-content: space-between; margin-bottom: 12px; }
        .profile-orders-heading h2 { font-size: 17px; font-weight: 650; margin: 0; }
        .profile-show-all { color: #2877c9; font-size: 13px; font-weight: 600; text-decoration: none; }
        .profile-order { align-items: center; animation: profile-order-enter .35s ease both; background: #fff; border: 1px solid #e8edf3; border-radius: 12px; color: inherit; display: flex; gap: 14px; justify-content: space-between; margin-top: 10px; padding: 16px; text-decoration: none; transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease; }
        .profile-order:hover { border-color: #c8dcf8; box-shadow: 0 8px 22px rgba(33, 83, 140, .08); transform: translateY(-2px); }
        @keyframes profile-order-enter { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .profile-order-name { font-weight: 600; }
        .profile-order-meta { color: #758092; font-size: 12px; margin-top: 3px; }
        .profile-order-total { font-weight: 650; white-space: nowrap; }
        @media (max-width: 575.98px) {
            .profile-page { padding-top: 112px; }
            .profile-hero { min-height: 124px; padding: 20px 16px; }
            .profile-avatar { flex-basis: 54px; height: 54px; width: 54px; }
            .profile-identity h1 { font-size: 18px; }
            .profile-statuses { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .profile-status:nth-child(2) { border-right: 0; }
            .profile-status:nth-child(n+3) { border-top: 1px solid #e7ebef; }
            .profile-orders { padding: 18px 15px; }
        }
    </style>
</head>
<body>
    <x-storefront-header />
    <main class="container profile-page">
        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        <section class="profile-hero">
            <div class="profile-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
            <div class="profile-identity">
                <h1>{{ $user->name }}</h1>
                <p>{{ $user->email }}</p>
            </div>
            <div class="profile-header-actions">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="profile-logout" type="submit">{{ __('store.log_out') }}</button>
                </form>
                <a class="profile-settings-link" href="{{ route('profile.settings') }}" aria-label="{{ __('store.settings') }}" title="{{ __('store.settings') }}">
                    <i class="fa fa-cog" aria-hidden="true"></i>
                </a>
            </div>
        </section>
        <section class="profile-body">
            <nav class="profile-statuses" aria-label="{{ __('store.purchase_history') }}">
                @foreach ([
                    'pending' => ['fa-credit-card', __('store.status_pending')],
                    'payment_submitted' => ['fa-receipt', __('store.status_payment_submitted_short')],
                    'payment_confirmed' => ['fa-truck', __('store.status_payment_confirmed')],
                    'completed' => ['fa-check-circle', __('store.status_completed')],
                ] as $status => [$icon, $label])
                    <a
                        class="profile-status{{ $selectedStatus === $status ? ' is-active' : '' }}"
                        href="{{ route('profile.edit', ['status' => $status]) }}#purchase-history"
                        @if ($selectedStatus === $status) aria-current="page" @endif
                    >
                        <i class="fa {{ $icon }}" data-status-icon="{{ $status }}" aria-hidden="true"></i>
                        <strong>{{ $statusCounts[$status] ?? 0 }}</strong>
                        <span>{{ $label }}</span>
                    </a>
                @endforeach
            </nav>
            <section class="profile-orders" id="purchase-history">
                <div class="profile-orders-heading">
                    <h2>{{ __('store.purchase_history') }}@if ($selectedStatus) · {{ __('store.status_'.$selectedStatus) }}@endif</h2>
                    <div class="align-items-center d-flex gap-3">
                        <a class="profile-show-all{{ $selectedStatus === null ? ' is-active' : '' }}" href="{{ route('profile.edit') }}#purchase-history" @if ($selectedStatus === null) aria-current="page" @endif>{{ __('store.show_all') }}</a>
                        <span class="text-secondary small">{{ $orders->total() }}</span>
                    </div>
                </div>
                @forelse ($orders as $order)
                    <a class="profile-order" href="{{ route('orders.show', $order) }}">
                        <span>
                            <span class="profile-order-name">{{ __('store.order') }} {{ $order->publicNumber() }}</span>
                            <span class="profile-order-meta d-block">{{ $order->created_at->format('d M Y, H:i') }} · {{ $order->items_count }} {{ __('store.product') }}</span>
                            <span class="profile-order-meta d-block">{{ __('store.status_'.$order->status) }}</span>
                        </span>
                        <span class="profile-order-total">Rp {{ number_format((float) $order->total, 0, ',', '.') }} <i class="fa fa-angle-right ms-1" aria-hidden="true"></i></span>
                    </a>
                @empty
                    <p class="text-secondary mb-0">{{ __('store.no_orders') }}</p>
                @endforelse
                @if ($orders->hasPages())
                    <div class="pt-3">{{ $orders->links('pagination::bootstrap-5') }}</div>
                @endif
            </section>
        </section>
    </main>
    <footer><div class="container"><div class="col-lg-12"><p>Copyright © {{ date('Y') }} Meoxstore. All rights reserved.</p></div></div></footer>
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
</body>
</html>
