<?php

namespace App\Http\Controllers\Admin;

use App\Actions\QueueBuyerOrderUpdate;
use App\Enums\BuyerOrderUpdateType;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    private const STATUS_TRANSITIONS = [
        'pending' => ['cancelled'],
        'payment_submitted' => ['payment_confirmed', 'cancelled'],
        'payment_confirmed' => ['completed', 'cancelled'],
    ];

    public function __construct(private QueueBuyerOrderUpdate $queueBuyerOrderUpdate) {}

    public function index(): View
    {
        return $this->orderList(false);
    }

    public function queue(): View
    {
        return $this->orderList(true);
    }

    private function orderList(bool $queuedOnly): View
    {
        $orders = Order::query()
            ->with('user:id,name,email')
            ->withCount('items')
            ->when(
                $queuedOnly,
                fn ($query) => $query->whereIn('status', ['payment_submitted', 'payment_confirmed']),
            )
            ->latest()
            ->paginate(20);

        return view('admin.orders.index', [
            'orders' => $orders,
            'statusTransitions' => self::STATUS_TRANSITIONS,
            'queuedOnly' => $queuedOnly,
        ]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load(['user:id,name,email', 'items.product.gameCategory']),
        ]);
    }

    public function paymentProof(Order $order)
    {
        abort_unless($order->payment_proof_path !== null, 404);

        return Storage::disk('local')->download($order->payment_proof_path);
    }

    public function updateDeliveryDetails(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'delivery_details' => ['nullable', 'array', 'max:20'],
            'delivery_details.*' => ['required', 'array:label,value'],
            'delivery_details.*.label' => ['required', 'string', 'max:100'],
            'delivery_details.*.value' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($order, $validated): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if (! in_array($lockedOrder->status, ['payment_confirmed', 'completed'], true)) {
                abort(422, __('store.delivery_details_not_available'));
            }

            $deliveryDetails = array_values($validated['delivery_details'] ?? []);
            $lockedOrder->update([
                'delivery_details' => $deliveryDetails === [] ? null : $deliveryDetails,
            ]);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('store.delivery_details_saved'),
            ]);
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', __('store.delivery_details_saved'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['payment_confirmed', 'completed', 'cancelled'])],
            'return_to' => ['sometimes', Rule::in(['index', 'show'])],
        ]);

        DB::transaction(function () use ($order, $validated): void {
            $lockedOrder = Order::query()->with('items')->lockForUpdate()->findOrFail($order->id);
            if (! in_array($validated['status'], self::STATUS_TRANSITIONS[$lockedOrder->status] ?? [], true)) {
                abort(422, 'Perubahan status pesanan tidak diizinkan.');
            }

            $this->applyStatusTransition($lockedOrder, $validated['status']);
        });

        if ($validated['status'] === 'payment_confirmed') {
            $this->queueBuyerOrderUpdate->handle(
                $order->refresh(),
                BuyerOrderUpdateType::PaymentConfirmed,
            );
        } elseif ($validated['status'] === 'completed' && filled($order->refresh()->delivery_details)) {
            $this->queueBuyerOrderUpdate->handle(
                $order,
                BuyerOrderUpdateType::DeliveryReady,
            );
        }

        $destination = $validated['return_to'] ?? 'show';

        return $destination === 'index'
            ? redirect()->route('admin.orders.index')->with('success', 'Status pesanan diperbarui.')
            : redirect()->route('admin.orders.show', $order)->with('success', 'Status pesanan diperbarui.');
    }

    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['required', 'integer', 'distinct', Rule::exists('orders', 'id')],
            'status' => ['required', Rule::in(['payment_confirmed', 'completed', 'cancelled'])],
        ]);

        $orderIds = DB::transaction(function () use ($validated): array {
            $orders = Order::query()
                ->with('items')
                ->whereKey($validated['ids'])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($orders->count() !== count($validated['ids'])) {
                throw ValidationException::withMessages([
                    'ids' => 'One or more selected orders no longer exist.',
                ]);
            }

            foreach ($orders as $order) {
                if (! in_array($validated['status'], self::STATUS_TRANSITIONS[$order->status] ?? [], true)) {
                    throw ValidationException::withMessages([
                        'status' => 'Perubahan status pesanan tidak diizinkan untuk semua pesanan terpilih.',
                    ]);
                }
            }

            foreach ($orders as $order) {
                $this->applyStatusTransition($order, $validated['status']);
            }

            return $orders->modelKeys();
        });

        if (in_array($validated['status'], ['payment_confirmed', 'completed'], true)) {
            Order::query()->whereKey($orderIds)->get()->each(function (Order $order) use ($validated): void {
                if ($validated['status'] === 'payment_confirmed') {
                    $this->queueBuyerOrderUpdate->handle($order, BuyerOrderUpdateType::PaymentConfirmed);
                } elseif (filled($order->delivery_details)) {
                    $this->queueBuyerOrderUpdate->handle($order, BuyerOrderUpdateType::DeliveryReady);
                }
            });
        }

        return redirect()->route('admin.orders.index')->with('success', 'Selected order statuses updated.');
    }

    private function applyStatusTransition(Order $order, string $nextStatus): void
    {
        if ($nextStatus === 'cancelled') {
            foreach ($order->items as $item) {
                if ($item->product_variant_id !== null) {
                    $item->variant()->lockForUpdate()->first()?->increment('stock', $item->quantity);
                }
            }
        }

        $order->update([
            'status' => $nextStatus,
            'payment_confirmed_at' => $nextStatus === 'payment_confirmed'
                ? now()
                : $order->payment_confirmed_at,
            'completed_at' => $nextStatus === 'completed' ? now() : null,
        ]);
    }
}
