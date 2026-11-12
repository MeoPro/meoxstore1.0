<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BlogCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $categories = BlogCategory::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->withCount('blogPosts')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.blog-categories.index', compact('categories', 'search'));
    }

    public function create(): View
    {
        return view('admin.blog-categories.form', [
            'blogCategory' => new BlogCategory,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $validated['slug'] = ($validated['slug'] ?? null) ?: $this->uniqueSlug($validated['name']);
        BlogCategory::create($validated);

        return redirect()
            ->route('admin.blog-categories.index')
            ->with('success', 'Blog category created successfully.');
    }

    public function edit(BlogCategory $blogCategory): View
    {
        return view('admin.blog-categories.form', compact('blogCategory'));
    }

    public function update(Request $request, BlogCategory $blogCategory): RedirectResponse
    {
        $validated = $this->validatedData($request, $blogCategory);
        $validated['slug'] = ($validated['slug'] ?? null) ?: $blogCategory->slug;
        $blogCategory->update($validated);

        return redirect()
            ->route('admin.blog-categories.index')
            ->with('success', 'Blog category updated successfully.');
    }

    public function destroy(BlogCategory $blogCategory): RedirectResponse
    {
        $blogCategory->delete();

        return redirect()
            ->route('admin.blog-categories.index')
            ->with('success', 'Blog category deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?BlogCategory $blogCategory = null): array
    {
        $slug = $request->input('slug');

        if (is_string($slug)) {
            $request->merge([
                'slug' => filled($slug) ? Str::slug($slug) : null,
            ]);
        }

        $uniqueSlug = Rule::unique('blog_categories', 'slug');

        if ($blogCategory !== null) {
            $uniqueSlug->ignore($blogCategory->id);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', $uniqueSlug],
            'description' => ['nullable', 'string', 'max:320'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            throw ValidationException::withMessages([
                'slug' => 'Enter a URL slug because one could not be generated from the category name.',
            ]);
        }

        $slug = $baseSlug;
        $suffix = 2;

        while (BlogCategory::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
