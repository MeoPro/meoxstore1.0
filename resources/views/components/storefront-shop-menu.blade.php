@php
    $shopCurrency = request()->query('currency', 'idr');
    $shopCurrency = in_array($shopCurrency, ['idr', 'myr', 'usd'], true) ? $shopCurrency : 'idr';
    $currencyQuery = $shopCurrency === 'idr' ? null : $shopCurrency;
@endphp
<li class="storefront-shop-menu">
    <div @class(['storefront-shop-trigger-row', 'is-active' => request()->routeIs('shop', 'games.index')])>
        <a href="{{ route('games.index', ['currency' => $currencyQuery]) }}"
           @class(['active' => request()->routeIs('shop', 'games.index')])
           @if (request()->routeIs('games.index')) aria-current="page" @endif>
            {{ __('store.shop') }}
        </a>
        <button class="storefront-shop-toggle"
                type="button"
                aria-label="{{ __('store.shop') }} menu"
                aria-expanded="false"
                aria-haspopup="true"
                aria-controls="storefront-shop-dropdown"
                data-storefront-shop-toggle>
            <span class="storefront-shop-toggle-icon" aria-hidden="true"></span>
        </button>
    </div>
    <ul class="storefront-shop-dropdown" id="storefront-shop-dropdown">
        @foreach ($featuredGames as $gameCategory)
            <li>
                <a href="{{ route('shop', ['game' => $gameCategory->id, 'currency' => $currencyQuery]) }}"
                   data-shop-category-link
                   @if (request()->routeIs('shop') && (string) request('game') === (string) $gameCategory->id) aria-current="page" @endif>
                    {{ $gameCategory->name }}
                </a>
            </li>
        @endforeach
        <li class="storefront-shop-dropdown-more">
            <a href="{{ route('shop', ['currency' => $currencyQuery]) }}">
                {{ __('store.all_items') }}
            </a>
        </li>
        <li>
            <a href="{{ route('games.index', ['currency' => $currencyQuery]) }}" @if (request()->routeIs('games.index')) aria-current="page" @endif>
                {{ __('store.other_games') }}
            </a>
        </li>
        <li>
            <a href="https://meoxstore.com/blog" @if (request()->routeIs('blog.*')) aria-current="page" @endif>
                {{ __('store.blog') }}
            </a>
        </li>
    </ul>
</li>
