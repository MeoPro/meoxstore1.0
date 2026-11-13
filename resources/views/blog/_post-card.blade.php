<article class="blog-post">
    <div class="blog-thumb">
        @if ($blogPost->cover_image)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($blogPost->cover_image) }}" alt="" loading="lazy">
        @else
            <img src="{{ asset('images/blog-thumb-01.jpg') }}" alt="" loading="lazy">
        @endif
    </div>
    <div class="down-content">
        <span>{{ __('store.blog') }}</span>
        <a href="{{ route('blog.show', $blogPost->slug) }}"><h4>{{ $blogPost->title }}</h4></a>
        <ul class="post-info">
            <li><a href="{{ route('blog.index') }}">Meoxstore</a></li>
            <li><time datetime="{{ $blogPost->published_at->toAtomString() }}">{{ $blogPost->published_at->format('M j, Y') }}</time></li>
        </ul>
        <p>{{ $blogPost->excerpt }}</p>
    </div>
</article>
