@extends('admin.layout')

@section('title', 'Cashier')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Cashier</h1>
            <p>Record an in-person sale without changing product stock.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif

    <section class="panel p-4">
        @if ($products->isEmpty())
            <p class="mb-0 text-muted">Create a product before recording a cashier sale.</p>
        @else
            <form method="POST" action="{{ route('admin.cashier.store') }}" class="row g-3 align-items-end" data-cashier-form>
                @csrf
                <div class="col-12 col-lg-7">
                    <label class="form-label" for="cashier-product-variant">Product and variant</label>
                    <select class="form-select @error('product_variant_id') is-invalid @enderror" id="cashier-product-variant" name="product_variant_id" required data-cashier-variant>
                        <option value="" data-unit-price="0">Choose a product</option>
                        @foreach ($products as $product)
                            @foreach ($product->variants as $variant)
                                @php($unitPrice = $product->priceInIdr((float) $variant->price, $currencyRates))
                                <option
                                    value="{{ $variant->id }}"
                                    data-unit-price="{{ number_format($unitPrice, 2, '.', '') }}"
                                    @selected((string) old('product_variant_id') === (string) $variant->id)
                                >{{ $product->name }} — {{ $variant->name }} (Rp {{ number_format($unitPrice, 0, ',', '.') }})</option>
                            @endforeach
                        @endforeach
                    </select>
                    @error('product_variant_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label" for="cashier-quantity">Quantity</label>
                    <input class="form-control @error('quantity') is-invalid @enderror" id="cashier-quantity" name="quantity" type="number" min="1" max="100000" step="1" value="{{ old('quantity', 1) }}" required>
                    @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6 col-lg-3">
                    <span class="form-label d-block">Sale total</span>
                    <output class="form-control" data-cashier-total aria-live="polite">Rp 0</output>
                </div>
                <div class="col-12">
                    <button class="btn btn-accent" type="submit">Record sale</button>
                </div>
            </form>

            <script>
                (() => {
                    const form = document.querySelector('[data-cashier-form]');
                    const variantSelect = form?.querySelector('[data-cashier-variant]');
                    const quantityInput = form?.querySelector('[name="quantity"]');
                    const totalOutput = form?.querySelector('[data-cashier-total]');

                    if (!variantSelect || !quantityInput || !totalOutput) {
                        return;
                    }

                    const updateTotal = () => {
                        const unitPrice = Number(variantSelect.selectedOptions[0]?.dataset.unitPrice || 0);
                        const quantity = Number(quantityInput.value || 0);
                        totalOutput.textContent = `Rp ${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(unitPrice * quantity)}`;
                    };

                    variantSelect.addEventListener('change', updateTotal);
                    quantityInput.addEventListener('input', updateTotal);
                    updateTotal();
                })();
            </script>
        @endif
    </section>
@endsection
