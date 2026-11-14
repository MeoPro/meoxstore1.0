<?php

namespace App\Http\Controllers;

use App\Models\GameCategory;
use App\Models\ItemCategory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCurrencyRate;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductDetailsController extends Controller
{
    public function index(): RedirectResponse
    {
        $product = Product::query()
            ->where('status', 'active')
            ->whereHas('variants', fn ($query) => $query->where('status', 'active'))
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        if ($product === null) {
            return redirect()->route('shop');
        }

        return redirect()->route('product-details.show', $product);
    }

    public function show(Request $request, Product $product): View
    {
        $selectedCurrency = $request->query('currency', 'idr');
        if (! in_array($selectedCurrency, ['idr', 'myr', 'usd'], true)) {
            $selectedCurrency = 'idr';
        }

        $product->load([
            'gameCategory:id,name',
            'itemCategory:id,name,bobux_type,uses_robux',
            'reviews' => fn ($query) => $query->where('is_hidden', false)->with('user:id,name')->latest()->limit(20),
            'variants' => fn ($query) => $query
                ->where('status', 'active')
                ->select(['id', 'product_id', 'name', 'price', 'bobux_amount', 'stock', 'minimum_quantity', 'maximum_quantity'])
                ->orderBy('sort_order')
                ->orderBy('id'),
        ]);

        abort_unless($product->status === 'active' && $product->variants->isNotEmpty(), 404);
        $currencyRates = ProductCurrencyRate::current();
        $availableVariants = $product->variants->where('stock', '>', 0);
        $variantsForSelection = $availableVariants->isNotEmpty() ? $availableVariants : $product->variants;
        $selectedVariant = $variantsForSelection
            ->sortBy(fn ($variant): float => $product->priceInIdr((float) $variant->price, $currencyRates))
            ->first();

        $soldCount = $product->orderItems()
            ->whereHas('order', fn ($query) => $query->where('status', 'completed'))
            ->sum('quantity');
        $reviewSummary = $product->reviews()
            ->where('is_hidden', false)
            ->selectRaw(
                'COUNT(*) as count, AVG(rating) as average, '.
                'SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_5_count, '.
                'SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4_count, '.
                'SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3_count, '.
                'SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2_count, '.
                'SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1_count',
            )
            ->first();
        $reviewRatingCounts = collect([5, 4, 3, 2, 1])
            ->mapWithKeys(fn (int $rating): array => [
                $rating => (int) ($reviewSummary->{"rating_{$rating}_count"} ?? 0),
            ]);
        $hasReviewed = auth()->check()
            && $product->reviews()->where('user_id', auth()->id())->exists();
        $canReviewOrder = null;

        if (auth()->check()) {
            $canReviewOrder = Order::query()
                ->where('user_id', auth()->id())
                ->where('status', 'completed')
                ->whereNotNull('customer_completed_at')
                ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
                ->whereDoesntHave('reviews', fn ($query) => $query->where('product_id', $product->id))
                ->latest('completed_at')
                ->first();
        }

        $recommendedProducts = Product::query()
            ->where('status', 'active')
            ->where('item_category_id', $product->item_category_id)
            ->where('id', '!=', $product->id)
            ->whereHas('variants', fn ($query) => $query->where('status', 'active'))
            ->with([
                'gameCategory:id,name',
                'itemCategory:id,bobux_type,uses_robux',
                'variants' => fn ($query) => $query
                    ->where('status', 'active')
                    ->select(['id', 'product_id', 'price', 'stock'])
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->orderByDesc('is_featured')
            ->latest()
            ->limit(5)
            ->get();

        $recommendedGames = GameCategory::query()
            ->leftJoinSub(
                $this->completedCategorySalesQuery('game_category_id'),
                'category_sales',
                fn (JoinClause $join) => $join->on('category_sales.category_id', '=', 'game_categories.id'),
            )
            ->where('game_categories.status', 'active')
            ->where('game_categories.id', '!=', $product->game_category_id)
            ->whereHas('products', fn ($query) => $query
                ->where('status', 'active')
                ->whereHas('variants', fn ($variantQuery) => $variantQuery->where('status', 'active')))
            ->with(['products' => fn ($query) => $query
                ->where('status', 'active')
                ->whereNotNull('image')
                ->whereHas('variants', fn ($variantQuery) => $variantQuery->where('status', 'active'))
                ->select(['id', 'game_category_id', 'image'])
                ->limit(1)])
            ->orderByDesc('category_sales.sold_quantity')
            ->orderBy('game_categories.name')
            ->get(['game_categories.id', 'game_categories.name', 'game_categories.image']);

        $recommendedItemCategories = ItemCategory::query()
            ->leftJoinSub(
                $this->completedCategorySalesQuery('item_category_id'),
                'category_sales',
                fn (JoinClause $join) => $join->on('category_sales.category_id', '=', 'item_categories.id'),
            )
            ->where('item_categories.status', 'active')
            ->where('item_categories.id', '!=', $product->item_category_id)
            ->whereHas('products', fn ($query) => $query
                ->where('status', 'active')
                ->whereHas('variants', fn ($variantQuery) => $variantQuery->where('status', 'active')))
            ->with(['products' => fn ($query) => $query
                ->where('status', 'active')
                ->whereNotNull('image')
                ->whereHas('variants', fn ($variantQuery) => $variantQuery->where('status', 'active'))
                ->select(['id', 'item_category_id', 'image'])
                ->limit(1)])
            ->orderByDesc('category_sales.sold_quantity')
            ->orderBy('item_categories.name')
            ->get(['item_categories.id', 'item_categories.name', 'item_categories.image'])
            ->unique(fn (ItemCategory $itemCategory): string => $itemCategory->localizedName())
            ->values();

        return view('product-details', [
            'product' => $product,
            'selectedVariant' => $selectedVariant,
            'currencyRates' => $currencyRates,
            'selectedCurrency' => $selectedCurrency,
            'soldCount' => $soldCount,
            'reviewCount' => (int) $reviewSummary->count,
            'averageRating' => (float) ($reviewSummary->average ?? 0),
            'reviewRatingCounts' => $reviewRatingCounts,
            'hasReviewed' => $hasReviewed,
            'canReviewOrder' => $canReviewOrder,
            'recommendedProducts' => $recommendedProducts,
            'recommendedGames' => $recommendedGames,
            'recommendedItemCategories' => $recommendedItemCategories,
        ]);
    }

    private function completedCategorySalesQuery(string $categoryForeignKey): Builder
    {
        return Product::query()
            ->join('order_items', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('products.status', 'active')
            ->where('orders.status', 'completed')
            ->whereHas('variants', fn ($query) => $query->where('status', 'active'))
            ->selectRaw('products.'.$categoryForeignKey.' as category_id, SUM(order_items.quantity) as sold_quantity')
            ->groupBy('products.'.$categoryForeignKey);
    }
}
