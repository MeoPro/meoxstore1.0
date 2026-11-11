<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductReviewController extends Controller
{
    public function index(): View
    {
        return view('admin.reviews.index', [
            'reviews' => ProductReview::query()
                ->with(['product:id,name', 'user:id,name,email', 'order:id,public_number'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function reply(Request $request, ProductReview $review): RedirectResponse
    {
        $validated = $request->validate([
            'admin_reply' => ['nullable', 'string', 'max:2000'],
            'admin_reply_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_admin_reply_image' => ['nullable', 'boolean'],
        ]);

        $oldImage = $review->admin_reply_image;
        $newImage = $request->file('admin_reply_image')?->store('product-review-replies', 'public');

        $review->update([
            'admin_reply' => $validated['admin_reply'] ?? null,
            'admin_reply_image' => $newImage
                ?? (($validated['remove_admin_reply_image'] ?? false) ? null : $oldImage),
        ]);

        if ($oldImage !== null && ($newImage !== null || ($validated['remove_admin_reply_image'] ?? false))) {
            Storage::disk('public')->delete($oldImage);
        }

        return back()->with('success', __('store.review_reply_saved'));
    }

    public function visibility(Request $request, ProductReview $review): RedirectResponse
    {
        $validated = $request->validate([
            'is_hidden' => ['required', 'boolean'],
        ]);

        $review->update([
            'is_hidden' => $validated['is_hidden'],
        ]);

        return back()->with('success', __('store.review_visibility_updated'));
    }

    public function destroy(ProductReview $review): RedirectResponse
    {
        $replyImage = $review->admin_reply_image;
        $review->delete();

        if ($replyImage !== null) {
            Storage::disk('public')->delete($replyImage);
        }

        return back()->with('success', __('store.review_deleted'));
    }
}
