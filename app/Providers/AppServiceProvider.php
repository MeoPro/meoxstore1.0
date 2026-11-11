<?php

namespace App\Providers;

use App\Models\GameCategory;
use App\Models\StoreSetting;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Database\Eloquent\Builder;
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
            $gameCategories = GameCategory::query()
                ->where('status', 'active')
                ->whereHas('products', function (Builder $query): void {
                    $query->where('status', 'active')
                        ->whereHas('variants', fn (Builder $variantQuery) => $variantQuery->where('status', 'active'));
                })
                ->orderBy('name')
                ->get(['id', 'name']);

            $featuredGameNames = ['blox fruit', 'roblox', 'fisch'];
            $featuredGames = collect($featuredGameNames)
                ->map(fn (string $name) => $gameCategories->first(
                    fn (GameCategory $gameCategory): bool => strtolower(trim($gameCategory->name)) === $name
                ))
                ->filter()
                ->values();

            $view->with([
                'storefrontFeaturedGames' => $featuredGames,
                'storefrontCartCount' => collect(session('cart', []))->sum(),
            ]);
        });
    }
}
