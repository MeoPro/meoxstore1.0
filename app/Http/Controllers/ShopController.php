<?php

namespace App\Http\Controllers;

use App\Models\GameCategory;
use App\Models\ItemCategory;
use App\Models\Product;
use App\Models\ProductCurrencyRate;
use App\Models\ProductVariant;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $selectedCurrency = $request->query('currency', 'idr');
        if (! in_array($selectedCurrency, ['idr', 'myr', 'usd'], true)) {
            $selectedCurrency = 'idr';
        }

        $requestedSort = $request->query('sort', 'recommended');
        $availableSorts = ['recommended', 'price_asc', 'price_desc', 'best_selling', 'newest'];
        $selectedSort = is_string($requestedSort) && in_array($requestedSort, $availableSorts, true)
            ? $requestedSort
            : 'recommended';

        $searchTerm = $request->query('search', '');
        abort_unless(is_string($searchTerm), 400);
        $searchTerm = trim($searchTerm);

        $priceFilterRules = [
            'min_price' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
        ];
        if ($request->filled('min_price')) {
            $priceFilterRules['max_price'][] = 'gte:min_price';
        }
        $priceFilters = $request->validate($priceFilterRules);
        $minimumPriceFilter = $priceFilters['min_price'] ?? null;
        $maximumPriceFilter = $priceFilters['max_price'] ?? null;

        $currencyRates = ProductCurrencyRate::current();
        $activeVariants = fn ($query) => $query->where('status', 'active');
        $selectedGameCategoryId = $request->integer('game');
        $selectedGameCategory = $selectedGameCategoryId > 0
            ? GameCategory::query()->where('status', 'active')->find($selectedGameCategoryId)
            : null;

        $itemCategories = ItemCategory::query()
            ->where('status', 'active')
            ->whereHas('products', function (Builder $query) use ($activeVariants, $selectedGameCategoryId): void {
                $query->where('status', 'active')
                    ->when($selectedGameCategoryId > 0, fn (Builder $productQuery) => $productQuery->where('game_category_id', $selectedGameCategoryId))
                    ->whereHas('variants', $activeVariants);
            })
            ->orderBy('name')
            ->get(['id', 'name']);
        $selectedItemCategory = $itemCategories->firstWhere('id', $request->integer('item_category'));

        $productQuery = Product::query()
            ->where('status', 'active')
            ->when($selectedGameCategoryId > 0, fn (Builder $query) => $query->where('game_category_id', $selectedGameCategoryId))
            ->when($selectedItemCategory !== null, fn (Builder $query) => $query->where('item_category_id', $selectedItemCategory->id))
            ->when($searchTerm !== '', fn (Builder $query) => $query->where('name', 'like', '%'.$searchTerm.'%'))
            ->whereHas('variants', $activeVariants);

        if ($minimumPriceFilter !== null) {
            $minimumDisplayedPriceQuery = $this->minimumDisplayedPriceQuery($selectedCurrency, $currencyRates);
            $productQuery->whereRaw(
                '('.$minimumDisplayedPriceQuery->toSql().') >= CAST(? AS DECIMAL(30, 10))',
                array_merge($minimumDisplayedPriceQuery->getBindings(), [$minimumPriceFilter]),
            );
        }

        if ($maximumPriceFilter !== null) {
            $minimumDisplayedPriceQuery = $this->minimumDisplayedPriceQuery($selectedCurrency, $currencyRates);
            $productQuery->whereRaw(
                '('.$minimumDisplayedPriceQuery->toSql().') <= CAST(? AS DECIMAL(30, 10))',
                array_merge($minimumDisplayedPriceQuery->getBindings(), [$maximumPriceFilter]),
            );
        }

        $productQuery = $productQuery
            ->with([
                'gameCategory:id,name',
                'itemCategory:id,bobux_type,uses_robux',
                'variants' => fn ($query) => $activeVariants($query)
                    ->select(['id', 'product_id', 'name', 'price', 'stock', 'minimum_quantity', 'maximum_quantity', 'sort_order'])
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->withCount(['reviews' => fn (Builder $query) => $query->where('is_hidden', false)])
            ->withAvg(['reviews' => fn (Builder $query) => $query->where('is_hidden', false)], 'rating')
            ->withSum([
                'orderItems as sold_quantity' => fn (Builder $query) => $query
                    ->whereHas('order', fn (Builder $orders) => $orders->where('status', 'completed')),
            ], 'quantity');

        if ($selectedSort === 'price_asc') {
            $productQuery->orderBy($this->minimumDisplayedPriceQuery($selectedCurrency, $currencyRates));
        } elseif ($selectedSort === 'price_desc') {
            $productQuery->orderByDesc($this->minimumDisplayedPriceQuery($selectedCurrency, $currencyRates));
        } elseif ($selectedSort === 'best_selling') {
            $productQuery->orderByDesc('sold_quantity');
        } elseif ($selectedSort === 'newest') {
            $productQuery->orderByDesc('created_at');
        } elseif ($selectedSort === 'recommended') {
            $productQuery->orderByDesc('is_featured');
        }

        if ($selectedSort !== 'newest') {
            $productQuery->orderByDesc('created_at');
        }
        $productQuery->orderByDesc('id');

        $products = $productQuery->paginate(12)->withQueryString();

        return view('shop', [
            'products' => $products,
            'itemCategories' => $itemCategories,
            'selectedItemCategory' => $selectedItemCategory,
            'currencyRates' => $currencyRates,
            'selectedCurrency' => $selectedCurrency,
            'selectedGameCategory' => $selectedGameCategory,
            'selectedSort' => $selectedSort,
            'searchTerm' => $searchTerm,
            'minimumPriceFilter' => $minimumPriceFilter,
            'maximumPriceFilter' => $maximumPriceFilter,
        ]);
    }

    private function minimumDisplayedPriceQuery(string $currency, ProductCurrencyRate $currencyRates): Builder
    {
        $unitPriceExpression = <<<'SQL'
CASE
    WHEN item_categories.bobux_type = ? THEN product_variants.price * ?
    WHEN item_categories.bobux_type = ? THEN product_variants.price * ?
    WHEN item_categories.bobux_type IS NULL AND item_categories.uses_robux = 1 THEN product_variants.price * ?
    ELSE product_variants.price
END
SQL;
        $priceBindings = [
            'gift_ingame',
            (float) $currencyRates->idr_per_robux,
            'gamepass_5_days',
            (float) $currencyRates->idr_per_gamepass_bobux,
            (float) $currencyRates->idr_per_robux,
        ];
        $discountedPriceExpression = '('.$unitPriceExpression.' * (1.0 - COALESCE(products.discount_percent, 0) / 100.0))';
        $minimumPriceExpression = match ($currency) {
            'myr' => 'ROUND(('.$discountedPriceExpression.' / ?) * 20, 0) / 20',
            'usd' => 'ROUND((('.$discountedPriceExpression.' + CASE WHEN products.is_account_listing = 1 THEN 10000 ELSE 0 END) / ?) * 20, 0) / 20',
            default => $discountedPriceExpression,
        };

        $priceBindings = array_merge($priceBindings, match ($currency) {
            'myr' => [(float) $currencyRates->idr_per_myr],
            'usd' => [(float) $currencyRates->idr_per_usd],
            default => [],
        });

        return ProductVariant::query()
            ->selectRaw('MIN('.$minimumPriceExpression.')', $priceBindings)
            ->leftJoin('item_categories', 'item_categories.id', '=', 'products.item_category_id')
            ->whereColumn('product_variants.product_id', 'products.id')
            ->where('product_variants.status', 'active');
    }
}
