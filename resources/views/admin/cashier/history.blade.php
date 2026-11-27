@extends('admin.layout')

@section('title', 'Cashier history')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Cashier history</h1>
            <p>Edit or remove the actual revenue amount without changing sold counts or reviews.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('admin.cashier.index') }}">New cashier sale</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <section class="panel p-4">
        <p class="small text-muted mb-3">Removing an entry hides it from this history and excludes its revenue from sales totals. The original order, product quantities sold, and five-star reviews remain saved.</p>

        @if ($orders->isEmpty())
            <p class="text-muted text-center py-4 mb-0">No cashier sales in this history.</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Order</th>
                            <th scope="col">Products</th>
                            <th class="text-end" scope="col">Original total</th>
                            <th scope="col">Actual revenue</th>
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td class="text-nowrap">
                                    <strong>#{{ $order->id }}</strong>
                                    <span class="d-block text-muted small">{{ $order->created_at->format('M j, Y H:i') }}</span>
                                </td>
                                <td>
                                    @foreach ($order->items as $item)
                                        <span class="d-block">{{ $item->product_name }} — {{ $item->variant_name }} <span class="text-muted">× {{ number_format($item->quantity) }}</span></span>
                                    @endforeach
                                </td>
                                <td class="text-end text-nowrap">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td>
                                <td>
                                    <form class="d-flex gap-2" method="POST" action="{{ route('admin.cashier.revenue.update', $order) }}">
                                        @csrf
                                        @method('PATCH')
                                        <label class="visually-hidden" for="actual-revenue-{{ $order->id }}">Actual revenue for order {{ $order->id }}</label>
                                        <div class="input-group input-group-sm" style="min-width: 190px;">
                                            <span class="input-group-text">Rp</span>
                                            <input
                                                class="form-control"
                                                id="actual-revenue-{{ $order->id }}"
                                                name="actual_revenue"
                                                type="number"
                                                min="0"
                                                max="9999999999.99"
                                                step="0.01"
                                                value="{{ old('actual_revenue', $order->cashier_actual_revenue) }}"
                                                required
                                            >
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('admin.cashier.destroy', $order) }}" onsubmit="return confirm('Remove this entry from cashier history? Its reviews and sold quantities will stay.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $orders->links() }}
        @endif
    </section>
@endsection
