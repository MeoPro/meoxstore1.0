<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogSidebarBlock;
use App\Models\GameCategory;
use App\Models\ItemCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(): View
    {
        $blogCategories = BlogCategory::active()
            ->whereHas('blogPosts', fn ($query) => $query->published())
            ->with([
                'blogPosts' => fn ($query) => $query->published()
                    ->latest('published_at')
                    ->limit(3),
            ])
            ->withCount(['blogPosts as published_posts_count' => fn ($query) => $query->published()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $uncategorizedPosts = BlogPost::published()
            ->whereDoesntHave('categories', fn ($query) => $query->active())
            ->latest('published_at')
            ->paginate(3);

        return view('blog.index', array_merge([
            'blogCategories' => $blogCategories,
            'uncategorizedPosts' => $uncategorizedPosts,
            'metaDescription' => __('store.blog_meta_description'),
        ], $this->sidebarData()));
    }

    public function category(BlogCategory $blogCategory): View
    {
        abort_unless($blogCategory->is_active, 404);

        $blogPosts = $blogCategory->blogPosts()
            ->published()
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
        ], $this->sidebarData()));
    }

    public function show(BlogPost $blogPost): View
    {
        abort_unless($blogPost->isPubliclyAvailable(), 404);

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
     *     sidebarBlocks: Collection<int, BlogSidebarBlock>,
     *     sidebarBlogCategories: Collection<int, BlogCategory>,
     *     sidebarGameCategories: Collection<int, GameCategory>,
     *     sidebarItemCategories: Collection<int, ItemCategory>
     * }
     */
    private function sidebarData(): array
    {
        $sidebarBlocks = BlogSidebarBlock::active()
            ->with(['product' => fn ($query) => $query
                ->where('status', 'active')
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
                ->whereHas('blogPosts', fn ($query) => $query->published())
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
            : collect();

        $sidebarGameCategories = $types->contains(BlogSidebarBlock::TYPE_GAME_CATEGORIES)
            ? GameCategory::query()
                ->where('status', 'active')
                ->whereHas('products', fn ($query) => $query
                    ->where('status', 'active')
                    ->whereHas('variants', fn ($variantQuery) => $variantQuery->where('status', 'active')))
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();

        $sidebarItemCategories = $types->contains(BlogSidebarBlock::TYPE_ITEM_CATEGORIES)
            ? ItemCategory::query()
                ->where('status', 'active')
                ->whereHas('products', fn ($query) => $query
                    ->where('status', 'active')
                    ->whereHas('variants', fn ($variantQuery) => $variantQuery->where('status', 'active')))
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();

        return compact(
            'sidebarBlocks',
            'sidebarBlogCategories',
            'sidebarGameCategories',
            'sidebarItemCategories',
        );
    }
}
