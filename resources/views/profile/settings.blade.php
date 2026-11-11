<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('store.settings') }} | Meoxstore</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    @vite([
        'resources/css/app.css',
        'resources/css/fontawesome.css',
        'resources/css/templatemo-lugx-gaming.css',
        'resources/css/owl.css',
        'resources/css/animate.css',
        'resources/js/app.js',
    ])
    <style>
        .settings-panel { background: #fff; border: 1px solid #e7ebef; border-radius: 12px; margin: 30px auto 54px; max-width: 620px; padding: clamp(20px, 4vw, 32px); }
        .settings-panel h1 { font-size: 22px; font-weight: 650; margin-bottom: 22px; }
        .settings-divider { border: 0; border-top: 1px solid #e7ebef; margin: 26px 0; }
    </style>
</head>
<body>
    <x-storefront-header />
    <div class="page-heading header-text"><div class="container"><div class="row"><div class="col-lg-12">
        <h3>{{ __('store.settings') }}</h3>
        <span class="breadcrumb"><a href="{{ route('home') }}">{{ __('store.home') }}</a> &gt; <a href="{{ route('profile.edit') }}">{{ __('store.profile') }}</a> &gt; {{ __('store.settings') }}</span>
    </div></div></div></div>
    <main class="container">
        <section class="settings-panel">
            @if (session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
            @endif
            <h1>{{ __('store.account_settings') }}</h1>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label" for="name">{{ __('store.name') }}</label>
                    <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" required maxlength="255">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">{{ __('store.email') }}</label>
                    <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="email" required maxlength="255">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="phone">{{ __('store.phone') }}</label>
                    <input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" autocomplete="tel" maxlength="30">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <hr class="settings-divider">
                <h2 class="h5 mb-3">{{ __('store.change_password') }}</h2>
                <div class="mb-3">
                    <label class="form-label" for="current_password">{{ __('store.current_password') }}</label>
                    <input class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" type="password" autocomplete="current-password">
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">{{ __('store.new_password') }}</label>
                    <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" autocomplete="new-password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password_confirmation">{{ __('store.confirm_password') }}</label>
                    <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-primary rounded-pill" type="submit">{{ __('store.save_profile') }}</button>
                    <a class="btn btn-outline-secondary rounded-pill" href="{{ route('profile.edit') }}">{{ __('store.cancel') }}</a>
                </div>
            </form>
        </section>
    </main>
    <footer><div class="container"><div class="col-lg-12"><p>Copyright © {{ date('Y') }} Meoxstore. All rights reserved.</p></div></div></footer>
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
</body>
</html>
