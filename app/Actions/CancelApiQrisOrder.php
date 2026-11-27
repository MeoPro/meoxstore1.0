<?php

namespace App\Actions;

use App\Models\Order;
use App\Services\ApiQrisClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use UnexpectedValueException;

class CancelApiQrisOrder
{
    public function __construct(
        private ApiQrisClient $apiQrisClient,
        private ProcessApiQrisWebhook $processWebhook,
    ) {}

    public function handle(Order $order): void
    {
        if ($order->status !== 'pending') {
            throw ValidationException::withMessages([
                'order' => __('store.apiqris_payment_already_received'),
            ]);
        }

        $paymentDetails = $order->payment_details ?? [];
        $storedInvoice = $paymentDetails['apiqris'] ?? [];
        if (! filled($storedInvoice['invoice_id'] ?? null)) {
            throw ValidationException::withMessages([
                'order' => __('store.apiqris_cancel_unavailable'),
            ]);
        }

        try {
            $invoice = $this->apiQrisClient->cancelInvoice($order->publicNumber());
        } catch (ConnectionException|RequestException|UnexpectedValueException|InvalidArgumentException $exception) {
            Log::warning('APIQris invoice cancellation could not be confirmed.', [
                'order_id' => $order->publicNumber(),
                'exception' => $exception::class,
            ]);

            throw ValidationException::withMessages([
                'order' => __('store.apiqris_cancel_unavailable'),
            ]);
        }

        if (($invoice['status'] ?? null) === 'not_found') {
            $invoice = [
                'id' => $storedInvoice['invoice_id'],
                'order_id' => $order->publicNumber(),
                'amount' => $storedInvoice['amount'],
                'total_amount' => $storedInvoice['total_amount'],
                'status' => 'cancelled',
            ];
        }

        $event = match ($invoice['status'] ?? null) {
            'paid' => 'invoice.paid',
            'expired' => 'invoice.expired',
            'cancelled' => 'invoice.cancelled',
            default => null,
        };

        if ($event === null) {
            throw ValidationException::withMessages([
                'order' => __('store.apiqris_cancel_unavailable'),
            ]);
        }

        $invoice['event'] = $event;
        $this->processWebhook->handle($invoice);

        if ($event === 'invoice.paid') {
            throw ValidationException::withMessages([
                'order' => __('store.apiqris_payment_already_received'),
            ]);
        }
    }
}
