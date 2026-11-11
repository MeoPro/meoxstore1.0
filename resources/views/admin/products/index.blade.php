@extends('admin.layout')

@section('title', 'Products')

@section('content')
    <div class="page-heading">
        <div><h1>Products</h1></div>
        <a class="btn btn-accent" href="{{ route('admin.products.create') }}">Add product</a>
    </div>

    <form class="panel mb-3 p-3" method="GET" action="{{ route('admin.products.index') }}">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label small mb-1" for="search">Search</label>
                <input class="form-control" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Product name">
            </div>
            <div class="col-6 col-xl-2">
                <label class="form-label small mb-1" for="game-category">Game category</label>
                <select class="form-select" id="game-category" name="game_category_id" data-game-filter>
                    <option value="">All games</option>
                    @foreach ($gameCategories as $category)
                        <option value="{{ $category->id }}" @selected(($filters['game_category_id'] ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-xl-2">
                <label class="form-label small mb-1" for="item-category">Item category</label>
                <select class="form-select" id="item-category" name="item_category_id">
                    <option value="">All items</option>
                    @foreach ($itemCategories as $category)
                        <option value="{{ $category->id }}" @selected(($filters['item_category_id'] ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-xl-2">
                <label class="form-label small mb-1" for="status">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All statuses</option>
                    @foreach (['draft' => 'Draft', 'active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-xl-2">
                <label class="form-label small mb-1" for="featured">Featured</label>
                <select class="form-select" id="featured" name="featured">
                    <option value="">All</option>
                    <option value="1" @selected(($filters['featured'] ?? '') === '1')>Featured</option>
                    <option value="0" @selected(($filters['featured'] ?? '') === '0')>Not featured</option>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-outline-secondary" type="submit">Filter</button></div>
            @if (count(array_filter($filters, fn ($value) => $value !== null && $value !== '')))
                <div class="col-auto"><a class="btn btn-light border" href="{{ route('admin.products.index') }}">Clear</a></div>
            @endif
        </div>
    </form>

    <form class="panel mb-3 p-3" id="bulk-product-form" method="POST" action="{{ route('admin.products.bulk') }}">
        @csrf
        <div class="align-items-end d-flex flex-wrap gap-2">
            <div>
                <label class="form-label small mb-1" for="bulk-product-action">Selected products</label>
                <select class="form-select" id="bulk-product-action" name="action" required>
                    <option value="">Choose an action</option>
                    <option value="archive">Archive / deactivate</option>
                    <option value="delete">Permanently delete</option>
                </select>
            </div>
            <button class="btn btn-outline-danger" type="submit">Apply to selected</button>
            <span class="small text-muted">Selections apply to products on this page.</span>
        </div>
    </form>

    <div class="panel overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th><input class="form-check-input" id="select-all-products" type="checkbox" aria-label="Select all products on this page"></th><th>Product</th><th>Game</th><th>Item</th><th>Variants</th><th>Price</th><th>Status</th><th>Featured</th><th>Created</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td><input class="form-check-input" form="bulk-product-form" name="ids[]" type="checkbox" value="{{ $product->id }}" aria-label="Select {{ $product->name }}"></td>
                        <td>
                            <div class="align-items-center d-flex gap-2">
                                @if ($product->image)
                                    <img class="product-thumb" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}">
                                @else
                                    <div class="product-thumb align-items-center d-flex justify-content-center text-muted">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</div>
                                @endif
                                <span class="fw-semibold">{{ $product->name }}</span>
                            </div>
                        </td>
                        <td>{{ $product->gameCategory?->name ?? '—' }}</td>
                        <td>{{ $product->itemCategory?->name ?? '—' }}</td>
                        <td>{{ $product->variants_count }}</td>
                        <td>
                            @if ($product->variants->isNotEmpty())
                                <details class="price-editor">
                                    @php
                                        $minimumPrice = $product->variants->min(
                                            fn ($variant) => $product->priceInIdr((float) $variant->price, $currencyRates),
                                        );
                                    @endphp
                                    <summary>Rp {{ rtrim(rtrim(number_format($minimumPrice, 2, '.', ','), '0'), '.') }}</summary>
                                    <div class="currency-preview currency-summary">
                                        <span>≈ RM {{ number_format($minimumPrice / $currencyRates->idr_per_myr, 2) }}</span>
                                        <span>≈ ${{ number_format($minimumPrice / $currencyRates->idr_per_usd, 2) }}</span>
                                    </div>
                                    <form class="price-editor-form" method="POST" action="{{ route('admin.products.variant-prices', $product) }}">
                                        @csrf
                                        @method('PATCH')
                                        @foreach ($product->variants as $variant)
                                            @php
                                                $variantPrice = (float) $variant->price;
                                                $bobuxRatePrice = $product->usesBobuxRate();
                                                $idrPrice = $product->priceInIdr($variantPrice, $currencyRates);
                                            @endphp
                                            <div class="mb-2">
                                                <label class="form-label small mb-1" for="price-{{ $product->id }}-{{ $variant->id }}">{{ $variant->name }} ({{ $bobuxRatePrice ? 'Bobux' : 'IDR' }})</label>
                                                <div data-price-group data-bobux-type="{{ $product->bobuxType() }}">
                                                    <input class="form-control form-control-sm" id="price-{{ $product->id }}-{{ $variant->id }}" type="text" inputmode="decimal" autocomplete="off" value="{{ rtrim(rtrim(number_format($variantPrice, 2, '.', ','), '0'), '.') }}" data-price-display required>
                                                    <input name="variants[{{ $loop->index }}][id]" type="hidden" value="{{ $variant->id }}">
                                                    <input name="variants[{{ $loop->index }}][price]" type="hidden" value="{{ number_format($variantPrice, 2, '.', '') }}" data-price-value>
                                                    <div class="currency-preview" data-price-preview>
                                                        <span data-price-idr>Rp {{ rtrim(rtrim(number_format($idrPrice, 2, '.', ','), '0'), '.') }}</span>
                                                        <span data-price-myr>≈ RM {{ number_format($idrPrice / $currencyRates->idr_per_myr, 2) }}</span>
                                                        <span data-price-usd>≈ ${{ number_format($idrPrice / $currencyRates->idr_per_usd, 2) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        <button class="btn btn-sm btn-accent w-100" type="submit">Save prices</button>
                                    </form>
                                </details>
                            @else
                                <span class="text-muted">No variants</span>
                            @endif
                        </td>
                        <td>
                            <form class="d-flex gap-1" method="POST" action="{{ route('admin.products.status', $product) }}">
                                @csrf
                                @method('PATCH')
                                <select class="form-select form-select-sm" name="status" aria-label="Status for {{ $product->name }}">
                                    @foreach (['draft' => 'Draft', 'active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)
                                        <option value="{{ $value }}" @selected($product->status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-outline-secondary" type="submit">Save</button>
                            </form>
                        </td>
                        <td>{{ $product->is_featured ? 'Yes' : 'No' }}</td>
                        <td>{{ $product->created_at->format('M j, Y') }}</td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.products.show', $product) }}">View</a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.products.edit', $product) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Permanently delete this product? Products with order history cannot be deleted.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="py-5 text-center text-muted" colspan="10">No products match these filters.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($products->hasPages())
            <div class="border-top p-3">{{ $products->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
    @include('admin.products._price-behavior')
    <script>
        (() => {
            document.querySelector('[data-game-filter]')?.addEventListener('change', (event) => {
                const filterForm = event.currentTarget.form;
                filterForm.querySelector('[name="item_category_id"]').value = '';
                filterForm.requestSubmit();
            });

            const form = document.getElementById('bulk-product-form');
            const selectAll = document.getElementById('select-all-products');
            const checkboxes = [...document.querySelectorAll('input[name="ids[]"][form="bulk-product-form"]')];

            selectAll?.addEventListener('change', () => {
                checkboxes.forEach((checkbox) => { checkbox.checked = selectAll.checked; });
            });

            checkboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    if (selectAll) {
                        selectAll.checked = checkboxes.length > 0 && checkboxes.every((item) => item.checked);
                        selectAll.indeterminate = checkboxes.some((item) => item.checked) && !selectAll.checked;
                    }
                });
            });

            form?.addEventListener('submit', (event) => {
                if (!checkboxes.some((checkbox) => checkbox.checked)) {
                    event.preventDefault();
                    window.alert('Select at least one product.');
                    return;
                }

                if (document.getElementById('bulk-product-action').value === 'delete' &&
                    !window.confirm('Permanently delete the selected products? Products with order history will be kept.')) {
                    event.preventDefault();
                }
            });
        })();
    </script>
@endsection