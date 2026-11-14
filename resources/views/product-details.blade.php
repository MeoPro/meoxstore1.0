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
    .product-detail-image { align-items: center; background: #f7f8fb; border-radius: 20px; display: flex; justify-content: center; min-height: 360px; overflow: hidden; }
    .product-detail-image img { height: 100%; max-height: 460px; object-fit: contain; width: 100%; }
    .product-detail-gallery { display: grid; gap: 10px; grid-template-columns: repeat(auto-fill, minmax(64px, 1fr)); margin-top: 12px; }
    .product-detail-gallery-heading { color: #303b4a; font-size: 14px; font-weight: 600; margin: 14px 0 0; }
    .product-detail-gallery button { background: #fff; border: 2px solid transparent; border-radius: 10px; cursor: pointer; overflow: hidden; padding: 0; }
    .product-detail-gallery button[aria-pressed="true"] { border-color: #0071f8; }
    .product-detail-gallery img { aspect-ratio: 1; display: block; object-fit: cover; width: 100%; }
    .product-detail-title { font-size: clamp(26px, 4vw, 38px); line-height: 1.25; margin: 12px 0; overflow-wrap: anywhere; }
    .product-detail-highlights { margin: 0 0 24px; }
    .product-detail-rating { align-items: center; color: #344054; display: flex; flex: 0 0 auto; font-size: 14px; gap: 6px; }
    .product-detail-rating-star { color: #f3a712; font-size: 18px; line-height: 1; }
    .product-detail-review-count { color: #778499; font-size: 13px; }
    .product-detail-account-features { align-items: center; display: flex; flex-wrap: wrap; gap: 10px 18px; list-style: none; margin: 0; padding: 0; }
    .product-detail-highlights .product-detail-account-features { margin: 0; }
    .single-product .product-detail-account-features li { align-items: center; color: #111; display: flex; font-size: 13px; font-weight: 600; gap: 8px; }
    .product-detail-highlights .product-detail-account-features li { margin: 0; }
    .single-product .product-detail-account-features li span { color: #111; display: inline; width: auto; }
    .product-detail-account-features i { color: #0071f8; font-size: 16px; }
    .product-detail-account-features li:nth-child(2) i { color: #ee626b; }
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
    .single-product .product-detail-variant:disabled,
    .single-product .product-detail-variant:disabled:hover {
      background: #f1f3f5;
      border-color: #e4e7ec;
      box-shadow: none;
      color: #98a2b3;
      cursor: not-allowed;
      transform: none;
    }
    .single-product .product-detail-variant:disabled .product-detail-variant-name,
    .single-product .product-detail-variant:disabled .product-detail-variant-stock,
    .single-product .product-detail-variant:disabled .product-detail-variant-bobux { color: #98a2b3; }
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
    .product-detail-description-copy { max-height: 7.6em; overflow: hidden; position: relative; }
    .product-detail-description-copy:not(.is-expanded)::after { background: linear-gradient(transparent, #f7f8fb); bottom: 0; content: ""; height: 2em; left: 0; pointer-events: none; position: absolute; right: 0; }
    .product-detail-description-copy.is-expanded { max-height: none; }
    .product-detail-description-toggle { background: none; border: 0; color: #0071f8; cursor: pointer; font-weight: 600; margin-top: 8px; padding: 0; }
    .product-detail-currency { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; }
    .product-detail-currency .btn { border-color: #ee626b; color: #ee626b; }
    .product-detail-currency .btn[aria-pressed="true"] { background: #ee626b; color: #fff; }
    .product-detail-description-card { background: #f7f8fb; border-radius: 20px; margin-top: 28px; padding: 28px; }
    .product-detail-description-card h5 { margin-bottom: 6px; }
    .product-detail-description-card .product-detail-description { margin-top: 0; }
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
    .product-recommendation-header { align-items: flex-end; display: flex; gap: 16px; justify-content: space-between; }
    .product-recommendation-header .section-heading { flex: 1 1 auto; margin-bottom: 0; min-width: 0; }
    .product-recommendation-controls { display: flex; flex: 0 0 auto; gap: 8px; padding-bottom: 4px; }
    .product-recommendation-controls[hidden] { display: none; }
    .product-recommendation-controls button { align-items: center; background: #fff; border: 1px solid #dfe5ef; border-radius: 50%; color: #1769d2; cursor: pointer; display: inline-flex; font-size: 30px; height: 40px; justify-content: center; line-height: 1; padding: 0 0 4px; width: 40px; }
    .product-recommendation-controls button:hover:not(:disabled) { background: #1769d2; border-color: #1769d2; color: #fff; }
    .product-recommendation-controls button:focus-visible { outline: 3px solid rgba(23, 105, 210, .3); outline-offset: 2px; }
    .product-recommendation-controls button:disabled { color: #aab4c2; cursor: not-allowed; }
    .product-recommendation-track { display: flex; gap: 16px; overflow-x: auto; overscroll-behavior-inline: contain; padding: 4px 3px 14px; scroll-snap-type: x mandatory; scrollbar-color: #9bb9e7 #edf2f8; scrollbar-width: thin; }
    .product-recommendation-track > .product-recommendation-item,
    .product-recommendation-track > .category-recommendation-item { flex: 0 0 calc((100% - 64px) / 5); max-width: calc((100% - 64px) / 5); min-width: 0; scroll-snap-align: start; }
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
    .category-recommendation-card img { aspect-ratio: 1 / 1; background: #f7f8fb; display: block; height: auto; object-fit: cover; width: 100%; }
    .category-recommendation-card h3 { font-size: 17px; padding: 16px; }
    .review-summary { align-items: center; display: flex; flex-wrap: wrap; gap: 8px 14px; }
    .product-reviews { background: #fff; border: 1px solid #e6eaf2; border-radius: 18px; padding: 28px; }
    .product-review-overview { align-items: center; border-bottom: 1px solid #e5e7eb; display: grid; gap: 24px; grid-template-columns: minmax(140px, .7fr) minmax(0, 3fr); padding-bottom: 24px; }
    .product-review-average { align-items: center; display: flex; flex-direction: column; justify-content: center; }
    .product-review-average-rating { align-items: center; display: flex; gap: 10px; }
    .product-review-average-star { color: #f3a712; font-size: 34px; line-height: 1; }
    .product-review-average-rating strong { color: #182230; font-size: 40px; line-height: 1; }
    .product-review-average-count { color: #667085; font-size: 14px; margin-top: 8px; }
    .product-review-distribution { display: grid; gap: 5px; }
    .product-review-distribution-row { align-items: center; color: #4b5563; display: grid; gap: 8px; grid-template-columns: 16px 12px minmax(0, 1fr) 42px; }
    .product-review-distribution-star { color: #f3a712; font-size: 18px; line-height: 1; }
    .product-review-distribution-bar { background: #e5e7eb; border-radius: 99px; height: 10px; overflow: hidden; }
    .product-review-distribution-bar span { background: #ffad00; border-radius: inherit; display: block; height: 100%; }
    .product-review-distribution-count { color: #778499; font-size: 13px; text-align: right; }
    .product-review-latest { margin-top: 20px; }
    .product-review-latest h3 { color: #344054; font-size: 18px; margin: 0 0 14px; }
    .product-review-list { display: flex; gap: 16px; overflow-x: auto; overscroll-behavior-inline: contain; padding: 0 0 8px; scroll-snap-type: x mandatory; }
    .product-review-card { background: #fff; border: 1px solid #dfe3e8; border-radius: 14px; flex: 0 0 min(370px, 85vw); min-height: 150px; padding: 20px; scroll-snap-align: start; }
    .product-review-card-header { align-items: center; display: flex; gap: 10px; justify-content: space-between; }
    .product-review-card-name { color: #111; font-size: 14px; font-weight: 700; }
    .product-review-card-date { color: #8992a1; font-size: 12px; white-space: nowrap; }
    .product-review-card-stars { color: #f3a712; letter-spacing: 2px; margin-top: 8px; }
    .product-review-card-stars .is-empty { color: #d8dde3; }
    .product-review-card-comment { color: #526071; font-size: 14px; line-height: 1.65; margin: 10px 0 0; overflow-wrap: anywhere; }
    .product-review-empty { color: #667085; margin: 0; }
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
    .product-detail-page .header-area.header-has-storefront-rating ~ .single-product.section { margin-top: 160px; }
    @media (min-width: 768px) and (max-width: 1199.98px) {
      .product-recommendation-track > .product-recommendation-item,
      .product-recommendation-track > .category-recommendation-item { flex-basis: calc((100% - 32px) / 3); max-width: calc((100% - 32px) / 3); }
    }
    @media (max-width: 767.98px) {
      .product-recommendation-header { align-items: flex-start; }
      .product-recommendation-track > .product-recommendation-item,
      .product-recommendation-track > .category-recommendation-item { flex-basis: calc((100% - 16px) / 2); max-width: calc((100% - 16px) / 2); }
      .product-reviews { padding: 20px; }
      .product-review-overview { gap: 18px; grid-template-columns: 1fr; }
      .product-review-average { align-items: flex-start; }
      .product-review-average-rating { flex-direction: row; }
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
    $isAccountProduct = $product->is_account_listing
        || in_array(
            $product->itemCategory?->localizedName(),
            [__('item_categories.account'), __('item_categories.akun')],
            true,
        );
    $productImages = array_map(
        fn ($image) => \Illuminate\Support\Facades\Storage::disk('public')->url($image),
        $product->galleryImages(),
    );
    $productImage = $productImages[0] ?? asset('images/trending-01.jpg');
    $productDescription = $product->description ?: __('store.no_description');
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
      @if ($isAccountProduct)
        <div class="product-detail-highlights">
          <ul class="product-detail-account-features" aria-label="{{ __('store.account_features') }}">
            <li><i class="fa fa-shield" aria-hidden="true"></i><span>{{ __('store.account_security') }}</span></li>
            <li><i class="fa fa-user-secret" aria-hidden="true"></i><span>{{ __('store.account_anti_hackback') }}</span></li>
            <li><i class="fa fa-check-circle" aria-hidden="true"></i><span>{{ __('store.account_full_selection') }}</span></li>
          </ul>
        </div>
      @else
        <div class="product-detail-highlights">
          <ul class="product-detail-account-features" aria-label="{{ __('store.product_benefits') }}">
            <li><i class="fa fa-shield" aria-hidden="true"></i><span>{{ __('store.buyer_protection') }}</span></li>
            <li><i class="fa fa-hand-holding-usd" aria-hidden="true"></i><span>{{ __('store.money_back_guarantee') }}</span></li>
            <li><i class="fa fa-headset" aria-hidden="true"></i><span>{{ __('store.customer_service_availability') }}</span></li>
          </ul>
        </div>
      @endif
      <div class="row align-items-start g-4">
        <div class="col-lg-7">
          <div class="product-detail-panel">
            <div class="row align-items-center g-4">
                <div class="col-md-5">
                  <div class="product-detail-image">
                    <img src="{{ $productImage }}" alt="{{ $product->name }}" data-product-gallery-main>
                  </div>
                  @if ($isAccountProduct && count($productImages) > 1)
                    <h2 class="product-detail-gallery-heading">{{ __('store.account_image_examples') }}</h2>
                  @endif
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
                  <h1 class="product-detail-title">{{ $product->name }}</h1>

                  <div class="product-detail-currency" data-product-currency-toggle role="group" aria-label="Pilih mata uang">
                    <span class="text-muted">Mata uang:</span>
                    <button class="btn btn-sm" type="button" data-product-currency="idr" aria-pressed="{{ $selectedCurrency === 'idr' ? 'true' : 'false' }}">IDR</button>
                    <button class="btn btn-sm" type="button" data-product-currency="myr" aria-pressed="{{ $selectedCurrency === 'myr' ? 'true' : 'false' }}">RM</button>
                    <button class="btn btn-sm" type="button" data-product-currency="usd" aria-pressed="{{ $selectedCurrency === 'usd' ? 'true' : 'false' }}">USD</button>
                  </div>
                  <div class="product-detail-price mt-2" data-product-price aria-live="polite">{{ match ($selectedCurrency) {
                      'myr' => 'RM '.number_format($product->priceInMyr((float) $selectedVariant->price, $currencyRates), 2),
                      'usd' => '$'.number_format($product->priceInUsd((float) $selectedVariant->price, $currencyRates), 2),
                      default => $selectedIdrPrice,
                  } }}</div>
                  <div class="product-detail-summary">
                    <div class="product-detail-rating" aria-label="Rating {{ number_format($averageRating, 1) }} dari 5">
                      <span class="product-detail-rating-star" aria-hidden="true">★</span>
                      <span>{{ number_format($averageRating, 1) }}</span>
                      <span class="product-detail-review-count">({{ __('store.review_count', ['count' => $reviewCount]) }})</span>
                    </div>
                    <span class="product-detail-sold">{{ __('store.sold_count', ['count' => number_format($soldCount)]) }}</span>
                  </div>

                </div>
                <section class="product-detail-description-card" aria-labelledby="product-description-heading">
                  <h5 id="product-description-heading">{{ __('store.description') }}</h5>
                  <div
                    id="product-description-copy"
                    class="product-detail-description-copy {{ \Illuminate\Support\Str::length($productDescription) > 320 ? '' : 'is-expanded' }}"
                    data-product-description
                  >
                    <p class="product-detail-description mb-0">{{ $productDescription }}</p>
                  </div>
                  @if (\Illuminate\Support\Str::length($productDescription) > 320)
                    <button
                      class="product-detail-description-toggle"
                      type="button"
                      aria-expanded="false"
                      aria-controls="product-description-copy"
                      data-product-description-toggle
                      data-show-more-label="{{ __('store.show_more') }}"
                      data-show-less-label="{{ __('store.show_less') }}"
                    >{{ __('store.show_more') }}</button>
                  @endif
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

              <input type="hidden" name="variant_id" value="{{ $selectedVariant->id }}" data-order-variant-id>
              <input type="hidden" name="currency" value="{{ $selectedCurrency }}" data-order-currency>
              <div class="product-detail-controls">
                <div class="product-detail-variants" role="group" aria-label="{{ __('store.choose_variant') }}">
                  <label class="form-label mb-0">{{ __('store.choose_variant') }}</label>
                  @foreach ($product->variants as $variant)
                    @php
                      $idrPriceInIdr = $product->priceInIdr((float) $variant->price, $currencyRates);
                      $idrPrice = 'Rp '.rtrim(rtrim(number_format($idrPriceInIdr, 2, '.', ','), '0'), '.');
                      $myrPrice = 'RM '.number_format($product->priceInMyr((float) $variant->price, $currencyRates), 2);
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
                      aria-disabled="{{ $variant->stock < 1 ? 'true' : 'false' }}"
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

      @include('components.product-reviews', [
          'product' => $product,
          'averageRating' => $averageRating,
          'reviewCount' => $reviewCount,
          'reviewRatingCounts' => $reviewRatingCounts,
          'canReviewOrder' => $canReviewOrder,
          'hasReviewed' => $hasReviewed,
      ])

      @if ($recommendedProducts->isNotEmpty())
        <section class="product-recommendations" aria-labelledby="item-recommendations-heading" data-recommendation-carousel>
          <div class="product-recommendation-header">
            <div class="section-heading">
              <h6>{{ __('store.similar_products') }}</h6>
              <h2 id="item-recommendations-heading">{{ __('store.more_category_products', ['category' => $product->itemCategory->localizedName()]) }}</h2>
            </div>
            <div class="product-recommendation-controls" data-recommendation-controls hidden>
              <button type="button" aria-label="{{ __('store.carousel_previous') }}" aria-controls="recommended-products-track" data-recommendation-previous disabled>
                <span aria-hidden="true">&lsaquo;</span>
              </button>
              <button type="button" aria-label="{{ __('store.carousel_next') }}" aria-controls="recommended-products-track" data-recommendation-next disabled>
                <span aria-hidden="true">&rsaquo;</span>
              </button>
            </div>
          </div>
          <div class="product-recommendation-track product-recommendation-items" id="recommended-products-track" role="region" aria-labelledby="item-recommendations-heading" tabindex="0" data-recommendation-track>
            @foreach ($recommendedProducts as $recommendedProduct)
              @php
                $availableVariants = $recommendedProduct->variants->where('stock', '>', 0);
                $priceVariants = $availableVariants->isNotEmpty() ? $availableVariants : $recommendedProduct->variants;
                $recommendedPrice = $priceVariants->min(fn ($variant) => $recommendedProduct->priceInIdr((float) $variant->price, $currencyRates));
                $recommendedImage = $recommendedProduct->image
                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($recommendedProduct->image)
                    : asset('images/trending-0'.(($loop->index % 4) + 1).'.jpg');
              @endphp
              <div class="product-recommendation-item">
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

      @if ($recommendedGameProducts->isNotEmpty())
        <section class="product-recommendations" aria-labelledby="game-product-recommendations-heading" data-recommendation-carousel>
          <div class="product-recommendation-header">
            <div class="section-heading">
              <h6>{{ __('store.explore_other_products') }}</h6>
              <h2 id="game-product-recommendations-heading">{{ __('store.recommended_products') }}</h2>
            </div>
            <div class="product-recommendation-controls" data-recommendation-controls hidden>
              <button type="button" aria-label="{{ __('store.carousel_previous') }}" aria-controls="recommended-game-products-track" data-recommendation-previous disabled>
                <span aria-hidden="true">&lsaquo;</span>
              </button>
              <button type="button" aria-label="{{ __('store.carousel_next') }}" aria-controls="recommended-game-products-track" data-recommendation-next disabled>
                <span aria-hidden="true">&rsaquo;</span>
              </button>
            </div>
          </div>
          <div class="product-recommendation-track product-recommendation-items" id="recommended-game-products-track" role="region" aria-labelledby="game-product-recommendations-heading" tabindex="0" data-recommendation-track>
            @foreach ($recommendedGameProducts as $recommendedProduct)
              @php
                $availableVariants = $recommendedProduct->variants->where('stock', '>', 0);
                $priceVariants = $availableVariants->isNotEmpty() ? $availableVariants : $recommendedProduct->variants;
                $recommendedPrice = $priceVariants->min(fn ($variant) => $recommendedProduct->priceInIdr((float) $variant->price, $currencyRates));
                $recommendedImage = $recommendedProduct->image
                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($recommendedProduct->image)
                    : asset('images/trending-0'.(($loop->index % 4) + 1).'.jpg');
              @endphp
              <div class="product-recommendation-item">
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

      @if ($recommendedItemCategories->isNotEmpty())
        <section class="product-recommendations" aria-labelledby="item-category-recommendations-heading" data-recommendation-carousel>
          <div class="product-recommendation-header">
            <div class="section-heading">
              <h6>{{ __('store.explore_other_items') }}</h6>
              <h2 id="item-category-recommendations-heading">{{ __('store.recommended_item_categories') }}</h2>
            </div>
            <div class="product-recommendation-controls" data-recommendation-controls hidden>
              <button type="button" aria-label="{{ __('store.carousel_previous') }}" aria-controls="recommended-item-categories-track" data-recommendation-previous disabled>
                <span aria-hidden="true">&lsaquo;</span>
              </button>
              <button type="button" aria-label="{{ __('store.carousel_next') }}" aria-controls="recommended-item-categories-track" data-recommendation-next disabled>
                <span aria-hidden="true">&rsaquo;</span>
              </button>
            </div>
          </div>
          <div class="product-recommendation-track" id="recommended-item-categories-track" role="region" aria-labelledby="item-category-recommendations-heading" tabindex="0" data-recommendation-track>
            @foreach ($recommendedItemCategories as $recommendedCategory)
              @php
                $categoryImage = $recommendedCategory->image
                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($recommendedCategory->image)
                    : ($recommendedCategory->products->first()?->image
                        ? \Illuminate\Support\Facades\Storage::disk('public')->url($recommendedCategory->products->first()->image)
                        : asset('images/categories-0'.(($loop->index % 5) + 1).'.jpg'));
              @endphp
              <div class="category-recommendation-item">
                <a class="category-recommendation-card d-block text-decoration-none" href="{{ route('shop', ['currency' => $selectedCurrency === 'idr' ? null : $selectedCurrency, 'item_category' => $recommendedCategory->id]) }}">
                  <img src="{{ $categoryImage }}" alt="{{ $recommendedCategory->localizedName() }}">
                  <h3>{{ $recommendedCategory->localizedName() }}</h3>
                </a>
              </div>
            @endforeach
          </div>
        </section>
      @endif

    </div>
  </main>

  <x-storefront-footer />

  <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
</body>
</html>
