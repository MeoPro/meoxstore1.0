<?php

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('protects blog category management from guests and non-admins', function () {
    $this->get(route('admin.blog-categories.index'))
        ->assertRedirectToRoute('login');

    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->get(route('admin.blog-categories.index'))
        ->assertForbidden();
});

it('creates and updates blog categories with stable generated slugs', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));

    $this->post(route('admin.blog-categories.store'), [
        'name' => 'Gaming News',
        'description' => 'News for gamers.',
        'sort_order' => 3,
        'is_active' => '1',
    ])->assertRedirectToRoute('admin.blog-categories.index');

    $category = BlogCategory::query()->where('name', 'Gaming News')->firstOrFail();
    expect($category->slug)->toBe('gaming-news')
        ->and($category->is_active)->toBeTrue();

    $this->put(route('admin.blog-categories.update', $category), [
        'name' => 'Game Updates',
        'description' => '',
        'sort_order' => 1,
        'is_active' => '0',
    ])->assertRedirectToRoute('admin.blog-categories.index');

    expect($category->fresh()->name)->toBe('Game Updates')
        ->and($category->fresh()->slug)->toBe('gaming-news')
        ->and($category->fresh()->is_active)->toBeFalse()
        ->and($category->fresh()->sort_order)->toBe(1);
});

it('deletes a category without deleting its articles', function () {
    $category = BlogCategory::factory()->create();
    $blogPost = BlogPost::factory()->create();
    $blogPost->categories()->attach($category);

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->delete(route('admin.blog-categories.destroy', $category))
        ->assertRedirectToRoute('admin.blog-categories.index');

    $this->assertDatabaseMissing('blog_categories', ['id' => $category->id]);
    $this->assertModelExists($blogPost);
    $this->assertDatabaseMissing('blog_category_blog_post', [
        'blog_category_id' => $category->id,
        'blog_post_id' => $blogPost->id,
    ]);
});
