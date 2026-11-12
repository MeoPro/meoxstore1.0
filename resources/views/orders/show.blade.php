<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('store.order_details') }} {{ $order->publicNumber() }} | Meoxstore</title>
    @if (! in_array($order->status, ['completed', 'cancelled'], true))
        <meta http-equiv="refresh" content="60">
    @endif
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
        body.order-detail-page { background: #f3f6fa; font-size: 14px; }
        .order-page { margin: 0 auto; max-width: 1160px; padding: 126px 16px 64px; }
        .order-topline { align-items: center; display: flex; gap: 12px; margin-bottom: 20px; }
        .order-back { align-items: center; background: #fff; border: 1px solid #b9d4fa; border-radius: 11px; color: #2877c9; display: inline-flex; flex: 0 0 44px; height: 44px; justify-content: center; text-decoration: none; transition: background .18s ease, transform .18s ease; width: 44px; }
        .order-back:hover { background: #eaf3ff; color: #1b5fa9; transform: translateX(-2px); }
        .order-topline h1 { color: #172b4d; font-size: clamp(19px, 3.2vw, 24px); font-weight: 700; letter-spacing: -.025em; margin: 0; }
        .order-layout { align-items: start; display: grid; gap: 20px; grid-template-columns: minmax(0, 1.35fr) minmax(300px, .85fr); }
        .order-main-column, .order-side-column { display: grid; gap: 16px; min-width: 0; }
        .order-card { animation: order-enter .38s ease both; background: #fff; border: 1px solid #e5eaf1; border-radius: 14px; box-shadow: 0 6px 20px rgba(28, 48, 75, .045); padding: clamp(17px, 2.5vw, 24px); }
        body.order-detail-page .order-card h2 { color: #1c2c43; font-size: 15px !important; font-weight: 650; line-height: 1.35 !important; margin-bottom: 10px; }
        .order-card-heading { align-items: center; display: flex; flex-wrap: wrap; gap: 10px; justify-content: space-between; margin-bottom: 13px; }
        .order-status-pill { background: #eaf2ff; border-radius: 999px; color: #2468b5; font-size: 11px; font-weight: 650; padding: 6px 10px; }
        .order-status-pill.is-success { background: #e8f7ef; color: #16834a; }
        .order-status-pill.is-warning { background: #fff5df; color: #a96300; }
        .order-status-pill.is-danger { background: #ffeded; color: #b42318; }
        .order-timeline { align-items: flex-start; display: grid; grid-template-columns: repeat(4, 1fr); margin: 24px 0; }
        .order-step { color: #8b96a6; font-size: 11px; font-weight: 550; position: relative; text-align: center; }
        .order-step:not(:last-child)::after { background: #e1e7ef; content: ""; height: 3px; left: calc(50% + 17px); position: absolute; right: calc(-50% + 17px); top: 14px; }
        .order-step.is-done:not(:last-child)::after { background: #347bd1; }
        .order-step-dot { align-items: center; background: #e9edf2; border-radius: 50%; color: #8792a1; display: flex; font-size: 12px; height: 29px; justify-content: center; margin: 0 auto 8px; position: relative; width: 29px; z-index: 1; }
        .order-step.is-done { color: #24364e; }
        .order-step.is-done .order-step-dot { background: #347bd1; color: #fff; }
        .order-meta { border-top: 1px solid #edf0f4; display: grid; gap: 12px; padding-top: 17px; }
        .order-meta-row { align-items: center; display: flex; flex-wrap: wrap; gap: 8px 18px; justify-content: space-between; }
        .order-meta-label { color: #7d899a; }
        .order-number-value { align-items: center; display: inline-flex; font-weight: 650; gap: 8px; }
        .copy-order-number { background: transparent; border: 0; color: #2877c9; padding: 2px 5px; }
        .order-item { align-items: center; border-bottom: 1px solid #edf0f4; display: flex; gap: 12px; justify-content: space-between; padding: 12px 0; }
        .order-product-image, .order-product-placeholder { background: #eef2f6; border-radius: 9px; flex: 0 0 56px; height: 56px; object-fit: cover; width: 56px; }
        .order-product-placeholder { align-items: center; color: #7c8999; display: flex; font-size: 22px; justify-content: center; }
        .order-item-info { min-width: 0; }
        .order-item-info span { display: block; }
        .order-item-info { font-size: 13px; line-height: 1.5; }
        .order-item-info .fw-semibold, .order-item > strong { font-size: 13px; }
        .order-review h3 { font-size: 15px; font-weight: 650; }
        .order-total { align-items: center; border-top: 1px solid #edf0f4; display: flex; font-size: 15px; justify-content: space-between; padding-top: 14px; }
        .order-total strong { color: #e36b29; }
        .payment-card { background: #f8fafc; border: 1px solid #edf0f4; border-radius: 12px; margin-top: 14px; padding: 15px; }
        .payment-card p { font-size: 13px; }
        .order-note { color: #778397; font-size: 13px; line-height: 1.55; }
        .order-review { border-top: 1px solid #edf0f4; margin-top: 16px; padding-top: 16px; }
        .order-review .form-control { border-color: #dfe5ec; border-radius: 9px; }
        .order-rating { display: flex; flex-direction: row-reverse; gap: 5px; justify-content: flex-end; margin: 4px 0 14px; }
        .order-rating input { height: 1px; opacity: 0; position: absolute; width: 1px; }
        .order-rating label { color: #cbd3de; cursor: pointer; font-size: 30px; line-height: 1; transition: color .15s ease, transform .15s ease; }
        .order-rating label:hover, .order-rating label:hover ~ label,
        .order-rating input:checked ~ label { color: #f2a900; }
        .order-rating label:hover { transform: scale(1.12); }
        .order-rating input:focus-visible + label { outline: 3px solid #7aa7e8; outline-offset: 4px; }
        .payment-details-list { display: grid; gap: 0; margin: 12px 0 18px; }
        .payment-detail-row { align-items: start; border-bottom: 1px solid #e8edf3; display: grid; gap: 10px; grid-template-columns: minmax(105px, .75fr) minmax(0, 1.25fr); padding: 10px 0; }
        .payment-detail-label { color: #778397; }
        .payment-detail-value { font-weight: 650; overflow-wrap: anywhere; text-align: right; }
        .order-delivery-details-list { display: grid; margin: 12px 0 0; }
        .order-delivery-detail-row { align-items: baseline; border-bottom: 1px solid #e8edf3; display: flex; gap: 8px; padding: 10px 0; }
        .order-delivery-detail-label { color: #778397; flex: 0 0 auto; }
        .order-delivery-detail-value { font-weight: 650; margin: 0; overflow-wrap: anywhere; }
        .order-action { border-radius: 10px; font-weight: 650; min-height: 43px; }
        .order-detail-page .alert-info { background: #eaf5ff !important; border-color: #cfe5fa !important; color: #205176 !important; }
        @keyframes order-enter { from { opacity: 0; transform: translateY(9px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 767.98px) { .order-layout { grid-template-columns: 1fr; } .order-page { padding-top: 112px; } }
        @media (prefers-reduced-motion: reduce) { .order-card { animation: none; } .order-back { transition: none; } }
    </style>
</head>
<body class="order-detail-page storefront-page">
    <x-storefront-header />
    <main class="order-page">
        <div class="order-topline">
            <a class="order-back" href="{{ auth()->check() ? route('profile.edit') : route('orders.lookup') }}" aria-label="{{ __('store.back_to_orders') }}"><i class="fa fa-arrow-left" aria-hidden="true"></i></a>
            <h1>{{ __('store.order_details') }}</h1>
        </div>
        <div class="order-layout">
        <div class="order-main-column">
        <section class="order-card">
            @php
                $paymentCurrency = $paymentDetails['currency'] ?? 'idr';
                $paymentCurrencySymbol = match ($paymentCurrency) {
                    'myr' => 'RM',
                    'usd' => '$',
                    default => 'Rp',
                };
            @endphp
            @if (session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
            @endif
            <div class="order-card-heading">
                <div>
                    <h2 class="mb-1">{{ __('store.status_'.$order->status) }}</h2>
                    <p class="order-note mb-0">{{ __('store.order_refresh_hint') }}</p>
                </div>
                <span class="order-status-pill {{ $order->status === 'completed' ? 'is-success' : ($order->status === 'cancelled' ? 'is-danger' : ($order->status === 'pending' ? 'is-warning' : '')) }}">{{ __('store.status_'.$order->status) }}</span>
            </div>
            @php
                $progress = match ($order->status) {
                    'payment_submitted' => 0,
                    'payment_confirmed' => 1,
                    'completed' => $order->customer_completed_at === null ? 2 : 3,
                    default => -1,
                };
            @endphp
            <div class="order-timeline" aria-label="{{ __('store.order_progress') }}">
                @foreach (['payment_submitted', 'payment_confirmed', 'processing_delivery', 'completed'] as $step => $stepStatus)
                    <div class="order-step{{ $progress >= $step ? ' is-done' : '' }}">
                        <span class="order-step-dot"><i class="fa {{ $progress >= $step ? 'fa-check' : 'fa-circle' }}" aria-hidden="true"></i></span>
                        {{ __('store.order_step_'.$stepStatus) }}
                    </div>
                @endforeach
            </div>
            <div class="order-meta">
                <div class="order-meta-row">
                    <span class="order-meta-label">{{ __('store.order_number') }}</span>
                    <span class="order-number-value">{{ $order->publicNumber() }}
                        <button class="copy-order-number" type="button" data-copy-order="{{ $order->publicNumber() }}" aria-label="{{ __('store.copy_order_number') }}"><i class="fa fa-copy" aria-hidden="true"></i></button>
                    </span>
                </div>
                <div class="order-meta-row"><span class="order-meta-label">{{ __('store.order_date') }}</span><span>{{ $order->created_at->format('d M Y, H:i') }}</span></div>
                @if ($order->customer_completed_at)
                    <div class="order-meta-row"><span class="order-meta-label">{{ __('store.order_customer_completed_at') }}</span><span>{{ $order->customer_completed_at->format('d M Y, H:i') }}</span></div>
                @endif
            </div>
            @if ($order->status === 'completed' && $order->customer_completed_at === null)
                <form method="POST" action="{{ route('orders.complete', $order) }}" class="mt-4">
                    @csrf
                    <button class="btn btn-success order-action" type="submit">{{ __('store.confirm_order_complete') }}</button>
                    <p class="order-note mt-2 mb-0">{{ __('store.confirm_order_complete_help') }}</p>
                </form>
            @elseif ($order->customer_completed_at)
                <p class="alert alert-success mt-4 mb-0">{{ __('store.order_customer_completed') }}</p>
            @endif
            @if (in_array($order->status, ['payment_submitted', 'payment_confirmed', 'completed'], true))
                <section class="payment-card">
                    <h2>{{ __('store.contact_admin') }}</h2>
                    <p class="order-note">{{ __('store.admin_contact_after_payment') }}</p>
                    <a class="btn btn-success order-action" href="https://wa.me/{{ preg_replace('/\D+/', '', config('store.admin_phone')) }}" target="_blank" rel="noopener noreferrer">
                        <i class="fa fa-comments me-1" aria-hidden="true"></i>{{ config('store.admin_phone') }}
                    </a>
                </section>
            @endif
        </section>
        @if (in_array($order->status, ['payment_confirmed', 'completed'], true))
            <section class="order-card">
                <h2>{{ __('store.delivery_details') }}</h2>
                @if ($order->delivery_details)
                    <dl class="order-delivery-details-list">
                        @foreach ($order->delivery_details as $deliveryDetail)
                            <div class="order-delivery-detail-row">
                                <dt class="order-delivery-detail-label">{{ $deliveryDetail['label'] }}</dt>
                                <dd class="order-delivery-detail-value">{{ $deliveryDetail['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <p class="order-note mb-0">{{ $order->status === 'completed' ? __('store.delivery_details_empty') : __('store.delivery_details_waiting') }}</p>
                @endif
            </section>
        @endif
        <section class="order-card">
            <h2>{{ __('store.order_items') }}</h2>
            @foreach ($order->items as $item)
                @php($productDescription = $item->product_description ?: $item->product?->description)
                <div class="order-item">
                    <div class="align-items-center d-flex gap-3">
                        @if ($item->product?->image)
                            <img class="order-product-image" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->product->image) }}" alt="{{ $item->product_name }}">
                        @else
                            <span class="order-product-placeholder" aria-hidden="true"><i class="fa fa-gamepad"></i></span>
                        @endif
                        <span class="order-item-info"><span class="fw-semibold">{{ $item->product_name }}</span><span class="order-note">{{ $item->variant_name }} × {{ $item->quantity }}</span>@if (filled($productDescription))<span class="order-note">{{ $productDescription }}</span>@endif</span>
                    </div>
                    <strong>{{ $paymentCurrencySymbol }} {{ number_format($order->itemPaymentAmount($item, $paymentDetails), $paymentCurrency === 'idr' ? 0 : 2, ',', '.') }}</strong>
                </div>
            @endforeach
            <div class="order-total"><span>{{ __('store.total') }}</span><strong>{{ $paymentCurrencySymbol }} {{ number_format($order->paymentAmount($paymentDetails), $paymentCurrency === 'idr' ? 0 : 2, ',', '.') }}</strong></div>
        </section>
        @if ($order->customer_completed_at)
            <section class="order-card">
                <h2>{{ __('store.product_reviews') }}</h2>
                <p class="order-note">{{ __('store.review_order_intro') }}</p>
                @foreach ($reviewableProducts as $product)
                    <div class="order-review">
                        <h3 class="h6">{{ $product->name }}</h3>
                        @if ($reviewedProducts->has($product->id))
                            @php($review = $reviewedProducts->get($product->id))
                            <div class="text-warning" aria-label="{{ __('store.rating_out_of_five', ['rating' => $review->rating]) }}">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
                            @if (filled($review->comment))
                                <p class="mb-0">{{ $review->comment }}</p>
                            @endif
                        @else
                            <form method="POST" action="{{ route('orders.reviews.store', [$order, $product]) }}">
                                @csrf
                                <fieldset class="border-0 m-0 p-0">
                                    <legend class="form-label small">{{ __('store.give_stars') }}</legend>
                                    <div class="order-rating" role="radiogroup" aria-label="{{ __('store.give_stars') }}">
                                        @foreach (range(5, 1) as $rating)
                                            <input id="rating-{{ $product->id }}-{{ $rating }}" type="radio" name="rating" value="{{ $rating }}" aria-label="{{ __('store.stars', ['count' => $rating]) }}" required @checked(old('rating') == $rating)>
                                            <label for="rating-{{ $product->id }}-{{ $rating }}" title="{{ __('store.stars', ['count' => $rating]) }}" aria-hidden="true">★</label>
                                        @endforeach
                                    </div>
                                    @error('rating')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                                </fieldset>
                                <label class="form-label small" for="comment-{{ $product->id }}">{{ __('store.review') }}</label>
                                <textarea class="form-control mb-2" id="comment-{{ $product->id }}" name="comment" rows="3" maxlength="2000">{{ old('comment') }}</textarea>
                                <button class="btn btn-primary order-action" type="submit">{{ __('store.send_review') }}</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </section>
        @endif
        </div>
        <aside class="order-side-column">
            @if ($order->status === 'pending')
                <section class="order-card">
                    <h2 class="h5">{{ __('store.payment') }}</h2>
                    @if ($order->contact_email || $order->contact_phone)
                        <div class="mb-3">
                            <strong>{{ __('store.customer_contact') }}</strong>
                            @if ($order->contact_email)
                                <div>{{ $order->contact_email }}</div>
                            @endif
                            @if ($order->contact_phone)
                                <div>{{ $order->contact_phone }}</div>
                            @endif
                        </div>
                    @endif
                    @if ($paymentDetails && (($paymentDetails['name'] ?? $paymentDetails['bank_name'] ?? null) || ($paymentDetails['qr_code_path'] ?? null)))
                        <p class="mb-1">{{ __('store.transfer_to') }}:</p>
                        <div class="payment-details-list">
                            <div class="payment-detail-row"><span class="payment-detail-label">{{ __('store.payment') }}</span><span class="payment-detail-value">{{ $paymentDetails['name'] ?? $paymentDetails['bank_name'] ?? '—' }}</span></div>
                            @if ($paymentDetails['account_name'] ?? null)
                                <div class="payment-detail-row"><span class="payment-detail-label">{{ __('store.account_name') }}</span><span class="payment-detail-value">{{ $paymentDetails['account_name'] }}</span></div>
                            @endif
                            @if ($paymentDetails['account_number'] ?? null)
                                <div class="payment-detail-row"><span class="payment-detail-label">{{ __('store.payment_to') }}</span><strong class="payment-detail-value">{{ $paymentDetails['account_number'] }}</strong></div>
                            @endif
                        </div>
                        @if ($paymentDetails['qr_code_path'] ?? null)
                            <img class="d-block mx-auto mb-3 rounded border p-2" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($paymentDetails['qr_code_path']) }}" alt="{{ __('store.payment') }} QR code" style="height: auto; max-height: 240px; max-width: 100%; object-fit: contain; width: auto">
                        @endif
                        @if ($paymentDetails['instructions'] ?? null)
                            <p>{{ __('store.payment_instructions') }}: {{ $paymentDetails['instructions'] }}</p>
                        @endif
                        <div class="payment-details-list">
                            <div class="payment-detail-row">
                                <span class="payment-detail-label">{{ __('store.total') }}</span>
                                <strong class="payment-detail-value">{{ $paymentCurrencySymbol }} {{ number_format($order->paymentAmount($paymentDetails), $paymentCurrency === 'idr' ? 0 : 2, ',', '.') }}</strong>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">{{ __('store.payment_not_configured') }}</div>
                    @endif
                    @if ($paymentDetails && (($paymentDetails['name'] ?? $paymentDetails['bank_name'] ?? null) || ($paymentDetails['qr_code_path'] ?? null)))
                        <form method="POST" action="{{ route('orders.payment-proof', $order) }}" enctype="multipart/form-data">
                            @csrf
                            <label class="form-label" for="payment-proof">{{ __('store.payment_proof') }}</label>
                            <input class="form-control @error('payment_proof') is-invalid @enderror" id="payment-proof" name="payment_proof" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                            @error('payment_proof')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">{{ __('store.payment_proof_help') }}</div>
                            <button class="btn btn-primary rounded-pill mt-3" type="submit">{{ __('store.upload_payment_proof') }}</button>
                        </form>
                    @endif
                </section>
            @elseif ($order->status === 'payment_submitted')
                <section class="order-card"><p class="alert alert-info mb-0">{{ __('store.status_payment_submitted') }}</p></section>
            @endif
            @if ($order->status !== 'pending')
                <section class="order-card">
                    <h2>{{ __('store.payment_details') }}</h2>
                    @if ($paymentDetails)
                        <div class="order-meta">
                            <div class="order-meta-row"><span class="order-meta-label">{{ __('store.payment') }}</span><strong>{{ $paymentDetails['name'] ?? $paymentDetails['bank_name'] ?? '—' }}</strong></div>
                            @if ($paymentDetails['account_number'] ?? null)
                                <div class="order-meta-row"><span class="order-meta-label">{{ __('store.payment_to') }}</span><strong>{{ $paymentDetails['account_number'] }}</strong></div>
                            @endif
                            <div class="order-meta-row"><span class="order-meta-label">{{ __('store.total') }}</span><strong>{{ $paymentCurrencySymbol }} {{ number_format($order->paymentAmount($paymentDetails), $paymentCurrency === 'idr' ? 0 : 2, ',', '.') }}</strong></div>
                        </div>
                    @else
                        <p class="order-note mb-0">{{ __('store.payment_not_configured') }}</p>
                    @endif
                </section>
            @endif
            <section class="order-card d-flex flex-wrap align-items-center justify-content-between gap-3">
                <span>{{ __('store.order_help_prompt') }}</span>
                <a class="btn btn-outline-secondary order-action" href="{{ route('contact') }}">{{ __('store.contact_us') }}</a>
            </section>
        </aside>
        </div>
    </main>
    <footer><div class="container"><div class="col-lg-12"><p>Copyright © {{ date('Y') }} Meoxstore. All rights reserved.</p></div></div></footer>
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
    <script>
        document.querySelector('[data-copy-order]')?.addEventListener('click', async function () {
            const value = this.dataset.copyOrder;
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(value);
            } else {
                const input = document.createElement('textarea');
                input.value = value;
                input.style.position = 'fixed';
                input.style.opacity = '0';
                document.body.append(input);
                input.select();
                document.execCommand('copy');
                input.remove();
            }
            this.innerHTML = '<i class="fa fa-check" aria-hidden="true"></i>';
        });
    </script>
</body>
</html>
