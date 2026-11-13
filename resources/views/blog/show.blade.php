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
        'resources/css/templatemo-stand-blog.css',
        'resources/css/owl.css',
        'resources/css/animate.css',
        'resources/js/app.js',
        'resources/js/blog.js',
    ])
    <script type="application/ld+json">
        @json($structuredData)
    </script>
</head>
<body class="storefront-page blog-storefront-page">
    <x-storefront-header :show-logout="true" />
    <main class="blog-page blog-article-page">
        <div class="heading-page header-text">
            <section class="page-heading">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <nav class="blog-breadcrumb" aria-label="Breadcrumb">
                                <a href="{{ route('home') }}">{{ __('store.home') }}</a>
                                <span aria-hidden="true">/</span>
                                <a href="{{ route('blog.index') }}">{{ __('store.blog') }}</a>
                                <span aria-hidden="true">/</span>
                                <span aria-current="page">{{ $blogPost->title }}</span>
                            </nav>
                            <div class="text-content">
                                @if ($blogPost->categories->isNotEmpty())
                                    <h4>{{ $blogPost->categories->pluck('name')->join(', ') }}</h4>
                                @else
                                    <h4>{{ __('store.blog') }}</h4>
                                @endif
                                <h1>{{ $blogPost->title }}</h1>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        @include('blog._call-to-action', [
            'href' => route('blog.posts'),
            'label' => __('store.blog_view_all'),
        ])

        <section class="blog-posts grid-system">
            <div class="container">
                <div class="row">
                    <div class="col-12 col-lg-8">
                        <article class="blog-post blog-post-detail">
                            @if ($coverImageUrl)
                                <div class="blog-thumb">
                                    <img src="{{ $coverImageUrl }}" alt="{{ $blogPost->title }}" fetchpriority="high">
                                </div>
                            @endif
                            <div class="down-content">
                                <ul class="post-info">
                                    <li><a href="{{ route('blog.index') }}">Meoxstore</a></li>
                                    <li><time datetime="{{ $blogPost->published_at->toAtomString() }}">{{ __('store.blog_published_on', ['date' => $blogPost->published_at->format('M j, Y')]) }}</time></li>
                                </ul>
                                @if ($blogPost->excerpt)
                                    <p class="blog-article-excerpt">{{ $blogPost->excerpt }}</p>
                                @endif
                                <div class="blog-article-body">{!! $bodyHtml !!}</div>

                                @if ($blogPost->categories->isNotEmpty())
                                    <div class="post-options">
                                        <ul class="post-tags" aria-label="{{ __('store.blog_categories') }}">
                                            <li><i class="fa fa-tags" aria-hidden="true"></i></li>
                                            @foreach ($blogPost->categories as $category)
                                                <li>
                                                    <a href="{{ route('blog.categories.show', $category->slug) }}">{{ $category->name }}</a>@if (! $loop->last),@endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="main-button blog-return-link">
                                    <a href="{{ route('blog.posts') }}">{{ __('store.blog_back_to_list') }}</a>
                                </div>
                            </div>
                        </article>
                    </div>
                    <div class="col-12 col-lg-4">
                        @include('blog._sidebar')
                    </div>
                </div>
            </div>
        </section>
    </main>
    <footer>
        <div class="container">
            <p>Copyright © {{ date('Y') }} Meoxstore. All rights reserved.</p>
        </div>
    </footer>
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
</body>
</html>
