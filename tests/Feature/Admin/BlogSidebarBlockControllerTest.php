<?php

use App\Models\BlogSidebarBlock;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('protects blog sidebar management from guests and non-admins', function () {
    $this->get(route('admin.blog-sidebar-blocks.index'))
        ->assertRedirectToRoute('login');

    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->post(route('admin.blog-sidebar-blocks.store'))
        ->assertForbidden();
});

it('creates category menu blocks and rejects inactive products', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));

    $this->post(route('admin.blog-sidebar-blocks.store'), [
        'type' => BlogSidebarBlock::TYPE_BLOG_CATEGORIES,
        'title' => 'Article categories',
        'sort_order' => 4,
        'is_active' => '1',
    ])->assertRedirectToRoute('admin.blog-sidebar-blocks.index');

    $inactiveProduct = Product::factory()->create(['status' => 'inactive']);
    $this->post(route('admin.blog-sidebar-blocks.store'), [
        'type' => BlogSidebarBlock::TYPE_PRODUCT,
        'title' => 'Featured product',
        'product_id' => $inactiveProduct->id,
        'sort_order' => 1,
        'is_active' => '1',
    ])->assertInvalid(['product_id']);

    expect(BlogSidebarBlock::query()->count())->toBe(1)
        ->and(BlogSidebarBlock::query()->firstOrFail()->type)->toBe(BlogSidebarBlock::TYPE_BLOG_CATEGORIES);
});

it('stores poster images and removes them when the block changes type', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['is_admin' => true]));

    $image = UploadedFile::fake()->createWithContent(
        'poster.png',
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lscAAAAASUVORK5CYII='),
    );

    $this->post(route('admin.blog-sidebar-blocks.store'), [
        'type' => BlogSidebarBlock::TYPE_POSTER,
        'title' => 'Summer promotion',
        'sort_order' => 2,
        'is_active' => '1',
        'link_url' => 'https://example.com/promotion',
        'image' => $image,
    ])->assertRedirectToRoute('admin.blog-sidebar-blocks.index');

    $block = BlogSidebarBlock::query()->firstOrFail();
    $posterPath = $block->image_path;
    Storage::disk('public')->assertExists($posterPath);

    $this->put(route('admin.blog-sidebar-blocks.update', $block), [
        'type' => BlogSidebarBlock::TYPE_POSTER,
        'title' => 'Updated promotion',
        'sort_order' => 3,
        'is_active' => '1',
        'link_url' => 'javascript:alert(1)',
    ])->assertInvalid(['link_url']);

    Storage::disk('public')->assertExists($posterPath);

    $this->put(route('admin.blog-sidebar-blocks.update', $block), [
        'type' => BlogSidebarBlock::TYPE_BLOG_CATEGORIES,
        'title' => 'News categories',
        'sort_order' => 3,
        'is_active' => '1',
    ])->assertRedirectToRoute('admin.blog-sidebar-blocks.index');

    expect($block->fresh()->image_path)->toBeNull();
    Storage::disk('public')->assertMissing($posterPath);
});
