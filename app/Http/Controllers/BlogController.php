<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogSidebarBlock;
use App\Models\GameCategory;
use App\Models\ItemCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function home(): View
    {
        $featuredPosts = BlogPost::published()
            ->inLocale()
            ->with(['categories' => fn ($query) => $query->active()])
            ->latest('published_at')
            ->latest('id')
            ->limit(6)
            ->get();

        return view('blog.home', array_merge([
            'blogCategories' => $this->blogCategories(),
            'blogPosts' => $featuredPosts->take(3),
            'featuredPosts' => $featuredPosts,
            'metaDescription' => __('store.blog_meta_description'),
            'searchQuery' => '',
        ], $this->sidebarData()));
    }

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $searchQuery = trim($validated['q'] ?? '');

        $blogPostsQuery = BlogPost::published()
            ->inLocale()
            ->with(['categories' => fn ($query) => $query->active()]);

        if ($searchQuery !== '') {
            $searchTerm = '%'.$searchQuery.'%';
            $blogPostsQuery->where(fn (Builder $query) => $query
                ->where('title', 'like', $searchTerm)
                ->orWhere('excerpt', 'like', $searchTerm)
                ->orWhere('body', 'like', $searchTerm));
        }

        $blogPosts = $blogPostsQuery
            ->latest('published_at')
            ->latest('id')
            ->paginate(6)
            ->withQueryString();

        $sidebarData = $this->sidebarData();

        return view('blog.index', array_merge([
            'blogPosts' => $blogPosts,
            'blogCategories' => $this->blogCategories(),
            'featuredPosts' => $sidebarData['recentBlogPosts'],
            'metaDescription' => __('store.blog_meta_description'),
            'searchQuery' => $searchQuery,
        ], $sidebarData));
    }

    /**
     * @return Collection<int, BlogCategory>
     */
    private function blogCategories(): Collection
    {
        return BlogCategory::active()
            ->whereHas('blogPosts', fn ($query) => $query->published()->inLocale())
            ->withCount(['blogPosts as published_posts_count' => fn ($query) => $query->published()->inLocale()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function category(BlogCategory $blogCategory): View
    {
        abort_unless($blogCategory->is_active, 404);

        $blogPosts = $blogCategory->blogPosts()
            ->published()
            ->inLocale()
            ->latest('published_at')
            ->paginate(12);

        $metaDescription = $blogCategory->description ?: __('store.blog_category_meta_description', [
            'category' => $blogCategory->name,
        ]);

        return view('blog.category', array_merge([
            'blogCategory' => $blogCategory,
            'blogPosts' => $blogPosts,
            'metaDescription' => $metaDescription,
            'canonicalUrl' => route('blog.categories.show', $blogCategory->slug),
        ], $this->sidebarData($blogCategory)));
    }

    public function show(BlogPost $blogPost): View|RedirectResponse
    {
        $requestedPost = $blogPost;
        $localizedPost = $blogPost->translations()
            ->where('locale', app()->getLocale())
            ->first();

        if ($localizedPost?->isPubliclyAvailable()) {
            $blogPost = $localizedPost;
        } elseif ($blogPost->locale !== $blogPost->source_locale) {
            $sourcePost = $blogPost->translations()
                ->where('locale', $blogPost->source_locale)
                ->first();

            if ($sourcePost?->isPubliclyAvailable()) {
                $blogPost = $sourcePost;
            }
        }

        abort_unless($blogPost->isPubliclyAvailable(), 404);

        if ($blogPost->getKey() !== $requestedPost->getKey()) {
            return redirect()->route('blog.show', $blogPost->slug);
        }

        $blogPost->load(['categories' => fn ($query) => $query->active()]);
        $metaDescription = $blogPost->meta_description ?: $blogPost->excerpt;
        $coverImageUrl = $blogPost->cover_image
            ? url(Storage::disk('public')->url($blogPost->cover_image))
            : null;
        $canonicalUrl = route('blog.show', $blogPost->slug);

        return view('blog.show', array_merge([
            'blogPost' => $blogPost,
            'bodyHtml' => Str::markdown($blogPost->body, [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]),
            'metaTitle' => $blogPost->seo_title ?: $blogPost->title,
            'metaDescription' => $metaDescription,
            'canonicalUrl' => $canonicalUrl,
            'coverImageUrl' => $coverImageUrl,
            'structuredData' => [
                '@context' => 'https://schema.org',
                '@type' => 'BlogPosting',
                'headline' => $blogPost->title,
                'description' => $metaDescription,
                'datePublished' => $blogPost->published_at->toAtomString(),
                'dateModified' => $blogPost->updated_at->toAtomString(),
                'mainEntityOfPage' => $canonicalUrl,
                'image' => $coverImageUrl,
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => config('app.name'),
                ],
            ],
        ], $this->sidebarData()));
    }

    /**
     * @return array{
     *     recentBlogPosts: Collection<int, BlogPost>,
     *     sidebarBlocks: Collection<int, BlogSidebarBlock>,
     *     sidebarBlogCategories: Collection<int, BlogCategory>,
     *     sidebarGameCategories: Collection<int, GameCategory>,
     *     sidebarItemCategories: Collection<int, ItemCategory>
     * }
     */
    private function sidebarData(?BlogCategory $blogCategory = null): array
    {
        $recentBlogPostsQuery = $blogCategory
            ? $blogCategory->blogPosts()->published()->inLocale()
            : BlogPost::published()->inLocale();
        $recentBlogPosts = $recentBlogPostsQuery
            ->latest('published_at')
            ->limit(3)
            ->get(['id', 'title', 'slug', 'published_at']);

        $sidebarBlocks = BlogSidebarBlock::active()
            ->with(['product' => fn ($query) => $query
                ->where('status', 'active')
                ->whereHas('variants', fn ($variantQuery) => $variantQuery
                    ->where('status', 'active')
                    ->where('stock', '>', 0))
                ->with(['gameCategory:id,name', 'itemCategory:id,name'])])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(fn (BlogSidebarBlock $block): bool => match ($block->type) {
                BlogSidebarBlock::TYPE_PRODUCT => $block->product !== null,
                BlogSidebarBlock::TYPE_POSTER => $block->image_path !== null,
                default => true,
            })
            ->values();

        $types = $sidebarBlocks->pluck('type')->unique();

        $sidebarBlogCategories = $types->contains(BlogSidebarBlock::TYPE_BLOG_CATEGORIES)
            ? BlogCategory::active()
                ->whereHas('blogPosts', fn ($query) => $query->published()->inLocale())
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
            : collect();

        $sidebarGameCategories = $types->contains(BlogSidebarBlock::TYPE_GAME_CATEGORIES)
            ? GameCategory::query()
                ->where('status', 'active')
                ->whereHas('products', fn ($query) => $query
                    ->where('status', 'active')
                    ->whereHas('variants', fn ($variantQuery) => $variantQuery
                        ->where('status', 'active')
                        ->where('stock', '>', 0)))
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();

        $sidebarItemCategories = $types->contains(BlogSidebarBlock::TYPE_ITEM_CATEGORIES)
            ? ItemCategory::query()
                ->where('status', 'active')
                ->whereHas('products', fn ($query) => $query
                    ->where('status', 'active')
                    ->whereHas('variants', fn ($variantQuery) => $variantQuery
                        ->where('status', 'active')
                        ->where('stock', '>', 0)))
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();

        return compact(
            'recentBlogPosts',
            'sidebarBlocks',
            'sidebarBlogCategories',
            'sidebarGameCategories',
            'sidebarItemCategories',
        );
    }
}
