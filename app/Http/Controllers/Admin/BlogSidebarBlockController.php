<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogSidebarBlock;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class BlogSidebarBlockController extends Controller
{
    public function index(): View
    {
        $sidebarBlocks = BlogSidebarBlock::query()
            ->with('product:id,name,image,status')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.blog-sidebar-blocks.index', compact('sidebarBlocks'));
    }

    public function create(): View
    {
        return view('admin.blog-sidebar-blocks.form', [
            'sidebarBlock' => new BlogSidebarBlock,
            'products' => $this->availableProducts(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $image = $request->file('image');
        $imagePath = $validated['type'] === BlogSidebarBlock::TYPE_POSTER && $image instanceof UploadedFile
            ? $this->storePoster($image)
            : null;

        try {
            BlogSidebarBlock::create($this->blockAttributes($validated, $imagePath));
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }

        return redirect()
            ->route('admin.blog-sidebar-blocks.index')
            ->with('success', 'Blog sidebar block created successfully.');
    }

    public function edit(BlogSidebarBlock $blogSidebarBlock): View
    {
        return view('admin.blog-sidebar-blocks.form', [
            'sidebarBlock' => $blogSidebarBlock,
            'products' => $this->availableProducts(),
        ]);
    }

    public function update(Request $request, BlogSidebarBlock $blogSidebarBlock): RedirectResponse
    {
        $validated = $this->validatedData($request, $blogSidebarBlock);
        $previousImagePath = $blogSidebarBlock->image_path;
        $image = $request->file('image');
        $newImagePath = $validated['type'] === BlogSidebarBlock::TYPE_POSTER && $image instanceof UploadedFile
            ? $this->storePoster($image)
            : null;

        try {
            $blogSidebarBlock->update($this->blockAttributes(
                $validated,
                $newImagePath ?? ($validated['type'] === BlogSidebarBlock::TYPE_POSTER ? $previousImagePath : null),
            ));
        } catch (Throwable $exception) {
            if ($newImagePath !== null) {
                Storage::disk('public')->delete($newImagePath);
            }

            throw $exception;
        }

        if ($previousImagePath !== null && $blogSidebarBlock->image_path !== $previousImagePath) {
            Storage::disk('public')->delete($previousImagePath);
        }

        return redirect()
            ->route('admin.blog-sidebar-blocks.index')
            ->with('success', 'Blog sidebar block updated successfully.');
    }

    public function destroy(BlogSidebarBlock $blogSidebarBlock): RedirectResponse
    {
        $imagePath = $blogSidebarBlock->image_path;
        $blogSidebarBlock->delete();

        if ($imagePath !== null) {
            Storage::disk('public')->delete($imagePath);
        }

        return redirect()
            ->route('admin.blog-sidebar-blocks.index')
            ->with('success', 'Blog sidebar block deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?BlogSidebarBlock $sidebarBlock = null): array
    {
        $type = $request->input('type');
        $isPosterWithoutImage = $type === BlogSidebarBlock::TYPE_POSTER
            && ($sidebarBlock === null || $sidebarBlock->image_path === null);

        return $request->validate([
            'type' => ['required', Rule::in(BlogSidebarBlock::TYPES)],
            'title' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'product_id' => [
                Rule::requiredIf($type === BlogSidebarBlock::TYPE_PRODUCT),
                'nullable',
                'integer',
                Rule::exists('products', 'id')->where('status', 'active'),
            ],
            'image' => [
                Rule::requiredIf($isPosterWithoutImage),
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
            'link_url' => ['nullable', 'url:http,https', 'max:2048'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function blockAttributes(array $validated, ?string $imagePath): array
    {
        $type = $validated['type'];

        return [
            'type' => $type,
            'title' => $validated['title'],
            'sort_order' => $validated['sort_order'],
            'is_active' => $validated['is_active'],
            'product_id' => $type === BlogSidebarBlock::TYPE_PRODUCT ? $validated['product_id'] : null,
            'image_path' => $type === BlogSidebarBlock::TYPE_POSTER ? $imagePath : null,
            'link_url' => $type === BlogSidebarBlock::TYPE_POSTER ? ($validated['link_url'] ?? null) : null,
        ];
    }

    /**
     * @return Collection<int, Product>
     */
    private function availableProducts(): Collection
    {
        return Product::query()
            ->where('status', 'active')
            ->with(['gameCategory:id,name', 'itemCategory:id,name'])
            ->orderBy('name')
            ->get(['id', 'name', 'game_category_id', 'item_category_id', 'image']);
    }

    private function storePoster(UploadedFile $image): string
    {
        $imagePath = $image->store('blog-sidebar', 'public');

        if ($imagePath === false) {
            throw new RuntimeException('The poster image could not be stored.');
        }

        return $imagePath;
    }
}
