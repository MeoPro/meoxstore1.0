<?php

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogSidebarBlock;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('shows published articles with search metadata and safely rendered Markdown', function () {
    BlogPost::factory()->create([
        'title' => 'A published game guide <script>alert("title")</script>',
        'slug' => 'published-game-guide',
        'excerpt' => 'Helpful advice <img src=x onerror=alert(1)> for choosing a game account.',
        'body' => "## Check account details\n\n<script>alert('unsafe')</script>\n\n[unsafe link](javascript:alert('unsafe'))",
        'seo_title' => 'The best guide <script>alert("seo")</script>',
        'meta_description' => 'Review this <img src=x onerror=alert(2)> before buying.',
        'status' => 'published',
        'published_at' => now(),
    ]);
    BlogPost::factory()->create([
        'title' => 'A private draft',
        'slug' => 'private-draft',
    ]);
    BlogPost::factory()->create([
        'title' => 'A scheduled article',
        'slug' => 'scheduled-article',
        'status' => 'published',
        'published_at' => now()->addDay(),
    ]);

    $this->get(route('blog.posts'))
        ->assertOk()
        ->assertSee('A published game guide')
        ->assertDontSee('A private draft')
        ->assertDontSee('A scheduled article')
        ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
        ->assertDontSee('<img src=x onerror=alert(1)>', false);

    $this->get(route('blog.show', 'published-game-guide'))
        ->assertOk()
        ->assertSee('<title>The best guide &lt;script&gt;alert', false)
        ->assertSee('<article class="blog-post blog-post-detail">', false)
        ->assertSee('name="description" content="Review this &lt;img src=x onerror=alert(2)&gt;', false)
        ->assertSee('"@type":"BlogPosting"', false)
        ->assertSee('<h2>Check account details</h2>', false)
        ->assertDontSee('<script>alert(', false)
        ->assertDontSee('<img src=x onerror=alert(1)>', false)
        ->assertDontSee('href="javascript:', false);
});

it('searches published articles by title, excerpt, and body and shows an empty search state', function () {
    $titlePost = BlogPost::factory()->create([
        'title' => 'Title-only search match',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $excerptPost = BlogPost::factory()->create([
        'title' => 'Excerpt search match',
        'excerpt' => 'Distinctive excerpt phrase',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $bodyPost = BlogPost::factory()->create([
        'title' => 'Body search match',
        'body' => 'Distinctive body phrase',
        'status' => 'published',
        'published_at' => now(),
    ]);

    foreach ([
        'Title-only' => $titlePost,
        'Distinctive excerpt' => $excerptPost,
        'Distinctive body' => $bodyPost,
    ] as $query => $matchingPost) {
        $this->get(route('blog.posts', ['q' => $query]))
            ->assertOk()
            ->assertSee($matchingPost->title)
            ->assertSee('name="q" class="searchText" value="'.e($query).'"', false);
    }

    $this->get(route('blog.posts', ['q' => 'no matching articles']))
        ->assertOk()
        ->assertSee('<p role="status">'.__('store.search_no_results').'</p>', false);
});

it('hides drafts and future articles and lists public articles in the sitemap', function () {
    $publishedPost = BlogPost::factory()->create([
        'slug' => 'published-guide',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $draftPost = BlogPost::factory()->create(['slug' => 'draft-guide']);
    $futurePost = BlogPost::factory()->create([
        'slug' => 'future-guide',
        'status' => 'published',
        'published_at' => now()->addDay(),
    ]);

    $this->get(route('blog.show', $draftPost->slug))->assertNotFound();
    $this->get(route('blog.show', $futurePost->slug))->assertNotFound();

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee(route('blog.show', $publishedPost->slug), false)
        ->assertDontSee(route('blog.show', $draftPost->slug), false)
        ->assertDontSee(route('blog.show', $futurePost->slug), false);

    $this->get(route('robots'))
        ->assertOk()
        ->assertSee('Sitemap: '.route('sitemap'), false);
});

it('groups published articles by active category and serves category archives', function () {
    $category = BlogCategory::factory()->create([
        'name' => 'Game guides',
        'slug' => 'game-guides',
    ]);
    $otherCategory = BlogCategory::factory()->create([
        'name' => 'News',
        'slug' => 'news',
    ]);
    $post = BlogPost::factory()->create([
        'title' => 'A guide in the right category',
        'slug' => 'right-category-guide',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $post->categories()->attach($category);
    $otherPost = BlogPost::factory()->create([
        'title' => 'A different category article',
        'slug' => 'different-category-article',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $otherPost->categories()->attach($otherCategory);
    $inactiveCategory = BlogCategory::factory()->create([
        'name' => 'Hidden category',
        'slug' => 'hidden-category',
        'is_active' => false,
    ]);
    $hiddenPost = BlogPost::factory()->create([
        'title' => 'Draft in hidden category',
        'status' => 'draft',
    ]);
    $hiddenPost->categories()->attach($inactiveCategory);

    $this->get(route('blog.posts'))
        ->assertSee('Game guides')
        ->assertSee('A guide in the right category')
        ->assertSee('News')
        ->assertDontSee('Hidden category')
        ->assertDontSee('Draft in hidden category')
        ->assertSee('id="navbarResponsive"', false)
        ->assertSee('aria-controls="navbarResponsive"', false)
        ->assertSee('data-bs-toggle="collapse"', false)
        ->assertSee('href="'.route('blog.index').'"', false);

    $this->get(route('blog.categories.show', $category->slug))
        ->assertSee('class="blog-posts grid-system"', false)
        ->assertSee('A guide in the right category')
        ->assertDontSee('A different category article');

    $this->get(route('blog.categories.show', $inactiveCategory->slug))
        ->assertNotFound();

    $this->get(route('sitemap'))
        ->assertSee(route('blog.categories.show', $category->slug), false)
        ->assertDontSee(route('blog.categories.show', $inactiveCategory->slug), false);
});

it('shows published articles on the blog homepage and links to the entries list', function () {
    $oldestPost = BlogPost::factory()->create([
        'title' => 'An older featured article',
        'status' => 'published',
        'published_at' => now()->subDays(3),
    ]);
    $middlePost = BlogPost::factory()->create([
        'title' => 'A middle featured article',
        'status' => 'published',
        'published_at' => now()->subDays(2),
    ]);
    $newestPost = BlogPost::factory()->create([
        'title' => 'The newest featured article',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    BlogPost::factory()->create([
        'title' => 'A draft that must not be featured',
    ]);

    $response = $this->get(route('blog.index'))
        ->assertSee('class="owl-banner owl-carousel"', false)
        ->assertSee(route('blog.show', $newestPost->slug), false)
        ->assertSee(route('blog.posts'), false)
        ->assertSee(route('locale.update'), false)
        ->assertDontSee('Post Details')
        ->assertDontSee('A draft that must not be featured');

    $content = $response->getContent();
    $carouselStart = strpos($content, 'class="owl-banner owl-carousel"');
    $callToActionStart = strpos($content, 'class="call-to-action"', $carouselStart);
    $articleListStart = strpos($content, 'class="all-blog-posts"');
    $carouselContent = substr($content, $carouselStart, $callToActionStart - $carouselStart);
    $callToAction = substr($content, $callToActionStart, $articleListStart - $callToActionStart);
    $articleList = substr($content, $articleListStart);

    expect(strpos($carouselContent, $newestPost->title))
        ->toBeLessThan(strpos($carouselContent, $middlePost->title))
        ->toBeLessThan(strpos($carouselContent, $oldestPost->title));

    expect($callToAction)->toContain(route('blog.posts'));

    expect(strpos($articleList, $newestPost->title))
        ->toBeLessThan(strpos($articleList, $middlePost->title))
        ->toBeLessThan(strpos($articleList, $oldestPost->title));
});

it('switches an article to the selected language and exposes account actions in the blog navigation', function () {
    $translationGroupId = (string) Str::uuid();
    $sourcePost = BlogPost::factory()->create([
        'title' => 'Panduan akun game',
        'slug' => 'panduan-akun-game',
        'translation_group_id' => $translationGroupId,
        'locale' => 'id',
        'source_locale' => 'id',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $englishPost = BlogPost::factory()->create([
        'title' => 'Game account guide',
        'slug' => 'game-account-guide-en',
        'translation_group_id' => $translationGroupId,
        'locale' => 'en',
        'source_locale' => 'id',
        'status' => 'published',
        'published_at' => $sourcePost->published_at,
    ]);
    BlogPost::factory()->create([
        'title' => 'Panduan akaun game',
        'slug' => 'panduan-akaun-game-ms',
        'translation_group_id' => $translationGroupId,
        'locale' => 'ms',
        'source_locale' => 'id',
        'status' => 'published',
        'published_at' => $sourcePost->published_at,
    ]);
    $legacyPost = BlogPost::factory()->create([
        'title' => 'Artikel lama bahasa Indonesia',
        'slug' => 'artikel-lama-indonesia',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->post(route('locale.update'), ['locale' => 'en']);

    $this->withSession(['locale' => 'en'])
        ->get(route('blog.posts'))
        ->assertSee('Game account guide')
        ->assertDontSee('Panduan akaun game')
        ->assertSee($legacyPost->title);

    $this->withSession(['locale' => 'en'])
        ->get(route('blog.show', $sourcePost->slug))
        ->assertRedirectToRoute('blog.show', $englishPost->slug);

    $this->get(route('blog.show', $legacyPost->slug))
        ->assertSee($legacyPost->title);

    $this->actingAs(User::factory()->create())
        ->get(route('blog.index'))
        ->assertSee(route('profile.edit'), false)
        ->assertSee('action="'.route('logout').'"', false)
        ->assertSee(route('locale.update'), false)
        ->assertDontSee('Post Details');

    $this->get(route('blog.show', $englishPost->slug))
        ->assertSee('Game account guide')
        ->assertSee('action="'.route('logout').'"', false);
});

it('paginates published articles that have no active category', function () {
    BlogPost::factory()->create([
        'title' => 'The oldest uncategorized article',
        'slug' => 'oldest-uncategorized-article',
        'status' => 'published',
        'published_at' => now()->subDays(8),
    ]);

    foreach (range(1, 6) as $day) {
        BlogPost::factory()->create([
            'title' => 'Recent uncategorized article '.$day,
            'status' => 'published',
            'published_at' => now()->subDays($day),
        ]);
    }

    $this->get(route('blog.posts'))
        ->assertSee('Recent uncategorized article 1')
        ->assertSee('Recent uncategorized article 6')
        ->assertDontSee('The oldest uncategorized article');

    $this->get(route('blog.posts', ['page' => 2]))
        ->assertSee('The oldest uncategorized article')
        ->assertDontSee('Recent uncategorized article 6');
});

it('renders active sidebar blocks in configured order on blog lists and articles', function () {
    Storage::fake('public');
    $category = BlogCategory::factory()->create([
        'name' => 'Featured stories',
        'slug' => 'featured-stories',
    ]);
    $post = BlogPost::factory()->create([
        'title' => 'Sidebar article',
        'slug' => 'sidebar-article',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $post->categories()->attach($category);
    $product = Product::factory()->create(['name' => 'Featured catalog item']);
    $posterPath = 'blog-sidebar/test-poster.png';
    Storage::disk('public')->put($posterPath, 'poster image');

    BlogSidebarBlock::factory()->create([
        'type' => BlogSidebarBlock::TYPE_POSTER,
        'title' => 'Poster promotion',
        'sort_order' => 1,
        'image_path' => $posterPath,
        'link_url' => 'https://example.com/promotion',
    ]);
    BlogSidebarBlock::factory()->create([
        'type' => BlogSidebarBlock::TYPE_BLOG_CATEGORIES,
        'title' => 'Blog topics menu',
        'sort_order' => 2,
    ]);
    BlogSidebarBlock::factory()->create([
        'type' => BlogSidebarBlock::TYPE_PRODUCT,
        'title' => 'Product spotlight',
        'sort_order' => 3,
        'product_id' => $product->id,
    ]);
    BlogSidebarBlock::factory()->create([
        'type' => BlogSidebarBlock::TYPE_GAME_CATEGORIES,
        'title' => 'Game menu',
        'sort_order' => 4,
    ]);
    BlogSidebarBlock::factory()->create([
        'type' => BlogSidebarBlock::TYPE_ITEM_CATEGORIES,
        'title' => 'Item menu',
        'sort_order' => 5,
    ]);
    BlogSidebarBlock::factory()->create([
        'type' => BlogSidebarBlock::TYPE_POSTER,
        'title' => 'Disabled promotion',
        'sort_order' => 0,
        'is_active' => false,
        'image_path' => $posterPath,
    ]);

    $response = $this->get(route('blog.posts'))
        ->assertSee('Poster promotion')
        ->assertSee('Blog topics menu')
        ->assertSee('Product spotlight')
        ->assertSee('Featured catalog item')
        ->assertSee('Game menu')
        ->assertSee('Item menu')
        ->assertSee($category->name)
        ->assertSee(route('shop', ['game' => $product->game_category_id]), false)
        ->assertSee(route('shop', ['item_category' => $product->item_category_id]), false)
        ->assertDontSee('Disabled promotion');

    $content = $response->getContent();
    expect(strpos($content, 'Poster promotion'))->toBeLessThan(strpos($content, 'Blog topics menu'))
        ->and(strpos($content, 'Blog topics menu'))->toBeLessThan(strpos($content, 'Product spotlight'))
        ->and(strpos($content, 'Product spotlight'))->toBeLessThan(strpos($content, 'Game menu'))
        ->and(strpos($content, 'Game menu'))->toBeLessThan(strpos($content, 'Item menu'));

    $this->get(route('blog.show', $post->slug))
        ->assertSee('Poster promotion')
        ->assertSee('Featured catalog item')
        ->assertSee($category->name);

    $product->update(['status' => 'inactive']);
    $this->get(route('blog.posts'))
        ->assertDontSee('Product spotlight');
});
