<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $blogCategory->name }} | {{ __('store.blog_title') }} | Meoxstore</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $blogCategory->name }} | {{ __('store.blog_title') }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
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
                <a href="{{ route('blog.index') }}">{{ __('store.blog') }}</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page">{{ $blogCategory->name }}</span>
            </nav>

            <header class="blog-heading blog-category-heading">
                <span class="blog-eyebrow">{{ __('store.blog') }}</span>
                <h1>{{ $blogCategory->name }}</h1>
                @if ($blogCategory->description)
                    <p>{{ $blogCategory->description }}</p>
                @endif
            </header>

            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    @if ($blogPosts->isNotEmpty())
                        <div class="row g-4">
                            @foreach ($blogPosts as $blogPost)
                                <div class="col-12 col-md-6">
                                    @include('blog._post-card', ['blogPost' => $blogPost])
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
