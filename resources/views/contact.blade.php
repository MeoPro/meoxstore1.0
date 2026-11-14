<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('store.contact_us') }} | Meoxstore</title>
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
        .contact-section { padding: 72px 0; }
        .contact-intro { margin: 0 auto 38px; max-width: 650px; text-align: center; }
        .contact-intro h1 { font-size: clamp(30px, 4vw, 42px); margin: 10px 0 14px; }
        .contact-intro p, .contact-panel p, .contact-option p { color: #6a6a6a; line-height: 1.8; }
        .contact-kicker { color: #ee626b; font-size: 12px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        .contact-layout { align-items: stretch; }
        .contact-panel { background: #fff; border: 1px solid #eceef2; border-radius: 22px; height: 100%; padding: clamp(24px, 4vw, 38px); }
        .contact-panel h2 { font-size: clamp(22px, 3vw, 28px); line-height: 1.35; margin: 12px 0; }
        .contact-panel .contact-options { display: grid; gap: 14px; margin-top: 28px; }
        .contact-option { align-items: flex-start; background: #f7f8fb; border-radius: 14px; display: flex; gap: 15px; padding: 18px; }
        .contact-option-icon { align-items: center; background: #fff; border-radius: 12px; color: #ee626b; display: inline-flex; flex: 0 0 44px; font-size: 17px; height: 44px; justify-content: center; }
        .contact-option h3 { font-size: 15px; margin: 2px 0 5px; }
        .contact-option p { font-size: 13px; margin: 0; }
        .contact-option a { color: #0071f8; font-size: 13px; font-weight: 600; }
        .contact-cta { align-items: center; background: linear-gradient(145deg, #fff4f4, #f0f6ff); border-radius: 20px; display: flex; flex-direction: column; height: 100%; justify-content: center; min-height: 320px; padding: 32px; text-align: center; }
        .contact-cta-icon { align-items: center; background: #fff; border-radius: 50%; box-shadow: 0 12px 30px rgba(30, 40, 70, .08); color: #ee626b; display: flex; font-size: 30px; height: 76px; justify-content: center; margin-bottom: 22px; width: 76px; }
        .contact-cta h2 { font-size: 22px; margin-bottom: 10px; }
        .contact-cta p { color: #6a6a6a; line-height: 1.8; margin-bottom: 20px; max-width: 360px; }
        .contact-followup { margin-top: 54px; }
        .contact-followup-heading { margin-bottom: 22px; }
        .contact-followup-heading h2 { font-size: clamp(23px, 3vw, 30px); margin: 8px 0; }
        .contact-followup-heading p { color: #6a6a6a; line-height: 1.8; margin: 0; }
        .contact-details-panel, .contact-map-panel, .contact-form-panel { background: #fff; border: 1px solid #eceef2; border-radius: 20px; height: 100%; overflow: hidden; }
        .contact-details-panel { padding: 26px; }
        .contact-details-panel h3, .contact-form-panel h3 { font-size: 19px; margin-bottom: 18px; }
        .contact-detail-list { display: grid; gap: 12px; }
        .contact-detail { align-items: flex-start; background: #f7f8fb; border-radius: 12px; display: flex; gap: 13px; padding: 15px; }
        .contact-detail .contact-option-icon { flex-basis: 40px; height: 40px; }
        .contact-detail-label { color: #7a7a7a; display: block; font-size: 12px; margin: 1px 0 3px; }
        .contact-detail-value { color: #1e1e1e; font-size: 14px; font-weight: 600; overflow-wrap: anywhere; }
        .contact-map-panel { position: relative; }
        .contact-map-panel iframe { border: 0; display: block; height: 100%; min-height: 270px; width: 100%; }
        .contact-form-panel { padding: clamp(22px, 4vw, 32px); }
        .contact-form-panel .form-label { color: #333; font-size: 13px; font-weight: 600; }
        .contact-form-panel .form-control { border-color: #e1e5eb; border-radius: 10px; min-height: 46px; }
        .contact-form-panel textarea.form-control { min-height: 140px; resize: vertical; }
        .contact-form-panel .form-control:focus { border-color: #ee626b; box-shadow: 0 0 0 .2rem rgba(238, 98, 107, .12); }
        .contact-form-panel button { border: 0; }
        @media (max-width: 767.98px) { .contact-section { padding: 48px 0; } }
    </style>
</head>
<body class="storefront-page">
    <x-storefront-header />
    <div class="page-heading header-text">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h3>{{ __('store.contact_us') }}</h3>
                    <span class="breadcrumb"><a href="{{ route('home') }}">{{ __('store.home') }}</a> &gt; {{ __('store.contact_us') }}</span>
                </div>
            </div>
        </div>
    </div>

    <main class="contact-section">
        <div class="container">
            <header class="contact-intro">
                <span class="contact-kicker">{{ __('store.contact_eyebrow') }}</span>
                <h1>{{ __('store.contact_heading') }}</h1>
                <p>{{ __('store.contact_intro') }}</p>
            </header>

            <div class="row g-4 contact-layout">
                <div class="col-lg-7">
                    <section class="contact-panel">
                        <span class="contact-kicker">{{ __('store.contact_support_label') }}</span>
                        <h2>{{ __('store.contact_support_title') }}</h2>
                        <p>{{ __('store.contact_support_copy') }}</p>

                        <div class="contact-options">
                            <article class="contact-option">
                                <span class="contact-option-icon" aria-hidden="true"><i class="fa fa-gamepad"></i></span>
                                <div>
                                    <h3>{{ __('store.contact_product_title') }}</h3>
                                    <p>{{ __('store.contact_product_copy') }}</p>
                                    <a href="{{ route('shop') }}">{{ __('store.browse_products') }} <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
                                </div>
                            </article>
                            <article class="contact-option">
                                <span class="contact-option-icon" aria-hidden="true"><i class="fa fa-shopping-bag"></i></span>
                                <div>
                                    <h3>{{ __('store.contact_order_title') }}</h3>
                                    <p>{{ __('store.contact_order_copy') }}</p>
                                    <a href="{{ auth()->check() ? route('profile.edit') : route('login') }}">
                                        {{ auth()->check() ? __('store.view_orders') : __('store.sign_in') }}
                                        <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </article>
                        </div>
                    </section>
                </div>
                <div class="col-lg-5">
                    <aside class="contact-cta">
                        <span class="contact-cta-icon" aria-hidden="true"><i class="fa fa-comments"></i></span>
                        <h2>{{ __('store.contact_cta_title') }}</h2>
                        <p>{{ __('store.contact_cta_copy') }}</p>
                        <a class="btn btn-primary rounded-pill px-4" href="{{ route('games.index') }}">{{ __('store.browse_games') }}</a>
                    </aside>
                </div>
            </div>

            <section class="contact-followup" aria-labelledby="contact-followup-heading">
                <header class="contact-followup-heading">
                    <span class="contact-kicker">{{ __('store.contact_details_label') }}</span>
                    <h2 id="contact-followup-heading">{{ __('store.contact_details_title') }}</h2>
                    <p>{{ __('store.contact_details_copy') }}</p>
                </header>

                <div class="row g-4">
                    <div class="col-lg-5">
                        <section class="contact-details-panel">
                            <h3>{{ __('store.contact_information') }}</h3>
                            <div class="contact-detail-list">
                                @if (filled(config('store.address')))
                                    <article class="contact-detail">
                                        <span class="contact-option-icon" aria-hidden="true"><i class="fa fa-map-marker"></i></span>
                                        <div>
                                            <span class="contact-detail-label">{{ __('store.address') }}</span>
                                            <span class="contact-detail-value">{{ config('store.address') }}</span>
                                        </div>
                                    </article>
                                @endif
                                <article class="contact-detail">
                                    <span class="contact-option-icon" aria-hidden="true"><i class="fa fa-phone"></i></span>
                                    <div>
                                        <span class="contact-detail-label">{{ __('store.phone') }}</span>
                                        <a class="contact-detail-value" href="tel:{{ config('store.admin_phone') }}">{{ config('store.admin_phone') }}</a>
                                    </div>
                                </article>
                                <article class="contact-detail">
                                    <span class="contact-option-icon" aria-hidden="true"><i class="fa fa-envelope"></i></span>
                                    <div>
                                        <span class="contact-detail-label">{{ __('store.email') }}</span>
                                        <a class="contact-detail-value" href="mailto:{{ config('store.admin_email') }}">{{ config('store.admin_email') }}</a>
                                    </div>
                                </article>
                            </div>
                        </section>
                    </div>
                    <div class="col-lg-7">
                        <div class="contact-map-panel">
                            <iframe
                                src="{{ config('services.google_maps.embed_url') }}"
                                title="{{ __('store.contact_map_title') }}"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                    <div class="col-12">
                        <section class="contact-form-panel">
                            <span class="contact-kicker">{{ __('store.contact_form_label') }}</span>
                            <h3>{{ __('store.contact_form_title') }}</h3>
                            <form id="contact-message" action="mailto:{{ config('store.admin_email') }}" method="POST" enctype="text/plain">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="contact-name">{{ __('store.name') }}</label>
                                        <input class="form-control" id="contact-name" name="name" type="text" autocomplete="name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="contact-email">{{ __('store.email') }}</label>
                                        <input class="form-control" id="contact-email" name="email" type="email" autocomplete="email" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="contact-subject">{{ __('store.subject') }}</label>
                                        <input class="form-control" id="contact-subject" name="subject" type="text" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="contact-message-body">{{ __('store.message') }}</label>
                                        <textarea class="form-control" id="contact-message-body" name="message" required></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-primary rounded-pill px-4 py-2" type="submit">{{ __('store.send_message') }}</button>
                                    </div>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <x-storefront-footer />
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
</body>
</html>
