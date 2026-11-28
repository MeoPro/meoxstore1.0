<?php

use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('allows static QRIS checkout and manual payment proof', function () {
    Storage::fake('public');
    Storage::fake('local');
    Storage::disk('public')->put('payment-methods/store-qris.png', 'static QR code');
    Http::preventStrayRequests();
    Mail::fake();

    $buyer = User::factory()->create();
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['price' => '12000.00', 'stock' => 2, 'maximum_quantity' => 2]);
    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'qris',
        'currency' => 'idr',
        'name' => 'Store QRIS',
        'qr_code_path' => 'payment-methods/store-qris.png',
        'instructions' => 'Scan the QR and upload your payment proof.',
        'is_active' => true,
    ]);

    $this->actingAs($buyer)
        ->withSession(['cart' => [$variant->id => 1]])
        ->post(route('cart.checkout'), ['items' => [$variant->id => '1']])
        ->assertRedirect(route('checkout.show'));
    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'buyer@example.com',
    ])->assertRedirect();

    $order = Order::query()->sole();
    expect($order->status)->toBe('pending')
        ->and($order->payment_details['qr_code_path'])->toBe('payment-methods/store-qris.png');

    $this->get(route('orders.show', $order))
        ->assertSee(Storage::disk('public')->url('payment-methods/store-qris.png'), false)
        ->assertSee(route('orders.payment-proof', $order), false);
    $this->post(route('orders.payment-proof', $order), [
        'payment_proof' => UploadedFile::fake()->createWithContent(
            'proof.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true),
        ),
    ])->assertRedirect(route('orders.show', $order));

    expect($order->fresh()->status)->toBe('payment_submitted');
    Storage::disk('local')->assertExists($order->fresh()->payment_proof_path);
});

it('removes the previous payment gateway callback endpoints', function () {
    $this->post('/webhook/qris')->assertNotFound();
    $this->post('/v1.0/access-token/b2b')->assertNotFound();
    $this->post('/v1.0/qr/qr-mpm-notify')->assertNotFound();
});
