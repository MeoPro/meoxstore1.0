<?php

namespace App\Http\Controllers;

use App\Actions\QueueAdminPaymentSubmittedNotification;
use App\Actions\QueueBuyerOrderUpdate;
use App\Enums\BuyerOrderUpdateType;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\ProductCurrencyRate;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(
        private QueueBuyerOrderUpdate $queueBuyerOrderUpdate,
        private QueueAdminPaymentSubmittedNotification $queueAdminPaymentSubmittedNotification,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $order = DB::transaction(function () use ($request, $validated): Order {
            $product = Product::query()
                ->whereKey($validated['product_id'])
                ->with('itemCategory:id,bobux_type,uses_robux')
                ->where('status', 'active')
                ->firstOrFail();
            $variant = ProductVariant::query()
                ->whereKey($validated['variant_id'])
                ->where('product_id', $product->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->firstOrFail();
            $quantity = (int) $validated['quantity'];

            if (
                $quantity < $variant->minimum_quantity
                || $quantity > $variant->maximum_quantity
                || $quantity > $variant->stock
            ) {
                throw ValidationException::withMessages([
                    'quantity' => 'Jumlah pembelian tidak sesuai stok atau batas varian.',
                ]);
            }

            $variant->decrement('stock', $quantity);
            $unitPrice = $product->priceInIdr((float) $variant->price, ProductCurrencyRate::current());
            $order = Order::query()->create([
                'user_id' => $request->user()->id,
                'status' => 'pending',
                'total' => $unitPrice * $quantity,
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

            return $order;
        });

        return redirect()
            ->route('orders.show', $order)
            ->with('success', __('store.order_created'));
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorizeOrderAccess($request, $order);
        $paymentDetails = $order->payment_details;

        if ($paymentDetails === null) {
            $legacyPaymentSetting = PaymentSetting::query()
                ->where('is_active', true)
                ->where(function ($query): void {
                    $query->where(function ($accountQuery): void {
                        $accountQuery->whereNotNull('bank_name')->whereNotNull('account_number');
                    })->orWhereNotNull('qr_code_path');
                })
                ->first();
            $paymentDetails = $legacyPaymentSetting?->only([
                'type',
                'currency',
                'name',
                'bank_name',
                'account_name',
                'account_number',
                'qr_code_path',
                'instructions',
            ]);
        }

        $order->load('items.product');
        $products = $order->items->pluck('product')->filter()->unique('id')->values();

        return view('orders.show', [
            'order' => $order,
            'paymentDetails' => $paymentDetails,
            'reviewedProducts' => ProductReview::query()
                ->where('order_id', $order->id)
                ->get()
                ->keyBy('product_id'),
            'reviewableProducts' => $products,
        ]);
    }

    public function submitPaymentProof(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrderAccess($request, $order);

        if (
            $order->payment_details === null
            && ! PaymentSetting::query()
                ->where('is_active', true)
                ->where(function ($query): void {
                    $query->where(function ($accountQuery): void {
                        $accountQuery->whereNotNull('bank_name')->whereNotNull('account_number');
                    })->orWhereNotNull('qr_code_path');
                })
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'payment_proof' => __('store.payment_not_configured'),
            ]);
        }

        if ($order->status !== 'pending') {
            abort(422, __('store.payment_proof_not_allowed'));
        }

        $validated = $request->validate([
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
        ]);

        $path = $validated['payment_proof']->store('payment-proofs', 'local');
        if ($path === false) {
            throw new \RuntimeException('Unable to store the uploaded payment proof.');
        }

        try {
            DB::transaction(function () use ($order, $path): void {
                $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
                if ($lockedOrder->status !== 'pending') {
                    throw ValidationException::withMessages([
                        'payment_proof' => __('store.payment_proof_not_allowed'),
                    ]);
                }

                $lockedOrder->update([
                    'payment_proof_path' => $path,
                    'status' => 'payment_submitted',
                ]);
            });
        } catch (ModelNotFoundException|ValidationException $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }

        $order->refresh();
        $this->queueBuyerOrderUpdate->handle($order, BuyerOrderUpdateType::PaymentSubmitted);
        $this->queueAdminPaymentSubmittedNotification->handle($order);

        return redirect()->route('orders.show', $order)->with('success', __('store.payment_proof_submitted'));
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

        abort_unless(array_key_exists($order->id, $request->session()->get('guest_orders', [])), 404);
    }
}
