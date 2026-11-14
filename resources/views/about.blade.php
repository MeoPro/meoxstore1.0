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
                <p class="about-repeat-purchases">
                    <span aria-hidden="true"><i class="fa fa-users"></i></span>
                    {{ __('store.about_repeat_purchases') }}
                </p>
                <section class="about-journey" aria-labelledby="about-journey-title">
                    <h2 class="h3 mb-0" id="about-journey-title">{{ __('store.our_journey') }}</h2>
                    <ol class="about-journey-grid">
                        <li>
                            <article class="about-journey-card">
                                <div class="about-journey-heading">
                                    <img class="about-game-logo" src="{{ asset('images/riseofkingdoms.webp') }}" alt="">
                                    <h3 class="about-journey-year"><time datetime="2019">2019</time></h3>
                                </div>
                                <p>{{ __('store.our_journey_2019') }}</p>
                            </article>
                        </li>
                        <li>
                            <article class="about-journey-card">
                                <div class="about-journey-heading">
                                    <img class="about-game-logo" src="{{ asset('images/growtopia.png') }}" alt="">
                                    <h3 class="about-journey-year"><time datetime="2021">2021</time></h3>
                                </div>
                                <p>{{ __('store.our_journey_2021') }}</p>
                            </article>
                        </li>
                        <li>
                            <article class="about-journey-card">
                                <div class="about-journey-heading">
                                    <img class="about-game-logo" src="{{ asset('images/roblox.png') }}" alt="">
                                    <h3 class="about-journey-year"><time datetime="2023">2023</time></h3>
                                </div>
                                <p>{{ __('store.our_journey_2023') }}</p>
                            </article>
                        </li>
                    </ol>
                </section>
                <section class="about-testimonials" aria-labelledby="about-testimonials-title">
                    <h2 class="h3 mb-2" id="about-testimonials-title">{{ __('store.about_testimonials_title') }}</h2>
                    <p class="about-testimonials-intro">{{ __('store.about_testimonials_intro') }}</p>
                    <div class="about-testimonials-scroller" role="region" aria-label="{{ __('store.about_testimonials_title') }}" tabindex="0">
                        <article class="about-testimonial-card">
                            <img class="about-testimonial-mark about-testimonial-mark--telegram" src="{{ asset('images/Telegram_2019_Logo.svg.webp') }}" alt="" aria-hidden="true">
                            <h3>{{ __('store.about_telegram_title') }}</h3>
                            <p>{{ __('store.about_telegram_copy') }}</p>
                            <a href="{{ config('store.about_testimonials.telegram_url') }}" target="_blank" rel="noopener noreferrer">
                                {{ __('store.about_open_channel') }}
                            </a>
                        </article>
                        <article class="about-testimonial-card">
                            <img class="about-testimonial-mark about-testimonial-mark--whatsapp" src="{{ asset('images/wa.png') }}" alt="" aria-hidden="true">
                            <h3>{{ __('store.about_whatsapp_title') }}</h3>
                            <p>{{ __('store.about_whatsapp_copy') }}</p>
                            <a href="{{ config('store.about_testimonials.whatsapp_url') }}" target="_blank" rel="noopener noreferrer">
                                {{ __('store.about_open_channel') }}
                            </a>
                        </article>
                        <article class="about-testimonial-card">
                            <span class="about-testimonial-mark about-testimonial-mark--maps" aria-hidden="true">G</span>
                            <h3>{{ __('store.about_google_reviews_title') }}</h3>
                            <p>{{ __('store.about_google_reviews_copy') }}</p>
                            <a href="{{ config('services.google_maps.profile_url') }}" target="_blank" rel="noopener noreferrer">
                                {{ __('store.about_open_reviews') }}
                            </a>
                        </article>
                    </div>
                    <article class="about-testimonial-instagram-panel">
                        <div class="about-testimonial-instagram-heading">
                            <img class="about-testimonial-mark about-testimonial-mark--instagram" src="{{ asset('images/ig.svg') }}" alt="" aria-hidden="true">
                            <div>
                                <h3>{{ __('store.about_instagram_title') }}</h3>
                                <p>{{ __('store.about_instagram_copy') }}</p>
                            </div>
                        </div>
                        <blockquote
                            class="instagram-media"
                            data-instgrm-permalink="{{ config('store.about_testimonials.instagram_post_url') }}"
                            data-instgrm-version="14">
                            <a href="{{ config('store.about_testimonials.instagram_post_url') }}" target="_blank" rel="noopener noreferrer">
                                {{ __('store.about_instagram_copy') }}
                            </a>
                        </blockquote>
                        <a class="about-testimonial-instagram-link" href="{{ config('store.about_testimonials.instagram_post_url') }}" target="_blank" rel="noopener noreferrer">
                            {{ __('store.about_open_instagram') }}
                        </a>
                    </article>
                </section>
                <a class="btn btn-primary rounded-pill px-4 mt-3" href="{{ route('games.index') }}">{{ __('store.browse_games') }}</a>
            </div>
        </div>
    </main>
    <x-storefront-footer />
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
    <script async src="https://www.instagram.com/embed.js"></script>
</body>
</html>
