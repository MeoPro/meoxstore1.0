<?php

use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\BlogSidebarBlockController;
use App\Http\Controllers\Admin\GameCategoryController;
use App\Http\Controllers\Admin\GoogleBusinessProfileController;
use App\Http\Controllers\Admin\ItemCategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentSettingsController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductCurrencyRateController;
use App\Http\Controllers\Admin\ProductReviewController as AdminProductReviewController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Admin\StoreStatusController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\GameCatalogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\ProductDetailsController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StoreReviewController;
use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/blog', [BlogController::class, 'home'])->name('blog.index');
Route::get('/blog/posts', [BlogController::class, 'index'])->name('blog.posts');
Route::get('/blog/category/{blogCategory:slug}', [BlogController::class, 'category'])->name('blog.categories.show');
Route::get('/blog/{blogPost:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', function (): Response {
    return response(
        "User-agent: *\nDisallow:\nSitemap: ".route('sitemap')."\n",
        200,
        ['Content-Type' => 'text/plain; charset=UTF-8'],
    );
})->name('robots');

Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/games', [GameCatalogController::class, 'index'])->name('games.index');
Route::get('/reviews', [StoreReviewController::class, 'index'])->name('reviews.index');
Route::get('/product-details', [ProductDetailsController::class, 'index'])->name('product-details');
Route::get('/product-details/{product}', [ProductDetailsController::class, 'show'])->name('product-details.show');
Route::view('/about', 'about')->name('about');
Route::view('/contact', 'contact')->name('contact');
Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');
Route::post('/telegram/webhook', TelegramWebhookController::class)->name('telegram.webhook');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
Route::patch('/cart/items/{variant}', [CartController::class, 'update'])->name('cart.items.update');
Route::patch('/cart/items/{variant}/details', [CartController::class, 'updateDetails'])->name('cart.items.details.update');
Route::post('/cart/items/selected/destroy', [CartController::class, 'destroySelected'])->name('cart.items.selected.destroy');
Route::delete('/cart/items/{variantId}', [CartController::class, 'destroy'])->whereNumber('variantId')->name('cart.items.destroy');
Route::post('/cart/checkout', [CheckoutController::class, 'fromCart'])->name('cart.checkout');
Route::post('/checkout/direct', [CheckoutController::class, 'direct'])->name('checkout.direct');
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::post('/orders/{order}/payment-proof', [OrderController::class, 'submitPaymentProof'])->name('orders.payment-proof');
Route::get('/order-tracking', [OrderTrackingController::class, 'index'])->name('orders.lookup');
Route::post('/order-tracking', [OrderTrackingController::class, 'lookup'])->middleware('throttle:5,1')->name('orders.lookup.submit');
Route::post('/orders/{order}/complete', [OrderTrackingController::class, 'complete'])->name('orders.complete');
Route::post('/orders/{order}/products/{product}/reviews', [OrderTrackingController::class, 'storeReview'])->name('orders.reviews.store');

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:5,1')->name('register.store');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('profile.settings');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store'])->name('products.reviews.store');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::resource('blog-posts', BlogPostController::class)->except(['show']);
    Route::resource('blog-categories', BlogCategoryController::class)->except(['show']);
    Route::resource('blog-sidebar-blocks', BlogSidebarBlockController::class)->except(['show']);
    Route::patch('store-status', [StoreStatusController::class, 'update'])->name('store-status.update');
    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/queue', [AdminOrderController::class, 'queue'])->name('orders.queue');
    Route::post('orders/bulk-status', [AdminOrderController::class, 'bulkUpdateStatus'])->name('orders.bulk-status');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::patch('orders/{order}/delivery-details', [AdminOrderController::class, 'updateDeliveryDetails'])
        ->name('orders.delivery-details');
    Route::get('orders/{order}/payment-proof', [AdminOrderController::class, 'paymentProof'])->name('orders.payment-proof');
    Route::get('reviews', [AdminProductReviewController::class, 'index'])->name('reviews.index');
    Route::get('rates', [ProductCurrencyRateController::class, 'index'])->name('rates.index');
    Route::patch('rates', [ProductCurrencyRateController::class, 'update'])->name('rates.update');
    Route::patch('reviews/{review}/reply', [AdminProductReviewController::class, 'reply'])->name('reviews.reply');
    Route::patch('reviews/{review}/visibility', [AdminProductReviewController::class, 'visibility'])->name('reviews.visibility');
    Route::delete('reviews/{review}', [AdminProductReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('payment-settings/bulk', [PaymentSettingsController::class, 'bulk'])->name('payment-settings.bulk');
    Route::resource('payment-settings', PaymentSettingsController::class)->except(['show']);
    Route::resource('social-links', SocialLinkController::class)->except(['show']);
    Route::get('google-business-profile', [GoogleBusinessProfileController::class, 'index'])
        ->name('google-business-profile.index');
    Route::get('google-business-profile/authorize', [GoogleBusinessProfileController::class, 'authorize'])
        ->name('google-business-profile.authorize');
    Route::get('google-business-profile/callback', [GoogleBusinessProfileController::class, 'callback'])
        ->name('google-business-profile.callback');
    Route::put('google-business-profile/location', [GoogleBusinessProfileController::class, 'updateLocation'])
        ->name('google-business-profile.location.update');
    Route::delete('google-business-profile', [GoogleBusinessProfileController::class, 'disconnect'])
        ->name('google-business-profile.disconnect');
    Route::get('game-categories/{game_category}/item-categories', [GameCategoryController::class, 'itemCategories'])
        ->name('game-categories.item-categories');
    Route::post('game-categories/bulk', [GameCategoryController::class, 'bulk'])->name('game-categories.bulk');
    Route::delete('game-categories/{game_category}', [GameCategoryController::class, 'destroy'])->name('game-categories.destroy');
    Route::patch('game-categories/{game_category}/status', [GameCategoryController::class, 'toggleStatus'])
        ->name('game-categories.status');
    Route::resource('game-categories', GameCategoryController::class)->except(['destroy']);

    Route::post('item-categories/bulk', [ItemCategoryController::class, 'bulk'])->name('item-categories.bulk');
    Route::delete('item-categories/{item_category}', [ItemCategoryController::class, 'destroy'])->name('item-categories.destroy');
    Route::patch('item-categories/{item_category}/status', [ItemCategoryController::class, 'toggleStatus'])
        ->name('item-categories.status');
    Route::resource('item-categories', ItemCategoryController::class)->except(['destroy']);

    Route::post('products/bulk', [ProductController::class, 'bulk'])->name('products.bulk');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::patch('products/{product}/status', [ProductController::class, 'toggleStatus'])
        ->name('products.status');
    Route::patch('products/{product}/variant-prices', [ProductController::class, 'updateVariantPrices'])
        ->name('products.variant-prices');
    Route::resource('products', ProductController::class)->except(['destroy']);
});
