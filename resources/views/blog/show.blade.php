<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $metaTitle }} | Meoxstore</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $metaTitle }} | Meoxstore">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="article:published_time" content="{{ $blogPost->published_at->toAtomString() }}">
    @if ($coverImageUrl)
        <meta property="og:image" content="{{ $coverImageUrl }}">
    @endif
    <meta name="twitter:card" content="{{ $coverImageUrl ? 'summary_large_image' : 'summary' }}">
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    @vite([
        'resources/css/app.css',
        'resources/css/fontawesome.css',
        'resources/css/templatemo-lugx-gaming.css',
        'resources/css/owl.css',
        'resources/css/animate.css',
        'resources/js/app.js',
    ])
    <script type="application/ld+json">
        @json($structuredData)
    </script>
</head>
<body class="storefront-page blog-storefront-page">
    <x-storefront-header />
    <main class="blog-page blog-article-page">
        <div class="container">
            <nav class="blog-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">{{ __('store.home') }}</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('blog.index') }}">{{ __('store.blog') }}</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page">{{ $blogPost->title }}</span>
            </nav>

            <div class="row g-4">
                    <div class="col-12 col-lg-8">
                        <article class="blog-article">
                            <header class="blog-article-heading">
                                <span class="blog-eyebrow">{{ __('store.blog') }}</span>
                                <h1>{{ $blogPost->title }}</h1>
                                @if ($blogPost->categories->isNotEmpty())
                                    <nav class="blog-article-categories" aria-label="{{ __('store.blog_categories') }}">
                                        @foreach ($blogPost->categories as $category)
                                            <a href="{{ route('blog.categories.show', $category->slug) }}">{{ $category->name }}</a>
                                        @endforeach
                                    </nav>
                                @endif
                                <p class="blog-article-excerpt">{{ $blogPost->excerpt }}</p>
                                <time class="blog-card-date" datetime="{{ $blogPost->published_at->toAtomString() }}">
                                    {{ __('store.blog_published_on', ['date' => $blogPost->published_at->format('M j, Y')]) }}
                                </time>
                            </header>

                            @if ($coverImageUrl)
                                <img class="blog-article-cover" src="{{ $coverImageUrl }}" alt="{{ $blogPost->title }}" fetchpriority="high">
                            @endif

                            <div class="blog-article-body">{!! $bodyHtml !!}</div>

                            <a class="blog-back-link" href="{{ route('blog.index') }}">← {{ __('store.blog_back_to_list') }}</a>
                        </article>
            </div>
                    <div class="col-12 col-lg-4">
                        @include('blog._sidebar')
                    </div>
                </div>
        </div>
    </main>
    <footer><div class="container"><div class="col-lg-12"><p>Copyright © {{ date('Y') }} Meoxstore. All rights reserved.</p></div></div></footer>
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
</body>
</html>
