@extends('admin.layout')

@section('title', 'Coupons')

@section('content')
    <div class="page-heading">
        <div><h1>Coupons</h1></div>
        <a class="btn btn-accent" href="{{ route('admin.coupons.create') }}">Add coupon</a>
    </div>

    <div class="panel overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Discount</th>
                        <th>Uses</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($coupons as $coupon)
                        <tr>
                            <td class="fw-semibold">{{ $coupon->code }}</td>
                            <td>
                                @if ($coupon->discount_type === 'percent')
                                    {{ rtrim(rtrim(number_format((float) $coupon->discount_value, 2, '.', ''), '0'), '.') }}%
                                @else
                                    Rp {{ number_format((float) $coupon->discount_value, 0, ',', '.') }}
                                @endif
                            </td>
                            <td>
                                <span class="d-block">{{ $coupon->availableUses() }} available / {{ $coupon->stock }} total</span>
                                <span class="small text-muted">{{ $coupon->reserved_uses }} reserved · {{ $coupon->redeemed_uses }} used</span>
                            </td>
                            <td><span class="badge {{ $coupon->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $coupon->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.coupons.edit', $coupon) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.coupons.status', $coupon) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm {{ $coupon->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}" type="submit">
                                            {{ $coupon->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="py-5 text-center text-muted" colspan="5">No coupons have been added.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($coupons->hasPages())
            <div class="border-top p-3">{{ $coupons->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
