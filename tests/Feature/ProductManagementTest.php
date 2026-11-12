<?php

use App\Models\GameCategory;
use App\Models\ItemCategory;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\ProductCurrencyRate;
use App\Models\ProductVariant;
use App\Models\StoreSetting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function productInput(GameCategory $gameCategory, ItemCategory $itemCategory, array $variants): array
{
    $gameCategory->itemCategories()->syncWithoutDetaching($itemCategory->id);

    return [
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
        'name' => 'Blox Fruit Account',
        'description' => 'A test product.',
        'status' => 'active',
        'is_featured' => '1',
        'variants' => $variants,
    ];
}

function variantInput(array $overrides = []): array
{
    $stock = (string) ($overrides['stock'] ?? '5');

    return array_merge([
        'name' => 'Default',
        'price' => '10.00',
        'stock' => $stock,
        'minimum_quantity' => '1',
        'maximum_quantity' => $stock,
        'status' => 'active',
        'sort_order' => '0',
    ], $overrides);
}

it('redirects guests away from product management', function () {
    $this->get(route('admin.products.index'))->assertRedirectToRoute('login');
    $this->get(route('admin.rates.index'))->assertRedirectToRoute('login');
});

it('lets admins toggle store availability and keeps checkout available while closed', function () {
    $this->patch(route('admin.store-status.update'), ['is_online' => false])
        ->assertRedirectToRoute('login');

    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)
        ->get(route('admin.products.index'))
        ->assertSee('role="switch" checked', false);

    $this->patch(route('admin.store-status.update'), ['is_online' => false])
        ->assertRedirect()
        ->assertSessionHas('success', __('store.store_status_updated'));

    expect(StoreSetting::current()->is_online)->toBeFalse();
    $this->get(route('home'))
        ->assertSee(__('store.home_system_offline'))
        ->assertSee(__('store.store_closed_warning'));

    $product = Product::factory()->create(['status' => 'active']);
    $variant = $product->variants()->firstOrFail();
    $variant->update(['stock' => 5, 'maximum_quantity' => 5]);
    PaymentSetting::query()->create([
        'type' => 'bank_transfer',
        'name' => 'BCA',
        'is_active' => true,
    ]);
    $this->withSession([
        'checkout' => [
            'source' => 'cart',
            'items' => [$variant->id => 1],
            'currencies' => [$variant->id => 'idr'],
        ],
    ])->get(route('checkout.show'))
        ->assertSee(__('store.store_closed_warning'))
        ->assertSee('action="'.route('checkout.store').'"', false);

    $this->patch(route('admin.store-status.update'), [])
        ->assertInvalid(['is_online']);
    expect(StoreSetting::current()->is_online)->toBeFalse();

    $this->patch(route('admin.store-status.update'), ['is_online' => true])
        ->assertRedirect();
    expect(StoreSetting::current()->is_online)->toBeTrue();
});

it('forbids non-admins from changing store availability', function () {
    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->patch(route('admin.store-status.update'), ['is_online' => false])
        ->assertForbidden();

    expect(StoreSetting::current()->is_online)->toBeTrue();
});

it('creates categories with active status and rejects duplicate names', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.game-categories.store'), ['name' => 'Blox Fruit'])
        ->assertRedirectToRoute('admin.game-categories.index');

    $category = GameCategory::query()->where('name', 'Blox Fruit')->firstOrFail();
    expect($category->status)->toBe('active');

    $this->put(route('admin.game-categories.update', $category), [
        'name' => 'Blox Fruit',
        'status' => 'active',
    ])->assertRedirectToRoute('admin.game-categories.index');

    $this->post(route('admin.game-categories.store'), ['name' => 'Blox Fruit'])
        ->assertInvalid(['name']);

    $this->patch(route('admin.game-categories.status', $category))
        ->assertRedirect();

    expect($category->fresh()->status)->toBe('inactive');
});

it('deactivates products when their game category is deactivated without reactivating them later', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $unrelatedGame = GameCategory::factory()->create();
    $affectedProduct = Product::factory()->create(['game_category_id' => $gameCategory->id]);
    $unrelatedProduct = Product::factory()->create(['game_category_id' => $unrelatedGame->id]);

    $this->patch(route('admin.game-categories.status', $gameCategory))->assertRedirect();

    expect($gameCategory->fresh()->status)->toBe('inactive');
    expect($affectedProduct->fresh()->status)->toBe('inactive');
    expect($unrelatedProduct->fresh()->status)->toBe('active');

    $this->patch(route('admin.game-categories.status', $gameCategory))->assertRedirect();

    expect($affectedProduct->fresh()->status)->toBe('inactive');
});

it('uploads images for game and item categories', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.game-categories.store'), [
        'name' => 'Blox Fruit',
        'image' => UploadedFile::fake()->create('blox-fruit.png', 1, 'image/png'),
    ])->assertRedirectToRoute('admin.game-categories.index');

    $gameCategory = GameCategory::query()->where('name', 'Blox Fruit')->firstOrFail();
    Storage::disk('public')->assertExists($gameCategory->image);

    $this->post(route('admin.item-categories.store'), [
        'name' => 'Fruit',
        'game_category_id' => $gameCategory->id,
        'image' => UploadedFile::fake()->create('fruit.png', 1, 'image/png'),
    ])->assertRedirectToRoute('admin.item-categories.index');

    $itemCategory = ItemCategory::query()->where('name', 'Fruit')->firstOrFail();
    Storage::disk('public')->assertExists($itemCategory->image);
    $this->assertDatabaseHas('game_category_item_category', [
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
    ]);
});

it('renders item category management and creates an active item category', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create(['name' => 'Blox Fruit']);

    $this->get(route('admin.item-categories.index'))
        ->assertOk()
        ->assertSee('Item Categories');

    $this->get(route('admin.item-categories.create'))
        ->assertOk()
        ->assertSee('Add Item Category')
        ->assertSee('name="game_category_id"', false)
        ->assertSee('Blox Fruit');

    $this->post(route('admin.item-categories.store'), [
        'name' => 'Account',
        'game_category_id' => $gameCategory->id,
    ])
        ->assertRedirectToRoute('admin.item-categories.index');

    $itemCategory = ItemCategory::query()->where('name', 'Account')->firstOrFail();
    expect($itemCategory->status)->toBe('active');
    $this->assertDatabaseHas('game_category_item_category', [
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
    ]);
});

it('links an existing item category to another game without creating a duplicate', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());
    $bloxFruit = GameCategory::factory()->create(['name' => 'Blox Fruit']);
    $fisch = GameCategory::factory()->create(['name' => 'Fisch']);
    $itemCategory = ItemCategory::factory()->create(['name' => 'Account']);
    $bloxFruit->itemCategories()->attach($itemCategory);

    $this->post(route('admin.item-categories.store'), [
        'name' => 'Account',
        'game_category_id' => $fisch->id,
        'image' => UploadedFile::fake()->create('unused-category.jpg', 1, 'image/jpeg'),
    ])
        ->assertRedirectToRoute('admin.item-categories.index')
        ->assertSessionHas(
            'success',
            'Existing item category linked to the selected game; its existing settings were kept.',
        );

    expect(ItemCategory::query()->where('name', 'Account')->count())->toBe(1);
    expect(Storage::disk('public')->allFiles('categories'))->toBe([]);
    $this->assertDatabaseHas('game_category_item_category', [
        'game_category_id' => $bloxFruit->id,
        'item_category_id' => $itemCategory->id,
    ]);
    $this->assertDatabaseHas('game_category_item_category', [
        'game_category_id' => $fisch->id,
        'item_category_id' => $itemCategory->id,
    ]);

    $this->get(route('admin.game-categories.item-categories', $fisch))
        ->assertOk()
        ->assertJsonFragment(['id' => $itemCategory->id, 'name' => 'Account']);
});

it('does not link an inactive item category to another game', function () {
    $this->actingAs(User::factory()->create());
    $fisch = GameCategory::factory()->create(['name' => 'Fisch']);
    $itemCategory = ItemCategory::factory()->create([
        'name' => 'Account',
        'status' => 'inactive',
    ]);

    $this->post(route('admin.item-categories.store'), [
        'name' => 'Account',
        'game_category_id' => $fisch->id,
    ])->assertInvalid(['name']);

    expect(ItemCategory::query()->where('name', 'Account')->count())->toBe(1);
    $this->assertDatabaseMissing('game_category_item_category', [
        'game_category_id' => $fisch->id,
        'item_category_id' => $itemCategory->id,
    ]);
});

it('requires an active game when creating an item category', function () {
    $this->actingAs(User::factory()->create());
    $inactiveGame = GameCategory::factory()->create(['status' => 'inactive']);

    $this->post(route('admin.item-categories.store'), ['name' => 'Fruit'])
        ->assertInvalid(['game_category_id']);

    $this->post(route('admin.item-categories.store'), [
        'name' => 'Fruit',
        'game_category_id' => $inactiveGame->id,
    ])->assertInvalid(['game_category_id']);

    expect(ItemCategory::query()->where('name', 'Fruit')->exists())->toBeFalse();
});

it('adds an existing item category to the selected game without unlinking its other games', function () {
    $this->actingAs(User::factory()->create());
    $firstGame = GameCategory::factory()->create(['name' => 'Blox Fruit']);
    $secondGame = GameCategory::factory()->create(['name' => 'Growtopia']);
    $itemCategory = ItemCategory::factory()->create(['name' => 'Item']);
    $firstGame->itemCategories()->attach($itemCategory);

    $this->put(route('admin.item-categories.update', $itemCategory), [
        'name' => 'Item',
        'game_category_id' => $secondGame->id,
    ])->assertRedirectToRoute('admin.item-categories.index');

    $this->assertDatabaseHas('game_category_item_category', [
        'game_category_id' => $firstGame->id,
        'item_category_id' => $itemCategory->id,
    ]);
    $this->assertDatabaseHas('game_category_item_category', [
        'game_category_id' => $secondGame->id,
        'item_category_id' => $itemCategory->id,
    ]);
});

it('deactivates all products when their item category is deactivated', function () {
    $this->actingAs(User::factory()->create());
    $itemCategory = ItemCategory::factory()->create();
    $firstGame = GameCategory::factory()->create();
    $secondGame = GameCategory::factory()->create();
    $firstProduct = Product::factory()->create([
        'game_category_id' => $firstGame->id,
        'item_category_id' => $itemCategory->id,
    ]);
    $secondProduct = Product::factory()->create([
        'game_category_id' => $secondGame->id,
        'item_category_id' => $itemCategory->id,
    ]);
    $unrelatedProduct = Product::factory()->create();

    $this->patch(route('admin.item-categories.status', $itemCategory))->assertRedirect();

    expect($itemCategory->fresh()->status)->toBe('inactive');
    expect($firstProduct->fresh()->status)->toBe('inactive');
    expect($secondProduct->fresh()->status)->toBe('inactive');
    expect($unrelatedProduct->fresh()->status)->toBe('active');
});

it('creates game and item categories from inline json requests', function () {
    $this->actingAs(User::factory()->create());

    $gameResponse = $this->postJson(route('admin.game-categories.store'), ['name' => 'Fisch'])
        ->assertCreated()
        ->assertJsonPath('name', 'Fisch');

    $this->postJson(route('admin.item-categories.store'), [
        'name' => 'Skin',
        'game_category_id' => $gameResponse->json('id'),
        'bobux_type' => 'gamepass_5_days',
    ])
        ->assertCreated()
        ->assertJsonPath('name', 'Skin')
        ->assertJsonPath('bobux_type', 'gamepass_5_days')
        ->assertJsonPath('uses_robux', true);

    $this->assertDatabaseHas('game_categories', ['name' => 'Fisch', 'status' => 'active']);
    $this->assertDatabaseHas('item_categories', [
        'name' => 'Skin',
        'status' => 'active',
        'bobux_type' => 'gamepass_5_days',
    ]);
    $this->assertDatabaseHas('game_category_item_category', [
        'game_category_id' => $gameResponse->json('id'),
        'item_category_id' => ItemCategory::query()->where('name', 'Skin')->value('id'),
    ]);
});

it('renders only game-mapped item categories when editing a product', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $otherGameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create(['name' => 'Account']);
    $otherItemCategory = ItemCategory::factory()->create(['name' => 'Unrelated Item']);
    $gameCategory->itemCategories()->attach($itemCategory);
    $otherGameCategory->itemCategories()->attach($otherItemCategory);
    $product = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
    ]);

    $this->get(route('admin.products.edit', $product))
        ->assertOk()
        ->assertSee('Account')
        ->assertDontSee('Unrelated Item');
});

it('only returns item categories mapped to the selected game', function () {
    $this->actingAs(User::factory()->create());
    $bloxFruit = GameCategory::factory()->create(['name' => 'Blox Fruit']);
    $fisch = GameCategory::factory()->create(['name' => 'Fisch']);
    $account = ItemCategory::factory()->create(['name' => 'Account']);
    $item = ItemCategory::factory()->create(['name' => 'Item']);
    $bloxFruit->itemCategories()->attach($item);
    $fisch->itemCategories()->attach($account);

    $this->getJson(route('admin.game-categories.item-categories', $bloxFruit))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['name' => 'Item'])
        ->assertJsonMissing(['name' => 'Account']);
});

it('limits product-list item filters and products to the selected game', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $fisch = GameCategory::factory()->create(['name' => 'Fisch']);
    $otherGame = GameCategory::factory()->create(['name' => 'Blox Fruit']);
    $fischCategory = ItemCategory::factory()->create(['name' => 'Fisch Items']);
    $otherCategory = ItemCategory::factory()->create(['name' => 'Blox Fruit Items']);
    $fisch->itemCategories()->attach($fischCategory);
    $otherGame->itemCategories()->attach($otherCategory);
    Product::factory()->create([
        'game_category_id' => $fisch->id,
        'item_category_id' => $fischCategory->id,
        'name' => 'Fisch product',
    ]);
    Product::factory()->create([
        'game_category_id' => $otherGame->id,
        'item_category_id' => $otherCategory->id,
        'name' => 'Blox Fruit product',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.products.index', ['game_category_id' => $fisch->id]))
        ->assertSee('Fisch Items')
        ->assertDontSee('Blox Fruit Items')
        ->assertSee('Fisch product')
        ->assertDontSee('Blox Fruit product')
        ->assertSee('data-game-filter', false)
        ->assertSee('filterForm.requestSubmit()', false)
        ->assertViewHas('itemCategories', fn ($categories): bool => $categories->contains('id', $fischCategory->id)
            && ! $categories->contains('id', $otherCategory->id));
});

it('rejects a product whose item category is not mapped to its game', function () {
    $this->actingAs(User::factory()->create());
    $selectedGame = GameCategory::factory()->create();
    $otherGame = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();
    $otherGame->itemCategories()->attach($itemCategory);
    $input = productInput($selectedGame, $itemCategory, [variantInput()]);
    $selectedGame->itemCategories()->detach($itemCategory);

    $this->post(route('admin.products.store'), $input)->assertInvalid(['item_category_id']);

    expect(Product::query()->count())->toBe(0);
});

it('renders the add product form with a default variant row', function () {
    $this->actingAs(User::factory()->create());
    GameCategory::factory()->create();
    ItemCategory::factory()->create();

    $this->get(route('admin.products.create'))
        ->assertOk()
        ->assertSee('Add Product')
        ->assertSeeText('Variant 1')
        ->assertSeeText('Active automatically')
        ->assertDontSee('name="status"', false)
        ->assertSee('Maximum qty', false)
        ->assertSee('data-stock-input', false)
        ->assertSee('data-maximum-quantity readonly', false);
});

it('creates a product without a status field and defaults it to active', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();
    $input = productInput($gameCategory, $itemCategory, [variantInput()]);
    unset($input['status']);

    $this->post(route('admin.products.store'), $input)
        ->assertRedirect();

    expect(Product::query()->where('name', 'Blox Fruit Account')->value('status'))->toBe('active');
});

it('removes sku storage from product variants', function () {
    expect(Schema::hasColumn('product_variants', 'sku'))->toBeFalse();
});

it('renders inline category creation and variant price/status controls', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();
    $product = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
        'name' => 'Editable Product',
    ]);
    $product->variants()->firstOrFail()->update(['price' => '40000.00']);

    $this->get(route('admin.products.create'))
        ->assertOk()
        ->assertSee(route('admin.game-categories.store'), false)
        ->assertSee(route('admin.item-categories.store'), false);

    $this->get(route('admin.products.index'))
        ->assertOk()
        ->assertSee('Rp 40,000')
        ->assertSee('≈ RM 10.00')
        ->assertSee('≈ $2.67')
        ->assertSee('value="40,000"', false)
        ->assertSee('Save prices')
        ->assertSee('Status for Editable Product')
        ->assertSee(route('admin.products.variant-prices', $product), false);
});

it('creates products active with their variants in one request', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();

    $input = productInput($gameCategory, $itemCategory, [
        variantInput(),
        variantInput(['name' => '2x Fruit', 'sort_order' => '1']),
    ]);
    $input['status'] = 'inactive';

    $this->post(route('admin.products.store'), $input)->assertRedirect();

    $product = Product::query()->where('name', 'Blox Fruit Account')->firstOrFail();
    expect($product->status)->toBe('active')
        ->and($product->variants)->toHaveCount(2);
    $this->assertDatabaseHas('product_variants', [
        'product_id' => $product->id,
        'name' => '2x Fruit',
        'sort_order' => 1,
    ]);
    expect($product->is_featured)->toBeTrue();
});

it('saves selected product badges and applies the discount only when its badge is enabled', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();
    $input = productInput($gameCategory, $itemCategory, [variantInput()]);
    $input['badges'] = ['promo', 'trending', 'hot', 'discount'];
    $input['discount_percent'] = '15';

    $this->post(route('admin.products.store'), $input)->assertRedirect();

    $product = Product::query()->where('name', 'Blox Fruit Account')->firstOrFail();
    expect($product->badges)->toBe(['promo', 'trending', 'hot', 'discount'])
        ->and((float) $product->discount_percent)->toBe(15.0);

    $this->get(route('admin.products.edit', $product))
        ->assertSee('id="badge-promo" name="badges[]" type="checkbox" value="promo" checked', false)
        ->assertSee('id="badge-discount" name="badges[]" type="checkbox" value="discount" checked', false);
});

it('requires a valid discount percentage when the discount badge is selected', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();
    $input = productInput($gameCategory, $itemCategory, [variantInput()]);
    $input['badges'] = ['discount'];

    $this->post(route('admin.products.store'), $input)
        ->assertInvalid(['discount_percent']);

    expect(Product::query()->exists())->toBeFalse();
});

it('rejects a discount percentage when the discount badge is not selected', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();
    $input = productInput($gameCategory, $itemCategory, [variantInput()]);
    $input['discount_percent'] = '15';

    $this->post(route('admin.products.store'), $input)
        ->assertInvalid(['discount_percent']);

    expect(Product::query()->exists())->toBeFalse();
});

it('rejects an unsupported product badge', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();
    $input = productInput($gameCategory, $itemCategory, [variantInput()]);
    $input['badges'] = ['unverified'];

    $this->post(route('admin.products.store'), $input)
        ->assertInvalid(['badges.0']);

    expect(Product::query()->exists())->toBeFalse();
});

it('saves a custom account form for a product', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();
    $input = productInput($gameCategory, $itemCategory, [variantInput()]);
    $input['purchase_fields'] = [
        ['key' => 'username', 'label' => 'Game username', 'type' => 'text'],
        ['key' => 'password', 'label' => 'Game password', 'type' => 'password'],
        ['key' => 'email', 'label' => 'Game email', 'type' => 'email'],
    ];

    $this->post(route('admin.products.store'), $input)->assertRedirect();

    $product = Product::query()->where('name', 'Blox Fruit Account')->firstOrFail();
    expect($product->purchaseFields())->toBe([
        ['key' => 'username', 'label' => 'Game username', 'type' => 'text'],
        ['key' => 'password', 'label' => 'Game password', 'type' => 'password'],
        ['key' => 'email', 'label' => 'Game email', 'type' => 'email'],
    ]);

    $this->get(route('admin.products.edit', $product))
        ->assertSee('name="purchase_fields[0][key]" value="username"', false)
        ->assertSee('name="purchase_fields[1][type]"', false)
        ->assertSee('Game password');
});

it('rejects duplicate or unsupported custom account form fields', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();
    $input = productInput($gameCategory, $itemCategory, [variantInput()]);
    $input['purchase_fields'] = [
        ['key' => 'username', 'label' => 'Username', 'type' => 'text'],
        ['key' => 'username', 'label' => 'Password', 'type' => 'password'],
    ];

    $this->post(route('admin.products.store'), $input)
        ->assertInvalid(['purchase_fields.1.key']);

    expect(Product::query()->count())->toBe(0);

    $input['purchase_fields'][1] = ['key' => 'password', 'label' => 'Password', 'type' => 'hidden'];
    $this->post(route('admin.products.store'), $input)
        ->assertInvalid(['purchase_fields.1.type']);

    expect(Product::query()->count())->toBe(0);

    $input['purchase_fields'][1] = ['key' => 'purchase_details', 'label' => 'Nested field', 'type' => 'text'];
    $this->post(route('admin.products.store'), $input)
        ->assertInvalid(['purchase_fields.1.key']);

    expect(Product::query()->count())->toBe(0);
});

it('toggles product status from the listing action', function () {
    $this->actingAs(User::factory()->create());
    $product = Product::factory()->create(['status' => 'draft']);

    $this->patch(route('admin.products.status', $product))->assertRedirect();

    expect($product->fresh()->status)->toBe('active');
});

it('sets an explicit product status from the listing', function () {
    $this->actingAs(User::factory()->create());
    $product = Product::factory()->create(['status' => 'draft']);

    $this->patch(route('admin.products.status', $product), ['status' => 'inactive'])
        ->assertRedirect();

    expect($product->fresh()->status)->toBe('inactive');
});

it('updates variant prices from the product listing endpoint', function () {
    $this->actingAs(User::factory()->create());
    $product = Product::factory()->create();
    $variant = $product->variants()->firstOrFail();

    $this->patch(route('admin.products.variant-prices', $product), [
        'variants' => [
            ['id' => $variant->id, 'price' => '18.75'],
        ],
    ])->assertRedirect();

    expect($variant->fresh()->price)->toBe('18.75');
});

it('updates currency rates from the dedicated rates menu and persists toggles', function () {
    $this->actingAs(User::factory()->create());
    expect(ProductCurrencyRate::current()->idr_per_gamepass_bobux)->toBe('150.0000');

    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();
    $product = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
    ]);
    $product->variants()->firstOrFail()->update(['price' => '40000.00']);

    $this->patch(route('admin.rates.update'), [
        'idr_per_myr' => '4000',
        'idr_per_usd' => '15000',
        'idr_per_robux' => '120',
        'idr_per_gamepass_bobux' => '155',
        'bobux_gift_ingame_enabled' => '1',
        'bobux_gamepass_5_days_enabled' => '1',
        'bobux_login_enabled' => '1',
    ])->assertRedirect();

    expect(ProductCurrencyRate::current()->idr_per_myr)->toBe('4000.0000');
    expect(ProductCurrencyRate::current()->idr_per_usd)->toBe('15000.0000');
    expect(ProductCurrencyRate::current()->idr_per_robux)->toBe('120.0000');
    expect(ProductCurrencyRate::current()->idr_per_gamepass_bobux)->toBe('155.0000');
    expect(ProductCurrencyRate::current()->bobux_gift_ingame_enabled)->toBeTrue()
        ->and(ProductCurrencyRate::current()->bobux_gamepass_5_days_enabled)->toBeTrue()
        ->and(ProductCurrencyRate::current()->bobux_login_enabled)->toBeTrue();

    $this->get(route('admin.rates.index'))
        ->assertOk()
        ->assertSee(route('admin.rates.index'), false)
        ->assertSee(route('admin.rates.update'), false)
        ->assertSee('1 RM = IDR')
        ->assertSee('value="4000"', false)
        ->assertSee('value="15000"', false)
        ->assertSee('value="120"', false)
        ->assertSee('value="155"', false)
        ->assertSee('name="bobux_gift_ingame_enabled"', false)
        ->assertSee('id="bobux_gift_ingame_enabled"', false)
        ->assertSee('Bobux Gamepass 5 hari')
        ->assertSee('Bobux via Login');

    $this->patch(route('admin.rates.update'), [
        'idr_per_myr' => '4000',
        'idr_per_usd' => '15000',
        'idr_per_robux' => '120',
        'idr_per_gamepass_bobux' => '155',
        'bobux_gift_ingame_enabled' => '0',
        'bobux_gamepass_5_days_enabled' => '0',
        'bobux_login_enabled' => '0',
    ])->assertRedirect();

    expect(ProductCurrencyRate::current()->bobux_gift_ingame_enabled)->toBeFalse()
        ->and(ProductCurrencyRate::current()->bobux_gamepass_5_days_enabled)->toBeFalse()
        ->and(ProductCurrencyRate::current()->bobux_login_enabled)->toBeFalse();

    $this->get(route('admin.rates.index'))
        ->assertOk()
        ->assertDontSee('id="bobux_gift_ingame_enabled" name="bobux_gift_ingame_enabled" type="checkbox" value="1" role="switch" checked', false)
        ->assertDontSee('id="bobux_gamepass_5_days_enabled" name="bobux_gamepass_5_days_enabled" type="checkbox" value="1" role="switch" checked', false)
        ->assertDontSee('id="bobux_login_enabled" name="bobux_login_enabled" type="checkbox" value="1" role="switch" checked', false);
});

it('rejects zero or negative currency rates', function (string $rate) {
    $this->actingAs(User::factory()->create());

    $this->patch(route('admin.rates.update'), [
        'idr_per_myr' => $rate,
        'idr_per_usd' => '15000',
        'idr_per_robux' => '120',
        'idr_per_gamepass_bobux' => '150',
        'bobux_gift_ingame_enabled' => '1',
        'bobux_gamepass_5_days_enabled' => '1',
        'bobux_login_enabled' => '1',
    ])->assertInvalid(['idr_per_myr']);
})->with(['zero' => '0', 'negative' => '-1']);

it('rejects a non-positive Gamepass Bobux rate', function (string $rate) {
    $this->actingAs(User::factory()->create());

    $this->patch(route('admin.rates.update'), [
        'idr_per_myr' => '4000',
        'idr_per_usd' => '15000',
        'idr_per_robux' => '120',
        'idr_per_gamepass_bobux' => $rate,
        'bobux_gift_ingame_enabled' => '1',
        'bobux_gamepass_5_days_enabled' => '1',
        'bobux_login_enabled' => '1',
    ])->assertInvalid(['idr_per_gamepass_bobux']);
})->with(['zero' => '0', 'negative' => '-1']);

it('marks item categories for Bobux pricing and shows their converted price in the product list', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create(['name' => 'Blox Fruit']);

    $this->post(route('admin.item-categories.store'), [
        'name' => 'Permanent',
        'game_category_id' => $gameCategory->id,
        'uses_robux' => '1',
    ])->assertRedirectToRoute('admin.item-categories.index');

    $itemCategory = ItemCategory::query()->where('name', 'Permanent')->firstOrFail();
    $gameCategory->itemCategories()->syncWithoutDetaching($itemCategory->id);
    $product = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
        'name' => 'Blox Fruit Permanent',
    ]);
    $product->variants()->firstOrFail()->update(['price' => '100.00']);

    $this->get(route('admin.products.index'))
        ->assertOk()
        ->assertSee('Blox Fruit Permanent')
        ->assertSee('Default (Bobux)')
        ->assertSee('Rp 12,000');

    expect($itemCategory->uses_robux)->toBeTrue();
});

it('creates login Bobux products with manual Rupiah prices and a Bobux amount per variant', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create([
        'bobux_type' => 'login',
        'uses_robux' => false,
    ]);
    $input = productInput($gameCategory, $itemCategory, [
        variantInput(['price' => '25000', 'bobux_amount' => '1200']),
    ]);

    $this->post(route('admin.products.store'), $input)->assertRedirect();

    $variant = ProductVariant::query()->whereHas('product', fn ($query) => $query->where('name', 'Blox Fruit Account'))->sole();
    expect($variant->price)->toBe('25000.00')
        ->and($variant->bobux_amount)->toBe('1200.00');

    $this->get(route('admin.products.edit', $variant->product))
        ->assertOk()
        ->assertSee('Bobux amount')
        ->assertSee('name="variants[0][bobux_amount]" type="number" min="1" step="1" value="1200.00"', false);

    $this->get(route('product-details.show', $variant->product))
        ->assertOk()
        ->assertSee('Rp 25,000')
        ->assertSee('1,200 Bobux');
});

it('requires a positive integer Bobux amount for login product variants', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create(['bobux_type' => 'login']);
    $input = productInput($gameCategory, $itemCategory, [variantInput()]);

    $this->from(route('admin.products.create'))
        ->post(route('admin.products.store'), $input)
        ->assertSessionHasErrors('variants.0.bobux_amount');

    $input['variants'][0]['bobux_amount'] = '0';
    $this->from(route('admin.products.create'))
        ->post(route('admin.products.store'), $input)
        ->assertSessionHasErrors('variants.0.bobux_amount');

    expect(Product::query()->where('name', 'Blox Fruit Account')->exists())->toBeFalse();
});

it('rejects variant price updates for another product', function () {
    $this->actingAs(User::factory()->create());
    $product = Product::factory()->create();
    $otherProduct = Product::factory()->create();
    $otherVariant = $otherProduct->variants()->firstOrFail();

    $this->patch(route('admin.products.variant-prices', $product), [
        'variants' => [
            ['id' => $otherVariant->id, 'price' => '18.75'],
        ],
    ])->assertInvalid(['variants.0.id']);

    expect($otherVariant->fresh()->price)->not->toBe('18.75');
});

it('stores an optional product image on the public disk', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();

    $this->post(route('admin.products.store'), array_merge(
        productInput($gameCategory, $itemCategory, [variantInput()]),
        ['image' => UploadedFile::fake()->createWithContent(
            'product.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC'),
        )],
    ))->assertRedirect();

    $product = Product::query()->where('name', 'Blox Fruit Account')->firstOrFail();
    expect($product->image)->not->toBeNull();
    Storage::disk('public')->assertExists($product->image);
});

it('rejects products without a variant and leaves no product behind', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();
    $input = productInput($gameCategory, $itemCategory, []);
    unset($input['variants']);

    $this->post(route('admin.products.store'), $input)->assertInvalid(['variants']);

    expect(Product::query()->count())->toBe(0);
});

it('rejects a maximum quantity below the minimum quantity', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();

    $this->post(route('admin.products.store'), productInput($gameCategory, $itemCategory, [
        variantInput(['minimum_quantity' => '5', 'maximum_quantity' => '4']),
    ]))->assertInvalid(['variants.0.maximum_quantity']);

    expect(Product::query()->count())->toBe(0);
});

it('requires maximum quantity to match stock and allows zero-stock variants', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();

    $this->post(route('admin.products.store'), productInput($gameCategory, $itemCategory, [
        variantInput(['stock' => '8', 'maximum_quantity' => '7']),
    ]))->assertInvalid(['variants.0.maximum_quantity']);

    $this->post(route('admin.products.store'), productInput($gameCategory, $itemCategory, [
        variantInput(['stock' => '0']),
    ]))->assertRedirect();

    $this->assertDatabaseHas('product_variants', [
        'name' => 'Default',
        'stock' => 0,
        'maximum_quantity' => 0,
    ]);
});

it('seeds the requested categories, products, and variants', function () {
    $this->seed(DatabaseSeeder::class);

    expect(GameCategory::query()->count())->toBe(4);
    expect(ItemCategory::query()->count())->toBe(4);
    expect(Product::query()->count())->toBe(4);
    expect(ProductVariant::query()->count())->toBe(6);
});

it('updates variants and removes omitted variants', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();
    $product = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
    ]);
    $variant = $product->variants()->firstOrFail();
    ProductVariant::factory()->for($product)->create(['name' => 'Remove me']);

    $this->put(route('admin.products.update', $product), productInput($gameCategory, $itemCategory, [
        variantInput(['id' => (string) $variant->id, 'name' => 'Updated Default']),
    ]))->assertRedirect();

    expect($product->variants()->count())->toBe(1);
    expect($variant->fresh()->name)->toBe('Updated Default');
    $this->assertDatabaseMissing('product_variants', [
        'product_id' => $product->id,
        'name' => 'Remove me',
    ]);

    $updatedInput = productInput($gameCategory, $itemCategory, [
        variantInput(['id' => (string) $variant->id, 'name' => 'Updated Default']),
    ]);
    unset($updatedInput['is_featured']);

    $this->put(route('admin.products.update', $product), $updatedInput)->assertRedirect();

    expect($product->fresh()->is_featured)->toBeFalse();
});

it('renders saved variants on product edit and detail pages', function () {
    $this->actingAs(User::factory()->create());
    $product = Product::factory()->create(['name' => 'Saved Product']);
    $variant = $product->variants()->firstOrFail();

    $this->get(route('admin.products.edit', $product))
        ->assertOk()
        ->assertSee('Edit Product')
        ->assertSee($variant->name);

    $this->get(route('admin.products.show', $product))
        ->assertOk()
        ->assertSee('Saved Product')
        ->assertSee($variant->name)
        ->assertDontSee('SKU');
});

it('does not allow removing the final product variant', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();
    $product = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
    ]);
    $input = productInput($gameCategory, $itemCategory, []);

    $this->put(route('admin.products.update', $product), $input)->assertInvalid(['variants']);

    expect($product->variants()->count())->toBe(1);
});

it('filters the product listing without omitting related category names', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create(['name' => 'Roblox']);
    $itemCategory = ItemCategory::factory()->create(['name' => 'Account']);
    Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
        'name' => 'Roblox Account',
        'status' => 'active',
        'is_featured' => true,
    ]);
    Product::factory()->create(['name' => 'Other Product']);

    $this->get(route('admin.products.index', [
        'game_category_id' => $gameCategory->id,
        'status' => 'active',
        'featured' => '1',
    ]))
        ->assertOk()
        ->assertSee('Roblox Account')
        ->assertSee('Roblox')
        ->assertSee('Account')
        ->assertDontSee('Other Product');
});

it('renders multi-select and delete controls for products and categories', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.products.index'))
        ->assertOk()
        ->assertSee('id="bulk-product-form"', false)
        ->assertSee('id="select-all-products"', false)
        ->assertSee('name="ids[]"', false)
        ->assertSee('Permanently delete', false);

    $this->get(route('admin.game-categories.index'))
        ->assertOk()
        ->assertSee('id="bulk-category-form"', false)
        ->assertSee('id="select-all-categories"', false)
        ->assertSee('name="ids[]"', false)
        ->assertSee('Permanently delete', false);
});

it('archives selected products without changing unselected products', function () {
    $this->actingAs(User::factory()->create());
    $selectedProducts = Product::factory()->count(2)->create();
    $unselectedProduct = Product::factory()->create();

    $this->post(route('admin.products.bulk'), [
        'action' => 'archive',
        'ids' => $selectedProducts->modelKeys(),
    ])->assertRedirect();

    foreach ($selectedProducts as $product) {
        expect($product->fresh()->status)->toBe('inactive');
    }
    expect($unselectedProduct->fresh()->status)->toBe('active');
});

it('permanently deletes selected products and their images when they have no order history', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());
    $firstProduct = Product::factory()->create(['image' => 'products/first.png']);
    $secondProduct = Product::factory()->create(['image' => 'products/second.png']);
    Storage::disk('public')->put('products/first.png', 'first');
    Storage::disk('public')->put('products/second.png', 'second');
    $variantIds = $firstProduct->variants()->pluck('id')->all();

    $this->post(route('admin.products.bulk'), [
        'action' => 'delete',
        'ids' => [$firstProduct->id, $secondProduct->id],
    ])->assertRedirect();

    $this->assertDatabaseMissing('products', ['id' => $firstProduct->id]);
    $this->assertDatabaseMissing('products', ['id' => $secondProduct->id]);
    $this->assertDatabaseMissing('product_variants', ['id' => $variantIds[0]]);
    Storage::disk('public')->assertMissing('products/first.png');
    Storage::disk('public')->assertMissing('products/second.png');
});

it('prevents permanent product deletion when order history exists', function () {
    $this->actingAs(User::factory()->create());
    $product = Product::factory()->create();
    $variant = $product->variants()->firstOrFail();
    $order = Order::query()->create([
        'user_id' => auth()->id(),
        'status' => 'pending',
        'total' => $variant->price,
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'product_name' => $product->name,
        'variant_name' => $variant->name,
        'quantity' => 1,
        'unit_price' => $variant->price,
    ]);

    $this->delete(route('admin.products.destroy', $product))
        ->assertSessionHasErrors('product');
    $this->post(route('admin.products.bulk'), [
        'action' => 'delete',
        'ids' => [$product->id],
    ])->assertSessionHasErrors('ids');

    $this->assertDatabaseHas('products', ['id' => $product->id]);
    $this->assertDatabaseHas('order_items', ['product_id' => $product->id]);
});

it('archives selected item categories and deactivates their products', function () {
    $this->actingAs(User::factory()->create());
    $firstCategory = ItemCategory::factory()->create();
    $secondCategory = ItemCategory::factory()->create();
    $firstProduct = Product::factory()->create(['item_category_id' => $firstCategory->id]);
    $secondProduct = Product::factory()->create(['item_category_id' => $secondCategory->id]);
    $unselectedCategory = ItemCategory::factory()->create();
    $unselectedProduct = Product::factory()->create(['item_category_id' => $unselectedCategory->id]);

    $this->post(route('admin.item-categories.bulk'), [
        'action' => 'archive',
        'ids' => [$firstCategory->id, $secondCategory->id],
    ])->assertRedirect();

    expect($firstCategory->fresh()->status)->toBe('inactive')
        ->and($secondCategory->fresh()->status)->toBe('inactive')
        ->and($firstProduct->fresh()->status)->toBe('inactive')
        ->and($secondProduct->fresh()->status)->toBe('inactive')
        ->and($unselectedCategory->fresh()->status)->toBe('active')
        ->and($unselectedProduct->fresh()->status)->toBe('active');
});

it('deletes a game category and deactivates its linked item categories and products', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $otherGameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();
    $gameCategory->itemCategories()->attach($itemCategory);
    $otherGameCategory->itemCategories()->attach($itemCategory);
    $gameProduct = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
    ]);
    $sharedItemProduct = Product::factory()->create([
        'game_category_id' => $otherGameCategory->id,
        'item_category_id' => $itemCategory->id,
    ]);
    $unrelatedProduct = Product::factory()->create(['game_category_id' => $otherGameCategory->id]);

    $this->delete(route('admin.game-categories.destroy', $gameCategory))
        ->assertRedirectToRoute('admin.game-categories.index');

    $this->assertDatabaseMissing('game_categories', ['id' => $gameCategory->id]);
    $this->assertDatabaseMissing('game_category_item_category', [
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
    ]);
    expect($itemCategory->fresh()->status)->toBe('inactive');
    expect($gameProduct->fresh()->status)->toBe('inactive');
    expect($gameProduct->fresh()->game_category_id)->toBeNull();
    expect($sharedItemProduct->fresh()->status)->toBe('inactive');
    expect($sharedItemProduct->fresh()->game_category_id)->toBe($otherGameCategory->id);
    expect($unrelatedProduct->fresh()->status)->toBe('active');
});

it('bulk deletes game categories and deactivates products before detaching their game references', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();
    $gameCategory->itemCategories()->attach($itemCategory);
    $product = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
    ]);
    $unrelatedGame = GameCategory::factory()->create();
    $unrelatedProduct = Product::factory()->create(['game_category_id' => $unrelatedGame->id]);

    $this->post(route('admin.game-categories.bulk'), [
        'action' => 'delete',
        'ids' => [$gameCategory->id],
    ])->assertRedirect();

    $this->assertDatabaseMissing('game_categories', ['id' => $gameCategory->id]);
    expect($itemCategory->fresh()->status)->toBe('inactive');
    expect($product->fresh()->status)->toBe('inactive');
    expect($product->fresh()->game_category_id)->toBeNull();
    expect($unrelatedProduct->fresh()->status)->toBe('active');
});

it('deletes an item category and deactivates its product without deleting order history', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $itemCategory = ItemCategory::factory()->create();
    $product = Product::factory()->create(['item_category_id' => $itemCategory->id]);
    $variant = $product->variants()->firstOrFail();
    $order = Order::query()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'total' => $variant->price,
    ]);
    $orderItem = $order->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'product_name' => $product->name,
        'variant_name' => $variant->name,
        'quantity' => 1,
        'unit_price' => $variant->price,
    ]);

    $this->delete(route('admin.item-categories.destroy', $itemCategory))
        ->assertRedirectToRoute('admin.item-categories.index');

    $this->assertDatabaseMissing('item_categories', ['id' => $itemCategory->id]);
    expect($product->fresh()->status)->toBe('inactive');
    expect($product->fresh()->item_category_id)->toBeNull();
    $this->assertDatabaseHas('order_items', ['id' => $orderItem->id, 'product_id' => $product->id]);
});

it('bulk deletes item categories and deactivates linked products', function () {
    $this->actingAs(User::factory()->create());
    $firstItemCategory = ItemCategory::factory()->create();
    $secondItemCategory = ItemCategory::factory()->create();
    $firstProduct = Product::factory()->create(['item_category_id' => $firstItemCategory->id]);
    $secondProduct = Product::factory()->create(['item_category_id' => $secondItemCategory->id]);
    $unselectedItemCategory = ItemCategory::factory()->create();
    $unselectedProduct = Product::factory()->create(['item_category_id' => $unselectedItemCategory->id]);

    $this->post(route('admin.item-categories.bulk'), [
        'action' => 'delete',
        'ids' => [$firstItemCategory->id, $secondItemCategory->id],
    ])->assertRedirect();

    $this->assertDatabaseMissing('item_categories', ['id' => $firstItemCategory->id]);
    $this->assertDatabaseMissing('item_categories', ['id' => $secondItemCategory->id]);
    expect($firstProduct->fresh()->status)->toBe('inactive');
    expect($firstProduct->fresh()->item_category_id)->toBeNull();
    expect($secondProduct->fresh()->status)->toBe('inactive');
    expect($secondProduct->fresh()->item_category_id)->toBeNull();
    expect($unselectedItemCategory->fresh()->status)->toBe('active');
    expect($unselectedProduct->fresh()->status)->toBe('active');
    expect($unselectedProduct->fresh()->item_category_id)->toBe($unselectedItemCategory->id);
});

it('allows viewing and editing a product after its game category has been deleted', function () {
    $this->actingAs(User::factory()->create());
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();
    $gameCategory->itemCategories()->attach($itemCategory);
    $product = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
        'status' => 'inactive',
    ]);

    $this->delete(route('admin.game-categories.destroy', $gameCategory))->assertRedirect();

    $this->get(route('admin.products.edit', $product))
        ->assertOk()
        ->assertSee('Choose a game', false);
    $this->get(route('admin.products.show', $product))
        ->assertOk()
        ->assertSee('Game category')
        ->assertSee('—');
});
