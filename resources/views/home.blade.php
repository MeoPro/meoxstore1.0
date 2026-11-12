    <!DOCTYPE html>
    <html lang="{{ app()->getLocale() }}">

    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        <title>Meoxstore | Toko Game</title>

        <!-- Bootstrap core CSS -->
        <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">


        <!-- Additional CSS Files -->
        @vite([
            'resources/css/app.css',
            'resources/css/fontawesome.css',
            'resources/css/templatemo-lugx-gaming.css',
            'resources/css/owl.css',
            'resources/css/animate.css',
            'resources/js/app.js',
        ])
        <link rel="stylesheet"href="https://unpkg.com/swiper@7/swiper-bundle.min.css"/>
        <style>
            .home-trending-items .shop-product-card { background: #fff; border: 1px solid #e6eaf2; box-shadow: 0 12px 30px rgba(30, 56, 90, .07); display: flex; flex-direction: column; height: 100%; position: relative; transition: transform .2s ease, box-shadow .2s ease; width: 100%; }
            .home-trending-items .shop-product-card .thumb { aspect-ratio: 1 / 1; flex: 0 0 auto; overflow: hidden; position: relative; }
            .home-trending-items .shop-product-card .thumb > a, .home-trending-items .shop-product-card .thumb img { display: block; height: 100%; width: 100%; }
            .home-trending-items .shop-product-card .thumb img { object-fit: cover; }
            .home-trending-items .shop-product-badges { align-items: flex-start; display: flex; flex-wrap: wrap; gap: 6px; left: 12px; position: absolute; right: 12px; top: 12px; z-index: 1; }
            .home-trending-items .shop-product-badge { border-radius: 999px; color: #fff; font-size: 11px; font-weight: 700; padding: 7px 12px; text-transform: uppercase; }
            .home-trending-items .shop-product-badge--promo, .home-trending-items .shop-product-badge--discount { background: #ee626b; }
            .home-trending-items .shop-product-badge--trending { background: #0071f8; }
            .home-trending-items .shop-product-badge--hot { background: #f97316; }
            .home-trending-items .shop-product-card .down-content { display: flex; flex: 1; flex-direction: column; }
            .home-trending-items .shop-product-card .down-content .category { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .home-trending-items .shop-product-card .down-content h4 { display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; line-height: 1.4; max-height: 2.8em; min-height: 2.8em; overflow: hidden; }
            .home-trending-items .shop-product-price { align-items: baseline; display: flex; flex-wrap: wrap; gap: 4px 8px; margin-top: 10px; }
            .home-trending-items .shop-product-price-current { color: #0071f8; font-size: 18px; font-weight: 700; }
            .home-trending-items .shop-product-price-before { color: #8790a0; font-size: 12px; text-decoration: line-through; }
            .home-trending-items .shop-product-stats { align-items: flex-start; color: #727b89; display: flex; flex-direction: column; font-size: 12px; gap: 3px; margin-top: auto; padding-top: 8px; }
            .home-trending-items .shop-product-rating-line { align-items: center; display: flex; gap: 12px; }
            .home-trending-items .shop-product-rating { align-items: center; color: #edae22; display: inline-flex; gap: 4px; white-space: nowrap; }
            .home-trending-items .shop-product-rating-count, .home-trending-items .shop-rating-empty { color: #727b89; }
            .home-category-grid { display: grid; gap: 24px; grid-template-columns: repeat(5, minmax(0, 1fr)); }
            .home-category-card { background: #fff; border: 1px solid #e6eaf2; border-radius: 14px; height: 100%; overflow: hidden; }
            .home-category-card-image { display: block; }
            .home-category-card-image img { aspect-ratio: 1 / 1; display: block; object-fit: cover; width: 100%; }
            .home-category-card-content { padding: 16px; }
            .home-category-card h3 { font-size: 17px; margin: 0 0 8px; }
            .home-category-card p { color: #7a7a7a; font-size: 13px; margin: 0 0 10px; }
            .home-category-card-link { color: #ee626b; font-size: 13px; font-weight: 600; text-decoration: none; }
            .home-category-card-link:hover { color: #ee626b; }
            @media (min-width: 768px) and (max-width: 1199.98px) {
                .home-category-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            }
            @media (max-width: 767.98px) {
                .home-category-grid { gap: 16px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
                .home-category-card-content { padding: 12px; }
                .home-category-card h3 { font-size: 15px; }
            }
            @media (min-width: 768px) and (max-width: 1199.98px) {
                .home-trending-items .trending-items { width: 33.333333%; }
            }
            @media (min-width: 1200px) {
                .home-trending-items .trending-items { width: 20%; }
            }
            @media (max-width: 767.98px) {
                .home-trending-items .trending-items { width: 50%; }
            }
            @media (hover: hover) and (prefers-reduced-motion: no-preference) {
                .home-trending-items .shop-product-card:hover { box-shadow: 0 20px 42px rgba(30, 56, 90, .16); transform: scale(1.035); z-index: 2; }
            }
            .home-store-status { align-items: center; background: #fff; border: 1px solid #e6eaf2; border-radius: 999px; box-shadow: 0 3px 10px rgba(30, 56, 90, .08); color: #344054; display: inline-flex; font-size: 13px; font-weight: 600; gap: 8px; margin-top: 16px; padding: 8px 14px; }
            .home-store-status::before { background: #16a765; border-radius: 50%; content: ''; height: 10px; width: 10px; }
            .home-store-status--closed::before { background: #f79009; }
            .home-store-stats { background: rgba(255, 255, 255, .94); border: 1px solid rgba(255, 255, 255, .55); border-radius: 14px; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); margin-top: 12px; max-width: 700px; padding: 14px 12px; }
            .home-store-stats > div { min-width: 0; padding: 6px 16px; text-align: center; }
            .home-store-stats > div + div { border-left: 1px solid #dfe4ec; }
            .home-store-stat-label { color: #756a99; display: block; font-size: 14px; }
            .home-store-stat-value { color: #33265d; display: block; font-size: 23px; font-weight: 700; line-height: 1.4; }
            @media (max-width: 575.98px) {
                .home-store-stats { grid-template-columns: 1fr; }
                .home-store-stats > div { padding: 8px 12px; }
                .home-store-stats > div + div { border-left: 0; border-top: 1px solid #dfe4ec; }
            }
            .store-closed-warning { background: #fff7e8; border: 1px solid #f6d79c; border-radius: 10px; color: #7a4b00; font-size: 14px; line-height: 1.55; margin: 16px auto 0; max-width: 1080px; padding: 12px 16px; }
            .main-banner .container > .store-closed-warning { margin-left: 12px; margin-right: 12px; }
        </style>
    <!--

    TemplateMo 589 lugx gaming

    https://templatemo.com/tm-589-lugx-gaming

    -->
    </head>

    <body class="storefront-page">

    <!-- ***** Preloader Start ***** -->
    <div id="js-preloader" class="js-preloader">
        <div class="preloader-inner">
        <span class="dot"></span>
        <div class="dots">
            <span></span>
            <span></span>
            <span></span>
        </div>
        </div>
    </div>
    <!-- ***** Preloader End ***** -->

    <!-- ***** Header Area Start ***** -->
    <x-storefront-header />
    <!-- ***** Header Area End ***** -->

    <div class="main-banner">
        <div class="container">
        <div class="row">
            <div class="col-lg-6 align-self-center">
            <div class="caption header-text">
                <h6>Welcome to MeoXStore</h6>
                <h2>BEST GAMING SITE EVER!</h2>
                <p>MeoXStore telah hadir sejak 2019, menyediakan berbagai kebutuhan gaming dengan layanan cepat, aman, dan terpercaya. Temukan produk dan layanan terbaik untuk pengalaman gaming yang lebih seru.</p>
                <div class="main-button">
                    <a href="{{ route('games.index') }}">{{ __('store.browse_games') }}</a>
                </div>
            </div>
            </div>
            <div class="col-lg-4 offset-lg-2">
            <div class="right-image">
                <img src="{{ asset('images/banner-image.jpg') }}" alt="">
                <span class="price">$22</span>
                <span class="offer">-40%</span>
            </div>
            </div>
        </div>
        <div @class(['home-store-status', 'home-store-status--closed' => ! $storeSetting->is_online]) role="status">
            {{ $storeSetting->is_online ? __('store.home_system_online') : __('store.home_system_offline') }}
        </div>
        <div class="home-store-stats" aria-label="{{ __('store.store_statistics') }}">
            <div>
                <span class="home-store-stat-label">{{ __('store.items_sold') }}</span>
                <strong class="home-store-stat-value">{{ number_format($completedSoldItemCount) }}</strong>
            </div>
            <div>
                <span class="home-store-stat-label">{{ __('store.robux_sold') }}</span>
                <strong class="home-store-stat-value">{{ $robuxSoldDisplay }} Bobux</strong>
            </div>
            <div>
                <span class="home-store-stat-label">{{ __('store.order_queue') }}</span>
                <strong class="home-store-stat-value">{{ number_format($activeOrderQueueCount) }}</strong>
            </div>
        </div>
        @unless ($storeSetting->is_online)
            <p class="store-closed-warning" role="status">{{ __('store.store_closed_warning') }}</p>
        @endunless
        </div>
    </div>

    <div class="features">
        <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6">
            <a href="#">
                <div class="item">
                <div class="image">
                    <img src="{{ asset('images/featured-01.png') }}" alt="" style="max-width: 44px;">
                </div>
                <h4>Free Storage</h4>
                </div>
            </a>
            </div>
            <div class="col-lg-3 col-md-6">
            <a href="#">
                <div class="item">
                <div class="image">
                    <img src="{{ asset('images/featured-02.png') }}" alt="" style="max-width: 44px;">
                </div>
                <h4>User More</h4>
                </div>
            </a>
            </div>
            <div class="col-lg-3 col-md-6">
            <a href="#">
                <div class="item">
                <div class="image">
                    <img src="{{ asset('images/featured-03.png') }}" alt="" style="max-width: 44px;">
                </div>
                <h4>Reply Ready</h4>
                </div>
            </a>
            </div>
            <div class="col-lg-3 col-md-6">
            <a href="#">
                <div class="item">
                <div class="image">
                    <img src="{{ asset('images/featured-04.png') }}" alt="" style="max-width: 44px;">
                </div>
                <h4>Easy Layout</h4>
                </div>
            </a>
            </div>
        </div>
        </div>
    </div>

    <div class="section trending home-trending-items">
        <div class="container">
        <div class="row">
            <div class="col-lg-6">
            <div class="section-heading">
                <h6>{{ __('store.trending_items_label') }}</h6>
                <h2>{{ __('store.trending_items_title') }}</h2>
            </div>
            </div>
            <div class="col-lg-6">
            <div class="main-button">
                <a href="{{ route('shop') }}">{{ __('store.view_all_products') }}</a>
            </div>
            </div>
            @forelse ($trendingItems as $product)
                @php
                    $startingPrice = $product->variants->min(
                        fn ($variant) => $product->priceInIdr((float) $variant->price, $currencyRates),
                    );
                    $startingPriceBeforeDiscount = $product->variants->min(
                        fn ($variant) => $product->priceBeforeDiscountInIdr((float) $variant->price, $currencyRates),
                    );
                    $stockCount = $product->variants->sum('stock');
                    $fallbackImage = asset('images/trending-0'.(($loop->index % 4) + 1).'.jpg');
                @endphp
                <div class="col-6 col-md-6 col-lg-3 trending-items">
                    <div class="item shop-product-card">
                        <div class="thumb">
                            <a href="{{ route('product-details.show', $product) }}">
                                <img src="{{ $product->image ? \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) : $fallbackImage }}" alt="{{ $product->name }}">
                            </a>
                            @if ($product->badges !== [])
                                <div class="shop-product-badges">
                                    @foreach ($product->badges as $badge)
                                        <span class="shop-product-badge shop-product-badge--{{ $badge }}">
                                            @if ($badge === 'discount')
                                                {{ __('store.discount_badge', ['percent' => rtrim(rtrim(number_format((float) $product->discount_percent, 2, '.', ''), '0'), '.')]) }}
                                            @else
                                                {{ __('store.badge_'.$badge) }}
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="down-content">
                            <span class="category">{{ $product->gameCategory?->name ?? $product->itemCategory?->localizedName() }}</span>
                            <h4>{{ $product->name }}</h4>
                            <div class="shop-product-price">
                                @if ($product->hasDiscount())
                                    <span class="shop-product-price-before">Rp {{ rtrim(rtrim(number_format($startingPriceBeforeDiscount, 2, '.', ','), '0'), '.') }}</span>
                                @endif
                                <span class="shop-product-price-current">Rp {{ rtrim(rtrim(number_format($startingPrice, 2, '.', ','), '0'), '.') }}</span>
                            </div>
                            <div class="shop-product-stats">
                                <span>{{ __('store.stock_count', ['count' => number_format($stockCount)]) }}</span>
                                <div class="shop-product-rating-line">
                                    <span class="shop-product-rating" aria-label="{{ __('store.rating_out_of_five', ['rating' => number_format((float) ($product->reviews_avg_rating ?? 0), 1)]) }}">
                                        @for ($star = 1; $star <= 5; $star++)
                                            <span @class(['shop-rating-empty' => $star > round((float) ($product->reviews_avg_rating ?? 0))])>★</span>
                                        @endfor
                                        <span class="shop-product-rating-count">{{ number_format((float) ($product->reviews_avg_rating ?? 0), 1) }} ({{ $product->reviews_count }})</span>
                                    </span>
                                    <span>{{ __('store.sold_count', ['count' => number_format((int) ($product->sold_quantity ?? 0))]) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-center text-secondary">{{ __('store.trending_items_empty') }}</p>
                </div>
            @endforelse
        </div>
        </div>
    </div>

    <div class="section most-played">
        <div class="container">
        <div class="row">
                <div class="col-lg-12">
                    <div class="section-heading">
                        <h6>{{ __('store.game') }}</h6>
                        <h2>{{ __('store.best_selling_game_categories') }}</h2>
                    </div>
            </div>
                <div class="col-lg-12">
                    @if ($bestSellingGameCategories->isEmpty())
                        <p class="text-center text-secondary">{{ __('store.best_selling_categories_empty') }}</p>
                    @else
                        <div class="home-category-grid">
                            @foreach ($bestSellingGameCategories as $gameCategory)
                                @php($representativeProduct = $gameCategory->products->first())
                                @php($gameImage = $gameCategory->image
                                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($gameCategory->image)
                                    : ($representativeProduct?->image
                                        ? \Illuminate\Support\Facades\Storage::disk('public')->url($representativeProduct->image)
                                        : asset('images/categories-0'.(($loop->index % 5) + 1).'.jpg')))
                                <article class="home-category-card">
                                    <a class="home-category-card-image" href="{{ route('shop', ['game' => $gameCategory->id]) }}">
                                        <img src="{{ $gameImage }}" alt="{{ $gameCategory->name }}">
                                    </a>
                                    <div class="home-category-card-content">
                                        <h3>{{ $gameCategory->name }}</h3>
                                        <p>{{ __('store.products_available', ['count' => $gameCategory->available_products_count]) }}</p>
                                        <a class="home-category-card-link" href="{{ route('shop', ['game' => $gameCategory->id]) }}">
                                            {{ __('store.view_products') }} <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            </div>
        </div>
    </div>

    <div class="section categories">
        <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="section-heading">
                    <h6>{{ __('store.item') }}</h6>
                    <h2>{{ __('store.best_selling_item_categories') }}</h2>
                </div>
            </div>
            <div class="col-lg-12">
                @if ($bestSellingItemCategories->isEmpty())
                    <p class="text-center text-secondary">{{ __('store.best_selling_categories_empty') }}</p>
                @else
                    <div class="home-category-grid">
                        @foreach ($bestSellingItemCategories as $itemCategory)
                            @php($representativeProduct = $itemCategory->products->first())
                            @php($itemImage = $itemCategory->image
                                ? \Illuminate\Support\Facades\Storage::disk('public')->url($itemCategory->image)
                                : ($representativeProduct?->image
                                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($representativeProduct->image)
                                    : asset('images/categories-0'.(($loop->index % 5) + 1).'.jpg')))
                            <article class="home-category-card">
                                <a class="home-category-card-image" href="{{ route('shop', ['item_category' => $itemCategory->id]) }}">
                                    <img src="{{ $itemImage }}" alt="{{ $itemCategory->localizedName() }}">
                                </a>
                                <div class="home-category-card-content">
                                    <h3>{{ $itemCategory->localizedName() }}</h3>
                                    <p>{{ __('store.products_available', ['count' => $itemCategory->available_products_count]) }}</p>
                                    <a class="home-category-card-link" href="{{ route('shop', ['item_category' => $itemCategory->id]) }}">
                                        {{ __('store.view_products') }} <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        </div>
    </div>
    
    <div class="section cta">
        <div class="container">
        <div class="row">
            <div class="col-lg-5">
            <div class="shop">
                <div class="row">
                <div class="col-lg-12">
                    <div class="section-heading">
                    <h6>Our Shop</h6>
                    <h2>Go Pre-Order Buy & Get Best <em>Prices</em> For You!</h2>
                    </div>
                    <p>Lorem ipsum dolor consectetur adipiscing, sed do eiusmod tempor incididunt.</p>
                    <div class="main-button">
                    <a href="{{ route('shop') }}">Shop Now</a>
                    </div>
                </div>
                </div>
            </div>
            </div>
            <div class="col-lg-5 offset-lg-2 align-self-end">
            <div class="subscribe">
                <div class="row">
                <div class="col-lg-12">
                    <div class="section-heading">
                    <h6>NEWSLETTER</h6>
                    <h2>Get Up To $100 Off Just Buy <em>Subscribe</em> Newsletter!</h2>
                    </div>
                    <div class="search-input">
                    <form id="subscribe" action="#">
                        <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Your email...">
                        <button type="submit">Subscribe Now</button>
                    </form>
                    </div>
                </div>
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>

    <footer>
        <div class="container">
        <div class="col-lg-12">
            <p>Copyright © {{ date('Y') }} Meoxstore. All rights reserved. &nbsp;&nbsp; <a rel="nofollow" href="https://templatemo.com" target="_blank">Design: TemplateMo</a></p>
        </div>
        </div>
    </footer>

    <!-- Scripts -->
    <!-- Bootstrap core JavaScript -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>

    </body>
    </html>