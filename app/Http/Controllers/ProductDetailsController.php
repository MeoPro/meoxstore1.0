<?php

namespace App\Http\Controllers;

use App\Models\GameCategory;
use App\Models\ItemCategory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCurrencyRate;
use Illuminate\Contracts\View\View;
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
        $selectedVariant = $product->variants
            ->sortBy(fn ($variant): float => $product->priceInIdr((float) $variant->price, $currencyRates))
            ->first();

        $soldCount = $product->orderItems()
            ->whereHas('order', fn ($query) => $query->where('status', 'completed'))
            ->sum('quantity');
        $reviewSummary = $product->reviews()
            ->where('is_hidden', false)
            ->selectRaw('COUNT(*) as count, AVG(rating) as average')
            ->first();
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
                    ->select(['id', 'product_id', 'price'])
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->orderByDesc('is_featured')
            ->latest()
            ->limit(5)
            ->get();

        $recommendedGames = GameCategory::query()
            ->where('status', 'active')
            ->where('id', '!=', $product->game_category_id)
            ->whereHas('products', fn ($query) => $query
                ->where('status', 'active')
                ->whereHas('variants', fn ($variantQuery) => $variantQuery->where('status', 'active')))
            ->with(['products' => fn ($query) => $query
                ->where('status', 'active')
                ->whereNotNull('image')
                ->whereHas('variants', fn ($variantQuery) => $variantQuery->where('status', 'active'))
                ->select(['id', 'game_category_id', 'image'])
                ->limit(1)])
            ->orderBy('name')
            ->limit(4)
            ->get(['id', 'name', 'image']);

        $recommendedItemCategories = ItemCategory::query()
            ->where('status', 'active')
            ->where('id', '!=', $product->item_category_id)
            ->whereHas('products', fn ($query) => $query
                ->where('status', 'active')
                ->whereHas('variants', fn ($variantQuery) => $variantQuery->where('status', 'active')))
            ->with(['products' => fn ($query) => $query
                ->where('status', 'active')
                ->whereNotNull('image')
                ->whereHas('variants', fn ($variantQuery) => $variantQuery->where('status', 'active'))
                ->select(['id', 'item_category_id', 'image'])
                ->limit(1)])
            ->orderBy('name')
            ->limit(4)
            ->get(['id', 'name', 'image']);

        return view('product-details', [
            'product' => $product,
            'selectedVariant' => $selectedVariant,
            'currencyRates' => $currencyRates,
            'selectedCurrency' => $selectedCurrency,
            'soldCount' => $soldCount,
            'reviewCount' => (int) $reviewSummary->count,
            'averageRating' => (float) ($reviewSummary->average ?? 0),
            'hasReviewed' => $hasReviewed,
            'canReviewOrder' => $canReviewOrder,
            'recommendedProducts' => $recommendedProducts,
            'recommendedGames' => $recommendedGames,
            'recommendedItemCategories' => $recommendedItemCategories,
        ]);
    }
}
