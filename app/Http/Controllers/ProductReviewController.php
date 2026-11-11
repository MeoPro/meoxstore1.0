<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        abort_unless($product->status === 'active', 404);

        $order = Order::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'completed')
            ->whereNotNull('customer_completed_at')
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
            ->whereDoesntHave('reviews', fn ($query) => $query->where('product_id', $product->id))
            ->latest('completed_at')
            ->first();

        if ($order === null) {
            $hasCompletedPurchase = Order::query()
                ->where('user_id', $request->user()->id)
                ->where('status', 'completed')
                ->whereNotNull('customer_completed_at')
                ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
                ->exists();

            if ($hasCompletedPurchase) {
                throw ValidationException::withMessages([
                    'rating' => __('store.review_already_submitted'),
                ]);
            }

            abort(403, 'Ulasan hanya dapat dikirim setelah pesanan selesai.');
        }

        $product->reviews()->create([
            'user_id' => $request->user()->id,
            'order_id' => $order->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return redirect()
            ->to(route('product-details.show', ['product' => $product->id]).'#reviews')
            ->with('success', 'Terima kasih atas ulasan Anda.');
    }
}
