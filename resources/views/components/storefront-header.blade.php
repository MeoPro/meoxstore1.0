@props(['showLogout' => false])

<header @class([
    'header-area',
    'header-sticky',
    'header-scroll-toggle' => request()->routeIs('profile.*', 'checkout.show', 'orders.show', 'orders.lookup*'),
    'header-has-storefront-rating' => $storefrontRatingAverage !== null,
])>
    @if ($storefrontRatingAverage !== null)
        <div class="storefront-rating-banner">
            <div class="container">
                <a class="storefront-rating-link" href="{{ route('reviews.index') }}">
                    <span>{{ __('store.store_rating_summary', ['rating' => rtrim(rtrim(number_format($storefrontRatingAverage, 1, '.', ''), '0'), '.')]) }}</span>
                    <span class="storefront-rating-star" aria-hidden="true">★</span>
                    <span>{{ __('store.store_rating_brand') }}</span>
                </a>
            </div>
        </div>
    @endif
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav class="main-nav" aria-label="{{ __('store.main_navigation') }}">
                    <a href="{{ route('home') }}" class="logo">
                        <img src="{{ asset('images/logo.png') }}" alt="Meoxstore" style="width: 158px; height: auto; filter: brightness(0) invert(1);">
                    </a>
                    <ul class="nav" id="storefront-main-navigation">
                        <li><a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')])>{{ __('store.home') }}</a></li>
                        @include('components.storefront-shop-menu', ['featuredGames' => $storefrontFeaturedGames])
                        <li><a href="{{ route('about') }}" @class(['active' => request()->routeIs('about')])>{{ __('store.about_us') }}</a></li>
                        <li><a href="{{ route('contact') }}" @class(['active' => request()->routeIs('contact')])>{{ __('store.contact_us') }}</a></li>
                        <li><a href="{{ route('orders.lookup') }}" @class(['active' => request()->routeIs('orders.lookup*')])>{{ __('store.track_order') }}</a></li>
                        <li>
                            <a href="{{ route('cart.index') }}" @class(['active' => request()->routeIs('cart.*')])>
                                {{ __('store.cart') }}<span class="storefront-cart-count" data-storefront-cart-count>{{ $storefrontCartCount }}</span>
                            </a>
                        </li>
                        @guest
                            <li><a href="{{ route('login') }}">{{ __('store.sign_in') }}</a></li>
                        @else
                            <li><a href="{{ route('profile.edit') }}" @class(['active' => request()->routeIs('profile.*')])>{{ __('store.profile') }}</a></li>
                            @if ($showLogout)
                                <li class="storefront-logout-list-item">
                                    <form class="storefront-logout-form" method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="storefront-logout-button" type="submit">{{ __('store.log_out') }}</button>
                                    </form>
                                </li>
                            @endif
                        @endguest
                        <li class="storefront-language-list-item">
                            <x-storefront-language-switcher />
                        </li>
                    </ul>
                    <button class="menu-trigger" type="button" aria-label="{{ __('store.toggle_navigation') }}" aria-expanded="false" aria-controls="storefront-main-navigation">
                        <span aria-hidden="true">Menu</span>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</header>
<style>
    .header-area.header-scroll-toggle {
        background: #0071f8 !important;
        border-radius: 0 0 25px 25px;
        box-shadow: 0 0 10px rgba(0, 0, 0, .15) !important;
        height: 80px !important;
        left: 0;
        position: fixed !important;
        right: 0;
        top: 0 !important;
        transition: transform .25s ease, background-color .5s ease, box-shadow .5s ease;
        z-index: 1000;
    }
    .header-area.header-has-storefront-rating { top: 40px !important; }
    .header-area.header-has-storefront-rating .storefront-rating-banner { top: -40px; }
    .header-area.header-scroll-toggle.header-hidden { transform: translateY(-100%); }
    .header-area.header-scroll-toggle .main-nav .logo { margin-top: 18px; }
    .header-area.header-scroll-toggle .main-nav .logo img { max-width: 110px; }
    .header-area.header-scroll-toggle .main-nav .nav li a { color: #fff !important; }
    .header-area .main-nav .nav li .storefront-logout-button {
        background: transparent;
        border: 0;
        color: #1e1e1e;
        cursor: pointer;
        font: inherit;
        font-size: 13px;
        font-weight: 500;
        line-height: 40px;
        padding: 0 10px;
        text-transform: uppercase;
    }
    .header-area .main-nav .nav li .storefront-logout-button:hover { color: #ee626b; }
    .header-area.header-scroll-toggle .main-nav .nav li .storefront-logout-button { color: #fff; }
    .storefront-logout-form { margin: 0; }
    .header-area.header-scroll-toggle .main-nav .nav .storefront-shop-dropdown li a { color: #1e1e1e !important; }
    .header-area.header-scroll-toggle .main-nav .nav .storefront-shop-dropdown li a:hover,
    .header-area.header-scroll-toggle .main-nav .nav .storefront-shop-dropdown li a[aria-current="page"] { color: #ee626b !important; }
    .header-area.header-scroll-toggle .main-nav .nav .storefront-shop-menu .storefront-shop-toggle-icon { border-color: #fff; }
    .header-area.header-scroll-toggle .main-nav .menu-trigger span,
    .header-area.header-scroll-toggle .main-nav .menu-trigger span::before,
    .header-area.header-scroll-toggle .main-nav .menu-trigger span::after { background-color: #fff; }
    @media (min-width: 768px) and (max-width: 1100px) {
        .header-area.header-scroll-toggle .main-nav .nav > li > a { color: #1e1e1e !important; }
        .header-area.header-scroll-toggle .main-nav .nav > li > a:hover,
        .header-area.header-scroll-toggle .main-nav .nav > li > a.active { background: #f1f0fe !important; color: #ee626b !important; }
    }
</style>
<div class="storefront-cart-notice" data-cart-notice role="status" aria-live="polite" aria-atomic="true"></div>
@php($whatsappPhone = preg_replace('/\D+/', '', (string) config('store.admin_phone', '')))
@if ($whatsappPhone !== '')
    <a
        class="storefront-whatsapp-float"
        href="https://wa.me/{{ $whatsappPhone }}"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="{{ __('store.whatsapp_chat_label') }}"
        title="{{ __('store.whatsapp_chat_label') }}">
        <i class="fa fa-whatsapp" aria-hidden="true"></i>
    </a>
@endif
