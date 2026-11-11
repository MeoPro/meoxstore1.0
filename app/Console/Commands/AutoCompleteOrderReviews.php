<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\ProductReview;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoCompleteOrderReviews extends Command
{
    protected $signature = 'app:auto-complete-order-reviews';

    protected $description = 'Add a default five-star review when a completed order has no review after 24 hours';

    public function handle(): int
    {
        $reviewDeadline = now()->subDay();
        $createdReviews = 0;

        Order::query()
            ->where('status', 'completed')
            ->whereNotNull('customer_completed_at')
            ->where('customer_completed_at', '<=', $reviewDeadline)
            ->select('id')
            ->chunkById(100, function ($orders) use (&$createdReviews): void {
                foreach ($orders as $order) {
                    $createdReviews += DB::transaction(function () use ($order): int {
                        $lockedOrder = Order::query()->with('items')->lockForUpdate()->find($order->id);
                        if (
                            $lockedOrder === null
                            || $lockedOrder->status !== 'completed'
                            || $lockedOrder->customer_completed_at === null
                            || $lockedOrder->customer_completed_at->gt(now()->subDay())
                        ) {
                            return 0;
                        }

                        $created = 0;
                        foreach ($lockedOrder->items->unique('product_id') as $item) {
                            if (
                                $item->product_id === null
                                || ProductReview::query()
                                    ->where('order_id', $lockedOrder->id)
                                    ->where('product_id', $item->product_id)
                                    ->exists()
                            ) {
                                continue;
                            }

                            ProductReview::query()->create([
                                'product_id' => $item->product_id,
                                'user_id' => null,
                                'order_id' => $lockedOrder->id,
                                'rating' => 5,
                            ]);
                            $created++;
                        }

                        return $created;
                    });
                }
            });

        $this->info("Created {$createdReviews} default five-star review(s).");

        return self::SUCCESS;
    }
}
