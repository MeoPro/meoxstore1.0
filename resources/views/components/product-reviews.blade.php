<section class="product-reviews" id="reviews" aria-labelledby="product-reviews-heading">
    <div class="section-heading">
        <h6>{{ __('store.customer_experience') }}</h6>
        <h2 id="product-reviews-heading">{{ __('store.product_reviews') }}</h2>
    </div>

    <div class="product-review-overview">
        <div class="product-review-average">
            <div class="product-review-average-rating">
                <span class="product-review-average-star" aria-hidden="true">★</span>
                <strong>{{ number_format($averageRating, 1) }}</strong>
            </div>
            <span class="product-review-average-count">{{ __('store.review_count', ['count' => number_format($reviewCount)]) }}</span>
        </div>

        <div class="product-review-distribution" role="img" aria-label="{{ __('store.rating_distribution') }}">
            @foreach ([5, 4, 3, 2, 1] as $rating)
                @php
                    $ratingCount = $reviewRatingCounts->get($rating, 0);
                    $ratingPercentage = $reviewCount > 0 ? ($ratingCount / $reviewCount) * 100 : 0;
                @endphp
                <div class="product-review-distribution-row" data-rating-row="{{ $rating }}" data-rating-count="{{ $ratingCount }}">
                    <span class="product-review-distribution-star" aria-hidden="true">★</span>
                    <span>{{ $rating }}</span>
                    <div class="product-review-distribution-bar" aria-hidden="true">
                        <span style="width: {{ number_format($ratingPercentage, 2, '.', '') }}%"></span>
                    </div>
                    <span class="product-review-distribution-count">{{ number_format($ratingCount) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success mt-3" role="status">{{ session('success') }}</div>
    @endif

    <div class="product-review-latest">
        <h3>{{ __('store.latest_reviews') }}</h3>
        @if ($product->reviews->isEmpty())
            <p class="product-review-empty">{{ __('store.no_reviews') }}</p>
        @else
            <div class="product-review-list" aria-label="{{ __('store.latest_reviews') }}">
                @foreach ($product->reviews as $review)
                    <article class="product-review-card">
                        <div class="product-review-card-header">
                            <strong class="product-review-card-name">{{ $review->user?->name ?? __('store.verified_buyer') }}</strong>
                            <time class="product-review-card-date" datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->format('d/m/Y') }}</time>
                        </div>
                        <div class="product-review-card-stars" aria-label="{{ __('store.stars', ['count' => $review->rating]) }}">
                            @for ($star = 1; $star <= 5; $star++)
                                <span class="{{ $star <= $review->rating ? '' : 'is-empty' }}">★</span>
                            @endfor
                        </div>
                        @if (filled($review->comment))
                            <p class="product-review-card-comment">{{ $review->comment }}</p>
                        @endif
                        @if ($review->admin_reply)
                            <div class="border-start border-3 ps-3 mt-3">
                                <strong>{{ __('store.store_reply') }}</strong>
                                <p class="text-secondary mt-1 mb-0">{{ $review->admin_reply }}</p>
                                @if ($review->admin_reply_image)
                                    <a class="d-inline-block mt-2" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($review->admin_reply_image) }}" target="_blank" rel="noopener noreferrer">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($review->admin_reply_image) }}" alt="{{ __('store.review_reply_image_alt') }}" style="max-height: 220px; max-width: 100%; object-fit: contain;">
                                    </a>
                                @endif
                            </div>
                        @elseif ($review->admin_reply_image)
                            <a class="d-inline-block mt-2" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($review->admin_reply_image) }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($review->admin_reply_image) }}" alt="{{ __('store.review_reply_image_alt') }}" style="max-height: 220px; max-width: 100%; object-fit: contain;">
                            </a>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    @if ($canReviewOrder)
        <form class="review-form" method="POST" action="{{ route('products.reviews.store', $product) }}">
            @csrf
            <h3 class="h5 mb-3">{{ __('store.write_review') }}</h3>
            <div class="mb-3">
                <fieldset class="border-0 m-0 p-0">
                    <legend class="form-label">{{ __('store.give_stars') }}</legend>
                    <div class="review-rating" role="radiogroup" aria-label="{{ __('store.give_stars') }}">
                        @foreach (range(5, 1) as $rating)
                            <input id="review-rating-{{ $rating }}" type="radio" name="rating" value="{{ $rating }}" aria-label="{{ __('store.stars', ['count' => $rating]) }}" required @checked(old('rating') == $rating)>
                            <label for="review-rating-{{ $rating }}" title="{{ __('store.stars', ['count' => $rating]) }}" aria-hidden="true">★</label>
                        @endforeach
                    </div>
                    @error('rating')<div class="text-danger small">{{ $message }}</div>@enderror
                </fieldset>
            </div>
            <div class="mb-3">
                <label class="form-label" for="review-comment">{{ __('store.review') }}</label>
                <textarea class="form-control @error('comment') is-invalid @enderror" id="review-comment" name="comment" rows="4" maxlength="2000">{{ old('comment') }}</textarea>
                @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit">{{ __('store.send_review') }}</button>
        </form>
    @elseif (auth()->check() && ! $hasReviewed)
        <p class="text-secondary mt-4">{{ __('store.review_after_completion') }}</p>
    @elseif (auth()->guest())
        <p class="text-secondary mt-4">{{ __('store.guest_review_tracking') }} <a href="{{ route('orders.lookup') }}">{{ __('store.track_order') }}</a>.</p>
    @endif
</section>
