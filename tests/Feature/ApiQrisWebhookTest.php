<?php

use App\Enums\BuyerOrderUpdateType;
use App\Mail\BuyerOrderUpdate;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function apiQrisWebhookHeaders(array $payload): array
{
    $timestamp = (string) now()->timestamp;
    $body = json_encode($payload, JSON_THROW_ON_ERROR);

    return [
        'X-APIQris-Signature' => 't='.$timestamp.',v1='.hash_hmac(
            'sha256',
            $timestamp.'.'.$body,
            config('services.apiqris.webhook_secret'),
        ),
    ];
}

function createApiQrisOrder(array $overrides = []): array
{
    $buyer = User::factory()->create();
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['stock' => 0, 'maximum_quantity' => 1]);
    $coupon = Coupon::factory()->create([
        'stock' => 1,
        'reserved_uses' => 1,
    ]);
    $order = Order::query()->create(array_merge([
        'user_id' => $buyer->id,
        'status' => 'pending',
        'total' => '9000.00',
        'coupon_id' => $coupon->id,
        'coupon_code' => $coupon->code,
        'coupon_discount_amount' => '1000.00',
        'coupon_usage_status' => 'reserved',
        'payment_details' => [
            'type' => 'qris',
            'currency' => 'idr',
            'payment_amount' => 9000,
            'apiqris_required' => true,
            'apiqris' => [
                'invoice_id' => 'qr_test_123',
                'amount' => 9000,
                'unique_code' => 123,
                'total_amount' => 9123,
                'status' => 'pending',
            ],
        ],
    ], $overrides));
    $order->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'product_name' => $product->name,
        'variant_name' => $variant->name,
        'quantity' => 1,
        'unit_price' => '10000.00',
    ]);

    return [$order, $variant, $coupon];
}

beforeEach(function () {
    config(['services.apiqris.webhook_secret' => 'test-webhook-secret']);
});

it('confirms a matching paid invoice and redeems the reserved coupon', function () {
    [$order, $variant, $coupon] = createApiQrisOrder();
    Mail::fake([BuyerOrderUpdate::class]);
    $payload = [
        'event' => 'invoice.paid',
        'id' => 'qr_test_123',
        'order_id' => $order->publicNumber(),
        'amount' => 9000,
        'total_amount' => 9123,
        'status' => 'paid',
    ];

    $this->withHeaders(apiQrisWebhookHeaders($payload))
        ->postJson(route('apiqris.webhook'), $payload)
        ->assertNoContent();

    expect($order->fresh()->status)->toBe('payment_confirmed')
        ->and($order->fresh()->coupon_usage_status)->toBe('redeemed')
        ->and($order->fresh()->payment_details['apiqris']['status'])->toBe('paid')
        ->and($variant->fresh()->stock)->toBe(0)
        ->and($coupon->fresh()->reserved_uses)->toBe(0)
        ->and($coupon->fresh()->redeemed_uses)->toBe(1);
    Mail::assertQueued(BuyerOrderUpdate::class, fn (BuyerOrderUpdate $mail): bool => $mail->order->is($order)
        && $mail->type === BuyerOrderUpdateType::PaymentConfirmed);
});

it('cancels an expired invoice and releases its stock and coupon reservation', function () {
    [$order, $variant, $coupon] = createApiQrisOrder();
    $payload = [
        'event' => 'invoice.expired',
        'id' => 'qr_test_123',
        'order_id' => $order->publicNumber(),
        'amount' => 9000,
        'total_amount' => 9123,
        'status' => 'expired',
    ];

    $this->withHeaders(apiQrisWebhookHeaders($payload))
        ->postJson(route('apiqris.webhook'), $payload)
        ->assertNoContent();

    expect($order->fresh()->status)->toBe('cancelled')
        ->and($order->fresh()->coupon_usage_status)->toBe('released')
        ->and($order->fresh()->payment_details['apiqris']['status'])->toBe('expired')
        ->and($variant->fresh()->stock)->toBe(1)
        ->and($coupon->fresh()->reserved_uses)->toBe(0);
});

it('rejects an invalid signature without changing the order', function () {
    [$order, $variant] = createApiQrisOrder();
    $payload = [
        'event' => 'invoice.paid',
        'id' => 'qr_test_123',
        'order_id' => $order->publicNumber(),
        'amount' => 9000,
        'total_amount' => 9123,
        'status' => 'paid',
    ];

    $this->withHeaders(['X-APIQris-Signature' => 't='.now()->timestamp.',v1='.str_repeat('0', 64)])
        ->postJson(route('apiqris.webhook'), $payload)
        ->assertUnauthorized();

    expect($order->fresh()->status)->toBe('pending')
        ->and($variant->fresh()->stock)->toBe(0);
});

it('ignores a correctly signed event for a different invoice', function () {
    [$order, $variant] = createApiQrisOrder();
    $payload = [
        'event' => 'invoice.paid',
        'id' => 'qr_another_invoice',
        'order_id' => $order->publicNumber(),
        'amount' => 9000,
        'total_amount' => 9123,
        'status' => 'paid',
    ];

    $this->withHeaders(apiQrisWebhookHeaders($payload))
        ->postJson(route('apiqris.webhook'), $payload)
        ->assertNoContent();

    expect($order->fresh()->status)->toBe('pending')
        ->and($variant->fresh()->stock)->toBe(0);
});
