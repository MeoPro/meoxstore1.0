<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TelegramSetWebhook extends Command
{
    protected $signature = 'telegram:set-webhook';

    protected $description = 'Register the Telegram webhook for account-posting updates.';

    public function handle(): int
    {
        $token = config('services.telegram.bot_token');
        $secret = config('services.telegram.webhook_secret');
        $adminIds = config('services.telegram.admin_ids', []);
        $channelId = config('services.telegram.channel_id');

        if (! is_string($token) || $token === '' || ! is_string($secret) || $secret === '' || $adminIds === []) {
            throw new RuntimeException('Set TELEGRAM_BOT_TOKEN, TELEGRAM_WEBHOOK_SECRET, and TELEGRAM_ADMIN_IDS before registering the webhook.');
        }

        if (! is_string($channelId) || $channelId === '') {
            throw new RuntimeException('Set TELEGRAM_CHANNEL_ID before registering the webhook.');
        }

        if (preg_match('/^[A-Za-z0-9_-]{1,256}$/', $secret) !== 1) {
            throw new RuntimeException('TELEGRAM_WEBHOOK_SECRET must contain 1–256 letters, numbers, underscores, or hyphens.');
        }

        $webhookUrl = route('telegram.webhook');
        if (! str_starts_with($webhookUrl, 'https://')) {
            throw new RuntimeException('APP_URL must use HTTPS before registering the Telegram webhook.');
        }

        try {
            $response = Http::connectTimeout(5)
                ->timeout(15)
                ->post('https://api.telegram.org/bot'.$token.'/setWebhook', [
                    'url' => $webhookUrl,
                    'secret_token' => $secret,
                    'allowed_updates' => ['message', 'callback_query'],
                ]);
        } catch (ConnectionException) {
            throw new RuntimeException('Telegram webhook registration failed due to a connection error.');
        }

        if (! $response->successful() || $response->json('ok') !== true) {
            throw new RuntimeException('Telegram rejected the webhook registration.');
        }

        $this->info('Telegram webhook registered for '.$webhookUrl);

        return self::SUCCESS;
    }
}
