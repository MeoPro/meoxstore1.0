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
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.variant_id' => ['required', 'integer', 'distinct', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $variantIds = collect($validated['items'])
                ->pluck('variant_id')
                ->map(fn (int $variantId): int => $variantId);
            $variants = ProductVariant::query()
                ->with(['product.itemCategory'])
                ->whereIn('id', $variantIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $currencyRates = ProductCurrencyRate::current();
            $lineItems = collect($validated['items'])->map(function (array $item) use ($variants, $currencyRates): array {
                $variant = $variants->get((int) $item['variant_id']);
                if ($variant === null) {
                    abort(404);
                }

                $product = $variant->product;
                $quantity = (int) $item['quantity'];
                $unitPrice = $product->priceInIdr((float) $variant->price, $currencyRates);

                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_description' => $product->description,
                    'product_variant_id' => $variant->id,
                    'variant_name' => $variant->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'robux_amount' => $product->usesRobuxPricing()
                        ? (float) $product->bobuxAmount(
                            (float) $variant->price,
                            $variant->bobux_amount === null ? null : (float) $variant->bobux_amount,
                        ) * $quantity
                        : null,
                ];
            });
            $total = round($lineItems->sum(
                fn (array $item): float => $item['unit_price'] * $item['quantity'],
            ), 2);
            $order = Order::query()->create([
                'user_id' => $request->user()->id,
                'status' => 'completed',
                'source' => 'cashier',
                'total' => $total,
                'cashier_actual_revenue' => $total,
                'payment_confirmed_at' => now(),
                'completed_at' => now(),
                'customer_completed_at' => now(),
            ]);

            $lineItems->each(fn (array $item) => $order->items()->create($item));

            $lineItems->pluck('product_id')->unique()->each(function (int $productId) use ($order): void {
                ProductReview::query()->create([
                    'product_id' => $productId,
                    'user_id' => null,
                    'order_id' => $order->id,
                    'rating' => 5,
                    'is_anonymous' => false,
                ]);
            });
        });

        return redirect()
            ->route('admin.cashier.index')
            ->with('success', 'Cashier sale recorded with a five-star review for each product.');
    }

    public function history(): View
    {
        $orders = Order::query()
            ->with('items')
            ->where('source', 'cashier')
            ->whereNull('cashier_history_deleted_at')
            ->latest()
            ->paginate(15);

        return view('admin.cashier.history', [
            'orders' => $orders,
        ]);
    }

    public function updateRevenue(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->source === 'cashier' && $order->cashier_history_deleted_at === null, 404);

        $validated = $request->validate([
            'actual_revenue' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
        ]);

        DB::transaction(function () use ($order, $validated): void {
            $cashierOrder = Order::query()
                ->whereKey($order->getKey())
                ->where('source', 'cashier')
                ->whereNull('cashier_history_deleted_at')
                ->lockForUpdate()
                ->firstOrFail();
            $cashierOrder->update([
                'cashier_actual_revenue' => $validated['actual_revenue'],
            ]);
        });

        return redirect()
            ->route('admin.cashier.history')
            ->with('success', 'Actual cashier revenue updated.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        abort_unless($order->source === 'cashier' && $order->cashier_history_deleted_at === null, 404);

        DB::transaction(function () use ($order): void {
            $cashierOrder = Order::query()
                ->whereKey($order->getKey())
                ->where('source', 'cashier')
                ->whereNull('cashier_history_deleted_at')
                ->lockForUpdate()
                ->firstOrFail();
            $cashierOrder->update([
                'cashier_actual_revenue' => 0,
                'cashier_history_deleted_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.cashier.history')
            ->with('success', 'Cashier revenue removed from history. Product sales and reviews were kept.');
    }
}
