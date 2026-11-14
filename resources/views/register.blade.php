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
        .auth-language-switch { --language-accent: #d66d4d; --language-ink: #1c3035; --language-line: #dce4e4; --language-muted: #667085; display: flex; justify-content: center; margin-bottom: 20px; }
        .auth-language-switch form { display: flex; margin: 0; }
        .auth-language-switch .storefront-language-dropdown { position: relative; }
        .auth-language-switch .storefront-language-dropdown summary { align-items: center; background: rgba(255, 255, 255, .94); border: 1px solid var(--language-line); border-radius: 999px; box-shadow: 0 8px 22px rgba(16, 24, 40, .08); color: var(--language-ink); cursor: pointer; display: flex; font-size: 13px; font-weight: 600; gap: 9px; height: 42px; list-style: none; padding: 0 14px; transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease; }
        .auth-language-switch .storefront-language-dropdown summary::-webkit-details-marker { display: none; }
        .auth-language-switch .storefront-language-dropdown summary:hover { border-color: #b8c5d6; box-shadow: 0 10px 24px rgba(16, 24, 40, .12); transform: translateY(-1px); }
        .auth-language-switch .storefront-language-dropdown summary:focus-visible { border-color: var(--language-accent); outline: 2px solid var(--language-accent); outline-offset: 2px; }
        .auth-language-switch .storefront-language-flag { border-radius: 4px; box-shadow: 0 0 0 1px rgba(16, 24, 40, .12); display: block; flex: 0 0 24px; height: 16px; width: 24px; }
        .auth-language-switch .storefront-language-dropdown summary > span:first-of-type { letter-spacing: .04em; }
        .auth-language-switch .storefront-language-chevron { border-bottom: 1.5px solid currentColor; border-right: 1.5px solid currentColor; height: 6px; margin-left: 2px; transform: translateY(-2px) rotate(45deg); transition: transform .18s ease; width: 6px; }
        .auth-language-switch .storefront-language-dropdown[open] .storefront-language-chevron { transform: translateY(1px) rotate(225deg); }
        .auth-language-switch .storefront-language-options { background: #fff; border: 1px solid rgba(148, 163, 184, .28); border-radius: 14px; box-shadow: 0 18px 40px rgba(16, 24, 40, .16); display: grid; gap: 4px; min-width: 232px; padding: 7px; position: absolute; right: 0; top: calc(100% + 9px); z-index: 1100; }
        .auth-language-switch .storefront-language-options form { display: block; margin: 0; }
        .auth-language-switch .storefront-language-options button { align-items: center; background: transparent; border: 0; border-radius: 10px; color: var(--language-ink); cursor: pointer; display: grid; font: inherit; font-size: 13px; gap: 9px; grid-template-columns: 24px 38px minmax(0, 1fr); min-height: 42px; padding: 0 10px; text-align: left; transition: background-color .16s ease, color .16s ease; width: 100%; }
        .auth-language-switch .storefront-language-options button:hover, .auth-language-switch .storefront-language-options button:focus-visible, .auth-language-switch .storefront-language-options button[aria-current="true"] { background: #fff2ed; color: var(--language-accent); }
        .auth-language-switch .storefront-language-options button:focus-visible { outline: 2px solid var(--language-accent); outline-offset: -2px; }
        .auth-language-switch .storefront-language-options button > span:first-of-type { font-size: 11px; font-weight: 700; letter-spacing: .04em; }
        .auth-language-switch .storefront-language-name { color: var(--language-muted); font-size: 12px; white-space: nowrap; }
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
