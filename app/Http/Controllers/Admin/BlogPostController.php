<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BlogPostController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $search = is_string($search) ? trim($search) : '';
        $status = $request->query('status');
        $status = is_string($status) && in_array($status, ['draft', 'published'], true) ? $status : '';

        $blogPosts = BlogPost::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%');
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.blog-posts.index', compact('blogPosts', 'search', 'status'));
    }

    public function create(): View
    {
        return view('admin.blog-posts.form', [
            'blogPost' => new BlogPost,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $blogPost = new BlogPost($this->postAttributes($validated));

        if ($request->hasFile('cover_image')) {
            $blogPost->cover_image = $request->file('cover_image')->store('blog-covers', 'public');
        }

        $blogPost->save();

        return redirect()
            ->route('admin.blog-posts.index')
            ->with('success', 'Blog post created successfully.');
    }

    public function edit(BlogPost $blogPost): View
    {
        return view('admin.blog-posts.form', compact('blogPost'));
    }

    public function update(Request $request, BlogPost $blogPost): RedirectResponse
    {
        $validated = $this->validatedData($request, $blogPost);
        $previousCoverImage = $blogPost->cover_image;
        $blogPost->fill($this->postAttributes($validated, $blogPost));

        if ($request->hasFile('cover_image')) {
            $blogPost->cover_image = $request->file('cover_image')->store('blog-covers', 'public');
        }

        $blogPost->save();

        if ($previousCoverImage !== null && $blogPost->cover_image !== $previousCoverImage) {
            Storage::disk('public')->delete($previousCoverImage);
        }

        return redirect()
            ->route('admin.blog-posts.index')
            ->with('success', 'Blog post updated successfully.');
    }

    public function destroy(BlogPost $blogPost): RedirectResponse
    {
        $coverImage = $blogPost->cover_image;
        $blogPost->delete();

        if ($coverImage !== null) {
            Storage::disk('public')->delete($coverImage);
        }

        return redirect()
            ->route('admin.blog-posts.index')
            ->with('success', 'Blog post deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?BlogPost $blogPost = null): array
    {
        $slug = $request->input('slug');

        if (is_string($slug)) {
            $request->merge([
                'slug' => filled($slug) ? Str::slug($slug) : null,
            ]);
        }

        $uniqueSlug = Rule::unique('blog_posts', 'slug');

        if ($blogPost !== null) {
            $uniqueSlug->ignore($blogPost->id);
        }

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', $uniqueSlug],
            'excerpt' => ['required', 'string', 'max:320'],
            'body' => ['required', 'string', 'max:100000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function postAttributes(array $validated, ?BlogPost $blogPost = null): array
    {
        $slug = $validated['slug'] ?? null;

        if (blank($slug)) {
            $slug = $blogPost?->slug ?? $this->uniqueSlug($validated['title']);
        }

        return [
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'],
            'body' => $validated['body'],
            'seo_title' => $validated['seo_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'status' => $validated['status'],
            'published_at' => $validated['status'] === 'published'
                ? ($validated['published_at'] ?? now())
                : null,
        ];
    }

    private function uniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);

        if ($baseSlug === '') {
            throw ValidationException::withMessages([
                'slug' => 'Enter a URL slug because one could not be generated from the title.',
            ]);
        }

        $slug = $baseSlug;
        $suffix = 2;

        while (BlogPost::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
