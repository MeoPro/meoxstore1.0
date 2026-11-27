<?php

use App\Enums\BuyerOrderUpdateType;
use App\Mail\BuyerOrderUpdate;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\ProductCurrencyRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function apiQrisCheckoutInvoice(string $orderId, int $amount = 12000): array
{
    return [
        'id' => 'qr_test_123',
        'order_id' => $orderId,
        'amount' => $amount,
        'unique_code' => 123,
        'total_amount' => $amount + 123,
        'status' => 'pending',
        'qris_string' => '00020101021226test',
        'qr_image_url' => 'https://app.apiqris.com/v1/qr/qr_test_123.png',
        'expires_at' => now()->addMinutes(30)->toIso8601String(),
    ];
}

function createApiQrisCheckoutData(string $currency = 'idr'): array
{
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update([
        'price' => '12000.00',
        'stock' => 3,
        'maximum_quantity' => 3,
    ]);
    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'qris',
        'currency' => $currency,
        'name' => 'QRIS APIQris',
        'is_active' => true,
    ]);

    return [$product, $variant, $paymentSetting];
}

function configureApiQrisForTests(): void
{
    config([
        'services.apiqris.api_key' => 'AQ_TEST_example',
        'services.apiqris.webhook_secret' => 'test-webhook-secret',
        'services.apiqris.callback_url' => 'https://meoxstore.com/webhook/qris',
        'services.apiqris.expired_minutes' => 30,
    ]);
}

function createApiQrisInitializingOrder(): array
{
    $buyer = User::factory()->create();
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['price' => '12000.00', 'stock' => 2, 'maximum_quantity' => 2]);
    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'pending',
        'total' => '12000.00',
        'payment_details' => [
            'type' => 'qris',
            'currency' => 'idr',
            'payment_amount' => 12000,
            'apiqris_required' => true,
        ],
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'product_name' => $product->name,
        'variant_name' => $variant->name,
        'quantity' => 1,
        'unit_price' => '12000.00',
    ]);

    return [$order, $variant];
}

it('creates an APIQris invoice for IDR checkout and displays the dynamic QR and total', function () {
    configureApiQrisForTests();
    [$product, $variant, $paymentSetting] = createApiQrisCheckoutData();
    Mail::fake([BuyerOrderUpdate::class]);
    Http::preventStrayRequests();
    Http::fake([
        'app.apiqris.com/v1/invoices' => function (Request $request) {
            return Http::response(apiQrisCheckoutInvoice($request->data()['order_id']), 201);
        },
    ]);

    $this->withSession(['cart' => [$variant->id => 1]])
        ->post(route('cart.checkout'), ['items' => [$variant->id => '1']])
        ->assertRedirect(route('checkout.show'));
    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '08123456789',
        'buyer_timezone' => 'Asia/Singapore',
    ])->assertRedirect();

    $order = Order::query()->with('items')->sole();
    expect($order->payment_details['apiqris']['invoice_id'])->toBe('qr_test_123')
        ->and($order->payment_details['apiqris']['total_amount'])->toBe(12123)
        ->and($order->payment_details['buyer_timezone'])->toBe('Asia/Singapore')
        ->and($order->status)->toBe('pending')
        ->and($variant->fresh()->stock)->toBe(2);
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://app.apiqris.com/v1/invoices'
        && $request->hasHeader('Authorization', 'Bearer AQ_TEST_example')
        && $request->data()['amount'] === 12000
        && $request->data()['order_id'] === $order->publicNumber()
        && $request->data()['callback_url'] === 'https://meoxstore.com/webhook/qris');
    Mail::assertQueued(BuyerOrderUpdate::class, fn (BuyerOrderUpdate $mail): bool => $mail->order->is($order)
        && $mail->type === BuyerOrderUpdateType::PaymentRequired
        && str_contains($mail->render(), '(+08:00)'));

    $this->get(route('orders.show', $order))
        ->assertSee('https://app.apiqris.com/v1/qr/qr_test_123.png')
        ->assertSee('Rp 12.123')
        ->assertSee(__('store.apiqris_scan_instructions'));
});

it('does not offer or accept APIQris for non-IDR checkout', function () {
    [$product, $variant, $paymentSetting] = createApiQrisCheckoutData('myr');
    ProductCurrencyRate::current()->update(['idr_per_myr' => '1000.0000']);
    Http::preventStrayRequests();

    $this->withSession(['cart' => [$variant->id => 1]])
        ->post(route('cart.checkout'), [
            'items' => [$variant->id => '1'],
            'currency' => 'myr',
        ])
        ->assertRedirect(route('checkout.show'));
    $this->get(route('checkout.show'))
        ->assertSee(__('store.apiqris_idr_only'))
        ->assertDontSee('value="'.$paymentSetting->id.'"', false);
    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '08123456789',
    ])->assertSessionHasErrors('payment_method_id');

    $this->assertDatabaseCount('orders', 0);
    expect($variant->fresh()->stock)->toBe(3);
});

it('restores reserved stock when APIQris rejects invoice creation', function () {
    configureApiQrisForTests();
    [$product, $variant, $paymentSetting] = createApiQrisCheckoutData();
    Mail::fake([BuyerOrderUpdate::class]);
    Http::preventStrayRequests();
    Http::fake([
        'app.apiqris.com/v1/invoices' => Http::response([
            'error' => [
                'code' => 'INVALID_REQUEST',
                'message' => 'Invalid amount',
            ],
        ], 400),
        'app.apiqris.com/v1/invoices/*/cancel' => Http::response([], 404),
    ]);

    $this->withSession(['cart' => [$variant->id => 1]])
        ->post(route('cart.checkout'), ['items' => [$variant->id => '1']])
        ->assertRedirect(route('checkout.show'));
    $this->from(route('checkout.show'))->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '08123456789',
    ])->assertRedirect(route('checkout.show'))
        ->assertSessionHasErrors('payment_method_id');

    expect(Order::query()->sole()->status)->toBe('cancelled')
        ->and($variant->fresh()->stock)->toBe(3);
    Mail::assertNotQueued(BuyerOrderUpdate::class);
});

it('allows a guest to open the order page when APIQris cannot confirm invoice creation', function () {
    configureApiQrisForTests();
    [, $variant, $paymentSetting] = createApiQrisCheckoutData();
    Http::preventStrayRequests();
    Http::fake([
        'app.apiqris.com/v1/invoices' => Http::failedConnection(),
        'app.apiqris.com/v1/invoices/*/cancel' => Http::failedConnection(),
    ]);

    $this->withSession(['cart' => [$variant->id => 1]])
        ->post(route('cart.checkout'), ['items' => [$variant->id => '1']])
        ->assertRedirect(route('checkout.show'));
    $response = $this->from(route('checkout.show'))->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '08123456789',
    ]);
    $order = Order::query()->sole();

    $response->assertRedirect(route('orders.show', $order))
        ->assertSessionHas('warning', __('store.apiqris_payment_initializing'));
    $this->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee(__('store.apiqris_payment_initializing'));

    expect($order->fresh()->status)->toBe('pending')
        ->and($variant->fresh()->stock)->toBe(2);
});

it('cancels the provider invoice before restoring stock for a buyer cancellation', function () {
    configureApiQrisForTests();
    [$product, $variant, $paymentSetting] = createApiQrisCheckoutData();
    $buyer = User::factory()->create(['email' => 'buyer@example.com']);
    Http::preventStrayRequests();
    Http::fake([
        'app.apiqris.com/v1/invoices' => function (Request $request) {
            return Http::response(apiQrisCheckoutInvoice($request->data()['order_id']), 201);
        },
        'app.apiqris.com/v1/invoices/*/cancel' => function (Request $request) {
            preg_match('~/invoices/([^/]+)/cancel$~', $request->url(), $matches);

            return Http::response([
                'id' => 'qr_test_123',
                'order_id' => $matches[1],
                'amount' => 12000,
                'unique_code' => 123,
                'total_amount' => 12123,
                'status' => 'cancelled',
            ]);
        },
    ]);

    $this->actingAs($buyer)
        ->withSession(['cart' => [$variant->id => 1]])
        ->post(route('cart.checkout'), ['items' => [$variant->id => '1']])
        ->assertRedirect(route('checkout.show'));
    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '08123456789',
    ])->assertRedirect();
    $order = Order::query()->sole();

    $this->post(route('orders.cancel', $order))
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('success', __('store.order_cancelled'));

    expect($order->fresh()->status)->toBe('cancelled')
        ->and($order->fresh()->payment_details['apiqris']['status'])->toBe('cancelled')
        ->and($variant->fresh()->stock)->toBe(3);
    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/cancel')
        && $request->method() === 'POST');
});

it('does not reserve stock when APIQris credentials are missing', function () {
    config([
        'services.apiqris.api_key' => null,
        'services.apiqris.webhook_secret' => null,
    ]);
    [$product, $variant, $paymentSetting] = createApiQrisCheckoutData();
    Http::preventStrayRequests();

    $this->withSession(['cart' => [$variant->id => 1]])
        ->post(route('cart.checkout'), ['items' => [$variant->id => '1']]);
    $this->from(route('checkout.show'))->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '08123456789',
    ])->assertRedirect(route('checkout.show'))
        ->assertSessionHasErrors('payment_method_id');

    $this->assertDatabaseCount('orders', 0);
    expect($variant->fresh()->stock)->toBe(3);
});

it('does not reserve stock when the HTTPS webhook callback is missing', function () {
    configureApiQrisForTests();
    config(['services.apiqris.callback_url' => null]);
    [, $variant, $paymentSetting] = createApiQrisCheckoutData();
    Http::preventStrayRequests();

    $this->withSession(['cart' => [$variant->id => 1]])
        ->post(route('cart.checkout'), ['items' => [$variant->id => '1']]);
    $this->from(route('checkout.show'))->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '08123456789',
    ])->assertRedirect(route('checkout.show'))
        ->assertSessionHasErrors('payment_method_id');

    $this->assertDatabaseCount('orders', 0);
    expect($variant->fresh()->stock)->toBe(3);
});

it('does not let an administrator cancel an APIQris order before its invoice is stored', function () {
    [$order, $variant] = createApiQrisInitializingOrder();
    Http::preventStrayRequests();

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->patch(route('admin.orders.status', $order), ['status' => 'cancelled'])
        ->assertSessionHasErrors('order');

    expect($order->fresh()->status)->toBe('pending')
        ->and($variant->fresh()->stock)->toBe(2);
});

it('does not let bulk cancellation bypass APIQris while its invoice is initializing', function () {
    [$order, $variant] = createApiQrisInitializingOrder();
    Http::preventStrayRequests();

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->post(route('admin.orders.bulk-status'), [
            'ids' => [$order->id],
            'status' => 'cancelled',
        ])
        ->assertSessionHasErrors('status');

    expect($order->fresh()->status)->toBe('pending')
        ->and($variant->fresh()->stock)->toBe(2);
});
