@extends('admin.layout')

@section('title', $title)

@section('content')
    @php
        $variantRows = old('variants');
        if (!is_array($variantRows) || $variantRows === []) {
            $variantRows = $product->exists
                ? $product->variants->map(fn ($variant) => $variant->only(['id', 'name', 'price', 'bobux_amount', 'stock', 'minimum_quantity', 'maximum_quantity', 'status', 'sort_order']))->all()
                : [['name' => 'Default', 'price' => '0.00', 'stock' => 0, 'minimum_quantity' => 1, 'maximum_quantity' => 0, 'status' => 'active', 'sort_order' => 0]];
        }
        $variantRows = array_map(fn ($variant) => is_array($variant) ? $variant : [], $variantRows);
        $nextVariantIndex = max(array_map('intval', array_keys($variantRows))) + 1;
        $purchaseFieldRows = old('purchase_fields', $product->purchaseFields());
        $purchaseFieldRows = is_array($purchaseFieldRows) ? array_values($purchaseFieldRows) : [];
        $nextPurchaseFieldIndex = count($purchaseFieldRows);
        $selectedGameCategoryId = (string) old('game_category_id', $product->game_category_id ?? '');
        $selectedItemCategoryId = (string) old('item_category_id', $product->item_category_id ?? '');
        $selectedItemCategory = $itemCategories->firstWhere('id', (int) $selectedItemCategoryId);
        $bobuxType = $selectedItemCategory?->bobux_type
            ?? ($selectedItemCategory?->uses_robux ? 'gift_ingame' : null);
        $usesBobuxRate = in_array($bobuxType, ['gift_ingame', 'gamepass_5_days'], true);
        $bobuxRate = $bobuxType === 'gamepass_5_days'
            ? (float) $currencyRates->idr_per_gamepass_bobux
            : (float) $currencyRates->idr_per_robux;
        $legacyBadges = $product->badges ?? array_values(array_filter([
            $product->is_featured ? 'trending' : null,
            $product->hasDiscount() ? 'discount' : null,
        ]));
        $selectedBadges = old('badges', $legacyBadges);
        $selectedBadges = is_array($selectedBadges) ? $selectedBadges : [];
    @endphp
    <div class="page-heading">
        <div><h1>{{ $title }}</h1></div>
        <a class="btn btn-light border" href="{{ route('admin.products.index') }}">Back to products</a>
    </div>

    <form class="d-grid gap-3" method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
        @csrf
        @if ($product->exists)
            @method('PUT')
        @endif
        <section class="panel p-4">
            <h2 class="fs-6 fw-semibold mb-3">Product details</h2>
            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    <label class="form-label" for="name">Name</label>
                    <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required maxlength="255">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="game_category_id">Game category</label>
                    <select class="form-select @error('game_category_id') is-invalid @enderror" id="game_category_id" name="game_category_id" required>
                        <option value="">Choose a game</option>
                        @foreach ($gameCategories as $category)
                            <option value="{{ $category->id }}" @selected($selectedGameCategoryId === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <details class="category-inline-create" data-inline-category data-category-type="game" data-endpoint="{{ route('admin.game-categories.store') }}" data-select="game_category_id">
                        <summary>Add a game category</summary>
                        <div class="d-flex gap-2 mt-2">
                            <input class="form-control form-control-sm" type="text" maxlength="255" placeholder="Category name" data-category-name>
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-create-category>Add</button>
                        </div>
                        <span class="category-inline-message small d-block mt-1" data-category-feedback role="status"></span>
                    </details>
                    @error('game_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="discount_percent">Product discount (%)</label>
                    <input class="form-control @error('discount_percent') is-invalid @enderror" id="discount_percent" name="discount_percent" type="number" min="0.01" max="99.99" step="0.01" value="{{ old('discount_percent', $product->discount_percent) }}" placeholder="e.g. 15" @disabled(! in_array('discount', $selectedBadges, true))>
                    @error('discount_percent')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <fieldset>
                        <legend class="form-label">Product badges</legend>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach (\App\Models\Product::BADGE_OPTIONS as $badge)
                                <div class="form-check">
                                    <input class="form-check-input" id="badge-{{ $badge }}" name="badges[]" type="checkbox" value="{{ $badge }}" @checked(in_array($badge, $selectedBadges, true)) data-product-badge>
                                    <label class="form-check-label" for="badge-{{ $badge }}">{{ __('store.badge_'.$badge) }}</label>
                                </div>
                            @endforeach
                        </div>
                        @error('badges')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        @foreach ($errors->get('badges.*') as $badgeErrors)
                            @foreach ($badgeErrors as $message)
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @endforeach
                        @endforeach
                        <div class="form-text">Choose one or more badges. Enable the discount badge and enter its percentage to reduce all variant prices.</div>
                    </fieldset>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="item_category_id">Item category</label>
                    <select class="form-select @error('item_category_id') is-invalid @enderror" id="item_category_id" name="item_category_id" @disabled($itemCategories->isEmpty()) required>
                        <option value="">{{ $selectedGameCategoryId === '' ? 'Choose a game first' : ($itemCategories->isEmpty() ? 'No item categories for this game' : 'Choose an item type') }}</option>
                        @foreach ($itemCategories as $category)
                            <option value="{{ $category->id }}" data-bobux-type="{{ $category->bobux_type ?? ($category->uses_robux ? 'gift_ingame' : '') }}" @selected($selectedItemCategoryId === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <details class="category-inline-create" data-inline-category data-category-type="item" data-endpoint="{{ route('admin.item-categories.store') }}" data-select="item_category_id" data-game-select="game_category_id" data-endpoint-template="{{ route('admin.game-categories.item-categories', ['game_category' => '__GAME_CATEGORY_ID__']) }}">
                        <summary>Add an item category</summary>
                        <div class="d-flex gap-2 mt-2">
                            <input class="form-control form-control-sm" type="text" maxlength="255" placeholder="Category name" data-category-name>
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-create-category @disabled($selectedGameCategoryId === '')>Add</button>
                        </div>
                        <label class="form-label small mt-2" for="inline-bobux-type">Bobux pricing type</label>
                        <select class="form-select form-select-sm" id="inline-bobux-type" data-category-bobux-type>
                            <option value="">Regular product (IDR)</option>
                            <option value="gift_ingame">Bobux gift in-game</option>
                            <option value="gamepass_5_days">Bobux Gamepass 5 days</option>
                            <option value="login">Bobux via login (manual amount)</option>
                        </select>
                        <span class="category-inline-message small d-block mt-1" data-category-feedback role="status"></span>
                    </details>
                    @error('item_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label" for="description">Description <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <div class="align-items-start d-flex flex-wrap justify-content-between gap-2 mb-2">
                        <div>
                            <h3 class="fs-6 fw-semibold mb-1">Account information form</h3>
                            <p class="form-text mb-0">Add the account fields customers must fill in. Email for customer contact remains required separately.</p>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" id="add-purchase-field" type="button">Add account field</button>
                    </div>
                    <div class="d-grid gap-2" id="purchase-field-list">
                        @foreach ($purchaseFieldRows as $index => $field)
                            <div class="variant-row" data-purchase-field-row>
                                <div class="row g-3 align-items-end">
                                    <div class="col-12 col-md-3">
                                        <label class="form-label" for="purchase-field-key-{{ $index }}">Field key</label>
                                        <input class="form-control" id="purchase-field-key-{{ $index }}" name="purchase_fields[{{ $index }}][key]" value="{{ $field['key'] ?? '' }}" pattern="[a-z][a-z0-9_]*" maxlength="50" required>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label" for="purchase-field-label-{{ $index }}">Customer label</label>
                                        <input class="form-control" id="purchase-field-label-{{ $index }}" name="purchase_fields[{{ $index }}][label]" value="{{ $field['label'] ?? '' }}" maxlength="100" required>
                                    </div>
                                    <div class="col-8 col-md-3">
                                        <label class="form-label" for="purchase-field-type-{{ $index }}">Input type</label>
                                        <select class="form-select" id="purchase-field-type-{{ $index }}" name="purchase_fields[{{ $index }}][type]" required>
                                            @foreach (['text' => 'Text', 'email' => 'Email', 'password' => 'Password'] as $type => $typeLabel)
                                                <option value="{{ $type }}" @selected(($field['type'] ?? 'text') === $type)>{{ $typeLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-4 col-md-2">
                                        <button class="btn btn-outline-danger w-100" type="button" data-remove-purchase-field>Remove</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('purchase_fields')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                    @foreach ($errors->get('purchase_fields.*') as $fieldErrors)
                        @foreach ($fieldErrors as $message)
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @endforeach
                    @endforeach
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="image">Product image <span class="text-muted">(optional, JPG/PNG/WebP, up to 4 MB)</span></label>
                    <input class="form-control @error('image') is-invalid @enderror" id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if ($product->image)
                        <img class="mt-2 product-thumb" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) }}" alt="Current {{ $product->name }} image">
                    @endif
                </div>
                @if ($product->exists)
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            @foreach (['draft' => 'Draft', 'active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $product->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @else
                    <div class="col-12 col-md-3">
                        <label class="form-label">Status</label>
                        <div class="form-control-plaintext">Active automatically</div>
                    </div>
                @endif
                <div class="col-12 col-md-3 d-flex align-items-end pb-2">
                    <div class="form-check">
                        <input class="form-check-input" id="is_featured" name="is_featured" type="checkbox" value="1" @checked(old('is_featured', $product->is_featured))>
                        <label class="form-check-label" for="is_featured">Featured product</label>
                    </div>
                </div>
            </div>
        </section>

        <section class="panel p-4">
            <div class="align-items-start d-flex flex-wrap justify-content-between gap-2 mb-3">
                <div><h2 class="fs-6 fw-semibold mb-0">Variants</h2></div>
                <button class="btn btn-outline-secondary" id="add-variant" type="button">Add variant</button>
            </div>
            @error('variants')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror
            <div class="d-grid gap-3" id="variant-list">
                @foreach ($variantRows as $index => $variant)
                    <div class="variant-row" data-variant-row>
                        @if (!empty($variant['id']))<input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant['id'] }}">@endif
                        <div class="align-items-center d-flex justify-content-between mb-3">
                            <span class="fw-semibold">Variant <span data-variant-number>{{ $loop->iteration }}</span></span>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-light border" type="button" data-move-up aria-label="Move variant up" title="Move up">Up</button>
                                <button class="btn btn-sm btn-light border" type="button" data-move-down aria-label="Move variant down" title="Move down">Down</button>
                                <button class="btn btn-sm btn-outline-danger" type="button" data-remove-variant>Remove</button>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-4"><label class="form-label">Variant name</label><input class="form-control" name="variants[{{ $index }}][name]" value="{{ $variant['name'] ?? '' }}" required></div>
                            @php($variantPrice = (float) ($variant['price'] ?? 0))
                            @php($variantIdrPrice = $usesBobuxRate ? $variantPrice * $bobuxRate : $variantPrice)
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="variant-price-{{ $index }}">Price ({{ $usesBobuxRate ? 'Bobux' : 'IDR' }})</label>
                                <div data-price-group data-bobux-type="{{ $bobuxType }}">
                                    <input class="form-control" id="variant-price-{{ $index }}" type="text" inputmode="decimal" autocomplete="off" value="{{ rtrim(rtrim(number_format($variantPrice, 2, '.', ','), '0'), '.') }}" data-price-display required>
                                    <input name="variants[{{ $index }}][price]" type="hidden" value="{{ number_format($variantPrice, 2, '.', '') }}" data-price-value>
                                    <div class="currency-preview" data-price-preview>
                                        <span data-price-idr>Rp {{ rtrim(rtrim(number_format($variantIdrPrice, 2, '.', ','), '0'), '.') }}</span>
                                        <span data-price-myr>≈ RM {{ number_format($variantIdrPrice / $currencyRates->idr_per_myr, 2) }}</span>
                                        <span data-price-usd>≈ ${{ number_format($variantIdrPrice / $currencyRates->idr_per_usd, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4" data-bobux-amount-field @if($bobuxType !== 'login') hidden @endif>
                                <label class="form-label" for="variant-bobux-amount-{{ $index }}">Bobux amount</label>
                                <input class="form-control" id="variant-bobux-amount-{{ $index }}" name="variants[{{ $index }}][bobux_amount]" type="number" min="1" step="1" value="{{ $variant['bobux_amount'] ?? '' }}" @required($bobuxType === 'login') @disabled($bobuxType !== 'login') data-bobux-amount>
                            </div>
                            <div class="col-6 col-md-2"><label class="form-label">Stock</label><input class="form-control" name="variants[{{ $index }}][stock]" type="number" min="0" step="1" value="{{ $variant['stock'] ?? 0 }}" data-stock-input required></div>
                            <div class="col-6 col-md-2"><label class="form-label">Minimum qty</label><input class="form-control" name="variants[{{ $index }}][minimum_quantity]" type="number" min="1" step="1" value="{{ $variant['minimum_quantity'] ?? 1 }}" required></div>
                            <div class="col-6 col-md-2"><label class="form-label">Maximum qty</label><input class="form-control" name="variants[{{ $index }}][maximum_quantity]" type="number" min="0" step="1" value="{{ $variant['stock'] ?? 0 }}" data-maximum-quantity readonly required></div>
                            <div class="col-6 col-md-2"><label class="form-label">Status</label><select class="form-select" name="variants[{{ $index }}][status]" required><option value="active" @selected(($variant['status'] ?? 'active') === 'active')>Active</option><option value="inactive" @selected(($variant['status'] ?? 'active') === 'inactive')>Inactive</option></select></div>
                            <div class="col-6 col-md-2"><label class="form-label">Sort order</label><input class="form-control" name="variants[{{ $index }}][sort_order]" type="number" min="0" step="1" value="{{ $variant['sort_order'] ?? $loop->index }}" required data-sort-order></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="d-flex gap-2">
            <button class="btn btn-accent" type="submit">{{ $product->exists ? 'Save product' : 'Create product' }}</button>
            <a class="btn btn-light border" href="{{ route('admin.products.index') }}">Cancel</a>
        </div>
    </form>

    <template id="variant-template">
        <div class="variant-row" data-variant-row>
            <div class="align-items-center d-flex justify-content-between mb-3">
                <span class="fw-semibold">Variant <span data-variant-number></span></span>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-light border" type="button" data-move-up aria-label="Move variant up" title="Move up">Up</button>
                    <button class="btn btn-sm btn-light border" type="button" data-move-down aria-label="Move variant down" title="Move down">Down</button>
                    <button class="btn btn-sm btn-outline-danger" type="button" data-remove-variant>Remove</button>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-12 col-md-4"><label class="form-label">Variant name</label><input class="form-control" name="variants[__INDEX__][name]" value="" required></div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="variant-price-__INDEX__">Price ({{ $usesBobuxRate ? 'Bobux' : 'IDR' }})</label>
                    <div data-price-group data-bobux-type="{{ $bobuxType }}">
                        <input class="form-control" id="variant-price-__INDEX__" type="text" inputmode="decimal" autocomplete="off" value="0" data-price-display required>
                        <input name="variants[__INDEX__][price]" type="hidden" value="0.00" data-price-value>
                        <div class="currency-preview" data-price-preview><span data-price-idr>Rp 0</span><span data-price-myr>≈ RM 0.00</span><span data-price-usd>≈ $0.00</span></div>
                    </div>
                </div>
                <div class="col-12 col-md-4" data-bobux-amount-field @if($bobuxType !== 'login') hidden @endif>
                    <label class="form-label" for="variant-bobux-amount-__INDEX__">Bobux amount</label>
                    <input class="form-control" id="variant-bobux-amount-__INDEX__" name="variants[__INDEX__][bobux_amount]" type="number" min="1" step="1" value="" @required($bobuxType === 'login') @disabled($bobuxType !== 'login') data-bobux-amount>
                </div>
                <div class="col-6 col-md-2"><label class="form-label">Stock</label><input class="form-control" name="variants[__INDEX__][stock]" type="number" min="0" step="1" value="0" data-stock-input required></div>
                <div class="col-6 col-md-2"><label class="form-label">Minimum qty</label><input class="form-control" name="variants[__INDEX__][minimum_quantity]" type="number" min="1" step="1" value="1" required></div>
                <div class="col-6 col-md-2"><label class="form-label">Maximum qty</label><input class="form-control" name="variants[__INDEX__][maximum_quantity]" type="number" min="0" step="1" value="0" data-maximum-quantity readonly required></div>
                <div class="col-6 col-md-2"><label class="form-label">Status</label><select class="form-select" name="variants[__INDEX__][status]" required><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                <div class="col-6 col-md-2"><label class="form-label">Sort order</label><input class="form-control" name="variants[__INDEX__][sort_order]" type="number" min="0" step="1" value="0" required data-sort-order></div>
            </div>
        </div>
    </template>

    <template id="purchase-field-template">
        <div class="variant-row" data-purchase-field-row>
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label" for="purchase-field-key-__INDEX__">Field key</label>
                    <input class="form-control" id="purchase-field-key-__INDEX__" name="purchase_fields[__INDEX__][key]" pattern="[a-z][a-z0-9_]*" maxlength="50" required>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="purchase-field-label-__INDEX__">Customer label</label>
                    <input class="form-control" id="purchase-field-label-__INDEX__" name="purchase_fields[__INDEX__][label]" maxlength="100" required>
                </div>
                <div class="col-8 col-md-3">
                    <label class="form-label" for="purchase-field-type-__INDEX__">Input type</label>
                    <select class="form-select" id="purchase-field-type-__INDEX__" name="purchase_fields[__INDEX__][type]" required>
                        <option value="text">Text</option>
                        <option value="email">Email</option>
                        <option value="password">Password</option>
                    </select>
                </div>
                <div class="col-4 col-md-2">
                    <button class="btn btn-outline-danger w-100" type="button" data-remove-purchase-field>Remove</button>
                </div>
            </div>
        </div>
    </template>

    <script>
        (() => {
            const discountBadge = document.getElementById('badge-discount');
            const discountInput = document.getElementById('discount_percent');
            discountBadge.addEventListener('change', () => {
                discountInput.disabled = !discountBadge.checked;
                if (!discountBadge.checked) {
                    discountInput.value = '';
                }
            });

            const list = document.getElementById('variant-list');
            const template = document.getElementById('variant-template');
            let nextIndex = {{ $nextVariantIndex }};

            const refreshRows = (resetOrder = false) => {
                const rows = [...list.querySelectorAll('[data-variant-row]')];
                rows.forEach((row, position) => {
                    row.querySelector('[data-variant-number]').textContent = position + 1;
                    if (resetOrder) row.querySelector('[data-sort-order]').value = position;
                    row.querySelector('[data-move-up]').disabled = position === 0;
                    row.querySelector('[data-move-down]').disabled = position === rows.length - 1;
                    row.querySelector('[data-remove-variant]').disabled = rows.length === 1;
                });
            };

            document.getElementById('add-variant').addEventListener('click', () => {
                const markup = template.innerHTML.replaceAll('__INDEX__', nextIndex++);
                list.insertAdjacentHTML('beforeend', markup);
                refreshRows(true);
                updateBobuxPricing();
            });

            list.addEventListener('click', (event) => {
                const row = event.target.closest('[data-variant-row]');
                if (!row) return;

                if (event.target.closest('[data-remove-variant]') && list.querySelectorAll('[data-variant-row]').length > 1) {
                    row.remove();
                } else if (event.target.closest('[data-move-up]') && row.previousElementSibling) {
                    list.insertBefore(row, row.previousElementSibling);
                } else if (event.target.closest('[data-move-down]') && row.nextElementSibling) {
                    list.insertBefore(row.nextElementSibling, row);
                }

                refreshRows(true);
            });

            const purchaseFieldList = document.getElementById('purchase-field-list');
            const purchaseFieldTemplate = document.getElementById('purchase-field-template');
            const addPurchaseFieldButton = document.getElementById('add-purchase-field');
            let nextPurchaseFieldIndex = {{ $nextPurchaseFieldIndex }};
            const refreshPurchaseFieldControls = () => {
                addPurchaseFieldButton.disabled = purchaseFieldList.querySelectorAll('[data-purchase-field-row]').length >= 10;
            };

            addPurchaseFieldButton.addEventListener('click', () => {
                if (purchaseFieldList.querySelectorAll('[data-purchase-field-row]').length >= 10) {
                    return;
                }

                purchaseFieldList.insertAdjacentHTML(
                    'beforeend',
                    purchaseFieldTemplate.innerHTML.replaceAll('__INDEX__', nextPurchaseFieldIndex++),
                );
                refreshPurchaseFieldControls();
            });

            purchaseFieldList.addEventListener('click', (event) => {
                if (event.target.closest('[data-remove-purchase-field]')) {
                    event.target.closest('[data-purchase-field-row]').remove();
                    refreshPurchaseFieldControls();
                }
            });
            refreshPurchaseFieldControls();

            refreshRows();
            const csrfToken = document.querySelector('input[name="_token"]').value;
            const gameCategorySelect = document.getElementById('game_category_id');
            const itemCategorySelect = document.getElementById('item_category_id');
            const itemCategoryEditor = document.querySelector('[data-category-type="item"]');
            const addItemCategoryButton = itemCategoryEditor.querySelector('[data-create-category]');
            const updateBobuxPricing = () => {
                const bobuxType = itemCategorySelect.selectedOptions[0]?.dataset.bobuxType || '';
                const usesBobuxRate = ['gift_ingame', 'gamepass_5_days'].includes(bobuxType);
                const isLoginBobux = bobuxType === 'login';

                document.querySelectorAll('[data-price-group]').forEach((group) => {
                    group.dataset.bobuxType = bobuxType;
                    const label = group.closest('.col-12')?.querySelector('label.form-label');
                    if (label) label.textContent = `Price (${usesBobuxRate ? 'Bobux' : 'IDR'})`;
                    group.querySelector('[data-price-display]').dispatchEvent(new Event('input', { bubbles: true }));

                    const amountField = group.closest('[data-variant-row]')?.querySelector('[data-bobux-amount-field]');
                    const amountInput = amountField?.querySelector('[data-bobux-amount]');
                    if (amountField && amountInput) {
                        amountField.hidden = !isLoginBobux;
                        amountInput.disabled = !isLoginBobux;
                        amountInput.required = isLoginBobux;
                    }
                });
            };
            const originalGameCategoryId = @json($selectedGameCategoryId);
            const originalItemCategoryId = @json($selectedItemCategoryId);
            let itemCategoryRequest = 0;

            const loadItemCategories = async () => {
                const gameCategoryId = gameCategorySelect.value;
                const requestId = ++itemCategoryRequest;
                addItemCategoryButton.disabled = true;
                itemCategorySelect.disabled = true;
                itemCategorySelect.replaceChildren(new Option(
                    gameCategoryId ? 'Loading item categories...' : 'Choose a game first',
                    '',
                ));

                if (!gameCategoryId) {
                    return;
                }

                const endpoint = itemCategoryEditor.dataset.endpointTemplate
                    .replace('__GAME_CATEGORY_ID__', encodeURIComponent(gameCategoryId));
                const query = new URLSearchParams();

                if (gameCategoryId === originalGameCategoryId && originalItemCategoryId) {
                    query.set('selected_item_category_id', originalItemCategoryId);
                }

                try {
                    const response = await fetch(`${endpoint}?${query}`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    const categories = await response.json();

                    if (!response.ok || requestId !== itemCategoryRequest) {
                        return;
                    }

                    itemCategorySelect.replaceChildren(new Option(
                        categories.length ? 'Choose an item type' : 'No item categories for this game',
                        '',
                    ));

                    categories.forEach((category) => {
                        const selected = category.id.toString() === originalItemCategoryId
                            && gameCategoryId === originalGameCategoryId;
                        const option = new Option(category.name, category.id, selected, selected);
                        option.dataset.bobuxType = category.bobux_type || (category.uses_robux ? 'gift_ingame' : '');
                        itemCategorySelect.add(option);
                    });

                    itemCategorySelect.disabled = false;
                    addItemCategoryButton.disabled = false;
                    updateBobuxPricing();
                } catch {
                    if (requestId === itemCategoryRequest) {
                        itemCategorySelect.replaceChildren(new Option('Could not load item categories', ''));
                    }
                }
            };

            gameCategorySelect.addEventListener('change', loadItemCategories);
            itemCategorySelect.addEventListener('change', updateBobuxPricing);

            document.querySelectorAll('[data-inline-category]').forEach((categoryEditor) => {
                const nameInput = categoryEditor.querySelector('[data-category-name]');
                const createButton = categoryEditor.querySelector('[data-create-category]');
                const feedback = categoryEditor.querySelector('[data-category-feedback]');
                const categorySelect = document.getElementById(categoryEditor.dataset.select);
                const bobuxTypeInput = categoryEditor.querySelector('[data-category-bobux-type]');

                const createCategory = async () => {
                    const name = nameInput.value.trim();
                    const gameCategoryId = categoryEditor.dataset.gameSelect
                        ? document.getElementById(categoryEditor.dataset.gameSelect).value
                        : null;

                    if (categoryEditor.dataset.categoryType === 'item' && !gameCategoryId) {
                        feedback.textContent = 'Choose a game category first.';
                        feedback.className = 'category-inline-message small d-block mt-1 error';
                        return;
                    }

                    if (!name) {
                        feedback.textContent = 'Enter a category name.';
                        feedback.className = 'category-inline-message small d-block mt-1 error';
                        nameInput.focus();
                        return;
                    }

                    createButton.disabled = true;
                    feedback.textContent = '';

                    try {
                        const response = await fetch(categoryEditor.dataset.endpoint, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: new URLSearchParams({
                                name,
                                ...(bobuxTypeInput?.value ? { bobux_type: bobuxTypeInput.value } : {}),
                                ...(gameCategoryId ? { game_category_id: gameCategoryId } : {}),
                            }),
                        });
                        const result = await response.json();

                        if (!response.ok) {
                            feedback.textContent = Object.values(result.errors ?? {}).flat()[0] ?? 'Could not add category.';
                            feedback.className = 'category-inline-message small d-block mt-1 error';
                            return;
                        }

                        const option = new Option(result.name, result.id, true, true);
                        option.dataset.bobuxType = result.bobux_type || '';
                        categorySelect.add(option);
                        categorySelect.value = result.id;
                        if (categoryEditor.dataset.categoryType === 'item') {
                            updateBobuxPricing();
                            if (bobuxTypeInput) bobuxTypeInput.value = '';
                        }
                        if (categoryEditor.dataset.categoryType === 'game') {
                            categorySelect.dispatchEvent(new Event('change'));
                        }
                        nameInput.value = '';
                        categoryEditor.open = false;
                        feedback.textContent = 'Category added.';
                        feedback.className = 'category-inline-message small d-block mt-1 success';
                    } catch {
                        feedback.textContent = 'Could not add category.';
                        feedback.className = 'category-inline-message small d-block mt-1 error';
                    } finally {
                        createButton.disabled = false;
                    }
                };

                createButton.addEventListener('click', createCategory);
                nameInput.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        createCategory();
                    }
                });
            });
        })();
    </script>
    @include('admin.products._price-behavior')
@endsection