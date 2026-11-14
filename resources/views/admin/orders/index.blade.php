@extends('admin.layout')

@section('title', 'Orders')

@section('content')
    <div class="page-heading"><div><h1>{{ $queuedOnly ? 'In Queue Orders' : 'Orders' }}</h1></div></div>
    <style>
        .order-row { cursor: pointer; }
        .order-row:focus { outline: 2px solid #80a5a2; outline-offset: -2px; }
        .order-status-form { min-width: 205px; position: relative; z-index: 1; }
    </style>
    <form id="bulk-orders-form" class="align-items-end d-flex flex-wrap gap-2 mb-3" method="POST" action="{{ route('admin.orders.bulk-status') }}">
        @csrf
        <div>
            <label class="form-label mb-1" for="bulk-order-status">Change selected orders to</label>
            <select class="form-select form-select-sm" id="bulk-order-status" name="status" required>
                <option value="" selected disabled>Choose status</option>
                @foreach (['payment_confirmed', 'completed', 'cancelled'] as $status)
                    <option value="{{ $status }}">{{ __('store.status_'.$status) }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-sm btn-accent" type="submit">Update selected</button>
        @if ($errors->has('status') || $errors->has('ids'))
            <span class="text-danger small">{{ $errors->first('status') ?: $errors->first('ids') }}</span>
        @endif
    </form>
    <div class="panel overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th><input class="form-check-input" id="select-all-orders" type="checkbox" aria-label="Select all orders on this page"></th><th>Order</th><th>Customer</th><th>Items</th><th>Total</th><th>Proof</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($orders as $order)
                    <tr
                        class="order-row"
                        role="link"
                        tabindex="0"
                        aria-label="Open order #{{ $order->id }}"
                        data-order-url="{{ route('admin.orders.show', $order) }}"
                        onclick="if (!event.target.closest('a, button, form, input, select, textarea')) window.location.href = this.dataset.orderUrl;"
                        onkeydown="if (event.key === 'Enter' && !event.target.closest('a, button, form, input, select, textarea')) window.location.href = this.dataset.orderUrl;"
                    >
                        <td><input class="form-check-input order-selection" form="bulk-orders-form" type="checkbox" name="ids[]" value="{{ $order->id }}" aria-label="Select order #{{ $order->id }}"></td>
                        <td>#{{ $order->id }}<br><small class="text-muted">{{ $order->created_at->format('M j, Y H:i') }}</small></td>
                        <td>{{ $order->user?->name ?? 'Guest customer' }}<br><small class="text-muted">{{ $order->contact_email ?? $order->user?->email }}</small></td>
                        <td>{{ $order->items_count }}</td>
                        <td>Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td>
                        <td>
                            @if ($order->payment_proof_path)
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.orders.payment-proof', $order) }}" target="_blank" rel="noopener noreferrer">Open proof</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if ($statusTransitions[$order->status] ?? [])
                                <div class="d-grid gap-1">
                                    @if ($order->status === 'payment_confirmed' && in_array('completed', $statusTransitions[$order->status], true))
                                        <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="completed">
                                            <input type="hidden" name="return_to" value="index">
                                            <button class="btn btn-sm btn-success text-nowrap" type="submit" aria-label="Mark order #{{ $order->id }} completed">Mark completed</button>
                                        </form>
                                    @endif
                                    @if ($order->status === 'payment_submitted')
                                        <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="payment_confirmed">
                                            <input type="hidden" name="return_to" value="index">
                                            <button class="btn btn-sm btn-accent text-nowrap" type="submit">{{ __('store.accept_order') }}</button>
                                        </form>
                                    @endif
                                    @php($availableTransitions = array_values(array_diff($statusTransitions[$order->status], ['payment_confirmed', 'completed'])))
                                    @if ($availableTransitions !== [])
                                        <form class="order-status-form d-flex gap-1" method="POST" action="{{ route('admin.orders.status', $order) }}" onsubmit="return this.elements.status.value !== 'cancelled' || confirm('Cancel order and return its stock?')">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="return_to" value="index">
                                            <label class="visually-hidden" for="status-{{ $order->id }}">Status for order #{{ $order->id }}</label>
                                            <select class="form-select form-select-sm" id="status-{{ $order->id }}" name="status" required>
                                                <option value="" selected disabled>{{ __('store.status_'.$order->status) }}</option>
                                                @foreach ($availableTransitions as $nextStatus)
                                                    <option value="{{ $nextStatus }}">{{ __('store.status_'.$nextStatus) }}</option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-sm btn-outline-secondary" type="submit">Save</button>
                                        </form>
                                    @endif
                                </div>
                            @else
                                {{ __('store.status_'.$order->status) }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td class="py-5 text-center text-muted" colspan="7">No orders yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="border-top p-3">{{ $orders->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
    <script>
        document.getElementById('select-all-orders')?.addEventListener('change', function () {
            document.querySelectorAll('.order-selection').forEach((checkbox) => {
                checkbox.checked = this.checked;
            });
        });
    </script>
@endsection
