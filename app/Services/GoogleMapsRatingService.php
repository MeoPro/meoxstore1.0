<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsRatingService
{
    /**
     * @return array{rating: float, user_rating_count: int}|null
     */
    public function fetch(): ?array
    {
        $apiKey = config('services.google_maps.places_api_key');
        $placeId = config('services.google_maps.place_id');

        if (! is_string($apiKey) || $apiKey === '' || ! is_string($placeId) || $placeId === '') {
            return null;
        }

        try {
            $response = Http::connectTimeout(2)
                ->timeout(3)
                ->withHeaders([
                    'X-Goog-Api-Key' => $apiKey,
                    'X-Goog-FieldMask' => 'rating,userRatingCount',
                ])
                ->get('https://places.googleapis.com/v1/places/'.rawurlencode($placeId));
        } catch (ConnectionException $exception) {
            Log::warning('Google Maps rating request could not connect.', [
                'place_id' => $placeId,
                'exception' => $exception::class,
            ]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('Google Maps rating request returned an unsuccessful response.', [
                'place_id' => $placeId,
                'status' => $response->status(),
            ]);

            return null;
        }

        $rating = $response->json('rating');
        $userRatingCount = $response->json('userRatingCount');

        if (! is_numeric($rating) || ! is_int($userRatingCount)
            || (float) $rating < 0 || (float) $rating > 5 || $userRatingCount < 0) {
            Log::warning('Google Maps rating response did not contain valid rating data.', [
                'place_id' => $placeId,
            ]);

            return null;
        }

        return [
            'rating' => (float) $rating,
            'user_rating_count' => $userRatingCount,
        ];
    }
}
