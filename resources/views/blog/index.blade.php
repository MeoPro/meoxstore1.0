<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('store.blog_title') }} | Meoxstore</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ route('blog.index') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ __('store.blog_title') }} | Meoxstore">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ route('blog.index') }}">
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
    <main class="blog-page">
        <div class="container">
            <nav class="blog-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">{{ __('store.home') }}</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page">{{ __('store.blog') }}</span>
            </nav>

            <header class="blog-heading">
                <span class="blog-eyebrow">{{ __('store.blog') }}</span>
                <h1>{{ __('store.blog_title') }}</h1>
                <p>{{ __('store.blog_intro') }}</p>
            </header>

            @if ($blogPosts->isNotEmpty())
                <div class="row g-4">
                    @foreach ($blogPosts as $blogPost)
                        <div class="col-12 col-md-6 col-lg-4">
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
                        </div>
                    @endforeach
                </div>
                @if ($blogPosts->hasPages())
                    <div class="blog-pagination">{{ $blogPosts->links('pagination::bootstrap-5') }}</div>
                @endif
            @else
                <div class="blog-empty">{{ __('store.blog_empty') }}</div>
            @endif
        </div>
    </main>
    <footer><div class="container"><div class="col-lg-12"><p>Copyright © {{ date('Y') }} Meoxstore. All rights reserved.</p></div></div></footer>
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
</body>
</html>
