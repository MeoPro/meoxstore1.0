<?php

namespace App\Services;

use App\Actions\CreateTelegramAccountProduct;
use App\Jobs\PublishTelegramAccountListing;
use App\Models\ProductCurrencyRate;
use App\Models\StoreSetting;
use App\Models\TelegramAccountDraft;
use App\Models\TelegramAccountItem;
use App\Models\TelegramUpdate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TelegramAccountPostingBot
{
    private const MAX_IMAGES = 20;

    private const MAX_LISTING_ITEMS = 20;

    private const MAX_CATEGORIES = 20;

    /**
     * @var array<string, mixed>
     */
    private const DEFAULT_ACCOUNT_VALUES = [
        'account_status' => '100% SAFE ANTI HB, NO VERIFIKASI',
        'status_label' => 'READY✅',
        'haki' => 'SKY JUMP, BODY, FLASH STEP',
        'race' => 'V3 HUMAN',
        'awaken' => '-',
        'permanent' => '-',
        'gamepass' => '-',
        'stat_refund' => 2,
    ];

    /**
     * @var array<string, string>
     */
    private const SETTING_LABELS = [
        'account_status' => 'Status akun',
        'status_label' => 'Label posting',
        'haki' => 'Haki',
        'race' => 'Race',
        'awaken' => 'Awaken',
        'permanent' => 'Permanent',
        'gamepass' => 'Gamepass',
        'stat_refund' => 'Stat Refund',
        'post_chat' => 'Chat',
        'post_instagram' => 'Instagram',
    ];

    public function __construct(
        private readonly TelegramBotApi $telegram,
        private readonly AccountListingFormatter $formatter,
        private readonly CreateTelegramAccountProduct $createProduct,
    ) {}

    public function handle(TelegramUpdate $telegramUpdate): void
    {
        $update = $telegramUpdate->payload;
        $message = $update['message'] ?? null;
        $callback = $update['callback_query'] ?? null;
        $from = is_array($message) ? ($message['from'] ?? null) : (is_array($callback) ? ($callback['from'] ?? null) : null);

        if (! is_array($from) || ! $this->isAllowedAdmin((string) ($from['id'] ?? ''))) {
            return;
        }

        $updateId = (int) $telegramUpdate->telegram_update_id;

        if (is_array($callback)) {
            $this->handleCallback($callback, $updateId);

            return;
        }

        if (! is_array($message) || ($message['chat']['type'] ?? null) !== 'private') {
            return;
        }

        $chatId = (string) $message['chat']['id'];
        $telegramUserId = (string) $from['id'];
        $text = trim((string) ($message['text'] ?? ''));

        if ($this->isCommand($text, '/start') || $this->isCommand($text, '/help')) {
            $this->sendHelp($chatId);

            return;
        }

        if ($this->isCommand($text, '/menu')) {
            Cache::forget($this->pendingInputKey($telegramUserId));
            $this->sendMainMenu($chatId);

            return;
        }

        if ($this->isCommand($text, '/settings')) {
            Cache::forget($this->pendingInputKey($telegramUserId));
            $this->sendSettingsMenu($chatId);

            return;
        }

        if ($this->isCommand($text, '/rates') || $this->isCommand($text, '/rate')) {
            Cache::forget($this->pendingInputKey($telegramUserId));
            $this->sendRatesMenu($chatId);

            return;
        }

        if (preg_match('/^\/setrate(?:@[\w_]+)?(?:\s+([a-zA-Z]+)\s+([0-9]+(?:[.,][0-9]+)?))?$/u', $text, $matches) === 1) {
            Cache::forget($this->pendingInputKey($telegramUserId));
            if (isset($matches[1], $matches[2])) {
                $this->setRate($chatId, $matches[1], $matches[2]);
            } else {
                $this->telegram->sendMessage($chatId, 'Format: /setrate RM 3600 atau /setrate USD 15000.');
            }

            return;
        }

        if ($this->isCommand($text, '/new')) {
            Cache::forget($this->pendingInputKey($telegramUserId));
            $this->startDraft($telegramUserId, $chatId, $updateId);

            return;
        }

        if ($this->isCommand($text, '/cancel') && Cache::has($this->pendingInputKey($telegramUserId))) {
            $this->cancelPendingInput($telegramUserId, $chatId);

            return;
        }

        if (Cache::has($this->pendingInputKey($telegramUserId)) && $text !== '') {
            $this->handlePendingInput($telegramUserId, $chatId, $text);

            return;
        }

        $draft = TelegramAccountDraft::query()
            ->where('telegram_user_id', $telegramUserId)
            ->where('status', 'active')
            ->latest('id')
            ->first();

        if ($draft === null) {
            $this->telegram->sendMessage($chatId, 'Belum ada draft. Kirim /new untuk mulai.');

            return;
        }

        if ($draft->last_update_id !== null && $updateId <= $draft->last_update_id) {
            return;
        }

        if ($this->isCommand($text, '/cancel')) {
            $draft->update(['last_update_id' => $updateId, 'chat_id' => $chatId]);
            $this->cancelDraft($draft);

            return;
        }

        if (isset($message['photo']) && is_array($message['photo'])) {
            $this->receivePhoto($draft, $message, $updateId);

            return;
        }

        $draft->update(['last_update_id' => $updateId, 'chat_id' => $chatId]);

        if ($this->isCommand($text, '/done')) {
            $this->finishCurrentStep($draft);

            return;
        }

        $this->receiveText($draft, $text);
    }

    private function isAllowedAdmin(string $telegramUserId): bool
    {
        return $telegramUserId !== ''
            && in_array($telegramUserId, config('services.telegram.admin_ids', []), true);
    }

    private function isCommand(string $text, string $command): bool
    {
        return preg_match('/^'.preg_quote($command, '/').'(?:@[\w_]+)?$/u', $text) === 1;
    }

    private function sendHelp(string $chatId): void
    {
        $this->telegram->sendMessage(
            $chatId,
            $this->helpText(),
            $this->mainMenuMarkup(),
        );
    }

    private function helpText(): string
    {
        return "Perintah bot:\n".
            "/start atau /help — tampilkan bantuan\n".
            "/menu — buka menu utama\n".
            "/new — mulai posting akun\n".
            "/done — selesai mengirim foto atau memilih item\n".
            "/skip — gunakan nilai default untuk kolom opsional\n".
            "/cancel — batalkan draft atau pengaturan yang sedang diedit\n".
            "/settings — atur nilai default untuk posting baru\n".
            "/rates — lihat kurs saat ini\n".
            "/setrate RM 3600 — atur kurs Telegram IDR per 1 RM\n".
            "/setrate USD 15000 — atur kurs Telegram IDR per 1 USD\n\n".
            'Kelola kategori dan jenis item dari menu utama. Nilai default Haki, Race, Awaken, Permanent, Gamepass, dan Stat Refund otomatis dipakai; ubah kapan saja lewat /settings.';
    }

    /**
     * @return array<string, mixed>
     */
    private function mainMenuMarkup(): array
    {
        return [
            'inline_keyboard' => [
                [['text' => '➕ Posting akun', 'callback_data' => 'menu:new']],
                [['text' => '🗂 Kategori & item', 'callback_data' => 'menu:catalog']],
                [
                    ['text' => '⚙️ Default posting', 'callback_data' => 'menu:settings'],
                    ['text' => '💱 Kurs', 'callback_data' => 'menu:rates'],
                ],
                [['text' => '❔ Bantuan', 'callback_data' => 'menu:help']],
            ],
        ];
    }

    private function sendMainMenu(string $chatId): void
    {
        $this->telegram->sendMessage($chatId, 'Menu utama bot posting akun:', $this->mainMenuMarkup());
    }

    private function sendSettingsMenu(string $chatId, ?int $messageId = null, ?string $notice = null): void
    {
        $defaults = $this->accountDefaults();
        $lines = ['Default untuk posting baru:'];
        foreach (self::SETTING_LABELS as $field => $label) {
            $lines[] = $label.': '.(string) ($defaults[$field] ?? '-');
        }

        $text = ($notice === null ? '' : $notice."\n\n").implode("\n", $lines)."\n\nPilih nilai yang ingin diubah. Kirim /skip untuk mengembalikan default awal.";
        $markup = $this->settingsMenuMarkup();

        $this->sendOrEditMessage($chatId, $messageId, $text, $markup);
    }

    /**
     * @return array<string, mixed>
     */
    private function settingsMenuMarkup(): array
    {
        $buttons = [];
        foreach (self::SETTING_LABELS as $field => $label) {
            $buttons[] = [
                'text' => $label,
                'callback_data' => 'settings:edit:'.$field,
            ];
        }

        return [
            'inline_keyboard' => [
                ...array_chunk($buttons, 2),
                [['text' => '⬅️ Menu utama', 'callback_data' => 'menu:main']],
            ],
        ];
    }

    private function sendRatesMenu(string $chatId, ?int $messageId = null, ?string $notice = null): void
    {
        $rates = StoreSetting::current()->telegramAccountRates();
        $text = ($notice === null ? '' : $notice."\n\n").
            'Kurs Telegram saat ini:'."\n".
            'RM 1 = Rp '.rtrim(rtrim(number_format((float) $rates['idr_per_myr'], 4, '.', ','), '0'), '.')."\n".
            'USD 1 = Rp '.rtrim(rtrim(number_format((float) $rates['idr_per_usd'], 4, '.', ','), '0'), '.')."\n\n".
            'Kurs ini hanya untuk posting Telegram. Kurs website diatur di Admin > Rates.';
        $markup = [
            'inline_keyboard' => [
                [
                    ['text' => 'Atur kurs RM', 'callback_data' => 'rates:edit:myr'],
                    ['text' => 'Atur kurs USD', 'callback_data' => 'rates:edit:usd'],
                ],
                [['text' => '⬅️ Menu utama', 'callback_data' => 'menu:main']],
            ],
        ];

        $this->sendOrEditMessage($chatId, $messageId, $text, $markup);
    }

    private function sendCatalogMenu(string $chatId, ?int $messageId = null, ?string $notice = null): void
    {
        $categories = StoreSetting::current()->telegramAccountCategories();
        $buttons = [];

        foreach ($categories as $key => $label) {
            $itemCount = TelegramAccountItem::query()->where('category', $label)->count();
            $buttons[] = [
                'text' => $label.' ('.$itemCount.')',
                'callback_data' => 'catalog:category:'.$key,
            ];
        }

        $markup = [
            'inline_keyboard' => [
                ...array_chunk($buttons, 2),
                [['text' => '➕ Tambah kategori', 'callback_data' => 'catalog:add-category']],
                [['text' => '⬅️ Menu utama', 'callback_data' => 'menu:main']],
            ],
        ];
        $text = ($notice === null ? '' : $notice."\n\n").
            'Kategori dan jenis item untuk posting. Pilih kategori untuk melihat atau menambahkan jenis item.';

        $this->sendOrEditMessage($chatId, $messageId, $text, $markup);
    }

    private function sendCatalogCategory(
        string $chatId,
        ?int $messageId,
        string $categoryKey,
        ?string $notice = null,
    ): void {
        $categories = StoreSetting::current()->telegramAccountCategories();
        $category = $categories[$categoryKey] ?? null;

        if ($category === null) {
            $this->sendCatalogMenu($chatId, $messageId, 'Kategori tidak ditemukan.');

            return;
        }

        $items = TelegramAccountItem::query()
            ->where('category', $category)
            ->orderBy('name')
            ->get();
        $itemLines = $items->map(fn (TelegramAccountItem $item): string => '• '.($item->emoji ?? '').$item->name);
        $text = ($notice === null ? '' : $notice."\n\n").
            'Kategori: '.$category."\n\n".
            'Jenis item:'."\n".
            ($itemLines->isEmpty() ? 'Belum ada. Tambahkan jenis item di bawah.' : $itemLines->implode("\n"));
        $markup = [
            'inline_keyboard' => [
                [['text' => '➕ Tambah jenis item', 'callback_data' => 'catalog:add-item:'.$categoryKey]],
                [['text' => '⬅️ Kembali ke kategori', 'callback_data' => 'catalog:main']],
                [['text' => '🏠 Menu utama', 'callback_data' => 'menu:main']],
            ],
        ];

        $this->sendOrEditMessage($chatId, $messageId, $text, $markup);
    }

    /**
     * @param  array<string, mixed>  $pendingInput
     */
    private function saveCatalogCategoryInput(
        string $telegramUserId,
        string $chatId,
        array $pendingInput,
        string $value,
    ): void {
        $category = trim($value);
        $messageId = filter_var($pendingInput['message_id'] ?? null, FILTER_VALIDATE_INT);
        if ($category === '' || Str::length($category) > 30 || str_contains($category, ':')) {
            $this->editPendingPrompt(
                $chatId,
                $messageId === false ? null : $messageId,
                'Nama kategori harus 1–30 karakter dan tidak boleh berisi titik dua. Kirim ulang atau /cancel.',
            );
            Cache::put($this->pendingInputKey($telegramUserId), $pendingInput, now()->addMinutes(10));

            return;
        }

        $storeSettings = StoreSetting::current();
        $categories = $storeSettings->telegramAccountCategories();
        foreach ($categories as $key => $label) {
            if (mb_strtolower($label) === mb_strtolower($category)) {
                Cache::forget($this->pendingInputKey($telegramUserId));
                $this->sendCatalogMenu(
                    $chatId,
                    $messageId === false ? null : $messageId,
                    'Kategori '.$label.' sudah tersedia.',
                );

                return;
            }
        }

        if (count($categories) >= self::MAX_CATEGORIES) {
            $this->editPendingPrompt(
                $chatId,
                $messageId === false ? null : $messageId,
                'Maksimal 20 kategori dapat disimpan. Kirim /cancel untuk kembali.',
            );
            Cache::put($this->pendingInputKey($telegramUserId), $pendingInput, now()->addMinutes(10));

            return;
        }

        $key = 'custom_'.substr(sha1(mb_strtolower($category)), 0, 12);
        $categories[$key] = $category;
        $storeSettings->update(['telegram_account_categories' => $categories]);
        Cache::forget($this->pendingInputKey($telegramUserId));
        $this->sendCatalogMenu(
            $chatId,
            $messageId === false ? null : $messageId,
            'Kategori '.$category.' berhasil ditambahkan.',
        );
    }

    /**
     * @param  array<string, mixed>  $pendingInput
     */
    private function saveCatalogItemNameInput(
        string $telegramUserId,
        string $chatId,
        array $pendingInput,
        string $value,
    ): void {
        $name = trim($value);
        $messageId = filter_var($pendingInput['message_id'] ?? null, FILTER_VALIDATE_INT);
        if ($name === '' || Str::length($name) > 80) {
            $this->editPendingPrompt(
                $chatId,
                $messageId === false ? null : $messageId,
                'Nama jenis item wajib diisi dan maksimal 80 karakter. Kirim ulang atau /cancel.',
            );
            Cache::put($this->pendingInputKey($telegramUserId), $pendingInput, now()->addMinutes(10));

            return;
        }

        $categoryKey = (string) ($pendingInput['category_key'] ?? '');
        $category = StoreSetting::current()->telegramAccountCategories()[$categoryKey] ?? null;
        if ($category === null) {
            Cache::forget($this->pendingInputKey($telegramUserId));
            $this->sendCatalogMenu($chatId, $messageId === false ? null : $messageId, 'Kategori tidak ditemukan.');

            return;
        }

        $pendingInput['type'] = 'catalog_item_emoji';
        $pendingInput['item_name'] = $name;
        Cache::put($this->pendingInputKey($telegramUserId), $pendingInput, now()->addMinutes(10));
        $this->editPendingPrompt(
            $chatId,
            $messageId === false ? null : $messageId,
            'Kirim emoji/icon untuk '.$name.' di kategori '.$category.', atau /skip jika tanpa emoji.',
        );
    }

    /**
     * @param  array<string, mixed>  $pendingInput
     */
    private function saveCatalogItemEmojiInput(
        string $telegramUserId,
        string $chatId,
        array $pendingInput,
        string $value,
    ): void {
        $emoji = trim($value);
        $messageId = filter_var($pendingInput['message_id'] ?? null, FILTER_VALIDATE_INT);
        if ($this->isCommand($emoji, '/skip') || $emoji === '-') {
            $emoji = '';
        }

        if (Str::length($emoji) > 8) {
            $this->editPendingPrompt(
                $chatId,
                $messageId === false ? null : $messageId,
                'Emoji maksimal 8 karakter, atau /skip untuk tanpa emoji.',
            );
            Cache::put($this->pendingInputKey($telegramUserId), $pendingInput, now()->addMinutes(10));

            return;
        }

        $categoryKey = (string) ($pendingInput['category_key'] ?? '');
        $category = StoreSetting::current()->telegramAccountCategories()[$categoryKey] ?? null;
        if ($category === null) {
            Cache::forget($this->pendingInputKey($telegramUserId));
            $this->sendCatalogMenu($chatId, $messageId === false ? null : $messageId, 'Kategori tidak ditemukan.');

            return;
        }

        $item = TelegramAccountItem::query()->firstOrCreate(
            [
                'category' => $category,
                'name' => (string) ($pendingInput['item_name'] ?? ''),
            ],
            ['emoji' => $emoji === '' ? null : $emoji],
        );
        if ($emoji !== '' && $item->emoji !== $emoji) {
            $item->update(['emoji' => $emoji]);
        }

        Cache::forget($this->pendingInputKey($telegramUserId));
        $this->sendCatalogCategory(
            $chatId,
            $messageId === false ? null : $messageId,
            $categoryKey,
            'Jenis item '.$item->name.' berhasil disimpan.',
        );
    }

    /**
     * @param  array<string, mixed>|null  $replyMarkup
     * @return array<string, mixed>
     */
    private function sendOrEditMessage(string $chatId, ?int $messageId, string $text, ?array $replyMarkup = null): array
    {
        if ($messageId === null) {
            return $this->telegram->sendMessage($chatId, $text, $replyMarkup);
        }

        return $this->telegram->editMessageText($chatId, $messageId, $text, $replyMarkup);
    }

    /**
     * @return array<string, mixed>
     */
    private function accountDefaults(): array
    {
        $defaults = array_merge(self::DEFAULT_ACCOUNT_VALUES, [
            'post_chat' => (string) config('services.telegram.post_chat', ''),
            'post_instagram' => (string) config('services.telegram.post_instagram', ''),
        ]);

        return array_merge($defaults, StoreSetting::current()->telegram_account_defaults ?? []);
    }

    private function pendingInputKey(string $telegramUserId): string
    {
        return 'telegram-account-bot-input:'.$telegramUserId;
    }

    private function handlePendingInput(string $telegramUserId, string $chatId, string $value): void
    {
        $pendingInput = Cache::get($this->pendingInputKey($telegramUserId));
        if (! is_array($pendingInput)) {
            return;
        }

        $messageId = filter_var($pendingInput['message_id'] ?? null, FILTER_VALIDATE_INT);
        $type = $pendingInput['type'] ?? null;

        if ($this->isCommand($value, '/skip') && $type === 'default') {
            $this->saveDefaultValue((string) ($pendingInput['field'] ?? ''), null);
            Cache::forget($this->pendingInputKey($telegramUserId));
            $this->sendSettingsMenu($chatId, $messageId === false ? null : $messageId, 'Default dikembalikan ke nilai awal.');

            return;
        }

        if ($type === 'default') {
            $field = (string) ($pendingInput['field'] ?? '');
            if (! array_key_exists($field, self::SETTING_LABELS) || $value === '' || Str::length($value) > 255) {
                $this->editPendingPrompt(
                    $chatId,
                    $messageId === false ? null : $messageId,
                    'Nilai tidak valid. Maksimal 255 karakter; kirim /skip untuk mengembalikan default.',
                );
                Cache::put($this->pendingInputKey($telegramUserId), $pendingInput, now()->addMinutes(10));

                return;
            }

            if ($field === 'stat_refund' && (! ctype_digit($value) || (int) $value > 100)) {
                $this->editPendingPrompt(
                    $chatId,
                    $messageId === false ? null : $messageId,
                    'Stat Refund harus angka 0–100, atau /skip untuk mengembalikan default.',
                );
                Cache::put($this->pendingInputKey($telegramUserId), $pendingInput, now()->addMinutes(10));

                return;
            }

            $this->saveDefaultValue($field, $field === 'stat_refund' ? (int) $value : $value);
            Cache::forget($this->pendingInputKey($telegramUserId));
            $this->sendSettingsMenu($chatId, $messageId === false ? null : $messageId, 'Default berhasil disimpan untuk posting berikutnya.');

            return;
        }

        if ($type === 'rate') {
            $currency = (string) ($pendingInput['field'] ?? '');
            if (! $this->setRate($chatId, $currency, $value, $messageId === false ? null : $messageId)) {
                Cache::put($this->pendingInputKey($telegramUserId), $pendingInput, now()->addMinutes(10));

                return;
            }

            Cache::forget($this->pendingInputKey($telegramUserId));

            return;
        }

        if ($type === 'catalog_category') {
            $this->saveCatalogCategoryInput($telegramUserId, $chatId, $pendingInput, $value);

            return;
        }

        if ($type === 'catalog_item_name') {
            $this->saveCatalogItemNameInput($telegramUserId, $chatId, $pendingInput, $value);

            return;
        }

        if ($type === 'catalog_item_emoji') {
            $this->saveCatalogItemEmojiInput($telegramUserId, $chatId, $pendingInput, $value);
        }
    }

    private function editPendingPrompt(string $chatId, ?int $messageId, string $text): void
    {
        if ($messageId === null) {
            $this->telegram->sendMessage($chatId, $text);

            return;
        }

        $this->telegram->editMessageText($chatId, $messageId, $text, ['inline_keyboard' => []]);
    }

    private function saveDefaultValue(string $field, mixed $value): void
    {
        $storedDefaults = StoreSetting::current()->telegram_account_defaults ?? [];
        if ($value === null) {
            unset($storedDefaults[$field]);
        } else {
            $storedDefaults[$field] = $value;
        }

        StoreSetting::current()->update(['telegram_account_defaults' => $storedDefaults]);
    }

    private function cancelPendingInput(string $telegramUserId, string $chatId): void
    {
        $pendingInput = Cache::pull($this->pendingInputKey($telegramUserId));
        $messageId = is_array($pendingInput)
            ? filter_var($pendingInput['message_id'] ?? null, FILTER_VALIDATE_INT)
            : false;

        if (is_array($pendingInput) && ($pendingInput['type'] ?? null) === 'rate') {
            $this->sendRatesMenu($chatId, $messageId === false ? null : $messageId, 'Pengaturan dibatalkan.');

            return;
        }

        if (is_array($pendingInput) && ($pendingInput['type'] ?? null) === 'catalog_category') {
            $this->sendCatalogMenu($chatId, $messageId === false ? null : $messageId, 'Pengaturan dibatalkan.');

            return;
        }

        if (is_array($pendingInput) && in_array($pendingInput['type'] ?? null, ['catalog_item_name', 'catalog_item_emoji'], true)) {
            $categoryKey = (string) ($pendingInput['category_key'] ?? '');
            $this->sendCatalogCategory(
                $chatId,
                $messageId === false ? null : $messageId,
                $categoryKey,
                'Pengaturan dibatalkan.',
            );

            return;
        }

        $this->sendSettingsMenu($chatId, $messageId === false ? null : $messageId, 'Pengaturan dibatalkan.');
    }

    private function setRate(string $chatId, string $currency, string $value, ?int $messageId = null): bool
    {
        $normalizedCurrency = mb_strtolower($currency);
        $field = match ($normalizedCurrency) {
            'rm', 'myr' => 'idr_per_myr',
            'usd' => 'idr_per_usd',
            default => null,
        };
        $normalizedValue = str_replace(',', '.', trim($value));

        if ($field === null || preg_match('/^\d+(?:\.\d{1,4})?$/', $normalizedValue) !== 1
            || (float) $normalizedValue <= 0 || (float) $normalizedValue > 9_999_999_999.9999) {
            $text = 'Nilai kurs tidak valid. Masukkan angka positif (maksimal 4 desimal), misalnya 3600.';
            if ($messageId === null) {
                $this->telegram->sendMessage($chatId, $text);
            } else {
                $this->telegram->editMessageText($chatId, $messageId, $text, ['inline_keyboard' => []]);
            }

            return false;
        }

        $storeSettings = StoreSetting::current();
        $telegramRates = $storeSettings->telegramAccountRates();
        $telegramRates[$field] = $normalizedValue;
        $storeSettings->update(['telegram_account_rates' => $telegramRates]);
        $label = $field === 'idr_per_myr' ? 'RM 1' : 'USD 1';
        $notice = 'Kurs Telegram tersimpan: '.$label.' = Rp '.number_format((float) $normalizedValue, 0, ',', '.').'.';

        if ($messageId === null) {
            $this->telegram->sendMessage($chatId, $notice);
        } else {
            $this->sendRatesMenu($chatId, $messageId, $notice);
        }

        return true;
    }

    private function startDraft(string $telegramUserId, string $chatId, int $updateId): void
    {
        $existingDraft = TelegramAccountDraft::query()
            ->where('telegram_user_id', $telegramUserId)
            ->where('status', 'active')
            ->latest('id')
            ->first();

        if ($existingDraft !== null) {
            $existingDraft->update(['last_update_id' => $updateId, 'chat_id' => $chatId]);
            $this->sendDraftMessage(
                $existingDraft,
                'Masih ada draft aktif. Lanjutkan draft tersebut atau kirim /cancel untuk membatalkan.',
                ['inline_keyboard' => []],
            );

            return;
        }

        $defaults = $this->accountDefaults();
        $draft = TelegramAccountDraft::query()->create([
            'telegram_user_id' => $telegramUserId,
            'chat_id' => $chatId,
            'status' => 'active',
            'step' => 'photos',
            'last_update_id' => $updateId,
            'data' => array_merge([
                'items' => [],
                'category_options' => StoreSetting::current()->telegramAccountCategories(),
                'selected_categories' => [],
            ], $defaults),
            'images' => [],
            'processed_message_ids' => [],
        ]);

        $this->sendDraftMessage(
            $draft,
            'Kirim foto akun (maksimal 20 foto). Setelah semua foto terkirim, kirim /done. Kirim /cancel untuk membatalkan.',
        );
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function receivePhoto(TelegramAccountDraft $draft, array $message, int $updateId): void
    {
        if ($draft->step !== 'photos') {
            $this->sendDraftMessage($draft, 'Foto hanya bisa ditambahkan pada langkah pertama. Lanjutkan dengan jawaban yang diminta atau kirim /cancel.');

            return;
        }

        $messageId = (int) ($message['message_id'] ?? 0);
        $processedMessageIds = $draft->processed_message_ids ?? [];
        if (in_array($messageId, $processedMessageIds, true)) {
            return;
        }

        $images = $draft->images ?? [];
        if (count($images) >= self::MAX_IMAGES) {
            $this->sendDraftMessage($draft, 'Batas 20 foto tercapai. Kirim /done untuk melanjutkan.');

            return;
        }

        $photoSizes = $message['photo'];
        $largestPhoto = end($photoSizes);
        $fileId = is_array($largestPhoto) ? ($largestPhoto['file_id'] ?? null) : null;

        if (! is_string($fileId) || $fileId === '') {
            $this->sendDraftMessage($draft, 'Foto tidak terbaca. Coba kirim ulang.');

            return;
        }

        $images[] = $this->telegram->downloadPhoto($fileId);
        $processedMessageIds[] = $messageId;
        $draft->update([
            'images' => $images,
            'processed_message_ids' => $processedMessageIds,
            'last_update_id' => $updateId,
        ]);
    }

    private function finishCurrentStep(TelegramAccountDraft $draft): void
    {
        if ($draft->step === 'photos') {
            if (($draft->images ?? []) === []) {
                $this->sendDraftMessage($draft, 'Kirim minimal satu foto sebelum melanjutkan.');

                return;
            }

            $draft->update(['step' => 'username']);
            $this->askForStep($draft, 'username');

            return;
        }

        if ($draft->step === 'categories') {
            $this->finishCategorySelection($draft);

            return;
        }

        if ($draft->step === 'items') {
            $this->finishItemSelection($draft);

            return;
        }

        $this->sendDraftMessage($draft, 'Kirim jawaban untuk melanjutkan langkah ini.');
    }

    private function receiveText(TelegramAccountDraft $draft, string $text): void
    {
        if ($draft->step === 'photos') {
            $this->sendDraftMessage($draft, 'Kirim foto, lalu /done jika sudah selesai.');

            return;
        }

        if ($draft->step === 'categories') {
            $this->sendDraftMessage($draft, 'Pilih kategori melalui tombol di bawah, lalu tekan Selesai.');

            return;
        }

        if ($draft->step === 'new_category') {
            $this->addCategory($draft, $text);

            return;
        }

        if ($draft->step === 'items') {
            $this->sendItemKeyboard($draft);

            return;
        }

        if ($draft->step === 'new_item_name') {
            $this->saveNewItemName($draft, $text);

            return;
        }

        if ($draft->step === 'new_item_emoji') {
            $this->saveNewItemEmoji($draft, $text);

            return;
        }

        if ($draft->step === 'preview') {
            $this->sendDraftMessage($draft, 'Periksa preview, lalu pilih Posting atau Batal melalui tombol.');

            return;
        }

        $this->saveStepValue($draft, $text);
    }

    private function saveStepValue(TelegramAccountDraft $draft, string $text): void
    {
        $data = $draft->data;
        $step = $draft->step;

        if ($step === 'username' && ($text === '' || Str::length($text) > 40)) {
            $this->sendDraftMessage($draft, 'Nama akun wajib diisi (maksimal 40 karakter).');

            return;
        }

        if ($step === 'level' && (! ctype_digit($text) || (int) $text < 1 || (int) $text > 100_000)) {
            $this->sendDraftMessage($draft, 'Level harus berupa angka antara 1 dan 100000.');

            return;
        }

        if ($step === 'website_title') {
            $websiteTitle = $this->isCommand($text, '/skip')
                ? 'Blox Fruit Account - '.$data['username']
                : trim($text);
            if ($websiteTitle === '' || Str::length($websiteTitle) > 255) {
                $this->sendDraftMessage($draft, 'Judul website wajib diisi (maksimal 255 karakter), atau /skip untuk memakai judul otomatis.');

                return;
            }

            $data['website_title'] = $websiteTitle;
        } elseif ($step === 'stat_refund') {
            if ($this->isCommand($text, '/skip')) {
                $data['stat_refund'] = $this->accountDefaults()['stat_refund'];
            } elseif (! ctype_digit($text) || (int) $text > 100) {
                $this->sendDraftMessage($draft, 'Stat refund harus angka 0–100, atau /skip untuk memakai default.');

                return;
            } else {
                $data['stat_refund'] = (int) $text;
            }
        } elseif ($step === 'price') {
            $price = $this->parsePriceInThousands($text);
            if ($price === null) {
                $this->sendDraftMessage($draft, 'Masukkan harga dalam ribuan, misalnya 50 untuk Rp50.000 (atau 50.5 untuk Rp50.500).');

                return;
            }

            $data['price_idr'] = $price;
        } else {
            $defaults = [
                'belly' => '-',
                'fragment' => '-',
            ];
            $value = $this->isCommand($text, '/skip')
                ? ($data[$step] ?? $defaults[$step] ?? '-')
                : $text;

            if (Str::length($value) > 255) {
                $this->sendDraftMessage($draft, 'Jawaban maksimal 255 karakter.');

                return;
            }

            $data[$step] = $value;
            if ($step === 'username') {
                $data['website_title'] = 'Blox Fruit Account - '.$value;
            }
        }

        $nextStep = $this->nextStep($step);
        $draft->update([
            'data' => $data,
            'step' => $nextStep,
        ]);

        if ($nextStep === 'preview') {
            $this->sendPreview($draft->fresh());

            return;
        }

        $this->askForStep($draft, $nextStep);
    }

    private function nextStep(string $step): string
    {
        return match ($step) {
            'username' => 'website_title',
            'website_title' => 'level',
            'level' => 'belly',
            'belly' => 'fragment',
            'fragment' => 'categories',
            'haki' => 'race',
            'race' => 'awaken',
            'awaken' => 'permanent',
            'permanent' => 'gamepass',
            'gamepass' => 'stat_refund',
            'stat_refund' => 'price',
            'price' => 'preview',
            default => 'preview',
        };
    }

    private function askForStep(TelegramAccountDraft $draft, string $step): void
    {
        $prompts = [
            'username' => 'Masukkan nama akun yang akan ditampilkan.',
            'website_title' => 'Masukkan judul produk untuk website (maksimal 255 karakter), atau /skip untuk memakai judul otomatis: '.($draft->data['website_title'] ?? 'Blox Fruit Account - '.($draft->data['username'] ?? 'akun')),
            'level' => 'Masukkan level akun (angka).',
            'belly' => 'Masukkan jumlah Belly, atau /skip jika kosong.',
            'fragment' => 'Masukkan jumlah Fragment, atau /skip jika kosong.',
            'haki' => 'Masukkan Haki. Default: SKY JUMP, BODY, FLASH STEP. Kirim /skip untuk memakai default.',
            'race' => 'Masukkan Race. Default: V3 HUMAN. Kirim /skip untuk memakai default.',
            'awaken' => 'Masukkan status Awaken, atau /skip untuk "-".',
            'permanent' => 'Masukkan Permanent, atau /skip untuk "-".',
            'gamepass' => 'Masukkan Gamepass, atau /skip untuk "-".',
            'stat_refund' => 'Masukkan Stat Refund. Default 2; kirim /skip untuk memakai 2.',
            'price' => 'Masukkan harga dalam ribuan (contoh 50 = Rp50.000). Nilai RM dan USD dihitung dari Admin > Rates.',
        ];

        if ($step === 'categories') {
            $this->sendCategoryKeyboard($draft);

            return;
        }

        $photoNotice = $step === 'username'
            ? 'Foto tersimpan: '.count($draft->images ?? []).".\n"
            : '';
        $this->sendDraftMessage(
            $draft,
            $photoNotice.($prompts[$step] ?? 'Kirim jawaban untuk melanjutkan.'),
            ['inline_keyboard' => []],
        );
    }

    private function addCategory(TelegramAccountDraft $draft, string $category): void
    {
        $category = trim($category);
        if ($category === '' || Str::length($category) > 30 || str_contains($category, ':')) {
            $this->sendDraftMessage($draft, 'Nama kategori harus 1–30 karakter dan tidak boleh berisi titik dua.');

            return;
        }

        $data = $draft->data;
        $storeSettings = StoreSetting::current();
        $options = $storeSettings->telegramAccountCategories();
        $key = null;
        foreach ($options as $optionKey => $label) {
            if (mb_strtolower($label) === mb_strtolower($category)) {
                $key = $optionKey;

                break;
            }
        }

        if ($key === null && count($options) >= self::MAX_CATEGORIES) {
            $this->sendDraftMessage($draft, 'Maksimal 20 kategori dapat dipilih atau ditambahkan.');

            return;
        }

        if ($key === null) {
            $key = 'custom_'.substr(sha1(mb_strtolower($category)), 0, 12);
            $options[$key] = $category;
            $storeSettings->update(['telegram_account_categories' => $options]);
        }

        $data['category_options'] = array_replace($options, $data['category_options'] ?? []);
        $data['category_options'][$key] = $category;
        $selected = $data['selected_categories'] ?? [];
        if (! in_array($key, $selected, true)) {
            $selected[] = $key;
        }
        $data['selected_categories'] = $selected;
        $draft->update(['data' => $data, 'step' => 'categories']);
        $this->sendCategoryKeyboard($draft->fresh(), 'Kategori ditambahkan dan dipilih.');
    }

    private function finishCategorySelection(TelegramAccountDraft $draft): void
    {
        $selected = $draft->data['selected_categories'] ?? [];
        if ($selected === []) {
            $this->sendDraftMessage($draft, 'Pilih minimal satu kategori atau tambahkan kategori baru.');

            return;
        }

        $draft->update(['step' => 'items']);
        $this->sendItemKeyboard($draft->fresh());
    }

    private function sendCategoryKeyboard(TelegramAccountDraft $draft, ?string $notice = null): void
    {
        $data = $draft->data;
        $selected = $data['selected_categories'] ?? [];
        $options = $data['category_options'] ?? self::DEFAULT_CATEGORIES;
        $buttons = [];

        foreach ($options as $key => $label) {
            $buttons[] = [
                'text' => (in_array($key, $selected, true) ? '✅ ' : '').$label,
                'callback_data' => 'cat:'.$key,
            ];
        }

        $keyboard = [
            'inline_keyboard' => [
                ...array_chunk($buttons, 2),
                [['text' => '➕ Tambah kategori', 'callback_data' => 'cat:add']],
                [['text' => '✅ Selesai memilih', 'callback_data' => 'cat:done']],
            ],
        ];

        $this->sendDraftMessage(
            $draft,
            ($notice === null ? '' : $notice."\n").'Pilih kategori yang dipakai. Tekan kategori untuk memilih atau membatalkan pilihan.',
            $keyboard,
        );
    }

    private function sendItemKeyboard(TelegramAccountDraft $draft, ?string $notice = null): void
    {
        $data = $draft->data;
        $categories = $data['category_options'] ?? StoreSetting::current()->telegramAccountCategories();
        $selectedCategoryKeys = $data['selected_categories'] ?? [];
        $selectedCategories = array_map(
            fn (string $key): string => $categories[$key] ?? '',
            $selectedCategoryKeys,
        );
        $selectedItems = array_map('intval', $data['selected_item_ids'] ?? []);
        $buttons = [];
        $itemsByCategory = TelegramAccountItem::query()
            ->whereIn('category', $selectedCategories)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        foreach ($selectedCategoryKeys as $key) {
            $category = $categories[$key] ?? null;
            if ($category === null) {
                continue;
            }

            $categoryItems = $itemsByCategory->get($category, collect());
            $selectedCount = $categoryItems->whereIn('id', $selectedItems)->count();
            $buttons[] = [
                'text' => $category.' ('.$selectedCount.'/'.$categoryItems->count().')',
                'callback_data' => 'item:category:'.$key,
            ];
        }

        $keyboard = [
            'inline_keyboard' => [
                ...array_chunk($buttons, 2),
                [['text' => '✅ Selesai memilih item', 'callback_data' => 'item:done']],
            ],
        ];

        $this->sendDraftMessage(
            $draft,
            ($notice === null ? '' : $notice."\n").'Pilih kategori untuk membuka daftar jenis item. Di dalam kategori, tandai semua item yang dimiliki; kamu bisa memilih lebih dari satu.',
            $keyboard,
        );
    }

    private function sendCategoryItemsKeyboard(
        TelegramAccountDraft $draft,
        string $categoryKey,
        ?string $notice = null,
    ): void {
        $data = $draft->data;
        $categories = $data['category_options'] ?? StoreSetting::current()->telegramAccountCategories();
        $category = $categories[$categoryKey] ?? null;
        if ($category === null || ! in_array($categoryKey, $data['selected_categories'] ?? [], true)) {
            $this->sendItemKeyboard($draft, 'Kategori tidak tersedia untuk posting ini.');

            return;
        }

        $selectedItems = array_map('intval', $data['selected_item_ids'] ?? []);
        $buttons = [];
        foreach (TelegramAccountItem::query()
            ->where('category', $category)
            ->orderBy('name')
            ->get() as $item) {
            $buttons[] = [
                'text' => (in_array($item->id, $selectedItems, true) ? '✅ ' : '').Str::limit(($item->emoji ?? '').$item->name, 60),
                'callback_data' => 'item:select:'.$item->id,
            ];
        }

        $keyboard = [
            'inline_keyboard' => [
                ...array_chunk($buttons, 2),
                [['text' => '➕ Tambah jenis item', 'callback_data' => 'item:add:'.$categoryKey]],
                [['text' => '⬅️ Kembali ke kategori', 'callback_data' => 'item:back']],
                [['text' => '✅ Selesai memilih item', 'callback_data' => 'item:done']],
            ],
        ];
        $this->sendDraftMessage(
            $draft,
            ($notice === null ? '' : $notice."\n").
                'Pilih satu atau beberapa jenis '.$category.'. Tekan item lagi untuk membatalkan pilihannya.',
            $keyboard,
        );
    }

    private function startAddingItem(TelegramAccountDraft $draft, string $categoryKey): void
    {
        $data = $draft->data;
        if (! in_array($categoryKey, $data['selected_categories'] ?? [], true)
            || ! isset($data['category_options'][$categoryKey])) {
            return;
        }

        $data['pending_item_category'] = $data['category_options'][$categoryKey];
        $data['active_item_category_key'] = $categoryKey;
        $draft->update(['data' => $data, 'step' => 'new_item_name']);
        $this->sendDraftMessage(
            $draft,
            'Kirim nama item baru untuk kategori '.$data['pending_item_category'].' (maksimal 80 karakter).',
            ['inline_keyboard' => []],
        );
    }

    private function saveNewItemName(TelegramAccountDraft $draft, string $name): void
    {
        $name = trim($name);
        if ($name === '' || Str::length($name) > 80) {
            $this->sendDraftMessage($draft, 'Nama item wajib diisi dan maksimal 80 karakter.');

            return;
        }

        $data = $draft->data;
        $data['pending_item_name'] = $name;
        $draft->update(['data' => $data, 'step' => 'new_item_emoji']);
        $this->sendDraftMessage(
            $draft,
            'Kirim emoji/icon untuk item ini, atau /skip jika tanpa emoji.',
            ['inline_keyboard' => []],
        );
    }

    private function saveNewItemEmoji(TelegramAccountDraft $draft, string $emoji): void
    {
        $emoji = trim($emoji);
        if ($this->isCommand($emoji, '/skip') || $emoji === '-') {
            $emoji = '';
        }

        if (Str::length($emoji) > 8) {
            $this->sendDraftMessage($draft, 'Emoji maksimal 8 karakter, atau /skip untuk tanpa emoji.');

            return;
        }

        $data = $draft->data;
        $item = TelegramAccountItem::query()->firstOrCreate(
            [
                'category' => $data['pending_item_category'],
                'name' => $data['pending_item_name'],
            ],
            ['emoji' => $emoji === '' ? null : $emoji],
        );

        if ($emoji !== '' && $item->emoji !== $emoji) {
            $item->update(['emoji' => $emoji]);
        }

        $selectedItems = array_map('intval', $data['selected_item_ids'] ?? []);
        $notice = 'Item tersimpan ke daftar dan dipilih untuk posting ini.';
        if (count($selectedItems) >= self::MAX_LISTING_ITEMS) {
            $notice = 'Item tersimpan di daftar, tetapi maksimal 20 item dapat dipilih per posting.';
        } elseif (! in_array($item->id, $selectedItems, true)) {
            $selectedItems[] = $item->id;
        }

        $data['selected_item_ids'] = $selectedItems;
        unset($data['pending_item_category'], $data['pending_item_name']);
        $draft->update(['data' => $data, 'step' => 'items']);
        $activeCategoryKey = (string) ($data['active_item_category_key'] ?? '');
        if ($activeCategoryKey !== '') {
            $this->sendCategoryItemsKeyboard($draft->fresh(), $activeCategoryKey, $notice);
        } else {
            $this->sendItemKeyboard($draft->fresh(), $notice);
        }
    }

    private function finishItemSelection(TelegramAccountDraft $draft): void
    {
        $selectedIds = array_map('intval', $draft->data['selected_item_ids'] ?? []);
        if ($selectedIds === []) {
            $this->sendDraftMessage($draft, 'Pilih minimal satu item sebelum melanjutkan.');

            return;
        }

        $selectedItems = TelegramAccountItem::query()->whereIn('id', $selectedIds)->get()->keyBy('id');
        $data = $draft->data;
        $data['items'] = [];

        foreach ($selectedIds as $itemId) {
            $item = $selectedItems->get($itemId);
            if ($item !== null) {
                $data['items'][] = [
                    'category' => $item->category,
                    'name' => ($item->emoji ?? '').$item->name,
                ];
            }
        }

        if ($data['items'] === []) {
            $this->sendDraftMessage($draft, 'Item yang dipilih tidak ditemukan. Pilih item kembali.');

            return;
        }

        $draft->update(['data' => $data, 'step' => 'price']);
        $this->askForStep($draft, 'price');
    }

    private function sendPreview(TelegramAccountDraft $draft): void
    {
        $rates = StoreSetting::current()->telegramAccountRates();
        $websiteTitle = $draft->data['website_title'] ?? 'Blox Fruit Account - '.$draft->data['username'];
        $this->sendDraftMessage(
            $draft,
            'Judul website: '.$websiteTitle."\n\n".
                $this->formatter->plainText($draft->data, $rates)."\n\nPosting akan membuat produk aktif di website dan mengirim foto serta detail ke channel.",
            [
                'inline_keyboard' => [[
                    ['text' => '✅ Posting Website + Telegram', 'callback_data' => 'publish'],
                    ['text' => '❌ Batal', 'callback_data' => 'cancel'],
                ]],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $callback
     */
    private function handleCallback(array $callback, int $updateId): void
    {
        $callbackId = (string) ($callback['id'] ?? '');
        if ($callbackId !== '') {
            $this->telegram->answerCallbackQuery($callbackId);
        }

        $message = $callback['message'] ?? null;
        if (! is_array($message) || ($message['chat']['type'] ?? null) !== 'private') {
            return;
        }

        $telegramUserId = (string) ($callback['from']['id'] ?? '');
        $chatId = (string) ($message['chat']['id'] ?? '');
        $messageId = filter_var($message['message_id'] ?? null, FILTER_VALIDATE_INT);
        $callbackData = (string) ($callback['data'] ?? '');

        if ($this->handleMenuCallback(
            $callbackData,
            $telegramUserId,
            $chatId,
            $messageId === false ? null : $messageId,
            $updateId,
        )) {
            return;
        }

        $draft = TelegramAccountDraft::query()
            ->where('telegram_user_id', $telegramUserId)
            ->where('status', 'active')
            ->latest('id')
            ->first();

        if ($draft === null || ($draft->last_update_id !== null && $updateId <= $draft->last_update_id)) {
            return;
        }

        $draft->last_update_id = $updateId;

        if (str_starts_with($callbackData, 'cat:')) {
            $draft->save();
            $this->handleCategoryCallback($draft, substr($callbackData, 4));

            return;
        }

        if (str_starts_with($callbackData, 'item:')) {
            $draft->save();
            $this->handleItemCallback($draft, substr($callbackData, 5));

            return;
        }

        if ($draft->step !== 'preview') {
            return;
        }

        if ($callbackData === 'cancel') {
            $draft->update(['last_update_id' => $updateId]);
            $this->cancelDraft($draft);

            return;
        }

        if ($callbackData === 'publish') {
            $data = $draft->data;
            $websiteRates = ProductCurrencyRate::current();
            $data['description'] = $this->formatter->plainText($data, [
                'idr_per_myr' => $websiteRates->idr_per_myr,
                'idr_per_usd' => $websiteRates->idr_per_usd,
            ]);
            $draft->update(['data' => $data]);
            $product = $this->createProduct->execute($draft->fresh());
            $draft->update(['last_update_id' => $updateId]);
            PublishTelegramAccountListing::dispatch($draft->fresh()->id);
            $productUrl = route('product-details.show', $product);

            $this->sendDraftMessage(
                $draft,
                'Produk sudah tayang di website: '.$productUrl."\nPosting foto dan detail ke channel sedang diproses.",
                ['inline_keyboard' => []],
            );
        }
    }

    private function handleMenuCallback(
        string $callbackData,
        string $telegramUserId,
        string $chatId,
        ?int $messageId,
        int $updateId,
    ): bool {
        if ($callbackData === 'menu:new') {
            Cache::forget($this->pendingInputKey($telegramUserId));
            $this->startDraft($telegramUserId, $chatId, $updateId);

            return true;
        }

        if ($callbackData === 'menu:main') {
            $this->sendOrEditMessage($chatId, $messageId, 'Menu utama bot posting akun:', $this->mainMenuMarkup());

            return true;
        }

        if ($callbackData === 'menu:settings') {
            Cache::forget($this->pendingInputKey($telegramUserId));
            $this->sendSettingsMenu($chatId, $messageId);

            return true;
        }

        if ($callbackData === 'menu:rates') {
            Cache::forget($this->pendingInputKey($telegramUserId));
            $this->sendRatesMenu($chatId, $messageId);

            return true;
        }

        if ($callbackData === 'menu:catalog') {
            Cache::forget($this->pendingInputKey($telegramUserId));
            $this->sendCatalogMenu($chatId, $messageId);

            return true;
        }

        if ($callbackData === 'menu:help') {
            $this->sendOrEditMessage($chatId, $messageId, $this->helpText(), $this->mainMenuMarkup());

            return true;
        }

        if ($callbackData === 'catalog:main') {
            $this->sendCatalogMenu($chatId, $messageId);

            return true;
        }

        if ($callbackData === 'catalog:add-category') {
            Cache::put(
                $this->pendingInputKey($telegramUserId),
                ['type' => 'catalog_category', 'message_id' => $messageId],
                now()->addMinutes(10),
            );
            $this->editPendingPrompt($chatId, $messageId, 'Kirim nama kategori baru (maksimal 30 karakter), atau /cancel untuk batal.');

            return true;
        }

        if (str_starts_with($callbackData, 'catalog:category:')) {
            $categoryKey = substr($callbackData, strlen('catalog:category:'));
            $this->sendCatalogCategory($chatId, $messageId, $categoryKey);

            return true;
        }

        if (str_starts_with($callbackData, 'catalog:add-item:')) {
            $categoryKey = substr($callbackData, strlen('catalog:add-item:'));
            $category = StoreSetting::current()->telegramAccountCategories()[$categoryKey] ?? null;
            if ($category === null) {
                $this->sendCatalogMenu($chatId, $messageId, 'Kategori tidak ditemukan.');

                return true;
            }

            Cache::put(
                $this->pendingInputKey($telegramUserId),
                ['type' => 'catalog_item_name', 'category_key' => $categoryKey, 'message_id' => $messageId],
                now()->addMinutes(10),
            );
            $this->editPendingPrompt($chatId, $messageId, 'Kirim nama jenis item untuk kategori '.$category.' (maksimal 80 karakter), atau /cancel untuk batal.');

            return true;
        }

        if (str_starts_with($callbackData, 'settings:edit:')) {
            $field = substr($callbackData, strlen('settings:edit:'));
            if (! array_key_exists($field, self::SETTING_LABELS)) {
                return true;
            }

            Cache::put(
                $this->pendingInputKey($telegramUserId),
                ['type' => 'default', 'field' => $field, 'message_id' => $messageId],
                now()->addMinutes(10),
            );
            $currentValue = $this->accountDefaults()[$field] ?? '-';
            $this->editPendingPrompt(
                $chatId,
                $messageId,
                'Kirim nilai default untuk '.self::SETTING_LABELS[$field].'. Saat ini: '.$currentValue.'. /skip untuk mengembalikan default awal, /cancel untuk batal.',
            );

            return true;
        }

        if (str_starts_with($callbackData, 'rates:edit:')) {
            $currency = substr($callbackData, strlen('rates:edit:'));
            if (! in_array($currency, ['myr', 'usd'], true)) {
                return true;
            }

            Cache::put(
                $this->pendingInputKey($telegramUserId),
                ['type' => 'rate', 'field' => $currency, 'message_id' => $messageId],
                now()->addMinutes(10),
            );
            $this->editPendingPrompt(
                $chatId,
                $messageId,
                'Kirim nilai IDR untuk 1 '.($currency === 'myr' ? 'RM' : 'USD').', misalnya '.($currency === 'myr' ? '3600' : '15000').'. /cancel untuk batal.',
            );

            return true;
        }

        return false;
    }

    private function handleCategoryCallback(TelegramAccountDraft $draft, string $action): void
    {
        if ($draft->step !== 'categories') {
            return;
        }

        if ($action === 'add') {
            $draft->update(['step' => 'new_category']);
            $this->sendDraftMessage($draft, 'Kirim nama kategori baru (maksimal 30 karakter).');

            return;
        }

        if ($action === 'done') {
            $this->finishCategorySelection($draft);

            return;
        }

        $data = $draft->data;
        if (! array_key_exists($action, $data['category_options'] ?? [])) {
            return;
        }

        $selected = $data['selected_categories'] ?? [];
        if (in_array($action, $selected, true)) {
            $selected = array_values(array_filter($selected, fn (string $key): bool => $key !== $action));
        } else {
            $selected[] = $action;
        }

        $data['selected_categories'] = $selected;
        $draft->update(['data' => $data]);
        $this->sendCategoryKeyboard($draft->fresh());
    }

    private function handleItemCallback(TelegramAccountDraft $draft, string $action): void
    {
        if ($draft->step !== 'items') {
            return;
        }

        if ($action === 'done') {
            $this->finishItemSelection($draft);

            return;
        }

        if ($action === 'back') {
            $data = $draft->data;
            unset($data['active_item_category_key']);
            $draft->update(['data' => $data]);
            $this->sendItemKeyboard($draft->fresh());

            return;
        }

        if (str_starts_with($action, 'category:')) {
            $categoryKey = substr($action, strlen('category:'));
            if (! in_array($categoryKey, $draft->data['selected_categories'] ?? [], true)) {
                return;
            }

            $data = $draft->data;
            $data['active_item_category_key'] = $categoryKey;
            $draft->update(['data' => $data]);
            $this->sendCategoryItemsKeyboard($draft->fresh(), $categoryKey);

            return;
        }

        if (str_starts_with($action, 'add:')) {
            $this->startAddingItem($draft, substr($action, 4));

            return;
        }

        if (! str_starts_with($action, 'select:')) {
            return;
        }

        $itemId = filter_var(substr($action, 7), FILTER_VALIDATE_INT);
        if ($itemId === false) {
            return;
        }

        $data = $draft->data;
        $item = TelegramAccountItem::query()->find($itemId);
        $selectedCategoryLabels = array_map(
            fn (string $key): string => $data['category_options'][$key] ?? '',
            $data['selected_categories'] ?? [],
        );
        $activeCategoryLabel = $data['category_options'][$data['active_item_category_key'] ?? ''] ?? null;

        if ($item === null || ! in_array($item->category, $selectedCategoryLabels, true)
            || ($activeCategoryLabel !== null && $item->category !== $activeCategoryLabel)) {
            return;
        }

        $selectedItems = array_map('intval', $data['selected_item_ids'] ?? []);
        if (in_array($itemId, $selectedItems, true)) {
            $selectedItems = array_values(array_filter($selectedItems, fn (int $id): bool => $id !== $itemId));
        } elseif (count($selectedItems) >= self::MAX_LISTING_ITEMS) {
            $this->sendDraftMessage($draft, 'Maksimal 20 item dapat dipilih per posting.');

            return;
        } else {
            $selectedItems[] = $itemId;
        }

        $data['selected_item_ids'] = $selectedItems;
        $draft->update(['data' => $data]);
        if ($activeCategoryLabel !== null) {
            $this->sendCategoryItemsKeyboard($draft->fresh(), (string) $data['active_item_category_key']);
        } else {
            $this->sendItemKeyboard($draft->fresh());
        }
    }

    private function parsePriceInThousands(string $text): ?float
    {
        $normalized = str_replace(',', '.', trim($text));
        if (str_ends_with(mb_strtolower($normalized), 'k')) {
            $normalized = substr($normalized, 0, -1);
        }

        if (! is_numeric($normalized)) {
            return null;
        }

        $price = (float) $normalized * 1000;

        return $price > 0 && $price <= 1_000_000_000 ? round($price, 2) : null;
    }

    private function cancelDraft(TelegramAccountDraft $draft): void
    {
        foreach ($draft->images ?? [] as $image) {
            Storage::disk('public')->delete($image);
        }

        $draft->update(['status' => 'cancelled']);
        $this->sendDraftMessage($draft, 'Draft dibatalkan.', ['inline_keyboard' => []]);
    }

    /**
     * @param  array<string, mixed>|null  $replyMarkup
     */
    private function sendDraftMessage(TelegramAccountDraft $draft, string $text, ?array $replyMarkup = null): void
    {
        $data = $draft->data ?? [];
        $messageId = filter_var($data['bot_message_id'] ?? null, FILTER_VALIDATE_INT);

        if ($messageId !== false && $messageId !== null) {
            $markup = $replyMarkup ?? ($data['bot_message_markup'] ?? ['inline_keyboard' => []]);
            if (($data['bot_message_text'] ?? null) === $text
                && ($data['bot_message_markup'] ?? null) === $markup) {
                return;
            }

            $this->telegram->editMessageText($draft->chat_id, $messageId, $text, $markup);
            $data['bot_message_text'] = $text;
            $data['bot_message_markup'] = $markup;
            $draft->update(['data' => $data]);

            return;
        }

        $result = $this->telegram->sendMessage($draft->chat_id, $text, $replyMarkup);
        $messageId = filter_var($result['message_id'] ?? null, FILTER_VALIDATE_INT);
        if ($messageId === false || $messageId === null) {
            return;
        }

        $data['bot_message_id'] = $messageId;
        $data['bot_message_text'] = $text;
        $data['bot_message_markup'] = $replyMarkup;
        $draft->update(['data' => $data]);
    }
}
