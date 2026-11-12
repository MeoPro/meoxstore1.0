<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentProofReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment submitted for order '.$this->order->publicNumber(),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.payment-proof-received',
            with: [
                'order' => $this->order,
                'currencySymbol' => match ($this->order->payment_details['currency'] ?? 'idr') {
                    'myr' => 'RM',
                    'usd' => '$',
                    default => 'Rp',
                },
            ],
        );
    }
}
