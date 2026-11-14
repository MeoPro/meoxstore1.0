<?php

namespace App\Http\Controllers;

use App\Models\GameCategory;
use App\Models\ItemCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCurrencyRate;
use App\Models\StoreSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $completedOrderItems = fn (Builder $query) => $query->whereHas(
            'order',
            fn (Builder $orders) => $orders->where('status', 'completed'),
        );

        $trendingItems = Product::query()
            ->where('status', 'active')
            ->whereHas('variants', fn (Builder $variants) => $variants
                ->where('status', 'active')
                ->where('stock', '>', 0))
            ->whereHas('orderItems', $completedOrderItems)
            ->with([
                'gameCategory:id,name',
                'itemCategory:id,name,bobux_type,uses_robux',
                'variants' => fn ($variants) => $variants
                    ->where('status', 'active')
                    ->select(['id', 'product_id', 'price', 'stock'])
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->withCount(['reviews' => fn (Builder $query) => $query->where('is_hidden', false)])
            ->withAvg(['reviews' => fn (Builder $query) => $query->where('is_hidden', false)], 'rating')
            ->withSum(['orderItems as sold_quantity' => $completedOrderItems], 'quantity')
            ->orderByDesc('sold_quantity')
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        $availableVariants = fn (Builder $query) => $query
            ->where('status', 'active')
            ->where('stock', '>', 0);
        $availableProducts = fn (Builder $query) => $query
            ->where('status', 'active')
            ->whereHas('variants', $availableVariants);

        $bestSellingGameCategoryIds = Product::query()
            ->join('order_items', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('products.status', 'active')
            ->where('orders.status', 'completed')
            ->whereHas('variants', $availableVariants)
            ->whereHas('gameCategory', fn (Builder $query) => $query->where('status', 'active'))
            ->selectRaw('products.game_category_id as category_id, SUM(order_items.quantity) as sold_quantity')
            ->groupBy('products.game_category_id')
            ->orderByDesc('sold_quantity')
            ->orderByDesc('category_id')
            ->limit(5)
            ->get()
            ->pluck('category_id')
            ->map(fn ($id) => (int) $id);

        $bestSellingGameCategoriesById = GameCategory::query()
            ->whereIn('id', $bestSellingGameCategoryIds)
            ->withCount(['products as available_products_count' => $availableProducts])
            ->with(['products' => fn (HasMany $query) => $query
                ->where('status', 'active')
                ->whereHas('variants', $availableVariants)
                ->select(['id', 'game_category_id', 'image'])
                ->orderBy('name')
                ->limit(1)])
            ->get(['id', 'name', 'image'])
            ->keyBy('id');

        $bestSellingGameCategories = $bestSellingGameCategoryIds
            ->map(fn (int $id) => $bestSellingGameCategoriesById->get($id))
            ->filter()
            ->values();

        $bestSellingItemCategoryIds = Product::query()
            ->join('order_items', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('products.status', 'active')
            ->where('orders.status', 'completed')
            ->whereHas('variants', $availableVariants)
            ->whereHas('itemCategory', fn (Builder $query) => $query->where('status', 'active'))
            ->selectRaw('products.item_category_id as category_id, SUM(order_items.quantity) as sold_quantity')
            ->groupBy('products.item_category_id')
            ->orderByDesc('sold_quantity')
            ->orderByDesc('category_id')
            ->limit(5)
            ->get()
            ->pluck('category_id')
            ->map(fn ($id) => (int) $id);

        $bestSellingItemCategoriesById = ItemCategory::query()
            ->whereIn('id', $bestSellingItemCategoryIds)
            ->withCount(['products as available_products_count' => $availableProducts])
            ->with(['products' => fn (HasMany $query) => $query
                ->where('status', 'active')
                ->whereHas('variants', $availableVariants)
                ->select(['id', 'item_category_id', 'image'])
                ->orderBy('name')
                ->limit(1)])
            ->get(['id', 'name', 'image'])
            ->keyBy('id');

        $bestSellingItemCategories = $bestSellingItemCategoryIds
            ->map(fn (int $id) => $bestSellingItemCategoriesById->get($id))
            ->filter()
            ->values();

        return view('home', [
            'trendingItems' => $trendingItems,
            'bestSellingGameCategories' => $bestSellingGameCategories,
            'bestSellingItemCategories' => $bestSellingItemCategories,
            'currencyRates' => ProductCurrencyRate::current(),
            'completedSoldItemCount' => (int) OrderItem::query()
                ->whereHas('order', fn (Builder $orders) => $orders->where('status', 'completed'))
                ->sum('quantity'),
            'robuxSoldDisplay' => $this->formatCompactRobux((float) OrderItem::query()->sum('robux_amount')),
            'activeOrderQueueCount' => Order::query()
                ->whereIn('status', ['payment_submitted', 'payment_confirmed'])
                ->count(),
            'storeSetting' => StoreSetting::current(),
        ]);
    }

    private function formatCompactRobux(float $robux): string
    {
        $units = __('store.robux_compact_units');
        $unitIndex = 0;

        while ($robux >= 1000 && $unitIndex < count($units) - 1) {
            $robux /= 1000;
            $unitIndex++;
        }

        $decimalSeparator = app()->getLocale() === 'en' ? '.' : ',';
        $thousandsSeparator = app()->getLocale() === 'en' ? ',' : '.';
        $decimalPlaces = $unitIndex > 0 && $robux < 100 ? 1 : 0;
        $formattedRobux = number_format($robux, $decimalPlaces, $decimalSeparator, $thousandsSeparator);

        if ($decimalPlaces > 0) {
            $formattedRobux = str_replace($decimalSeparator.'0', '', $formattedRobux);
        }

        return $formattedRobux.$units[$unitIndex];
    }
}
