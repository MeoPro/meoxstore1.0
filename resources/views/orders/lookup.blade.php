<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('store.track_order') }} | Meoxstore</title>
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
        body { background: #f1f5f9; }
        .tracking-wrap { margin: 0 auto; max-width: 620px; padding: 145px 16px 72px; }
        .tracking-card { animation: tracking-enter .45s ease both; background: #fff; border: 1px solid #e4eaf1; border-radius: 22px; box-shadow: 0 18px 50px rgba(30, 50, 75, .09); padding: clamp(24px, 5vw, 44px); }
        .tracking-icon { align-items: center; background: #e8f1ff; border-radius: 16px; color: #2877c9; display: inline-flex; font-size: 25px; height: 58px; justify-content: center; margin-bottom: 18px; width: 58px; }
        .tracking-card h1 { color: #172b4d; font-size: clamp(24px, 4vw, 32px); font-weight: 750; letter-spacing: -.03em; }
        .tracking-card > p { color: #758297; line-height: 1.7; margin-bottom: 28px; }
        .tracking-card .form-label { color: #344054; font-size: 13px; font-weight: 650; }
        .tracking-card .form-control { border-color: #dfe5ec; border-radius: 11px; min-height: 50px; }
        .tracking-card .form-control:focus { border-color: #5794e5; box-shadow: 0 0 0 .22rem rgba(40, 119, 201, .12); }
        .tracking-submit { background: #2877c9; border: 0; border-radius: 11px; color: #fff; font-weight: 700; min-height: 50px; transition: transform .18s ease, background-color .18s ease; }
        .tracking-submit:hover { background: #1b5fa9; color: #fff; transform: translateY(-1px); }
        .tracking-help { color: #8a95a5; font-size: 12px; margin-top: 18px; }
        @keyframes tracking-enter { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        @media (prefers-reduced-motion: reduce) { .tracking-card { animation: none; } .tracking-submit { transition: none; } }
    </style>
</head>
<body>
    <x-storefront-header />
    <main class="tracking-wrap">
        <section class="tracking-card">
            <span class="tracking-icon" aria-hidden="true"><i class="fa fa-search"></i></span>
            <h1>{{ __('store.track_order') }}</h1>
            <p>{{ __('store.order_lookup_intro') }}</p>
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('orders.lookup.submit') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="order-number">{{ __('store.order_number') }}</label>
                    <input class="form-control @error('order_number') is-invalid @enderror" id="order-number" name="order_number" type="text" value="{{ old('order_number') }}" placeholder="MEOX-00000001" maxlength="30" autocomplete="off" required>
                    @error('order_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label" for="order-phone">{{ __('store.phone') }}</label>
                    <input class="form-control @error('phone') is-invalid @enderror" id="order-phone" name="phone" type="tel" value="{{ old('phone') }}" maxlength="40" autocomplete="tel" required>
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button class="btn tracking-submit w-100" type="submit">{{ __('store.find_order') }} <i class="fa fa-arrow-right ms-1" aria-hidden="true"></i></button>
            </form>
            <p class="tracking-help mb-0">{{ __('store.order_lookup_help') }}</p>
        </section>
    </main>
    <footer><div class="container"><div class="col-lg-12"><p>Copyright © {{ date('Y') }} Meoxstore. All rights reserved.</p></div></div></footer>
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
</body>
</html>
