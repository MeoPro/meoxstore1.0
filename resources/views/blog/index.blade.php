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

            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    @forelse ($blogCategories as $blogCategory)
                        <section class="blog-category-section" aria-labelledby="blog-category-{{ $blogCategory->id }}">
                            <header class="blog-category-section-heading">
                                <div>
                                    <span class="blog-eyebrow">{{ __('store.blog_category_label') }}</span>
                                    <h2 id="blog-category-{{ $blogCategory->id }}">{{ $blogCategory->name }}</h2>
                                </div>
                                <a href="{{ route('blog.categories.show', $blogCategory->slug) }}">
                                    {{ __('store.blog_view_all') }} <span aria-hidden="true">→</span>
                                </a>
                            </header>
                            <div class="row g-4">
                                @foreach ($blogCategory->blogPosts as $blogPost)
                                    <div class="col-12 col-md-6">
                                        @include('blog._post-card', ['blogPost' => $blogPost])
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @empty
                        @if ($uncategorizedPosts->isEmpty())
                            <div class="blog-empty">{{ __('store.blog_empty') }}</div>
                        @endif
                    @endforelse

                    @if ($uncategorizedPosts->isNotEmpty())
                        <section class="blog-category-section" aria-labelledby="blog-uncategorized-heading">
                            <header class="blog-category-section-heading">
                                <div>
                                    <span class="blog-eyebrow">{{ __('store.blog_category_label') }}</span>
                                    <h2 id="blog-uncategorized-heading">{{ __('store.blog_latest') }}</h2>
                                </div>
                            </header>
                            <div class="row g-4">
                                @foreach ($uncategorizedPosts as $blogPost)
                                    <div class="col-12 col-md-6">
                                        @include('blog._post-card', ['blogPost' => $blogPost])
                                    </div>
                                @endforeach
                            </div>
                            @if ($uncategorizedPosts->hasPages())
                                <div class="blog-pagination">{{ $uncategorizedPosts->links('pagination::bootstrap-5') }}</div>
                            @endif
                        </section>
                    @endif
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
