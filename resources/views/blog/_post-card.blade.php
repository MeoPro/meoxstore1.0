<article class="blog-card">
    @if ($blogPost->cover_image)
        <a class="blog-card-cover" href="{{ route('blog.show', $blogPost->slug) }}" tabindex="-1" aria-hidden="true">
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($blogPost->cover_image) }}" alt="" loading="lazy">
        </a>
    @else
        <a class="blog-card-cover blog-card-cover-placeholder" href="{{ route('blog.show', $blogPost->slug) }}" tabindex="-1" aria-hidden="true">
            <span>Meoxstore</span>
        </a>
    @endif
    <div class="blog-card-content">
        <time class="blog-card-date" datetime="{{ $blogPost->published_at->toAtomString() }}">
            {{ __('store.blog_published_on', ['date' => $blogPost->published_at->format('M j, Y')]) }}
        </time>
        <h2><a href="{{ route('blog.show', $blogPost->slug) }}">{{ $blogPost->title }}</a></h2>
        <p>{{ $blogPost->excerpt }}</p>
        <a class="blog-read-more" href="{{ route('blog.show', $blogPost->slug) }}">
            {{ __('store.blog_read_more') }} <span aria-hidden="true">→</span>
        </a>
    </div>
</article>
