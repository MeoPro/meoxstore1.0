<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($staticPages as $url)
        <url>
            <loc>{{ $url }}</loc>
            <lastmod>{{ now()->toAtomString() }}</lastmod>
        </url>
    @endforeach
    @foreach ($blogPosts as $blogPost)
        <url>
            <loc>{{ route('blog.show', $blogPost->slug) }}</loc>
            <lastmod>{{ $blogPost->updated_at->toAtomString() }}</lastmod>
        </url>
    @endforeach
    @foreach ($blogCategories as $blogCategory)
        <url>
            <loc>{{ route('blog.categories.show', $blogCategory->slug) }}</loc>
            <lastmod>{{ $blogCategory->updated_at->toAtomString() }}</lastmod>
        </url>
    @endforeach
</urlset>
