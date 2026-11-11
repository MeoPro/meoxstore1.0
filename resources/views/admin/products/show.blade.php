@extends('admin.layout')

@section('title', $product->name)

@section('content')
    <div class="page-heading">
        <div><h1>{{ $product->name }}</h1></div>
        <div class="d-flex gap-2"><a class="btn btn-light border" href="{{ route('admin.products.index') }}">Back</a><a class="btn btn-accent" href="{{ route('admin.products.edit', $product) }}">Edit product</a></div>
    </div>
    <section class="panel mb-3 p-4">
        <div class="row g-4">
            <div class="col-12 col-md-3">
                @if ($product->image)
                    <img class="img-fluid rounded" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}">
                @else
                    <div class="align-items-center bg-light d-flex justify-content-center rounded text-muted" style="min-height: 180px">No image</div>
                @endif
            </div>
            <div class="col-12 col-md-9">
                <div class="row g-3">
                    <div class="col-sm-6"><div class="small text-muted">Game category</div><div class="fw-semibold">{{ $product->gameCategory?->name ?? '—' }}</div></div>
                    <div class="col-sm-6"><div class="small text-muted">Item category</div><div class="fw-semibold">{{ $product->itemCategory?->name ?? '—' }}</div></div>
                    <div class="col-sm-6"><div class="small text-muted">Status</div><div class="fw-semibold">{{ ucfirst($product->status) }}</div></div>
                    <div class="col-sm-6"><div class="small text-muted">Featured</div><div class="fw-semibold">{{ $product->is_featured ? 'Yes' : 'No' }}</div></div>
                    <div class="col-12"><div class="small text-muted">Description</div><div>{{ $product->description ?: 'No description.' }}</div></div>
                </div>
            </div>
        </div>
    </section>
    <section class="panel overflow-hidden">
        <div class="border-bottom p-3"><h2 class="fs-6 fw-semibold mb-0">Variants ({{ $product->variants->count() }})</h2></div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Order</th><th>Name</th><th>Price</th><th>Stock</th><th>Min / max</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($product->variants as $variant)
                    @php
                        $variantPrice = (float) $variant->price;
                        $idrPrice = $product->priceInIdr($variantPrice, $currencyRates);
                    @endphp
                    <tr>
                        <td>{{ $variant->sort_order }}</td>
                        <td class="fw-semibold">{{ $variant->name }}</td>
                        <td>
                            @if ($product->bobuxType() === 'login')
                                <div>Rp {{ rtrim(rtrim(number_format($idrPrice, 2, '.', ','), '0'), '.') }} ({{ __('store.bobux_amount', ['count' => number_format((float) $variant->bobux_amount)]) }})</div>
                            @elseif ($product->usesBobuxRate())
                                <div>{{ number_format($variantPrice, 0, ',', '.') }} Bobux</div>
                            @else
                                <div>Rp {{ rtrim(rtrim(number_format($idrPrice, 2, '.', ','), '0'), '.') }}</div>
                            @endif
                            <div class="currency-preview">
                                <span>≈ Rp {{ rtrim(rtrim(number_format($idrPrice, 2, '.', ','), '0'), '.') }}</span>
                                <span>≈ RM {{ number_format($idrPrice / $currencyRates->idr_per_myr, 2) }}</span>
                                <span>≈ ${{ number_format($idrPrice / $currencyRates->idr_per_usd, 2) }}</span>
                            </div>
                        </td>
                        <td>{{ $variant->stock }}</td>
                        <td>{{ $variant->minimum_quantity }} / {{ $variant->maximum_quantity }}</td>
                        <td>{{ ucfirst($variant->status) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection