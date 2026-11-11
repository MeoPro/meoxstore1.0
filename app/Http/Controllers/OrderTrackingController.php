<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderTrackingController extends Controller
{
    public function index(): View
    {
        return view('orders.lookup');
    }

    public function lookup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'regex:/^MEOX-(?:[A-Z0-9]{12}|\d{8,})$/i'],
            'phone' => ['required', 'string', 'regex:/^[+0-9][0-9\s().-]{6,39}$/'],
        ]);

        $orderNumber = Str::upper($validated['order_number']);
        $order = preg_match('/^MEOX-[A-Z0-9]{12}$/', $orderNumber) === 1
            ? Order::query()->where('public_number', $orderNumber)->first()
            : Order::query()->find((int) substr($orderNumber, 5));

        if (
            $order === null
            || ! hash_equals($this->normalizePhone($order->contact_phone), $this->normalizePhone($validated['phone']))
        ) {
            throw ValidationException::withMessages([
                'order_number' => __('store.order_lookup_not_found'),
            ]);
        }

        $verifiedOrders = $request->session()->get('verified_orders', []);
        $verifiedOrders[$order->id] = true;
        $request->session()->put('verified_orders', $verifiedOrders);

        return redirect()->route('orders.show', $order);
    }

    public function complete(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrderAccess($request, $order);

        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            abort_unless($lockedOrder->status === 'completed', 422, __('store.order_not_ready_to_complete'));

            if ($lockedOrder->customer_completed_at === null) {
                $lockedOrder->update(['customer_completed_at' => now()]);
            }
        });

        return redirect()->route('orders.show', $order)->with('success', __('store.order_customer_completed'));
    }

    public function storeReview(Request $request, Order $order, Product $product): RedirectResponse
    {
        $this->authorizeOrderAccess($request, $order);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $order, $product, $validated): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            abort_unless(
                $lockedOrder->status === 'completed' && $lockedOrder->customer_completed_at !== null,
                422,
                __('store.review_after_customer_completion'),
            );
            abort_unless($lockedOrder->items()->where('product_id', $product->id)->exists(), 404);

            if (ProductReview::query()->where('order_id', $lockedOrder->id)->where('product_id', $product->id)->exists()) {
                throw ValidationException::withMessages([
                    'rating' => __('store.review_already_submitted'),
                ]);
            }

            ProductReview::query()->create([
                'product_id' => $product->id,
                'user_id' => $request->user()?->id,
                'order_id' => $lockedOrder->id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]);
        });

        return redirect()->route('orders.show', $order)->with('success', __('store.review_submitted'));
    }

    private function authorizeOrderAccess(Request $request, Order $order): void
    {
        if (isset($request->session()->get('verified_orders', [])[$order->id])) {
            return;
        }

        if ($order->user_id !== null) {
            abort_unless($order->user_id === $request->user()?->id, 404);

            return;
        }

        abort_unless(isset($request->session()->get('guest_orders', [])[$order->id]), 404);
    }

    private function normalizePhone(?string $phone): string
    {
        return preg_replace('/\D+/', '', $phone ?? '') ?? '';
    }
}
