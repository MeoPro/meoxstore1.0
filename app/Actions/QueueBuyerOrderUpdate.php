<?php

namespace App\Actions;

use App\Enums\BuyerOrderUpdateType;
use App\Mail\BuyerOrderUpdate;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class QueueBuyerOrderUpdate
{
    public function handle(Order $order, BuyerOrderUpdateType $type): void
    {
        $recipient = $order->contact_email ?? $order->user?->email;
        if (! filled($recipient)) {
            Log::warning('Buyer order email was not queued because no buyer email is available.', [
                'order_id' => $order->id,
                'notification_type' => $type->name,
            ]);

            return;
        }

        Mail::to($recipient)->queue(new BuyerOrderUpdate($order->load(['user', 'items']), $type));
    }
}
