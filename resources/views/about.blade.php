<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('store.about_title') }} | Meoxstore</title>
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
<body>
    <x-storefront-header />
    <div class="page-heading header-text">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h3>{{ __('store.about_title') }}</h3>
                    <span class="breadcrumb"><a href="{{ route('home') }}">{{ __('store.home') }}</a> &gt; {{ __('store.about_us') }}</span>
                </div>
            </div>
        </div>
    </div>
    <main class="section">
        <div class="container" style="max-width: 900px;">
            <div class="bg-white rounded-4 p-4 p-md-5 shadow-sm">
                <h1 class="h2 mb-4">{{ __('store.about_title') }}</h1>
                <p class="lead">{{ __('store.about_intro') }}</p>
                <p>{{ __('store.about_copy') }}</p>
                <a class="btn btn-primary rounded-pill px-4 mt-3" href="{{ route('games.index') }}">{{ __('store.browse_games') }}</a>
            </div>
        </div>
    </main>
    <footer><div class="container"><div class="col-lg-12"><p>Copyright © {{ date('Y') }} Meoxstore. All rights reserved.</p></div></div></footer>
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
</body>
</html>
