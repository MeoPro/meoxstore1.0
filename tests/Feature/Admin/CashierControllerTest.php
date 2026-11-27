<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('records completed cashier sales and anonymous five-star reviews without changing stock', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $product = Product::factory()->create(['name' => 'Cashier Fruit']);
    $variant = $product->variants()->firstOrFail();
    $variant->update([
        'price' => '40000.00',
        'stock' => 3,
        'maximum_quantity' => 3,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.cashier.index'))
        ->assertOk()
        ->assertSee('Cashier Fruit — Default');

    $this->actingAs($admin)
        ->post(route('admin.cashier.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ])
        ->assertRedirect(route('admin.cashier.index'))
        ->assertSessionHas('success');

    $order = Order::query()->sole();
    $review = ProductReview::query()->sole();
    expect($order->source)->toBe('cashier')
        ->and($order->status)->toBe('completed')
        ->and((float) $order->total)->toBe(80000.0)
        ->and($variant->fresh()->stock)->toBe(3)
        ->and($review->rating)->toBe(5)
        ->and($review->is_anonymous)->toBeTrue();

    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => '40000.00',
    ]);
    $this->get(route('product-details.show', $product))
        ->assertSee('2 terjual')
        ->assertSee(__('store.anonymous_reviewer'))
        ->assertSee('5.0');
    $this->actingAs($admin)
        ->get(route('admin.performance.index'))
        ->assertSee('Rp 80.000')
        ->assertSee('2 sold');
});

it('requires admin access and valid cashier quantities', function () {
    $this->get(route('admin.cashier.index'))
        ->assertRedirectToRoute('login');

    $product = Product::factory()->create();
    $variant = $product->variants()->firstOrFail();
    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->get(route('admin.cashier.index'))
        ->assertForbidden();

    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)
        ->post(route('admin.cashier.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 0,
        ])
        ->assertInvalid(['quantity']);

    $this->assertDatabaseCount('orders', 0);
});
