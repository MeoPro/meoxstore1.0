<?php

use App\Models\GameCategory;
use App\Models\ItemCategory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCurrencyRate;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows active products with game category, image, and starting price', function () {
    $gameCategory = GameCategory::factory()->create(['name' => 'Fisch']);
    $itemCategory = ItemCategory::factory()->create(['name' => 'Rod']);
    $product = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
        'name' => 'Golden Rod',
        'status' => 'active',
    ]);
    $product->variants()->firstOrFail()->update(['price' => '40000.00', 'stock' => 3]);
    ProductVariant::factory()->for($product)->create([
        'name' => 'Premium Rod',
        'price' => '65000.00',
        'stock' => 2,
        'maximum_quantity' => 2,
    ]);
    Product::factory()->create(['name' => 'Draft Product', 'status' => 'draft']);

    $this->get(route('shop'))
        ->assertOk()
        ->assertViewHas('products', fn ($products): bool => $products->total() === 1)
        ->assertSee('Golden Rod')
        ->assertSee('Fisch')
        ->assertSee('Rod')
        ->assertSee('Rp 40,000')
        ->assertSee(__('store.stock_count', ['count' => '5']))
        ->assertDontSee(__('store.variants'))
        ->assertDontSee('shop-item-category', false)
        ->assertSee(route('shop', ['game' => $gameCategory->id]), false)
        ->assertSee('data-filter=".item-'.$itemCategory->id.'"', false)
        ->assertSee('data-shop-filter-group="item"', false)
        ->assertDontSee('<details class="shop-item-category-picker">', false)
        ->assertSee('data-shop-currency="idr"', false)
        ->assertSee('data-myr="RM 11.10"', false)
        ->assertSee('data-usd="$2.65"', false)
        ->assertSee(route('product-details.show', $product), false)
        ->assertDontSee('data-cart-add', false)
        ->assertDontSee('shop-add-cart', false)
        ->assertDontSee('shop-buy', false)
        ->assertDontSee('shop-view-product', false)
        ->assertDontSee('Draft Product');
});

it('rounds converted product prices to the nearest five cents', function (string $idrPrice, string $expectedPrice) {
    ProductCurrencyRate::current()->update([
        'idr_per_myr' => '1000.0000',
        'idr_per_usd' => '1000.0000',
    ]);
    $product = Product::factory()->create(['status' => 'active']);
    $product->variants()->firstOrFail()->update(['price' => $idrPrice]);

    $this->get(route('product-details.show', ['product' => $product, 'currency' => 'myr']))
        ->assertSee('RM '.$expectedPrice);

    $this->get(route('product-details.show', ['product' => $product, 'currency' => 'usd']))
        ->assertSee('$'.$expectedPrice);
})->with([
    '11.11 rounds down' => ['11110.00', '11.10'],
    '11.12 rounds down' => ['11120.00', '11.10'],
    '11.13 rounds up' => ['11130.00', '11.15'],
]);

it('shows product badges on images and prices beneath product names', function () {
    $gameCategory = GameCategory::factory()->create(['name' => 'Blox Fruit']);
    $discountedProduct = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'name' => 'Discounted Fruit',
        'badges' => ['discount', 'promo', 'hot'],
        'discount_percent' => 25,
        'is_featured' => true,
        'status' => 'active',
    ]);
    $discountedProduct->variants()->firstOrFail()->update(['price' => '40000.00']);
    $featuredProduct = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'name' => 'Featured Fruit',
        'badges' => ['trending'],
        'is_featured' => true,
        'status' => 'active',
    ]);
    $availableProduct = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'name' => 'Available Fruit',
        'status' => 'active',
    ]);

    $response = $this->get(route('shop'))
        ->assertOk()
        ->assertSee('Diskon 25%')
        ->assertSee('Promo')
        ->assertSee('Trending')
        ->assertSee('Hot')
        ->assertDontSee('Tersedia')
        ->assertSee('data-idr="Rp 30,000"', false)
        ->assertSee('data-idr="Rp 40,000"', false)
        ->assertDontSee('<span class="price"', false);

    expect($response->getContent())->toMatch(
        '/<h4>Discounted Fruit<\/h4>[\s\S]*?<div class="shop-product-price">/',
    );
});

it('shows the most purchased active items on the homepage using completed order quantities', function () {
    $buyer = User::factory()->create();
    $mostPurchased = Product::factory()->create([
        'name' => 'Most Purchased Item',
        'badges' => ['promo', 'discount'],
        'discount_percent' => 10,
        'status' => 'active',
    ]);
    $secondMostPurchased = Product::factory()->create([
        'name' => 'Second Most Purchased Item',
        'status' => 'active',
    ]);
    $pendingOnly = Product::factory()->create([
        'name' => 'Pending Only Item',
        'status' => 'active',
    ]);
    $inactive = Product::factory()->create([
        'name' => 'Inactive Item',
        'status' => 'inactive',
    ]);
    $mostPurchased->variants()->firstOrFail()->update(['price' => '1000.00', 'stock' => 8]);

    $recordOrderItem = function (Product $product, string $status, int $quantity) use ($buyer): void {
        $variant = $product->variants()->firstOrFail();
        $order = Order::query()->create([
            'user_id' => $buyer->id,
            'status' => $status,
            'total' => 500,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_name' => $variant->name,
            'quantity' => $quantity,
            'unit_price' => 50,
        ]);
    };

    $recordOrderItem($mostPurchased, 'completed', 4);
    $recordOrderItem($mostPurchased, 'completed', 3);
    $recordOrderItem($mostPurchased, 'pending', 100);
    $recordOrderItem($secondMostPurchased, 'completed', 5);
    $recordOrderItem($pendingOnly, 'pending', 99);
    $recordOrderItem($inactive, 'completed', 50);

    $response = $this->get(route('home'))
        ->assertViewHas('trendingItems', fn ($items): bool => $items
            ->pluck('name')
            ->all() === ['Most Purchased Item', 'Second Most Purchased Item'])
        ->assertSee('Most Purchased Item')
        ->assertSee('Second Most Purchased Item')
        ->assertSee(__('store.sold_count', ['count' => '7']))
        ->assertSee(__('store.sold_count', ['count' => '5']))
        ->assertSee(__('store.badge_promo'))
        ->assertSee(__('store.discount_badge', ['percent' => '10']))
        ->assertSee('Rp 1,000')
        ->assertSee('Rp 900')
        ->assertSee(__('store.stock_count', ['count' => '8']))
        ->assertSee(__('store.items_sold'))
        ->assertSee(__('store.home_system_online'))
        ->assertSee('62')
        ->assertSee('shop-product-rating')
        ->assertSee('shop-product-stats')
        ->assertDontSee('Pending Only Item')
        ->assertDontSee('Inactive Item')
        ->assertSee(__('store.trending_items_title'));

    expect($response->getContent())->toMatch(
        '/class="home-store-status[^"]*" role="status">.*?<\/div>\s*<div class="home-store-stats"/s',
    );
});

it('shows the count of paid and processing orders in the homepage statistics', function () {
    foreach (['payment_submitted', 'payment_confirmed', 'payment_confirmed'] as $status) {
        Order::query()->create([
            'status' => $status,
            'total' => '1000.00',
        ]);
    }

    Order::query()->create(['status' => 'pending', 'total' => '1000.00']);
    Order::query()->create(['status' => 'completed', 'total' => '1000.00']);
    Order::query()->create(['status' => 'cancelled', 'total' => '1000.00']);

    $this->get(route('home'))
        ->assertViewHas('activeOrderQueueCount', 3)
        ->assertSee(__('store.order_queue'))
        ->assertSee('.home-store-stats { background: rgba(255, 255, 255, .94); border: 1px solid rgba(255, 255, 255, .55); border-radius: 14px; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); margin-top: 12px; max-width: 700px; padding: 14px 12px; }', false)
        ->assertSee('.home-store-stats > div { min-width: 0; padding: 6px 16px; text-align: center; }', false)
        ->assertSee('<strong class="home-store-stat-value">3</strong>', false);
});

it('formats Bobux totals with compact localized units', function () {
    $product = Product::factory()->create();
    $variant = $product->variants()->firstOrFail();
    $order = Order::query()->create([
        'status' => 'completed',
        'total' => '1000.00',
    ]);
    $orderItem = $order->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'product_name' => $product->name,
        'variant_name' => $variant->name,
        'quantity' => 1,
        'unit_price' => '1000.00',
        'robux_amount' => '36000.00',
    ]);

    $this->get(route('home'))
        ->assertSee('36K Bobux');

    $orderItem->update(['robux_amount' => '3000000.00']);

    $this->get(route('home'))
        ->assertSee('3JT Bobux');

    $this->withSession(['locale' => 'en'])
        ->get(route('home'))
        ->assertSee('3M Bobux');
});

it('shows best-selling game and item categories based on completed sales', function () {
    $buyer = User::factory()->create();
    $mostPurchasedGame = GameCategory::factory()->create(['name' => 'Most Purchased Game']);
    $secondMostPurchasedGame = GameCategory::factory()->create(['name' => 'Second Most Purchased Game']);
    $pendingOnlyGame = GameCategory::factory()->create(['name' => 'Pending Only Game']);
    $inactiveGame = GameCategory::factory()->create(['name' => 'Inactive Game', 'status' => 'inactive']);

    $mostPurchasedItemCategory = ItemCategory::factory()->create(['name' => 'Most Purchased Category']);
    $secondMostPurchasedItemCategory = ItemCategory::factory()->create(['name' => 'Second Most Purchased Category']);
    $pendingOnlyItemCategory = ItemCategory::factory()->create(['name' => 'Pending Only Category']);
    $inactiveItemCategory = ItemCategory::factory()->create(['name' => 'Inactive Category', 'status' => 'inactive']);

    $recordOrderItem = function (
        GameCategory $gameCategory,
        ItemCategory $itemCategory,
        string $productName,
        string $orderStatus,
        int $quantity,
        string $productStatus = 'active',
    ) use ($buyer): void {
        $product = Product::factory()->create([
            'game_category_id' => $gameCategory->id,
            'item_category_id' => $itemCategory->id,
            'name' => $productName,
            'status' => $productStatus,
        ]);
        $variant = $product->variants()->firstOrFail();
        $order = Order::query()->create([
            'user_id' => $buyer->id,
            'status' => $orderStatus,
            'total' => 500,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_name' => $variant->name,
            'quantity' => $quantity,
            'unit_price' => 50,
        ]);
    };

    $recordOrderItem($mostPurchasedGame, $mostPurchasedItemCategory, 'Most Purchased Product', 'completed', 5);
    $recordOrderItem($secondMostPurchasedGame, $mostPurchasedItemCategory, 'Second Game Product', 'completed', 4);
    $recordOrderItem($secondMostPurchasedGame, $secondMostPurchasedItemCategory, 'Second Category Product', 'completed', 3);
    $recordOrderItem($pendingOnlyGame, $pendingOnlyItemCategory, 'Pending Product', 'pending', 100);
    $recordOrderItem($inactiveGame, $inactiveItemCategory, 'Inactive Category Product', 'completed', 90);
    $recordOrderItem($mostPurchasedGame, $mostPurchasedItemCategory, 'Inactive Product', 'completed', 80, 'inactive');

    $this->get(route('home'))
        ->assertViewHas('bestSellingGameCategories', fn ($categories): bool => $categories
            ->pluck('name')
            ->all() === ['Second Most Purchased Game', 'Most Purchased Game'])
        ->assertViewHas('bestSellingItemCategories', fn ($categories): bool => $categories
            ->pluck('name')
            ->all() === ['Most Purchased Category', 'Second Most Purchased Category'])
        ->assertSee(__('store.best_selling_game_categories'))
        ->assertSee(__('store.best_selling_item_categories'))
        ->assertSee(route('shop', ['game' => $secondMostPurchasedGame->id]), false)
        ->assertSee(route('shop', ['item_category' => $mostPurchasedItemCategory->id]), false)
        ->assertDontSee('Most Played')
        ->assertSee('Pending Only Game')
        ->assertDontSee('Pending Only Category');
});

it('keeps purchasing actions and account forms on product details instead of product listings', function () {
    foreach (['Mobile Legends' => 'mobile_legends', 'Blox Fruit' => 'blox_fruit', 'Roblox' => 'roblox'] as $name => $purchaseType) {
        $gameCategory = GameCategory::factory()->create(['name' => $name]);
        Product::factory()->create([
            'game_category_id' => $gameCategory->id,
            'status' => 'active',
        ]);
    }

    $this->get(route('shop'))
        ->assertDontSee('data-cart-add', false)
        ->assertDontSee('data-purchase-dialog', false)
        ->assertDontSee('shop-add-cart', false)
        ->assertDontSee('shop-buy', false);

    $product = Product::query()->where('game_category_id', GameCategory::query()->where('name', 'Blox Fruit')->value('id'))->firstOrFail();

    $this->get(route('product-details.show', $product))
        ->assertSee('data-cart-add', false)
        ->assertSee('name="purchase_details[game_username]"', false)
        ->assertSee('name="seller_note"', false)
        ->assertDontSee('name="contact_email"', false)
        ->assertDontSee('name="contact_phone"', false)
        ->assertSee('<nav class="main-nav"', false)
        ->assertDontSee('<div class="page-heading header-text">', false)
        ->assertSee('Beli');
});

it('escapes a custom account field label on the product details page', function () {
    $product = Product::factory()->create([
        'status' => 'active',
        'purchase_fields' => [
            ['key' => 'username', 'label' => '<script>alert("xss")</script>', 'type' => 'text'],
        ],
    ]);

    $this->get(route('product-details.show', $product))
        ->assertSee('&lt;script&gt;alert', false)
        ->assertDontSee('<script>alert("xss")</script>', false);
});

it('selects the cheapest active variant by default on the product details page', function () {
    $product = Product::factory()->create(['status' => 'active']);
    $product->variants()->firstOrFail()->update([
        'name' => 'Expensive variant',
        'price' => '65000.00',
        'stock' => 5,
        'maximum_quantity' => 5,
        'sort_order' => 1,
    ]);
    $cheapestVariant = ProductVariant::factory()->for($product)->create([
        'name' => 'Cheapest variant',
        'price' => '40000.00',
        'stock' => 5,
        'maximum_quantity' => 5,
        'sort_order' => 2,
    ]);

    $response = $this->get(route('product-details.show', $product));

    expect($response->getContent())->toMatch(
        '/<button(?=[^>]*value="'.$cheapestVariant->id.'")(?=[^>]*aria-pressed="true")[^>]*>/',
    );
});

it('shows other item categories on product details and links to that shop filter', function () {
    $currentGame = GameCategory::factory()->create(['name' => 'Blox Fruit']);
    $otherGame = GameCategory::factory()->create(['name' => 'Roblox']);
    $currentCategory = ItemCategory::factory()->create(['name' => 'Fruit']);
    $recommendedCategory = ItemCategory::factory()->create(['name' => 'Accessories']);
    $product = Product::factory()->create([
        'game_category_id' => $currentGame->id,
        'item_category_id' => $currentCategory->id,
        'name' => 'Current Fruit',
        'status' => 'active',
    ]);
    Product::factory()->create([
        'game_category_id' => $otherGame->id,
        'item_category_id' => $recommendedCategory->id,
        'name' => 'Recommended Accessory',
        'status' => 'active',
    ]);

    $this->get(route('product-details.show', ['product' => $product, 'currency' => 'usd']))
        ->assertSee(__('store.explore_other_items'))
        ->assertSee(__('store.recommended_item_categories'))
        ->assertSee('Accessories')
        ->assertSee('item_category='.$recommendedCategory->id, false)
        ->assertDontSee('Explore more products')
        ->assertDontSee('Fruit</h3>', false);
});

it('filters shop products by selected game and keeps item categories as buttons', function () {
    $bloxFruit = GameCategory::factory()->create(['name' => 'Blox Fruit']);
    $growtopia = GameCategory::factory()->create(['name' => 'Growtopia']);
    $fruit = ItemCategory::factory()->create(['name' => 'Fruit']);
    $lock = ItemCategory::factory()->create(['name' => 'Lock']);
    Product::factory()->create([
        'game_category_id' => $bloxFruit->id,
        'item_category_id' => $fruit->id,
        'name' => 'Kitsune',
        'status' => 'active',
    ]);
    Product::factory()->create([
        'game_category_id' => $growtopia->id,
        'item_category_id' => $lock->id,
        'name' => 'World Lock',
        'status' => 'active',
    ]);

    $this->get(route('shop', ['game' => $bloxFruit->id]))
        ->assertOk()
        ->assertViewHas('products', fn ($products): bool => $products->total() === 1)
        ->assertViewHas('selectedGameCategory', fn ($category): bool => $category?->is($bloxFruit) ?? false)
        ->assertSee('aria-current="page">Blox Fruit</span>', false)
        ->assertSee('Kitsune')
        ->assertSee(route('shop', ['game' => $bloxFruit->id]), false)
        ->assertSee('data-shop-filter-group="item"', false)
        ->assertDontSee('<details class="shop-item-category-picker">', false)
        ->assertSee('Buah')
        ->assertDontSee('World Lock')
        ->assertDontSee('data-shop-filter-group="game"', false);
});

it('translates known item category names in the selected storefront language', function () {
    $gameCategory = GameCategory::factory()->create(['name' => 'Blox Fruit']);

    foreach (['Account', 'Akun', 'Fruit', 'Item', 'Joki', 'Permanent', 'Skin', 'Mystery Category'] as $name) {
        $itemCategory = ItemCategory::factory()->create(['name' => $name]);
        Product::factory()->create([
            'game_category_id' => $gameCategory->id,
            'item_category_id' => $itemCategory->id,
            'status' => 'active',
        ]);
    }

    $this->withSession(['locale' => 'en'])
        ->get(route('shop'))
        ->assertSee('Account')
        ->assertSee('Fruit')
        ->assertSee('Item')
        ->assertSee('Leveling Service')
        ->assertSee('Permanent')
        ->assertSee('Skin')
        ->assertSee('Mystery Category');

    $this->withSession(['locale' => 'id'])
        ->get(route('shop'))
        ->assertSee('Akun')
        ->assertSee('Buah')
        ->assertSee('Item')
        ->assertSee('Joki')
        ->assertSee('Permanen')
        ->assertSee('Skin')
        ->assertSee('Mystery Category');

    $this->withSession(['locale' => 'ms'])
        ->get(route('shop'))
        ->assertSee('Akaun')
        ->assertSee('Buah')
        ->assertSee('Item')
        ->assertSee('Servis naik level')
        ->assertSee('Kekal')
        ->assertSee('Skin')
        ->assertSee('Mystery Category');
});

it('filters shop results by a recommended item category', function () {
    $gameCategory = GameCategory::factory()->create(['name' => 'Blox Fruit']);
    $fruitCategory = ItemCategory::factory()->create(['name' => 'Fruit']);
    $accessoryCategory = ItemCategory::factory()->create(['name' => 'Accessories']);
    Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $fruitCategory->id,
        'name' => 'Kitsune Fruit',
        'status' => 'active',
    ]);
    Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $accessoryCategory->id,
        'name' => 'Rare Accessory',
        'status' => 'active',
    ]);

    $this->get(route('shop', ['game' => $gameCategory->id, 'item_category' => $accessoryCategory->id]))
        ->assertViewHas('products', fn ($products): bool => $products->total() === 1)
        ->assertViewHas('selectedItemCategory', fn ($category): bool => $category?->is($accessoryCategory) ?? false)
        ->assertSee('Rare Accessory')
        ->assertDontSee('Kitsune Fruit')
        ->assertSee('class="is_active" href="#!" data-filter=".item-'.$accessoryCategory->id.'"', false);
});

it('keeps the selected currency when switching game categories from the storefront menu', function () {
    $gameCategory = GameCategory::factory()->create(['name' => 'Roblox']);
    Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'status' => 'active',
    ]);

    $this->get(route('shop', ['currency' => 'myr']))
        ->assertSee(route('shop', ['game' => $gameCategory->id, 'currency' => 'myr']))
        ->assertSee(route('games.index', ['currency' => 'myr']));
});

it('shows up to five available games in the storefront menu and links to the full game catalog', function () {
    $featuredGames = collect([
        '99 Nights in the Forest',
        'Blox Fruit',
        'Fisch',
        'Growtopia',
        'Roblox',
        'Zelda',
    ])->map(function (string $name): GameCategory {
        $gameCategory = GameCategory::factory()->create(['name' => $name]);
        Product::factory()->create([
            'game_category_id' => $gameCategory->id,
            'status' => 'active',
        ]);

        return $gameCategory;
    });

    $unavailableCategory = GameCategory::factory()->create(['name' => 'Unavailable Game']);
    $unavailableProduct = Product::factory()->create([
        'game_category_id' => $unavailableCategory->id,
        'status' => 'active',
    ]);
    $unavailableProduct->variants()->update(['status' => 'inactive']);

    $buyer = User::factory()->create();
    $recordSale = function (string $gameName, string $orderStatus, int $quantity) use ($featuredGames, $buyer): void {
        $gameCategory = $featuredGames->firstWhere('name', $gameName);
        $product = $gameCategory->products()->firstOrFail();
        $variant = $product->variants()->firstOrFail();
        $order = Order::query()->create([
            'user_id' => $buyer->id,
            'status' => $orderStatus,
            'total' => 500,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_name' => $variant->name,
            'quantity' => $quantity,
            'unit_price' => 50,
        ]);
    };
    $recordSale('Zelda', 'completed', 20);
    $recordSale('Fisch', 'completed', 15);
    $recordSale('Blox Fruit', 'completed', 10);
    $recordSale('Growtopia', 'completed', 5);
    $recordSale('99 Nights in the Forest', 'completed', 1);
    $recordSale('Roblox', 'pending', 100);

    $homeResponse = $this->get(route('home'));
    $homeResponse
        ->assertOk()
        ->assertSee('<title>Meoxstore | Toko Game</title>', false)
        ->assertSee(asset('images/logo.png'), false)
        ->assertDontSee('LUGX Gaming Company')
        ->assertSee('Our Shop')
        ->assertSee('data-storefront-shop-toggle', false)
        ->assertSee('class="storefront-shop-toggle"', false)
        ->assertSee('Game lainnya')
        ->assertSee(route('games.index'), false)
        ->assertSee(route('shop', ['game' => $featuredGames[0]->id]), false)
        ->assertDontSee('Roblox')
        ->assertDontSee('Unavailable Game');
    $this->assertSame(
        1,
        preg_match(
            '/<ul class="storefront-shop-dropdown" id="storefront-shop-dropdown">(.*?)<\/ul>/s',
            $homeResponse->getContent(),
            $dropdownMatches,
        ),
    );
    preg_match_all(
        '/<a\b[^>]*data-shop-category-link[^>]*>\s*(.*?)\s*<\/a>/s',
        $dropdownMatches[1],
        $gameLinks,
    );
    expect(array_map('trim', $gameLinks[1]))->toBe([
        'Zelda',
        'Fisch',
        'Blox Fruit',
        'Growtopia',
        '99 Nights in the Forest',
    ]);
    $this->assertMatchesRegularExpression(
        '/<a href="'.preg_quote(route('games.index'), '/').'".*?>\s*'.preg_quote(__('store.shop'), '/').'\s*<\/a>/s',
        $homeResponse->getContent(),
    );
});

it('shows storefront help links instead of placeholder contact information', function () {
    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('Bantuan Meoxstore')
        ->assertSee('Pesanan dan pembayaran')
        ->assertSee('Informasi kontak')
        ->assertSee(route('shop'), false)
        ->assertSee(route('login'), false)
        ->assertSee(asset('images/logo.png'), false)
        ->assertSee('Sunny Isles Beach')
        ->assertSee(config('store.admin_email'))
        ->assertDontSee('lugx@contact.com')
        ->assertSee('google.com/maps')
        ->assertSee('id="contact-message"', false)
        ->assertSee('name="message"', false);
});

it('lists all available games with an image and a link to their shop products', function () {
    $bloxFruit = GameCategory::factory()->create([
        'name' => 'Blox Fruit',
        'image' => 'categories/blox-fruit.webp',
    ]);
    Product::factory()->create([
        'game_category_id' => $bloxFruit->id,
        'name' => 'Kitsune',
        'status' => 'active',
    ]);
    Product::factory()->create([
        'game_category_id' => $bloxFruit->id,
        'name' => 'Dragon',
        'status' => 'active',
    ]);
    $roblox = GameCategory::factory()->create(['name' => 'Roblox']);
    Product::factory()->create([
        'game_category_id' => $roblox->id,
        'name' => 'Roblox Account',
        'image' => 'products/roblox.webp',
        'status' => 'active',
    ]);
    $fisch = GameCategory::factory()->create(['name' => 'Fisch']);
    Product::factory()->create([
        'game_category_id' => $fisch->id,
        'name' => 'Fisch Rod',
        'status' => 'active',
    ]);
    $unavailableGame = GameCategory::factory()->create(['name' => 'Unavailable Game']);
    $unavailableProduct = Product::factory()->create([
        'game_category_id' => $unavailableGame->id,
        'status' => 'active',
    ]);
    $unavailableProduct->variants()->update(['status' => 'inactive']);

    $this->get(route('games.index'))
        ->assertOk()
        ->assertSee('Jelajahi semua game')
        ->assertSee('Blox Fruit')
        ->assertSee('Roblox')
        ->assertSee('Fisch')
        ->assertSee('2 produk tersedia')
        ->assertSee('/storage/categories/blox-fruit.webp')
        ->assertSee('/storage/products/roblox.webp')
        ->assertSee(route('shop', ['game' => $fisch->id]), false)
        ->assertDontSee('Unavailable Game');
});

it('keeps a selected currency when choosing a game from the full game catalog', function () {
    $gameCategory = GameCategory::factory()->create(['name' => 'Roblox']);
    Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'status' => 'active',
    ]);

    $this->get(route('games.index', ['currency' => 'usd']))
        ->assertSee(route('shop', ['game' => $gameCategory->id, 'currency' => 'usd']));
});

it('hides active products that have no active variants', function () {
    $product = Product::factory()->create([
        'name' => 'Unavailable Product',
        'status' => 'active',
    ]);
    $product->variants()->update(['status' => 'inactive']);

    $this->get(route('shop'))
        ->assertOk()
        ->assertSee('Produk belum tersedia')
        ->assertDontSee('Unavailable Product');
});

it('shows product details with active variants and safely rendered description', function () {
    $gameCategory = GameCategory::factory()->create(['name' => 'Blox Fruit']);
    $itemCategory = ItemCategory::factory()->create(['name' => 'Fruit']);
    $product = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
        'name' => 'Kitsune Fruit',
        'description' => '<script>alert("xss")</script>Rare in-game fruit.',
        'status' => 'active',
    ]);
    $product->variants()->firstOrFail()->update([
        'name' => 'Physical',
        'price' => '65000.00',
        'stock' => 4,
        'sort_order' => 2,
    ]);
    $cheapestVariant = ProductVariant::factory()->for($product)->create([
        'name' => 'Permanent',
        'price' => '40000.00',
        'stock' => 2,
        'sort_order' => 1,
    ]);
    ProductVariant::factory()->for($product)->create([
        'name' => 'Hidden variant',
        'status' => 'inactive',
    ]);

    $response = $this->get(route('product-details.show', $product));

    $response
        ->assertOk()
        ->assertSee('<nav class="product-detail-breadcrumb"', false)
        ->assertSee('aria-current="page">Kitsune Fruit</li>', false)
        ->assertSee('href="'.route('shop', ['game' => $gameCategory->id]).'">Blox Fruit</a>', false)
        ->assertSee('Kitsune Fruit')
        ->assertSee('Blox Fruit')
        ->assertDontSee('Kategori item')
        ->assertSee('Rare in-game fruit.')
        ->assertSee('Physical')
        ->assertSee('Permanent')
        ->assertSee('data-cart-add', false)
        ->assertSee('name="purchase_details[game_username]"', false)
        ->assertDontSee('name="contact_email"', false)
        ->assertDontSee('name="contact_phone"', false)
        ->assertSee('fa-shopping-cart', false)
        ->assertSee('data-stock-error-label="Stok tidak cukup."', false)
        ->assertSee('Beli')
        ->assertSee('Rp 40,000')
        ->assertSee('data-product-variant', false)
        ->assertDontSee('<select', false)
        ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false)
        ->assertDontSee('<script>alert("xss")</script>', false)
        ->assertDontSee('Hidden variant');

    expect($response->getContent())->toMatch(
        '/<button(?=[^>]*value="'.$cheapestVariant->id.'")(?=[^>]*aria-pressed="true")[^>]*>/',
    );
});

it('redirects the default product details page to the first active product', function () {
    $product = Product::factory()->create([
        'name' => 'Featured Fruit',
        'status' => 'active',
        'is_featured' => true,
    ]);

    $this->get(route('product-details'))
        ->assertRedirect(route('product-details.show', $product));
});

it('does not show inactive products or products without active variants', function () {
    $inactiveProduct = Product::factory()->create([
        'name' => 'Inactive Product',
        'status' => 'inactive',
    ]);
    $unavailableProduct = Product::factory()->create([
        'name' => 'Unavailable Product',
        'status' => 'active',
    ]);
    $unavailableProduct->variants()->update(['status' => 'inactive']);

    $this->get(route('product-details.show', $inactiveProduct))
        ->assertNotFound();

    $this->get(route('product-details.show', $unavailableProduct))
        ->assertNotFound();
});

it('shows same-item recommendations, other games, and uploaded category images', function () {
    $bloxFruit = GameCategory::factory()->create(['name' => 'Blox Fruit']);
    $growtopia = GameCategory::factory()->create([
        'name' => 'Growtopia',
        'image' => 'categories/growtopia.webp',
    ]);
    $fruit = ItemCategory::factory()->create([
        'name' => 'Fruit',
        'image' => 'categories/fruit.webp',
    ]);
    $item = ItemCategory::factory()->create(['name' => 'Item']);
    $currentProduct = Product::factory()->create([
        'game_category_id' => $bloxFruit->id,
        'item_category_id' => $fruit->id,
        'name' => 'Kitsune',
        'status' => 'active',
    ]);
    Product::factory()->create([
        'game_category_id' => $bloxFruit->id,
        'item_category_id' => $fruit->id,
        'name' => 'Dragon Fruit',
        'status' => 'active',
    ]);
    Product::factory()->create([
        'game_category_id' => $growtopia->id,
        'item_category_id' => $item->id,
        'name' => 'World Lock',
        'status' => 'active',
    ]);

    $this->get(route('product-details.show', $currentProduct))
        ->assertOk()
        ->assertSee('Rekomendasi Buah lainnya')
        ->assertSee('Dragon Fruit')
        ->assertSee('Rekomendasi game lainnya')
        ->assertSee('Growtopia')
        ->assertSee('/storage/categories/fruit.webp')
        ->assertSee('/storage/categories/growtopia.webp');
});

it('shows up to five same-item product recommendations', function () {
    $gameCategory = GameCategory::factory()->create();
    $itemCategory = ItemCategory::factory()->create();
    $currentProduct = Product::factory()->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
        'status' => 'active',
    ]);
    Product::factory()->count(6)->create([
        'game_category_id' => $gameCategory->id,
        'item_category_id' => $itemCategory->id,
        'status' => 'active',
    ]);

    $this->get(route('product-details.show', $currentProduct))
        ->assertViewHas('recommendedProducts', fn ($products): bool => $products->count() === 5)
        ->assertSee('product-recommendation-items');
});
