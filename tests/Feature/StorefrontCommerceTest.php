<?php

use App\Models\GameCategory;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;

uses(RefreshDatabase::class);

it('keeps guest cart items and checks out them together after sign in', function () {
    $firstProduct = Product::factory()->create(['status' => 'active']);
    $firstVariant = $firstProduct->variants()->firstOrFail();
    $firstVariant->update(['stock' => 5, 'maximum_quantity' => 5, 'price' => '10000.00']);
    $secondProduct = Product::factory()->create(['status' => 'active']);
    $secondVariant = $secondProduct->variants()->firstOrFail();
    $secondVariant->update(['stock' => 4, 'maximum_quantity' => 4, 'price' => '20000.00']);

    $this->post(route('cart.items.store'), ['variant_id' => $firstVariant->id, 'quantity' => 2])
        ->assertRedirect(route('cart.index'))
        ->assertSessionHas('cart.'.$firstVariant->id, 2);
    $this->post(route('cart.items.store'), ['variant_id' => $secondVariant->id, 'quantity' => 1])
        ->assertRedirect(route('cart.index'))
        ->assertSessionHas('cart.'.$secondVariant->id, 1);
    $this->get(route('cart.index'))
        ->assertSee(__('store.select_all_products'))
        ->assertSee(__('store.cart_summary'))
        ->assertSee('data-cart-select-all', false)
        ->assertSee('class="cart-item-details"', false)
        ->assertSee('<summary>'.__('store.purchase_details').'</summary>', false)
        ->assertSee('data-selected-total', false);
    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'BCA',
        'bank_name' => 'BCA',
        'account_number' => '1234567890',
        'is_active' => true,
    ]);

    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->post(route('cart.checkout'), [
            'items' => [
                $firstVariant->id => '1',
                $secondVariant->id => '1',
            ],
        ])
        ->assertRedirect(route('checkout.show'));
    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '08123456789',
    ])
        ->assertRedirect()
        ->assertSessionMissing('cart');

    $order = Order::query()->with('items')->sole();
    expect($order->status)->toBe('pending')
        ->and((float) $order->total)->toBe(40000.0)
        ->and($order->items)->toHaveCount(2)
        ->and($firstVariant->fresh()->stock)->toBe(3)
        ->and($secondVariant->fresh()->stock)->toBe(3);
});

it('removes only selected cart items and their saved details', function () {
    $firstProduct = Product::factory()->create(['status' => 'active']);
    $firstVariant = $firstProduct->variants()->firstOrFail();
    $firstVariant->update(['stock' => 2, 'maximum_quantity' => 2]);
    $secondProduct = Product::factory()->create(['status' => 'active']);
    $secondVariant = $secondProduct->variants()->firstOrFail();
    $secondVariant->update(['stock' => 2, 'maximum_quantity' => 2]);

    $this->post(route('cart.items.store'), [
        'variant_id' => $firstVariant->id,
        'quantity' => 1,
        'seller_note' => 'Remove me',
    ])->assertRedirect(route('cart.index'));
    $this->post(route('cart.items.store'), [
        'variant_id' => $secondVariant->id,
        'quantity' => 1,
    ])->assertRedirect(route('cart.index'));

    $this->post(route('cart.items.selected.destroy'), [
        'items' => [$firstVariant->id => '1'],
    ])->assertRedirect(route('cart.index'))
        ->assertSessionHas('cart', [$secondVariant->id => 1]);

    $this->get(route('cart.index'))
        ->assertDontSee('Remove me')
        ->assertSee($secondProduct->name);
});

it('adds a cart item through JSON without redirecting away from the current page', function () {
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['stock' => 5, 'minimum_quantity' => 1, 'maximum_quantity' => 5]);

    $this->postJson(route('cart.items.store'), [
        'variant_id' => $variant->id,
        'quantity' => 2,
    ])
        ->assertCreated()
        ->assertJsonPath('message', 'Produk ditambahkan ke keranjang.')
        ->assertJsonPath('cart_count', 2)
        ->assertSessionHas('cart.'.$variant->id, 2);
});

it('returns a stock-specific message when a cart quantity exceeds available stock', function () {
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['stock' => 1, 'minimum_quantity' => 1, 'maximum_quantity' => 5]);

    $this->postJson(route('cart.items.store'), [
        'variant_id' => $variant->id,
        'quantity' => 2,
    ])
        ->assertUnprocessable()
        ->assertJsonPath('errors.quantity.0', 'Stok tidak cukup.')
        ->assertSessionMissing('cart.'.$variant->id);
});

it('shows a stock-specific message when quantity in the cart exceeds stock', function () {
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['stock' => 1, 'minimum_quantity' => 1, 'maximum_quantity' => 1]);

    $this->withSession(['cart' => [$variant->id => 1]])
        ->get(route('cart.index'))
        ->assertSee('data-stock="1"', false)
        ->assertSee('data-stock-error-label="Stok tidak cukup."', false);

    $this->from(route('cart.index'))
        ->patch(route('cart.items.update', $variant), ['quantity' => 2])
        ->assertRedirect(route('cart.index'))
        ->assertSessionHasErrors(['quantity' => 'Stok tidak cukup.'])
        ->assertSessionHas('cart.'.$variant->id, 1);
});

it('updates cart quantity through JSON and does not save quantities above stock', function () {
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['stock' => 3, 'minimum_quantity' => 1, 'maximum_quantity' => 3]);

    $this->withSession(['cart' => [$variant->id => 1]])
        ->patchJson(route('cart.items.update', $variant), ['quantity' => 3])
        ->assertOk()
        ->assertJsonPath('quantity', 3)
        ->assertSessionHas('cart.'.$variant->id, 3);

    $this->patchJson(route('cart.items.update', $variant), ['quantity' => 4])
        ->assertUnprocessable()
        ->assertJsonPath('errors.quantity.0', 'Stok tidak cukup.')
        ->assertSessionHas('cart.'.$variant->id, 3);
});

it('preserves one cart item quantity while another item is updated', function () {
    $firstProduct = Product::factory()->create(['status' => 'active']);
    $firstVariant = $firstProduct->variants()->firstOrFail();
    $firstVariant->update(['stock' => 5, 'minimum_quantity' => 1, 'maximum_quantity' => 5]);
    $secondProduct = Product::factory()->create(['status' => 'active']);
    $secondVariant = $secondProduct->variants()->firstOrFail();
    $secondVariant->update(['stock' => 5, 'minimum_quantity' => 1, 'maximum_quantity' => 5]);

    $this->withSession(['cart' => [$firstVariant->id => 1, $secondVariant->id => 1]])
        ->patchJson(route('cart.items.update', $firstVariant), ['quantity' => 3])
        ->assertOk();
    $this->patchJson(route('cart.items.update', $firstVariant), ['quantity' => 2])
        ->assertOk()
        ->assertSessionHas('cart.'.$firstVariant->id, 2);
    $this->patchJson(route('cart.items.update', $secondVariant), ['quantity' => 2])
        ->assertOk()
        ->assertSessionHas('cart', [
            $firstVariant->id => 2,
            $secondVariant->id => 2,
        ]);
});

it('requires the account fields for each supported game when adding a cart item', function (string $gameName, array $purchaseDetails, string $missingField) {
    $gameCategory = GameCategory::factory()->create(['name' => $gameName]);
    $product = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'status' => 'active',
    ]);
    $variant = $product->variants()->firstOrFail();

    $this->postJson(route('cart.items.store'), [
        'variant_id' => $variant->id,
        'quantity' => 1,
        ...($purchaseDetails === [] ? [] : ['purchase_details' => $purchaseDetails]),
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors($missingField)
        ->assertSessionMissing('cart.'.$variant->id);
})->with([
    'Mobile Legends requires player ID and server' => ['Mobile Legends', ['game_id' => '12345'], 'purchase_details.game_server'],
    'Blox Fruit requires username' => ['Blox Fruit', [], 'purchase_details.game_username'],
    'Roblox requires username and password' => ['Roblox', ['game_username' => 'player123'], 'purchase_details.game_password'],
]);

it('uses the buyer contact details saved in their profile at checkout', function () {
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['stock' => 5, 'maximum_quantity' => 5]);

    $this->postJson(route('cart.items.store'), [
        'variant_id' => $variant->id,
        'quantity' => 1,
    ])
        ->assertCreated()
        ->assertSessionHas('cart.'.$variant->id, 1);

    $buyer = User::factory()->create([
        'email' => 'profile@example.com',
        'phone' => '08123456789',
        'is_admin' => false,
    ]);
    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'BCA',
        'is_active' => true,
    ]);
    $this->actingAs($buyer)
        ->post(route('cart.checkout'), ['items' => [$variant->id => '1']])
        ->assertRedirect(route('checkout.show'));
    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'profile@example.com',
        'customer_phone' => '08123456789',
    ])->assertRedirect();

    expect(Order::query()->with('items')->sole()->items->sole()->customer_details)->toBe([
        'contact_email' => 'profile@example.com',
        'contact_phone' => '08123456789',
    ]);
});

it('encrypts Roblox purchase details and exposes them only on the admin order page', function () {
    $gameCategory = GameCategory::factory()->create(['name' => 'Roblox']);
    $product = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'status' => 'active',
    ]);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['stock' => 5, 'maximum_quantity' => 5]);
    $password = 'SecretRobloxPassword!42';

    $this->postJson(route('cart.items.store'), [
        'variant_id' => $variant->id,
        'quantity' => 1,
        'contact_email' => 'player@example.com',
        'purchase_details' => [
            'game_username' => 'player123',
            'game_password' => $password,
        ],
    ])->assertCreated();

    $rawSessionDetails = session('cart_details')[$variant->id];
    expect($rawSessionDetails)->not->toContain($password);

    $buyer = User::factory()->create([
        'email' => 'player@example.com',
        'phone' => '08123456789',
        'is_admin' => false,
    ]);
    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'BCA',
        'is_active' => true,
    ]);
    $this->actingAs($buyer)
        ->post(route('cart.checkout'), ['items' => [$variant->id => '1']])
        ->assertRedirect(route('checkout.show'));
    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'player@example.com',
        'customer_phone' => '08123456789',
    ])->assertRedirect();

    $order = Order::query()->with('items')->sole();
    $item = $order->items->sole();
    $rawDetails = $item->getRawOriginal('customer_details');
    expect($rawDetails)->not->toContain($password)
        ->and($item->customer_details)->toBe([
            'contact_email' => 'player@example.com',
            'contact_phone' => '08123456789',
            'purchase_details' => [
                'game_username' => 'player123',
                'game_password' => $password,
            ],
        ]);

    $this->actingAs($buyer)
        ->get(route('orders.show', $order))
        ->assertOk()
        ->assertDontSee($password);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee($password)
        ->assertSee('player123')
        ->assertSee('player@example.com');
});

it('shows and edits only cart account details without exposing the saved password', function () {
    $gameCategory = GameCategory::factory()->create(['name' => 'Roblox']);
    $product = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'status' => 'active',
    ]);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['stock' => 5, 'maximum_quantity' => 5]);
    $password = 'SecretRobloxPassword!42';

    $this->postJson(route('cart.items.store'), [
        'variant_id' => $variant->id,
        'quantity' => 1,
        'contact_phone' => '08123456789',
        'contact_email' => 'buyer@example.com',
        'purchase_details' => [
            'game_username' => 'old-player',
            'game_password' => $password,
        ],
    ])->assertCreated();

    $this->get(route('cart.index'))
        ->assertSee('old-player')
        ->assertDontSee('name="contact_email"', false)
        ->assertDontSee('name="contact_phone"', false)
        ->assertSee('name="purchase_details[game_username]"', false)
        ->assertDontSee($password);

    $this->patch(route('cart.items.details.update', $variant), [
        'purchase_details' => [
            'game_username' => 'new-player',
            'game_password' => '',
        ],
    ])->assertRedirect(route('cart.index'))
        ->assertSessionHas('success', __('store.cart_details_updated'));

    $rawDetails = session('cart_details')[$variant->id];
    expect($rawDetails)->not->toContain($password)
        ->and(json_decode(Crypt::decryptString($rawDetails), true))->toBe([
            'purchase_details' => [
                'game_username' => 'new-player',
                'game_password' => $password,
            ],
        ]);

    $buyer = User::factory()->create([
        'email' => 'profile-buyer@example.com',
        'phone' => '0811222333',
        'is_admin' => false,
    ]);
    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'BCA',
        'is_active' => true,
    ]);
    $this->actingAs($buyer)
        ->post(route('cart.checkout'), ['items' => [$variant->id => '1']])
        ->assertRedirect(route('checkout.show'));
    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'profile-buyer@example.com',
        'customer_phone' => '0811222333',
    ])->assertRedirect();

    expect(Order::query()->with('items')->sole()->items->sole()->customer_details)->toBe([
        'contact_email' => 'profile-buyer@example.com',
        'contact_phone' => '0811222333',
        'purchase_details' => [
            'game_username' => 'new-player',
            'game_password' => $password,
        ],
    ]);
});

it('does not show contact fields in the cart', function () {
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['stock' => 5, 'maximum_quantity' => 5]);

    $this->postJson(route('cart.items.store'), [
        'variant_id' => $variant->id,
        'quantity' => 1,
    ])->assertCreated();

    $this->get(route('cart.index'))
        ->assertDontSee('name="contact_email"', false)
        ->assertDontSee('name="contact_phone"', false);

    expect(Order::query()->count())->toBe(0)
        ->and(session('cart')[$variant->id])->toBe(1);
});

it('uses custom per-product account fields through cart and checkout', function () {
    $gameCategory = GameCategory::factory()->create(['name' => 'Custom Service']);
    $product = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'status' => 'active',
        'purchase_fields' => [
            ['key' => 'account_email', 'label' => 'Game account email', 'type' => 'email'],
            ['key' => 'account_password', 'label' => 'Game account password', 'type' => 'password'],
        ],
    ]);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['stock' => 5, 'maximum_quantity' => 5]);
    $password = 'CustomGamePassword!42';

    $this->get(route('product-details.show', $product))
        ->assertOk()
        ->assertSee('name="purchase_details[account_email]"', false)
        ->assertDontSee('name="contact_email"', false)
        ->assertDontSee('name="contact_phone"', false);

    $this->postJson(route('cart.items.store'), [
        'variant_id' => $variant->id,
        'quantity' => 1,
        'purchase_details' => [
            'account_email' => 'not-an-email',
            'account_password' => $password,
        ],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('purchase_details.account_email')
        ->assertSessionMissing('cart.'.$variant->id);

    $this->postJson(route('cart.items.store'), [
        'variant_id' => $variant->id,
        'quantity' => 1,
        'purchase_details' => [
            'account_email' => 'game@example.com',
            'account_password' => $password,
            'unexpected' => 'not-allowed',
        ],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('purchase_details')
        ->assertSessionMissing('cart.'.$variant->id);

    $this->postJson(route('cart.items.store'), [
        'variant_id' => $variant->id,
        'quantity' => 1,
        'purchase_details' => [
            'account_email' => 'game@example.com',
            'account_password' => $password,
        ],
    ])->assertCreated();

    $this->get(route('cart.index'))
        ->assertSee('Game account email')
        ->assertSee('value="game@example.com"', false)
        ->assertDontSee('name="contact_email"', false)
        ->assertDontSee('name="contact_phone"', false)
        ->assertDontSee($password);

    $this->patch(route('cart.items.details.update', $variant), [
        'purchase_details' => [
            'account_email' => 'updated-game@example.com',
            'account_password' => '',
        ],
    ])->assertRedirect(route('cart.index'));

    $encryptedDetails = session('cart_details')[$variant->id];
    expect($encryptedDetails)->not->toContain($password)
        ->and(json_decode(Crypt::decryptString($encryptedDetails), true))->toBe([
            'purchase_details' => [
                'account_email' => 'updated-game@example.com',
                'account_password' => $password,
            ],
        ]);

    $buyer = User::factory()->create([
        'email' => 'profile-buyer@example.com',
        'phone' => '0811222333',
        'is_admin' => false,
    ]);
    $paymentSetting = PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'BCA',
        'is_active' => true,
    ]);
    $this->actingAs($buyer)
        ->post(route('cart.checkout'), ['items' => [$variant->id => '1']])
        ->assertRedirect(route('checkout.show'));
    $this->post(route('checkout.store'), [
        'payment_method_id' => $paymentSetting->id,
        'customer_email' => 'profile-buyer@example.com',
        'customer_phone' => '0811222333',
    ])->assertRedirect();

    $order = Order::query()->with('items')->sole();
    expect($order->items->sole()->customer_details)->toBe([
        'contact_email' => 'profile-buyer@example.com',
        'contact_phone' => '0811222333',
        'purchase_details' => [
            'account_email' => 'updated-game@example.com',
            'account_password' => $password,
        ],
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.orders.show', $order))
        ->assertSee('Game account email')
        ->assertSee('updated-game@example.com')
        ->assertSee($password);
});

it('calculates customer spend from paid orders and shows only that customers history', function () {
    $customer = User::factory()->create(['is_admin' => false]);
    $otherCustomer = User::factory()->create(['is_admin' => false]);
    Order::query()->create(['user_id' => $customer->id, 'status' => 'completed', 'total' => '50000.00']);
    Order::query()->create(['user_id' => $customer->id, 'status' => 'pending', 'total' => '30000.00']);
    Order::query()->create(['user_id' => $otherCustomer->id, 'status' => 'completed', 'total' => '90000.00']);

    $this->actingAs($customer)
        ->get(route('profile.edit'))
        ->assertSee('Rp 50.000')
        ->assertSee('Belum bayar')
        ->assertSee('Selesai')
        ->assertDontSee('Rp 90.000');
});

it('persists the selected language and renders the matching local dictionary', function () {
    $this->post(route('locale.update'), ['locale' => 'ms'])
        ->assertRedirect()
        ->assertSessionHas('locale', 'ms');
    $this->get(route('about'))
        ->assertSee('Tentang Meoxstore');
});

it('lets an administrator manage payment method details', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.payment-settings.index'))
        ->assertSee('Payment Methods');

    $this->post(route('admin.payment-settings.store'), [
        'type' => 'bank_transfer',
        'currency' => 'idr',
        'name' => 'Bank Example',
        'bank_name' => 'Bank Example',
        'account_name' => 'Meoxstore',
        'account_number' => '1234567890',
        'instructions' => 'Include the order number in the transfer note.',
        'is_active' => '1',
    ])->assertRedirect(route('admin.payment-settings.index'));

    $this->assertDatabaseHas('payment_settings', [
        'name' => 'Bank Example',
        'type' => 'bank_transfer',
        'bank_name' => 'Bank Example',
        'account_name' => 'Meoxstore',
        'account_number' => '1234567890',
    ]);

    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->get(route('admin.payment-settings.index'))
        ->assertForbidden();
});

it('rejects checkout when a cart variant no longer has enough stock', function () {
    $customer = User::factory()->create(['is_admin' => false]);
    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['stock' => 1, 'maximum_quantity' => 5]);

    $this->withSession(['cart' => [$variant->id => 2]])
        ->actingAs($customer)
        ->post(route('cart.checkout'), ['items' => [$variant->id => '1']])
        ->assertRedirect(route('checkout.show'));
    $this->get(route('checkout.show'))
        ->assertRedirect(route('cart.index'))
        ->assertSessionHasErrors('cart');

    expect(Order::query()->count())->toBe(0)
        ->and($variant->fresh()->stock)->toBe(1);
});

it('does not accept payment proof before transfer details have been configured', function () {
    $customer = User::factory()->create(['is_admin' => false]);
    $order = Order::query()->create([
        'user_id' => $customer->id,
        'status' => 'pending',
        'total' => '10000.00',
    ]);

    $this->actingAs($customer)
        ->post(route('orders.payment-proof', $order), [])
        ->assertSessionHasErrors('payment_proof');

    expect($order->fresh()->status)->toBe('pending')
        ->and($order->fresh()->payment_proof_path)->toBeNull();
});

it('does not let an administrator confirm payment while an order is still unpaid', function () {
    $order = Order::query()->create([
        'user_id' => User::factory()->create(['is_admin' => false])->id,
        'status' => 'pending',
        'total' => '10000.00',
    ]);

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.orders.status', $order), ['status' => 'payment_confirmed'])
        ->assertUnprocessable();

    expect($order->fresh()->status)->toBe('pending');
});

it('lets a customer remove a cart item whose variant was deleted', function () {
    $staleVariantId = 987654;

    $this->withSession(['cart' => [$staleVariantId => 1]])
        ->get(route('cart.index'))
        ->assertSee('Tidak tersedia');

    $this->delete(route('cart.items.destroy', $staleVariantId))
        ->assertRedirect(route('cart.index'))
        ->assertSessionMissing('cart.'.$staleVariantId);
});
