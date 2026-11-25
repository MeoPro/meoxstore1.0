<?php

namespace App\Actions;

use App\Models\Order;
use Illuminate\Http\Request;

class AuthorizeOrderAccess
{
    public function handle(Request $request, Order $order): void
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
}
