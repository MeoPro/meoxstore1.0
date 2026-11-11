<?php

namespace App\Http\Controllers;

use App\Models\GameCategory;
use App\Models\ItemCategory;
use App\Models\Product;
use App\Models\ProductCurrencyRate;
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

        $products = Product::query()
            ->where('status', 'active')
            ->when($selectedGameCategoryId > 0, fn (Builder $query) => $query->where('game_category_id', $selectedGameCategoryId))
            ->when($selectedItemCategory !== null, fn (Builder $query) => $query->where('item_category_id', $selectedItemCategory->id))
            ->whereHas('variants', $activeVariants)
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
            ], 'quantity')
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('shop', [
            'products' => $products,
            'itemCategories' => $itemCategories,
            'selectedItemCategory' => $selectedItemCategory,
            'currencyRates' => ProductCurrencyRate::current(),
            'selectedCurrency' => $selectedCurrency,
            'selectedGameCategory' => $selectedGameCategory,
        ]);
    }
}
