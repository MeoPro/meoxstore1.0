<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCurrencyRate;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with([
                'itemCategory:id,bobux_type,uses_robux',
                'variants',
            ])
            ->orderBy('name')
            ->get();
        $currencyRates = ProductCurrencyRate::current();

        return view('admin.cashier.index', [
            'products' => $products,
            'currencyRates' => $currencyRates,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $variant = ProductVariant::query()
                ->with(['product.itemCategory'])
                ->lockForUpdate()
                ->findOrFail($validated['product_variant_id']);
            $product = $variant->product;
            $quantity = (int) $validated['quantity'];
            $unitPrice = $product->priceInIdr(
                (float) $variant->price,
                ProductCurrencyRate::current(),
            );
            $order = Order::query()->create([
                'user_id' => $request->user()->id,
                'status' => 'completed',
                'source' => 'cashier',
                'total' => round($unitPrice * $quantity, 2),
                'payment_confirmed_at' => now(),
                'completed_at' => now(),
                'customer_completed_at' => now(),
            ]);

            $order->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'product_name' => $product->name,
                'product_description' => $product->description,
                'variant_name' => $variant->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ]);

            ProductReview::query()->create([
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'order_id' => $order->id,
                'rating' => 5,
                'is_anonymous' => true,
            ]);
        });

        return redirect()
            ->route('admin.cashier.index')
            ->with('success', 'Cashier sale recorded with a five-star review.');
    }
}
