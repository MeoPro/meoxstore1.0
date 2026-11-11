<?php

namespace App\Http\Controllers;

use App\Models\GameCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class GameCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $selectedCurrency = $request->query('currency', 'idr');
        if (! in_array($selectedCurrency, ['idr', 'myr', 'usd'], true)) {
            $selectedCurrency = 'idr';
        }

        $activeVariants = fn (Builder $query) => $query->where('status', 'active');
        $availableProducts = fn (Builder $query) => $query
            ->where('status', 'active')
            ->whereHas('variants', $activeVariants);

        $games = GameCategory::query()
            ->where('status', 'active')
            ->whereHas('products', $availableProducts)
            ->withCount(['products as available_products_count' => $availableProducts])
            ->with(['products' => fn (HasMany $query) => $query
                ->where('status', 'active')
                ->whereHas('variants', $activeVariants)
                ->select(['id', 'game_category_id', 'image'])
                ->orderBy('name')
                ->limit(1)])
            ->orderBy('name')
            ->get(['id', 'name', 'image']);

        return view('games.index', [
            'games' => $games,
            'selectedCurrency' => $selectedCurrency,
        ]);
    }
}
