<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameCategory;
use App\Models\ItemCategory;
use App\Models\Product;
use App\Models\ProductCurrencyRate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Product::query()
            ->with(['gameCategory:id,name', 'itemCategory:id,name,bobux_type,uses_robux'])
            ->with(['variants:id,product_id,name,price,sort_order'])
            ->withCount('variants');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        if ($request->filled('game_category_id')) {
            $query->where('game_category_id', $request->query('game_category_id'));
        }

        if ($request->filled('item_category_id')) {
            $query->where('item_category_id', $request->query('item_category_id'));
        }

        if (in_array($request->query('status'), ['draft', 'active', 'inactive'], true)) {
            $query->where('status', $request->query('status'));
        }

        if (in_array($request->query('featured'), ['0', '1'], true)) {
            $query->where('is_featured', $request->query('featured') === '1');
        }

        $itemCategoriesQuery = ItemCategory::query()->orderBy('name');
        if ($request->filled('game_category_id')) {
            $itemCategoriesQuery->whereHas(
                'gameCategories',
                fn ($gameCategories) => $gameCategories->whereKey($request->query('game_category_id')),
            );
        }

        return view('admin.products.index', [
            'products' => $query->orderByDesc('created_at')->orderByDesc('id')->paginate(15)->withQueryString(),
            'gameCategories' => GameCategory::query()->orderBy('name')->get(['id', 'name']),
            'itemCategories' => $itemCategoriesQuery->get(['id', 'name', 'bobux_type', 'uses_robux']),
            'currencyRates' => ProductCurrencyRate::current(),
            'filters' => $request->only(['search', 'game_category_id', 'item_category_id', 'status', 'featured']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $itemCategories = collect();
        $selectedGameCategory = GameCategory::query()->find($request->old('game_category_id'));

        if ($selectedGameCategory !== null) {
            $itemCategories = $selectedGameCategory->itemCategories()
                ->where('item_categories.status', 'active')
                ->orderBy('item_categories.name')
                ->get(['item_categories.id', 'item_categories.name', 'item_categories.bobux_type', 'item_categories.uses_robux']);
        }

        return view('admin.products.form', [
            'product' => new Product,
            'gameCategories' => GameCategory::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'itemCategories' => $itemCategories,
            'currencyRates' => ProductCurrencyRate::current(),
            'title' => 'Add Product',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);
        $imagePath = $request->file('image')?->store('products', 'public');

        try {
            $product = DB::transaction(function () use ($validated, $imagePath): Product {
                $attributes = Arr::only($validated, [
                    'game_category_id',
                    'item_category_id',
                    'name',
                    'description',
                    'badges',
                    'discount_percent',
                    'purchase_fields',
                    'status',
                    'is_featured',
                ]);
                $attributes['image'] = $imagePath;
                $attributes['status'] = 'active';

                $product = Product::create($attributes);

                foreach ($validated['variants'] as $variant) {
                    $product->variants()->create(Arr::except($variant, ['id']));
                }

                return $product;
            });
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): View
    {
        $product->load(['gameCategory:id,name', 'itemCategory:id,name,bobux_type,uses_robux', 'variants']);

        return view('admin.products.show', [
            'product' => $product,
            'currencyRates' => ProductCurrencyRate::current(),
        ]);
    }

    public function toggleStatus(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'required', Rule::in(['draft', 'active', 'inactive'])],
        ]);

        $product->update([
            'status' => $validated['status'] ?? ($product->status === 'active' ? 'inactive' : 'active'),
        ]);

        return back()->with('success', 'Product status updated.');
    }

    public function updateVariantPrices(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('product_variants', 'id')->where('product_id', $product->id),
            ],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($product, $validated): void {
            foreach ($validated['variants'] as $variant) {
                $product->variants()->whereKey($variant['id'])->update(['price' => $variant['price']]);
            }
        });

        return back()->with('success', 'Variant prices updated.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        $product->load('variants');
        $itemCategories = $product->gameCategory
            ? $product->gameCategory->itemCategories()
                ->where(function ($query) use ($product): void {
                    $query->where('item_categories.status', 'active')
                        ->orWhere('item_categories.id', $product->item_category_id);
                })
                ->orderBy('item_categories.name')
                ->get(['item_categories.id', 'item_categories.name', 'item_categories.bobux_type', 'item_categories.uses_robux'])
            : collect();

        return view('admin.products.form', [
            'product' => $product,
            'gameCategories' => GameCategory::query()
                ->where('status', 'active')
                ->orWhere('id', $product->game_category_id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'itemCategories' => $itemCategories,
            'currencyRates' => ProductCurrencyRate::current(),
            'title' => 'Edit Product',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request, $product);
        $previousImage = $product->image;
        $imagePath = $request->file('image')?->store('products', 'public');

        try {
            DB::transaction(function () use ($validated, $product, $imagePath): void {
                $attributes = Arr::only($validated, [
                    'game_category_id',
                    'item_category_id',
                    'name',
                    'description',
                    'badges',
                    'discount_percent',
                    'purchase_fields',
                    'status',
                    'is_featured',
                ]);

                if ($imagePath !== null) {
                    $attributes['image'] = $imagePath;
                }

                $product->update($attributes);
                $keptVariantIds = [];

                foreach ($validated['variants'] as $variantData) {
                    $variantId = $variantData['id'] ?? null;
                    $variantAttributes = Arr::except($variantData, ['id']);

                    if ($variantId !== null) {
                        $variant = $product->variants()->whereKey($variantId)->firstOrFail();
                        $variant->update($variantAttributes);
                        $keptVariantIds[] = $variant->id;
                    } else {
                        $variant = $product->variants()->create($variantAttributes);
                        $keptVariantIds[] = $variant->id;
                    }
                }

                $product->variants()->whereNotIn('id', $keptVariantIds)->delete();
            });
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }

        if ($imagePath !== null && $previousImage !== null) {
            Storage::disk('public')->delete($previousImage);
        }

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        if ($product->orderItems()->exists()) {
            throw ValidationException::withMessages([
                'product' => 'Products with order history cannot be permanently deleted. Deactivate the product instead.',
            ]);
        }

        $imagePath = DB::transaction(function () use ($product): ?string {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);

            if ($lockedProduct->orderItems()->exists()) {
                throw ValidationException::withMessages([
                    'product' => 'Products with order history cannot be permanently deleted. Deactivate the product instead.',
                ]);
            }

            $imagePath = $lockedProduct->image;
            $lockedProduct->delete();

            return $imagePath;
        });

        if ($imagePath !== null) {
            Storage::disk('public')->delete($imagePath);
        }

        return redirect()->route('admin.products.index')->with('success', 'Product permanently deleted.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['archive', 'delete'])],
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['required', 'integer', 'distinct', Rule::exists('products', 'id')],
        ]);

        $imagePaths = DB::transaction(function () use ($validated): array {
            $products = Product::query()
                ->whereKey($validated['ids'])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($products->count() !== count($validated['ids'])) {
                throw ValidationException::withMessages([
                    'ids' => 'One or more selected products no longer exist.',
                ]);
            }

            if ($validated['action'] === 'archive') {
                Product::query()->whereKey($validated['ids'])->update(['status' => 'inactive']);

                return [];
            }

            if (Product::query()->whereKey($validated['ids'])->whereHas('orderItems')->exists()) {
                throw ValidationException::withMessages([
                    'ids' => 'Products with order history cannot be permanently deleted. Deactivate them instead.',
                ]);
            }

            $imagePaths = $products->pluck('image')->filter()->unique()->values()->all();
            foreach ($products as $product) {
                $product->delete();
            }

            return $imagePaths;
        });

        if ($imagePaths !== []) {
            Storage::disk('public')->delete($imagePaths);
        }

        $message = $validated['action'] === 'archive'
            ? 'Selected products deactivated.'
            : 'Selected products permanently deleted.';

        return back()->with('success', $message);
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $hasDiscountBadge = in_array('discount', (array) $request->input('badges', []), true);
        $selectedItemCategory = ItemCategory::query()->find($request->input('item_category_id'));
        $bobuxType = $selectedItemCategory?->bobux_type
            ?? ($selectedItemCategory?->uses_robux ? 'gift_ingame' : null);
        $requiresManualBobuxAmount = $bobuxType === 'login';
        $rules = [
            'game_category_id' => ['required', 'integer', 'exists:game_categories,id'],
            'item_category_id' => [
                'required',
                'integer',
                'exists:item_categories,id',
                Rule::exists('game_category_item_category', 'item_category_id')
                    ->where('game_category_id', $request->input('game_category_id')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'badges' => ['nullable', 'array', 'max:'.count(Product::BADGE_OPTIONS)],
            'badges.*' => ['required', 'string', 'distinct', Rule::in(Product::BADGE_OPTIONS)],
            'discount_percent' => [
                Rule::when($hasDiscountBadge, ['required', 'numeric', 'gt:0', 'max:99.99'], ['prohibited']),
            ],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'purchase_fields' => ['nullable', 'array', 'max:10'],
            'purchase_fields.*' => ['required', 'array:key,label,type'],
            'purchase_fields.*.key' => ['required', 'string', 'alpha_dash:ascii', 'max:50', 'distinct:ignore_case', 'not_in:purchase_details'],
            'purchase_fields.*.label' => ['required', 'string', 'max:100'],
            'purchase_fields.*.type' => ['required', Rule::in(['text', 'email', 'password'])],
            'status' => $product === null
                ? ['sometimes', Rule::in(['draft', 'active', 'inactive'])]
                : ['required', Rule::in(['draft', 'active', 'inactive'])],
            'is_featured' => ['sometimes', 'boolean'],
            'variants' => ['required', 'array', 'min:1'],
        ];

        foreach ((array) $request->input('variants', []) as $index => $variant) {
            $variant = is_array($variant) ? $variant : [];
            $variantId = $variant['id'] ?? null;

            $rules["variants.{$index}.id"] = $product === null
                ? ['prohibited']
                : ['nullable', 'integer', 'distinct', Rule::exists('product_variants', 'id')->where('product_id', $product->id)];
            $rules["variants.{$index}.name"] = ['required', 'string', 'max:255'];
            $rules["variants.{$index}.price"] = ['required', 'numeric', 'min:0'];
            $rules["variants.{$index}.bobux_amount"] = $requiresManualBobuxAmount
                ? ['required', 'integer', 'min:1']
                : ['prohibited'];
            $rules["variants.{$index}.stock"] = ['required', 'integer', 'min:0'];
            $rules["variants.{$index}.minimum_quantity"] = ['required', 'integer', 'min:1'];
            $maximumQuantityRules = [
                'required',
                'integer',
                'min:0',
                'same:variants.'.$index.'.stock',
            ];

            if (is_numeric($variant['stock'] ?? null) && (int) $variant['stock'] > 0) {
                $maximumQuantityRules[] = 'gte:variants.'.$index.'.minimum_quantity';
            }

            $rules["variants.{$index}.maximum_quantity"] = $maximumQuantityRules;
            $rules["variants.{$index}.status"] = ['required', Rule::in(['active', 'inactive'])];
            $rules["variants.{$index}.sort_order"] = ['required', 'integer', 'min:0'];
        }

        $validated = $request->validate($rules);
        $validated['badges'] = array_values($validated['badges'] ?? []);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['purchase_fields'] = array_values($validated['purchase_fields'] ?? []);

        return $validated;
    }
}
