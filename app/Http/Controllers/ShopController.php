<?php

namespace App\Http\Controllers;

use App\Models\GameCategory;
use App\Models\ItemCategory;
use App\Models\Product;
use App\Models\ProductCurrencyRate;
use App\Models\ProductVariant;
use App\Services\AccountListingFormatter;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function __construct(
        private readonly AccountListingFormatter $accountListingFormatter,
    ) {}

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
        $styledSearchTerm = $this->accountListingFormatter->stylizeCategoryName($searchTerm);

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
        $availableVariants = fn (Builder $query) => $query
            ->where('status', 'active')
            ->where('stock', '>', 0);
        $gameSlug = $request->query('game');
        abort_unless($gameSlug === null || is_string($gameSlug), 400);
        $hasGameFilter = is_string($gameSlug) && $gameSlug !== '';
        $selectedGameCategory = $gameSlug === null || $gameSlug === ''
            ? null
            : GameCategory::query()
                ->where('status', 'active')
                ->where('slug', $gameSlug)
                ->first();
        if ($selectedGameCategory === null && is_string($gameSlug) && ctype_digit($gameSlug)) {
            $selectedGameCategory = GameCategory::query()
                ->where('status', 'active')
                ->find((int) $gameSlug);
        }
        $selectedGameCategoryId = $selectedGameCategory?->id ?? ($gameSlug === null || $gameSlug === '' ? 0 : -1);

        $availableItemCategories = ItemCategory::query()
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->whereHas('products', function (Builder $query) use ($availableVariants, $selectedGameCategoryId, $hasGameFilter): void {
                $query->where('status', 'active')
                    ->when($hasGameFilter, fn (Builder $productQuery) => $productQuery->where('game_category_id', $selectedGameCategoryId))
                    ->whereHas('variants', $availableVariants);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
        $itemCategorySlug = $request->query('item_category');
        abort_unless($itemCategorySlug === null || is_string($itemCategorySlug), 400);
        $selectedItemCategory = $itemCategorySlug === null || $itemCategorySlug === ''
            ? null
            : $availableItemCategories->firstWhere('slug', $itemCategorySlug);
        if ($selectedItemCategory === null && is_string($itemCategorySlug) && ctype_digit($itemCategorySlug)) {
            $selectedItemCategory = $availableItemCategories->firstWhere('id', (int) $itemCategorySlug);
        }
        $selectedItemCategoryIds = $selectedItemCategory === null
            ? []
            : $availableItemCategories
                ->filter(fn (ItemCategory $itemCategory): bool => $itemCategory->localizedName() === $selectedItemCategory->localizedName())
                ->pluck('id')
                ->all();
        $itemCategories = $availableItemCategories
            ->unique(fn (ItemCategory $itemCategory): string => $itemCategory->localizedName())
            ->values();
        $availableItemSubcategories = $selectedItemCategoryIds === []
            ? collect()
            : ItemCategory::query()
                ->whereIn('parent_id', $selectedItemCategoryIds)
                ->where('status', 'active')
                ->whereHas('subcategoryProducts', function (Builder $query) use ($availableVariants, $selectedGameCategoryId, $hasGameFilter): void {
                    $query->where('status', 'active')
                        ->when($hasGameFilter, fn (Builder $productQuery) => $productQuery->where('game_category_id', $selectedGameCategoryId))
                        ->whereHas('variants', $availableVariants);
                })
                ->orderBy('name')
                ->get(['id', 'parent_id', 'name', 'slug']);
        $itemSubcategorySlug = $request->query('item_subcategory');
        abort_unless($itemSubcategorySlug === null || is_string($itemSubcategorySlug), 400);
        $selectedItemSubcategory = $itemSubcategorySlug === null || $itemSubcategorySlug === ''
            ? null
            : $availableItemSubcategories->firstWhere('slug', $itemSubcategorySlug);
        if ($selectedItemSubcategory === null && is_string($itemSubcategorySlug) && ctype_digit($itemSubcategorySlug)) {
            $selectedItemSubcategory = $availableItemSubcategories->firstWhere('id', (int) $itemSubcategorySlug);
        }
        $itemSubcategories = $availableItemSubcategories
            ->unique(fn (ItemCategory $itemCategory): string => $itemCategory->localizedName())
            ->values();
        $selectedItemSubcategoryIds = $selectedItemSubcategory === null
            ? []
            : $availableItemSubcategories
                ->filter(fn (ItemCategory $itemCategory): bool => $itemCategory->localizedName() === $selectedItemSubcategory->localizedName())
                ->pluck('id')
                ->all();

        $productQuery = Product::query()
            ->where('status', 'active')
            ->when($hasGameFilter, fn (Builder $query) => $query->where('game_category_id', $selectedGameCategoryId))
            ->when($selectedItemCategory !== null, fn (Builder $query) => $query->whereIn('item_category_id', $selectedItemCategoryIds))
            ->when($selectedItemSubcategory !== null, fn (Builder $query) => $query
                ->whereHas(
                    'itemSubcategories',
                    fn (Builder $subcategories) => $subcategories->whereIn('item_categories.id', $selectedItemSubcategoryIds),
                ))
            ->when($searchTerm !== '', function (Builder $query) use ($searchTerm, $styledSearchTerm): void {
                $query->where(function (Builder $searchQuery) use ($searchTerm, $styledSearchTerm): void {
                    $searchQuery->where('name', 'like', '%'.$searchTerm.'%')
                        ->orWhere('description', 'like', '%'.$searchTerm.'%')
                        ->orWhere('description', 'like', '%'.$styledSearchTerm.'%');
                });
            })
            ->whereHas('variants', $availableVariants);

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
                'gameCategory:id,name,slug',
                'itemCategory:id,name,slug,bobux_type,uses_robux',
                'itemSubcategories:id,parent_id,name,slug',
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
            'itemSubcategories' => $itemSubcategories,
            'selectedItemSubcategory' => $selectedItemSubcategory,
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
            ->where('product_variants.status', 'active')
            ->where(function (Builder $query): void {
                $query->where('product_variants.stock', '>', 0)
                    ->orWhereNotExists(function (QueryBuilder $availableVariants): void {
                        $availableVariants->selectRaw('1')
                            ->from('product_variants as available_variants')
                            ->whereColumn('available_variants.product_id', 'products.id')
                            ->where('available_variants.status', 'active')
                            ->where('available_variants.stock', '>', 0);
                    });
            });
    }
}
