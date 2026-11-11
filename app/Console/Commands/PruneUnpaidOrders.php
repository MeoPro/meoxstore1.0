<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneUnpaidOrders extends Command
{
    protected $signature = 'app:prune-unpaid-orders';

    protected $description = 'Delete unpaid orders older than 24 hours and restore reserved stock';

    public function handle(): int
    {
        $expiredBefore = now()->subDay();
        $deletedOrders = 0;

        Order::query()
            ->where('status', 'pending')
            ->whereNull('payment_proof_path')
            ->where('created_at', '<=', $expiredBefore)
            ->select('id')
            ->chunkById(100, function ($orders) use ($expiredBefore, &$deletedOrders): void {
                foreach ($orders as $order) {
                    $wasDeleted = DB::transaction(function () use ($order, $expiredBefore): bool {
                        $lockedOrder = Order::query()
                            ->with('items')
                            ->lockForUpdate()
                            ->find($order->id);

                        if (
                            $lockedOrder === null
                            || $lockedOrder->status !== 'pending'
                            || $lockedOrder->payment_proof_path !== null
                            || $lockedOrder->created_at->gt($expiredBefore)
                        ) {
                            return false;
                        }

                        foreach ($lockedOrder->items as $item) {
                            if ($item->product_variant_id !== null) {
                                $item->variant()->lockForUpdate()->first()?->increment('stock', $item->quantity);
                            }
                        }

                        $lockedOrder->delete();

                        return true;
                    });

                    if ($wasDeleted) {
                        $deletedOrders++;
                    }
                }
            });

        $this->info("Deleted {$deletedOrders} expired unpaid order(s).");

        return self::SUCCESS;
    }
}
