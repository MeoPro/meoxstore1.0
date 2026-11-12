<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $staticPages = [
            route('home'),
            route('shop'),
            route('games.index'),
            route('blog.index'),
        ];

        $blogPosts = BlogPost::published()
            ->latest('published_at')
            ->limit(50000)
            ->get(['slug', 'published_at', 'updated_at']);

        $blogCategories = BlogCategory::active()
            ->whereHas('blogPosts', fn ($query) => $query->published())
            ->get(['slug', 'updated_at']);

        return response()
            ->view('sitemap', compact('staticPages', 'blogPosts', 'blogCategories'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
