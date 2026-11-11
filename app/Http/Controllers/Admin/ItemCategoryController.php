<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameCategory;
use App\Models\ItemCategory;
use App\Models\Product;
use Illuminate\Contracts\View\View;
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

        $categories = ItemCategory::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
            'categoryType' => 'item',
            'title' => 'Item Categories',
            'routePrefix' => 'admin.item-categories',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.categories.form', [
            'category' => new ItemCategory,
            'categoryType' => 'item',
            'title' => 'Add Item Category',
            'routePrefix' => 'admin.item-categories',
            'gameCategories' => GameCategory::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'selectedGameCategoryId' => null,
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
            'name' => ['required', 'string', 'max:255', Rule::unique('item_categories', 'name')],
            'game_category_id' => [
                'required',
                'integer',
                Rule::exists('game_categories', 'id')->where('status', 'active'),
            ],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
            'bobux_type' => ['sometimes', 'nullable', Rule::in(ItemCategory::BOBUX_TYPES)],
            'uses_robux' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $bobuxType = array_key_exists('bobux_type', $validated)
            ? $validated['bobux_type']
            : ($request->boolean('uses_robux') ? 'gift_ingame' : null);

        $imagePath = $request->file('image')?->store('categories', 'public');

        try {
            DB::transaction(function () use ($validated, $imagePath, $bobuxType): void {
                $category = ItemCategory::create([
                    'name' => $validated['name'],
                    'bobux_type' => $bobuxType,
                    'uses_robux' => in_array($bobuxType, ['gift_ingame', 'gamepass_5_days'], true),
                    'status' => $validated['status'] ?? 'active',
                    'image' => $imagePath,
                ]);

                GameCategory::query()
                    ->findOrFail($validated['game_category_id'])
                    ->itemCategories()
                    ->syncWithoutDetaching($category->id);
            });
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }

        return redirect()->route('admin.item-categories.index')->with('success', 'Item category created.');
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
            'selectedGameCategoryId' => $itemCategory->gameCategories()
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
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
            'bobux_type' => ['sometimes', 'nullable', Rule::in(ItemCategory::BOBUX_TYPES)],
            'uses_robux' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $bobuxType = array_key_exists('bobux_type', $validated)
            ? $validated['bobux_type']
            : ($request->has('uses_robux')
                ? ($request->boolean('uses_robux') ? 'gift_ingame' : null)
                : $itemCategory->bobux_type);

        $imagePath = $request->file('image')?->store('categories', 'public');
        $previousImage = $itemCategory->image;

        try {
            DB::transaction(function () use ($validated, $imagePath, $previousImage, $itemCategory, $bobuxType): void {
                $itemCategory->update([
                    'name' => $validated['name'],
                    'bobux_type' => $bobuxType,
                    'uses_robux' => in_array($bobuxType, ['gift_ingame', 'gamepass_5_days'], true),
                    'status' => $validated['status'] ?? $itemCategory->status,
                    'image' => $imagePath ?? $previousImage,
                ]);

                GameCategory::query()
                    ->findOrFail($validated['game_category_id'])
                    ->itemCategories()
                    ->syncWithoutDetaching($itemCategory->id);
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
        $imagePath = DB::transaction(function () use ($itemCategory): ?string {
            $lockedCategory = ItemCategory::query()->lockForUpdate()->findOrFail($itemCategory->id);
            $lockedCategory->products()->update([
                'item_category_id' => null,
                'status' => 'inactive',
            ]);

            $imagePath = $lockedCategory->image;
            $lockedCategory->delete();

            return $imagePath;
        });

        if ($imagePath !== null) {
            Storage::disk('public')->delete($imagePath);
        }

        return redirect()->route('admin.item-categories.index')->with('success', 'Item category permanently deleted and its products deactivated.');
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
                Product::query()->whereIn('item_category_id', $validated['ids'])->update(['status' => 'inactive']);

                return [];
            }

            Product::query()->whereIn('item_category_id', $validated['ids'])->update([
                'item_category_id' => null,
                'status' => 'inactive',
            ]);

            $imagePaths = $categories->pluck('image')->filter()->unique()->values()->all();
            foreach ($categories as $category) {
                $category->delete();
            }

            return $imagePaths;
        });

        if ($imagePaths !== []) {
            Storage::disk('public')->delete($imagePaths);
        }

        $message = $validated['action'] === 'archive'
            ? 'Selected item categories archived and their products deactivated.'
            : 'Selected item categories permanently deleted and their products deactivated.';

        return back()->with('success', $message);
    }

    public function toggleStatus(ItemCategory $itemCategory): RedirectResponse
    {
        DB::transaction(function () use ($itemCategory): void {
            $status = $itemCategory->status === 'active' ? 'inactive' : 'active';
            $itemCategory->update(['status' => $status]);

            if ($status === 'inactive') {
                $itemCategory->products()->update(['status' => 'inactive']);
            }
        });

        return back()->with('success', 'Item category status updated.');
    }
}
