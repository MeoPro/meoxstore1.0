<?php

use App\Enums\BuyerOrderUpdateType;
use App\Mail\BuyerOrderUpdate;
use App\Mail\PaymentProofReceived;
use App\Models\GameCategory;
use App\Models\ItemCategory;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\ProductCurrencyRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('blocks the selected Bobux type while leaving regular purchases available', function () {
    $gameCategory = GameCategory::factory()->create(['name' => 'Fisch']);
    $robuxCategory = ItemCategory::factory()->create(['name' => 'Gamepass', 'uses_robux' => true]);
    $robuxProduct = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $robuxCategory->id,
        'status' => 'active',
    ]);
    $robuxVariant = $robuxProduct->variants()->firstOrFail();
    $robuxVariant->update(['price' => '100.00', 'stock' => 5, 'maximum_quantity' => 5]);
    $regularProduct = Product::factory()->create(['status' => 'active']);
    $regularVariant = $regularProduct->variants()->firstOrFail();
    $regularVariant->update(['price' => '10000.00', 'stock' => 5, 'maximum_quantity' => 5]);
    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'BCA',
        'is_active' => true,
    ]);
    ProductCurrencyRate::current()->update(['bobux_gift_ingame_enabled' => false]);
    Mail::fake();

    $this->get(route('product-details.show', $robuxProduct))
        ->assertSee(__('store.robux_transactions_disabled'))
        ->assertSee('data-order-submit', false)
        ->assertSee('disabled', false);

    $this->withSession([
        'cart' => [$robuxVariant->id => 1],
    ])->get(route('cart.index'))
        ->assertSee(__('store.robux_transactions_disabled'));

    $this->withSession(['cart' => []])->postJson(route('cart.items.store'), [
        'variant_id' => $robuxVariant->id,
        'quantity' => 1,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('variant_id')
        ->assertSessionMissing('cart.'.$robuxVariant->id);

    $this->post(route('checkout.direct'), [
        'variant_id' => $robuxVariant->id,
        'quantity' => 1,
    ])->assertSessionHasErrors('variant_id');

    $this->withSession([
        'cart' => [$robuxVariant->id => 1],
    ])->post(route('cart.checkout'), [
        'items' => [$robuxVariant->id => '1'],
    ])->assertSessionHasErrors('items')
        ->assertSessionMissing('checkout');

    $this->withSession([
        'checkout' => [
            'source' => 'direct',
            'items' => [$robuxVariant->id => 1],
            'currencies' => [$robuxVariant->id => 'idr'],
        ],
    ])->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '08123456789',
    ])->assertSessionHasErrors('cart');

    expect($robuxVariant->fresh()->stock)->toBe(5);
    $this->assertDatabaseCount('orders', 0);

    $this->withSession([
        'checkout' => [
            'source' => 'cart',
            'items' => [$robuxVariant->id => 1],
            'currencies' => [$robuxVariant->id => 'idr'],
        ],
    ])->get(route('checkout.show'))
        ->assertRedirect(route('cart.index'))
        ->assertSessionHasErrors('cart')
        ->assertSessionMissing('checkout');

    $this->withSession([
        'cart' => [$regularVariant->id => 1],
    ])->post(route('cart.checkout'), [
        'items' => [$regularVariant->id => '1'],
    ])->assertRedirect(route('checkout.show'));
    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '08123456789',
    ])->assertRedirect();

    expect($regularVariant->fresh()->stock)->toBe(4)
        ->and(Order::query()->sole()->total)->toBe('10000.00');
});

it('blocks only the Bobux delivery type whose transaction switch is off', function (
    string $disabledType,
    string $switch,
    string $enabledType,
) {
    $gameCategory = GameCategory::factory()->create(['name' => 'Example Game']);
    $disabledCategory = ItemCategory::factory()->create([
        'name' => 'Disabled Bobux',
        'bobux_type' => $disabledType,
        'uses_robux' => $disabledType !== 'login',
    ]);
    $enabledCategory = ItemCategory::factory()->create([
        'name' => 'Enabled Bobux',
        'bobux_type' => $enabledType,
        'uses_robux' => $enabledType !== 'login',
    ]);
    $disabledProduct = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $disabledCategory->id,
        'status' => 'active',
    ]);
    $enabledProduct = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $enabledCategory->id,
        'status' => 'active',
    ]);
    $disabledVariant = $disabledProduct->variants()->firstOrFail();
    $disabledVariant->update(['stock' => 3, 'maximum_quantity' => 3]);
    $enabledVariant = $enabledProduct->variants()->firstOrFail();
    $enabledVariant->update(['stock' => 3, 'maximum_quantity' => 3]);
    ProductCurrencyRate::current()->update([$switch => false]);

    $this->get(route('product-details.show', $disabledProduct))
        ->assertSee(__('store.robux_transactions_disabled'));
    $this->withSession(['cart' => []])
        ->postJson(route('cart.items.store'), [
            'variant_id' => $disabledVariant->id,
            'quantity' => 1,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('variant_id')
        ->assertSessionMissing('cart.'.$disabledVariant->id);
    $this->postJson(route('cart.items.store'), [
        'variant_id' => $enabledVariant->id,
        'quantity' => 1,
    ])->assertCreated()
        ->assertSessionHas('cart.'.$enabledVariant->id, 1);
})->with([
    'gift switch' => ['gift_ingame', 'bobux_gift_ingame_enabled', 'gamepass_5_days'],
    'gamepass switch' => ['gamepass_5_days', 'bobux_gamepass_5_days_enabled', 'login'],
    'login switch' => ['login', 'bobux_login_enabled', 'gift_ingame'],
]);

it('checks out only the selected cart products and keeps the other items in the cart', function () {
    $customer = User::factory()->create(['is_admin' => false]);
    $selectedProduct = Product::factory()->create([
        'name' => 'Selected Product',
        'description' => "Account status: ready.\nVerified account with full details.",
        'image' => 'products/selected-product.webp',
        'status' => 'active',
    ]);
    $selectedVariant = $selectedProduct->variants()->firstOrFail();
    $selectedVariant->update(['price' => '12000.00', 'stock' => 4, 'maximum_quantity' => 4]);
    $remainingProduct = Product::factory()->create(['name' => 'Remaining Product', 'status' => 'active']);
    $remainingVariant = $remainingProduct->variants()->firstOrFail();
    $remainingVariant->update(['price' => '30000.00', 'stock' => 5, 'maximum_quantity' => 5]);
    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'BCA transfer',
        'bank_name' => 'BCA',
        'account_name' => 'Example account owner',
        'account_number' => '1234567890',
        'instructions' => 'Transfer the exact amount shown.',
        'is_active' => true,
    ]);
    User::factory()->create(['is_admin' => true, 'email' => 'registered-admin@example.com']);
    Mail::fake();
    PaymentSetting::query()->create([
        'type' => 'e_wallet',
        'name' => 'Inactive wallet',
        'is_active' => false,
    ]);

    $this->actingAs($customer)
        ->withSession(['cart' => [$selectedVariant->id => 2, $remainingVariant->id => 1]])
        ->post(route('cart.checkout'), ['items' => [$selectedVariant->id => '1']])
        ->assertRedirect(route('checkout.show'));

    $this->get(route('checkout.show'))
        ->assertSee('Selected Product')
        ->assertSee(Storage::disk('public')->url('products/selected-product.webp'), false)
        ->assertSee('<details class="checkout-item-description">', false)
        ->assertSee(__('store.description'))
        ->assertSee('Account status: ready.')
        ->assertSee('Verified account with full details.')
        ->assertDontSee('Remaining Product')
        ->assertSee('BCA transfer')
        ->assertDontSee('Example account owner')
        ->assertDontSee('1234567890')
        ->assertDontSee('Transfer the exact amount shown.')
        ->assertDontSee('Inactive wallet');

    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '08123456789',
    ])
        ->assertRedirect()
        ->assertSessionHas('cart', [$remainingVariant->id => 1]);

    $order = Order::query()->with('items')->sole();
    expect($order->items)->toHaveCount(1)
        ->and($order->items->sole()->product_name)->toBe('Selected Product')
        ->and($order->items->sole()->robux_amount)->toBeNull()
        ->and((float) $order->total)->toBe(24000.0)
        ->and($selectedVariant->fresh()->stock)->toBe(2)
        ->and($remainingVariant->fresh()->stock)->toBe(5)
        ->and($order->payment_details['name'])->toBe('BCA transfer');
    Mail::assertNotQueued(PaymentProofReceived::class);
    Mail::assertQueued(BuyerOrderUpdate::class, fn (BuyerOrderUpdate $mail): bool => $mail->order->is($order)
        && $mail->type === BuyerOrderUpdateType::PaymentRequired
        && $mail->hasTo('buyer@example.com'));
    $this->get(route('orders.show', $order))
        ->assertSee('Transfer the exact amount shown.')
        ->assertSee('Rp 24.000')
        ->assertSee(Storage::disk('public')->url('products/selected-product.webp'), false);
});

it('records Bobux value multiplied by quantity as soon as checkout creates an order', function () {
    $gameCategory = GameCategory::factory()->create(['name' => 'Roblox']);
    $robuxCategory = ItemCategory::factory()->create([
        'name' => 'Bobux',
        'uses_robux' => true,
    ]);
    $product = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $robuxCategory->id,
        'status' => 'active',
    ]);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['price' => '400.00', 'stock' => 5, 'maximum_quantity' => 5]);
    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'BCA',
        'is_active' => true,
    ]);
    Mail::fake([BuyerOrderUpdate::class]);

    $this->withSession(['cart' => [$variant->id => 2]])
        ->post(route('cart.checkout'), ['items' => [$variant->id => '1']])
        ->assertRedirect(route('checkout.show'));

    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '08123456789',
    ])->assertRedirect();

    $order = Order::query()->with('items')->sole();
    expect($order->items->sole()->robux_amount)->toBe('800.00');
    $this->get(route('home'))
        ->assertSee('800 Bobux')
        ->assertSee(__('store.items_sold'))
        ->assertSee('0');
    Mail::assertQueued(BuyerOrderUpdate::class, fn (BuyerOrderUpdate $mail): bool => $mail->order->is($order)
        && $mail->type === BuyerOrderUpdateType::PaymentRequired
        && $mail->hasTo('buyer@example.com'));

    $order->update(['status' => 'completed']);
    $this->get(route('home'))
        ->assertSee('800 Bobux')
        ->assertSee('2');
});

it('sends a product detail buy-now request to checkout with only that product', function () {
    $gameCategory = GameCategory::factory()->create(['name' => 'Roblox']);
    $product = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'name' => 'Direct Product',
        'status' => 'active',
    ]);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['price' => '15000.00', 'stock' => 3, 'maximum_quantity' => 3]);
    $otherProduct = Product::factory()->create(['name' => 'Already in cart', 'status' => 'active']);
    $otherVariant = $otherProduct->variants()->firstOrFail();
    $otherVariant->update(['stock' => 2, 'maximum_quantity' => 2]);
    ProductCurrencyRate::current()->update([
        'idr_per_myr' => '1000.0000',
        'idr_per_usd' => '2000.0000',
    ]);
    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'e_wallet',
        'name' => 'Dana',
        'currency' => 'usd',
        'account_number' => '08123456789',
        'is_active' => true,
    ]);
    Mail::fake();

    $this->get(route('product-details.show', $product))
        ->assertSee('formaction="'.route('checkout.direct').'"', false)
        ->assertSee('class="fa fa-shopping-cart"', false)
        ->assertDontSee('data-cart-button-label', false);

    $this->withSession(['cart' => [$otherVariant->id => 1]])
        ->post(route('checkout.direct'), [
            'variant_id' => $variant->id,
            'quantity' => 1,
            'currency' => 'usd',
            'purchase_details' => [
                'game_username' => 'roblox-player',
                'game_password' => 'PrivateRobloxPassword',
            ],
            'seller_note' => 'Please deliver this after 6 PM.',
        ])
        ->assertRedirect(route('checkout.show'));

    $this->get(route('checkout.show'))
        ->assertSee('Direct Product')
        ->assertDontSee('Already in cart')
        ->assertSee('$ 7,50')
        ->assertSee(__('store.checkout_email_required_note'));

    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'guest@example.com',
        'customer_phone' => '08123456789',
    ])
        ->assertRedirect()
        ->assertSessionHas('cart', [$otherVariant->id => 1]);

    $order = Order::query()->with('items')->sole();
    $item = $order->items->sole();
    expect($item->product_name)->toBe('Direct Product')
        ->and($item->customer_details['purchase_details'])->toBe([
            'game_username' => 'roblox-player',
            'game_password' => 'PrivateRobloxPassword',
        ])
        ->and($item->customer_details['seller_note'])->toBe('Please deliver this after 6 PM.');
    expect($order->user_id)->toBeNull()
        ->and($order->contact_email)->toBe('guest@example.com')
        ->and($order->contact_phone)->toBe('08123456789')
        ->and((float) $order->total)->toBe(15000.0);
    Mail::assertNotQueued(PaymentProofReceived::class);
    Mail::assertQueued(BuyerOrderUpdate::class, fn (BuyerOrderUpdate $mail): bool => $mail->order->is($order)
        && $mail->type === BuyerOrderUpdateType::PaymentRequired
        && $mail->hasTo('guest@example.com'));
    $this->get(route('orders.show', $order))
        ->assertSee('Dana')
        ->assertDontSee('USD')
        ->assertSee('$ 7,50')
        ->assertSee('guest@example.com')
        ->assertSee('08123456789');

    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)
        ->get(route('admin.orders.show', $order))
        ->assertSee('Please deliver this after 6 PM.');
});

it('converts Bobux category prices to IDR across product listing and checkout', function () {
    $gameCategory = GameCategory::factory()->create(['name' => 'Fisch']);
    $itemCategory = ItemCategory::factory()->create([
        'name' => 'Gamepass',
        'uses_robux' => true,
    ]);
    $gameCategory->itemCategories()->attach($itemCategory);
    $product = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
        'name' => 'Fisch Gamepass',
        'status' => 'active',
    ]);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['price' => '100.00', 'stock' => 3, 'maximum_quantity' => 3]);
    ProductCurrencyRate::current()->update(['idr_per_robux' => '120.0000']);
    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'BCA',
        'bank_name' => 'BCA',
        'account_name' => 'Meoxstore',
        'account_number' => '1234567890',
        'currency' => 'idr',
        'is_active' => true,
    ]);
    Mail::fake();

    $this->get(route('product-details.show', $product))
        ->assertSee('Rp 12,000');
    $this->get(route('shop'))
        ->assertSee('Rp 12,000');
    $this->withSession(['cart' => [$variant->id => 1]])
        ->get(route('cart.index'))
        ->assertSee('Rp 12.000');

    $this->withSession(['cart' => [$variant->id => 1]])
        ->post(route('cart.checkout'), ['items' => [$variant->id => '1']])
        ->assertRedirect(route('checkout.show'));
    $this->get(route('checkout.show'))
        ->assertSee('Rp 12.000');
    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'robux-buyer@example.com',
        'customer_phone' => '08123456789',
    ])->assertRedirect();

    $order = Order::query()->with('items')->sole();
    expect((float) $order->total)->toBe(12000.0)
        ->and((float) $order->items->sole()->unit_price)->toBe(12000.0);

    ProductCurrencyRate::current()->update(['idr_per_robux' => '150.0000']);
    $this->get(route('product-details.show', $product))
        ->assertSee('Rp 15,000');
    $this->get(route('orders.show', $order))
        ->assertSee('Rp 12.000');
});

it('uses the separate Gamepass rate and records the purchased Bobux amount', function () {
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create([
        'bobux_type' => 'gamepass_5_days',
        'uses_robux' => true,
    ]);
    $product = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
        'status' => 'active',
    ]);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['price' => '100.00', 'stock' => 3, 'maximum_quantity' => 3]);
    ProductCurrencyRate::current()->update([
        'idr_per_robux' => '120.0000',
        'idr_per_gamepass_bobux' => '150.0000',
    ]);
    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'BCA',
        'is_active' => true,
    ]);
    Mail::fake([BuyerOrderUpdate::class]);

    $this->get(route('product-details.show', $product))->assertSee('Rp 15,000');
    $this->withSession(['cart' => [$variant->id => 2]])
        ->post(route('cart.checkout'), ['items' => [$variant->id => '1']])
        ->assertRedirect(route('checkout.show'));
    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'gamepass-buyer@example.com',
        'customer_phone' => '08123456789',
    ])->assertRedirect();

    $order = Order::query()->with('items')->sole();
    expect((float) $order->total)->toBe(30000.0)
        ->and($order->items->sole()->robux_amount)->toBe('200.00');
});

it('uses manual Rupiah pricing and records the manual Bobux amount for login products', function () {
    $gameCategory = GameCategory::factory()->create(['name' => 'Roblox']);
    $itemCategory = ItemCategory::factory()->create([
        'bobux_type' => 'login',
        'uses_robux' => false,
    ]);
    $product = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
        'status' => 'active',
    ]);
    $variant = $product->variants()->firstOrFail();
    $variant->update([
        'price' => '25000.00',
        'bobux_amount' => '1200.00',
        'stock' => 3,
        'maximum_quantity' => 3,
    ]);
    ProductCurrencyRate::current()->update(['idr_per_robux' => '999.0000']);
    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'BCA',
        'is_active' => true,
    ]);
    Mail::fake([BuyerOrderUpdate::class]);

    $this->get(route('product-details.show', $product))
        ->assertSee('Rp 25,000')
        ->assertSee('1,200 Bobux');
    $this->withSession(['cart' => [$variant->id => 2]])
        ->post(route('cart.checkout'), ['items' => [$variant->id => '1']])
        ->assertRedirect(route('checkout.show'));
    $this->get(route('checkout.show'))->assertSee('Rp 50.000');
    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'login-buyer@example.com',
        'customer_phone' => '08123456789',
    ])->assertRedirect();

    $order = Order::query()->with('items')->sole();
    expect((float) $order->total)->toBe(50000.0)
        ->and($order->items->sole()->robux_amount)->toBe('2400.00');
    $this->get(route('home'))->assertSee('2,4K Bobux');
});

it('saves and updates a seller note from the cart details form', function () {
    $product = Product::factory()->create([
        'status' => 'active',
        'purchase_fields' => [],
    ]);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['stock' => 2, 'maximum_quantity' => 2]);

    $this->post(route('cart.items.store'), [
        'variant_id' => $variant->id,
        'quantity' => 1,
        'seller_note' => 'Initial note',
    ])->assertRedirect(route('cart.index'));

    $this->get(route('cart.index'))
        ->assertSee('Initial note')
        ->assertSee('name="seller_note"', false);

    $this->patch(route('cart.items.details.update', $variant), [
        'seller_note' => 'Updated delivery note',
    ])->assertRedirect(route('cart.index'));

    $this->get(route('cart.index'))->assertSee('Updated delivery note');

    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'Note payment bank',
        'is_active' => true,
    ]);
    $this->post(route('cart.checkout'), [
        'items' => [$variant->id => '1'],
    ])->assertRedirect(route('checkout.show'));
    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '08123456789',
    ])->assertRedirect();

    expect(Order::query()->with('items')->sole()->items->sole()->customer_details['seller_note'])
        ->toBe('Updated delivery note');
});

it('preserves the selected shop currency when opening product details', function () {
    $product = Product::factory()->create(['status' => 'active']);

    $this->get(route('shop', ['currency' => 'myr']))
        ->assertSee('data-shop-currency="myr" aria-pressed="true"', false)
        ->assertSee(route('product-details.show', ['product' => $product, 'currency' => 'myr']), false);

    $this->get(route('product-details.show', ['product' => $product, 'currency' => 'myr']))
        ->assertSee('data-product-currency="myr" aria-pressed="true"', false)
        ->assertSee('name="currency" value="myr"', false)
        ->assertSee('data-product-price aria-live="polite">RM', false);
});

it('shows checkout prices in the currency selected on the product page', function (string $currency, string $expectedPrice) {
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['price' => '15000.00', 'stock' => 2, 'maximum_quantity' => 2]);
    ProductCurrencyRate::current()->update([
        'idr_per_myr' => '1000.0000',
        'idr_per_usd' => '2000.0000',
    ]);

    $this->post(route('checkout.direct'), [
        'variant_id' => $variant->id,
        'quantity' => 1,
        'currency' => $currency,
    ])->assertRedirect(route('checkout.show'));

    $this->get(route('checkout.show'))->assertSee($expectedPrice);
})->with([
    'Indonesian rupiah' => ['idr', 'Rp 15.000'],
    'Malaysian ringgit' => ['myr', 'RM 15,00'],
    'US dollars' => ['usd', '$ 7,50'],
]);

it('rounds cart, checkout, order, and payment email amounts to five-cent increments', function (
    string $currency,
    string $idrPrice,
    float $expectedPaymentAmount,
    string $expectedDisplay,
) {
    ProductCurrencyRate::current()->update([
        'idr_per_myr' => '1000.0000',
        'idr_per_usd' => '1000.0000',
    ]);
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['price' => $idrPrice, 'stock' => 2, 'maximum_quantity' => 2]);
    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'e_wallet',
        'name' => 'Currency wallet',
        'currency' => $currency,
        'is_active' => true,
    ]);
    Mail::fake();

    $this->post(route('cart.items.store'), [
        'variant_id' => $variant->id,
        'quantity' => 1,
        'currency' => $currency,
    ])->assertRedirect(route('cart.index'));

    $this->get(route('cart.index'))->assertSee($expectedDisplay);

    $this->post(route('cart.checkout'), [
        'items' => [$variant->id => '1'],
    ])->assertRedirect(route('checkout.show'));

    $this->get(route('checkout.show'))->assertSee($expectedDisplay);

    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '08123456789',
    ])->assertRedirect();

    $order = Order::query()->with('items')->sole();
    expect($order->total)->toBe($idrPrice)
        ->and((float) $order->payment_details['payment_amount'])->toBe($expectedPaymentAmount)
        ->and($order->items->sole()->unit_price)->toBe($idrPrice);

    $this->get(route('orders.show', $order))->assertSee($expectedDisplay);

    (new BuyerOrderUpdate($order, BuyerOrderUpdateType::PaymentRequired))
        ->assertSeeInHtml($expectedDisplay);
    (new PaymentProofReceived($order))
        ->assertSeeInHtml($expectedDisplay);
})->with([
    'RM 11.12 rounds down' => ['myr', '11120.00', 11.10, 'RM 11,10'],
    'RM 11.13 rounds up' => ['myr', '11130.00', 11.15, 'RM 11,15'],
    'USD 11.12 rounds down' => ['usd', '11120.00', 11.10, '$ 11,10'],
    'USD 11.13 rounds up' => ['usd', '11130.00', 11.15, '$ 11,15'],
]);

it('keeps the selected currency for cart items sent to checkout', function () {
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['price' => '15000.00', 'stock' => 2, 'maximum_quantity' => 2]);
    ProductCurrencyRate::current()->update([
        'idr_per_myr' => '1000.0000',
        'idr_per_usd' => '2000.0000',
    ]);

    $this->post(route('cart.items.store'), [
        'variant_id' => $variant->id,
        'quantity' => 1,
        'currency' => 'myr',
    ])->assertRedirect(route('cart.index'));

    $this->post(route('cart.checkout'), [
        'items' => [$variant->id => '1'],
    ])->assertRedirect(route('checkout.show'));

    $this->get(route('checkout.show'))
        ->assertSee('RM 15,00');
});

it('uses the latest selected currency from cart through checkout', function () {
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['price' => '15000.00', 'stock' => 2, 'maximum_quantity' => 2]);
    ProductCurrencyRate::current()->update([
        'idr_per_myr' => '1000.0000',
        'idr_per_usd' => '2000.0000',
    ]);
    PaymentSetting::query()->create([
        'type' => 'e_wallet',
        'name' => 'USD wallet',
        'currency' => 'usd',
        'is_active' => true,
    ]);

    $this->post(route('cart.items.store'), [
        'variant_id' => $variant->id,
        'quantity' => 1,
        'currency' => 'idr',
    ])->assertRedirect(route('cart.index'));

    $this->get(route('cart.index', ['currency' => 'usd']))
        ->assertSee('$ 7,50');

    $this->post(route('cart.checkout'), [
        'items' => [$variant->id => '1'],
        'currency' => 'usd',
    ])->assertRedirect(route('checkout.show'));

    $this->get(route('checkout.show'))
        ->assertSee('$ 7,50')
        ->assertSee('USD wallet')
        ->assertDontSee('Rp 15.000');
});

it('filters checkout payments by currency and rejects a payment in another currency', function () {
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['price' => '15000.00', 'stock' => 2, 'maximum_quantity' => 2]);
    $usdPayment = PaymentSetting::query()->create([
        'type' => 'e_wallet',
        'name' => 'USD wallet',
        'currency' => 'usd',
        'is_active' => true,
    ]);
    $idrPayment = PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'IDR bank',
        'currency' => 'idr',
        'is_active' => true,
    ]);

    $this->post(route('checkout.direct'), [
        'variant_id' => $variant->id,
        'quantity' => 1,
        'currency' => 'usd',
    ])->assertRedirect(route('checkout.show'));

    $this->get(route('checkout.show'))
        ->assertSee('USD wallet')
        ->assertDontSee('IDR bank');

    $this->post(route('checkout.store'), [
        'payment_method_id' => $idrPayment->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '08123456789',
    ])->assertSessionHasErrors('payment_method_id');

    expect(Order::query()->count())->toBe(0)
        ->and($variant->fresh()->stock)->toBe(2);

    $this->post(route('checkout.store'), [
        'payment_method_id' => $usdPayment->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '08123456789',
    ])->assertRedirect();

    expect(Order::query()->sole()->payment_details['currency'])->toBe('usd');
});

it('uses the latest added product currency for all cart prices and checkout payments', function () {
    $firstProduct = Product::factory()->create(['name' => 'First product', 'status' => 'active']);
    $firstVariant = $firstProduct->variants()->firstOrFail();
    $firstVariant->update(['price' => '15000.00', 'stock' => 2, 'maximum_quantity' => 2]);
    $secondProduct = Product::factory()->create(['name' => 'Second product', 'status' => 'active']);
    $secondVariant = $secondProduct->variants()->firstOrFail();
    $secondVariant->update(['price' => '30000.00', 'stock' => 2, 'maximum_quantity' => 2]);
    ProductCurrencyRate::current()->update([
        'idr_per_myr' => '1000.0000',
        'idr_per_usd' => '2000.0000',
    ]);
    $usdPayment = PaymentSetting::query()->create([
        'type' => 'e_wallet',
        'name' => 'USD wallet',
        'currency' => 'usd',
        'is_active' => true,
    ]);
    PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'IDR bank',
        'currency' => 'idr',
        'is_active' => true,
    ]);

    $this->post(route('cart.items.store'), [
        'variant_id' => $firstVariant->id,
        'quantity' => 2,
        'currency' => 'idr',
    ])->assertRedirect(route('cart.index'));

    $this->post(route('cart.items.store'), [
        'variant_id' => $secondVariant->id,
        'quantity' => 1,
        'currency' => 'usd',
    ])->assertRedirect(route('cart.index'));

    $this->get(route('cart.index'))
        ->assertSee('First product')
        ->assertSee('Second product')
        ->assertSee('$ 7,50')
        ->assertSee(__('store.unit_price'))
        ->assertSee(__('store.subtotal').': $ 15,00')
        ->assertSee('$ 30,00')
        ->assertDontSee('Rp 15.000');

    $this->post(route('cart.checkout'), [
        'items' => [$firstVariant->id => '1', $secondVariant->id => '1'],
    ])->assertRedirect(route('checkout.show'));

    $this->get(route('checkout.show'))
        ->assertSee('USD wallet')
        ->assertDontSee('IDR bank')
        ->assertSee('$ 30,00');

    $this->post(route('checkout.store'), [
        'payment_method_id' => $usdPayment->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '08123456789',
    ])->assertRedirect();

    expect(Order::query()->sole()->payment_details['currency'])->toBe('usd');
});

it('requires guest checkout contact details and creates a guest-accessible payment page', function () {
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['stock' => 2, 'maximum_quantity' => 2]);
    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'Guest Bank',
        'bank_name' => 'Guest Bank',
        'account_number' => '987654321',
        'is_active' => true,
    ]);
    $checkout = ['source' => 'direct', 'items' => [$variant->id => 1]];

    $this->withSession(['checkout' => $checkout])
        ->get(route('checkout.show'))
        ->assertSee('name="customer_email"', false)
        ->assertSee('name="customer_phone"', false)
        ->assertSee('Guest Bank')
        ->assertSee(__('store.checkout_contact_note'));

    $this->post(route('checkout.store'), ['payment_method_id' => $paymentSetting->id])
        ->assertInvalid([
            'customer_email' => 'The customer email field is required.',
            'customer_phone' => 'The customer phone field is required.',
        ]);
    expect(Order::query()->count())->toBe(0);

    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'guest@example.com',
        'customer_phone' => 'not a phone',
    ])->assertInvalid([
        'customer_phone' => __('store.checkout_phone_invalid'),
    ]);

    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'guest@example.com',
        'customer_phone' => '08123456789',
    ])->assertRedirect(route('orders.show', 1));

    $order = Order::query()->with('items')->sole();
    expect($order->user_id)->toBeNull()
        ->and($order->contact_email)->toBe('guest@example.com')
        ->and($order->contact_phone)->toBe('08123456789')
        ->and($order->items->sole()->customer_details)->toBe([
            'contact_email' => 'guest@example.com',
            'contact_phone' => '08123456789',
        ]);
    $this->get(route('orders.show', $order))
        ->assertSee('Guest Bank')
        ->assertSee('987654321')
        ->assertSee('guest@example.com');

    $this->app['session']->flush();
    $this->get(route('orders.show', $order))->assertNotFound();
});

it('allows administrators to create, update, list, and delete payment methods', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    Storage::fake('public');

    $this->actingAs($admin)
        ->get(route('admin.payment-settings.index'))
        ->assertSee('Payment Methods');
    $this->get(route('admin.payment-settings.create'))
        ->assertSee('id="is_active" name="is_active" type="checkbox" value="1" checked', false)
        ->assertDontSee('Payment type')
        ->assertDontSee('Bank / provider');

    $this->post(route('admin.payment-settings.store'), [
        'type' => 'bank_transfer',
        'currency' => 'idr',
        'name' => 'Bank Example',
        'bank_name' => 'Example Bank',
        'account_name' => 'Meoxstore',
        'account_number' => '1234567890',
        'instructions' => 'Include the order number.',
        'is_active' => '1',
    ])->assertRedirect(route('admin.payment-settings.index'));

    $paymentSetting = PaymentSetting::query()->sole();
    $this->assertDatabaseHas('payment_settings', [
        'id' => $paymentSetting->id,
        'name' => 'Bank Example',
        'type' => 'bank_transfer',
        'is_active' => true,
    ]);

    $this->put(route('admin.payment-settings.update', $paymentSetting), [
        'type' => 'qris',
        'currency' => 'usd',
        'name' => 'Store QRIS',
        'bank_name' => 'QRIS',
        'account_name' => '',
        'account_number' => 'QR-123',
        'qr_code' => UploadedFile::fake()->createWithContent(
            'store-qris.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true),
        ),
        'instructions' => 'Scan this QR code.',
        'is_active' => '1',
    ])->assertRedirect(route('admin.payment-settings.index'));

    $this->get(route('admin.payment-settings.index'))
        ->assertSee('Store QRIS')
        ->assertSee('QRIS')
        ->assertSee('USD');

    $this->put(route('admin.payment-settings.update', $paymentSetting), [
        'type' => 'qris',
        'currency' => 'usd',
        'name' => 'Store QRIS',
        'bank_name' => 'QRIS',
        'account_number' => 'QR-123',
        'instructions' => 'Scan this QR code.',
        'is_active_present' => '1',
    ])->assertRedirect(route('admin.payment-settings.index'));
    $this->assertDatabaseHas('payment_settings', [
        'id' => $paymentSetting->id,
        'is_active' => false,
    ]);

    $this->delete(route('admin.payment-settings.destroy', $paymentSetting))
        ->assertRedirect(route('admin.payment-settings.index'));
    $this->assertDatabaseMissing('payment_settings', ['id' => $paymentSetting->id]);

    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->get(route('admin.payment-settings.index'))
        ->assertForbidden();
});

it('rejects checkout when a submitted payment method has been deactivated', function () {
    $customer = User::factory()->create(['is_admin' => false]);
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['stock' => 2, 'maximum_quantity' => 2]);
    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'Inactive bank',
        'is_active' => false,
    ]);

    $this->actingAs($customer)
        ->withSession([
            'checkout' => ['source' => 'direct', 'items' => [$variant->id => 1]],
        ])
        ->post(route('checkout.store'), [
            'payment_method_id' => $paymentSetting->id,
            'customer_email' => 'buyer@example.com',
            'customer_phone' => '08123456789',
        ])
        ->assertSessionHasErrors('payment_method_id');

    expect(Order::query()->count())->toBe(0)
        ->and($variant->fresh()->stock)->toBe(2);
});

it('lets administrators apply bulk actions to selected payment methods', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $firstPayment = PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'First method',
        'is_active' => true,
    ]);
    $secondPayment = PaymentSetting::query()->create([
        'type' => 'e_wallet',
        'name' => 'Second method',
        'is_active' => true,
    ]);
    $thirdPayment = PaymentSetting::query()->create([
        'type' => 'qris',
        'name' => 'Third method',
        'is_active' => false,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.payment-settings.index'))
        ->assertSee('name="ids[]"', false)
        ->assertSee(route('admin.payment-settings.bulk'), false);
    $this->post(route('admin.payment-settings.bulk'), [
        'ids' => [$firstPayment->id, $secondPayment->id],
        'action' => 'deactivate',
    ])->assertRedirect(route('admin.payment-settings.index'));
    $this->assertDatabaseHas('payment_settings', ['id' => $firstPayment->id, 'is_active' => false]);
    $this->assertDatabaseHas('payment_settings', ['id' => $secondPayment->id, 'is_active' => false]);
    $this->assertDatabaseHas('payment_settings', ['id' => $thirdPayment->id, 'is_active' => false]);

    $this->post(route('admin.payment-settings.bulk'), [
        'ids' => [$firstPayment->id, $thirdPayment->id],
        'action' => 'activate',
    ])->assertRedirect(route('admin.payment-settings.index'));
    $this->assertDatabaseHas('payment_settings', ['id' => $firstPayment->id, 'is_active' => true]);
    $this->assertDatabaseHas('payment_settings', ['id' => $thirdPayment->id, 'is_active' => true]);

    $this->post(route('admin.payment-settings.bulk'), [
        'ids' => [$secondPayment->id, $thirdPayment->id],
        'action' => 'delete',
    ])->assertRedirect(route('admin.payment-settings.index'));
    $this->assertDatabaseMissing('payment_settings', ['id' => $secondPayment->id]);
    $this->assertDatabaseMissing('payment_settings', ['id' => $thirdPayment->id]);
    $this->assertDatabaseHas('payment_settings', ['id' => $firstPayment->id]);
});

it('rejects bulk payment actions when no methods are selected', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $payment = PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'Existing method',
        'is_active' => false,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.payment-settings.bulk'), ['action' => 'activate'])
        ->assertSessionHasErrors('ids');

    $this->assertDatabaseHas('payment_settings', ['id' => $payment->id, 'is_active' => false]);
});
