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

class GameCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $categories = GameCategory::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
            'categoryType' => 'game',
            'title' => 'Game Categories',
            'routePrefix' => 'admin.game-categories',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.categories.form', [
            'category' => new GameCategory,
            'categoryType' => 'game',
            'title' => 'Add Game Category',
            'routePrefix' => 'admin.game-categories',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('game_categories', 'name')],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $imagePath = $request->file('image')?->store('categories', 'public');

        try {
            $category = GameCategory::create([
                'name' => $validated['name'],
                'status' => $validated['status'] ?? 'active',
                'image' => $imagePath,
            ]);
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }

        if ($request->expectsJson()) {
            return response()->json(['id' => $category->id, 'name' => $category->name], 201);
        }

        return redirect()->route('admin.game-categories.index')->with('success', 'Game category created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(GameCategory $gameCategory): RedirectResponse
    {
        return redirect()->route('admin.game-categories.edit', $gameCategory);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GameCategory $gameCategory): View
    {
        return view('admin.categories.form', [
            'category' => $gameCategory,
            'categoryType' => 'game',
            'title' => 'Edit Game Category',
            'routePrefix' => 'admin.game-categories',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GameCategory $gameCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('game_categories', 'name')->ignore($gameCategory)],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $imagePath = $request->file('image')?->store('categories', 'public');
        $previousImage = $gameCategory->image;

        try {
            $gameCategory->update([
                'name' => $validated['name'],
                'status' => $validated['status'] ?? $gameCategory->status,
                'image' => $imagePath ?? $previousImage,
            ]);
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }

        if ($imagePath !== null && $previousImage !== null) {
            Storage::disk('public')->delete($previousImage);
        }

        return redirect()->route('admin.game-categories.index')->with('success', 'Game category updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GameCategory $gameCategory): RedirectResponse
    {
        $imagePath = DB::transaction(function () use ($gameCategory): ?string {
            $lockedCategory = GameCategory::query()->lockForUpdate()->findOrFail($gameCategory->id);
            $itemCategoryIds = $lockedCategory->itemCategories()->pluck('item_categories.id')->all();

            ItemCategory::query()->whereKey($itemCategoryIds)->update(['status' => 'inactive']);
            Product::query()->whereIn('item_category_id', $itemCategoryIds)->update(['status' => 'inactive']);
            $lockedCategory->products()->update([
                'game_category_id' => null,
                'status' => 'inactive',
            ]);

            $imagePath = $lockedCategory->image;
            $lockedCategory->delete();

            return $imagePath;
        });

        if ($imagePath !== null) {
            Storage::disk('public')->delete($imagePath);
        }

        return redirect()->route('admin.game-categories.index')->with('success', 'Game category permanently deleted. Its item categories and related products were deactivated.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['archive', 'delete'])],
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['required', 'integer', 'distinct', Rule::exists('game_categories', 'id')],
        ]);

        $imagePaths = DB::transaction(function () use ($validated): array {
            $categories = GameCategory::query()
                ->whereKey($validated['ids'])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($categories->count() !== count($validated['ids'])) {
                throw ValidationException::withMessages([
                    'ids' => 'One or more selected game categories no longer exist.',
                ]);
            }

            if ($validated['action'] === 'archive') {
                GameCategory::query()->whereKey($validated['ids'])->update(['status' => 'inactive']);
                Product::query()->whereIn('game_category_id', $validated['ids'])->update(['status' => 'inactive']);

                return [];
            }

            $itemCategoryIds = ItemCategory::query()
                ->whereHas('gameCategories', fn ($query) => $query->whereKey($validated['ids']))
                ->pluck('item_categories.id')
                ->all();

            ItemCategory::query()->whereKey($itemCategoryIds)->update(['status' => 'inactive']);
            Product::query()->whereIn('item_category_id', $itemCategoryIds)->update(['status' => 'inactive']);
            Product::query()->whereIn('game_category_id', $validated['ids'])->update([
                'game_category_id' => null,
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
            ? 'Selected game categories archived and their products deactivated.'
            : 'Selected game categories permanently deleted. Their item categories and related products were deactivated.';

        return back()->with('success', $message);
    }

    public function toggleStatus(GameCategory $gameCategory): RedirectResponse
    {
        DB::transaction(function () use ($gameCategory): void {
            $status = $gameCategory->status === 'active' ? 'inactive' : 'active';
            $gameCategory->update(['status' => $status]);

            if ($status === 'inactive') {
                $gameCategory->products()->update(['status' => 'inactive']);
            }
        });

        return back()->with('success', 'Game category status updated.');
    }

    public function itemCategories(Request $request, GameCategory $gameCategory): JsonResponse
    {
        $selectedItemCategoryId = filter_var($request->query('selected_item_category_id'), FILTER_VALIDATE_INT);

        $itemCategories = $gameCategory->itemCategories()
            ->where(function ($query) use ($selectedItemCategoryId): void {
                $query->where('item_categories.status', 'active');

                if ($selectedItemCategoryId !== false) {
                    $query->orWhere('item_categories.id', $selectedItemCategoryId);
                }
            })
            ->orderBy('item_categories.name')
            ->get(['item_categories.id', 'item_categories.name', 'item_categories.bobux_type', 'item_categories.uses_robux']);

        return response()->json($itemCategories);
    }
}
