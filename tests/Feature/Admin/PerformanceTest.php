<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('shows paid sales for the current month and ranks products by units sold', function () {
    $this->travelTo(Carbon::parse('2026-11-25 12:00:00'));

    $admin = User::factory()->create(['is_admin' => true]);
    $buyer = User::factory()->create();
    $bestSeller = Product::factory()->create(['name' => 'Monthly best seller']);
    $secondSeller = Product::factory()->create(['name' => 'Monthly second seller']);

    $createOrder = function (string $status, string $total, ?Carbon $paymentConfirmedAt) use ($buyer): Order {
        return Order::query()->create([
            'user_id' => $buyer->id,
            'status' => $status,
            'total' => $total,
            'payment_confirmed_at' => $paymentConfirmedAt,
        ]);
    };

    $firstPaidOrder = $createOrder('payment_confirmed', '12000.00', now()->subDays(3));
    $firstPaidOrder->items()->create([
        'product_id' => $bestSeller->id,
        'product_name' => $bestSeller->name,
        'variant_name' => 'Default',
        'quantity' => 3,
        'unit_price' => '4000.00',
    ]);

    $secondPaidOrder = $createOrder('completed', '8000.00', now()->subDay());
    $secondPaidOrder->items()->create([
        'product_id' => $bestSeller->id,
        'product_name' => $bestSeller->name,
        'variant_name' => 'Default',
        'quantity' => 2,
        'unit_price' => '4000.00',
    ]);

    $thirdPaidOrder = $createOrder('completed', '3000.00', now());
    $thirdPaidOrder->items()->create([
        'product_id' => $secondSeller->id,
        'product_name' => $secondSeller->name,
        'variant_name' => 'Default',
        'quantity' => 1,
        'unit_price' => '3000.00',
    ]);

    $unconfirmedOrder = $createOrder('payment_submitted', '99000.00', null);
    $unconfirmedOrder->items()->create([
        'product_id' => $secondSeller->id,
        'product_name' => $secondSeller->name,
        'variant_name' => 'Default',
        'quantity' => 99,
        'unit_price' => '1000.00',
    ]);

    $lastMonthOrder = $createOrder('completed', '100000.00', now()->subMonth());
    $lastMonthOrder->items()->create([
        'product_id' => $bestSeller->id,
        'product_name' => $bestSeller->name,
        'variant_name' => 'Default',
        'quantity' => 100,
        'unit_price' => '1000.00',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.performance.index'));

    $response
        ->assertSee('Paid sales')
        ->assertSee('Best-selling products')
        ->assertSee('Rp 23.000')
        ->assertSee('Rp 7.667')
        ->assertSee('Rp 20.000')
        ->assertSee('Designed &amp; developed by', false)
        ->assertSee('https://datadropx.net', false)
        ->assertViewHas('salesRevenue', 23000.0)
        ->assertViewHas('paidOrderCount', 3)
        ->assertViewHas('topProducts', fn ($products): bool => $products->map(
            fn ($product): array => [$product->product_name, (int) $product->units_sold],
        )->all() === [
            ['Monthly best seller', 5],
            ['Monthly second seller', 1],
        ]);
});

it('forbids non-admin users from viewing sales performance', function () {
    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->get(route('admin.performance.index'))
        ->assertForbidden();
});

it('shows an alert beside orders while an order needs admin action', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $buyer = User::factory()->create();
    $createOrder = function (string $status) use ($buyer): Order {
        return Order::query()->create([
            'user_id' => $buyer->id,
            'status' => $status,
            'total' => '10000.00',
        ]);
    };

    $createOrder('pending');
    $createOrder('completed');

    $this->actingAs($admin)
        ->get(route('admin.performance.index'))
        ->assertDontSee('data-order-action-alert', false);

    $order = $createOrder('payment_submitted');
    $this->get(route('admin.performance.index'))
        ->assertSee('data-order-action-alert', false)
        ->assertSee('aria-label="Orders require admin action"', false);

    $order->update(['status' => 'payment_confirmed']);
    $this->get(route('admin.performance.index'))
        ->assertSee('data-order-action-alert', false);

    $order->update(['status' => 'completed']);
    $this->get(route('admin.performance.index'))
        ->assertDontSee('data-order-action-alert', false);
});
