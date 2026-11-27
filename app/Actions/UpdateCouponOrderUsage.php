<?php

namespace App\Actions;

use App\Models\Coupon;
use App\Models\Order;
use LogicException;

class UpdateCouponOrderUsage
{
    public function redeem(Order $order): void
    {
        if ($order->coupon_id === null || $order->coupon_usage_status !== 'reserved') {
            return;
        }

        $coupon = Coupon::query()->lockForUpdate()->findOrFail($order->coupon_id);
        if ($coupon->reserved_uses < 1) {
            throw new LogicException('A coupon order reservation is missing its reserved-use count.');
        }

        $coupon->decrement('reserved_uses');
        $coupon->increment('redeemed_uses');
        $order->update(['coupon_usage_status' => 'redeemed']);
    }

    public function release(Order $order): void
    {
        if (
            $order->coupon_id === null
            || ! in_array($order->coupon_usage_status, ['reserved', 'redeemed'], true)
        ) {
            return;
        }

        $coupon = Coupon::query()->lockForUpdate()->findOrFail($order->coupon_id);
        $usageCounter = $order->coupon_usage_status === 'reserved' ? 'reserved_uses' : 'redeemed_uses';
        if ($coupon->{$usageCounter} < 1) {
            throw new LogicException('A coupon order usage is missing its corresponding count.');
        }

        $coupon->decrement($usageCounter);
        $order->update(['coupon_usage_status' => 'released']);
    }
}
