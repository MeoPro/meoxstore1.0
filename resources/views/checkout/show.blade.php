<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('store.checkout_title') }} | Meoxstore</title>
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
        .checkout-layout { align-items: start; display: grid; gap: 28px; grid-template-columns: minmax(0, 1.1fr) minmax(340px, .9fr); margin: 34px auto 54px; max-width: 1080px; }
        .checkout-section { background: #fff; border: 1px solid #e7ebef; border-radius: 12px; padding: clamp(20px, 3vw, 28px); }
        .checkout-section h1, .checkout-section h2 { color: #182230; font-weight: 650; }
        .checkout-heading { margin-bottom: 20px; }
        .checkout-heading p { color: #77818e; font-size: 14px; margin: 4px 0 0; }
        .checkout-items { border-top: 1px solid #edf0f3; }
        .checkout-item { align-items: flex-start; border-bottom: 1px solid #edf0f3; display: flex; gap: 14px; justify-content: space-between; padding: 15px 0; }
        .checkout-item-main { align-items: flex-start; display: flex; flex: 1 1 auto; gap: 14px; min-width: 0; }
        .checkout-product-image, .checkout-product-placeholder { background: #eef2f6; border-radius: 9px; flex: 0 0 64px; height: 64px; object-fit: cover; width: 64px; }
        .checkout-product-placeholder { align-items: center; color: #7c8999; display: flex; font-size: 22px; justify-content: center; }
        .checkout-item-copy { flex: 1 1 auto; min-width: 0; }
        .checkout-item h2 { font-size: 15px; margin: 0 0 4px; overflow-wrap: anywhere; }
        .checkout-item-price { color: #1976bd; flex: 0 0 auto; font-size: 15px; padding-top: 2px; white-space: nowrap; }
        .checkout-item-description summary { color: #0071f8; cursor: pointer; font-size: 12px; font-weight: 600; list-style: none; margin-top: 8px; width: fit-content; }
        .checkout-item-description summary::-webkit-details-marker { display: none; }
        .checkout-item-description summary::after { content: ' +'; }
        .checkout-item-description[open] summary::after { content: ' −'; }
        .checkout-item-description p { background: #f7f8fb; border-radius: 8px; color: #637083; font-size: 13px; line-height: 1.6; margin: 8px 0 0; overflow-wrap: anywhere; padding: 10px 12px; white-space: pre-line; }
        .checkout-total { align-items: center; display: flex; justify-content: space-between; padding-top: 18px; }
        .checkout-total strong { color: #182230; font-size: 20px; }
        .checkout-contact-note { color: #77818e; font-size: 13px; line-height: 1.6; margin: -6px 0 18px; }
        .payment-method { align-items: center; background: #fff; border: 1px solid #e7ebef; border-radius: 9px; cursor: pointer; display: flex; min-height: 76px; padding: 14px; transition: border-color .15s, background-color .15s; }
        .payment-method:has(input:checked) { background: #f3f8ff; border-color: #4d9ce0; }
        .payment-method + .payment-method { margin-top: 9px; }
        .payment-method-choice { align-items: center; display: flex; gap: 14px; width: 100%; }
        .payment-method-choice input { flex: 0 0 auto; margin: 0; }
        .payment-method-logo { flex: 0 0 56px; height: 44px; object-fit: contain; width: 56px; }
        .payment-method-name { color: #182230; font-size: 15px; }
        .checkout-submit { background: #009b69; border: 0; border-radius: 8px; color: #fff; font-weight: 650; min-height: 48px; }
        .checkout-submit:hover { background: #00845a; color: #fff; }
        .store-closed-warning { background: #fff7e8; border: 1px solid #f6d79c; border-radius: 10px; color: #7a4b00; font-size: 14px; line-height: 1.55; margin: 16px auto 0; max-width: 1080px; padding: 12px 16px; }
        .page-heading.header-text { border-radius: 0 0 56px 56px; padding: 108px 0 22px; }
        .page-heading.header-text h3 { font-size: 26px; margin: 10px 0; }
        @media (max-width: 767.98px) { .checkout-layout { grid-template-columns: 1fr; margin-top: 20px; } .checkout-item { gap: 10px; } .checkout-item-main { gap: 10px; } .checkout-item-price { font-size: 14px; } }
        @media (max-width: 767.98px) { .page-heading.header-text { padding: 96px 0 18px; } .page-heading.header-text h3 { font-size: 22px; } }
    </style>
</head>
<body class="storefront-page">
    <x-storefront-header />
    <div class="page-heading header-text">
        <div class="container"><div class="row"><div class="col-lg-12">
            <h3>{{ __('store.checkout_title') }}</h3>
            <span class="breadcrumb"><a href="{{ route('home') }}">{{ __('store.home') }}</a> &gt; <a href="{{ route('cart.index') }}">{{ __('store.cart') }}</a> &gt; {{ __('store.checkout') }}</span>
        </div></div></div>
    </div>
    <main class="container">
        @unless ($storeSetting->is_online)
            <div class="store-closed-warning" role="status">{{ __('store.store_closed_warning') }}</div>
        @endunless
        <div class="checkout-layout">
            @if ($errors->any())
                <div class="alert alert-danger" role="alert" style="grid-column: 1 / -1">{{ $errors->first() }}</div>
            @endif
            <section class="checkout-section">
                <div class="checkout-heading">
                    <h1 class="h4 mb-0">{{ __('store.checkout_review') }}</h1>
                    <p>{{ __('store.checkout_title') }}</p>
                </div>
                <div class="checkout-items">
                    @foreach ($items as $item)
                        <article class="checkout-item">
                            <div class="checkout-item-main">
                                @if ($item['variant']->product->image)
                                    <img class="checkout-product-image" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item['variant']->product->image) }}" alt="{{ $item['variant']->product->name }}">
                                @else
                                    <span class="checkout-product-placeholder" aria-hidden="true"><i class="fa fa-gamepad"></i></span>
                                @endif
                                <div class="checkout-item-copy">
                                    <h2>{{ $item['variant']->product->name }}</h2>
                                    <span class="text-secondary small">{{ $item['variant']->name }} × {{ $item['quantity'] }}</span>
                                    @if (filled($item['variant']->product->description))
                                        <details class="checkout-item-description">
                                            <summary>{{ __('store.description') }}</summary>
                                            <p>{{ $item['variant']->product->description }}</p>
                                        </details>
                                    @endif
                                </div>
                            </div>
                            <strong class="checkout-item-price">{{ $item['currencySymbol'] }} {{ number_format($item['displaySubtotal'], $item['currency'] === 'idr' ? 0 : 2, ',', '.') }}</strong>
                        </article>
                    @endforeach
                </div>
                <div class="checkout-total">
                    <span>{{ __('store.total') }}</span>
                    <strong>{{ $totalCurrencySymbol }} {{ number_format((float) $total, $totalCurrency === 'idr' ? 0 : 2, ',', '.') }}</strong>
                </div>
                <a class="btn btn-link px-0 mt-2 text-decoration-none" href="{{ route('cart.index') }}">← {{ __('store.back_to_cart') }}</a>
            </section>
            <section class="checkout-section">
            @if ($paymentSettings->isEmpty())
                <h2 class="h5 mb-3">{{ __('store.choose_payment_method') }}</h2>
                <div class="alert alert-warning mb-0">{{ __('store.payment_methods_unavailable') }}</div>
            @else
                <form method="POST" action="{{ route('checkout.store') }}">
                    @csrf
                    <div class="checkout-heading">
                        <h2 class="h5 mb-1">{{ __('store.customer_contact') }}</h2>
                        <p class="checkout-contact-note">{{ __('store.checkout_contact_note') }}</p>
                    </div>
                    <div class="d-grid gap-3 mb-4">
                        <div>
                            <label class="form-label" for="customer-email">{{ __('store.email') }}</label>
                            <input class="form-control @error('customer_email') is-invalid @enderror" id="customer-email" name="customer_email" type="email" value="{{ old('customer_email', $customerEmail) }}" maxlength="255" autocomplete="email" required>
                            <div class="form-text">{{ __('store.checkout_email_required_note') }}</div>
                            @error('customer_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="customer-phone">{{ __('store.phone') }}</label>
                            <input class="form-control @error('customer_phone') is-invalid @enderror" id="customer-phone" name="customer_phone" type="tel" value="{{ old('customer_phone', $customerPhone) }}" maxlength="40" autocomplete="tel" required>
                            @error('customer_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <h2 class="h5 mb-3">{{ __('store.choose_payment_method') }}</h2>
                    <div>
                        @foreach ($paymentSettings as $paymentSetting)
                            <label class="payment-method">
                                <span class="payment-method-choice">
                                    <input class="form-check-input" type="radio" name="payment_method_id" value="{{ $paymentSetting->id }}" @checked(old('payment_method_id') == $paymentSetting->id) required>
                                    @if ($paymentSetting->logo_path)
                                        <img class="payment-method-logo" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($paymentSetting->logo_path) }}" alt="">
                                    @endif
                                    <strong class="payment-method-name">{{ $paymentSetting->name ?: $paymentSetting->bank_name }}</strong>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <button class="btn checkout-submit w-100 mt-4" type="submit">{{ __('store.place_order') }} · {{ $totalCurrencySymbol }} {{ number_format((float) $total, $totalCurrency === 'idr' ? 0 : 2, ',', '.') }}</button>
                </form>
            @endif
            </section>
        </div>
    </main>
    <footer><div class="container"><div class="col-lg-12"><p>Copyright © {{ date('Y') }} Meoxstore. All rights reserved.</p></div></div></footer>
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
</body>
</html>
