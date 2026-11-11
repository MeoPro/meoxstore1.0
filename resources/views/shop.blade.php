<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <title>Meoxstore | {{ __('store.our_shop_title') }}</title>

    <!-- Bootstrap core CSS -->
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
      .shop-page-heading { padding: 108px 0 22px; }
      .shop-page-heading h3 { font-size: 26px; margin: 8px 0 12px; }
      .shop-page-section { margin-top: 30px; }
      .shop-toolbar { align-items: center; display: flex; flex-wrap: wrap; gap: 12px 24px; justify-content: space-between; margin-bottom: 24px; }
      .shop-currency-toggle { align-items: center; display: flex; flex: 0 0 auto; flex-direction: column; gap: 4px; justify-content: center; margin: 0 auto; text-align: center; }
      .shop-currency-toggle .btn { border-color: #ee626b; color: #ee626b; }
      .shop-currency-toggle .btn[aria-pressed="true"] { background: #ee626b; color: #fff; }
      .shop-category-filters { margin: 0; }
      .shop-category-filter-row { align-items: center; display: flex; flex-wrap: wrap; gap: 4px 12px; justify-content: flex-end; }
      .shop-category-filter-label { color: #7a7a7a; font-size: 12px; font-weight: 600; text-transform: uppercase; }
      .shop-category-filters { width: 100%; }
      .shop-category-filter-row { justify-content: center; width: 100%; }
      .shop-category-filter-label { display: none; }
      .trending .shop-category-filter-row ul.trending-filter { display: flex; flex: 0 1 auto; flex-wrap: nowrap; gap: 8px; margin: 0 auto; max-width: 100%; min-width: 0; overflow-x: auto; overflow-y: hidden; padding: 4px 2px 10px; scrollbar-width: thin; text-align: left; white-space: nowrap; width: max-content; -webkit-overflow-scrolling: touch; }
      .shop-category-filter-row .trending-filter li { margin-top: 0; margin-bottom: 0; }
      .shop-category-filter-row .trending-filter li { flex: 0 0 auto; margin: 0; }
      .trending ul.trending-filter li[hidden] { display: none; }
      .trending .shop-product-card { background: #fff; border: 1px solid #e6eaf2; box-shadow: 0 12px 30px rgba(30, 56, 90, .07); display: flex; flex-direction: column; height: 100%; position: relative; transition: transform .2s ease, box-shadow .2s ease; width: 100%; }
      @media (hover: hover) and (prefers-reduced-motion: no-preference) {
        .trending .shop-product-card:hover { box-shadow: 0 20px 42px rgba(30, 56, 90, .16); transform: scale(1.035); z-index: 2; }
      }
      .shop-product-card .thumb { aspect-ratio: 1 / 1; flex: 0 0 auto; }
      .shop-product-card .thumb > a { display: block; height: 100%; }
      .shop-product-card .thumb > a > img { height: 100%; object-fit: cover; width: 100%; }
      .shop-product-badges { align-items: flex-start; display: flex; flex-wrap: wrap; gap: 6px; left: 12px; position: absolute; right: 12px; top: 12px; z-index: 1; }
      .shop-product-badge { border-radius: 999px; color: #fff; font-size: 11px; font-weight: 700; padding: 7px 12px; text-transform: uppercase; }
      .shop-product-badge--promo { background: #ee626b; }
      .shop-product-badge--trending { background: #0071f8; }
      .shop-product-badge--hot { background: #f97316; }
      .shop-product-badge--discount { background: #ee626b; }
      .shop-product-card .down-content { display: flex; flex: 1; flex-direction: column; }
      .shop-product-card .down-content .category { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
      .shop-product-card .down-content h4 { display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; line-height: 1.4; max-height: 2.8em; min-height: 2.8em; overflow: hidden; }
      .shop-product-price { align-items: baseline; display: flex; flex-wrap: wrap; gap: 4px 8px; margin-top: 10px; }
      .shop-product-price-current { color: #0071f8; font-size: 18px; font-weight: 700; }
      .shop-product-price-before { color: #8790a0; font-size: 12px; text-decoration: line-through; }
      .shop-product-stats { align-items: flex-start; color: #727b89; display: flex; flex-direction: column; font-size: 12px; gap: 3px; margin-top: auto; padding-top: 8px; }
      .shop-product-rating-line { align-items: center; display: flex; gap: 12px; }
      .shop-product-rating { align-items: center; color: #edae22; display: inline-flex; gap: 4px; white-space: nowrap; }
      .shop-product-rating-count { color: #727b89; }
      .shop-product-rating .shop-rating-empty { color: #d8dde3; }
      .shop-product-list .trending-items { display: flex; }
      .shop-product-list > .trending-items { margin-bottom: 30px; }
      @media (min-width: 768px) and (max-width: 1199.98px) {
        .shop-product-list > .trending-items { width: 33.333333%; }
      }
      @media (min-width: 1200px) {
        .shop-product-list > .trending-items { width: 20%; }
      }
      @media (max-width: 767.98px) {
        .shop-page-heading { padding: 96px 0 18px; }
        .shop-page-heading h3 { font-size: 22px; }
        .shop-page-section { margin-top: 24px; }
        .shop-toolbar { align-items: flex-start; flex-direction: column; gap: 10px; margin-bottom: 18px; }

        .shop-product-list > .trending-items { width: 50%; }
      }
    </style>
<!--

TemplateMo 589 lugx gaming

https://templatemo.com/tm-589-lugx-gaming

-->
  </head>

<body>

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

  <div class="page-heading header-text shop-page-heading">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <h3>{{ __('store.our_shop_title') }}</h3>
          <span class="breadcrumb">
            <a href="{{ route('home') }}">{{ __('store.home') }}</a>
            &gt; <a href="{{ route('shop') }}">{{ __('store.shop') }}</a>
            @if ($selectedGameCategory)
              &gt; <span aria-current="page">{{ $selectedGameCategory->name }}</span>
            @endif
          </span>
        </div>
      </div>
    </div>
  </div>

  <div class="section trending shop-page-section">
    <div class="container">
      <div class="shop-toolbar">
        <div class="shop-currency-toggle" data-shop-currency-toggle role="group" aria-label="{{ __('store.price_currency') }}">
          <span>{{ __('store.currency') }}</span>
          <div class="btn-group btn-group-sm" role="group">
            <button class="btn" type="button" data-shop-currency="idr" aria-pressed="{{ $selectedCurrency === 'idr' ? 'true' : 'false' }}">IDR</button>
            <button class="btn" type="button" data-shop-currency="myr" aria-pressed="{{ $selectedCurrency === 'myr' ? 'true' : 'false' }}">RM</button>
            <button class="btn" type="button" data-shop-currency="usd" aria-pressed="{{ $selectedCurrency === 'usd' ? 'true' : 'false' }}">USD</button>
          </div>
        </div>
        <div class="shop-category-filters">
          <div class="shop-category-filter-row">
            <span class="shop-category-filter-label">{{ __('store.item') }}</span>
            <ul class="trending-filter" data-shop-filter-group="item" aria-label="{{ __('store.filter_item_category') }}">
              <li><a @class(['is_active' => $selectedItemCategory === null]) href="#!" data-filter="*">{{ __('store.all_items') }}</a></li>
              @foreach ($itemCategories as $itemCategory)
                <li><a @class(['is_active' => $selectedItemCategory?->is($itemCategory)]) href="#!" data-filter=".item-{{ $itemCategory->id }}">{{ $itemCategory->name }}</a></li>
              @endforeach
            </ul>
          </div>
        </div>
      </div>
      <div class="row trending-box shop-product-list">
        @forelse ($products as $product)
          @php
            $minimumPrice = $product->variants->min(
              fn ($variant) => $product->priceInIdr((float) $variant->price, $currencyRates),
            );
            $minimumPriceBeforeDiscount = $product->variants->min(
              fn ($variant) => $product->priceBeforeDiscountInIdr((float) $variant->price, $currencyRates),
            );
            $minimumUsdPrice = $product->variants->min(
              fn ($variant) => $product->priceInUsd((float) $variant->price, $currencyRates),
            );
            $minimumUsdPriceBeforeDiscount = $product->variants->min(
              fn ($variant) => $product->priceBeforeDiscountInUsd((float) $variant->price, $currencyRates),
            );
          @endphp
          @php($fallbackImage = asset('images/trending-0'.(($loop->index % 4) + 1).'.jpg'))
          @php($stockCount = $product->variants->sum('stock'))
          <div class="col-6 col-md-6 col-lg-3 trending-items game-{{ $product->game_category_id }} item-{{ $product->item_category_id }}">
            <div class="item shop-product-card">
              <div class="thumb">
                <a href="{{ route('product-details.show', ['product' => $product, 'currency' => $selectedCurrency === 'idr' ? null : $selectedCurrency]) }}" data-shop-product-link>
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
                <span class="category">{{ $product->gameCategory->name }}</span>
                <h4>{{ $product->name }}</h4>
                <div class="shop-product-price">
                  @if ($product->hasDiscount())
                    <span
                      class="shop-product-price-before"
                      data-shop-price
                      data-idr="Rp {{ rtrim(rtrim(number_format($minimumPriceBeforeDiscount, 2, '.', ','), '0'), '.') }}"
                      data-myr="RM {{ number_format($minimumPriceBeforeDiscount / $currencyRates->idr_per_myr, 2) }}"
                      data-usd="${{ number_format($minimumUsdPriceBeforeDiscount, 2) }}"
                    >{{ match ($selectedCurrency) {
                        'myr' => 'RM '.number_format($minimumPriceBeforeDiscount / $currencyRates->idr_per_myr, 2),
                        'usd' => '$'.number_format($minimumUsdPriceBeforeDiscount, 2),
                        default => 'Rp '.rtrim(rtrim(number_format($minimumPriceBeforeDiscount, 2, '.', ','), '0'), '.'),
                    } }}</span>
                  @endif
                  <span
                    class="shop-product-price-current"
                    data-shop-price
                    data-idr="Rp {{ rtrim(rtrim(number_format($minimumPrice, 2, '.', ','), '0'), '.') }}"
                    data-myr="RM {{ number_format($minimumPrice / $currencyRates->idr_per_myr, 2) }}"
                    data-usd="${{ number_format($minimumUsdPrice, 2) }}"
                  >{{ match ($selectedCurrency) {
                      'myr' => 'RM '.number_format($minimumPrice / $currencyRates->idr_per_myr, 2),
                      'usd' => '$'.number_format($minimumUsdPrice, 2),
                      default => 'Rp '.rtrim(rtrim(number_format($minimumPrice, 2, '.', ','), '0'), '.'),
                  } }}</span>
                </div>
                <div class="shop-product-stats">
                  <span>{{ __('store.stock_count', ['count' => number_format($stockCount)]) }}</span>
                  <div class="shop-product-rating-line">
                    <span class="shop-product-rating" aria-label="{{ __('store.rating_out_of_five', ['rating' => number_format((float) ($product->reviews_avg_rating ?? 0), 1)]) }}">
                      @for ($star = 1; $star <= 5; $star++)
                        <span class="{{ $star <= round((float) ($product->reviews_avg_rating ?? 0)) ? '' : 'shop-rating-empty' }}">★</span>
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
          <div class="col-12 text-center py-5">
            <h4>{{ __('store.no_products') }}</h4>
          </div>
        @endforelse
      </div>
      @if ($products->hasPages())
        <div class="row">
          <div class="col-lg-12">{{ $products->links('pagination::bootstrap-5') }}</div>
        </div>
      @endif
    </div>
  </div>

  <footer>
    <div class="container">
      <div class="col-lg-12">
        <p>Copyright © {{ date('Y') }} Meoxstore. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <!-- Bootstrap core JavaScript -->
  <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>

  </body>
</html>