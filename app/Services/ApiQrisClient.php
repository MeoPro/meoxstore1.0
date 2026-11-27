<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use UnexpectedValueException;

class ApiQrisClient
{
    public function isConfigured(): bool
    {
        $callbackUrl = config('services.apiqris.callback_url');

        return filled(config('services.apiqris.api_key'))
            && filled(config('services.apiqris.webhook_secret'))
            && is_string($callbackUrl)
            && filter_var($callbackUrl, FILTER_VALIDATE_URL) !== false
            && parse_url($callbackUrl, PHP_URL_SCHEME) === 'https';
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function createInvoice(int $amount, string $orderId, string $description): array
    {
        $callbackUrl = config('services.apiqris.callback_url');
        $expiredMinutes = (int) config('services.apiqris.expired_minutes', 30);
        if ($expiredMinutes < 5 || $expiredMinutes > 1440) {
            throw new InvalidArgumentException('APIQris invoice expiry must be between 5 and 1440 minutes.');
        }
        if (
            ! is_string($callbackUrl)
            || filter_var($callbackUrl, FILTER_VALIDATE_URL) === false
            || parse_url($callbackUrl, PHP_URL_SCHEME) !== 'https'
        ) {
            throw new InvalidArgumentException('APIQris callback URL must use HTTPS.');
        }

        $payload = [
            'amount' => $amount,
            'order_id' => $orderId,
            'expired_minutes' => $expiredMinutes,
            'description' => $description,
            'callback_url' => $callbackUrl,
        ];

        $response = $this->request()
            ->post('/invoices', $payload)
            ->throw();

        $invoice = $this->invoiceData($response->json(), requireQr: true);
        if (
            $invoice['order_id'] !== $orderId
            || $invoice['amount'] !== $amount
            || $invoice['status'] !== 'pending'
        ) {
            throw new UnexpectedValueException('APIQris returned an invoice that does not match the order.');
        }

        return $invoice;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function getInvoice(string $orderId): array
    {
        $response = $this->request()->get('/invoices/'.rawurlencode($orderId));

        if ($response->notFound()) {
            return ['status' => 'not_found'];
        }

        $response->throw();

        return $this->invoiceData($response->json(), requireQr: true);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function cancelInvoice(string $orderId): array
    {
        $response = $this->request()->post('/invoices/'.rawurlencode($orderId).'/cancel');

        if ($response->notFound()) {
            return ['status' => 'not_found'];
        }

        if ($response->conflict()) {
            return $this->getInvoice($orderId);
        }

        $response->throw();

        return $this->invoiceData($response->json(), requireQr: false);
    }

    public function hasValidWebhookSignature(string $body, ?string $signatureHeader): bool
    {
        $secret = config('services.apiqris.webhook_secret');
        if (! is_string($secret) || $secret === '' || ! is_string($signatureHeader)) {
            return false;
        }

        if (! preg_match('/^\s*t=(\d+)\s*,\s*v1=([a-f0-9]{64})\s*$/i', $signatureHeader, $matches)) {
            return false;
        }

        $timestamp = (int) $matches[1];
        if (abs(now()->timestamp - $timestamp) > 300) {
            return false;
        }

        $expected = hash_hmac('sha256', $matches[1].'.'.$body, $secret);

        return hash_equals($expected, strtolower($matches[2]));
    }

    private function request(): PendingRequest
    {
        $apiKey = config('services.apiqris.api_key');
        if (! is_string($apiKey) || $apiKey === '') {
            throw new InvalidArgumentException('APIQris API key is not configured.');
        }

        return Http::baseUrl(rtrim((string) config('services.apiqris.base_url'), '/'))
            ->acceptJson()
            ->withToken($apiKey)
            ->connectTimeout(3)
            ->timeout(15);
    }

    /**
     * @return array<string, mixed>
     */
    private function invoiceData(mixed $payload, bool $requireQr): array
    {
        if (! is_array($payload)) {
            throw new UnexpectedValueException('APIQris returned an invalid invoice response.');
        }

        $invoice = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;
        $amount = $this->integerValue($invoice['amount'] ?? null);
        $totalAmount = $this->integerValue($invoice['total_amount'] ?? null);
        $invalidFields = [];

        if (! is_string($invoice['id'] ?? null)) {
            $invalidFields[] = $this->invalidFieldDescription($invoice, 'id');
        }
        if (! is_string($invoice['order_id'] ?? null)) {
            $invalidFields[] = $this->invalidFieldDescription($invoice, 'order_id');
        }
        if ($amount === null) {
            $invalidFields[] = $this->invalidFieldDescription($invoice, 'amount');
        }
        if ($totalAmount === null) {
            $invalidFields[] = $this->invalidFieldDescription($invoice, 'total_amount');
        }
        if (
            ! is_string($invoice['status'] ?? null)
            || ! in_array($invoice['status'], ['pending', 'paid', 'expired', 'cancelled'], true)
        ) {
            $invalidFields[] = 'status (unsupported)';
        }
        if ($amount !== null && $totalAmount !== null && $totalAmount < $amount) {
            $invalidFields[] = 'total_amount (less than amount)';
        }
        if ($invalidFields !== []) {
            throw new UnexpectedValueException(
                'APIQris returned incomplete invoice details: '.implode(', ', $invalidFields).'.',
            );
        }

        $invoice['amount'] = $amount;
        $invoice['total_amount'] = $totalAmount;

        if ($requireQr) {
            $imageUrl = $invoice['qr_image_url'] ?? null;
            $imageHost = is_string($imageUrl) ? parse_url($imageUrl, PHP_URL_HOST) : null;
            if (
                ! is_string($imageUrl)
                || parse_url($imageUrl, PHP_URL_SCHEME) !== 'https'
                || $imageHost !== 'app.apiqris.com'
                || ! is_string($invoice['expires_at'] ?? null)
            ) {
                throw new UnexpectedValueException('APIQris returned invalid QR or expiry details.');
            }
        }

        return $invoice;
    }

    private function integerValue(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (! is_string($value) || ! preg_match('/\A(?:0|[1-9]\d*)\z/', $value)) {
            return null;
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($integer) ? $integer : null;
    }

    /**
     * @param  array<string, mixed>  $invoice
     */
    private function invalidFieldDescription(array $invoice, string $field): string
    {
        $type = array_key_exists($field, $invoice) ? get_debug_type($invoice[$field]) : 'missing';

        return $field.' ('.$type.')';
    }
}
