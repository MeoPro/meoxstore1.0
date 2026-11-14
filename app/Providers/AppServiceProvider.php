<?php

namespace App\Providers;

use App\Models\GameCategory;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\StoreSetting;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('admin.layout', function (ViewContract $view): void {
            $view->with('adminStoreSetting', StoreSetting::current());
        });

        View::composer('components.storefront-header', function (ViewContract $view): void {
            $ratingStatistics = ProductReview::query()
                ->where('is_hidden', false)
                ->whereHas('order', fn (Builder $query) => $query
                    ->where('status', 'completed')
                    ->whereNotNull('customer_completed_at'))
                ->toBase()
                ->selectRaw('COUNT(*) as review_count, AVG(rating) as average_rating')
                ->first();

            $ratingCount = (int) $ratingStatistics->review_count;

            $salesByGame = Product::query()
                ->join('order_items', 'order_items.product_id', '=', 'products.id')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('products.status', 'active')
                ->where('orders.status', 'completed')
                ->whereHas('variants', fn (Builder $query) => $query->where('status', 'active'))
                ->selectRaw('products.game_category_id, SUM(order_items.quantity) as sold_quantity')
                ->groupBy('products.game_category_id');

            $featuredGames = GameCategory::query()
                ->leftJoinSub($salesByGame, 'game_sales', function (JoinClause $join): void {
                    $join->on('game_categories.id', '=', 'game_sales.game_category_id');
                })
                ->where('game_categories.status', 'active')
                ->whereHas('products', function (Builder $query): void {
                    $query->where('status', 'active')
                        ->whereHas('variants', fn (Builder $variantQuery) => $variantQuery->where('status', 'active'));
                })
                ->orderByDesc('game_sales.sold_quantity')
                ->orderBy('game_categories.name')
                ->limit(5)
                ->get(['game_categories.id', 'game_categories.name']);

            $view->with([
                'storefrontFeaturedGames' => $featuredGames,
                'storefrontCartCount' => collect(session('cart', []))->sum(),
                'storefrontRatingAverage' => $ratingCount > 0 ? (float) $ratingStatistics->average_rating : null,
                'storefrontRatingCount' => $ratingCount,
            ]);
        });
    }
}
