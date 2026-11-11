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
        .checkout-item { align-items: center; border-bottom: 1px solid #edf0f3; display: flex; gap: 14px; justify-content: space-between; padding: 15px 0; }
        .checkout-product-image, .checkout-product-placeholder { background: #eef2f6; border-radius: 9px; flex: 0 0 64px; height: 64px; object-fit: cover; width: 64px; }
        .checkout-product-placeholder { align-items: center; color: #7c8999; display: flex; font-size: 22px; justify-content: center; }
        .checkout-item h2 { font-size: 15px; margin: 0 0 4px; }
        .checkout-item strong { color: #1976bd; white-space: nowrap; }
        .checkout-total { align-items: center; display: flex; justify-content: space-between; padding-top: 18px; }
        .checkout-total strong { color: #182230; font-size: 20px; }
        .checkout-contact-note { color: #77818e; font-size: 13px; line-height: 1.6; margin: -6px 0 18px; }
        .payment-method { background: #fff; border: 1px solid #e7ebef; border-radius: 9px; cursor: pointer; display: block; padding: 14px; transition: border-color .15s, background-color .15s; }
        .payment-method:has(input:checked) { background: #f3f8ff; border-color: #4d9ce0; }
        .payment-method + .payment-method { margin-top: 9px; }
        .payment-method-info { color: #637083; font-size: 13px; line-height: 1.55; }
        .checkout-submit { background: #009b69; border: 0; border-radius: 8px; color: #fff; font-weight: 650; min-height: 48px; }
        .checkout-submit:hover { background: #00845a; color: #fff; }
        .store-closed-warning { background: #fff7e8; border: 1px solid #f6d79c; border-radius: 10px; color: #7a4b00; font-size: 14px; line-height: 1.55; margin: 16px auto 0; max-width: 1080px; padding: 12px 16px; }
        .page-heading.header-text { border-radius: 0 0 56px 56px; padding: 108px 0 22px; }
        .page-heading.header-text h3 { font-size: 26px; margin: 10px 0; }
        @media (max-width: 767.98px) { .checkout-layout { grid-template-columns: 1fr; margin-top: 20px; } }
        @media (max-width: 767.98px) { .page-heading.header-text { padding: 96px 0 18px; } .page-heading.header-text h3 { font-size: 22px; } }
    </style>
</head>
<body>
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
                            <div class="align-items-center d-flex gap-3">
                                @if ($item['variant']->product->image)
                                    <img class="checkout-product-image" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item['variant']->product->image) }}" alt="{{ $item['variant']->product->name }}">
                                @else
                                    <span class="checkout-product-placeholder" aria-hidden="true"><i class="fa fa-gamepad"></i></span>
                                @endif
                                <div>
                                    <h2>{{ $item['variant']->product->name }}</h2>
                                    <span class="text-secondary small">{{ $item['variant']->name }} × {{ $item['quantity'] }}</span>
                                    @if (filled($item['variant']->product->description))
                                        <span class="d-block text-secondary small mt-1">{{ $item['variant']->product->description }}</span>
                                    @endif
                                </div>
                            </div>
                            <strong>{{ $item['currencySymbol'] }} {{ number_format($item['displaySubtotal'], $item['currency'] === 'idr' ? 0 : 2, ',', '.') }}</strong>
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
                                <span class="align-items-start d-flex gap-3">
                                    <input class="form-check-input mt-1" type="radio" name="payment_method_id" value="{{ $paymentSetting->id }}" @checked(old('payment_method_id') == $paymentSetting->id) required>
                                    <span>
                                        <strong>{{ $paymentSetting->name ?: $paymentSetting->bank_name }}</strong>
                                    @if ($paymentSetting->account_name)
                                        <span class="payment-method-info d-block mt-2">{{ __('store.account_name') }}: {{ $paymentSetting->account_name }}</span>
                                    @endif
                                    @if ($paymentSetting->account_number)
                                        <span class="payment-method-info d-block mt-2">{{ __('store.payment_to') }}: {{ $paymentSetting->account_number }}</span>
                                    @endif
                                    @if ($paymentSetting->qr_code_path)
                                        <span class="d-block mt-3">
                                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($paymentSetting->qr_code_path) }}" alt="{{ $paymentSetting->name }} QR code" style="display: block; height: auto; max-height: 180px; max-width: 100%; object-fit: contain; width: auto">
                                        </span>
                                    @endif
                                        @if ($paymentSetting->instructions)
                                            <span class="payment-method-info d-block mt-2"><strong>{{ __('store.payment_instructions') }}:</strong> {{ $paymentSetting->instructions }}</span>
                                        @endif
                                    </span>
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
