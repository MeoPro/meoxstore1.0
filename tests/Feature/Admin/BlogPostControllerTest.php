<?php

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('redirects guests away from blog post management', function () {
    $this->get(route('admin.blog-posts.index'))
        ->assertRedirectToRoute('login');

    $this->post(route('admin.blog-posts.store'))
        ->assertRedirectToRoute('login');
});

it('forbids non-admins from managing blog posts', function () {
    $this->actingAs(User::factory()->create(['is_admin' => false]));

    $this->get(route('admin.blog-posts.index'))->assertForbidden();
    $this->post(route('admin.blog-posts.store'))->assertForbidden();

    expect(BlogPost::query()->count())->toBe(0);
});

it('creates and updates blog posts with a cover image', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin);

    $this->post(route('admin.blog-posts.store'), [
        'title' => 'A guide to game accounts',
        'excerpt' => 'Learn what to check before buying a game account.',
        'body' => "## Check account details\n\nReview the account information carefully.",
        'status' => 'published',
        'cover_image' => UploadedFile::fake()->createWithContent(
            'cover.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lscAAAAASUVORK5CYII='),
        ),
    ])->assertRedirectToRoute('admin.blog-posts.index');

    $blogPost = BlogPost::query()->where('title', 'A guide to game accounts')->firstOrFail();
    expect($blogPost->slug)->toBe('a-guide-to-game-accounts')
        ->and($blogPost->status)->toBe('published')
        ->and($blogPost->published_at)->not->toBeNull();
    Storage::disk('public')->assertExists($blogPost->cover_image);

    $this->post(route('admin.blog-posts.store'), [
        'title' => 'A guide to game accounts',
        'excerpt' => 'A second summary.',
        'body' => 'A second article.',
        'status' => 'draft',
    ])->assertRedirectToRoute('admin.blog-posts.index');

    $duplicatePost = BlogPost::query()
        ->where('title', 'A guide to game accounts')
        ->where('id', '!=', $blogPost->id)
        ->firstOrFail();
    expect($duplicatePost->slug)->toBe('a-guide-to-game-accounts-2');

    $this->put(route('admin.blog-posts.update', $blogPost), [
        'title' => 'A safer guide to game accounts',
        'slug' => 'safer-game-account-guide',
        'excerpt' => 'Updated buying guidance.',
        'body' => 'Review the account details before buying.',
        'status' => 'draft',
    ])->assertRedirectToRoute('admin.blog-posts.index');

    expect($blogPost->fresh()->slug)->toBe('safer-game-account-guide')
        ->and($blogPost->fresh()->status)->toBe('draft')
        ->and($blogPost->fresh()->published_at)->toBeNull();
    Storage::disk('public')->assertExists($blogPost->cover_image);
});

it('rejects duplicate slugs and invalid cover images without creating a post', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    BlogPost::factory()->create(['slug' => 'account-guide']);

    $this->post(route('admin.blog-posts.store'), [
        'title' => 'Another account guide',
        'slug' => 'account-guide',
        'excerpt' => 'A short excerpt.',
        'body' => 'Some article content.',
        'status' => 'draft',
        'cover_image' => UploadedFile::fake()->create('cover.pdf', 20, 'application/pdf'),
    ])->assertInvalid(['slug', 'cover_image']);

    expect(BlogPost::query()->count())->toBe(1);
});

it('deletes blog posts and their cover images', function () {
    Storage::fake('public');
    $coverImage = 'blog-covers/account-guide.png';
    Storage::disk('public')->put($coverImage, 'image content');
    $blogPost = BlogPost::factory()->create(['cover_image' => $coverImage]);

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->delete(route('admin.blog-posts.destroy', $blogPost))
        ->assertRedirectToRoute('admin.blog-posts.index');

    $this->assertDatabaseMissing('blog_posts', ['id' => $blogPost->id]);
    Storage::disk('public')->assertMissing($coverImage);
});
