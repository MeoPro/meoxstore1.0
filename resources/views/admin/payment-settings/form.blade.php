@extends('admin.layout')

@section('title', $title)

@section('content')
    <div class="page-heading">
        <div><h1>{{ $title }}</h1></div>
        <a class="btn btn-outline-secondary" href="{{ route('admin.payment-settings.index') }}">Back to payment methods</a>
    </div>
    <form class="panel p-4" method="POST" action="{{ $action }}" enctype="multipart/form-data" style="max-width: 760px">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif
        <div class="mb-3">
            <label class="form-label" for="name">Payment method name</label>
            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $paymentSetting->name ?: $paymentSetting->bank_name) }}" required maxlength="100">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="currency">Payment currency</label>
            <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency" required>
                @foreach (['idr' => 'IDR', 'myr' => 'RM', 'usd' => 'USD'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('currency', $paymentSetting->currency) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="logo">Payment provider logo</label>
            <input class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp">
            <div class="form-text">Optional. PNG, JPG, or WEBP, up to 2 MB. This logo appears in the checkout payment selector.</div>
            @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @if ($paymentSetting->logo_path)
                <img class="mt-3 rounded border" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($paymentSetting->logo_path) }}" alt="Current payment provider logo" style="height: 56px; max-width: 120px; object-fit: contain; width: auto">
            @endif
        </div>
        <div class="mb-3">
            <label class="form-label" for="account_name">Account holder</label>
            <input class="form-control @error('account_name') is-invalid @enderror" id="account_name" name="account_name" value="{{ old('account_name', $paymentSetting->account_name) }}" maxlength="255">
            @error('account_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="account_number">Payment to (account number, email, or ID)</label>
            <input class="form-control @error('account_number') is-invalid @enderror" id="account_number" name="account_number" value="{{ old('account_number', $paymentSetting->account_number) }}" maxlength="100">
            @error('account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="qr_code">QR payment image</label>
            <input class="form-control @error('qr_code') is-invalid @enderror" id="qr_code" name="qr_code" type="file" accept="image/png,image/jpeg,image/webp">
            <div class="form-text">Upload a QR image to use QRIS. PNG, JPG, or WEBP, up to 4 MB.</div>
            @error('qr_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @if ($paymentSetting->qr_code_path)
                <img class="mt-3 rounded border" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($paymentSetting->qr_code_path) }}" alt="Current payment QR code" style="display: block; height: auto; max-height: 180px; max-width: 100%; object-fit: contain; width: auto">
            @endif
        </div>
        <div class="mb-3">
            <label class="form-label" for="instructions">Payment instructions</label>
            <textarea class="form-control @error('instructions') is-invalid @enderror" id="instructions" name="instructions" rows="4" maxlength="2000">{{ old('instructions', $paymentSetting->instructions) }}</textarea>
            @error('instructions')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <input type="hidden" name="is_active_present" value="1">
        <div class="form-check mb-4">
            <input class="form-check-input" id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $paymentSetting->is_active))>
            <label class="form-check-label" for="is_active">Available to customers at checkout</label>
        </div>
        <button class="btn btn-accent" type="submit">Save payment method</button>
    </form>
@endsection
