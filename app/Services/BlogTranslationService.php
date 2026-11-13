<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BlogTranslationService
{
    private const ENDPOINT = 'https://translation.googleapis.com/language/translate/v2';

    private const MAX_CHARS_PER_REQUEST = 25000;

    public function translate(string $text, string $sourceLocale, string $targetLocale): string
    {
        if (trim($text) === '') {
            return $text;
        }

        $apiKey = config('services.google_translate.api_key');
        $projectId = config('services.google_translate.project_id');
        $location = config('services.google_translate.location');

        if (! is_string($apiKey) || $apiKey === '' || ! is_string($projectId) || $projectId === '') {
            throw new BlogTranslationException(
                'Google Cloud Translation is not configured. Set GOOGLE_TRANSLATE_API_KEY and GOOGLE_CLOUD_PROJECT_ID.',
            );
        }

        $model = sprintf(
            'projects/%s/locations/%s/models/general/translation-llm',
            $projectId,
            is_string($location) && $location !== '' ? $location : 'global',
        );
        $translatedChunks = [];

        foreach ($this->splitText($text) as $chunk) {
            try {
                $response = Http::asForm()
                    ->acceptJson()
                    ->connectTimeout(3)
                    ->timeout(20)
                    ->post(self::ENDPOINT.'?key='.rawurlencode($apiKey), [
                        'q' => $chunk,
                        'source' => $sourceLocale,
                        'target' => $targetLocale,
                        'format' => 'text',
                        'model' => $model,
                    ]);
            } catch (ConnectionException $exception) {
                Log::warning('Google Cloud Translation connection failed.', [
                    'target_locale' => $targetLocale,
                    'exception_class' => $exception::class,
                ]);

                throw new BlogTranslationException(
                    'Could not reach Google Cloud Translation. Check server connectivity and try again.',
                    previous: $exception,
                );
            }

            if (! $response->successful()) {
                Log::warning('Google Cloud Translation returned an error.', [
                    'target_locale' => $targetLocale,
                    'status' => $response->status(),
                ]);

                throw new BlogTranslationException(
                    'Google Cloud Translation rejected the request (HTTP '.$response->status().'). Check the API, model access, billing, and quota.',
                );
            }

            $translatedText = $response->json('data.translations.0.translatedText');

            if (! is_string($translatedText) || trim($translatedText) === '') {
                throw new BlogTranslationException(
                    'Google Cloud Translation returned an empty or invalid translation. No article versions were saved.',
                );
            }

            $translatedChunks[] = html_entity_decode($translatedText, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return implode('', $translatedChunks);
    }

    /**
     * @return array<int, string>
     */
    private function splitText(string $text): array
    {
        $chunks = [];

        while (mb_strlen($text) > self::MAX_CHARS_PER_REQUEST) {
            $window = mb_substr($text, 0, self::MAX_CHARS_PER_REQUEST);
            $splitAt = mb_strrpos($window, "\n");
            $includeSeparator = $splitAt !== false && $splitAt >= self::MAX_CHARS_PER_REQUEST / 2;

            if ($splitAt === false || $splitAt < self::MAX_CHARS_PER_REQUEST / 2) {
                $splitAt = mb_strrpos($window, ' ');
                $includeSeparator = $splitAt !== false && $splitAt >= self::MAX_CHARS_PER_REQUEST / 2;
            }

            if ($splitAt === false || $splitAt < self::MAX_CHARS_PER_REQUEST / 2) {
                $splitAt = self::MAX_CHARS_PER_REQUEST;
                $includeSeparator = false;
            }

            $chunkLength = $splitAt + (int) $includeSeparator;
            $chunks[] = mb_substr($text, 0, $chunkLength);
            $text = mb_substr($text, $chunkLength);
        }

        $chunks[] = $text;

        return $chunks;
    }
}
