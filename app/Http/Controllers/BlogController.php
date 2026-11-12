<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(): View
    {
        $blogPosts = BlogPost::published()
            ->latest('published_at')
            ->paginate(9);

        return view('blog.index', [
            'blogPosts' => $blogPosts,
            'metaDescription' => __('store.blog_meta_description'),
        ]);
    }

    public function show(BlogPost $blogPost): View
    {
        abort_unless($blogPost->isPubliclyAvailable(), 404);

        $metaDescription = $blogPost->meta_description ?: $blogPost->excerpt;
        $coverImageUrl = $blogPost->cover_image
            ? url(Storage::disk('public')->url($blogPost->cover_image))
            : null;
        $canonicalUrl = route('blog.show', $blogPost->slug);

        return view('blog.show', [
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
        ]);
    }
}
