<?php

namespace App\Actions;

use App\Models\Order;

class CancelOrder
{
    public function __construct(private UpdateCouponOrderUsage $updateCouponOrderUsage) {}

    public function handle(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->product_variant_id !== null) {
                $item->variant()->lockForUpdate()->first()?->increment('stock', $item->quantity);
            }
        }

        $this->updateCouponOrderUsage->release($order);

        $order->update([
            'status' => 'cancelled',
            'completed_at' => null,
        ]);
    }
}
