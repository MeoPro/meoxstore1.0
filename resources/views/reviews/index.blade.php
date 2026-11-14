<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('store.store_reviews_title') }} | Meoxstore</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    @vite([
        'resources/css/app.css',
        'resources/css/fontawesome.css',
        'resources/css/templatemo-lugx-gaming.css',
        'resources/css/owl.css',
        'resources/css/animate.css',
        'resources/js/app.js',
    ])
</head>
<body class="storefront-page">
    <x-storefront-header />
    <div class="page-heading header-text">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h3>{{ __('store.store_reviews_title') }}</h3>
                    <span class="breadcrumb">
                        <a href="{{ route('home') }}">{{ __('store.home') }}</a>
                        &gt; {{ __('store.store_reviews_title') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    <main class="section">
        <div class="container store-reviews-page">
            <section class="store-reviews-summary" aria-label="{{ __('store.store_reviews_title') }}">
                <p>{{ __('store.store_reviews_intro') }}</p>
                @if ($reviewCount > 0)
                    <div class="store-reviews-score">
                        <span class="store-reviews-score-star" aria-hidden="true">★</span>
                        <strong>{{ number_format($averageRating, 1) }}</strong>
                        <span class="store-reviews-score-out-of">/ 5</span>
                        <span class="store-reviews-score-count">{{ __('store.review_count', ['count' => number_format($reviewCount)]) }}</span>
                    </div>
                @endif
            </section>

            @if ($googleReviewsUnavailable)
                <p class="store-reviews-empty" role="alert">{{ __('store.google_reviews_unavailable') }}</p>
            @elseif ($googleReviews !== null)
                <section class="store-google-reviews" aria-labelledby="google-reviews-heading">
                    <div class="store-reviews-summary">
                        <h2 id="google-reviews-heading">{{ __('store.google_reviews_heading') }}</h2>
                        <p>{{ __('store.google_reviews_intro') }}</p>
                        @if ($googleReviews['total_review_count'] > 0)
                            <div class="store-reviews-score">
                                <span class="store-reviews-score-star" aria-hidden="true">★</span>
                                <strong>{{ number_format($googleReviews['average_rating'], 1) }}</strong>
                                <span class="store-reviews-score-out-of">/ 5</span>
                                <span class="store-reviews-score-count">
                                    {{ __('store.review_count', ['count' => number_format($googleReviews['total_review_count'])]) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    @if (count($googleReviews['reviews']) === 0)
                        <p class="store-reviews-empty">{{ __('store.google_reviews_empty') }}</p>
                    @else
                        <div class="store-review-list">
                            @foreach ($googleReviews['reviews'] as $googleReview)
                                <article class="store-review-card">
                                    <div class="store-review-card-header">
                                        <div>
                                            <h3 class="store-review-product">{{ $googleReview['reviewer'] }}</h3>
                                            <span class="store-review-buyer">{{ __('store.google_review_source') }}</span>
                                        </div>
                                        @if ($googleReview['created_at'] !== null)
                                            <time class="store-review-date" datetime="{{ $googleReview['created_at'] }}">
                                                {{ \Illuminate\Support\Carbon::parse($googleReview['created_at'])->format('d/m/Y') }}
                                            </time>
                                        @endif
                                    </div>
                                    <div class="store-review-stars" role="img" aria-label="{{ __('store.stars', ['count' => $googleReview['rating']]) }}">
                                        @for ($star = 1; $star <= 5; $star++)
                                            <span @class(['is-empty' => $star > $googleReview['rating']])>★</span>
                                        @endfor
                                    </div>
                                    @if (filled($googleReview['comment']))
                                        <p class="store-review-comment">{{ $googleReview['comment'] }}</p>
                                    @endif
                                    @if (filled($googleReview['reply']))
                                        <div class="store-review-reply">
                                            <strong>{{ __('store.store_reply') }}</strong>
                                            <p>{{ $googleReview['reply'] }}</p>
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                        @if ($googleReviews['next_page_token'] !== null)
                            <div class="store-reviews-pagination">
                                <a
                                    class="btn btn-primary"
                                    href="{{ route('reviews.index', ['google_page_token' => $googleReviews['next_page_token']]) }}"
                                    rel="next">
                                    {{ __('store.google_reviews_next_page') }}
                                </a>
                            </div>
                        @endif
                    @endif
                </section>
            @endif

            @if ($reviews->isEmpty())
                <p class="store-reviews-empty" role="status">{{ __('store.no_store_reviews') }}</p>
            @else
                <section class="store-review-list" aria-label="{{ __('store.latest_reviews') }}">
                    @foreach ($reviews as $review)
                        <article class="store-review-card">
                            <div class="store-review-card-header">
                                <div>
                                    <h2 class="store-review-product">
                                        @if ($review->product->status === 'active')
                                            <a href="{{ route('product-details.show', $review->product) }}">{{ $review->product->name }}</a>
                                        @else
                                            {{ $review->product->name }}
                                        @endif
                                    </h2>
                                    <span class="store-review-buyer">{{ $review->user?->name ?? __('store.verified_buyer') }}</span>
                                </div>
                                <time class="store-review-date" datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->format('d/m/Y') }}</time>
                            </div>
                            <div class="store-review-stars" role="img" aria-label="{{ __('store.stars', ['count' => $review->rating]) }}">
                                @for ($star = 1; $star <= 5; $star++)
                                    <span @class(['is-empty' => $star > $review->rating])>★</span>
                                @endfor
                            </div>
                            @if (filled($review->comment))
                                <p class="store-review-comment">{{ $review->comment }}</p>
                            @endif
                            @if ($review->admin_reply)
                                <div class="store-review-reply">
                                    <strong>{{ __('store.store_reply') }}</strong>
                                    <p>{{ $review->admin_reply }}</p>
                                    @if ($review->admin_reply_image)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($review->admin_reply_image) }}" target="_blank" rel="noopener noreferrer">
                                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($review->admin_reply_image) }}" alt="{{ __('store.review_reply_image_alt') }}">
                                        </a>
                                    @endif
                                </div>
                            @elseif ($review->admin_reply_image)
                                <a class="store-review-reply-image" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($review->admin_reply_image) }}" target="_blank" rel="noopener noreferrer">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($review->admin_reply_image) }}" alt="{{ __('store.review_reply_image_alt') }}">
                                </a>
                            @endif
                        </article>
                    @endforeach
                </section>
                <div class="store-reviews-pagination">
                    {{ $reviews->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </main>
    <x-storefront-footer />
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
</body>
</html>
