<?php

namespace App\Http\Controllers;

use App\Actions\AuthorizeOrderAccess;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderTrackingController extends Controller
{
    public function __construct(private AuthorizeOrderAccess $authorizeOrderAccess) {}

    public function index(): View
    {
        return view('orders.lookup');
    }

    public function lookup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'regex:/^MEOX-(?:[A-Z0-9]{12}|\d{8,})$/i'],
            'contact' => ['required', 'string', 'max:255'],
        ]);

        $orderNumber = Str::upper($validated['order_number']);
        $order = preg_match('/^MEOX-[A-Z0-9]{12}$/', $orderNumber) === 1
            ? Order::query()->where('public_number', $orderNumber)->first()
            : Order::query()->find((int) substr($orderNumber, 5));

        $contact = trim($validated['contact']);
        $contactMatches = $order !== null && (
            ($order->contact_email !== null && strcasecmp($order->contact_email, $contact) === 0)
            || (
                $order->contact_phone !== null
                && preg_match('/^[+0-9][0-9\s().-]{6,39}$/', $contact) === 1
                && hash_equals($this->normalizePhone($order->contact_phone), $this->normalizePhone($contact))
            )
        );

        if (! $contactMatches) {
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
        $this->authorizeOrderAccess->handle($request, $order);

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
        $this->authorizeOrderAccess->handle($request, $order);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->storeOrderReviews($order, [[
            'product_id' => (int) $product->id,
            'rating' => (int) $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]], $request->user()?->id, 'rating');

        return redirect()->route('orders.show', $order)->with('success', __('store.review_submitted'));
    }

    public function storeReviews(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        $this->authorizeOrderAccess->handle($request, $order);

        $maximumReviewCount = $order->items()->distinct()->count('product_id');
        $validated = $request->validate([
            'reviews' => ['required', 'array', 'min:1', 'max:'.$maximumReviewCount],
            'reviews.*' => ['required', 'array:product_id,rating,comment'],
            'reviews.*.product_id' => ['required', 'integer', 'distinct'],
            'reviews.*.rating' => ['required', 'integer', 'between:1,5'],
            'reviews.*.comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $reviews = array_map(
            fn (array $review): array => [
                'product_id' => (int) $review['product_id'],
                'rating' => (int) $review['rating'],
                'comment' => $review['comment'] ?? null,
            ],
            $validated['reviews'],
        );

        $this->storeOrderReviews($order, $reviews, $request->user()?->id, 'reviews');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('store.reviews_submitted'),
            ]);
        }

        return redirect()->route('orders.show', $order)->with('success', __('store.reviews_submitted'));
    }

    /**
     * @param  array<int, array{product_id: int, rating: int, comment: ?string}>  $reviews
     */
    private function storeOrderReviews(
        Order $order,
        array $reviews,
        ?int $userId,
        string $duplicateErrorField,
    ): void {
        DB::transaction(function () use ($order, $reviews, $userId, $duplicateErrorField): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            abort_unless(
                $lockedOrder->status === 'completed' && $lockedOrder->customer_completed_at !== null,
                422,
                __('store.review_after_customer_completion'),
            );

            $productIds = array_column($reviews, 'product_id');
            $purchasedProductIds = $lockedOrder->items()
                ->whereIn('product_id', $productIds)
                ->distinct()
                ->pluck('product_id')
                ->map(fn (int|string $productId): int => (int) $productId);

            abort_unless($purchasedProductIds->count() === count($reviews), 404);

            if (ProductReview::query()
                ->where('order_id', $lockedOrder->id)
                ->whereIn('product_id', $productIds)
                ->exists()) {
                throw ValidationException::withMessages([
                    $duplicateErrorField => __('store.review_already_submitted'),
                ]);
            }

            foreach ($reviews as $review) {
                ProductReview::query()->create([
                    'product_id' => $review['product_id'],
                    'user_id' => $userId,
                    'order_id' => $lockedOrder->id,
                    'rating' => $review['rating'],
                    'comment' => $review['comment'],
                ]);
            }
        });
    }

    private function normalizePhone(?string $phone): string
    {
        return preg_replace('/\D+/', '', $phone ?? '') ?? '';
    }
}
