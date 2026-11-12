<?php

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertSee('A published game guide')
        ->assertDontSee('A private draft')
        ->assertDontSee('A scheduled article')
        ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
        ->assertDontSee('<img src=x onerror=alert(1)>', false);

    $this->get(route('blog.show', 'published-game-guide'))
        ->assertOk()
        ->assertSee('<title>The best guide &lt;script&gt;alert', false)
        ->assertSee('name="description" content="Review this &lt;img src=x onerror=alert(2)&gt;', false)
        ->assertSee('"@type":"BlogPosting"', false)
        ->assertSee('<h2>Check account details</h2>', false)
        ->assertDontSee('<script>alert(', false)
        ->assertDontSee('<img src=x onerror=alert(1)>', false)
        ->assertDontSee('href="javascript:', false);
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
