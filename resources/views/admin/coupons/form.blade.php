@extends('admin.layout')

@section('title', $title)

@section('content')
    <div class="page-heading">
        <div><h1>{{ $title }}</h1></div>
        <a class="btn btn-outline-secondary" href="{{ route('admin.coupons.index') }}">Back to coupons</a>
    </div>
    <form class="panel p-4" method="POST" action="{{ $action }}" style="max-width: 760px">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif
        <div class="mb-3">
            <label class="form-label" for="code">Coupon code</label>
            <input class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $coupon->code) }}" required maxlength="40" pattern="[A-Za-z0-9_-]+">
            <div class="form-text">Letters, numbers, underscores, and hyphens only. Codes are stored in uppercase.</div>
            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="discount_type">Discount type</label>
            <select class="form-select @error('discount_type') is-invalid @enderror" id="discount_type" name="discount_type" required>
                <option value="percent" @selected(old('discount_type', $coupon->discount_type) === 'percent')>Percentage</option>
                <option value="fixed" @selected(old('discount_type', $coupon->discount_type) === 'fixed')>Fixed amount (IDR)</option>
            </select>
            @error('discount_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="discount_value">Discount value</label>
            <input class="form-control @error('discount_value') is-invalid @enderror" id="discount_value" name="discount_value" type="number" min="0.01" step="0.01" value="{{ old('discount_value', $coupon->discount_value) }}" required>
            <div class="form-text">Percentage discounts are capped at 100%. Fixed discounts are entered in IDR.</div>
            @error('discount_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="stock">Total uses</label>
            <input class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" type="number" min="0" step="1" value="{{ old('stock', $coupon->stock) }}" required>
            <div class="form-text">
                Available uses are reduced at checkout and restored if an order is cancelled.
                @if ($coupon->exists)
                    Current: {{ $coupon->availableUses() }} available, {{ $coupon->reserved_uses }} reserved, {{ $coupon->redeemed_uses }} used.
                @endif
            </div>
            @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-check mb-4">
            <input class="form-check-input" id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $coupon->is_active))>
            <label class="form-check-label" for="is_active">Active and available at checkout</label>
        </div>
        <button class="btn btn-accent" type="submit">Save coupon</button>
    </form>
@endsection
