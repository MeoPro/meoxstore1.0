<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('records multiple cashier products with bobux amounts and verified-buyer reviews without changing stock', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $product = Product::factory()->create(['name' => 'Cashier Fruit']);
    $variant = $product->variants()->firstOrFail();
    $variant->update([
        'price' => '40000.00',
        'stock' => 3,
        'maximum_quantity' => 3,
    ]);
    $bobuxProduct = Product::factory()->create(['name' => 'Cashier Bobux']);
    $bobuxProduct->itemCategory()->update([
        'bobux_type' => 'gift_ingame',
        'uses_robux' => true,
    ]);
    $bobuxVariant = $bobuxProduct->variants()->firstOrFail();
    $bobuxVariant->update([
        'price' => '100.00',
        'stock' => 3,
        'maximum_quantity' => 3,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.cashier.index'))
        ->assertOk()
        ->assertSee('Cashier Fruit')
        ->assertSee('Cashier Bobux');

    $this->actingAs($admin)
        ->post(route('admin.cashier.store'), [
            'items' => [
                ['variant_id' => $variant->id, 'quantity' => 2],
                ['variant_id' => $bobuxVariant->id, 'quantity' => 3],
            ],
        ])
        ->assertRedirect(route('admin.cashier.index'))
        ->assertSessionHas('success');

    $order = Order::query()->sole();
    $reviews = ProductReview::query()->orderBy('product_id')->get();
    $bobuxOrderItem = $order->items()->where('product_id', $bobuxProduct->id)->firstOrFail();
    expect($order->source)->toBe('cashier')
        ->and($order->status)->toBe('completed')
        ->and((float) $order->total)->toBeGreaterThan(80000.0)
        ->and($variant->fresh()->stock)->toBe(3)
        ->and($bobuxVariant->fresh()->stock)->toBe(3)
        ->and((float) $bobuxOrderItem->robux_amount)->toBe(300.0)
        ->and($reviews)->toHaveCount(2)
        ->and($reviews->every(fn (ProductReview $review): bool => $review->rating === 5 && ! $review->is_anonymous && $review->user_id === null))->toBeTrue();

    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => '40000.00',
    ]);
    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'product_id' => $bobuxProduct->id,
        'quantity' => 3,
        'robux_amount' => '300.00',
    ]);
    $this->get(route('product-details.show', $bobuxProduct))
        ->assertSee(__('store.sold_count', ['count' => number_format(300)]))
        ->assertSee(__('store.verified_buyer'))
        ->assertDontSee(__('store.anonymous_reviewer'))
        ->assertSee('5.0');
    $this->actingAs($admin)
        ->get(route('admin.performance.index'))
        ->assertSee('300 sold');
});

it('edits or removes cashier revenue without changing orders, reviews, or sold quantities', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $product = Product::factory()->create(['name' => 'Cashier Fruit']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['price' => '40000.00', 'stock' => 3, 'maximum_quantity' => 3]);

    $this->actingAs($admin)->post(route('admin.cashier.store'), [
        'items' => [['variant_id' => $variant->id, 'quantity' => 2]],
    ])->assertRedirect(route('admin.cashier.index'));
    $this->actingAs($admin)->post(route('admin.cashier.store'), [
        'items' => [['variant_id' => $variant->id, 'quantity' => 1]],
    ])->assertRedirect(route('admin.cashier.index'));
    [$orderToEdit, $orderToRemove] = Order::query()->orderBy('id')->get()->all();

    $this->actingAs($admin)
        ->patch(route('admin.cashier.revenue.update', $orderToEdit), ['actual_revenue' => 65000])
        ->assertRedirect(route('admin.cashier.history'));

    expect((float) $orderToEdit->fresh()->total)->toBe(80000.0)
        ->and((float) $orderToEdit->fresh()->cashier_actual_revenue)->toBe(65000.0);

    $this->actingAs($admin)
        ->patch(route('admin.cashier.revenue.update', $orderToEdit), ['actual_revenue' => -1])
        ->assertInvalid(['actual_revenue']);

    $this->actingAs($admin)
        ->delete(route('admin.cashier.destroy', $orderToRemove))
        ->assertRedirect(route('admin.cashier.history'));

    $removedOrder = $orderToRemove->fresh();
    expect($removedOrder->cashier_history_deleted_at)->not->toBeNull()
        ->and((float) $removedOrder->cashier_actual_revenue)->toBe(0.0)
        ->and($removedOrder->items()->count())->toBe(1)
        ->and($removedOrder->reviews()->count())->toBe(1)
        ->and(ProductReview::query()->where('order_id', $removedOrder->id)->exists())->toBeTrue();

    $this->actingAs($admin)
        ->get(route('admin.cashier.history'))
        ->assertSee('<strong>#'.$orderToEdit->id.'</strong>', false)
        ->assertDontSee('<strong>#'.$orderToRemove->id.'</strong>', false);
    $this->get(route('product-details.show', $product))
        ->assertSee(__('store.sold_count', ['count' => number_format(3)]))
        ->assertSee(__('store.review_count', ['count' => 2]));
    $this->actingAs($admin)
        ->get(route('admin.performance.index'))
        ->assertSee('Rp 65.000')
        ->assertSee('3 sold');
});

it('requires admin access and valid cashier quantities', function () {
    $this->get(route('admin.cashier.index'))
        ->assertRedirectToRoute('login');
    $this->get(route('admin.cashier.history'))
        ->assertRedirectToRoute('login');

    $product = Product::factory()->create();
    $variant = $product->variants()->firstOrFail();
    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->get(route('admin.cashier.index'))
        ->assertForbidden();
    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->get(route('admin.cashier.history'))
        ->assertForbidden();

    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)
        ->post(route('admin.cashier.store'), [
            'items' => [['variant_id' => $variant->id, 'quantity' => 0]],
        ])
        ->assertInvalid(['items.0.quantity']);

    $this->assertDatabaseCount('orders', 0);
});
