<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameCategory;
use App\Models\ItemCategory;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class ItemCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $parentCategoryId = filter_var($request->query('parent_id'), FILTER_VALIDATE_INT);
        $parentCategoryId = $parentCategoryId === false || $parentCategoryId < 1
            ? null
            : $parentCategoryId;

        $categories = ItemCategory::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($parentCategoryId !== null, fn ($query) => $query->where('parent_id', $parentCategoryId))
            ->with('parent:id,name')
            ->orderBy('parent_id')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
            'categoryType' => 'item',
            'title' => 'Item Categories',
            'routePrefix' => 'admin.item-categories',
            'parentCategories' => ItemCategory::query()
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get(['id', 'name']),
            'selectedParentCategoryId' => $parentCategoryId,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        return view('admin.categories.form', [
            'category' => new ItemCategory,
            'categoryType' => 'item',
            'title' => 'Add Item Category',
            'routePrefix' => 'admin.item-categories',
            'gameCategories' => GameCategory::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'parentCategories' => $this->parentCategories(
                selectedId: filter_var($request->old('parent_id'), FILTER_VALIDATE_INT) ?: null,
            ),
            'selectedGameCategoryId' => $request->old('game_category_id'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'game_category_id' => [
                    'required',
                    'integer',
                    Rule::exists('game_categories', 'id')->where('status', 'active'),
                ],
                'bobux_type' => ['sometimes', 'nullable', Rule::in(ItemCategory::BOBUX_TYPES)],
                'uses_robux' => ['sometimes', 'boolean'],
            ]);
            $bobuxType = array_key_exists('bobux_type', $validated)
                ? $validated['bobux_type']
                : ($request->boolean('uses_robux') ? 'gift_ingame' : null);

            $category = ItemCategory::query()->firstOrCreate(
                ['name' => $validated['name']],
                [
                    'status' => 'active',
                    'bobux_type' => $bobuxType,
                    'uses_robux' => in_array($bobuxType, ['gift_ingame', 'gamepass_5_days'], true),
                ],
            );

            if ($category->status !== 'active') {
                return response()->json([
                    'errors' => ['name' => ['This item category is inactive.']],
                ], 422);
            }

            if ($category->parent_id !== null) {
                return response()->json([
                    'errors' => ['name' => ['This name is already used by a subcategory.']],
                ], 422);
            }

            GameCategory::query()
                ->findOrFail($validated['game_category_id'])
                ->itemCategories()
                ->syncWithoutDetaching($category->id);

            return response()->json([
                'id' => $category->id,
                'name' => $category->name,
                'bobux_type' => $category->bobux_type,
                'uses_robux' => $category->uses_robux,
            ], 201);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'game_category_id' => [
                'required',
                'integer',
                Rule::exists('game_categories', 'id')->where('status', 'active'),
            ],
            'parent_id' => ['nullable', 'integer', 'exists:item_categories,id'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
            'bobux_type' => ['sometimes', 'nullable', Rule::in(ItemCategory::BOBUX_TYPES)],
            'uses_robux' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $parentCategory = $this->validatedParentCategory($validated);
        $bobuxType = array_key_exists('bobux_type', $validated)
            ? $validated['bobux_type']
            : ($request->boolean('uses_robux') ? 'gift_ingame' : null);

        $imagePath = $request->file('image')?->store('categories', 'public');

        try {
            $category = DB::transaction(function () use ($validated, $imagePath, $bobuxType, $parentCategory): ItemCategory {
                $category = ItemCategory::query()->firstOrCreate(
                    ['name' => $validated['name']],
                    [
                        'parent_id' => $parentCategory?->id,
                        'bobux_type' => $parentCategory === null ? $bobuxType : null,
                        'uses_robux' => $parentCategory === null
                            && in_array($bobuxType, ['gift_ingame', 'gamepass_5_days'], true),
                        'status' => $validated['status'] ?? 'active',
                        'image' => $imagePath,
                    ],
                );

                if (! $category->wasRecentlyCreated && $category->status !== 'active') {
                    throw ValidationException::withMessages([
                        'name' => ['This item category is inactive.'],
                    ]);
                }

                if ($category->parent_id !== $parentCategory?->id) {
                    throw ValidationException::withMessages([
                        'name' => ['This item category already exists at a different level.'],
                    ]);
                }

                if ($parentCategory === null) {
                    GameCategory::query()
                        ->findOrFail($validated['game_category_id'])
                        ->itemCategories()
                        ->syncWithoutDetaching($category->id);
                }

                return $category;
            });
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }

        if (! $category->wasRecentlyCreated && $imagePath !== null) {
            Storage::disk('public')->delete($imagePath);
        }

        $message = $category->wasRecentlyCreated
            ? 'Item category created.'
            : ($parentCategory === null
                ? 'Existing item category linked to the selected game; its existing settings were kept.'
                : 'The subcategory already belongs to the selected main category.');

        return redirect()->route('admin.item-categories.index')->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(ItemCategory $itemCategory): RedirectResponse
    {
        return redirect()->route('admin.item-categories.edit', $itemCategory);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ItemCategory $itemCategory): View
    {
        return view('admin.categories.form', [
            'category' => $itemCategory,
            'categoryType' => 'item',
            'title' => 'Edit Item Category',
            'routePrefix' => 'admin.item-categories',
            'gameCategories' => GameCategory::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'parentCategories' => $this->parentCategories(
                $itemCategory,
                $itemCategory->parent_id,
            ),
            'selectedGameCategoryId' => ($itemCategory->parent?->gameCategories() ?? $itemCategory->gameCategories())
                ->orderBy('game_categories.name')
                ->value('game_categories.id'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ItemCategory $itemCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('item_categories', 'name')->ignore($itemCategory)],
            'game_category_id' => [
                'required',
                'integer',
                Rule::exists('game_categories', 'id')->where('status', 'active'),
            ],
            'parent_id' => ['nullable', 'integer', 'exists:item_categories,id'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
            'bobux_type' => ['sometimes', 'nullable', Rule::in(ItemCategory::BOBUX_TYPES)],
            'uses_robux' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $parentCategory = $this->validatedParentCategory($validated, $itemCategory);
        $bobuxType = array_key_exists('bobux_type', $validated)
            ? $validated['bobux_type']
            : ($request->has('uses_robux')
                ? ($request->boolean('uses_robux') ? 'gift_ingame' : null)
                : $itemCategory->bobux_type);

        $imagePath = $request->file('image')?->store('categories', 'public');
        $previousImage = $itemCategory->image;

        try {
            DB::transaction(function () use ($validated, $imagePath, $previousImage, $itemCategory, $bobuxType, $parentCategory): void {
                $status = $validated['status'] ?? $itemCategory->status;
                $itemCategory->update([
                    'name' => $validated['name'],
                    'parent_id' => $parentCategory?->id,
                    'bobux_type' => $parentCategory === null ? $bobuxType : null,
                    'uses_robux' => $parentCategory === null
                        && in_array($bobuxType, ['gift_ingame', 'gamepass_5_days'], true),
                    'status' => $status,
                    'image' => $imagePath ?? $previousImage,
                ]);

                if ($status === 'inactive') {
                    $this->deactivateProductsInCategory($itemCategory);
                }

                if ($parentCategory === null) {
                    GameCategory::query()
                        ->findOrFail($validated['game_category_id'])
                        ->itemCategories()
                        ->syncWithoutDetaching($itemCategory->id);
                } else {
                    $itemCategory->gameCategories()->detach();
                }
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

        return redirect()->route('admin.item-categories.index')->with('success', 'Item category updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ItemCategory $itemCategory): RedirectResponse
    {
        $wasSubcategory = $itemCategory->parent_id !== null;
        if ($itemCategory->children()->exists()) {
            throw ValidationException::withMessages([
                'category' => "Delete this category's subcategories before deleting the category.",
            ]);
        }

        $imagePath = DB::transaction(function () use ($itemCategory): ?string {
            $lockedCategory = ItemCategory::query()->lockForUpdate()->findOrFail($itemCategory->id);
            if ($lockedCategory->parent_id === null) {
                $lockedCategory->products()->update([
                    'item_category_id' => null,
                    'status' => 'inactive',
                ]);
            }

            $imagePath = $lockedCategory->image;
            $lockedCategory->delete();

            return $imagePath;
        });

        if ($imagePath !== null) {
            Storage::disk('public')->delete($imagePath);
        }

        $message = $wasSubcategory
            ? 'Item subcategory permanently deleted and removed from its products.'
            : 'Item category permanently deleted and its products deactivated.';

        return redirect()->route('admin.item-categories.index')->with('success', $message);
    }

    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['archive', 'delete'])],
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['required', 'integer', 'distinct', Rule::exists('item_categories', 'id')],
        ]);

        $imagePaths = DB::transaction(function () use ($validated): array {
            $categories = ItemCategory::query()
                ->whereKey($validated['ids'])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($categories->count() !== count($validated['ids'])) {
                throw ValidationException::withMessages([
                    'ids' => 'One or more selected item categories no longer exist.',
                ]);
            }

            if ($validated['action'] === 'archive') {
                ItemCategory::query()->whereKey($validated['ids'])->update(['status' => 'inactive']);
                Product::query()->where(function ($query) use ($validated): void {
                    $query->whereIn('item_category_id', $validated['ids'])
                        ->orWhereHas(
                            'itemSubcategories',
                            fn ($subcategories) => $subcategories->whereIn('item_categories.id', $validated['ids']),
                        );
                })->update(['status' => 'inactive']);

                return [];
            }

            $unselectedChildrenExist = ItemCategory::query()
                ->whereIn('parent_id', $validated['ids'])
                ->whereNotIn('id', $validated['ids'])
                ->exists();
            if ($unselectedChildrenExist) {
                throw ValidationException::withMessages([
                    'ids' => 'Select the subcategories before permanently deleting their parent categories.',
                ]);
            }

            Product::query()->whereIn('item_category_id', $validated['ids'])->update([
                'item_category_id' => null,
                'status' => 'inactive',
            ]);

            $imagePaths = $categories->pluck('image')->filter()->unique()->values()->all();
            foreach ($categories->sortBy(fn (ItemCategory $category): int => $category->parent_id === null ? 1 : 0) as $category) {
                $category->delete();
            }

            return $imagePaths;
        });

        if ($imagePaths !== []) {
            Storage::disk('public')->delete($imagePaths);
        }

        $message = $validated['action'] === 'archive'
            ? 'Selected item categories archived and their products deactivated.'
            : 'Selected item categories permanently deleted and related products updated.';

        return back()->with('success', $message);
    }

    public function toggleStatus(ItemCategory $itemCategory): RedirectResponse
    {
        DB::transaction(function () use ($itemCategory): void {
            $status = $itemCategory->status === 'active' ? 'inactive' : 'active';
            $itemCategory->update(['status' => $status]);

            if ($status === 'inactive') {
                $this->deactivateProductsInCategory($itemCategory);
            }
        });

        return back()->with('success', 'Item category status updated.');
    }

    /**
     * @return Collection<int, ItemCategory>
     */
    private function parentCategories(?ItemCategory $category = null, ?int $selectedId = null): Collection
    {
        return ItemCategory::query()
            ->whereNull('parent_id')
            ->where(function ($query) use ($category, $selectedId): void {
                $query->where('status', 'active');

                if ($selectedId !== null) {
                    $query->orWhere('id', $selectedId);
                }

                if ($category?->parent_id !== null) {
                    $query->orWhere('id', $category->parent_id);
                }
            })
            ->with('gameCategories:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'status']);
    }

    private function validatedParentCategory(array $validated, ?ItemCategory $category = null): ?ItemCategory
    {
        $parentId = $validated['parent_id'] ?? null;
        if ($parentId === null) {
            if ($category !== null
                && $category->parent_id !== null
                && ($category->products()->exists() || $category->subcategoryProducts()->exists() || $category->children()->exists())) {
                throw ValidationException::withMessages([
                    'parent_id' => 'This category cannot become a main category while products or subcategories use it.',
                ]);
            }

            return null;
        }

        if ($category !== null && (int) $category->id === (int) $parentId) {
            throw ValidationException::withMessages([
                'parent_id' => 'A category cannot be its own subcategory.',
            ]);
        }

        $parentCategory = ItemCategory::query()
            ->whereNull('parent_id')
            ->with('gameCategories:id,name')
            ->find($parentId);

        if ($parentCategory === null
            || ! $parentCategory->gameCategories->contains('id', (int) $validated['game_category_id'])) {
            throw ValidationException::withMessages([
                'parent_id' => 'Choose a main category linked to the selected game.',
            ]);
        }

        if ($parentCategory->status !== 'active'
            && (int) ($category?->parent_id ?? 0) !== (int) $parentCategory->id) {
            throw ValidationException::withMessages([
                'parent_id' => 'Choose an active main category.',
            ]);
        }

        if ($category !== null
            && (int) ($category->parent_id ?? 0) !== (int) $parentCategory->id
            && ($category->products()->exists() || $category->subcategoryProducts()->exists() || $category->children()->exists())) {
            throw ValidationException::withMessages([
                'parent_id' => 'This category cannot change level while products or subcategories use it.',
            ]);
        }

        return $parentCategory;
    }

    private function deactivateProductsInCategory(ItemCategory $itemCategory): void
    {
        Product::query()
            ->where(function ($query) use ($itemCategory): void {
                $query->where('item_category_id', $itemCategory->id)
                    ->orWhereHas(
                        'itemSubcategories',
                        fn ($subcategories) => $subcategories->where('item_categories.id', $itemCategory->id),
                    );
            })
            ->update(['status' => 'inactive']);
    }
}
