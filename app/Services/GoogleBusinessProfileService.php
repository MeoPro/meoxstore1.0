<?php

namespace App\Services;

use App\Exceptions\GoogleBusinessProfileException;
use App\Models\GoogleBusinessProfileConnection;
use Google\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GoogleBusinessProfileService
{
    private const BUSINESS_MANAGE_SCOPE = 'https://www.googleapis.com/auth/business.manage';

    private const ACCOUNT_MANAGEMENT_API = 'https://mybusinessaccountmanagement.googleapis.com/v1';

    private const BUSINESS_INFORMATION_API = 'https://mybusinessbusinessinformation.googleapis.com/v1';

    private const REVIEWS_API = 'https://mybusiness.googleapis.com/v4';

    public function __construct(private Client $client) {}

    public function isConfigured(): bool
    {
        return filled(config('services.google_business_profile.client_id'))
            && filled(config('services.google_business_profile.client_secret'))
            && filled(config('services.google_business_profile.redirect_uri'));
    }

    public function authorizationUrl(string $state): string
    {
        $client = $this->configuredClient();
        $client->setScopes([self::BUSINESS_MANAGE_SCOPE]);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setIncludeGrantedScopes(true);
        $client->setState($state);

        return $client->createAuthUrl();
    }

    /**
     * @return array<string, mixed>
     */
    public function exchangeAuthorizationCode(string $code): array
    {
        $tokens = $this->configuredClient()->fetchAccessTokenWithAuthCode($code);

        if (isset($tokens['error']) || ! is_string($tokens['access_token'] ?? null)) {
            throw new GoogleBusinessProfileException('Google OAuth did not return an access token.');
        }

        return $tokens;
    }

    /**
     * @return list<array{resource_name: string, title: string}>
     */
    public function availableLocations(GoogleBusinessProfileConnection $connection): array
    {
        $accessToken = $this->accessToken($connection);
        $accounts = $this->getAllPages(
            self::ACCOUNT_MANAGEMENT_API.'/accounts',
            'accounts',
            $accessToken,
            ['pageSize' => 20],
        );
        $locations = [];

        foreach ($accounts as $account) {
            $accountName = $account['name'] ?? null;

            if (! is_string($accountName) || ! preg_match('/\Aaccounts\/[A-Za-z0-9_-]+\z/', $accountName)) {
                throw new GoogleBusinessProfileException('Google returned an invalid Business Profile account.');
            }

            $accountLocations = $this->getAllPages(
                self::BUSINESS_INFORMATION_API.'/'.$accountName.'/locations',
                'locations',
                $accessToken,
                ['pageSize' => 100, 'readMask' => 'name,title'],
            );

            foreach ($accountLocations as $location) {
                $locationName = $location['name'] ?? null;
                $title = $location['title'] ?? null;

                if (! is_string($locationName) || ! preg_match('/\Alocations\/[A-Za-z0-9_-]+\z/', $locationName)
                    || ! is_string($title) || trim($title) === '') {
                    throw new GoogleBusinessProfileException('Google returned an invalid Business Profile location.');
                }

                $locations[] = [
                    'resource_name' => $accountName.'/'.$locationName,
                    'title' => $title,
                ];
            }
        }

        usort($locations, fn (array $first, array $second): int => strcasecmp($first['title'], $second['title']));

        return $locations;
    }

    /**
     * @return array{
     *     reviews: list<array{reviewer: string, rating: int, comment: ?string, created_at: ?string, reply: ?string}>,
     *     average_rating: float,
     *     total_review_count: int,
     *     next_page_token: ?string
     * }
     */
    public function reviews(
        GoogleBusinessProfileConnection $connection,
        ?string $pageToken = null,
    ): array {
        $locationName = $connection->location_name;

        if (! is_string($locationName) || ! preg_match('/\Aaccounts\/[A-Za-z0-9_-]+\/locations\/[A-Za-z0-9_-]+\z/', $locationName)) {
            throw new GoogleBusinessProfileException('A valid Google Business Profile location has not been selected.');
        }

        $cacheKey = 'google-business-profile.reviews.'
            .hash('sha256', $locationName).'.'
            .hash('sha256', $pageToken ?? '');

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($connection, $locationName, $pageToken): array {
            $query = [
                'pageSize' => 50,
                'orderBy' => 'updateTime desc',
            ];
            if ($pageToken !== null && $pageToken !== '') {
                $query['pageToken'] = $pageToken;
            }

            $payload = Http::withToken($this->accessToken($connection))
                ->connectTimeout(3)
                ->timeout(10)
                ->get(self::REVIEWS_API.'/'.$locationName.'/reviews', $query)
                ->throw()
                ->json();

            if (! is_array($payload)) {
                throw new GoogleBusinessProfileException('Google returned an invalid reviews response.');
            }

            $reviews = $payload['reviews'] ?? [];
            $averageRating = $payload['averageRating'] ?? 0;
            $totalReviewCount = $payload['totalReviewCount'] ?? 0;
            $nextPageToken = $payload['nextPageToken'] ?? null;

            if (! is_array($reviews) || ! is_numeric($averageRating) || (float) $averageRating < 0
                || (float) $averageRating > 5 || ! is_numeric($totalReviewCount) || (int) $totalReviewCount < 0
                || ($nextPageToken !== null && ! is_string($nextPageToken))) {
                throw new GoogleBusinessProfileException('Google returned incomplete reviews data.');
            }

            $normalizedReviews = [];
            foreach ($reviews as $review) {
                if (! is_array($review)) {
                    throw new GoogleBusinessProfileException('Google returned an invalid review.');
                }

                $normalizedReviews[] = $this->normalizeReview($review);
            }

            return [
                'reviews' => $normalizedReviews,
                'average_rating' => (float) $averageRating,
                'total_review_count' => (int) $totalReviewCount,
                'next_page_token' => $nextPageToken !== '' ? $nextPageToken : null,
            ];
        });
    }

    private function accessToken(GoogleBusinessProfileConnection $connection): string
    {
        $cacheKey = 'google-business-profile.access-token.'
            .$connection->id.'.'.hash('sha256', $connection->refresh_token);

        return Cache::remember($cacheKey, now()->addMinutes(55), function () use ($connection): string {
            $tokens = $this->configuredClient()->fetchAccessTokenWithRefreshToken($connection->refresh_token);

            if (isset($tokens['error']) || ! is_string($tokens['access_token'] ?? null)) {
                throw new GoogleBusinessProfileException('Google OAuth could not refresh the Business Profile access token.');
            }

            return $tokens['access_token'];
        });
    }

    private function configuredClient(): Client
    {
        $clientId = config('services.google_business_profile.client_id');
        $clientSecret = config('services.google_business_profile.client_secret');
        $redirectUri = config('services.google_business_profile.redirect_uri');

        if (! is_string($clientId) || $clientId === '' || ! is_string($clientSecret) || $clientSecret === ''
            || ! is_string($redirectUri) || $redirectUri === '') {
            throw new GoogleBusinessProfileException('Google Business Profile OAuth credentials are not configured.');
        }

        $this->client->setClientId($clientId);
        $this->client->setClientSecret($clientSecret);
        $this->client->setRedirectUri($redirectUri);

        return $this->client;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return list<array<string, mixed>>
     */
    private function getAllPages(string $url, string $itemsKey, string $accessToken, array $query): array
    {
        $items = [];
        $pageToken = null;
        $seenPageTokens = [];

        do {
            if ($pageToken !== null) {
                $query['pageToken'] = $pageToken;
            }

            $payload = Http::withToken($accessToken)
                ->connectTimeout(3)
                ->timeout(10)
                ->get($url, $query)
                ->throw()
                ->json();

            if (! is_array($payload)) {
                throw new GoogleBusinessProfileException('Google returned an invalid Business Profile list response.');
            }

            $pageItems = $payload[$itemsKey] ?? [];
            if (! is_array($pageItems)) {
                throw new GoogleBusinessProfileException('Google returned invalid Business Profile list data.');
            }

            foreach ($pageItems as $item) {
                if (! is_array($item)) {
                    throw new GoogleBusinessProfileException('Google returned an invalid Business Profile resource.');
                }

                $items[] = $item;
            }

            $pageToken = $payload['nextPageToken'] ?? null;
            if ($pageToken !== null && (! is_string($pageToken) || $pageToken === '' || isset($seenPageTokens[$pageToken]))) {
                throw new GoogleBusinessProfileException('Google returned an invalid Business Profile page token.');
            }
            if (is_string($pageToken)) {
                $seenPageTokens[$pageToken] = true;
            }
        } while ($pageToken !== null);

        return $items;
    }

    /**
     * @param  array<string, mixed>  $review
     * @return array{reviewer: string, rating: int, comment: ?string, created_at: ?string, reply: ?string}
     */
    private function normalizeReview(array $review): array
    {
        $rating = match ($review['starRating'] ?? null) {
            'ONE' => 1,
            'TWO' => 2,
            'THREE' => 3,
            'FOUR' => 4,
            'FIVE' => 5,
            default => null,
        };
        $reviewer = data_get($review, 'reviewer.displayName');
        $comment = $review['comment'] ?? null;
        $createdAt = $review['createTime'] ?? null;
        $reply = data_get($review, 'reviewReply.comment');

        if (! is_int($rating) || ($reviewer !== null && ! is_string($reviewer))
            || ($comment !== null && ! is_string($comment))
            || ($createdAt !== null && ! is_string($createdAt))
            || ($reply !== null && ! is_string($reply))) {
            throw new GoogleBusinessProfileException('Google returned an invalid review.');
        }

        return [
            'reviewer' => is_string($reviewer) && trim($reviewer) !== '' ? $reviewer : __('store.google_reviewer'),
            'rating' => $rating,
            'comment' => $comment,
            'created_at' => $createdAt,
            'reply' => $reply,
        ];
    }
}
