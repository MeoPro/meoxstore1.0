<?php

use App\Enums\BuyerOrderUpdateType;
use App\Mail\BuyerOrderUpdate;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\ProductCurrencyRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('applies a percentage coupon and restores its reserved use when the order is cancelled', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['price' => '10000.00', 'stock' => 2, 'maximum_quantity' => 2]);
    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'Bank IDR',
        'currency' => 'idr',
        'is_active' => true,
    ]);
    $coupon = Coupon::factory()->create([
        'code' => 'SAVE20',
        'discount_type' => 'percent',
        'discount_value' => '20.00',
        'stock' => 1,
    ]);
    Mail::fake();

    $this->withSession([
        'checkout' => [
            'source' => 'direct',
            'items' => [$variant->id => 1],
            'currencies' => [$variant->id => 'idr'],
        ],
    ])->post(route('checkout.coupon.store'), ['coupon_code' => 'save20'])
        ->assertRedirect(route('checkout.show'));
    $this->get(route('checkout.show'))
        ->assertSee('SAVE20')
        ->assertSee(__('store.coupon_discount'));
    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '',
    ])->assertRedirect();

    $order = Order::query()->sole();
    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'total' => '8000.00',
        'coupon_id' => $coupon->id,
        'coupon_code' => 'SAVE20',
        'coupon_discount_amount' => '2000.00',
        'coupon_usage_status' => 'reserved',
    ]);
    expect((new BuyerOrderUpdate($order, BuyerOrderUpdateType::PaymentRequired))->render())
        ->toContain('Diskon kupon (SAVE20)')
        ->toContain('Rp 2.000');
    Mail::assertQueued(BuyerOrderUpdate::class, fn (BuyerOrderUpdate $mail): bool => $mail->order->is($order)
        && $mail->type === BuyerOrderUpdateType::PaymentRequired);
    expect($coupon->fresh()->availableUses())->toBe(0)
        ->and($coupon->fresh()->reserved_uses)->toBe(1);

    $this->actingAs($admin)->patch(route('admin.orders.status', $order), [
        'status' => 'cancelled',
    ])->assertRedirect();

    expect($coupon->fresh()->availableUses())->toBe(1)
        ->and($coupon->fresh()->reserved_uses)->toBe(0)
        ->and($order->fresh()->coupon_usage_status)->toBe('released');
});

it('converts a fixed IDR coupon discount into the selected payment currency', function () {
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['price' => '10000.00', 'stock' => 1, 'maximum_quantity' => 1]);
    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'USD Bank',
        'currency' => 'usd',
        'is_active' => true,
    ]);
    ProductCurrencyRate::current()->update(['idr_per_usd' => '10000.0000']);
    $coupon = Coupon::factory()->create([
        'code' => 'LESS2000',
        'discount_type' => 'fixed',
        'discount_value' => '2000.00',
        'stock' => 2,
    ]);
    Mail::fake();

    $this->withSession([
        'checkout' => [
            'source' => 'direct',
            'items' => [$variant->id => 1],
            'currencies' => [$variant->id => 'usd'],
        ],
    ])->post(route('checkout.coupon.store'), ['coupon_code' => $coupon->code])
        ->assertRedirect(route('checkout.show'));
    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '',
    ])->assertRedirect();

    $order = Order::query()->sole();
    expect((float) $order->total)->toBe(8000.0)
        ->and((float) $order->payment_details['payment_amount'])->toBe(0.8)
        ->and((float) $order->coupon_discount_amount)->toBe(2000.0);
    Mail::assertQueued(BuyerOrderUpdate::class, fn (BuyerOrderUpdate $mail): bool => $mail->order->is($order)
        && $mail->type === BuyerOrderUpdateType::PaymentRequired);
});

it('caps a fixed coupon discount at the product subtotal', function () {
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['price' => '10000.00', 'stock' => 1, 'maximum_quantity' => 1]);
    $coupon = Coupon::factory()->create([
        'code' => 'TOOBIG',
        'discount_type' => 'fixed',
        'discount_value' => '20000.00',
        'stock' => 1,
    ]);
    Mail::fake();

    $this->withSession([
        'checkout' => [
            'source' => 'direct',
            'items' => [$variant->id => 1],
            'currencies' => [$variant->id => 'idr'],
        ],
    ])->post(route('checkout.coupon.store'), ['coupon_code' => $coupon->code])
        ->assertRedirect(route('checkout.show'));
    $this->get(route('checkout.show'))
        ->assertSee(__('store.no_payment_required'))
        ->assertDontSee('name="payment_method_id"', false);
    $this->post(route('checkout.store'), [
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '',
    ])->assertRedirect();

    $order = Order::query()->sole();
    expect((float) $order->total)->toBe(0.0)
        ->and((float) $order->coupon_discount_amount)->toBe(10000.0)
        ->and((float) $order->payment_details['payment_amount'])->toBe(0.0)
        ->and($order->status)->toBe('payment_confirmed')
        ->and($order->coupon_usage_status)->toBe('redeemed')
        ->and($coupon->fresh()->redeemed_uses)->toBe(1);
    Mail::assertQueued(BuyerOrderUpdate::class, fn (BuyerOrderUpdate $mail): bool => $mail->order->is($order)
        && $mail->type === BuyerOrderUpdateType::PaymentConfirmed);
});

it('rejects unknown coupon codes without applying them to checkout', function () {
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();

    $this->withSession([
        'checkout' => [
            'source' => 'direct',
            'items' => [$variant->id => 1],
            'currencies' => [$variant->id => 'idr'],
        ],
    ])->post(route('checkout.coupon.store'), ['coupon_code' => 'UNKNOWN'])
        ->assertSessionHasErrors('coupon_code')
        ->assertSessionMissing('checkout_coupon_code');
});

it('rejects a coupon that becomes inactive before order creation', function () {
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['price' => '10000.00', 'stock' => 2, 'maximum_quantity' => 2]);
    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'Bank IDR',
        'currency' => 'idr',
        'is_active' => true,
    ]);
    $coupon = Coupon::factory()->create(['code' => 'NOTNOW', 'stock' => 2]);
    Mail::fake();

    $this->withSession([
        'checkout' => [
            'source' => 'direct',
            'items' => [$variant->id => 1],
            'currencies' => [$variant->id => 'idr'],
        ],
    ])->post(route('checkout.coupon.store'), ['coupon_code' => $coupon->code])
        ->assertRedirect(route('checkout.show'));
    $coupon->update(['is_active' => false]);

    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '',
    ])->assertSessionHasErrors('coupon_code');

    $this->assertDatabaseCount('orders', 0);
    expect($variant->fresh()->stock)->toBe(2)
        ->and($coupon->fresh()->reserved_uses)->toBe(0);
    Mail::assertNothingQueued();
});

it('moves coupon reservations to redeemed uses on payment and releases them on cancellation', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $coupon = Coupon::factory()->create([
        'code' => 'PAID10',
        'stock' => 3,
        'reserved_uses' => 1,
    ]);
    $order = Order::query()->create([
        'status' => 'payment_submitted',
        'total' => '9000.00',
        'contact_email' => 'buyer@example.com',
        'coupon_id' => $coupon->id,
        'coupon_code' => $coupon->code,
        'coupon_discount_amount' => '1000.00',
        'coupon_usage_status' => 'reserved',
    ]);
    Mail::fake();

    $this->actingAs($admin)->patch(route('admin.orders.status', $order), [
        'status' => 'payment_confirmed',
    ])->assertRedirect();

    expect($coupon->fresh()->reserved_uses)->toBe(0)
        ->and($coupon->fresh()->redeemed_uses)->toBe(1)
        ->and($order->fresh()->coupon_usage_status)->toBe('redeemed');

    $this->patch(route('admin.orders.status', $order), [
        'status' => 'cancelled',
    ])->assertRedirect();

    expect($coupon->fresh()->availableUses())->toBe(3)
        ->and($coupon->fresh()->redeemed_uses)->toBe(0)
        ->and($order->fresh()->coupon_usage_status)->toBe('released');
    Mail::assertQueued(BuyerOrderUpdate::class, fn (BuyerOrderUpdate $mail): bool => $mail->order->is($order)
        && $mail->type === BuyerOrderUpdateType::PaymentConfirmed);
});
