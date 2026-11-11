<?php

namespace App\Actions;

use App\Mail\PaymentProofReceived;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class QueueAdminPaymentSubmittedNotification
{
    public function handle(Order $order): void
    {
        $recipient = config('store.admin_email');
        if (! filled($recipient)) {
            Log::warning('Payment submitted email was not queued because no admin recipient is configured.', [
                'order_id' => $order->id,
            ]);

            return;
        }

        Mail::to($recipient)->queue(new PaymentProofReceived($order->load(['user', 'items'])));
    }
}
