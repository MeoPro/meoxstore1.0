<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('rate limits repeated order submissions from the same IP address', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post(route('checkout.store'), [
            'customer_email' => 'buyer@example.com',
        ])->assertSessionHasErrors('cart');
    }

    $this->post(route('checkout.store'), [
        'customer_email' => 'buyer@example.com',
    ])->assertTooManyRequests();

    $this->assertDatabaseCount('orders', 0);
});

it('rate limits repeated direct order submissions from one signed-in buyer', function () {
    $buyer = User::factory()->create();
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['stock' => 10, 'maximum_quantity' => 10]);

    foreach (range(1, 5) as $attempt) {
        $this->actingAs($buyer)
            ->post(route('orders.store'), [
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'quantity' => 1,
            ])
            ->assertRedirect();
    }

    $this->post(route('orders.store'), [
        'product_id' => $product->id,
        'variant_id' => $variant->id,
        'quantity' => 1,
    ])->assertTooManyRequests();

    $this->assertDatabaseCount('orders', 5);
    expect($variant->fresh()->stock)->toBe(5);
});

it('hides unpaid orders from the admin list but keeps them in the buyer history', function () {
    $buyer = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);
    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'pending',
        'total' => '12000.00',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.orders.index'))
        ->assertOk()
        ->assertDontSee('data-order-url="'.route('admin.orders.show', $order).'"', false);

    $this->actingAs($buyer)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee($order->publicNumber())
        ->assertSee('data-local-time', false);
});

it('renders order and QRIS expiry timestamps for local browser formatting', function () {
    $buyer = User::factory()->create();
    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'pending',
        'total' => '12000.00',
        'payment_details' => [
            'type' => 'qris',
            'currency' => 'idr',
            'name' => 'APIQris',
            'payment_amount' => 12000,
            'apiqris_required' => true,
            'apiqris' => [
                'invoice_id' => 'qr_test_123',
                'amount' => 12000,
                'total_amount' => 12123,
                'status' => 'pending',
                'expires_at' => '2026-11-28T00:00:00+00:00',
            ],
        ],
    ]);

    $this->actingAs($buyer)
        ->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee('datetime="'.$order->created_at->utc()->toIso8601String().'" data-local-time', false)
        ->assertSee('datetime="2026-11-28T00:00:00+00:00" data-local-time', false)
        ->assertSee(__('store.apiqris_payment_deadline'));
});
