<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createProductReview(array $reviewAttributes = []): ProductReview
{
    $product = Product::factory()->create(['name' => 'Review Product']);
    $user = User::factory()->create(['name' => 'Review Buyer']);
    $order = Order::query()->create([
        'user_id' => $user->id,
        'status' => 'completed',
        'total' => '12000.00',
    ]);

    return ProductReview::query()->create(array_merge([
        'product_id' => $product->id,
        'user_id' => $user->id,
        'order_id' => $order->id,
        'rating' => 4,
        'comment' => 'Good product review',
    ], $reviewAttributes));
}

function reviewReplyImage(): UploadedFile
{
    return UploadedFile::fake()->createWithContent(
        'reply.png',
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC'),
    );
}

it('lets an admin reply to a review with an image and makes the reply visible to buyers', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['is_admin' => true]);
    $review = createProductReview();

    $this->actingAs($admin)
        ->get(route('admin.reviews.index'))
        ->assertSee('Buyer Reviews')
        ->assertSee('Review Buyer')
        ->assertSee('Good product review')
        ->assertSee(route('admin.reviews.reply', $review), false);

    $this->patch(route('admin.reviews.reply', $review), [
        'admin_reply' => 'Thank you for your feedback!',
        'admin_reply_image' => reviewReplyImage(),
    ])->assertRedirect()
        ->assertSessionHas('success', __('store.review_reply_saved'));

    $review->refresh();
    expect($review->admin_reply)->toBe('Thank you for your feedback!')
        ->and($review->admin_reply_image)->not->toBeNull();
    Storage::disk('public')->assertExists($review->admin_reply_image);

    $this->get(route('product-details.show', $review->product))
        ->assertSee('Good product review')
        ->assertSee(__('store.store_reply'))
        ->assertSee('Thank you for your feedback!')
        ->assertSee(Storage::disk('public')->url($review->admin_reply_image), false);
});

it('lets an admin hide and restore a buyer review on public product pages', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $review = createProductReview();

    $this->actingAs($admin)
        ->patch(route('admin.reviews.visibility', $review), ['is_hidden' => '1'])
        ->assertRedirect()
        ->assertSessionHas('success', __('store.review_visibility_updated'));

    expect($review->fresh()->is_hidden)->toBeTrue();
    $this->get(route('product-details.show', $review->product))
        ->assertDontSee('Good product review')
        ->assertSee(__('store.review_count', ['count' => 0]));

    $this->patch(route('admin.reviews.visibility', $review), ['is_hidden' => '0'])
        ->assertRedirect();

    expect($review->fresh()->is_hidden)->toBeFalse();
    $this->get(route('product-details.show', $review->product))
        ->assertSee('Good product review');
});

it('deletes a review and its uploaded reply image for admins', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['is_admin' => true]);
    $imagePath = reviewReplyImage()->store('product-review-replies', 'public');
    $review = createProductReview(['admin_reply_image' => $imagePath]);

    $this->actingAs($admin)
        ->delete(route('admin.reviews.destroy', $review))
        ->assertRedirect()
        ->assertSessionHas('success', __('store.review_deleted'));

    $this->assertDatabaseMissing('product_reviews', ['id' => $review->id]);
    Storage::disk('public')->assertMissing($imagePath);
});

it('requires admin permissions and validates reply image uploads', function () {
    $review = createProductReview();

    $this->get(route('admin.reviews.index'))
        ->assertRedirectToRoute('login');

    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->patch(route('admin.reviews.reply', $review), ['admin_reply' => 'Unauthorized'])
        ->assertForbidden();

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->patch(route('admin.reviews.reply', $review), [
            'admin_reply' => 'Reply text',
            'admin_reply_image' => UploadedFile::fake()->create('reply.pdf', 10, 'application/pdf'),
        ])
        ->assertInvalid(['admin_reply_image']);

    expect($review->fresh()->admin_reply)->toBeNull();
});

it('shows only submitted-payment and paid orders in the in-queue list', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $pendingOrder = Order::query()->create(['status' => 'pending', 'total' => '1000.00']);
    $submittedOrder = Order::query()->create(['status' => 'payment_submitted', 'total' => '2000.00']);
    $confirmedOrder = Order::query()->create(['status' => 'payment_confirmed', 'total' => '3000.00']);
    $completedOrder = Order::query()->create(['status' => 'completed', 'total' => '4000.00']);
    $cancelledOrder = Order::query()->create(['status' => 'cancelled', 'total' => '5000.00']);

    $this->actingAs($admin)
        ->get(route('admin.orders.queue'))
        ->assertSee('In Queue Orders')
        ->assertSee('#'.$submittedOrder->id)
        ->assertSee('#'.$confirmedOrder->id)
        ->assertViewHas('orders', fn ($orders): bool => $orders->count() === 2
            && $orders->contains(fn ($order): bool => $order->is($confirmedOrder))
            && $orders->contains(fn ($order): bool => $order->is($submittedOrder)));
});
