<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $product->name }} | Meoxstore</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
    .product-detail-panel,
    .single-product .product-purchase-card { background: #fff; border: 1px solid #e6eaf2; border-radius: 24px; box-shadow: 0 16px 40px rgba(30, 56, 90, .08); }
    .product-detail-panel { padding: 28px; }
    .single-product .product-purchase-card { display: block; padding: clamp(26px, 3vw, 38px); width: 100%; }
    .product-purchase-card h2 { color: #182230; font-size: clamp(24px, 2.2vw, 30px); font-weight: 700; letter-spacing: -.6px; margin-bottom: 8px; }
    .product-purchase-product-name { color: #7a8493; font-size: 15px; margin-bottom: 24px; overflow-wrap: anywhere; }
    .product-detail-image { align-items: center; background: #f7f8fb; border-radius: 20px; display: flex; justify-content: center; min-height: 360px; overflow: hidden; }
    .product-detail-image img { height: 100%; max-height: 460px; object-fit: contain; width: 100%; }
    .product-detail-gallery { display: grid; gap: 10px; grid-template-columns: repeat(auto-fill, minmax(64px, 1fr)); margin-top: 12px; }
    .product-detail-gallery button { background: #fff; border: 2px solid transparent; border-radius: 10px; cursor: pointer; overflow: hidden; padding: 0; }
    .product-detail-gallery button[aria-pressed="true"] { border-color: #0071f8; }
    .product-detail-gallery img { aspect-ratio: 1; display: block; object-fit: cover; width: 100%; }
    .product-detail-category { color: #7a7a7a; font-size: 14px; }
    .product-detail-title { font-size: clamp(26px, 4vw, 38px); line-height: 1.25; margin: 12px 0; overflow-wrap: anywhere; }
    .product-detail-price { color: #0071f8; font-size: 30px; font-weight: 700; }
    .product-detail-controls { display: grid; gap: 16px; }
    .product-purchase-card .product-detail-controls { margin-bottom: 26px; }
    .product-purchase-card .purchase-account-fields { display: grid; gap: 18px; }
    .product-purchase-card .purchase-account-field label { color: #303b4a; font-size: 14px; margin: 0 0 8px; }
    .product-purchase-card .purchase-account-field input,
    .product-purchase-card .product-detail-buy input { background: #fff; border: 1px solid #dce3ee; border-radius: 14px; color: #182230; font-size: 15px; min-height: 54px; padding: 12px 16px; text-align: left; transition: border-color .18s ease, box-shadow .18s ease; width: 100%; }
    .product-purchase-card .purchase-account-field input:focus,
    .product-purchase-card .product-detail-buy input:focus { border-color: #779df7; box-shadow: 0 0 0 4px rgba(89, 132, 245, .14); outline: 0; }
    .product-detail-controls label { color: #1e1e1e; font-size: 14px; font-weight: 600; }
    .product-detail-variants { display: grid; gap: 10px; }
    .product-detail-variant { align-items: center; background: #fff; border: 1px solid #e3e8f1; border-radius: 16px; color: #1e1e1e; cursor: pointer; display: flex; justify-content: space-between; min-height: 62px; padding: 16px 18px; text-align: left; transition: border-color .2s, background-color .2s, box-shadow .2s, transform .2s; width: 100%; }
    .single-product .product-detail-variant { background: #fff; }
    .single-product .product-detail-variant,
    .single-product .product-detail-variant .product-detail-variant-name,
    .single-product .product-detail-variant .product-detail-variant-stock { color: #1e1e1e; }
    .product-detail-variant:hover:not(:disabled) { border-color: #a7bdf7; transform: translateY(-1px); }
    .single-product .product-detail-controls .product-detail-variant[aria-pressed="true"] { background: #0071f8; border-color: #0071f8; box-shadow: 0 0 0 3px rgba(0, 113, 248, .16); }
    .product-detail-variant:disabled { color: #999; cursor: not-allowed; }
    .product-detail-variant-name { font-size: 14px; font-weight: 600; }
    .product-detail-variant-stock { color: #7a7a7a; font-size: 12px; margin-left: 12px; white-space: nowrap; }
    .product-detail-variant-bobux { color: #7a7a7a; display: block; font-size: 12px; }
    .product-detail-stock { color: #18864b; font-size: 14px; font-weight: 600; }
    .product-detail-stock.is-unavailable { color: #c0392b; }
    .product-detail-summary { align-items: center; display: flex; flex-wrap: wrap; gap: 12px 18px; margin-top: 12px; }
    .product-detail-stars, .review-stars { color: #f3a712; letter-spacing: 2px; }
    .product-detail-stars .is-empty, .review-stars .is-empty { color: #d8dde3; }
    .product-detail-sold { color: #7a7a7a; font-size: 13px; }
    .product-detail-description { color: #6a6a6a; line-height: 1.9; overflow-wrap: anywhere; white-space: pre-line; }
    .product-detail-meta { border-top: 1px solid #eee; display: grid; gap: 12px; margin-top: 26px; padding-top: 20px; }
    .product-detail-meta span { color: #7a7a7a; display: inline-block; min-width: 110px; }
    .product-detail-meta strong { color: #1e1e1e; font-weight: 600; }
    .product-detail-currency { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; }
    .product-detail-currency .btn { border-color: #ee626b; color: #ee626b; }
    .product-detail-currency .btn[aria-pressed="true"] { background: #ee626b; color: #fff; }
    .product-detail-description-card { background: #f7f8fb; border-radius: 20px; margin-top: 28px; padding: 28px; }
    .product-detail-description-card h5 { margin-bottom: 14px; }
    .product-detail-buy { align-items: end; display: grid; gap: 12px; grid-template-columns: minmax(0, 1fr) 56px minmax(0, 1fr); margin-top: 26px; }
    .product-detail-buy > div { min-width: 0; }
    .product-detail-buy label { color: #1e1e1e; display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
    .single-product .product-detail-buy button { align-items: center; background: #ee626b; border: 0; border-radius: 16px; box-sizing: border-box; color: #fff; cursor: pointer; display: inline-flex; height: 56px; justify-content: center; line-height: 1; font-weight: 700; padding: 10px; text-align: center; transition: background-color .2s, box-shadow .2s, transform .2s; vertical-align: middle; }
    .single-product .product-detail-buy .product-detail-add-cart { font-size: 20px; padding: 0; width: 56px; }
    .single-product .product-detail-buy .product-detail-add-cart i { display: block; line-height: 1; margin: 0; }
    .single-product .product-detail-buy .product-detail-buy-now { box-shadow: 0 8px 18px rgba(238, 98, 107, .2); font-size: 16px; min-width: 0; padding: 10px 16px; width: 100%; }
    .product-detail-buy button:hover { background: #0071f8; }
    .product-detail-buy button:disabled { cursor: not-allowed; opacity: .55; }
    .product-recommendations, .product-reviews { margin-top: 56px; }
    .review-rating { display: flex; flex-direction: row-reverse; gap: 5px; justify-content: flex-end; margin: 4px 0 14px; }
    .review-rating input { height: 1px; opacity: 0; position: absolute; width: 1px; }
    .review-rating label { color: #cbd3de; cursor: pointer; font-size: 30px; line-height: 1; transition: color .15s ease, transform .15s ease; }
    .review-rating label:hover, .review-rating label:hover ~ label,
    .review-rating input:checked ~ label { color: #f3a712; }
    .review-rating label:hover { transform: scale(1.12); }
    .review-rating input:focus-visible + label { outline: 3px solid #7aa7e8; outline-offset: 4px; }
    .product-recommendation-card,
    .category-recommendation-card { background: #fff; border: 1px solid #e6eaf2; box-shadow: 0 12px 30px rgba(30, 56, 90, .07); }
    .product-recommendation-card,
    .category-recommendation-card { position: relative; transition: transform .2s ease, box-shadow .2s ease; }
    .product-recommendation-card { border-radius: 18px; height: 100%; overflow: hidden; }
    @media (hover: hover) and (prefers-reduced-motion: no-preference) {
      .product-recommendation-card:hover,
      .category-recommendation-card:hover { box-shadow: 0 20px 42px rgba(30, 56, 90, .16); transform: scale(1.035); z-index: 2; }
    }
    .product-recommendation-card > a { color: inherit; display: block; text-decoration: none; }
    .product-recommendation-card img { background: #f7f8fb; height: 180px; object-fit: cover; width: 100%; }
    .product-recommendation-copy { padding: 16px; }
    .product-recommendation-copy span { color: #7a7a7a; font-size: 12px; }
    .product-recommendation-copy h3 { font-size: 16px; margin: 6px 0; overflow-wrap: anywhere; }
    .product-recommendation-price { color: #0071f8; font-weight: 700; }
    .category-recommendation-card { border-radius: 18px; height: 100%; overflow: hidden; }
    .category-recommendation-card img { background: #f7f8fb; height: 180px; object-fit: cover; width: 100%; }
    .category-recommendation-card h3 { font-size: 17px; padding: 16px; }
    .review-summary { align-items: center; display: flex; flex-wrap: wrap; gap: 8px 14px; }
    .review-card { background: #fff; border: 1px solid #eee; border-radius: 14px; margin-top: 14px; padding: 18px; }
    .review-form { background: #f7f8fb; border-radius: 18px; margin-top: 24px; padding: 24px; }
    .review-form .form-control, .review-form .form-select { border-color: #e7e7e7; border-radius: 10px; }
    .review-form button { background: #ee626b; border: 0; border-radius: 22px; color: #fff; font-weight: 600; padding: 10px 18px; }
    .review-form button:hover { background: #0071f8; }
    .product-detail-page .header-area {
      background-color: #0071f8 !important;
      border-radius: 0 0 25px 25px;
      box-shadow: 0 0 10px rgba(0, 0, 0, .15) !important;
      height: 80px !important;
      position: fixed !important;
      top: 0 !important;
      transition: transform .25s ease, background-color .5s ease, box-shadow .5s ease;
    }
    .product-detail-page .header-area.header-hidden { transform: translateY(-100%); }
    .product-detail-page .header-area .main-nav .logo { margin-top: 18px; }
    .product-detail-page .header-area .main-nav .logo img { max-width: 110px; }
    .product-detail-breadcrumb { color: #667085; font-size: 16px; margin-bottom: 26px; }
    .product-detail-breadcrumb ol { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; list-style: none; margin: 0; padding: 0; }
    .product-detail-breadcrumb li { color: #182230; font-size: inherit; margin: 0; }
    .product-detail-breadcrumb li + li::before { color: #667085; content: "›"; margin-right: 8px; }
    .product-detail-breadcrumb a { color: #667085; text-decoration: none; }
    .product-detail-breadcrumb a:hover { color: #0071f8; }
    @media (min-width: 1200px) {
      .product-recommendation-items > .product-recommendation-item { flex: 0 0 20%; max-width: 20%; }
    }
    @media (max-width: 767.98px) {
      .product-detail-panel { padding: 18px; }
      .product-detail-image { min-height: 250px; }
      .product-detail-description-card { padding: 20px; }
    }
  </style>
</head>
<body class="product-detail-page storefront-page">
  <div id="js-preloader" class="js-preloader">
    <div class="preloader-inner">
      <span class="dot"></span>
      <div class="dots"><span></span><span></span><span></span></div>
    </div>
  </div>

  <x-storefront-header />

  @php
    $productImages = array_map(
        fn ($image) => \Illuminate\Support\Facades\Storage::disk('public')->url($image),
        $product->galleryImages(),
    );
    $productImage = $productImages[0] ?? asset('images/trending-01.jpg');
    $selectedIdrPrice = 'Rp '.rtrim(rtrim(number_format($product->priceInIdr((float) $selectedVariant->price, $currencyRates), 2, '.', ','), '0'), '.');
    $robuxTransactionsDisabled = $product->usesRobuxPricing()
        && ! $currencyRates->bobuxTransactionsEnabled($product->bobuxType());
  @endphp

  <main class="single-product section">
    <div class="container">
      <nav class="product-detail-breadcrumb" aria-label="Breadcrumb">
        <ol>
          <li><a href="{{ route('home') }}">{{ __('store.home') }}</a></li>
          <li><a href="{{ route('shop', ['currency' => $selectedCurrency === 'idr' ? null : $selectedCurrency]) }}">{{ __('store.shop') }}</a></li>
          <li><a href="{{ route('shop', ['game' => $product->game_category_id, 'currency' => $selectedCurrency === 'idr' ? null : $selectedCurrency]) }}">{{ $product->gameCategory->name }}</a></li>
          <li aria-current="page">{{ $product->name }}</li>
        </ol>
      </nav>
      <div class="row align-items-start g-4">
        <div class="col-lg-7">
          <div class="product-detail-panel">
            <div class="row align-items-center g-4">
                <div class="col-md-5">
                  <div class="product-detail-image">
                    <img src="{{ $productImage }}" alt="{{ $product->name }}" data-product-gallery-main>
                  </div>
                  @if (count($productImages) > 1)
                    <div class="product-detail-gallery" aria-label="Product images">
                      @foreach ($productImages as $image)
                        <button type="button" aria-label="Show product image {{ $loop->iteration }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" data-product-gallery-thumbnail data-image-url="{{ $image }}">
                          <img src="{{ $image }}" alt="">
                        </button>
                      @endforeach
                    </div>
                  @endif
                </div>
                <div class="col-md-7">
                  <span class="product-detail-category">{{ $product->gameCategory->name }}</span>
                  <h1 class="product-detail-title">{{ $product->name }}</h1>

                  <div class="product-detail-currency" data-product-currency-toggle role="group" aria-label="Pilih mata uang">
                    <span class="product-detail-category">Mata uang:</span>
                    <button class="btn btn-sm" type="button" data-product-currency="idr" aria-pressed="{{ $selectedCurrency === 'idr' ? 'true' : 'false' }}">IDR</button>
                    <button class="btn btn-sm" type="button" data-product-currency="myr" aria-pressed="{{ $selectedCurrency === 'myr' ? 'true' : 'false' }}">RM</button>
                    <button class="btn btn-sm" type="button" data-product-currency="usd" aria-pressed="{{ $selectedCurrency === 'usd' ? 'true' : 'false' }}">USD</button>
                  </div>
                  <div class="product-detail-price mt-2" data-product-price aria-live="polite">{{ match ($selectedCurrency) {
                      'myr' => 'RM '.number_format($product->priceInIdr((float) $selectedVariant->price, $currencyRates) / (float) $currencyRates->idr_per_myr, 2),
                      'usd' => '$'.number_format($product->priceInUsd((float) $selectedVariant->price, $currencyRates), 2),
                      default => $selectedIdrPrice,
                  } }}</div>
                  <div class="product-detail-summary">
                    <div class="review-summary">
                      <span class="product-detail-stars" aria-label="Rating {{ number_format($averageRating, 1) }} dari 5">
                        @for ($star = 1; $star <= 5; $star++)
                          <span class="{{ $star <= round($averageRating) ? '' : 'is-empty' }}">★</span>
                        @endfor
                      </span>
                      <span class="product-detail-sold">{{ number_format($averageRating, 1) }} ({{ __('store.review_count', ['count' => $reviewCount]) }})</span>
                    </div>
                    <span class="product-detail-sold">{{ __('store.sold_count', ['count' => number_format($soldCount)]) }}</span>
                  </div>

                  <div class="product-detail-meta">
                    <div><span>{{ __('store.game') }}</span><strong>{{ $product->gameCategory->name }}</strong></div>
                  </div>
                </div>
                <section class="product-detail-description-card" aria-labelledby="product-description-heading">
                  <h5 id="product-description-heading">{{ __('store.description') }}</h5>
                  <p class="product-detail-description mb-0">{{ $product->description ?: __('store.no_description') }}</p>
                </section>
            </div>
          </div>
        </div>
        <div class="col-lg-5">
            <form class="purchase-details-panel product-purchase-card"
                  method="POST"
                  action="{{ route('cart.items.store') }}"
                  data-cart-add
                  data-added-label="{{ __('store.added_to_cart') }}"
                  data-adding-label="{{ __('store.adding_to_cart') }}"
                  data-request-error-label="{{ __('store.cart_add_failed') }}">
              @csrf
              <h2>{{ __('store.purchase_details_title') }}</h2>
              <p class="product-purchase-product-name">{{ $product->name }}</p>

              <input type="hidden" name="variant_id" value="{{ $selectedVariant->id }}" data-order-variant-id>
              <input type="hidden" name="currency" value="{{ $selectedCurrency }}" data-order-currency>
              <div class="product-detail-controls">
                <div class="product-detail-variants" role="group" aria-label="{{ __('store.choose_variant') }}">
                  <label class="form-label mb-0">{{ __('store.choose_variant') }}</label>
                  @foreach ($product->variants as $variant)
                    @php
                      $idrPriceInIdr = $product->priceInIdr((float) $variant->price, $currencyRates);
                      $idrPrice = 'Rp '.rtrim(rtrim(number_format($idrPriceInIdr, 2, '.', ','), '0'), '.');
                      $myrPrice = 'RM '.number_format($idrPriceInIdr / (float) $currencyRates->idr_per_myr, 2);
                      $usdPrice = '$'.number_format($product->priceInUsd((float) $variant->price, $currencyRates), 2);
                    @endphp
                    <button
                      class="product-detail-variant"
                      type="button"
                      data-product-variant
                      value="{{ $variant->id }}"
                      data-idr="{{ $idrPrice }}"
                      data-myr="{{ $myrPrice }}"
                      data-usd="{{ $usdPrice }}"
                      data-stock="{{ $variant->stock }}"
                      data-minimum="{{ $variant->minimum_quantity }}"
                      data-maximum="{{ min($variant->maximum_quantity, $variant->stock) }}"
                      aria-pressed="{{ $variant->id === $selectedVariant->id ? 'true' : 'false' }}"
                      @disabled($variant->stock < 1)
                    >
                      <span class="product-detail-variant-name">{{ $variant->name }}</span>
                      <span class="product-detail-variant-stock">{{ $variant->stock > 0 ? __('store.stock_count', ['count' => number_format($variant->stock)]) : __('store.stock_empty') }}</span>
                      @if ($product->bobuxType() === 'login')
                        <span class="product-detail-variant-bobux">{{ __('store.bobux_amount', ['count' => number_format((float) $variant->bobux_amount)]) }}</span>
                      @endif
                    </button>
                  @endforeach
                </div>
                <div
                  class="product-detail-stock {{ $selectedVariant->stock < 1 ? 'is-unavailable' : '' }}"
                  data-product-stock
                  data-available-label="{{ __('store.stock_count', ['count' => '']) }}"
                  data-unavailable-label="{{ __('store.stock_empty') }}"
                  aria-live="polite"
                >
                  {{ $selectedVariant->stock > 0 ? __('store.stock_count', ['count' => number_format($selectedVariant->stock)]) : __('store.stock_empty') }}
                </div>
              </div>

              <div class="purchase-account-fields" data-purchase-fields>
                @foreach ($product->purchaseFields() as $field)
                  <div class="purchase-account-field">
                    <label for="purchase-field-{{ $field['key'] }}">{{ $field['label'] }}</label>
                    <input
                      id="purchase-field-{{ $field['key'] }}"
                      name="purchase_details[{{ $field['key'] }}]"
                      type="{{ $field['type'] }}"
                      maxlength="255"
                      autocomplete="{{ $field['type'] === 'password' ? 'new-password' : 'off' }}"
                      required
                    >
                  </div>
                @endforeach
              </div>

              <div class="purchase-seller-note">
                <label for="seller-note">{{ __('store.seller_note') }}</label>
                <textarea
                  class="form-control"
                  id="seller-note"
                  name="seller_note"
                  rows="3"
                  maxlength="1000"
                  placeholder="{{ __('store.seller_note_placeholder') }}"
                  aria-describedby="seller-note-help"
                ></textarea>
                <small class="form-text" id="seller-note-help">{{ __('store.seller_note_help') }}</small>
              </div>

              @if ($robuxTransactionsDisabled)
                <p class="text-danger small mb-2" role="status">{{ __('store.robux_transactions_disabled') }}</p>
              @endif
              <div class="product-detail-buy">
                <div>
                  <label for="order-quantity">{{ __('store.quantity') }}</label>
                  <input id="order-quantity" name="quantity" type="number" min="{{ $selectedVariant->minimum_quantity }}" max="{{ min($selectedVariant->maximum_quantity, $selectedVariant->stock) }}" value="{{ $selectedVariant->minimum_quantity }}" required data-order-quantity data-stock="{{ $selectedVariant->stock }}" data-stock-error-label="{{ __('store.stock_not_enough') }}">
                </div>
                <button class="product-detail-add-cart" type="submit" aria-label="{{ __('store.add_product_to_cart', ['product' => $product->name]) }}" title="{{ __('store.add_to_cart') }}" data-order-submit @disabled($selectedVariant->stock < 1 || $robuxTransactionsDisabled)>
                  <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                  <span class="visually-hidden">{{ __('store.add_to_cart') }}</span>
                </button>
                <button class="product-detail-buy-now" type="submit" formaction="{{ route('checkout.direct') }}" data-checkout-submit data-order-submit @disabled($selectedVariant->stock < 1 || $robuxTransactionsDisabled)>{{ __('store.buy_now') }}</button>
              </div>
            </form>
        </div>
      </div>

      @if ($recommendedProducts->isNotEmpty())
        <section class="product-recommendations" aria-labelledby="item-recommendations-heading">
          <div class="section-heading">
            <h6>{{ __('store.similar_products') }}</h6>
            <h2 id="item-recommendations-heading">{{ __('store.more_category_products', ['category' => str_contains(strtolower($product->itemCategory->name), 'fruit') ? 'Fruit' : $product->itemCategory->name]) }}</h2>
          </div>
          <img class="mb-4 rounded-4" src="{{ $itemCategoryImageUrl ?: $productImage }}" alt="{{ $product->itemCategory->name }}" style="height: 130px; object-fit: cover; width: 100%;">
          <div class="row g-3 product-recommendation-items">
            @foreach ($recommendedProducts as $recommendedProduct)
              @php
                $recommendedPrice = $recommendedProduct->variants->min(fn ($variant) => $recommendedProduct->priceInIdr((float) $variant->price, $currencyRates));
                $recommendedImage = $recommendedProduct->image
                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($recommendedProduct->image)
                    : asset('images/trending-0'.(($loop->index % 4) + 1).'.jpg');
              @endphp
              <div class="col-6 col-md-4 col-lg-3 product-recommendation-item">
                <article class="product-recommendation-card">
                  <a href="{{ route('product-details.show', ['product' => $recommendedProduct, 'currency' => $selectedCurrency === 'idr' ? null : $selectedCurrency]) }}">
                    <img src="{{ $recommendedImage }}" alt="{{ $recommendedProduct->name }}">
                    <div class="product-recommendation-copy">
                      <span>{{ $recommendedProduct->gameCategory->name }}</span>
                      <h3>{{ $recommendedProduct->name }}</h3>
                      <div class="product-recommendation-price">{{ __('store.starts_at') }} Rp {{ number_format((float) $recommendedPrice, 0, ',', '.') }}</div>
                    </div>
                  </a>
                </article>
              </div>
            @endforeach
          </div>
        </section>
      @endif

      @if ($recommendedGames->isNotEmpty())
        <section class="product-recommendations" aria-labelledby="game-recommendations-heading">
          <div class="section-heading">
            <h6>{{ __('store.explore_other_games') }}</h6>
            <h2 id="game-recommendations-heading">{{ __('store.recommended_games') }}</h2>
          </div>
          <div class="row g-3">
            @foreach ($recommendedGames as $recommendedGame)
              @php
                $gameImage = $recommendedGame->image
                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($recommendedGame->image)
                    : ($recommendedGame->products->first()?->image
                        ? \Illuminate\Support\Facades\Storage::disk('public')->url($recommendedGame->products->first()->image)
                        : asset('images/categories-0'.(($loop->index % 5) + 1).'.jpg'));
              @endphp
              <div class="col-6 col-lg-3">
                <a class="category-recommendation-card d-block text-decoration-none" href="{{ route('shop', ['game' => $recommendedGame->id, 'currency' => $selectedCurrency === 'idr' ? null : $selectedCurrency]) }}">
                  <img src="{{ $gameImage }}" alt="{{ $recommendedGame->name }}">
                  <h3>{{ $recommendedGame->name }}</h3>
                </a>
              </div>
            @endforeach
          </div>
        </section>
      @endif

      @if ($recommendedItemCategories->isNotEmpty())
        <section class="product-recommendations" aria-labelledby="item-category-recommendations-heading">
          <div class="section-heading">
            <h6>{{ __('store.explore_other_items') }}</h6>
            <h2 id="item-category-recommendations-heading">{{ __('store.recommended_item_categories') }}</h2>
          </div>
          <div class="row g-3">
            @foreach ($recommendedItemCategories as $recommendedCategory)
              @php
                $categoryImage = $recommendedCategory->image
                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($recommendedCategory->image)
                    : ($recommendedCategory->products->first()?->image
                        ? \Illuminate\Support\Facades\Storage::disk('public')->url($recommendedCategory->products->first()->image)
                        : asset('images/categories-0'.(($loop->index % 5) + 1).'.jpg'));
              @endphp
              <div class="col-6 col-lg-3">
                <a class="category-recommendation-card d-block text-decoration-none" href="{{ route('shop', ['currency' => $selectedCurrency === 'idr' ? null : $selectedCurrency, 'item_category' => $recommendedCategory->id]) }}">
                  <img src="{{ $categoryImage }}" alt="{{ $recommendedCategory->name }}">
                  <h3>{{ $recommendedCategory->name }}</h3>
                </a>
              </div>
            @endforeach
          </div>
        </section>
      @endif

      <section class="product-reviews" id="reviews" aria-labelledby="product-reviews-heading">
        <div class="section-heading">
          <h6>{{ __('store.customer_experience') }}</h6>
          <h2 id="product-reviews-heading">{{ __('store.product_reviews') }}</h2>
        </div>
        <div class="review-summary mb-3">
          <span class="product-detail-stars" aria-label="Rating rata-rata {{ number_format($averageRating, 1) }} dari 5">
            @for ($star = 1; $star <= 5; $star++)
              <span class="{{ $star <= round($averageRating) ? '' : 'is-empty' }}">★</span>
            @endfor
          </span>
          <strong>{{ number_format($averageRating, 1) }} / 5</strong>
          <span class="text-secondary">{{ __('store.review_count', ['count' => $reviewCount]) }}</span>
        </div>
        @if (session('success'))
          <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if ($product->reviews->isEmpty())
          <p class="text-secondary">{{ __('store.no_reviews') }}</p>
        @else
          @foreach ($product->reviews as $review)
            <article class="review-card">
              <div class="d-flex flex-wrap justify-content-between gap-2">
                <strong>{{ $review->user?->name ?? __('store.verified_buyer') }}</strong>
                <span class="review-stars" aria-label="Rating {{ $review->rating }} dari 5">
                  @for ($star = 1; $star <= 5; $star++)
                    <span class="{{ $star <= $review->rating ? '' : 'is-empty' }}">★</span>
                  @endfor
                </span>
              </div>
              @if (filled($review->comment))
                <p class="text-secondary mt-2 mb-0">{{ $review->comment }}</p>
              @endif
              @if ($review->admin_reply)
                <div class="border-start border-3 ps-3 mt-3">
                  <strong>{{ __('store.store_reply') }}</strong>
                  <p class="text-secondary mt-1 mb-0">{{ $review->admin_reply }}</p>
                  @if ($review->admin_reply_image)
                    <a class="d-inline-block mt-2" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($review->admin_reply_image) }}" target="_blank" rel="noopener noreferrer">
                      <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($review->admin_reply_image) }}" alt="{{ __('store.review_reply_image_alt') }}" style="max-height: 220px; max-width: 100%; object-fit: contain;">
                    </a>
                  @endif
                </div>
              @elseif ($review->admin_reply_image)
                <a class="d-inline-block mt-2" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($review->admin_reply_image) }}" target="_blank" rel="noopener noreferrer">
                  <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($review->admin_reply_image) }}" alt="{{ __('store.review_reply_image_alt') }}" style="max-height: 220px; max-width: 100%; object-fit: contain;">
                </a>
              @endif
            </article>
          @endforeach
        @endif

        @if ($canReviewOrder)
          <form class="review-form" method="POST" action="{{ route('products.reviews.store', $product) }}">
            @csrf
            <h3 class="h5 mb-3">{{ __('store.write_review') }}</h3>
            <div class="mb-3">
              <fieldset class="border-0 m-0 p-0">
                <legend class="form-label">{{ __('store.give_stars') }}</legend>
                <div class="review-rating" role="radiogroup" aria-label="{{ __('store.give_stars') }}">
                  @foreach (range(5, 1) as $rating)
                    <input id="review-rating-{{ $rating }}" type="radio" name="rating" value="{{ $rating }}" aria-label="{{ __('store.stars', ['count' => $rating]) }}" required @checked(old('rating') == $rating)>
                    <label for="review-rating-{{ $rating }}" title="{{ __('store.stars', ['count' => $rating]) }}" aria-hidden="true">★</label>
                  @endforeach
                </div>
                @error('rating')<div class="text-danger small">{{ $message }}</div>@enderror
              </fieldset>
            </div>
            <div class="mb-3">
              <label class="form-label" for="review-comment">{{ __('store.review') }}</label>
              <textarea class="form-control @error('comment') is-invalid @enderror" id="review-comment" name="comment" rows="4" maxlength="2000">{{ old('comment') }}</textarea>
              @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit">{{ __('store.send_review') }}</button>
          </form>
        @elseif (auth()->check() && ! $hasReviewed)
          <p class="text-secondary mt-4">{{ __('store.review_after_completion') }}</p>
        @elseif (auth()->guest())
          <p class="text-secondary mt-4">{{ __('store.guest_review_tracking') }} <a href="{{ route('orders.lookup') }}">{{ __('store.track_order') }}</a>.</p>
        @endif
      </section>
    </div>
  </main>

  <footer>
    <div class="container">
      <div class="col-lg-12">
        <p>Copyright &copy; {{ date('Y') }} Meoxstore. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
</body>
</html>
