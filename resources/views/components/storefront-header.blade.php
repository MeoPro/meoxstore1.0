<header @class([
    'header-area',
    'header-sticky',
    'header-scroll-toggle' => request()->routeIs('profile.*', 'checkout.show', 'orders.show', 'orders.lookup*'),
])>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav class="main-nav" aria-label="{{ __('store.main_navigation') }}">
                    <a href="{{ route('home') }}" class="logo">
                        <img src="{{ asset('images/logo.png') }}" alt="Meoxstore" style="width: 158px; height: auto; filter: brightness(0) invert(1);">
                    </a>
                    <ul class="nav">
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
                        @endguest
                        <li class="storefront-language-list-item">
                            <x-storefront-language-switcher />
                        </li>
                    </ul>
                    <a class="menu-trigger"><span>Menu</span></a>
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
    .header-area.header-scroll-toggle.header-hidden { transform: translateY(-100%); }
    .header-area.header-scroll-toggle .main-nav .logo { margin-top: 18px; }
    .header-area.header-scroll-toggle .main-nav .logo img { max-width: 110px; }
    .header-area.header-scroll-toggle .main-nav .nav li a { color: #fff !important; }
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
