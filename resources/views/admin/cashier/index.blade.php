@extends('admin.layout')

@section('title', 'Cashier')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Cashier</h1>
            <p>Add one or more products to a sale. Product stock stays unchanged.</p>
        </div>
        <a class="btn btn-outline-primary" href="{{ route('admin.cashier.history') }}">Cashier history</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    @if ($products->isEmpty())
        <section class="panel p-4">
            <p class="mb-0 text-muted">Create a product before recording a cashier sale.</p>
        </section>
    @else
        <div class="row g-4 align-items-start">
            <section class="col-12 col-xl-7" aria-labelledby="cashier-products-heading">
                <div class="panel p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                        <div>
                            <h2 class="h5 mb-1" id="cashier-products-heading">Choose products</h2>
                            <p class="text-muted small mb-0">Click a product to add it to the cart.</p>
                        </div>
                        <div class="flex-grow-1" style="max-width: 360px;">
                            <label class="visually-hidden" for="cashier-product-search">Search products</label>
                            <input class="form-control" id="cashier-product-search" type="search" placeholder="Search products..." autocomplete="off" data-cashier-search>
                        </div>
                    </div>

                    <div class="row g-3" data-cashier-products>
                        @foreach ($products as $product)
                            @foreach ($product->variants as $variant)
                                @php($unitPrice = $product->priceInIdr((float) $variant->price, $currencyRates))
                                <div class="col-12 col-md-6" data-cashier-product-card data-search-text="{{ \Illuminate\Support\Str::lower($product->name.' '.$variant->name) }}">
                                    <button
                                        class="btn btn-light border text-start w-100 h-100 p-3 rounded-4"
                                        type="button"
                                        data-cashier-add
                                        data-variant-id="{{ $variant->id }}"
                                        data-product-name="{{ $product->name }}"
                                        data-variant-name="{{ $variant->name }}"
                                        data-unit-price="{{ number_format($unitPrice, 2, '.', '') }}"
                                    >
                                        <span class="d-flex align-items-center gap-3">
                                            @if ($product->image)
                                                <img
                                                    class="rounded-3 flex-shrink-0"
                                                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) }}"
                                                    alt=""
                                                    width="56"
                                                    height="56"
                                                    style="object-fit: cover;"
                                                >
                                            @else
                                                <span class="rounded-3 d-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold flex-shrink-0" style="width: 56px; height: 56px;">
                                                    {{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}
                                                </span>
                                            @endif
                                            <span class="min-w-0 flex-grow-1">
                                                <strong class="d-block text-body">{{ $product->name }}</strong>
                                                <span class="d-block text-muted small">{{ $variant->name }}</span>
                                                <span class="d-block fw-semibold mt-1">Rp {{ number_format($unitPrice, 0, ',', '.') }}</span>
                                            </span>
                                            <span class="badge rounded-pill text-bg-primary">Add</span>
                                        </span>
                                    </button>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="col-12 col-xl-5" aria-labelledby="cashier-cart-heading">
                <form method="POST" action="{{ route('admin.cashier.store') }}" class="panel p-4 sticky-top" style="top: 1rem;" data-cashier-form>
                    @csrf
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h2 class="h5 mb-1" id="cashier-cart-heading">Sale cart</h2>
                            <p class="text-muted small mb-0">Five-star reviews are added once per product.</p>
                        </div>
                        <span class="badge rounded-pill text-bg-light" data-cashier-item-count>0 items</span>
                    </div>

                    <div class="d-grid gap-2 mb-3" data-cashier-cart-lines></div>
                    <p class="text-muted text-center py-4 mb-0" data-cashier-cart-empty>Your cart is empty. Choose products to begin.</p>
                    <div data-cashier-inputs></div>

                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-semibold">Sale total</span>
                            <output class="fs-5 fw-bold" data-cashier-total aria-live="polite">Rp 0</output>
                        </div>
                        <button class="btn btn-primary w-100" type="submit" data-cashier-submit disabled>Record sale</button>
                        <p class="small text-muted text-center mt-2 mb-0" data-cashier-status aria-live="polite"></p>
                    </div>
                </form>
            </section>
        </div>

        <script>
            (() => {
                const form = document.querySelector('[data-cashier-form]');
                const cartLines = form?.querySelector('[data-cashier-cart-lines]');
                const cartEmpty = form?.querySelector('[data-cashier-cart-empty]');
                const hiddenInputs = form?.querySelector('[data-cashier-inputs]');
                const totalOutput = form?.querySelector('[data-cashier-total]');
                const itemCountOutput = form?.querySelector('[data-cashier-item-count]');
                const submitButton = form?.querySelector('[data-cashier-submit]');
                const statusOutput = form?.querySelector('[data-cashier-status]');
                const productButtons = Array.from(document.querySelectorAll('[data-cashier-add]'));
                const searchInput = document.querySelector('[data-cashier-search]');

                if (!form || !cartLines || !cartEmpty || !hiddenInputs || !totalOutput || !itemCountOutput || !submitButton || !statusOutput) {
                    return;
                }

                const products = new Map(productButtons.map((button) => [
                    button.dataset.variantId,
                    {
                        productName: button.dataset.productName,
                        variantName: button.dataset.variantName,
                        unitPrice: Number(button.dataset.unitPrice || 0),
                    },
                ]));
                const cart = new Map();
                const money = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });

                const element = (tagName, className, text) => {
                    const node = document.createElement(tagName);
                    node.className = className;
                    if (text !== undefined) {
                        node.textContent = text;
                    }
                    return node;
                };

                const render = () => {
                    cartLines.replaceChildren();
                    hiddenInputs.replaceChildren();
                    let total = 0;
                    let totalQuantity = 0;

                    Array.from(cart.entries()).forEach(([variantId, item], index) => {
                        const lineTotal = item.unitPrice * item.quantity;
                        total += lineTotal;
                        totalQuantity += item.quantity;

                        const row = element('div', 'border rounded-3 p-3');
                        const heading = element('div', 'd-flex justify-content-between align-items-start gap-2');
                        const details = element('div', 'min-w-0');
                        details.append(
                            element('strong', 'd-block text-break', `${item.productName} — ${item.variantName}`),
                            element('span', 'd-block text-muted small mt-1', `Rp ${money.format(item.unitPrice)} each`),
                        );

                        const removeButton = element('button', 'btn btn-sm btn-outline-danger', 'Remove');
                        removeButton.type = 'button';
                        removeButton.setAttribute('aria-label', `Remove ${item.productName}`);
                        removeButton.addEventListener('click', () => {
                            cart.delete(variantId);
                            render();
                        });
                        heading.append(details, removeButton);

                        const controls = element('div', 'd-flex justify-content-between align-items-center gap-3 mt-3');
                        const quantityInput = element('input', 'form-control form-control-sm');
                        quantityInput.type = 'number';
                        quantityInput.min = '1';
                        quantityInput.max = '100000';
                        quantityInput.step = '1';
                        quantityInput.value = String(item.quantity);
                        quantityInput.setAttribute('aria-label', `Quantity for ${item.productName}`);
                        quantityInput.style.maxWidth = '100px';
                        quantityInput.addEventListener('change', () => {
                            const requestedQuantity = Number(quantityInput.value);
                            const quantity = Number.isFinite(requestedQuantity)
                                ? Math.min(100000, Math.max(1, Math.floor(requestedQuantity)))
                                : 1;
                            cart.set(variantId, { ...item, quantity });
                            render();
                        });
                        controls.append(
                            quantityInput,
                            element('strong', 'text-nowrap', `Rp ${money.format(lineTotal)}`),
                        );
                        row.append(heading, controls);
                        cartLines.append(row);

                        const variantInput = element('input', '');
                        variantInput.type = 'hidden';
                        variantInput.name = `items[${index}][variant_id]`;
                        variantInput.value = variantId;
                        const quantityHiddenInput = element('input', '');
                        quantityHiddenInput.type = 'hidden';
                        quantityHiddenInput.name = `items[${index}][quantity]`;
                        quantityHiddenInput.value = String(item.quantity);
                        hiddenInputs.append(variantInput, quantityHiddenInput);
                    });

                    cartEmpty.hidden = cart.size > 0;
                    totalOutput.textContent = `Rp ${money.format(total)}`;
                    itemCountOutput.textContent = `${totalQuantity} ${totalQuantity === 1 ? 'item' : 'items'}`;
                    submitButton.disabled = cart.size === 0;
                };

                productButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const variantId = button.dataset.variantId;
                        const current = cart.get(variantId);

                        if (!current && cart.size >= 50) {
                            statusOutput.textContent = 'A sale can contain up to 50 different variants.';
                            return;
                        }

                        cart.set(variantId, {
                            ...products.get(variantId),
                            quantity: Math.min(100000, (current?.quantity || 0) + 1),
                        });
                        statusOutput.textContent = '';
                        render();
                    });
                });

                searchInput?.addEventListener('input', () => {
                    const searchTerm = searchInput.value.trim().toLocaleLowerCase();
                    document.querySelectorAll('[data-cashier-product-card]').forEach((card) => {
                        card.hidden = !card.dataset.searchText.includes(searchTerm);
                    });
                });

                form.addEventListener('submit', (event) => {
                    if (cart.size === 0) {
                        event.preventDefault();
                        statusOutput.textContent = 'Add at least one product before recording the sale.';
                    }
                });

                const previousItems = @js(old('items', []));
                if (Array.isArray(previousItems)) {
                    previousItems.forEach((item) => {
                        const variantId = String(item?.variant_id || '');
                        const quantity = Number(item?.quantity);
                        if (products.has(variantId) && Number.isInteger(quantity) && quantity > 0 && quantity <= 100000) {
                            cart.set(variantId, { ...products.get(variantId), quantity });
                        }
                    });
                }

                render();
            })();
        </script>
    @endif
@endsection
