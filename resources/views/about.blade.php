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
<body class="storefront-page">
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
        <div class="container about-page-container">
            <div class="bg-white rounded-4 p-4 p-md-5 shadow-sm about-page-card">
                <h1 class="h2 mb-4">{{ __('store.about_title') }}</h1>
                <p class="lead">{{ __('store.about_intro') }}</p>
                <p>{{ __('store.about_copy') }}</p>
                <section class="about-journey" aria-labelledby="about-journey-title">
                    <h2 class="h3 mb-0" id="about-journey-title">{{ __('store.our_journey') }}</h2>
                    <ol class="about-journey-grid">
                        <li>
                            <article class="about-journey-card">
                                <h3 class="about-journey-year"><time datetime="2019">2019</time></h3>
                                <p>{{ __('store.our_journey_2019') }}</p>
                            </article>
                        </li>
                        <li>
                            <article class="about-journey-card">
                                <h3 class="about-journey-year"><time datetime="2021">2021</time></h3>
                                <p>{{ __('store.our_journey_2021') }}</p>
                            </article>
                        </li>
                        <li>
                            <article class="about-journey-card">
                                <h3 class="about-journey-year"><time datetime="2023">2023</time></h3>
                                <p>{{ __('store.our_journey_2023') }}</p>
                            </article>
                        </li>
                    </ol>
                </section>
                <a class="btn btn-primary rounded-pill px-4 mt-3" href="{{ route('games.index') }}">{{ __('store.browse_games') }}</a>
            </div>
        </div>
    </main>
    <x-storefront-footer />
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
</body>
</html>
