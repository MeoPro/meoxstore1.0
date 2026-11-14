<?php

namespace App\Http\Controllers;

use App\Exceptions\GoogleBusinessProfileException;
use App\Models\GoogleBusinessProfileConnection;
use App\Models\ProductReview;
use App\Services\GoogleBusinessProfileService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StoreReviewController extends Controller
{
    public function index(Request $request, GoogleBusinessProfileService $googleBusinessProfileService): View
    {
        $pageToken = $request->query('google_page_token');
        abort_unless(
            $pageToken === null || (is_string($pageToken) && strlen($pageToken) <= 4096),
            400,
        );

        $reviewStatistics = $this->visibleCompletedPurchaseReviews()
            ->toBase()
            ->selectRaw('COUNT(*) as review_count, AVG(rating) as average_rating')
            ->first();

        $reviews = $this->visibleCompletedPurchaseReviews()
            ->with([
                'product:id,name,status',
                'user:id,name',
            ])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $googleReviews = null;
        $googleReviewsUnavailable = false;
        $googleBusinessProfileConnection = GoogleBusinessProfileConnection::query()->find(1);

        if ($googleBusinessProfileConnection?->location_name !== null) {
            try {
                $googleReviews = $googleBusinessProfileService->reviews(
                    $googleBusinessProfileConnection,
                    $pageToken,
                );
            } catch (ConnectionException|RequestException|GuzzleException|GoogleBusinessProfileException $exception) {
                Log::warning('Google Business Profile reviews could not be loaded.', [
                    'location_name' => $googleBusinessProfileConnection->location_name,
                    'exception' => $exception::class,
                    'status' => $exception instanceof RequestException ? $exception->response->status() : null,
                ]);
                $googleReviewsUnavailable = true;
            }
        }

        return view('reviews.index', [
            'averageRating' => (float) ($reviewStatistics->average_rating ?? 0),
            'reviewCount' => (int) $reviewStatistics->review_count,
            'reviews' => $reviews,
            'googleReviews' => $googleReviews,
            'googleReviewsUnavailable' => $googleReviewsUnavailable,
        ]);
    }

    /**
     * @return Builder<ProductReview>
     */
    private function visibleCompletedPurchaseReviews(): Builder
    {
        return ProductReview::query()
            ->where('is_hidden', false)
            ->whereHas('order', fn (Builder $query) => $query
                ->where('status', 'completed')
                ->whereNotNull('customer_completed_at'));
    }
}
