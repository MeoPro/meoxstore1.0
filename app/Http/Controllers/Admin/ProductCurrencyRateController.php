<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCurrencyRate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductCurrencyRateController extends Controller
{
    public function index(): View
    {
        return view('admin.rates.index', [
            'currencyRates' => ProductCurrencyRate::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'idr_per_myr' => ['required', 'numeric', 'gt:0', 'max:9999999999.9999'],
            'idr_per_usd' => ['required', 'numeric', 'gt:0', 'max:9999999999.9999'],
            'idr_per_robux' => ['required', 'numeric', 'gt:0', 'max:9999999999.9999'],
            'idr_per_gamepass_bobux' => ['required', 'numeric', 'gt:0', 'max:9999999999.9999'],
            'bobux_gift_ingame_enabled' => ['required', 'boolean'],
            'bobux_gamepass_5_days_enabled' => ['required', 'boolean'],
            'bobux_login_enabled' => ['required', 'boolean'],
        ]);

        ProductCurrencyRate::current()->update($validated);

        return back()->with('success', 'Bobux rates and transaction settings updated.');
    }
}
