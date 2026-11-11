<?php

use App\Enums\BuyerOrderUpdateType;
use App\Mail\BuyerOrderUpdate;
use App\Mail\PaymentProofReceived;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('renders modern branded buyer and admin order emails with their key details', function () {
    $buyer = User::factory()->create(['name' => 'Test Buyer', 'email' => 'buyer@example.com']);
    $product = Product::factory()->create(['name' => 'Fruit Pack']);
    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'contact_email' => $buyer->email,
        'status' => 'pending',
        'total' => '12000.00',
        'payment_details' => [
            'name' => 'Bank Transfer',
            'account_name' => 'Meoxstore',
            'account_number' => '1234567890',
        ],
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => 'Fruit Pack',
        'variant_name' => 'Kitsune',
        'quantity' => 1,
        'unit_price' => '12000.00',
    ]);

    $buyerEmail = (new BuyerOrderUpdate($order->load(['user', 'items']), BuyerOrderUpdateType::PaymentRequired))->render();
    $adminEmail = (new PaymentProofReceived($order->load(['user', 'items'])))->render();

    expect($buyerEmail)
        ->toContain('Meoxstore', 'data:image/png;base64,', 'Selesaikan pembayaranmu', 'Test Buyer', '12.000', '1234567890', 'Lacak pesanan')
        ->and($adminEmail)
        ->toContain('data:image/png;base64,', 'Bukti pembayaran baru', $order->publicNumber(), 'buyer@example.com', 'Fruit Pack', 'Periksa pesanan di admin');
});

it('uses the order currency in admin email and embeds the buyer logo as an inline image', function () {
    $buyer = User::factory()->create(['name' => 'Test Buyer', 'email' => 'buyer@example.com']);
    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'contact_email' => $buyer->email,
        'status' => 'payment_submitted',
        'total' => '150000.00',
        'payment_details' => [
            'currency' => 'usd',
            'idr_per_unit' => 15000,
        ],
    ]);

    $adminEmail = (new PaymentProofReceived($order))->render();
    $sentBuyerEmail = (new BuyerOrderUpdate($order, BuyerOrderUpdateType::PaymentSubmitted))
        ->to($buyer->email)
        ->send(Mail::mailer('array'));
    $rawBuyerEmail = $sentBuyerEmail->getSymfonySentMessage()
        ->getOriginalMessage()
        ->toString();

    expect($adminEmail)
        ->toContain('$ 10,00')
        ->not->toContain('Rp 150.000');

    expect($rawBuyerEmail)
        ->toContain('Content-ID:', 'Content-Type: image/png', 'Content-Disposition: inline', 'src=3D"cid:', 'width=3D"100"')
        ->not->toContain('src=3D"data:image/');
});

it('reserves stock for a customer order and counts sales only after completion', function () {
    $buyer = User::factory()->create();
    $product = Product::factory()->create([
        'status' => 'active',
        'name' => 'Fruit Pack',
        'image' => 'products/fruit-pack.webp',
    ]);
    $variant = $product->variants()->firstOrFail();
    $variant->update([
        'name' => 'Kitsune',
        'price' => '40000.00',
        'stock' => 5,
        'minimum_quantity' => 1,
        'maximum_quantity' => 5,
    ]);
    Mail::fake();

    $this->actingAs($buyer)
        ->post(route('orders.store'), [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ])
        ->assertRedirect();

    $order = Order::query()->firstOrFail();
    expect($order->status)->toBe('pending')
        ->and((float) $order->total)->toBe(80000.0)
        ->and($variant->fresh()->stock)->toBe(3);
    Mail::assertNotQueued(PaymentProofReceived::class);
    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'product_name' => 'Fruit Pack',
        'variant_name' => 'Kitsune',
        'quantity' => 2,
    ]);
    $this->get(route('product-details.show', $product))
        ->assertOk()
        ->assertSee('0 terjual');

    $admin = User::factory()->create();
    $this->actingAs($admin)
        ->get(route('admin.orders.index'))
        ->assertOk()
        ->assertSee('#'.$order->id)
        ->assertSee('data-order-url="'.route('admin.orders.show', $order).'"', false)
        ->assertDontSee('Open</a>', false)
        ->assertSee('name="return_to" value="index"', false);
    $this->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee('Kitsune');
    PaymentSetting::query()->create([
        'bank_name' => 'Bank Example',
        'account_name' => 'Meoxstore',
        'account_number' => '1234567890',
    ]);
    Storage::fake('local');
    $this->actingAs($buyer)
        ->post(route('orders.payment-proof', $order), [
            'payment_proof' => UploadedFile::fake()->createWithContent(
                'receipt.pdf',
                "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF"
            ),
        ])
        ->assertRedirect(route('orders.show', $order));
    expect($order->fresh()->status)->toBe('payment_submitted');
    Mail::assertQueued(BuyerOrderUpdate::class, fn (BuyerOrderUpdate $mail): bool => $mail->order->is($order)
        && $mail->type === BuyerOrderUpdateType::PaymentSubmitted
        && $mail->hasTo($buyer->email));
    Mail::assertQueued(PaymentProofReceived::class, fn (PaymentProofReceived $mail): bool => $mail->order->is($order)
        && $mail->hasTo('devanoivan@gmail.com'));
    Storage::disk('local')->assertExists($order->fresh()->payment_proof_path);
    $this->get(route('orders.show', $order))
        ->assertSee(Storage::disk('public')->url('products/fruit-pack.webp'), false)
        ->assertSee('https://wa.me/6285933875292', false)
        ->assertSee('+6285933875292');

    $this->actingAs($admin)
        ->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee(route('admin.orders.payment-proof', $order));
    Mail::fake();
    $this->get(route('admin.orders.index'))
        ->assertSee(__('store.accept_order'))
        ->assertSee('value="payment_confirmed"', false);
    $this->patch(route('admin.orders.status', $order), [
        'status' => 'payment_confirmed',
        'return_to' => 'index',
    ])->assertRedirect(route('admin.orders.index'));
    expect($order->fresh()->status)->toBe('payment_confirmed');
    Mail::assertQueued(BuyerOrderUpdate::class, fn (BuyerOrderUpdate $mail): bool => $mail->order->is($order)
        && $mail->type === BuyerOrderUpdateType::PaymentConfirmed
        && $mail->hasTo($buyer->email));
    $this->patch(route('admin.orders.status', $order), ['status' => 'completed'])
        ->assertRedirect();
    $this->get(route('product-details.show', $product))
        ->assertOk()
        ->assertSee('2 terjual');
});

it('stores admin delivery information encrypted and emails it to the buyer when the order is completed', function () {
    $buyer = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);
    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'payment_submitted',
        'total' => '10000.00',
    ]);
    Mail::fake();

    $this->actingAs($admin)
        ->get(route('admin.orders.index'))
        ->assertSee(__('store.accept_order'));

    $this->patch(route('admin.orders.delivery-details', $order), [
        'delivery_details' => [
            ['label' => 'Username'],
        ],
    ])->assertInvalid(['delivery_details.0.value']);

    $this->patch(route('admin.orders.delivery-details', $order), [
        'delivery_details' => [
            ['label' => 'Username', 'value' => 'buyer-login'],
        ],
    ])->assertUnprocessable();

    $this->patch(route('admin.orders.status', $order), [
        'status' => 'payment_confirmed',
        'return_to' => 'index',
    ])->assertRedirect(route('admin.orders.index'));

    $this->get(route('admin.orders.show', $order))
        ->assertSee(route('admin.orders.delivery-details', $order), false)
        ->assertSee('data-add-delivery-detail', false)
        ->assertSee('data-delivery-details-form', false)
        ->assertSee(__('store.delivery_details_autosave'));

    $this->withHeader('Accept', 'application/json')
        ->patch(route('admin.orders.delivery-details', $order), [
            'delivery_details' => [
                ['label' => 'Username', 'value' => 'buyer-<script>alert(1)</script>'],
                ['label' => 'Password', 'value' => 'private-password'],
            ],
        ])->assertOk()
        ->assertJson(['message' => __('store.delivery_details_saved')]);
    Mail::assertNotQueued(BuyerOrderUpdate::class, fn (BuyerOrderUpdate $mail): bool => $mail->type === BuyerOrderUpdateType::DeliveryReady);

    $storedOrder = $order->fresh();
    expect($storedOrder->delivery_details)->toBe([
        ['label' => 'Username', 'value' => 'buyer-<script>alert(1)</script>'],
        ['label' => 'Password', 'value' => 'private-password'],
    ])->and($storedOrder->getRawOriginal('delivery_details'))
        ->not->toContain('private-password');

    $this->actingAs($buyer)
        ->get(route('orders.show', $order))
        ->assertSeeInOrder([__('store.delivery_details'), __('store.order_items')])
        ->assertSee('Username')
        ->assertSee('buyer-<script>alert(1)</script>')
        ->assertDontSee('<script>alert(1)</script>', false);

    $this->actingAs($admin)
        ->patch(route('admin.orders.status', $order), ['status' => 'completed'])
        ->assertRedirect(route('admin.orders.show', $order));
    Mail::assertQueued(BuyerOrderUpdate::class, fn (BuyerOrderUpdate $mail): bool => $mail->order->is($order)
        && $mail->type === BuyerOrderUpdateType::DeliveryReady
        && $mail->hasTo($buyer->email)
        && str_contains($mail->render(), 'buyer-&lt;script&gt;alert(1)&lt;/script&gt;')
        && str_contains($mail->render(), 'private-password'));

    $this->actingAs(User::factory()->create())
        ->get(route('orders.show', $order))
        ->assertNotFound();
});

it('restores reserved stock when an administrator cancels an order', function () {
    $buyer = User::factory()->create();
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['stock' => 4, 'maximum_quantity' => 4]);
    $this->actingAs($buyer)
        ->post(route('orders.store'), [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ])
        ->assertRedirect();
    $order = Order::query()->firstOrFail();

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.orders.status', $order), ['status' => 'cancelled'])
        ->assertRedirect();

    expect($order->fresh()->status)->toBe('cancelled')
        ->and($variant->fresh()->stock)->toBe(4);
});

it('updates selected order statuses together and returns stock when cancelling them', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['stock' => 0, 'maximum_quantity' => 5]);
    $pendingOrder = Order::query()->create(['status' => 'pending', 'total' => '10000.00']);
    $submittedOrder = Order::query()->create(['status' => 'payment_submitted', 'total' => '20000.00']);
    foreach ([[$pendingOrder, 1], [$submittedOrder, 2]] as [$order, $quantity]) {
        $order->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_name' => $variant->name,
            'quantity' => $quantity,
            'unit_price' => '10000.00',
        ]);
    }

    $this->actingAs($admin)
        ->get(route('admin.orders.index'))
        ->assertSee(route('admin.orders.bulk-status'), false)
        ->assertSee('name="ids[]"', false);
    $this->post(route('admin.orders.bulk-status'), [
        'ids' => [$pendingOrder->id, $submittedOrder->id],
        'status' => 'cancelled',
    ])->assertRedirect(route('admin.orders.index'));

    expect($pendingOrder->fresh()->status)->toBe('cancelled')
        ->and($submittedOrder->fresh()->status)->toBe('cancelled')
        ->and($variant->fresh()->stock)->toBe(3);
});

it('does not partially update orders when a bulk status is invalid for one selection', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $submittedOrder = Order::query()->create(['status' => 'payment_submitted', 'total' => '10000.00']);
    $pendingOrder = Order::query()->create(['status' => 'pending', 'total' => '10000.00']);

    $this->actingAs($admin)
        ->post(route('admin.orders.bulk-status'), [
            'ids' => [$submittedOrder->id, $pendingOrder->id],
            'status' => 'payment_confirmed',
        ])
        ->assertSessionHasErrors('status');

    $this->assertDatabaseHas('orders', ['id' => $submittedOrder->id, 'status' => 'payment_submitted']);
    $this->assertDatabaseHas('orders', ['id' => $pendingOrder->id, 'status' => 'pending']);
});

it('deletes pending orders after 24 hours and restores reserved stock', function () {
    $this->travelTo(Carbon::parse('2026-10-04 12:00:00'));
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['stock' => 3, 'maximum_quantity' => 5]);

    $expiredOrder = Order::query()->create([
        'status' => 'pending',
        'total' => '20000.00',
    ]);
    $expiredOrder->forceFill([
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ])->save();
    $expiredOrder->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'product_name' => $product->name,
        'variant_name' => $variant->name,
        'quantity' => 2,
        'unit_price' => '10000.00',
    ]);
    $notExpiredOrder = Order::query()->create([
        'status' => 'pending',
        'total' => '10000.00',
    ]);
    $notExpiredOrder->forceFill([
        'created_at' => now()->subDay()->addSecond(),
        'updated_at' => now()->subDay()->addSecond(),
    ])->save();
    $submittedOrder = Order::query()->create([
        'status' => 'payment_submitted',
        'total' => '10000.00',
        'payment_proof_path' => 'payment-proofs/submitted.pdf',
    ]);
    $submittedOrder->forceFill([
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDays(2),
    ])->save();

    $this->artisan('app:prune-unpaid-orders')
        ->assertSuccessful();

    $this->assertDatabaseMissing('orders', ['id' => $expiredOrder->id]);
    $this->assertDatabaseMissing('order_items', ['order_id' => $expiredOrder->id]);
    $this->assertDatabaseHas('orders', ['id' => $notExpiredOrder->id, 'status' => 'pending']);
    $this->assertDatabaseHas('orders', ['id' => $submittedOrder->id, 'status' => 'payment_submitted']);
    expect($variant->fresh()->stock)->toBe(5);
});

it('allows customers to review each completed purchase once', function () {
    $buyer = User::factory()->create(['name' => 'Fruit Buyer']);
    $otherUser = User::factory()->create();
    $product = Product::factory()->create(['status' => 'active', 'name' => 'Reviewable Fruit']);
    $variant = $product->variants()->firstOrFail();

    $pendingOrder = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'pending',
        'total' => '40000.00',
    ]);
    $pendingOrder->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'product_name' => $product->name,
        'variant_name' => $variant->name,
        'quantity' => 1,
        'unit_price' => '40000.00',
    ]);

    $this->actingAs($buyer)
        ->post(route('products.reviews.store', $product), ['rating' => 5, 'comment' => 'Too early'])
        ->assertForbidden();
    $this->actingAs($otherUser)
        ->post(route('products.reviews.store', $product), ['rating' => 5, 'comment' => 'Great'])
        ->assertForbidden();

    $completedOrder = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'completed',
        'total' => '40000.00',
        'completed_at' => now(),
        'customer_completed_at' => now(),
    ]);
    $completedOrder->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'product_name' => $product->name,
        'variant_name' => $variant->name,
        'quantity' => 1,
        'unit_price' => '40000.00',
    ]);

    $this->actingAs($buyer)
        ->get(route('product-details.show', $product))
        ->assertOk()
        ->assertSee('type="radio" name="rating"', false)
        ->assertDontSee('name="comment" rows="4" maxlength="2000" required', false);

    $this->actingAs($buyer)
        ->post(route('products.reviews.store', $product), ['rating' => 5, 'comment' => 'Great fruit!'])
        ->assertRedirect(route('product-details.show', $product).'#reviews');

    $this->assertDatabaseHas('product_reviews', [
        'product_id' => $product->id,
        'user_id' => $buyer->id,
        'order_id' => $completedOrder->id,
        'rating' => 5,
        'comment' => 'Great fruit!',
    ]);

    $this->get(route('product-details.show', $product))
        ->assertOk()
        ->assertSee('Fruit Buyer')
        ->assertSee('Great fruit!')
        ->assertSee('1 ulasan');

    $this->post(route('products.reviews.store', $product), ['rating' => 5, 'comment' => 'Again'])
        ->assertInvalid(['rating']);

    $secondCompletedOrder = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'completed',
        'total' => '40000.00',
        'completed_at' => now(),
        'customer_completed_at' => now(),
    ]);
    $secondCompletedOrder->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'product_name' => $product->name,
        'variant_name' => $variant->name,
        'quantity' => 1,
        'unit_price' => '40000.00',
    ]);

    $this->get(route('product-details.show', $product))
        ->assertOk()
        ->assertSee('class="review-form"', false);

    $this->post(route('products.reviews.store', $product), ['rating' => 3, 'comment' => 'Second purchase'])
        ->assertRedirect(route('product-details.show', $product).'#reviews');

    $this->assertDatabaseHas('product_reviews', [
        'product_id' => $product->id,
        'user_id' => $buyer->id,
        'order_id' => $secondCompletedOrder->id,
        'rating' => 3,
        'comment' => 'Second purchase',
    ]);
    expect(ProductReview::query()->where('product_id', $product->id)->where('user_id', $buyer->id)->count())->toBe(2);
});

it('lets a guest find an order by order number and phone, complete it, and review its product', function () {
    $product = Product::factory()->create(['name' => 'Guest Review Product', 'status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $order = Order::query()->create([
        'status' => 'completed',
        'total' => '25000.00',
        'contact_phone' => '08123456789',
        'contact_email' => 'guest@example.test',
        'completed_at' => now(),
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'product_name' => $product->name,
        'variant_name' => $variant->name,
        'quantity' => 1,
        'unit_price' => '25000.00',
    ]);
    $orderNumber = $order->publicNumber();
    expect($orderNumber)->toMatch('/^MEOX-[A-Z0-9]{12}$/');

    $this->get(route('orders.lookup'))
        ->assertSee('name="order_number"', false)
        ->assertSee('name="phone"', false);
    $this->get(route('orders.show', $order))->assertNotFound();
    $this->post(route('orders.lookup.submit'), [
        'order_number' => $orderNumber,
        'phone' => '0812-345-6789',
    ])->assertRedirect(route('orders.show', $order));
    $orderDetailResponse = $this->get(route('orders.show', $order));
    $orderDetailResponse->assertSee($orderNumber);
    $orderDetailResponse->assertSee(__('store.confirm_order_complete'));
    $orderDetailResponse->assertDontSee('page-heading header-text', false);

    $this->post(route('orders.complete', $order))
        ->assertRedirect(route('orders.show', $order));
    expect($order->fresh()->customer_completed_at)->not->toBeNull();
    $this->get(route('orders.show', $order))
        ->assertSee(__('store.review_order_intro'))
        ->assertSee(route('orders.reviews.store', [$order, $product]), false)
        ->assertSee('type="radio" name="rating"', false)
        ->assertDontSee('name="comment" rows="3" maxlength="2000" required', false);

    $this->post(route('orders.reviews.store', [$order, $product]), [
        'rating' => 5,
        'comment' => 'Fast and smooth delivery.',
    ])->assertRedirect(route('orders.show', $order));

    $this->assertDatabaseHas('product_reviews', [
        'product_id' => $product->id,
        'user_id' => null,
        'order_id' => $order->id,
        'rating' => 5,
        'comment' => 'Fast and smooth delivery.',
    ]);
    $this->get(route('product-details.show', $product))
        ->assertSee(__('store.verified_buyer'))
        ->assertSee('Fast and smooth delivery.');
});

it('accepts different ratings for multiple products in the same completed order', function () {
    $buyer = User::factory()->create();
    $products = collect([
        Product::factory()->create(['name' => 'First reviewed product', 'status' => 'active']),
        Product::factory()->create(['name' => 'Second reviewed product', 'status' => 'active']),
    ]);
    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'completed',
        'total' => '20000.00',
        'completed_at' => now(),
        'customer_completed_at' => now(),
    ]);

    foreach ($products as $product) {
        $variant = $product->variants()->firstOrFail();
        $order->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_name' => $variant->name,
            'quantity' => 1,
            'unit_price' => '10000.00',
        ]);
    }

    $this->actingAs($buyer)
        ->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee(route('orders.reviews.store', [$order, $products[0]]), false)
        ->assertSee(route('orders.reviews.store', [$order, $products[1]]), false);

    foreach ($products->values() as $index => $product) {
        $this->post(route('orders.reviews.store', [$order, $product]), [
            'rating' => $index === 0 ? 2 : 4,
            'comment' => 'Review '.($index + 1),
        ])->assertRedirect(route('orders.show', $order));
    }

    expect(ProductReview::query()->where('order_id', $order->id)->count())->toBe(2);
    $this->assertDatabaseHas('product_reviews', [
        'product_id' => $products[0]->id,
        'order_id' => $order->id,
        'rating' => 2,
        'comment' => 'Review 1',
    ]);
    $this->assertDatabaseHas('product_reviews', [
        'product_id' => $products[1]->id,
        'order_id' => $order->id,
        'rating' => 4,
        'comment' => 'Review 2',
    ]);
});

it('creates an automatic five-star review one day after customer completion and skips existing reviews', function () {
    $this->travelTo(Carbon::parse('2026-10-04 12:00:00'));
    $buyer = User::factory()->create();
    $firstProduct = Product::factory()->create(['status' => 'active']);
    $secondProduct = Product::factory()->create(['status' => 'active']);
    $firstVariant = $firstProduct->variants()->firstOrFail();
    $secondVariant = $secondProduct->variants()->firstOrFail();
    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'completed',
        'total' => '20000.00',
        'customer_completed_at' => now()->subDay()->addSecond(),
    ]);

    foreach ([[$firstProduct, $firstVariant], [$secondProduct, $secondVariant]] as [$product, $variant]) {
        $order->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_name' => $variant->name,
            'quantity' => 1,
            'unit_price' => '10000.00',
        ]);
    }

    $this->artisan('app:auto-complete-order-reviews')->assertSuccessful();
    expect(ProductReview::query()->count())->toBe(0);

    $order->customer_completed_at = now()->subDay();
    $order->save();
    ProductReview::query()->create([
        'product_id' => $firstProduct->id,
        'user_id' => $buyer->id,
        'order_id' => $order->id,
        'rating' => 4,
        'comment' => 'Already reviewed',
    ]);

    $this->artisan('app:auto-complete-order-reviews')->assertSuccessful();

    $this->assertDatabaseHas('product_reviews', [
        'product_id' => $secondProduct->id,
        'user_id' => null,
        'order_id' => $order->id,
        'rating' => 5,
        'comment' => null,
    ]);
    expect(ProductReview::query()->where('order_id', $order->id)->count())->toBe(2);

    $this->artisan('app:auto-complete-order-reviews')->assertSuccessful();
    expect(ProductReview::query()->where('order_id', $order->id)->count())->toBe(2);
});

it('allows a buyer to submit a five-star rating without a review comment', function () {
    $buyer = User::factory()->create();
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'completed',
        'total' => '10000.00',
        'customer_completed_at' => now(),
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'product_name' => $product->name,
        'variant_name' => $variant->name,
        'quantity' => 1,
        'unit_price' => '10000.00',
    ]);

    $this->actingAs($buyer)
        ->post(route('products.reviews.store', $product), ['rating' => 5])
        ->assertRedirect(route('product-details.show', $product).'#reviews');

    $this->assertDatabaseHas('product_reviews', [
        'product_id' => $product->id,
        'user_id' => $buyer->id,
        'order_id' => $order->id,
        'rating' => 5,
        'comment' => null,
    ]);
});

it('does not reveal a public order when the phone number does not match', function () {
    $order = Order::query()->create([
        'status' => 'pending',
        'total' => '10000.00',
        'contact_phone' => '08123456789',
    ]);

    $this->post(route('orders.lookup.submit'), [
        'order_number' => $order->publicNumber(),
        'phone' => '08111111111',
    ])->assertInvalid(['order_number']);
    $this->get(route('orders.show', $order))->assertNotFound();
});

it('requires admin completion before a buyer can confirm an order', function () {
    $buyer = User::factory()->create();
    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'payment_confirmed',
        'total' => '10000.00',
    ]);

    $this->actingAs($buyer)
        ->post(route('orders.complete', $order))
        ->assertUnprocessable();

    expect($order->fresh()->customer_completed_at)->toBeNull();
});

it('validates order stock and prevents customers from viewing another customers order', function () {
    $buyer = User::factory()->create();
    $otherUser = User::factory()->create();
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['stock' => 1, 'maximum_quantity' => 1]);

    $this->actingAs($buyer)
        ->post(route('orders.store'), [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ])
        ->assertInvalid(['quantity']);

    expect(Order::query()->count())->toBe(0)
        ->and($variant->fresh()->stock)->toBe(1);

    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'pending',
        'total' => '1.00',
    ]);

    $this->actingAs($otherUser)
        ->get(route('orders.show', $order))
        ->assertNotFound();
});
