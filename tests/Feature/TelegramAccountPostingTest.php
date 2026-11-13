<?php

use App\Jobs\ProcessTelegramUpdate;
use App\Jobs\PublishTelegramAccountListing;
use App\Models\GameCategory;
use App\Models\ItemCategory;
use App\Models\Product;
use App\Models\ProductCurrencyRate;
use App\Models\StoreSetting;
use App\Models\TelegramAccountDraft;
use App\Models\TelegramAccountItem;
use App\Models\TelegramUpdate;
use App\Services\AccountListingFormatter;
use App\Services\TelegramAccountPostingBot;
use App\Services\TelegramBotApi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
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
    $gameCategories = collect(['Blox Fruit', 'Fisch', 'Growtopia', 'Roblox'])
        ->map(fn (string $name): GameCategory => GameCategory::factory()->create(['name' => $name]));
    GameCategory::factory()->create(['name' => 'Disabled Game', 'status' => 'inactive']);
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
    expect($draft->step)->toBe('game')
        ->and($draft->data['stat_refund'])->toBe(2)
        ->and($draft->data['race'])->toBe('V3 HUMAN')
        ->and($draft->data['haki'])->toBe('SKY JUMP, BODY, FLASH STEP')
        ->and($draft->data['post_chat'])->toBe('@LunaXStore')
        ->and($draft->data['post_instagram'])->toBe('jokibloxfruit.roblox');

    Http::assertSent(function (Request $request) use ($gameCategories): bool {
        $keyboard = json_encode($request['reply_markup'], JSON_UNESCAPED_UNICODE);

        return str_ends_with($request->url(), '/sendMessage')
            && (string) $request['chat_id'] === '12345'
            && str_contains($request['text'], 'Pilih kategori game')
            && collect($gameCategories)->every(fn (GameCategory $gameCategory): bool => str_contains($keyboard, 'game:'.$gameCategory->id))
            && ! str_contains($keyboard, 'Disabled Game');
    });
});

it('records the selected active game before asking for account photos', function () {
    config([
        'services.telegram.admin_ids' => ['12345'],
        'services.telegram.bot_token' => 'test-token',
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 2]]),
    ]);
    $gameCategory = GameCategory::factory()->create(['name' => 'Growtopia']);
    $draft = TelegramAccountDraft::query()->create([
        'telegram_user_id' => '12345',
        'chat_id' => '12345',
        'status' => 'active',
        'step' => 'game',
        'data' => [],
        'images' => [],
        'processed_message_ids' => [],
        'last_update_id' => 871236,
    ]);
    $update = TelegramUpdate::query()->create([
        'telegram_update_id' => 871237,
        'payload' => [
            'callback_query' => [
                'id' => 'game-callback',
                'from' => ['id' => 12345],
                'message' => [
                    'message_id' => 2,
                    'chat' => ['id' => 12345, 'type' => 'private'],
                ],
                'data' => 'game:'.$gameCategory->id,
            ],
        ],
    ]);

    app(TelegramAccountPostingBot::class)->handle($update);

    expect($draft->fresh()->step)->toBe('photos')
        ->and($draft->fresh()->data['game_category_id'])->toBe($gameCategory->id)
        ->and($draft->fresh()->data['game_category_name'])->toBe('Growtopia')
        ->and($draft->fresh()->data['account_template'])->toBe('generic');
    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/sendMessage')
        && str_contains($request['text'], 'Game dipilih: Growtopia.')
        && str_contains($request['text'], 'Kirim foto akun'));
});

it('asks for a custom website title after the account name', function () {
    config([
        'services.telegram.admin_ids' => ['12345'],
        'services.telegram.bot_token' => 'test-token',
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 2]]),
    ]);
    $draft = TelegramAccountDraft::query()->create([
        'telegram_user_id' => '12345',
        'chat_id' => '12345',
        'status' => 'active',
        'step' => 'username',
        'data' => ['username' => ''],
        'images' => ['products/one.jpg'],
        'processed_message_ids' => [],
        'last_update_id' => 871236,
    ]);
    $update = TelegramUpdate::query()->create([
        'telegram_update_id' => 871237,
        'payload' => ['message' => [
            'from' => ['id' => 12345],
            'chat' => ['id' => 12345, 'type' => 'private'],
            'text' => 'Wetoxxa',
        ]],
    ]);

    app(TelegramAccountPostingBot::class)->handle($update);

    expect($draft->fresh()->step)->toBe('website_title')
        ->and($draft->fresh()->data['website_title'])->toBe('Blox Fruit Account - Wetoxxa');
    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/sendMessage')
        && str_contains($request['text'], 'judul produk untuk website')
        && str_contains($request['text'], '/skip'));
});

it('uses a custom website title or the generated title when skipped', function (string $title, string $expectedTitle) {
    config([
        'services.telegram.admin_ids' => ['12345'],
        'services.telegram.bot_token' => 'test-token',
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 2]]),
    ]);
    $draft = TelegramAccountDraft::query()->create([
        'telegram_user_id' => '12345',
        'chat_id' => '12345',
        'status' => 'active',
        'step' => 'website_title',
        'data' => [
            'username' => 'Wetoxxa',
            'website_title' => 'Blox Fruit Account - Wetoxxa',
        ],
        'images' => ['products/one.jpg'],
        'processed_message_ids' => [],
        'last_update_id' => 871237,
    ]);
    $update = TelegramUpdate::query()->create([
        'telegram_update_id' => 871238,
        'payload' => ['message' => [
            'from' => ['id' => 12345],
            'chat' => ['id' => 12345, 'type' => 'private'],
            'text' => $title,
        ]],
    ]);

    app(TelegramAccountPostingBot::class)->handle($update);

    expect($draft->fresh()->step)->toBe('level')
        ->and($draft->fresh()->data['website_title'])->toBe($expectedTitle);
})->with([
    'custom title' => ['Premium Blox Fruit Account', 'Premium Blox Fruit Account'],
    'automatic title' => ['/skip', 'Blox Fruit Account - Wetoxxa'],
]);

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
    StoreSetting::current()->update([
        'telegram_account_rates' => [
            'idr_per_myr' => '2500',
            'idr_per_usd' => '10000',
        ],
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
        && str_contains($request['text'], '50K | RM 20.00 | $6.00')
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

    expect($draft->fresh()->step)->toBe('price')
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

it('adds persistent categories and item types from the main menu', function () {
    config([
        'services.telegram.admin_ids' => ['12345'],
        'services.telegram.bot_token' => 'test-token',
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 4]]),
    ]);
    $processUpdate = function (int $updateId, array $update): void {
        $telegramUpdate = TelegramUpdate::query()->create([
            'telegram_update_id' => $updateId,
            'payload' => $update,
        ]);

        app(TelegramAccountPostingBot::class)->handle($telegramUpdate);
    };

    $processUpdate(1, ['message' => [
        'from' => ['id' => 12345],
        'chat' => ['id' => 12345, 'type' => 'private'],
        'text' => '/menu',
    ]]);
    $processUpdate(2, ['callback_query' => [
        'id' => 'open-catalog',
        'from' => ['id' => 12345],
        'message' => ['message_id' => 4, 'chat' => ['id' => 12345, 'type' => 'private']],
        'data' => 'menu:catalog',
    ]]);
    $processUpdate(3, ['callback_query' => [
        'id' => 'add-category',
        'from' => ['id' => 12345],
        'message' => ['message_id' => 4, 'chat' => ['id' => 12345, 'type' => 'private']],
        'data' => 'catalog:add-category',
    ]]);
    $processUpdate(4, ['message' => [
        'from' => ['id' => 12345],
        'chat' => ['id' => 12345, 'type' => 'private'],
        'text' => 'Relics',
    ]]);

    $categories = StoreSetting::current()->telegramAccountCategories();
    $categoryKey = array_search('Relics', $categories, true);
    expect($categoryKey)->toBeString();

    $processUpdate(5, ['callback_query' => [
        'id' => 'open-category',
        'from' => ['id' => 12345],
        'message' => ['message_id' => 4, 'chat' => ['id' => 12345, 'type' => 'private']],
        'data' => 'catalog:category:'.$categoryKey,
    ]]);
    $processUpdate(6, ['callback_query' => [
        'id' => 'add-item',
        'from' => ['id' => 12345],
        'message' => ['message_id' => 4, 'chat' => ['id' => 12345, 'type' => 'private']],
        'data' => 'catalog:add-item:'.$categoryKey,
    ]]);
    $processUpdate(7, ['message' => [
        'from' => ['id' => 12345],
        'chat' => ['id' => 12345, 'type' => 'private'],
        'text' => 'Dark Blade',
    ]]);
    $processUpdate(8, ['message' => [
        'from' => ['id' => 12345],
        'chat' => ['id' => 12345, 'type' => 'private'],
        'text' => '/skip',
    ]]);
    GameCategory::factory()->create(['name' => 'Blox Fruit']);
    $processUpdate(9, ['callback_query' => [
        'id' => 'new-post',
        'from' => ['id' => 12345],
        'message' => ['message_id' => 4, 'chat' => ['id' => 12345, 'type' => 'private']],
        'data' => 'menu:new',
    ]]);

    $this->assertDatabaseHas('telegram_account_items', [
        'category' => 'Relics',
        'name' => 'Dark Blade',
        'emoji' => null,
    ]);
    expect(TelegramAccountDraft::query()->sole()->data['category_options'][$categoryKey])->toBe('Relics');
    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/sendMessage')
        && str_contains(json_encode($request->data()), 'menu:catalog'));
});

it('selects multiple item types from an opened category when posting', function () {
    config([
        'services.telegram.admin_ids' => ['12345'],
        'services.telegram.bot_token' => 'test-token',
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 5]]),
    ]);
    $firstSword = TelegramAccountItem::query()->create([
        'category' => 'Sword',
        'name' => 'Shark Anchor',
        'emoji' => '⚓',
    ]);
    $secondSword = TelegramAccountItem::query()->create([
        'category' => 'Sword',
        'name' => 'Dark Blade',
        'emoji' => '🗡️',
    ]);
    $draft = TelegramAccountDraft::query()->create([
        'telegram_user_id' => '12345',
        'chat_id' => '12345',
        'status' => 'active',
        'step' => 'items',
        'data' => [
            'category_options' => ['sword' => 'Sword'],
            'selected_categories' => ['sword'],
            'selected_item_ids' => [],
            'items' => [],
        ],
        'images' => ['products/one.jpg'],
        'processed_message_ids' => [],
        'last_update_id' => 1,
    ]);
    $processUpdate = function (int $updateId, string $callbackId, string $callbackData): void {
        $telegramUpdate = TelegramUpdate::query()->create([
            'telegram_update_id' => $updateId,
            'payload' => ['callback_query' => [
                'id' => $callbackId,
                'from' => ['id' => 12345],
                'message' => ['chat' => ['id' => 12345, 'type' => 'private']],
                'data' => $callbackData,
            ]],
        ]);

        app(TelegramAccountPostingBot::class)->handle($telegramUpdate);
    };

    $processUpdate(2, 'open-swords', 'item:category:sword');
    $processUpdate(3, 'select-anchor', 'item:select:'.$firstSword->id);
    $processUpdate(4, 'select-dark-blade', 'item:select:'.$secondSword->id);
    $processUpdate(5, 'finish-swords', 'item:done');

    expect($draft->fresh()->step)->toBe('price')
        ->and($draft->fresh()->data['items'])->toBe([
            ['category' => 'Sword', 'name' => '⚓Shark Anchor'],
            ['category' => 'Sword', 'name' => '🗡️Dark Blade'],
        ]);
    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/editMessageText')
        && str_contains($request['text'], 'Pilih satu atau beberapa jenis Sword')
        && str_contains(json_encode($request['reply_markup']), 'Shark Anchor')
        && str_contains(json_encode($request['reply_markup']), 'Dark Blade'));
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
            'id' => 'open-race-category',
            'from' => ['id' => 12345],
            'message' => ['chat' => ['id' => 12345, 'type' => 'private']],
            'data' => 'item:category:race',
        ]],
    ]);

    app(TelegramAccountPostingBot::class)->handle($telegramUpdate);

    $telegramUpdate = TelegramUpdate::query()->create([
        'telegram_update_id' => 12,
        'payload' => ['callback_query' => [
            'id' => 'reuse-item',
            'from' => ['id' => 12345],
            'message' => ['chat' => ['id' => 12345, 'type' => 'private']],
            'data' => 'item:select:'.$savedItem->id,
        ]],
    ]);
    app(TelegramAccountPostingBot::class)->handle($telegramUpdate);

    $telegramUpdate = TelegramUpdate::query()->create([
        'telegram_update_id' => 13,
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
    ])->and($draft->fresh()->step)->toBe('price');

    Http::assertSent(function (Request $request) use ($savedItem): bool {
        $requestData = json_encode($request->data(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return str_ends_with($request->url(), '/sendMessage')
            && str_contains($requestData, 'Pilih satu atau beberapa jenis Race')
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
    StoreSetting::current()->update([
        'telegram_account_rates' => [
            'idr_per_myr' => '2500',
            'idr_per_usd' => '10000',
        ],
    ]);
    $draft = TelegramAccountDraft::query()->create([
        'telegram_user_id' => '12345',
        'chat_id' => '12345',
        'status' => 'active',
        'step' => 'preview',
        'data' => [
            'username' => 'Dearamko',
            'website_title' => 'Dearamko Blox Fruit Account - Premium',
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
    expect($product->name)->toBe('Dearamko Blox Fruit Account - Premium')
        ->and($product->is_account_listing)->toBeTrue()
        ->and($product->images)->toBe(['products/one.jpg', 'products/two.jpg'])
        ->and($product->galleryImages())->toBe(['products/one.jpg', 'products/two.jpg'])
        ->and($product->variants()->sole()->price)->toBe('50000.00')
        ->and($product->variants()->sole()->stock)->toBe(1)
        ->and($draft->fresh()->status)->toBe('publishing')
        ->and($draft->fresh()->data['description'])->toContain('𝙎𝙏𝘼𝙏 𝙍𝙀𝙁𝙐𝙉𝘿 : 2');
    expect($draft->fresh()->data['description'])->toContain('RM 12.50 | $4.00');

    Queue::assertPushed(PublishTelegramAccountListing::class, fn (PublishTelegramAccountListing $job): bool => $job->telegramAccountDraftId === $draft->id);

    $this->get(route('product-details.show', ['product' => $product, 'currency' => 'usd']))
        ->assertOk()
        ->assertSee('data-product-gallery-thumbnail', false)
        ->assertSee(Storage::disk('public')->url('products/two.jpg'), false)
        ->assertSee('$4.00');
});

it('publishes a generic account listing under the game selected in Telegram', function () {
    config([
        'services.telegram.bot_token' => 'test-token',
        'services.telegram.admin_ids' => ['12345'],
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 3]]),
    ]);
    Queue::fake([PublishTelegramAccountListing::class]);
    $gameCategory = GameCategory::factory()->create(['name' => 'Growtopia']);
    $itemCategory = ItemCategory::factory()->create(['name' => 'Account']);
    ProductCurrencyRate::current()->update(['idr_per_myr' => 4000, 'idr_per_usd' => 15000]);
    StoreSetting::current()->update([
        'telegram_account_rates' => [
            'idr_per_myr' => '2500',
            'idr_per_usd' => '10000',
        ],
    ]);
    $draft = TelegramAccountDraft::query()->create([
        'telegram_user_id' => '12345',
        'chat_id' => '12345',
        'status' => 'active',
        'step' => 'preview',
        'data' => [
            'account_template' => 'generic',
            'game_category_id' => $gameCategory->id,
            'game_category_name' => 'Growtopia',
            'username' => 'WorldOwner',
            'website_title' => 'Growtopia Account - WorldOwner',
            'details' => 'World: MEOW | Lock: Diamond Lock',
            'price_idr' => 50000,
            'post_chat' => '',
            'post_instagram' => '',
        ],
        'images' => [],
        'processed_message_ids' => [],
        'last_update_id' => 871237,
    ]);
    $update = TelegramUpdate::query()->create([
        'telegram_update_id' => 871238,
        'payload' => [
            'callback_query' => [
                'id' => 'publish-generic',
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
    expect($product->game_category_id)->toBe($gameCategory->id)
        ->and($product->item_category_id)->toBe($itemCategory->id)
        ->and($product->name)->toBe('Growtopia Account - WorldOwner')
        ->and($product->description)->toContain('World: MEOW | Lock: Diamond Lock')
        ->and($product->description)->not->toContain('BELLY');
    Queue::assertPushed(PublishTelegramAccountListing::class);
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
    $mediaGroupBody = '';
    Http::fake([
        'api.telegram.org/*/sendMediaGroup' => function (Request $request) use (&$mediaGroupBody) {
            $mediaGroupBody = $request->body();

            return Http::response([
                'ok' => true,
                'result' => [['message_id' => 1], ['message_id' => 2]],
            ]);
        },
        'api.telegram.org/*/sendMessage' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 3],
        ]),
    ]);
    ProductCurrencyRate::current()->update(['idr_per_myr' => 4000, 'idr_per_usd' => 15000]);
    StoreSetting::current()->update([
        'telegram_account_rates' => [
            'idr_per_myr' => '2500',
            'idr_per_usd' => '10000',
        ],
    ]);
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

    expect($mediaGroupBody)
        ->toContain('"caption"')
        ->toContain('RM 20.00 | $6.00')
        ->toContain('jokibloxfruit.roblox')
        ->toContain(str_replace('/', '\/', route('product-details.show', $product)));
    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/sendMediaGroup'));
    Http::assertSentCount(2);
});

it('escapes free-text values in the Telegram account template', function () {
    $rates = [
        'idr_per_myr' => '4000.0000',
        'idr_per_usd' => '15000.0000',
    ];
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

it('escapes generic account details in Telegram listings', function () {
    $html = app(AccountListingFormatter::class)->telegramHtml([
        'account_template' => 'generic',
        'game_category_name' => 'Growtopia',
        'website_title' => 'Growtopia <img src=x>',
        'username' => 'WorldOwner',
        'details' => '<script>alert(1)</script>',
        'price_idr' => 50000,
    ], [
        'idr_per_myr' => '4000.0000',
        'idr_per_usd' => '15000.0000',
    ]);

    expect($html)
        ->toContain('&lt;script&gt;alert(1)&lt;/script&gt;')
        ->toContain('&lt;img src=x&gt;')
        ->not->toContain('<script>alert(1)</script>')
        ->not->toContain('<img src=x>');
});

it('rounds account listing currency prices to the nearest five cents', function (int $price, string $expectedPrices) {
    $rates = [
        'idr_per_myr' => '1000.0000',
        'idr_per_usd' => '1000.0000',
    ];
    $html = app(AccountListingFormatter::class)->telegramHtml([
        'username' => 'Example account',
        'level' => '1',
        'items' => [],
        'price_idr' => $price,
    ], $rates);

    expect($html)->toContain($expectedPrices);
})->with([
    '11.12 rounds down' => [1120, 'RM 1.10 | $11.10'],
    '11.13 rounds up' => [1130, 'RM 1.15 | $11.15'],
]);

it('shows all bot commands from help', function () {
    config([
        'services.telegram.admin_ids' => ['12345'],
        'services.telegram.bot_token' => 'test-token',
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'api.telegram.org/*/sendMessage' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 21],
        ]),
    ]);
    $update = TelegramUpdate::query()->create([
        'telegram_update_id' => 900001,
        'payload' => ['message' => [
            'from' => ['id' => 12345],
            'chat' => ['id' => 12345, 'type' => 'private'],
            'text' => '/help',
        ]],
    ]);

    app(TelegramAccountPostingBot::class)->handle($update);

    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/sendMessage')
        && str_contains($request['text'], '/setrate RM 3600')
        && str_contains($request['text'], '/settings')
        && str_contains(json_encode($request['reply_markup']), 'menu:rates'));
});

it('lets admins set the RM rate from Telegram', function () {
    config([
        'services.telegram.admin_ids' => ['12345'],
        'services.telegram.bot_token' => 'test-token',
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'api.telegram.org/*/sendMessage' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 22],
        ]),
    ]);
    ProductCurrencyRate::current()->update(['idr_per_myr' => 4000]);
    $update = TelegramUpdate::query()->create([
        'telegram_update_id' => 900002,
        'payload' => ['message' => [
            'from' => ['id' => 12345],
            'chat' => ['id' => 12345, 'type' => 'private'],
            'text' => '/setrate RM 3600',
        ]],
    ]);

    app(TelegramAccountPostingBot::class)->handle($update);

    ProductCurrencyRate::current()->update(['idr_per_myr' => 5000]);
    $ratesUpdate = TelegramUpdate::query()->create([
        'telegram_update_id' => 900003,
        'payload' => ['message' => [
            'from' => ['id' => 12345],
            'chat' => ['id' => 12345, 'type' => 'private'],
            'text' => '/rates',
        ]],
    ]);

    app(TelegramAccountPostingBot::class)->handle($ratesUpdate);

    expect((float) ProductCurrencyRate::current()->fresh()->idr_per_myr)->toBe(5000.0)
        ->and(StoreSetting::current()->fresh()->telegram_account_rates)->toBe([
            'idr_per_myr' => '3600',
            'idr_per_usd' => '15000.0000',
        ]);
    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/sendMessage')
        && str_contains($request['text'], 'Kurs Telegram saat ini:')
        && str_contains($request['text'], 'RM 1 = Rp 3,600')
        && str_contains($request['text'], 'USD 1 = Rp 15,000')
        && str_contains($request['text'], 'Kurs website diatur di Admin > Rates.'));
});

it('lets admins edit a default from the settings menu', function () {
    config([
        'services.telegram.admin_ids' => ['12345'],
        'services.telegram.bot_token' => 'test-token',
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 23],
        ]),
    ]);
    $processUpdate = function (int $updateId, array $payload): void {
        $update = TelegramUpdate::query()->create([
            'telegram_update_id' => $updateId,
            'payload' => $payload,
        ]);

        app(TelegramAccountPostingBot::class)->handle($update);
    };

    $processUpdate(900003, ['message' => [
        'from' => ['id' => 12345],
        'chat' => ['id' => 12345, 'type' => 'private'],
        'text' => '/settings',
    ]]);
    $processUpdate(900004, ['callback_query' => [
        'id' => 'edit-haki',
        'from' => ['id' => 12345],
        'message' => ['message_id' => 23, 'chat' => ['id' => 12345, 'type' => 'private']],
        'data' => 'settings:edit:haki',
    ]]);
    $processUpdate(900005, ['message' => [
        'from' => ['id' => 12345],
        'chat' => ['id' => 12345, 'type' => 'private'],
        'text' => 'Haki pilihan saya',
    ]]);
    GameCategory::factory()->create(['name' => 'Blox Fruit']);
    $processUpdate(900006, ['message' => [
        'from' => ['id' => 12345],
        'chat' => ['id' => 12345, 'type' => 'private'],
        'text' => '/new',
    ]]);

    expect(StoreSetting::current()->fresh()->telegram_account_defaults['haki'])->toBe('Haki pilihan saya');
    expect(TelegramAccountDraft::query()->sole()->data['haki'])->toBe('Haki pilihan saya');
    Cache::forget('telegram-account-bot-input:12345');
});

it('stores incoming photos without sending a reply for every photo', function () {
    config([
        'services.telegram.admin_ids' => ['12345'],
        'services.telegram.bot_token' => 'test-token',
    ]);
    Storage::fake('public');
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC', true);
    Http::preventStrayRequests();
    Http::fake([
        'api.telegram.org/*/sendMessage' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 24],
        ]),
        'api.telegram.org/*/answerCallbackQuery' => Http::response(['ok' => true, 'result' => true]),
        'api.telegram.org/*/editMessageText' => Http::response(['ok' => true, 'result' => ['message_id' => 24]]),
        'api.telegram.org/*/getFile' => Http::response([
            'ok' => true,
            'result' => ['file_path' => 'photos/account.png'],
        ]),
        'api.telegram.org/file/*' => Http::response($png),
    ]);
    $gameCategory = GameCategory::factory()->create(['name' => 'Blox Fruit']);
    $bot = app(TelegramAccountPostingBot::class);
    $start = TelegramUpdate::query()->create([
        'telegram_update_id' => 900006,
        'payload' => ['message' => [
            'from' => ['id' => 12345],
            'chat' => ['id' => 12345, 'type' => 'private'],
            'text' => '/new',
        ]],
    ]);
    $bot->handle($start);
    $gameSelection = TelegramUpdate::query()->create([
        'telegram_update_id' => 900007,
        'payload' => ['callback_query' => [
            'id' => 'select-game',
            'from' => ['id' => 12345],
            'message' => [
                'message_id' => 24,
                'chat' => ['id' => 12345, 'type' => 'private'],
            ],
            'data' => 'game:'.$gameCategory->id,
        ]],
    ]);
    $bot->handle($gameSelection);
    $photo = TelegramUpdate::query()->create([
        'telegram_update_id' => 900008,
        'payload' => ['message' => [
            'message_id' => 25,
            'from' => ['id' => 12345],
            'chat' => ['id' => 12345, 'type' => 'private'],
            'photo' => [['file_id' => 'file-1']],
        ]],
    ]);

    $bot->handle($photo);

    $draft = TelegramAccountDraft::query()->sole();
    expect($draft->images)->toHaveCount(1);
    Storage::disk('public')->assertExists($draft->images[0]);
    Http::assertSentCount(5);
});
