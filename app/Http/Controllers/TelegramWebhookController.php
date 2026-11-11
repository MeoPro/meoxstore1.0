<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTelegramUpdate;
use App\Models\TelegramUpdate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $secret = config('services.telegram.webhook_secret');
        $providedSecret = $request->header('X-Telegram-Bot-Api-Secret-Token');

        abort_unless(is_string($secret) && $secret !== '', 503, 'Telegram webhook secret is not configured.');
        abort_unless(is_string($providedSecret) && hash_equals($secret, $providedSecret), 403);

        $validated = $request->validate([
            'update_id' => ['required', 'integer', 'min:0'],
        ]);
        $payload = $request->all();
        $telegramUserId = data_get($payload, 'message.from.id')
            ?? data_get($payload, 'callback_query.from.id');

        if (! is_string($telegramUserId) && ! is_int($telegramUserId)) {
            return response()->json(['ok' => true]);
        }

        $allowedAdminIds = config('services.telegram.admin_ids', []);
        if (! in_array((string) $telegramUserId, $allowedAdminIds, true)) {
            return response()->json(['ok' => true]);
        }

        $now = now();

        DB::table('telegram_updates')->insertOrIgnore([
            'telegram_update_id' => $validated['update_id'],
            'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            'status' => 'pending',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $telegramUpdate = TelegramUpdate::query()
            ->where('telegram_update_id', $validated['update_id'])
            ->firstOrFail();

        if ($telegramUpdate->status === 'pending') {
            ProcessTelegramUpdate::dispatch($telegramUpdate->id);
        }

        return response()->json(['ok' => true]);
    }
}
