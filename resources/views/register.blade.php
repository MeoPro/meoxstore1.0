<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar | Meoxstore</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { align-items: center; background: #f3f6f5; color: #1c3035; display: flex; font-family: 'Poppins', sans-serif; justify-content: center; min-height: 100vh; padding: 24px; }
        .register-card { background: #fff; border: 1px solid #dce4e4; border-radius: 18px; max-width: 480px; padding: clamp(24px, 5vw, 44px); width: 100%; }
        .register-card h1 { font-size: 28px; font-weight: 600; margin-bottom: 8px; }
        .register-card .form-control { min-height: 46px; }
        .btn-register { background: #d66d4d; border-color: #d66d4d; color: #fff; min-height: 46px; }
        .btn-register:hover { background: #bd583a; border-color: #bd583a; color: #fff; }
        .auth-language-switch { display: flex; justify-content: center; margin-bottom: 20px; }
        .auth-language-switch form { display: flex; margin: 0; }
        .auth-language-switch .storefront-language-dropdown { position: relative; }
        .auth-language-switch .storefront-language-dropdown summary { align-items: center; background: #fff; border: 1px solid #dce4e4; border-radius: 18px; color: #1c3035; cursor: pointer; display: flex; font-size: 14px; gap: 7px; list-style: none; min-height: 36px; padding: 0 10px; }
        .auth-language-switch .storefront-language-dropdown summary::-webkit-details-marker { display: none; }
        .auth-language-switch .storefront-language-dropdown summary:focus-visible { border-color: #d66d4d; outline: 2px solid #d66d4d; outline-offset: 2px; }
        .auth-language-switch .storefront-language-options { background: #fff; border: 1px solid #dce4e4; border-radius: 10px; box-shadow: 0 8px 24px rgba(30, 56, 90, .18); min-width: 154px; padding: 5px; position: absolute; right: 0; top: calc(100% + 8px); z-index: 1100; }
        .auth-language-switch .storefront-language-options form { display: block; margin: 0; }
        .auth-language-switch .storefront-language-options button { align-items: center; background: transparent; border: 0; border-radius: 6px; color: #1c3035; cursor: pointer; display: flex; font: inherit; gap: 8px; min-height: 38px; padding: 0 8px; text-align: left; width: 100%; }
        .auth-language-switch .storefront-language-options button:hover, .auth-language-switch .storefront-language-options button:focus-visible, .auth-language-switch .storefront-language-options button[aria-current="true"] { background: #fff0f1; color: #d66d4d; }
        .auth-language-switch .storefront-language-name { font-size: 12px; white-space: nowrap; }
    </style>
</head>
<body>
<main class="register-card">
    <x-storefront-language-switcher class="auth-language-switch" />
    <a class="text-decoration-none fw-bold" href="{{ route('home') }}">MeoXStore</a>
    <h1 class="mt-4">{{ __('store.register_title') }}</h1>
    <p class="text-secondary">{{ __('store.register_intro') }}</p>
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="name">{{ __('store.name') }}</label>
            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="255" required autofocus>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="email">{{ __('store.email') }}</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">{{ __('store.password') }}</label>
            <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="form-label" for="password_confirmation">{{ __('store.confirm_password') }}</label>
            <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
        </div>
        <button class="btn btn-register w-100" type="submit">{{ __('store.register') }}</button>
    </form>
    <p class="mt-3 mb-0">{{ __('store.have_account') }} <a href="{{ route('login') }}">{{ __('store.sign_in') }}</a></p>
</main>
</body>
</html>
