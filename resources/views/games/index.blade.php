<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('store.games_title') }} | Meoxstore</title>
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
        .game-catalog-grid { display: grid; gap: 24px; grid-template-columns: repeat(auto-fill, minmax(min(100%, 240px), 1fr)); }
        .game-catalog-card { background: #fff; border: 1px solid #eee; border-radius: 18px; height: 100%; overflow: hidden; position: relative; transition: transform .2s ease, box-shadow .2s ease; }
        @media (hover: hover) and (prefers-reduced-motion: no-preference) {
            .game-catalog-card:hover { box-shadow: 0 20px 42px rgba(30, 56, 90, .16); transform: scale(1.035); z-index: 2; }
        }
        .game-catalog-card img { aspect-ratio: 1 / 1; display: block; object-fit: cover; width: 100%; }
        .game-catalog-card-content { padding: 20px; }
        .game-catalog-card h2 { font-size: 19px; margin: 0 0 8px; }
        .game-catalog-card p { color: #7a7a7a; margin: 0 0 16px; }
        .game-catalog-card a { color: inherit; text-decoration: none; }
        .game-catalog-link { color: #ee626b !important; font-weight: 600; }
        .game-catalog-search { align-items: center; display: flex; gap: 8px; margin: 0 auto 24px; max-width: 560px; }
        .game-catalog-search .form-control { min-width: 0; }
        @media (max-width: 575.98px) {
            .game-catalog-search { align-items: stretch; flex-direction: column; }
        }
    </style>
</head>
<body class="storefront-page">
    <div id="js-preloader" class="js-preloader">
        <div class="preloader-inner">
            <span class="dot"></span>
            <div class="dots"><span></span><span></span><span></span></div>
        </div>
    </div>

    <x-storefront-header />

    <div class="page-heading header-text">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h3>{{ __('store.games_title') }}</h3>
                    <span class="breadcrumb"><a href="{{ route('home') }}">{{ __('store.home') }}</a> &gt; {{ __('store.all_games') }}</span>
                </div>
            </div>
        </div>
    </div>

    <main class="section">
        <div class="container">
            <div class="section-heading text-center mb-5">
                <h6>{{ __('store.select_game') }}</h6>
                <h2>{{ __('store.games_title') }}</h2>
                <p class="text-secondary">{{ __('store.choose_game_help') }}</p>
            </div>
            <form class="game-catalog-search" method="GET" action="{{ route('games.index') }}" role="search">
                @if ($selectedCurrency !== 'idr')
                    <input type="hidden" name="currency" value="{{ $selectedCurrency }}">
                @endif
                <label class="visually-hidden" for="game-search">{{ __('store.search_games') }}</label>
                <input class="form-control" id="game-search" name="search" type="search" value="{{ $searchTerm }}" placeholder="{{ __('store.search_games') }}">
                <button class="btn btn-primary" type="submit">{{ __('store.search') }}</button>
                @if ($searchTerm !== '')
                    <a class="btn btn-outline-secondary" href="{{ route('games.index', ['currency' => $selectedCurrency === 'idr' ? null : $selectedCurrency]) }}">{{ __('store.clear_search') }}</a>
                @endif
            </form>
            @if ($games->isEmpty())
                <p class="text-center text-secondary">{{ $searchTerm !== '' ? __('store.search_no_results') : __('store.all_games_empty') }}</p>
            @else
                <div class="game-catalog-grid">
                    @foreach ($games as $gameCategory)
                        @php($representativeProduct = $gameCategory->products->first())
                        @php($gameImage = $gameCategory->image
                            ? \Illuminate\Support\Facades\Storage::disk('public')->url($gameCategory->image)
                            : ($representativeProduct?->image
                                ? \Illuminate\Support\Facades\Storage::disk('public')->url($representativeProduct->image)
                                : asset('images/categories-0'.(($loop->index % 5) + 1).'.jpg')))
                        <article class="game-catalog-card">
                            <a href="{{ route('shop', ['game' => $gameCategory->id, 'currency' => $selectedCurrency === 'idr' ? null : $selectedCurrency]) }}" aria-label="Lihat produk {{ $gameCategory->name }}">
                                <img src="{{ $gameImage }}" alt="{{ $gameCategory->name }}">
                            </a>
                            <div class="game-catalog-card-content">
                                <h2>{{ $gameCategory->name }}</h2>
                                <p>{{ __('store.products_available', ['count' => $gameCategory->available_products_count]) }}</p>
                                <a class="game-catalog-link" href="{{ route('shop', ['game' => $gameCategory->id, 'currency' => $selectedCurrency === 'idr' ? null : $selectedCurrency]) }}">
                                    {{ __('store.view_products') }} <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </main>

    <x-storefront-footer />

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
</body>
</html>
