<?php

namespace App\Actions;

use App\Enums\BuyerOrderUpdateType;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessApiQrisWebhook
{
    public function __construct(
        private CancelOrder $cancelOrder,
        private QueueBuyerOrderUpdate $queueBuyerOrderUpdate,
        private UpdateCouponOrderUsage $updateCouponOrderUsage,
    ) {}

    /**
     * @param  array<string, mixed>  $event
     */
    public function handle(array $event): void
    {
        $eventStatus = match ($event['event'] ?? null) {
            'invoice.paid' => 'paid',
            'invoice.expired' => 'expired',
            'invoice.cancelled' => 'cancelled',
            default => null,
        };

        if ($eventStatus === null) {
            return;
        }

        $confirmedOrderId = DB::transaction(function () use ($event, $eventStatus): ?int {
            $order = Order::query()
                ->with('items')
                ->where('public_number', $event['order_id'])
                ->lockForUpdate()
                ->first();

            if ($order === null) {
                Log::warning('APIQris webhook referenced an unknown order.', [
                    'order_id' => $event['order_id'],
                    'invoice_id' => $event['id'],
                ]);

                return null;
            }

            $paymentDetails = $order->payment_details ?? [];
            $invoice = $paymentDetails['apiqris'] ?? [];
            if (
                ($paymentDetails['type'] ?? null) !== 'qris'
                || ($invoice['invoice_id'] ?? null) !== $event['id']
                || (int) ($invoice['amount'] ?? -1) !== $event['amount']
                || (int) ($invoice['total_amount'] ?? -1) !== $event['total_amount']
            ) {
                Log::warning('APIQris webhook did not match the stored invoice.', [
                    'order_id' => $event['order_id'],
                    'invoice_id' => $event['id'],
                ]);

                return null;
            }

            if ($order->status !== 'pending') {
                return null;
            }

            $invoice['status'] = $eventStatus;
            $paymentDetails['apiqris'] = $invoice;
            $order->update(['payment_details' => $paymentDetails]);

            if ($eventStatus === 'paid') {
                $this->updateCouponOrderUsage->redeem($order);
                $order->update([
                    'status' => 'payment_confirmed',
                    'payment_confirmed_at' => now(),
                ]);

                return $order->id;
            }

            $this->cancelOrder->handle($order);

            return null;
        });

        if ($confirmedOrderId !== null) {
            $this->queueBuyerOrderUpdate->handle(
                Order::query()->findOrFail($confirmedOrderId),
                BuyerOrderUpdateType::PaymentConfirmed,
            );
        }
    }
}
