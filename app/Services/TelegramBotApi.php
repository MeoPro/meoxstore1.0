<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class TelegramBotApi
{
    /**
     * @param  array<string, mixed>|null  $replyMarkup
     * @return array<string, mixed>
     */
    public function sendMessage(string $chatId, string $text, ?array $replyMarkup = null, ?string $parseMode = null): array
    {
        $parameters = [
            'chat_id' => $chatId,
            'text' => $text,
            'disable_web_page_preview' => true,
        ];

        if ($replyMarkup !== null) {
            $parameters['reply_markup'] = $replyMarkup;
        }

        if ($parseMode !== null) {
            $parameters['parse_mode'] = $parseMode;
        }

        return $this->call('sendMessage', $parameters);
    }

    /**
     * @param  array<string, mixed>  $replyMarkup
     * @return array<string, mixed>
     */
    public function editMessageReplyMarkup(string $chatId, int $messageId, array $replyMarkup): array
    {
        return $this->call('editMessageReplyMarkup', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'reply_markup' => $replyMarkup,
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $replyMarkup
     * @return array<string, mixed>
     */
    public function editMessageText(string $chatId, int $messageId, string $text, ?array $replyMarkup = null): array
    {
        $parameters = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'disable_web_page_preview' => true,
        ];

        if ($replyMarkup !== null) {
            $parameters['reply_markup'] = $replyMarkup;
        }

        return $this->call('editMessageText', $parameters);
    }

    /**
     * @return array<string, mixed>
     */
    public function answerCallbackQuery(string $callbackQueryId, ?string $text = null): array
    {
        $parameters = ['callback_query_id' => $callbackQueryId];
        if ($text !== null) {
            $parameters['text'] = $text;
        }

        return $this->call('answerCallbackQuery', $parameters);
    }

    public function downloadPhoto(string $fileId): string
    {
        $file = $this->call('getFile', ['file_id' => $fileId]);
        $filePath = $file['file_path'] ?? null;

        if (! is_string($filePath) || $filePath === '') {
            throw new RuntimeException('Telegram did not return a photo file path.');
        }

        try {
            $response = $this->http()->get($this->fileUrl($filePath));
        } catch (ConnectionException) {
            throw new RuntimeException('Telegram photo download failed due to a connection error.');
        }
        if (! $response->successful()) {
            throw new RuntimeException('Telegram photo download failed with HTTP '.$response->status().'.');
        }

        $contents = $response->body();
        if ($contents === '' || strlen($contents) > 20 * 1024 * 1024) {
            throw new RuntimeException('The Telegram photo is empty or exceeds the 20 MB limit.');
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents);
        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw new RuntimeException('Telegram returned an unsupported image format.'),
        };
        $storedPath = 'products/telegram-'.Str::uuid().'.'.$extension;

        if (! Storage::disk('public')->put($storedPath, $contents)) {
            throw new RuntimeException('Unable to store the Telegram photo.');
        }

        return $storedPath;
    }

    /**
     * @param  array<int, string>  $imagePaths
     * @return array<int, array<string, mixed>>
     */
    public function sendPhotoGroup(string $chatId, array $imagePaths, ?string $caption = null): array
    {
        if ($imagePaths === [] || count($imagePaths) > 10) {
            throw new RuntimeException('Telegram photo groups must contain between 1 and 10 images.');
        }

        if (count($imagePaths) === 1) {
            $stream = Storage::disk('public')->readStream($imagePaths[0]);
            if (! is_resource($stream)) {
                throw new RuntimeException('Unable to read a product image for Telegram.');
            }

            try {
                $request = $this->http()->attach('photo', $stream, basename($imagePaths[0]));
                $parameters = ['chat_id' => $chatId];
                if ($caption !== null) {
                    $parameters['caption'] = $caption;
                    $parameters['parse_mode'] = 'HTML';
                }

                $result = $this->responseResult(
                    $this->post($request, $this->apiUrl('sendPhoto'), $parameters, 'sendPhoto'),
                    'sendPhoto',
                );
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }

            return is_array($result) ? [$result] : [];
        }

        $request = $this->http();
        $media = [];
        $streams = [];

        foreach ($imagePaths as $index => $imagePath) {
            $stream = Storage::disk('public')->readStream($imagePath);
            if (! is_resource($stream)) {
                throw new RuntimeException('Unable to read a product image for Telegram.');
            }

            $attachmentName = 'image'.$index;
            $streams[] = $stream;
            $request = $request->attach($attachmentName, $stream, basename($imagePath));
            $mediaItem = [
                'type' => 'photo',
                'media' => 'attach://'.$attachmentName,
            ];

            if ($index === 0 && $caption !== null) {
                $mediaItem['caption'] = $caption;
                $mediaItem['parse_mode'] = 'HTML';
            }

            $media[] = $mediaItem;
        }

        try {
            $result = $this->responseResult(
                $this->post($request, $this->apiUrl('sendMediaGroup'), [
                    'chat_id' => $chatId,
                    'media' => json_encode($media, JSON_THROW_ON_ERROR),
                ], 'sendMediaGroup'),
                'sendMediaGroup',
            );
        } finally {
            foreach ($streams as $stream) {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }

        return is_array($result) ? $result : [];
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return array<string, mixed>
     */
    private function call(string $method, array $parameters): array
    {
        $result = $this->responseResult(
            $this->post($this->http(), $this->apiUrl($method), $parameters, $method),
            $method,
        );

        return is_array($result) ? $result : [];
    }

    private function responseResult(Response $response, string $method): mixed
    {
        if (! $response->successful()) {
            throw new RuntimeException('Telegram '.$method.' request failed with HTTP '.$response->status().'.');
        }

        $body = $response->json();
        if (! is_array($body) || ($body['ok'] ?? false) !== true) {
            $description = is_array($body) && is_string($body['description'] ?? null)
                ? ': '.$body['description']
                : '.';
            throw new RuntimeException('Telegram '.$method.' request was rejected'.$description);
        }

        return $body['result'] ?? [];
    }

    private function http(): PendingRequest
    {
        return Http::connectTimeout(5)->timeout(60);
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function post(PendingRequest $request, string $url, array $parameters, string $method): Response
    {
        try {
            return $request->post($url, $parameters);
        } catch (ConnectionException) {
            throw new RuntimeException('Telegram '.$method.' request failed due to a connection error.');
        }
    }

    private function apiUrl(string $method): string
    {
        $token = config('services.telegram.bot_token');
        if (! is_string($token) || $token === '') {
            throw new RuntimeException('TELEGRAM_BOT_TOKEN is not configured.');
        }

        return 'https://api.telegram.org/bot'.$token.'/'.$method;
    }

    private function fileUrl(string $filePath): string
    {
        $token = config('services.telegram.bot_token');
        if (! is_string($token) || $token === '') {
            throw new RuntimeException('TELEGRAM_BOT_TOKEN is not configured.');
        }

        return 'https://api.telegram.org/file/bot'.$token.'/'.$filePath;
    }
}
