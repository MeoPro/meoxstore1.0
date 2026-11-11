<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('store.cart_title') }} | Meoxstore</title>
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
        .cart-layout { align-items: start; display: grid; gap: 36px; grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr); }
        .cart-items { min-width: 0; }
        .cart-toolbar { align-items: center; border-bottom: 1px solid #e3e7ec; display: flex; gap: 18px; justify-content: space-between; padding: 14px 0; }
        .cart-toolbar-actions { align-items: center; display: flex; gap: 16px; }
        .cart-toolbar .form-check { margin: 0; }
        .cart-item { align-items: center; border-bottom: 1px solid #e3e7ec; display: grid; gap: 16px; grid-template-columns: minmax(0, 1fr) auto; padding: 22px 0; }
        .cart-item-main { align-items: center; display: flex; gap: 12px; min-width: 0; }
        .cart-item-main img { background: #f5f6f8; border-radius: 9px; flex: 0 0 auto; height: 96px; object-fit: cover; width: 104px; }
        .cart-item-main h2 { font-size: 16px; font-weight: 650; margin: 0 0 5px; }
        .cart-item-main .form-check-input { flex: 0 0 auto; margin: 0; }
        .cart-item-price { color: #1976bd; font-size: 18px; font-weight: 700; }
        .cart-unit-price { color: #798394; display: block; font-size: 12px; font-weight: 400; }
        .cart-item-subtotal { color: #798394; display: block; font-size: 12px; font-weight: 400; margin-top: 4px; }
        .cart-actions { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; }
        .cart-quantity-form { align-items: center; display: inline-flex; gap: 0; }
        .cart-quantity { border: 1px solid #dce3ec; border-left: 0; border-radius: 0; border-right: 0; font-weight: 650; min-height: 38px; padding: 5px 3px; text-align: center; width: 44px; }
        .cart-quantity::-webkit-inner-spin-button, .cart-quantity::-webkit-outer-spin-button { appearance: none; margin: 0; }
        .cart-quantity { -moz-appearance: textfield; }
        .cart-quantity-step { align-items: center; background: #fff; border: 1px solid #dce3ec; color: #344054; display: inline-flex; font-size: 17px; height: 38px; justify-content: center; width: 36px; }
        .cart-quantity-step:first-child { border-radius: 8px 0 0 8px; }
        .cart-quantity-step:last-child { border-radius: 0 8px 8px 0; }
        .cart-quantity-step:hover:not(:disabled) { background: #f1f6fd; color: #2877c9; }
        .cart-quantity-step:disabled { color: #aab3c0; }
        .cart-item-details { position: relative; }
        .cart-item-details summary { color: #536174; cursor: pointer; font-size: 12px; font-weight: 600; white-space: nowrap; }
        .cart-item-details form { background: #f8f9fb; border: 1px solid #e3e7ec; border-radius: 12px; margin-top: 10px; padding: 14px; position: absolute; right: 0; top: 100%; width: min(380px, 80vw); z-index: 3; }
        .cart-item-details .form-control { background: #fff; }
        .cart-seller-note { color: #697586; font-size: 13px; margin: 8px 0 0; overflow-wrap: anywhere; }
        .cart-summary { background: #fff; border: 1px solid #dce3ec; border-radius: 5px; padding: 22px; position: sticky; top: 20px; }
        .cart-summary-title { font-size: 16px; font-weight: 650; margin: 0 0 22px; }
        .cart-summary-total { border-bottom: 1px solid #e3e7ec; display: flex; font-size: 14px; justify-content: space-between; padding-bottom: 18px; }
        .cart-summary-bill { display: flex; font-size: 17px; font-weight: 700; justify-content: space-between; padding: 18px 0; }
        .cart-summary .btn-checkout { background: #009b69; border: 0; border-radius: 5px; color: #fff; font-weight: 650; min-height: 48px; }
        .cart-summary .btn-checkout:hover { background: #00845a; color: #fff; }
        .page-heading.header-text { border-radius: 0 0 56px 56px; padding: 108px 0 22px; }
        .page-heading.header-text h3 { font-size: 26px; margin: 10px 0; }
        @media (max-width: 900px) { .cart-layout { grid-template-columns: 1fr; } .cart-summary { position: static; } }
        @media (max-width: 767.98px) { .page-heading.header-text { padding: 96px 0 18px; } .page-heading.header-text h3 { font-size: 22px; } }
        @media (max-width: 650px) { .cart-item { align-items: start; gap: 10px; } .cart-item-main img { height: 70px; width: 76px; } .cart-item-main h2 { font-size: 14px; } .cart-item-price { font-size: 15px; grid-column: 1 / -1; } .cart-item-details form { max-width: calc(100vw - 32px); } .cart-actions { justify-content: flex-end; } .cart-toolbar { align-items: flex-start; flex-direction: column; } }
    </style>
</head>
<body>
    <x-storefront-header />
    <div class="page-heading header-text">
        <div class="container"><div class="row"><div class="col-lg-12">
            <h3>{{ __('store.cart_title') }}</h3>
            <span class="breadcrumb"><a href="{{ route('home') }}">{{ __('store.home') }}</a> &gt; {{ __('store.cart') }}</span>
        </div></div></div>
    </div>
    <main class="section">
        <div class="container">
            @if (session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
            @endif
            <div class="alert alert-danger" role="alert" data-cart-quantity-error hidden></div>
            <section>
                @if ($items->isEmpty())
                    <div class="cart-panel">
                        <h1 class="h3">{{ __('store.cart_title') }}</h1>
                        <p class="text-secondary my-4">{{ __('store.empty_cart') }}</p>
                        <a class="btn btn-primary rounded-pill" href="{{ route('shop') }}">{{ __('store.continue_shopping') }}</a>
                    </div>
                @else
                    <form id="cart-checkout-form" method="POST" action="{{ route('cart.checkout') }}">
                        @csrf
                        <input type="hidden" name="currency" value="{{ $currency }}">
                    </form>
                    @php($availableItems = $items->filter(fn (array $item): bool => $item['available']))
                    @php($selectedTotal = $availableItems->sum('displaySubtotal'))
                    @php($selectedProductCount = $availableItems->count())
                    <div class="cart-layout">
                        <div class="cart-items">
                            <h1 class="h3 mb-2">{{ __('store.cart_title') }}</h1>
                            <div class="cart-toolbar">
                                <label class="form-check d-flex align-items-center gap-2" for="cart-select-all">
                                    <input class="form-check-input mt-0" id="cart-select-all" type="checkbox" data-cart-select-all @checked($availableItems->isNotEmpty())>
                                    <span>{{ __('store.select_all_products') }}</span>
                                </label>
                                <div class="cart-toolbar-actions">
                                    <span class="text-secondary small" data-selected-count>{{ $selectedProductCount }}</span>
                                    <button class="btn btn-link text-danger btn-sm p-0" type="submit" form="cart-checkout-form" formaction="{{ route('cart.items.selected.destroy') }}" formmethod="POST" data-delete-selected>
                                        {{ __('store.remove_selected') }}
                                    </button>
                                </div>
                            </div>
                            @foreach ($items as $item)
                                @php($variant = $item['variant'])
                                <article class="cart-item">
                                    <div class="cart-item-main">
                                        <input
                                            class="form-check-input"
                                            form="cart-checkout-form"
                                            type="checkbox"
                                            name="items[{{ $variant?->id ?? $loop->index }}]"
                                            value="1"
                                            aria-label="{{ __('store.select_for_checkout') }}"
                                            data-cart-select
                                            data-subtotal="{{ $item['displaySubtotal'] }}"
                                            @checked($variant && $item['available'])
                                            @disabled(! $variant || ! $item['available'])
                                        >
                                        @if ($variant?->product?->image)
                                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($variant->product->image) }}" alt="{{ $variant->product->name }}">
                                        @else
                                            <img src="{{ asset('images/trending-01.jpg') }}" alt="">
                                        @endif
                                        <div>
                                            <h2>{{ $variant?->product?->name ?? __('store.unavailable') }}</h2>
                                            <span class="text-secondary">{{ $variant?->name ?? __('store.unavailable') }}</span>
                                            @unless ($item['available'])
                                                <div class="text-danger small">
                                                    {{ $item['robuxUnavailable'] ? __('store.robux_transactions_disabled') : __('store.unavailable') }}
                                                </div>
                                            @endunless
                                            @if ($item['details']['seller_note'] ?? false)
                                                <p class="cart-seller-note"><strong>{{ __('store.seller_note') }}:</strong> {{ $item['details']['seller_note'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="cart-item-price">
                                        <span class="cart-unit-price">{{ __('store.unit_price') }}:</span>
                                        {{ $item['currencySymbol'] }} {{ number_format($item['displayUnitPrice'], $item['currency'] === 'idr' ? 0 : 2, ',', '.') }}
                                        <span class="cart-item-subtotal">{{ __('store.subtotal') }}: {{ $item['currencySymbol'] }} {{ number_format($item['displaySubtotal'], $item['currency'] === 'idr' ? 0 : 2, ',', '.') }}</span>
                                    </div>
                                    <div class="cart-actions">
                                        @if ($variant && $item['available'])
                                            <form method="POST" action="{{ route('cart.items.update', $variant) }}" class="cart-quantity-form" data-cart-quantity-form>
                                                @csrf
                                                @method('PATCH')
                                                <button class="cart-quantity-step" type="button" data-quantity-decrease aria-label="{{ __('store.quantity_decrease') }}">−</button>
                                                <label class="visually-hidden" for="quantity-{{ $variant->id }}">{{ __('store.quantity') }}</label>
                                                <input class="cart-quantity" id="quantity-{{ $variant->id }}" type="number" name="quantity" min="{{ $variant->minimum_quantity }}" max="{{ min($variant->maximum_quantity, $variant->stock) }}" value="{{ $item['quantity'] }}" data-saved-quantity="{{ $item['quantity'] }}" data-unit-price="{{ $item['displayUnitPrice'] }}" data-stock="{{ $variant->stock }}" data-stock-error-label="{{ __('store.stock_not_enough') }}" required>
                                                <button class="cart-quantity-step" type="button" data-quantity-increase aria-label="{{ __('store.quantity_increase') }}">+</button>
                                            </form>
                                        @endif
                                        @if ($variant)
                                            @php($sellerNote = $item['details']['seller_note'] ?? '')
                                            <details class="cart-item-details">
                                                <summary>{{ __('store.purchase_details') }}</summary>
                                                <form method="POST" action="{{ route('cart.items.details.update', $variant) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    @foreach ($item['purchaseFields'] as $field)
                                                        @php($savedFieldValue = $item['details']['purchase_details'][$field['key']] ?? $item['details'][$field['key']] ?? '')
                                                        @php($hasSavedFieldValue = filled($savedFieldValue))
                                                        <div class="mb-3">
                                                            <label class="form-label" for="purchase-field-{{ $variant->id }}-{{ $field['key'] }}">{{ $field['label'] }}</label>
                                                            <input class="form-control" id="purchase-field-{{ $variant->id }}-{{ $field['key'] }}" name="purchase_details[{{ $field['key'] }}]" type="{{ $field['type'] }}" autocomplete="{{ $field['type'] === 'password' ? 'new-password' : 'off' }}" value="{{ $field['type'] === 'password' ? '' : $savedFieldValue }}" maxlength="255" {{ $field['type'] !== 'password' || ! $hasSavedFieldValue ? 'required' : '' }}>
                                                            @if ($field['type'] === 'password')
                                                                <small class="form-text">{{ ! $hasSavedFieldValue ? __('store.password_missing') : __('store.password_unchanged_help') }}</small>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                    <div class="mb-3">
                                                        <label class="form-label" for="seller-note-{{ $variant->id }}">{{ __('store.seller_note') }}</label>
                                                        <textarea class="form-control" id="seller-note-{{ $variant->id }}" name="seller_note" rows="2" maxlength="1000">{{ $sellerNote }}</textarea>
                                                    </div>
                                                    <button class="btn btn-outline-primary btn-sm" type="submit">{{ __('store.save_details') }}</button>
                                                </form>
                                            </details>
                                        @endif
                                        <form method="POST" action="{{ route('cart.items.destroy', $variant?->id ?? 0) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-link text-danger btn-sm" type="submit">{{ __('store.remove') }}</button>
                                        </form>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                        <aside class="cart-summary">
                            <h2 class="cart-summary-title">{{ __('store.cart_summary') }}</h2>
                            <div class="cart-summary-total">
                                <span>{{ __('store.cart_total') }} (<span data-selected-item-label>{{ $selectedProductCount }}</span>)</span>
                                <strong><span data-selected-total>{{ $currencySymbol }} {{ number_format((float) $selectedTotal, $currency === 'idr' ? 0 : 2, ',', '.') }}</span></strong>
                            </div>
                            <div class="cart-summary-bill">
                                <span>{{ __('store.total') }}</span>
                                <strong><span data-selected-total-copy>{{ $currencySymbol }} {{ number_format((float) $selectedTotal, $currency === 'idr' ? 0 : 2, ',', '.') }}</span></strong>
                            </div>
                            @if ($hasUnavailableItems)
                                <p class="text-danger small">{{ __('store.cart_item_unavailable') }}</p>
                            @endif
                            <button class="btn btn-checkout w-100" type="submit" form="cart-checkout-form" data-checkout-button>
                                {{ __('store.buy_selected') }} (<span data-selected-item-label>{{ $selectedProductCount }}</span>)
                            </button>
                            <a class="d-block text-center mt-3" href="{{ route('shop') }}">{{ __('store.continue_shopping') }}</a>
                        </aside>
                    </div>
                @endif
            </section>
        </div>
    </main>
    <footer><div class="container"><div class="col-lg-12"><p>Copyright © {{ date('Y') }} Meoxstore. All rights reserved.</p></div></div></footer>
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
    <script>
        (() => {
            const checkboxes = [...document.querySelectorAll('[data-cart-select]')];
            const selectAll = document.querySelector('[data-cart-select-all]');
            const countNodes = document.querySelectorAll('[data-selected-item-label]');
            const countDisplay = document.querySelector('[data-selected-count]');
            const deleteButton = document.querySelector('[data-delete-selected]');
            const checkoutButton = document.querySelector('[data-checkout-button]');
            const totalNodes = document.querySelectorAll('[data-selected-total], [data-selected-total-copy]');
            const currency = @js($currency);
            const currencySymbol = @js($currencySymbol);
            const decimals = currency === 'idr' ? 0 : 2;
            let cartQuantityUpdateQueue = Promise.resolve();

            const formatPrice = (value) => {
                const [integer, fraction] = Number(value).toFixed(decimals).split('.');
                const formattedInteger = integer.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                return fraction ? `${formattedInteger},${fraction}` : formattedInteger;
            };

            const updateSelection = () => {
                const selected = checkboxes.filter((checkbox) => checkbox.checked);
                const selectedCount = selected.length;
                const selectedTotal = selected.reduce((total, checkbox) => total + Number(checkbox.dataset.subtotal), 0);

                countNodes.forEach((node) => { node.textContent = selectedCount; });
                if (countDisplay) {
                    countDisplay.textContent = selectedCount;
                }
                totalNodes.forEach((node) => { node.textContent = `${currencySymbol} ${formatPrice(selectedTotal)}`; });
                if (selectAll) {
                    const available = checkboxes.filter((checkbox) => !checkbox.disabled);
                    selectAll.checked = available.length > 0 && available.every((checkbox) => checkbox.checked);
                    selectAll.indeterminate = available.some((checkbox) => checkbox.checked) && !selectAll.checked;
                }
                if (deleteButton) {
                    deleteButton.disabled = selected.length === 0;
                }
                if (checkoutButton) {
                    checkoutButton.disabled = selected.length === 0;
                }
            };

            checkboxes.forEach((checkbox) => checkbox.addEventListener('change', updateSelection));
            selectAll?.addEventListener('change', () => {
                checkboxes.filter((checkbox) => !checkbox.disabled).forEach((checkbox) => { checkbox.checked = selectAll.checked; });
                updateSelection();
            });
            document.querySelectorAll('[data-cart-quantity-form]').forEach((form) => {
                const quantityInput = form.querySelector('input[name="quantity"]');
                const item = form.closest('.cart-item');
                const checkbox = item?.querySelector('[data-cart-select]');
                const subtotalNode = item?.querySelector('.cart-item-subtotal');
                const unitPrice = Number(quantityInput.dataset.unitPrice);
                let saveTimer;
                let saving = false;
                let saveAgain = false;
                let changeVersion = 0;

                const updateQuantityUi = () => {
                    const quantity = Number(quantityInput.value);
                    const subtotal = unitPrice * quantity;
                    if (checkbox) {
                        checkbox.dataset.subtotal = String(subtotal);
                    }
                    if (subtotalNode) {
                        subtotalNode.textContent = `{{ __('store.subtotal') }}: ${currencySymbol} ${formatPrice(subtotal)}`;
                    }
                    form.querySelector('[data-quantity-decrease]').disabled = quantity <= Number(quantityInput.min);
                    form.querySelector('[data-quantity-increase]').disabled = quantity >= Number(quantityInput.max);
                    updateSelection();
                };

                const saveQuantity = async () => {
                    if (saving) {
                        saveAgain = true;
                        return;
                    }
                    if (!form.reportValidity()) {
                        quantityInput.value = quantityInput.dataset.savedQuantity;
                        updateQuantityUi();
                        return;
                    }

                    saving = true;
                    const requestVersion = changeVersion;
                    const body = new FormData(form);
                    const updateRequest = cartQuantityUpdateQueue.then(async () => {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body,
                            credentials: 'same-origin',
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });
                        const result = await response.json();
                        if (!response.ok) {
                            throw new Error(result.errors?.quantity?.[0] || result.message || '{{ __('store.cart_update_error') }}');
                        }

                        return result;
                    });
                    cartQuantityUpdateQueue = updateRequest.then(() => undefined, () => undefined);
                    try {
                        const result = await updateRequest;
                        quantityInput.dataset.savedQuantity = String(result.quantity);
                        if (changeVersion === requestVersion) {
                            quantityInput.value = String(result.quantity);
                        }
                        document.querySelector('[data-cart-quantity-error]')?.setAttribute('hidden', '');
                    } catch (error) {
                        if (changeVersion === requestVersion) {
                            quantityInput.value = quantityInput.dataset.savedQuantity;
                            updateQuantityUi();
                        }
                        const notice = document.querySelector('[data-cart-quantity-error]');
                        if (notice) {
                            notice.textContent = error.message || '{{ __('store.cart_update_error') }}';
                            notice.hidden = false;
                        }
                    } finally {
                        saving = false;
                        if (saveAgain || changeVersion !== requestVersion) {
                            saveAgain = false;
                            window.clearTimeout(saveTimer);
                            saveTimer = window.setTimeout(saveQuantity, 0);
                        }
                    }
                };

                const scheduleSave = () => {
                    changeVersion += 1;
                    updateQuantityUi();
                    window.clearTimeout(saveTimer);
                    saveTimer = window.setTimeout(saveQuantity, 250);
                };

                form.querySelector('[data-quantity-decrease]').addEventListener('click', () => {
                    quantityInput.value = String(Math.max(Number(quantityInput.min), Number(quantityInput.value) - 1));
                    scheduleSave();
                });
                form.querySelector('[data-quantity-increase]').addEventListener('click', () => {
                    quantityInput.value = String(Math.min(Number(quantityInput.max), Number(quantityInput.value) + 1));
                    scheduleSave();
                });
                quantityInput.addEventListener('input', () => {
                    scheduleSave();
                    document.querySelector('[data-cart-quantity-error]')?.setAttribute('hidden', '');
                });
                quantityInput.addEventListener('change', () => {
                    window.clearTimeout(saveTimer);
                    saveTimer = window.setTimeout(saveQuantity, 0);
                });
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    window.clearTimeout(saveTimer);
                    saveQuantity();
                });
                updateQuantityUi();
            });
            updateSelection();
        })();
    </script>
</body>
</html>
