<?php

namespace App\Jobs;

use App\Models\TelegramUpdate;
use App\Services\TelegramAccountPostingBot;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessTelegramUpdate implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public array $backoff = [5, 15, 60];

    public int $uniqueFor = 900;

    public int $timeout = 240;

    public function __construct(public int $telegramUpdateId) {}

    public function handle(TelegramAccountPostingBot $bot): void
    {
        $telegramUpdate = TelegramUpdate::query()->findOrFail($this->telegramUpdateId);
        if ($telegramUpdate->status === 'processed' || $telegramUpdate->status === 'failed') {
            return;
        }

        $telegramUpdate->update(['status' => 'processing']);
        $bot->handle($telegramUpdate);
        $telegramUpdate->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }

    public function uniqueId(): string
    {
        return (string) $this->telegramUpdateId;
    }

    public function middleware(): array
    {
        $telegramUpdate = TelegramUpdate::query()->find($this->telegramUpdateId);
        $payload = $telegramUpdate?->payload ?? [];
        $telegramUserId = $payload['message']['from']['id']
            ?? $payload['callback_query']['from']['id']
            ?? null;

        return $telegramUserId === null
            ? []
            : [(new WithoutOverlapping('telegram-account-'.$telegramUserId))->releaseAfter(3)->expireAfter(180)];
    }

    public function failed(?Throwable $exception): void
    {
        TelegramUpdate::query()->whereKey($this->telegramUpdateId)->update(['status' => 'failed']);

        Log::error('Telegram account posting update failed.', [
            'telegram_update_id' => $this->telegramUpdateId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
