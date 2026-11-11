<?php

use App\Jobs\ProcessTelegramUpdate;
use App\Jobs\PublishTelegramAccountListing;
use App\Models\GameCategory;
use App\Models\ItemCategory;
use App\Models\Product;
use App\Models\ProductCurrencyRate;
use App\Models\TelegramAccountDraft;
use App\Models\TelegramAccountItem;
use App\Models\TelegramUpdate;
use App\Services\AccountListingFormatter;
use App\Services\TelegramAccountPostingBot;
use App\Services\TelegramBotApi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('authenticates Telegram webhooks and queues each authorized update once', function () {
    config([
        'services.telegram.webhook_secret' => 'webhook-secret',
        'services.telegram.admin_ids' => ['12345'],
    ]);
    Queue::fake([ProcessTelegramUpdate::class]);
    $payload = [
        'update_id' => 871234,
        'message' => [
            'from' => ['id' => 12345],
            'chat' => ['id' => 12345, 'type' => 'private'],
            'text' => '/new',
        ],
    ];

    $this->postJson(route('telegram.webhook'), $payload)
        ->assertForbidden();

    $this->postJson(route('telegram.webhook'), $payload, [
        'X-Telegram-Bot-Api-Secret-Token' => 'webhook-secret',
    ])->assertOk();

    $this->postJson(route('telegram.webhook'), $payload, [
        'X-Telegram-Bot-Api-Secret-Token' => 'webhook-secret',
    ])->assertOk();

    $this->assertDatabaseHas('telegram_updates', [
        'telegram_update_id' => 871234,
        'status' => 'pending',
    ]);
    Queue::assertPushed(ProcessTelegramUpdate::class, 1);
});

it('does not queue webhook updates from non-admin Telegram users', function () {
    config([
        'services.telegram.webhook_secret' => 'webhook-secret',
        'services.telegram.admin_ids' => ['12345'],
    ]);
    Queue::fake([ProcessTelegramUpdate::class]);

    $this->postJson(route('telegram.webhook'), [
        'update_id' => 871235,
        'message' => [
            'from' => ['id' => 99999],
            'chat' => ['id' => 99999, 'type' => 'private'],
            'text' => '/new',
        ],
    ], [
        'X-Telegram-Bot-Api-Secret-Token' => 'webhook-secret',
    ])->assertOk();

    $this->assertDatabaseMissing('telegram_updates', ['telegram_update_id' => 871235]);
    Queue::assertNothingPushed();
});

it('starts an admin draft with the requested defaults', function () {
    config([
        'services.telegram.bot_token' => 'test-token',
        'services.telegram.admin_ids' => ['12345'],
        'services.telegram.post_chat' => '@LunaXStore',
        'services.telegram.post_instagram' => 'jokibloxfruit.roblox',
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]]),
    ]);
    $update = TelegramUpdate::query()->create([
        'telegram_update_id' => 871236,
        'payload' => [
            'message' => [
                'message_id' => 1,
                'from' => ['id' => 12345],
                'chat' => ['id' => 12345, 'type' => 'private'],
                'text' => '/new',
            ],
        ],
    ]);

    app(TelegramAccountPostingBot::class)->handle($update);

    $draft = TelegramAccountDraft::query()->sole();
    expect($draft->step)->toBe('photos')
        ->and($draft->data['stat_refund'])->toBe(2)
        ->and($draft->data['race'])->toBe('V3 HUMAN')
        ->and($draft->data['haki'])->toBe('SKY JUMP, BODY, FLASH STEP')
        ->and($draft->data['post_chat'])->toBe('@LunaXStore')
        ->and($draft->data['post_instagram'])->toBe('jokibloxfruit.roblox');

    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/sendMessage')
        && (string) $request['chat_id'] === '12345'
        && str_contains($request['text'], '/done'));
});

it('converts an entered price in thousands and shows the preview with defaults', function () {
    config(['services.telegram.admin_ids' => ['12345']]);
    config(['services.telegram.bot_token' => 'test-token']);
    Http::preventStrayRequests();
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 2]]),
    ]);
    ProductCurrencyRate::current()->update([
        'idr_per_myr' => 4000,
        'idr_per_usd' => 15000,
    ]);
    $draft = TelegramAccountDraft::query()->create([
        'telegram_user_id' => '12345',
        'chat_id' => '12345',
        'status' => 'active',
        'step' => 'price',
        'data' => [
            'username' => 'Dearamko',
            'level' => '2805',
            'items' => [
                ['category' => 'Fruit', 'name' => '🦊Kitsune'],
                ['category' => 'Sword', 'name' => '⚓ Shark Anchor'],
            ],
            'haki' => 'SKY JUMP, BODY, FLASH STEP',
            'race' => 'V3 HUMAN',
            'awaken' => '-',
            'permanent' => '-',
            'gamepass' => '-',
            'stat_refund' => 2,
            'post_chat' => '',
            'post_instagram' => '',
        ],
        'images' => ['products/one.jpg'],
        'processed_message_ids' => [],
        'last_update_id' => 871236,
    ]);
    $update = TelegramUpdate::query()->create([
        'telegram_update_id' => 871237,
        'payload' => [
            'message' => [
                'message_id' => 2,
                'from' => ['id' => 12345],
                'chat' => ['id' => 12345, 'type' => 'private'],
                'text' => '50',
            ],
        ],
    ]);

    app(TelegramAccountPostingBot::class)->handle($update);

    $draft->refresh();
    expect($draft->step)->toBe('preview')
        ->and($draft->data['price_idr'])->toBe(50000);

    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/sendMessage')
        && str_contains($request['text'], '50K | RM 12.50 | $4.00')
        && str_contains($request['text'], 'SKY JUMP, BODY, FLASH STEP')
        && str_contains($request['text'], '𝙎𝙏𝘼𝙏 𝙍𝙀𝙁𝙐𝙉𝘿 : 2'));
});

it('saves new catalog items and selects multiple items for a listing', function () {
    config([
        'services.telegram.admin_ids' => ['12345'],
        'services.telegram.bot_token' => 'test-token',
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 4]]),
    ]);
    $kitsune = TelegramAccountItem::query()->create([
        'category' => 'Fruit',
        'name' => 'Kitsune',
        'emoji' => '🦊',
    ]);
    $anchor = TelegramAccountItem::query()->create([
        'category' => 'Sword',
        'name' => 'Shark Anchor',
        'emoji' => '⚓',
    ]);
    $draft = TelegramAccountDraft::query()->create([
        'telegram_user_id' => '12345',
        'chat_id' => '12345',
        'status' => 'active',
        'step' => 'items',
        'data' => [
            'category_options' => ['fruit' => 'Fruit', 'sword' => 'Sword'],
            'selected_categories' => ['fruit', 'sword'],
            'selected_item_ids' => [],
            'items' => [],
        ],
        'images' => ['products/one.jpg'],
        'processed_message_ids' => [],
        'last_update_id' => 1,
    ]);
    $processUpdate = function (int $updateId, array $update): void {
        $telegramUpdate = TelegramUpdate::query()->create([
            'telegram_update_id' => $updateId,
            'payload' => $update,
        ]);

        app(TelegramAccountPostingBot::class)->handle($telegramUpdate);
    };

    $processUpdate(2, ['callback_query' => [
        'id' => 'select-kitsune',
        'from' => ['id' => 12345],
        'message' => ['chat' => ['id' => 12345, 'type' => 'private']],
        'data' => 'item:select:'.$kitsune->id,
    ]]);
    $processUpdate(3, ['callback_query' => [
        'id' => 'select-anchor',
        'from' => ['id' => 12345],
        'message' => ['chat' => ['id' => 12345, 'type' => 'private']],
        'data' => 'item:select:'.$anchor->id,
    ]]);
    $processUpdate(4, ['callback_query' => [
        'id' => 'add-fruit',
        'from' => ['id' => 12345],
        'message' => ['chat' => ['id' => 12345, 'type' => 'private']],
        'data' => 'item:add:fruit',
    ]]);
    $processUpdate(5, ['message' => [
        'from' => ['id' => 12345],
        'chat' => ['id' => 12345, 'type' => 'private'],
        'text' => 'Dough King',
    ]]);
    $processUpdate(6, ['message' => [
        'from' => ['id' => 12345],
        'chat' => ['id' => 12345, 'type' => 'private'],
        'text' => '/skip',
    ]]);
    $processUpdate(7, ['callback_query' => [
        'id' => 'finish-items',
        'from' => ['id' => 12345],
        'message' => ['chat' => ['id' => 12345, 'type' => 'private']],
        'data' => 'item:done',
    ]]);

    expect($draft->fresh()->step)->toBe('haki')
        ->and($draft->fresh()->data['items'])->toBe([
            ['category' => 'Fruit', 'name' => '🦊Kitsune'],
            ['category' => 'Sword', 'name' => '⚓Shark Anchor'],
            ['category' => 'Fruit', 'name' => 'Dough King'],
        ]);
    $this->assertDatabaseHas('telegram_account_items', [
        'category' => 'Fruit',
        'name' => 'Dough King',
        'emoji' => null,
    ]);
});

it('allows a saved catalog item to be selected in a later draft', function () {
    config([
        'services.telegram.admin_ids' => ['12345'],
        'services.telegram.bot_token' => 'test-token',
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 5]]),
    ]);
    $savedItem = TelegramAccountItem::query()->create([
        'category' => 'Race',
        'name' => 'Human',
        'emoji' => '🧍',
    ]);
    $draft = TelegramAccountDraft::query()->create([
        'telegram_user_id' => '12345',
        'chat_id' => '12345',
        'status' => 'active',
        'step' => 'items',
        'data' => [
            'category_options' => ['race' => 'Race'],
            'selected_categories' => ['race'],
            'selected_item_ids' => [],
            'items' => [],
        ],
        'images' => ['products/one.jpg'],
        'processed_message_ids' => [],
        'last_update_id' => 10,
    ]);
    $telegramUpdate = TelegramUpdate::query()->create([
        'telegram_update_id' => 11,
        'payload' => ['callback_query' => [
            'id' => 'reuse-item',
            'from' => ['id' => 12345],
            'message' => ['chat' => ['id' => 12345, 'type' => 'private']],
            'data' => 'item:select:'.$savedItem->id,
        ]],
    ]);

    app(TelegramAccountPostingBot::class)->handle($telegramUpdate);

    $telegramUpdate = TelegramUpdate::query()->create([
        'telegram_update_id' => 12,
        'payload' => ['callback_query' => [
            'id' => 'finish-reused-item',
            'from' => ['id' => 12345],
            'message' => ['chat' => ['id' => 12345, 'type' => 'private']],
            'data' => 'item:done',
        ]],
    ]);
    app(TelegramAccountPostingBot::class)->handle($telegramUpdate);

    expect($draft->fresh()->data['items'])->toBe([
        ['category' => 'Race', 'name' => '🧍Human'],
    ]);

    Http::assertSent(function (Request $request) use ($savedItem): bool {
        $requestData = json_encode($request->data(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return str_ends_with($request->url(), '/sendMessage')
            && str_contains($requestData, 'item:select:'.$savedItem->id)
            && str_contains($requestData, '🧍Human');
    });
});

it('creates an active one-stock product with a gallery when the admin confirms the preview', function () {
    config([
        'services.telegram.bot_token' => 'test-token',
        'services.telegram.admin_ids' => ['12345'],
        'services.telegram.channel_id' => '@store_channel',
    ]);
    Storage::fake('public');
    Storage::disk('public')->put('products/one.jpg', 'photo-one');
    Storage::disk('public')->put('products/two.jpg', 'photo-two');
    Http::preventStrayRequests();
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 3]]),
    ]);
    Queue::fake([PublishTelegramAccountListing::class]);
    GameCategory::factory()->create(['name' => 'Blox Fruit']);
    ItemCategory::factory()->create(['name' => 'Account']);
    ProductCurrencyRate::current()->update(['idr_per_myr' => 4000, 'idr_per_usd' => 15000]);
    $draft = TelegramAccountDraft::query()->create([
        'telegram_user_id' => '12345',
        'chat_id' => '12345',
        'status' => 'active',
        'step' => 'preview',
        'data' => [
            'username' => 'Dearamko',
            'level' => '2805',
            'items' => [['category' => 'Fruit', 'name' => '🦊Kitsune']],
            'price_idr' => 50000,
            'haki' => 'SKY JUMP, BODY, FLASH STEP',
            'race' => 'V3 HUMAN',
            'awaken' => '-',
            'permanent' => '-',
            'gamepass' => '-',
            'stat_refund' => 2,
            'post_chat' => '',
            'post_instagram' => '',
        ],
        'images' => ['products/one.jpg', 'products/two.jpg'],
        'processed_message_ids' => [],
        'last_update_id' => 871237,
    ]);
    $update = TelegramUpdate::query()->create([
        'telegram_update_id' => 871238,
        'payload' => [
            'callback_query' => [
                'id' => 'callback-1',
                'from' => ['id' => 12345],
                'message' => [
                    'message_id' => 3,
                    'chat' => ['id' => 12345, 'type' => 'private'],
                ],
                'data' => 'publish',
            ],
        ],
    ]);

    app(TelegramAccountPostingBot::class)->handle($update);

    $product = Product::query()->sole();
    expect($product->is_account_listing)->toBeTrue()
        ->and($product->images)->toBe(['products/one.jpg', 'products/two.jpg'])
        ->and($product->galleryImages())->toBe(['products/one.jpg', 'products/two.jpg'])
        ->and($product->variants()->sole()->price)->toBe('50000.00')
        ->and($product->variants()->sole()->stock)->toBe(1)
        ->and($draft->fresh()->status)->toBe('publishing')
        ->and($draft->fresh()->data['description'])->toContain('𝙎𝙏𝘼𝙏 𝙍𝙀𝙁𝙐𝙉𝘿 : 2');

    Queue::assertPushed(PublishTelegramAccountListing::class, fn (PublishTelegramAccountListing $job): bool => $job->telegramAccountDraftId === $draft->id);

    $this->get(route('product-details.show', ['product' => $product, 'currency' => 'usd']))
        ->assertOk()
        ->assertSee('data-product-gallery-thumbnail', false)
        ->assertSee(Storage::disk('public')->url('products/two.jpg'), false)
        ->assertSee('$4.00');
});

it('sends product photos and formatted account details to the configured Telegram channel', function () {
    config([
        'services.telegram.bot_token' => 'test-token',
        'services.telegram.channel_id' => '@store_channel',
        'services.telegram.admin_ids' => ['12345'],
    ]);
    Storage::fake('public');
    Storage::disk('public')->put('products/one.jpg', 'photo-one');
    Storage::disk('public')->put('products/two.jpg', 'photo-two');
    Http::preventStrayRequests();
    Http::fake([
        'api.telegram.org/*/sendMediaGroup' => Http::response([
            'ok' => true,
            'result' => [['message_id' => 1], ['message_id' => 2]],
        ]),
        'api.telegram.org/*/sendMessage' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 3],
        ]),
    ]);
    ProductCurrencyRate::current()->update(['idr_per_myr' => 4000, 'idr_per_usd' => 15000]);
    $gameCategory = GameCategory::factory()->create(['name' => 'Blox Fruit']);
    $itemCategory = ItemCategory::factory()->create(['name' => 'Account']);
    $product = Product::factory()
        ->for($gameCategory)
        ->for($itemCategory)
        ->create([
            'name' => 'Blox Fruit Account - Dearamko',
            'image' => 'products/one.jpg',
            'images' => ['products/one.jpg', 'products/two.jpg'],
            'is_account_listing' => true,
        ]);
    $draft = TelegramAccountDraft::query()->create([
        'telegram_user_id' => '12345',
        'chat_id' => '12345',
        'status' => 'publishing',
        'step' => 'preview',
        'data' => [
            'username' => 'Dearamko',
            'level' => '2805',
            'items' => [['category' => 'Fruit', 'name' => '🦊Kitsune']],
            'price_idr' => 50000,
            'haki' => 'SKY JUMP, BODY, FLASH STEP',
            'race' => 'V3 HUMAN',
            'awaken' => '-',
            'permanent' => '-',
            'gamepass' => '-',
            'stat_refund' => 2,
            'post_chat' => '@LunaXStore',
            'post_instagram' => 'jokibloxfruit.roblox',
        ],
        'images' => ['products/one.jpg', 'products/two.jpg'],
        'processed_message_ids' => [],
        'product_id' => $product->id,
    ]);

    app(PublishTelegramAccountListing::class, ['telegramAccountDraftId' => $draft->id])
        ->handle(app(TelegramBotApi::class), app(AccountListingFormatter::class));

    expect($draft->fresh()->status)->toBe('published')
        ->and($draft->fresh()->channel_media_offset)->toBe(2)
        ->and($draft->fresh()->channel_text_sent)->toBeTrue();

    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/sendMediaGroup'));
    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/sendMessage')
        && (string) $request['chat_id'] === '@store_channel'
        && str_contains($request['text'], '$4.00')
        && str_contains($request['text'], 'jokibloxfruit.roblox'));
});

it('escapes free-text values in the Telegram account template', function () {
    $rates = ProductCurrencyRate::current();
    $html = app(AccountListingFormatter::class)->telegramHtml([
        'username' => '<script>alert(1)</script>',
        'level' => '1',
        'items' => [['category' => 'Fruit', 'name' => '<b>unsafe</b>']],
        'price_idr' => 50000,
        'post_chat' => '',
        'post_instagram' => '',
    ], $rates);

    expect($html)
        ->toContain('&lt;script&gt;')
        ->toContain('&lt;b&gt;unsafe&lt;/b&gt;')
        ->not->toContain('<script>alert(1)</script>')
        ->not->toContain('<b>unsafe</b>');
});
