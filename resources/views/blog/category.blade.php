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
        'resources/css/templatemo-stand-blog.css',
        'resources/css/owl.css',
        'resources/css/animate.css',
        'resources/js/app.js',
        'resources/js/blog.js',
    ])
</head>
<body class="storefront-page blog-storefront-page">
    <x-storefront-header :show-logout="true" />
    <main class="blog-page">
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
                                <span aria-current="page">{{ $blogCategory->name }}</span>
                            </nav>
                            <div class="text-content">
                                <h4>{{ __('store.blog_category_label') }}</h4>
                                <h1>{{ $blogCategory->name }}</h1>
                                @if ($blogCategory->description)
                                    <p>{{ $blogCategory->description }}</p>
                                @endif
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
                            <div class="blog-empty" role="status">{{ __('store.blog_empty') }}</div>
                        @endif
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
