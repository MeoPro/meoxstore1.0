<?php

namespace App\Services;

use App\Actions\CreateTelegramAccountProduct;
use App\Jobs\PublishTelegramAccountListing;
use App\Models\ProductCurrencyRate;
use App\Models\TelegramAccountDraft;
use App\Models\TelegramAccountItem;
use App\Models\TelegramUpdate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TelegramAccountPostingBot
{
    private const MAX_IMAGES = 20;

    private const MAX_LISTING_ITEMS = 20;

    private const MAX_CATEGORIES = 20;

    /**
     * @var array<string, string>
     */
    private const DEFAULT_CATEGORIES = [
        'sword' => 'Sword',
        'gun' => 'Gun',
        'fruit' => 'Fruit',
        'gamepass' => 'Gamepass',
        'perm' => 'Perm',
        'awaken' => 'Awaken',
        'race' => 'Race',
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
            $this->telegram->sendMessage(
                $chatId,
                "Kirim /new untuk membuat posting akun.\nKirim foto satu per satu atau sebagai album, lalu /done.\nKirim /cancel kapan saja untuk membatalkan draft.",
            );

            return;
        }

        if ($this->isCommand($text, '/new')) {
            $this->startDraft($telegramUserId, $chatId, $updateId);

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

    private function startDraft(string $telegramUserId, string $chatId, int $updateId): void
    {
        $existingDraft = TelegramAccountDraft::query()
            ->where('telegram_user_id', $telegramUserId)
            ->where('status', 'active')
            ->latest('id')
            ->first();

        if ($existingDraft !== null) {
            $existingDraft->update(['last_update_id' => $updateId, 'chat_id' => $chatId]);
            $this->telegram->sendMessage($chatId, 'Masih ada draft aktif. Kirim /cancel untuk membatalkan atau lanjutkan draft tersebut.');

            return;
        }

        TelegramAccountDraft::query()->create([
            'telegram_user_id' => $telegramUserId,
            'chat_id' => $chatId,
            'status' => 'active',
            'step' => 'photos',
            'last_update_id' => $updateId,
            'data' => [
                'items' => [],
                'category_options' => self::DEFAULT_CATEGORIES,
                'selected_categories' => [],
                'account_status' => '100% SAFE ANTI HB, NO VERIFIKASI',
                'status_label' => 'READY✅',
                'haki' => 'SKY JUMP, BODY, FLASH STEP',
                'race' => 'V3 HUMAN',
                'awaken' => '-',
                'permanent' => '-',
                'gamepass' => '-',
                'stat_refund' => 2,
                'post_chat' => (string) config('services.telegram.post_chat', ''),
                'post_instagram' => (string) config('services.telegram.post_instagram', ''),
            ],
            'images' => [],
            'processed_message_ids' => [],
        ]);

        $this->telegram->sendMessage(
            $chatId,
            'Kirim foto akun (maksimal 20 foto). Setelah semua foto terkirim, kirim /done. Kirim /cancel untuk membatalkan.',
        );
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function receivePhoto(TelegramAccountDraft $draft, array $message, int $updateId): void
    {
        if ($draft->step !== 'photos') {
            $this->telegram->sendMessage($draft->chat_id, 'Foto hanya bisa ditambahkan pada langkah pertama. Lanjutkan dengan jawaban yang diminta atau kirim /cancel.');

            return;
        }

        $messageId = (int) ($message['message_id'] ?? 0);
        $processedMessageIds = $draft->processed_message_ids ?? [];
        if (in_array($messageId, $processedMessageIds, true)) {
            return;
        }

        $images = $draft->images ?? [];
        if (count($images) >= self::MAX_IMAGES) {
            $this->telegram->sendMessage($draft->chat_id, 'Batas 20 foto tercapai. Kirim /done untuk melanjutkan.');

            return;
        }

        $photoSizes = $message['photo'];
        $largestPhoto = end($photoSizes);
        $fileId = is_array($largestPhoto) ? ($largestPhoto['file_id'] ?? null) : null;

        if (! is_string($fileId) || $fileId === '') {
            $this->telegram->sendMessage($draft->chat_id, 'Foto tidak terbaca. Coba kirim ulang.');

            return;
        }

        $images[] = $this->telegram->downloadPhoto($fileId);
        $processedMessageIds[] = $messageId;
        $draft->update([
            'images' => $images,
            'processed_message_ids' => $processedMessageIds,
            'last_update_id' => $updateId,
        ]);

        $this->telegram->sendMessage($draft->chat_id, 'Foto diterima ('.count($images).'/'.self::MAX_IMAGES.'). Kirim foto lagi atau /done.');
    }

    private function finishCurrentStep(TelegramAccountDraft $draft): void
    {
        if ($draft->step === 'photos') {
            if (($draft->images ?? []) === []) {
                $this->telegram->sendMessage($draft->chat_id, 'Kirim minimal satu foto sebelum melanjutkan.');

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

        $this->telegram->sendMessage($draft->chat_id, 'Kirim jawaban untuk melanjutkan langkah ini.');
    }

    private function receiveText(TelegramAccountDraft $draft, string $text): void
    {
        if ($draft->step === 'photos') {
            $this->telegram->sendMessage($draft->chat_id, 'Kirim foto, lalu /done jika sudah selesai.');

            return;
        }

        if ($draft->step === 'categories') {
            $this->telegram->sendMessage($draft->chat_id, 'Pilih kategori melalui tombol di bawah, lalu tekan Selesai.');

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
            $this->telegram->sendMessage($draft->chat_id, 'Periksa preview, lalu pilih Posting atau Batal melalui tombol.');

            return;
        }

        $this->saveStepValue($draft, $text);
    }

    private function saveStepValue(TelegramAccountDraft $draft, string $text): void
    {
        $data = $draft->data;
        $step = $draft->step;

        if ($step === 'username' && ($text === '' || Str::length($text) > 40)) {
            $this->telegram->sendMessage($draft->chat_id, 'Nama akun wajib diisi (maksimal 40 karakter).');

            return;
        }

        if ($step === 'level' && (! ctype_digit($text) || (int) $text < 1 || (int) $text > 100_000)) {
            $this->telegram->sendMessage($draft->chat_id, 'Level harus berupa angka antara 1 dan 100000.');

            return;
        }

        if ($step === 'stat_refund') {
            if ($this->isCommand($text, '/skip')) {
                $data['stat_refund'] = 2;
            } elseif (! ctype_digit($text) || (int) $text > 100) {
                $this->telegram->sendMessage($draft->chat_id, 'Stat refund harus angka 0–100, atau /skip untuk memakai default 2.');

                return;
            } else {
                $data['stat_refund'] = (int) $text;
            }
        } elseif ($step === 'price') {
            $price = $this->parsePriceInThousands($text);
            if ($price === null) {
                $this->telegram->sendMessage($draft->chat_id, 'Masukkan harga dalam ribuan, misalnya 50 untuk Rp50.000 (atau 50.5 untuk Rp50.500).');

                return;
            }

            $data['price_idr'] = $price;
        } else {
            $defaults = [
                'belly' => '-',
                'fragment' => '-',
                'haki' => 'SKY JUMP, BODY, FLASH STEP',
                'race' => 'V3 HUMAN',
                'awaken' => '-',
                'permanent' => '-',
                'gamepass' => '-',
            ];
            $value = $this->isCommand($text, '/skip') ? ($defaults[$step] ?? '-') : $text;

            if (Str::length($value) > 255) {
                $this->telegram->sendMessage($draft->chat_id, 'Jawaban maksimal 255 karakter.');

                return;
            }

            $data[$step] = $value;
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
            'username' => 'level',
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

        $this->telegram->sendMessage($draft->chat_id, $prompts[$step] ?? 'Kirim jawaban untuk melanjutkan.');
    }

    private function addCategory(TelegramAccountDraft $draft, string $category): void
    {
        $category = trim($category);
        if ($category === '' || Str::length($category) > 30 || str_contains($category, ':')) {
            $this->telegram->sendMessage($draft->chat_id, 'Nama kategori harus 1–30 karakter dan tidak boleh berisi titik dua.');

            return;
        }

        $data = $draft->data;
        $key = 'custom_'.substr(sha1(mb_strtolower($category)), 0, 12);
        $options = $data['category_options'] ?? self::DEFAULT_CATEGORIES;
        if (! array_key_exists($key, $options) && count($options) >= self::MAX_CATEGORIES) {
            $this->telegram->sendMessage($draft->chat_id, 'Maksimal 20 kategori dapat dipilih atau ditambahkan.');

            return;
        }

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
            $this->telegram->sendMessage($draft->chat_id, 'Pilih minimal satu kategori atau tambahkan kategori baru.');

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

        $this->telegram->sendMessage(
            $draft->chat_id,
            ($notice === null ? '' : $notice."\n").'Pilih kategori yang dipakai. Tekan kategori untuk memilih atau membatalkan pilihan.',
            $keyboard,
        );
    }

    private function sendItemKeyboard(TelegramAccountDraft $draft, ?string $notice = null): void
    {
        $data = $draft->data;
        $categories = $data['category_options'] ?? self::DEFAULT_CATEGORIES;
        $selectedCategoryKeys = $data['selected_categories'] ?? [];
        $selectedCategories = array_map(
            fn (string $key): string => $categories[$key] ?? '',
            $selectedCategoryKeys,
        );
        $selectedItems = array_map('intval', $data['selected_item_ids'] ?? []);
        $buttons = [];

        foreach (TelegramAccountItem::query()
            ->whereIn('category', $selectedCategories)
            ->orderBy('category')
            ->orderBy('name')
            ->get() as $item) {
            $label = $item->category.': '.($item->emoji ?? '').$item->name;
            $buttons[] = [
                'text' => (in_array($item->id, $selectedItems, true) ? '✅ ' : '').Str::limit($label, 60),
                'callback_data' => 'item:select:'.$item->id,
            ];
        }

        $addButtons = [];
        foreach ($selectedCategoryKeys as $key) {
            if (isset($categories[$key])) {
                $addButtons[] = [
                    'text' => '➕ '.$categories[$key],
                    'callback_data' => 'item:add:'.$key,
                ];
            }
        }

        $keyboard = [
            'inline_keyboard' => [
                ...array_chunk($buttons, 2),
                ...array_chunk($addButtons, 2),
                [['text' => '✅ Selesai memilih item', 'callback_data' => 'item:done']],
            ],
        ];

        $this->telegram->sendMessage(
            $draft->chat_id,
            ($notice === null ? '' : $notice."\n").'Pilih beberapa item dari daftar. Item baru dapat ditambahkan dan disimpan untuk posting berikutnya.',
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
        $draft->update(['data' => $data, 'step' => 'new_item_name']);
        $this->telegram->sendMessage(
            $draft->chat_id,
            'Kirim nama item baru untuk kategori '.$data['pending_item_category'].' (maksimal 80 karakter).',
        );
    }

    private function saveNewItemName(TelegramAccountDraft $draft, string $name): void
    {
        $name = trim($name);
        if ($name === '' || Str::length($name) > 80) {
            $this->telegram->sendMessage($draft->chat_id, 'Nama item wajib diisi dan maksimal 80 karakter.');

            return;
        }

        $data = $draft->data;
        $data['pending_item_name'] = $name;
        $draft->update(['data' => $data, 'step' => 'new_item_emoji']);
        $this->telegram->sendMessage($draft->chat_id, 'Kirim emoji/icon untuk item ini, atau /skip jika tanpa emoji.');
    }

    private function saveNewItemEmoji(TelegramAccountDraft $draft, string $emoji): void
    {
        $emoji = trim($emoji);
        if ($this->isCommand($emoji, '/skip') || $emoji === '-') {
            $emoji = '';
        }

        if (Str::length($emoji) > 8) {
            $this->telegram->sendMessage($draft->chat_id, 'Emoji maksimal 8 karakter, atau /skip untuk tanpa emoji.');

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
        if (count($selectedItems) >= self::MAX_LISTING_ITEMS) {
            $this->telegram->sendMessage($draft->chat_id, 'Maksimal 20 item per posting. Item baru tetap tersimpan di daftar kategori.');
        } elseif (! in_array($item->id, $selectedItems, true)) {
            $selectedItems[] = $item->id;
        }

        $data['selected_item_ids'] = $selectedItems;
        unset($data['pending_item_category'], $data['pending_item_name']);
        $draft->update(['data' => $data, 'step' => 'items']);
        $this->sendItemKeyboard($draft->fresh(), 'Item tersimpan ke daftar dan dipilih untuk posting ini.');
    }

    private function finishItemSelection(TelegramAccountDraft $draft): void
    {
        $selectedIds = array_map('intval', $draft->data['selected_item_ids'] ?? []);
        if ($selectedIds === []) {
            $this->telegram->sendMessage($draft->chat_id, 'Pilih minimal satu item sebelum melanjutkan.');

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
            $this->telegram->sendMessage($draft->chat_id, 'Item yang dipilih tidak ditemukan. Pilih item kembali.');

            return;
        }

        $draft->update(['data' => $data, 'step' => 'haki']);
        $this->askForStep($draft, 'haki');
    }

    private function sendPreview(TelegramAccountDraft $draft): void
    {
        $rates = ProductCurrencyRate::current();
        $this->telegram->sendMessage(
            $draft->chat_id,
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
        $draft = TelegramAccountDraft::query()
            ->where('telegram_user_id', $telegramUserId)
            ->where('status', 'active')
            ->latest('id')
            ->first();

        if ($draft === null || ($draft->last_update_id !== null && $updateId <= $draft->last_update_id)) {
            return;
        }

        $draft->last_update_id = $updateId;
        $callbackData = (string) ($callback['data'] ?? '');

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
            $data['description'] = $this->formatter->plainText($data, ProductCurrencyRate::current());
            $draft->update(['data' => $data]);
            $product = $this->createProduct->execute($draft->fresh());
            $draft->update(['last_update_id' => $updateId]);
            PublishTelegramAccountListing::dispatch($draft->fresh()->id);
            $productUrl = route('product-details.show', $product);

            $this->telegram->sendMessage(
                $draft->chat_id,
                'Produk sudah tayang di website: '.$productUrl."\nPosting foto dan detail ke channel sedang diproses.",
            );
        }
    }

    private function handleCategoryCallback(TelegramAccountDraft $draft, string $action): void
    {
        if ($draft->step !== 'categories') {
            return;
        }

        if ($action === 'add') {
            $draft->update(['step' => 'new_category']);
            $this->telegram->sendMessage($draft->chat_id, 'Kirim nama kategori baru (maksimal 30 karakter).');

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

        if ($item === null || ! in_array($item->category, $selectedCategoryLabels, true)) {
            return;
        }

        $selectedItems = array_map('intval', $data['selected_item_ids'] ?? []);
        if (in_array($itemId, $selectedItems, true)) {
            $selectedItems = array_values(array_filter($selectedItems, fn (int $id): bool => $id !== $itemId));
        } elseif (count($selectedItems) >= self::MAX_LISTING_ITEMS) {
            $this->telegram->sendMessage($draft->chat_id, 'Maksimal 20 item dapat dipilih per posting.');

            return;
        } else {
            $selectedItems[] = $itemId;
        }

        $data['selected_item_ids'] = $selectedItems;
        $draft->update(['data' => $data]);
        $this->sendItemKeyboard($draft->fresh());
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
        $this->telegram->sendMessage($draft->chat_id, 'Draft dibatalkan.');
    }
}
