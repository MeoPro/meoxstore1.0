<?php

namespace App\Mail;

use App\Enums\BuyerOrderUpdateType;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BuyerOrderUpdate extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public BuyerOrderUpdateType $type,
    ) {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->type) {
            BuyerOrderUpdateType::PaymentRequired => 'Complete payment for order '.$this->order->publicNumber(),
            BuyerOrderUpdateType::PaymentSubmitted => 'Payment proof received for order '.$this->order->publicNumber(),
            BuyerOrderUpdateType::PaymentConfirmed => 'Payment confirmed for order '.$this->order->publicNumber(),
            BuyerOrderUpdateType::DeliveryReady => 'Your order information is ready: '.$this->order->publicNumber(),
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.buyer-update',
            with: [
                'order' => $this->order,
                'type' => $this->type,
                'orderLookupUrl' => route('orders.lookup'),
                'currencySymbol' => match ($this->order->payment_details['currency'] ?? 'idr') {
                    'myr' => 'RM',
                    'usd' => '$',
                    default => 'Rp',
                },
                'currencyRate' => (float) ($this->order->payment_details['idr_per_unit'] ?? 1),
            ],
        );
    }
}
