<?php

namespace App\Jobs;

use App\Models\ProductCurrencyRate;
use App\Models\TelegramAccountDraft;
use App\Services\AccountListingFormatter;
use App\Services\TelegramBotApi;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class PublishTelegramAccountListing implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public array $backoff = [10, 30, 120];

    public int $uniqueFor = 1800;

    public int $timeout = 360;

    public function __construct(public int $telegramAccountDraftId) {}

    public function handle(TelegramBotApi $telegram, AccountListingFormatter $formatter): void
    {
        $draft = TelegramAccountDraft::query()->with('product')->findOrFail($this->telegramAccountDraftId);
        if ($draft->status === 'published') {
            return;
        }

        $channelId = config('services.telegram.channel_id');
        if (! is_string($channelId) || $channelId === '') {
            throw new RuntimeException('TELEGRAM_CHANNEL_ID is not configured.');
        }

        $product = $draft->product;
        if ($product === null) {
            throw new RuntimeException('The account listing has no website product.');
        }

        $images = $product->galleryImages();
        if ($images === []) {
            throw new RuntimeException('The account listing has no photos to publish.');
        }

        while ($draft->channel_media_offset < count($images)) {
            $batch = array_slice($images, $draft->channel_media_offset, 10);
            $caption = null;
            if (! $draft->channel_text_sent && $draft->channel_media_offset === 0) {
                $caption = $formatter->telegramHtml(
                    $draft->data,
                    ProductCurrencyRate::current(),
                    route('product-details.show', $product),
                );
            }

            $telegram->sendPhotoGroup($channelId, $batch, $caption);
            $draft->update([
                'channel_media_offset' => $draft->channel_media_offset + count($batch),
                'channel_text_sent' => $draft->channel_text_sent || $caption !== null,
            ]);
            $draft->refresh();
        }

        if (! $draft->channel_text_sent) {
            $text = $formatter->telegramHtml(
                $draft->data,
                ProductCurrencyRate::current(),
                route('product-details.show', $product),
            );
            $telegram->sendMessage($channelId, $text, parseMode: 'HTML');
            $draft->update(['channel_text_sent' => true]);
        }

        $telegram->sendMessage(
            $draft->chat_id,
            'Posting selesai. Produk website dan channel sudah tayang: '.route('product-details.show', $product),
        );
        $draft->update(['status' => 'published']);
    }

    public function uniqueId(): string
    {
        return (string) $this->telegramAccountDraftId;
    }

    public function failed(?Throwable $exception): void
    {
        TelegramAccountDraft::query()
            ->whereKey($this->telegramAccountDraftId)
            ->where('status', 'publishing')
            ->update(['status' => 'failed']);

        Log::error('Telegram account listing publication failed.', [
            'telegram_account_draft_id' => $this->telegramAccountDraftId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
