<footer class="storefront-footer">
    <div class="container">
        <div class="storefront-footer-grid">
            <div class="storefront-footer-column-stack">
                <section aria-labelledby="storefront-footer-help">
                    <h2 id="storefront-footer-help">{{ __('store.footer_help') }}</h2>
                    <ul>
                        <li><a href="{{ route('contact') }}">{{ __('store.footer_help_center') }}</a></li>
                        <li><a href="{{ route('orders.lookup') }}">{{ __('store.footer_track_order') }}</a></li>
                    </ul>
                </section>
                <section aria-labelledby="storefront-footer-about">
                    <h2 id="storefront-footer-about">Meoxstore</h2>
                    <ul>
                        <li><a href="{{ route('about') }}">{{ __('store.footer_about') }}</a></li>
                        <li><a href="{{ route('blog.index') }}">{{ __('store.footer_blog') }}</a></li>
                        <li><a href="{{ route('contact') }}">{{ __('store.footer_contact') }}</a></li>
                    </ul>
                </section>
            </div>
            <div class="storefront-footer-column-stack">
                <section aria-labelledby="storefront-footer-buyers">
                    <h2 id="storefront-footer-buyers">{{ __('store.footer_buyers') }}</h2>
                    <ul>
                        <li><a href="{{ route('shop') }}">{{ __('store.footer_shop') }}</a></li>
                        <li><a href="{{ route('cart.index') }}">{{ __('store.footer_cart') }}</a></li>
                        <li><a href="{{ route('orders.lookup') }}">{{ __('store.footer_track_order') }}</a></li>
                    </ul>
                </section>
            </div>
            <section class="storefront-footer-payments" aria-labelledby="storefront-footer-payments-heading">
                <h2 id="storefront-footer-payments-heading">{{ __('store.footer_payment_methods') }}</h2>
                @if ($paymentMethods->isNotEmpty())
                    <ul class="storefront-footer-payment-list">
                        @foreach ($paymentMethods as $paymentMethod)
                            <li>
                                <span class="storefront-footer-payment-logo">
                                    <img
                                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($paymentMethod->logo_path) }}"
                                        alt="{{ $paymentMethod->name }}"
                                        loading="lazy">
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
            <section class="storefront-footer-social" aria-labelledby="storefront-footer-social-heading">
                <h2 id="storefront-footer-social-heading">{{ __('store.footer_follow_us') }}</h2>
                @if ($socialLinks !== [])
                    <ul class="storefront-footer-social-list">
                        @foreach ($socialLinks as $socialLink)
                            <li>
                                <a
                                    class="storefront-footer-social-link storefront-footer-social-link--{{ $socialLink['network'] }}"
                                    href="{{ $socialLink['url'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="{{ $socialLink['label'] }}"
                                    title="{{ $socialLink['label'] }}">
                                    @if ($socialLink['logo_url'] !== null)
                                        <img src="{{ $socialLink['logo_url'] }}" alt="" loading="lazy">
                                    @elseif ($socialLink['icon'] !== '')
                                        <i class="fa {{ $socialLink['icon'] }}" aria-hidden="true"></i>
                                    @else
                                        <span aria-hidden="true">{{ $socialLink['mark'] }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                @if ($googleMapsProfileUrl !== '')
                    <a class="storefront-footer-maps-link" href="{{ $googleMapsProfileUrl }}" target="_blank" rel="noopener noreferrer">
                        {{ __('store.footer_google_maps') }}
                    </a>
                @endif
            </section>
        </div>

        <div class="storefront-footer-contact">
            <div>
                <h2>{{ __('store.footer_consumer_complaints') }}</h2>
                <p>{{ __('store.footer_complaint_text') }}</p>
            </div>
            <div class="storefront-footer-contact-links">
                @if ($supportEmail !== '')
                    <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>
                @endif
                @if ($supportPhone !== '')
                    <a href="tel:{{ $supportPhone }}">{{ $supportPhone }}</a>
                @endif
            </div>
        </div>

        <div class="storefront-footer-bottom">
            <span>Copyright © {{ date('Y') }} Meoxstore. {{ __('store.footer_copyright') }}</span>
            @if ($showTemplateCredit)
                <a href="https://templatemo.com" target="_blank" rel="nofollow noopener noreferrer">
                    {{ __('store.footer_template_credit') }}
                </a>
            @endif
        </div>
    </div>
</footer>