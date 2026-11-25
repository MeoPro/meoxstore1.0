<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class PerformanceController extends Controller
{
    public function __invoke(): View
    {
        $monthStart = now()->startOfMonth();
        $periodEnd = now();

        $paidOrders = Order::query()
            ->whereIn('status', ['payment_confirmed', 'completed'])
            ->whereBetween('payment_confirmed_at', [$monthStart, $periodEnd]);

        $salesSummary = (clone $paidOrders)
            ->selectRaw('COUNT(*) AS paid_order_count, COALESCE(SUM(total), 0) AS sales_revenue')
            ->first();

        $topProducts = OrderItem::query()
            ->select(['product_id', 'product_name'])
            ->selectRaw('SUM(quantity) AS units_sold, SUM(quantity * unit_price) AS product_revenue')
            ->with('product:id,image')
            ->whereHas('order', fn (Builder $orders) => $orders
                ->whereIn('status', ['payment_confirmed', 'completed'])
                ->whereBetween('payment_confirmed_at', [$monthStart, $periodEnd]))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('units_sold')
            ->orderByDesc('product_revenue')
            ->limit(5)
            ->get();

        $salesRevenue = (float) $salesSummary->sales_revenue;
        $paidOrderCount = (int) $salesSummary->paid_order_count;

        return view('admin.performance', [
            'monthLabel' => $monthStart->translatedFormat('F Y'),
            'salesRevenue' => $salesRevenue,
            'paidOrderCount' => $paidOrderCount,
            'averageOrderValue' => $paidOrderCount > 0 ? $salesRevenue / $paidOrderCount : 0,
            'topProducts' => $topProducts,
            'topProductUnits' => (int) ($topProducts->first()->units_sold ?? 0),
        ]);
    }
}
