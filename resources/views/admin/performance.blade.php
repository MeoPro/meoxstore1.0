@extends('admin.layout')

@section('title', 'Performance')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Performance</h1>
            <p>Sales and best-selling products for {{ $monthLabel }}.</p>
        </div>
        <span class="performance-period">
            <x-admin-icon name="calendar" />
            {{ $monthLabel }}
        </span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <section class="performance-stat-card gradient-primary">
                <div>
                    <p class="performance-stat-label">Paid sales</p>
                    <strong>Rp {{ number_format($salesRevenue, 0, ',', '.') }}</strong>
                    <span>Payments confirmed this month</span>
                </div>
                <span class="performance-stat-icon"><x-admin-icon name="sales" /></span>
            </section>
        </div>
        <div class="col-sm-6 col-xl-3">
            <section class="performance-stat-card gradient-success">
                <div>
                    <p class="performance-stat-label">Paid orders</p>
                    <strong>{{ number_format($paidOrderCount) }}</strong>
                    <span>Confirmed and completed orders</span>
                </div>
                <span class="performance-stat-icon"><x-admin-icon name="orders" /></span>
            </section>
        </div>
        <div class="col-sm-6 col-xl-3">
            <section class="performance-stat-card gradient-ocean">
                <div>
                    <p class="performance-stat-label">Average order value</p>
                    <strong>Rp {{ number_format($averageOrderValue, 0, ',', '.') }}</strong>
                    <span>Average per paid order</span>
                </div>
                <span class="performance-stat-icon"><x-admin-icon name="average" /></span>
            </section>
        </div>
        <div class="col-sm-6 col-xl-3">
            <section class="performance-stat-card gradient-sunset">
                <div>
                    <p class="performance-stat-label">Top product</p>
                    <strong>{{ number_format($topProductUnits) }} sold</strong>
                    <span>{{ $topProducts->first()->product_name ?? 'No paid sales yet' }}</span>
                </div>
                <span class="performance-stat-icon"><x-admin-icon name="trophy" /></span>
            </section>
        </div>
    </div>

    <section class="panel performance-products">
        <div class="performance-panel-heading">
            <div>
                <h2>Best-selling products</h2>
                <p>Ranked by units sold from payments confirmed in {{ $monthLabel }}.</p>
            </div>
            <span class="performance-count">{{ $topProducts->count() }} products</span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Rank</th>
                        <th scope="col">Product</th>
                        <th class="text-end" scope="col">Units sold</th>
                        <th class="text-end" scope="col">Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($topProducts as $product)
                        @php($unitsSold = (int) $product->units_sold)
                        @php($rankWidth = $topProductUnits > 0 ? ($unitsSold / $topProductUnits) * 100 : 0)
                        <tr>
                            <td><span class="performance-rank">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span></td>
                            <td>
                                <div class="performance-product">
                                    @if ($product->product?->image)
                                        <img class="product-thumb" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->product->image) }}" alt="">
                                    @else
                                        <span class="product-thumb performance-product-placeholder">{{ mb_strtoupper(mb_substr($product->product_name, 0, 1)) }}</span>
                                    @endif
                                    <div class="performance-product-copy">
                                        <strong>{{ $product->product_name }}</strong>
                                        <div class="performance-bar" aria-hidden="true">
                                            <span style="width: {{ number_format($rankWidth, 2, '.', '') }}%"></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end fw-semibold">{{ number_format($unitsSold) }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format((float) $product->product_revenue, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center py-5" colspan="4">
                                <div class="performance-empty-state">
                                    <x-admin-icon name="sales" />
                                    <strong>No paid sales yet</strong>
                                    <span>Product performance will appear here after a payment is confirmed.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
