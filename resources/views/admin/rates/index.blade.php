@extends('admin.layout')

@section('title', 'Rates')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Website rates and Bobux transactions</h1>
            <p class="form-text mb-0">Update website currency conversion rates and control purchases for each Bobux delivery type.</p>
        </div>
    </div>

    <form class="panel p-4" method="POST" action="{{ route('admin.rates.update') }}">
        @csrf
        @method('PATCH')

        <section class="mb-4">
            <h2 class="fs-5 mb-3">Website currency rates</h2>
            <div class="row g-3">
                @foreach ([
                    'idr_per_myr' => ['1 RM = IDR', $currencyRates->idr_per_myr],
                    'idr_per_usd' => ['1 USD = IDR', $currencyRates->idr_per_usd],
                    'idr_per_robux' => ['1 Bobux Gift In-game = IDR', $currencyRates->idr_per_robux],
                    'idr_per_gamepass_bobux' => ['1 Bobux Gamepass 5 hari = IDR', $currencyRates->idr_per_gamepass_bobux],
                ] as $name => [$label, $rate])
                    <div class="col-12 col-sm-6 col-lg-4">
                        <label class="form-label" for="{{ $name }}">{{ $label }}</label>
                        <input
                            class="form-control @error($name) is-invalid @enderror"
                            id="{{ $name }}"
                            name="{{ $name }}"
                            type="number"
                            min="0.0001"
                            step="0.0001"
                            value="{{ old($name, rtrim(rtrim(number_format((float) $rate, 4, '.', ''), '0'), '.')) }}"
                            required
                        >
                        @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endforeach
            </div>
        </section>

        <section class="border-top pt-4">
            <h2 class="fs-5 mb-3">Bobux transaction switches</h2>
            <div class="row g-3">
                @foreach ([
                    'bobux_gift_ingame_enabled' => ['Bobux Gift In-game', $currencyRates->bobux_gift_ingame_enabled],
                    'bobux_gamepass_5_days_enabled' => ['Bobux Gamepass 5 hari', $currencyRates->bobux_gamepass_5_days_enabled],
                    'bobux_login_enabled' => ['Bobux via Login', $currencyRates->bobux_login_enabled],
                ] as $name => [$label, $isEnabled])
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="border rounded p-3 h-100">
                            <input type="hidden" name="{{ $name }}" value="0">
                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input"
                                    id="{{ $name }}"
                                    name="{{ $name }}"
                                    type="checkbox"
                                    value="1"
                                    role="switch"
                                    @checked(old($name, $isEnabled))
                                >
                                <label class="form-check-label fw-semibold" for="{{ $name }}">{{ $label }}</label>
                            </div>
                            <small class="form-text d-block mt-2">Allow customers to purchase this Bobux type.</small>
                            @error($name)<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="d-flex justify-content-end mt-4">
            <button class="btn btn-accent" type="submit">Save rates and switches</button>
        </div>
    </form>
@endsection
