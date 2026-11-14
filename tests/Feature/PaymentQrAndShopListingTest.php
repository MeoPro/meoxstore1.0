<?php

use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('shows payment logos in checkout while keeping QR instructions on the order page', function () {
    Storage::fake('public');
    Storage::fake('local');
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->post(route('admin.payment-settings.store'), [
            'currency' => 'idr',
            'name' => 'Scan to pay',
            'account_name' => 'Example owner',
            'account_number' => 'QR-987654',
            'instructions' => 'Scan this QR code to pay.',
            'logo' => UploadedFile::fake()->createWithContent(
                'payment-logo.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true),
            ),
            'qr_code' => UploadedFile::fake()->createWithContent(
                'store-qr.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true),
            ),
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.payment-settings.index'));

    $paymentSetting = PaymentSetting::query()->sole();
    expect($paymentSetting->type)->toBe('qris');
    Storage::disk('public')->assertExists($paymentSetting->qr_code_path);
    Storage::disk('public')->assertExists($paymentSetting->logo_path);

    $product = Product::factory()->create([
        'name' => 'QR-only product',
        'description' => 'A description saved when the order is created.',
        'status' => 'active',
    ]);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['price' => '12500.00', 'stock' => 3, 'maximum_quantity' => 3]);
    $buyer = User::factory()->create();

    $this->actingAs($buyer)
        ->post(route('checkout.direct'), ['variant_id' => $variant->id, 'quantity' => 1])
        ->assertRedirect(route('checkout.show'));
    $this->get(route('checkout.show'))
        ->assertOk()
        ->assertSee('<details class="checkout-item-description">', false)
        ->assertSee('Scan to pay')
        ->assertSee(Storage::disk('public')->url($paymentSetting->logo_path), false)
        ->assertDontSee(Storage::disk('public')->url($paymentSetting->qr_code_path), false)
        ->assertDontSee('Example owner')
        ->assertDontSee('QR-987654')
        ->assertDontSee('Scan this QR code to pay.')
        ->assertDontSee(__('store.payment_type'))
        ->assertDontSee(__('store.currency'))
        ->assertDontSee(__('store.payment_provider'));

    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '+628123456789',
    ])->assertRedirect();

    $order = Order::query()->with('items')->sole();
    $originalQrCodePath = $paymentSetting->qr_code_path;
    expect($order->payment_details['qr_code_path'])->toBe($paymentSetting->qr_code_path)
        ->and($order->items->sole()->product_description)->toBe('A description saved when the order is created.');

    $this->actingAs($admin)
        ->put(route('admin.payment-settings.update', $paymentSetting), [
            'currency' => 'idr',
            'name' => 'Scan to pay updated',
            'logo' => UploadedFile::fake()->createWithContent(
                'updated-payment-logo.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true),
            ),
            'qr_code' => UploadedFile::fake()->createWithContent(
                'updated-store-qr.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true),
            ),
            'is_active_present' => '1',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.payment-settings.index'));
    Storage::disk('public')->assertExists($originalQrCodePath);
    Storage::disk('public')->assertExists($paymentSetting->fresh()->logo_path);

    $this->actingAs($buyer);
    $this->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee(Storage::disk('public')->url($paymentSetting->qr_code_path), false)
        ->assertSee('Example owner')
        ->assertSee('QR-987654')
        ->assertSee('Scan this QR code to pay.')
        ->assertSee('<details class="order-details-disclosure order-item-description">', false)
        ->assertSee('<details class="order-details-disclosure payment-instructions-disclosure">', false)
        ->assertSee('<summary>Noted:</summary>', false)
        ->assertSee('A description saved when the order is created.')
        ->assertDontSee(__('store.payment_type'))
        ->assertDontSee(__('store.currency'))
        ->assertDontSee(__('store.payment_provider'));

    $this->post(route('orders.payment-proof', $order), [
        'payment_proof' => UploadedFile::fake()->createWithContent(
            'proof.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true),
        ),
    ])->assertRedirect(route('orders.show', $order));
    $order->refresh();
    Storage::disk('local')->assertExists($order->payment_proof_path);
    $this->get(route('orders.show', $order))
        ->assertSee('src="'.asset('images/wa.png').'"', false)
        ->assertSee('<details class="order-details-disclosure order-contact-description">', false);

    $this->actingAs($admin)
        ->get(route('admin.orders.index'))
        ->assertOk()
        ->assertSee('href="'.route('admin.orders.payment-proof', $order).'"', false)
        ->assertSee('Open proof');
});

it('shows product rating and only completed sales in the shop listing', function () {
    $buyer = User::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Rated shop item',
        'status' => 'active',
    ]);
    $variant = $product->variants()->firstOrFail();

    $completedOrder = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'completed',
        'total' => 100,
    ]);
    $completedOrder->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'product_name' => $product->name,
        'variant_name' => $variant->name,
        'quantity' => 2,
        'unit_price' => 50,
    ]);
    ProductReview::query()->create([
        'product_id' => $product->id,
        'user_id' => $buyer->id,
        'order_id' => $completedOrder->id,
        'rating' => 4,
        'comment' => 'Good',
    ]);

    $pendingOrder = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'pending',
        'total' => 150,
    ]);
    $pendingOrder->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'product_name' => $product->name,
        'variant_name' => $variant->name,
        'quantity' => 3,
        'unit_price' => 50,
    ]);

    $this->get(route('shop'))
        ->assertOk()
        ->assertSee('Rated shop item')
        ->assertSee('class="shop-product-rating-star" aria-hidden="true">★</span>', false)
        ->assertSee('class="shop-product-rating-value">4.0</span>', false)
        ->assertDontSee('4.0 (1)')
        ->assertSee(__('store.sold_count', ['count' => '2']))
        ->assertDontSee('shop-item-category', false)
        ->assertDontSee(__('store.variants'));
});
