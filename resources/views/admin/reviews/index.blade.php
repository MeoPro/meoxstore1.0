@extends('admin.layout')

@section('title', 'Reviews')

@section('content')
    <div class="page-heading"><div><h1>Buyer Reviews</h1></div></div>
    <div class="d-grid gap-3">
        @forelse ($reviews as $review)
            <article class="panel p-3 p-lg-4">
                <div class="d-flex flex-wrap justify-content-between gap-3">
                    <div>
                        <h2 class="h5 mb-1">{{ $review->product->name }}</h2>
                        <p class="text-muted mb-1">{{ $review->user?->name ?? __('store.verified_buyer') }} · {{ $review->user?->email }}</p>
                        <p class="text-muted small mb-2">Order {{ $review->order->public_number }}</p>
                        <div class="text-warning" aria-label="{{ __('store.rating_out_of_five', ['rating' => $review->rating]) }}">
                            @for ($star = 1; $star <= 5; $star++)
                                <span class="{{ $star <= $review->rating ? '' : 'text-muted' }}">★</span>
                            @endfor
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge {{ $review->is_hidden ? 'text-bg-secondary' : 'text-bg-success' }}">
                            {{ $review->is_hidden ? 'Hidden' : 'Visible' }}
                        </span>
                        <div class="small text-muted mt-2">{{ $review->created_at->format('M j, Y H:i') }}</div>
                    </div>
                </div>
                <p class="border-start border-3 ps-3 my-3">{{ $review->comment ?: '—' }}</p>
                <form class="d-grid gap-2" method="POST" action="{{ route('admin.reviews.reply', $review) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <label class="form-label mb-0" for="reply-{{ $review->id }}">Admin reply</label>
                    <textarea class="form-control" id="reply-{{ $review->id }}" name="admin_reply" rows="3" maxlength="2000">{{ old('admin_reply', $review->admin_reply) }}</textarea>
                    @if ($review->admin_reply_image)
                        <div class="d-flex align-items-start flex-wrap gap-2">
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($review->admin_reply_image) }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($review->admin_reply_image) }}" alt="{{ __('store.review_reply_image_alt') }}" style="height: 88px; object-fit: cover; width: 88px">
                            </a>
                            <label class="form-check-label small">
                                <input class="form-check-input" type="checkbox" name="remove_admin_reply_image" value="1">
                                Remove current image
                            </label>
                        </div>
                    @endif
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <input class="form-control form-control-sm" type="file" name="admin_reply_image" accept="image/jpeg,image/png,image/webp" aria-label="Upload an image with the reply">
                        <button class="btn btn-sm btn-accent" type="submit">Save reply</button>
                    </div>
                </form>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <form method="POST" action="{{ route('admin.reviews.visibility', $review) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="is_hidden" value="{{ $review->is_hidden ? '0' : '1' }}">
                        <button class="btn btn-sm btn-outline-secondary" type="submit">{{ $review->is_hidden ? 'Show review' : 'Hide review' }}</button>
                    </form>
                    <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Delete this review permanently?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete review</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="panel p-4 text-center text-muted">No buyer reviews yet.</div>
        @endforelse
    </div>
    @if ($reviews->hasPages())
        <div class="mt-3">{{ $reviews->links('pagination::bootstrap-5') }}</div>
    @endif
@endsection
