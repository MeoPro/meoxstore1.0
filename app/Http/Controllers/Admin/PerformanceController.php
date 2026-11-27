<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\View\View;

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
            ->selectRaw("COUNT(*) AS paid_order_count, COALESCE(SUM(CASE WHEN source = 'cashier' THEN COALESCE(cashier_actual_revenue, total) ELSE total END), 0) AS sales_revenue")
            ->first();

        $topProducts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->select(['order_items.product_id', 'order_items.product_name'])
            ->selectRaw('SUM(COALESCE(order_items.robux_amount, order_items.quantity)) AS units_sold')
            ->selectRaw('SUM(CASE WHEN orders.source = ? THEN CASE WHEN orders.total > 0 THEN order_items.quantity * order_items.unit_price * COALESCE(orders.cashier_actual_revenue, orders.total) / orders.total ELSE 0 END ELSE order_items.quantity * order_items.unit_price END) AS product_revenue', ['cashier'])
            ->with('product:id,image')
            ->whereIn('orders.status', ['payment_confirmed', 'completed'])
            ->whereBetween('orders.payment_confirmed_at', [$monthStart, $periodEnd])
            ->groupBy('order_items.product_id', 'order_items.product_name')
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
