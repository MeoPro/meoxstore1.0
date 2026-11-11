@php
    $languages = [
        'en' => ['code' => 'ENG', 'name' => 'English'],
        'id' => ['code' => 'IDN', 'name' => 'Bahasa Indonesia'],
        'ms' => ['code' => 'MYS', 'name' => 'Bahasa Melayu'],
    ];
    $currentLocale = app()->getLocale();
@endphp

<div {{ $attributes->merge(['class' => 'storefront-language-switch']) }}>
    <details class="storefront-language-dropdown">
        <summary aria-label="{{ __('store.choose_language') }}">
            @include('components.storefront-language-flag', ['locale' => $currentLocale])
            <span>{{ $languages[$currentLocale]['code'] }}</span>
            <span class="storefront-language-chevron" aria-hidden="true"></span>
        </summary>
        <div class="storefront-language-options">
            @foreach ($languages as $locale => $language)
                <form method="POST" action="{{ route('locale.update') }}">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $locale }}">
                    <button type="submit" lang="{{ $locale }}" @if ($currentLocale === $locale) aria-current="true" @endif>
                        @include('components.storefront-language-flag', ['locale' => $locale])
                        <span>{{ $language['code'] }}</span>
                        <span class="storefront-language-name">{{ $language['name'] }}</span>
                    </button>
                </form>
            @endforeach
        </div>
    </details>
</div>
