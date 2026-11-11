<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Sign In | Meoxstore</title>
	<link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
	<style>
		:root { --ink: #1c3035; --muted: #718186; --line: #dce4e4; --accent: #d66d4d; --rail: #18343a; }
		body { background: #f3f6f5; color: var(--ink); font-family: 'Poppins', sans-serif; }
		.auth-page { display: grid; grid-template-columns: minmax(0, 1fr) minmax(420px, 1fr); min-height: 100vh; }
		.auth-visual { background: var(--rail); min-height: 100vh; overflow: hidden; position: relative; }
		.auth-visual img { height: 100%; inset: 0; object-fit: cover; opacity: .62; position: absolute; width: 100%; }
		.auth-visual::after { background: rgba(24, 52, 58, .45); content: ''; inset: 0; position: absolute; }
		.auth-copy { color: #fff; left: clamp(28px, 7vw, 100px); max-width: 480px; position: absolute; right: 32px; top: 50%; transform: translateY(-50%); z-index: 1; }
		.auth-brand { color: #fff; font-size: 20px; font-weight: 700; text-decoration: none; }
		.auth-brand span { color: #eea07d; }
		.auth-kicker { color: #f1b498; font-size: 12px; font-weight: 600; margin-top: 48px; }
		.auth-copy h1 { font-size: 38px; font-weight: 600; line-height: 1.25; margin: 12px 0; }
		.auth-copy p { color: #e1e9e8; font-size: 14px; line-height: 1.8; max-width: 390px; }
		.auth-main { align-items: center; display: flex; justify-content: center; padding: 42px; }
		.auth-form { max-width: 420px; width: 100%; }
		.auth-form h2 { font-size: 28px; font-weight: 600; margin-bottom: 8px; }
		.auth-form .form-control { border-color: #ccd7d6; min-height: 46px; }
		.auth-form .form-control:focus { border-color: #80a5a2; box-shadow: 0 0 0 .2rem rgba(65, 126, 120, .13); }
		.btn-auth { background: var(--accent); border-color: var(--accent); color: #fff; min-height: 46px; }
		.btn-auth:hover { background: #bd583a; border-color: #bd583a; color: #fff; }
		.auth-back { color: var(--muted); text-decoration: none; }
		.auth-language-switch { display: flex; justify-content: flex-end; margin: 18px 24px 0; position: fixed; right: 0; top: 0; z-index: 10; }
		.auth-language-switch form { display: flex; margin: 0; }
		.auth-language-switch .storefront-language-dropdown { position: relative; }
		.auth-language-switch .storefront-language-dropdown summary { align-items: center; background: #fff; border: 1px solid var(--line); border-radius: 18px; color: var(--ink); cursor: pointer; display: flex; font-size: 14px; gap: 7px; list-style: none; min-height: 36px; padding: 0 10px; }
		.auth-language-switch .storefront-language-dropdown summary::-webkit-details-marker { display: none; }
		.auth-language-switch .storefront-language-dropdown summary:focus-visible { border-color: var(--accent); outline: 2px solid var(--accent); outline-offset: 2px; }
		.auth-language-switch .storefront-language-options { background: #fff; border: 1px solid var(--line); border-radius: 10px; box-shadow: 0 8px 24px rgba(30, 56, 90, .18); min-width: 154px; padding: 5px; position: absolute; right: 0; top: calc(100% + 8px); z-index: 1100; }
		.auth-language-switch .storefront-language-options form { display: block; margin: 0; }
		.auth-language-switch .storefront-language-options button { align-items: center; background: transparent; border: 0; border-radius: 6px; color: var(--ink); cursor: pointer; display: flex; font: inherit; gap: 8px; min-height: 38px; padding: 0 8px; text-align: left; width: 100%; }
		.auth-language-switch .storefront-language-options button:hover, .auth-language-switch .storefront-language-options button:focus-visible, .auth-language-switch .storefront-language-options button[aria-current="true"] { background: #fff0f1; color: var(--accent); }
		.auth-language-switch .storefront-language-name { font-size: 12px; white-space: nowrap; }
		@media (max-width: 767.98px) {
			.auth-page { grid-template-columns: 1fr; }
			.auth-visual { min-height: 210px; }
			.auth-copy { left: 24px; right: 24px; top: 50%; }
			.auth-kicker, .auth-copy p { display: none; }
			.auth-copy h1 { font-size: 24px; margin: 8px 0 0; }
			.auth-main { padding: 36px 24px; }
		}
	</style>
</head>
<body>
<x-storefront-language-switcher class="auth-language-switch" />
<div class="auth-page">
	<aside class="auth-visual" aria-label="Meoxstore">
		<img src="{{ asset('images/banner-image.jpg') }}" alt="">
		<div class="auth-copy">
			<a class="auth-brand" href="{{ route('home') }}">Meo<span>X</span>Store</a>
			<div class="auth-kicker">{{ __('store.account_access') }}</div>
			<h1>{{ __('store.welcome_back') }}</h1>
		</div>
	</aside>
	<main class="auth-main">
		<form class="auth-form" method="POST" action="{{ route('login.store') }}">
			@csrf
			<h2>{{ __('store.sign_in') }}</h2>

			@if ($errors->any())
				<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
			@endif

			<div class="mb-3">
				<label class="form-label" for="email">{{ __('store.email') }}</label>
				<input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" maxlength="255" required autofocus>
				@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
			</div>
			<div class="mb-3">
				<label class="form-label" for="password">{{ __('store.password') }}</label>
				<input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" autocomplete="current-password" required>
				@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
			</div>
			<div class="form-check mb-4">
				<input class="form-check-input" id="remember" name="remember" type="checkbox" value="1">
				<label class="form-check-label" for="remember">{{ __('store.remember_me') }}</label>
			</div>
			<button class="btn btn-auth w-100" type="submit">{{ __('store.sign_in') }}</button>
			<p class="mt-3 mb-0">{{ __('store.no_account') }} <a href="{{ route('register') }}">{{ __('store.register') }}</a></p>
			<a class="auth-back d-inline-block mt-4" href="{{ route('home') }}">{{ __('store.back_to_storefront') }}</a>
		</form>
	</main>
</div>
</body>
</html>
