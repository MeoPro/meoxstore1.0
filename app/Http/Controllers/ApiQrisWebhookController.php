<?php

namespace App\Http\Controllers;

use App\Actions\ProcessApiQrisWebhook;
use App\Services\ApiQrisClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class ApiQrisWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        ApiQrisClient $apiQrisClient,
        ProcessApiQrisWebhook $processWebhook,
    ): Response {
        abort_unless(
            $apiQrisClient->hasValidWebhookSignature(
                $request->getContent(),
                $request->header('X-APIQris-Signature'),
            ),
            401,
        );

        $payload = $request->json()->all();
        if (($payload['event'] ?? null) === 'ping') {
            return response()->noContent();
        }

        $event = validator($payload, [
            'event' => ['required', 'string', Rule::in(['invoice.paid', 'invoice.expired', 'invoice.cancelled'])],
            'id' => ['required', 'string', 'max:100'],
            'order_id' => ['required', 'string', 'max:64'],
            'amount' => ['required', 'integer', 'min:0'],
            'total_amount' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'string', Rule::in(['paid', 'expired', 'cancelled'])],
        ])->validate();

        $expectedStatus = match ($event['event']) {
            'invoice.paid' => 'paid',
            'invoice.expired' => 'expired',
            'invoice.cancelled' => 'cancelled',
        };

        abort_unless($event['status'] === $expectedStatus, 422);

        $processWebhook->handle($event);

        return response()->noContent();
    }
}
