@extends('admin.layout')

@section('title', 'Payment Methods')

@section('content')
    <div class="page-heading">
        <div><h1>Payment Methods</h1></div>
        <a class="btn btn-accent" href="{{ route('admin.payment-settings.create') }}">Add payment method</a>
    </div>

    <form id="bulk-payment-settings-form" class="align-items-end d-flex flex-wrap gap-2 mb-3" method="POST" action="{{ route('admin.payment-settings.bulk') }}" onsubmit="return this.elements.namedItem('action').value !== 'delete' || confirm('Delete selected payment methods?')">
        @csrf
        <div>
            <label class="form-label mb-1" for="bulk-payment-action">Action for selected methods</label>
            <select class="form-select form-select-sm" id="bulk-payment-action" name="action" required>
                <option value="" selected disabled>Choose action</option>
                <option value="activate">Activate</option>
                <option value="deactivate">Deactivate</option>
                <option value="delete">Delete</option>
            </select>
        </div>
        <button class="btn btn-sm btn-accent" type="submit">Apply to selected</button>
        @if ($errors->has('ids'))
            <span class="text-danger small">{{ $errors->first('ids') }}</span>
        @endif
    </form>
    <div class="panel overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th><input class="form-check-input" id="select-all-payment-settings" type="checkbox" aria-label="Select all payment methods on this page"></th><th>Method</th><th>Type</th><th>Currency</th><th>Payment to</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse ($paymentSettings as $paymentSetting)
                    <tr>
                        <td><input class="form-check-input payment-setting-selection" form="bulk-payment-settings-form" type="checkbox" name="ids[]" value="{{ $paymentSetting->id }}" aria-label="Select {{ $paymentSetting->name ?: $paymentSetting->bank_name }}"></td>
                        <td class="fw-semibold">{{ $paymentSetting->name ?: $paymentSetting->bank_name }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $paymentSetting->type)) }}</td>
                        <td>{{ strtoupper($paymentSetting->currency) }}</td>
                        <td>
                            {{ $paymentSetting->bank_name }}
                            @if ($paymentSetting->account_name || $paymentSetting->account_number)
                                <span class="d-block small text-muted">{{ $paymentSetting->account_name }} {{ $paymentSetting->account_number }}</span>
                            @endif
                            @if ($paymentSetting->qr_code_path)
                                <span class="d-block small text-muted">QR image configured</span>
                            @endif
                        </td>
                        <td><span class="badge {{ $paymentSetting->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $paymentSetting->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.payment-settings.edit', $paymentSetting) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.payment-settings.destroy', $paymentSetting) }}" onsubmit="return confirm('Delete this payment method?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="py-5 text-center text-muted" colspan="7">No payment methods have been added.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($paymentSettings->hasPages())
            <div class="border-top p-3">{{ $paymentSettings->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
    <script>
        document.getElementById('select-all-payment-settings')?.addEventListener('change', function () {
            document.querySelectorAll('.payment-setting-selection').forEach((checkbox) => {
                checkbox.checked = this.checked;
            });
        });
    </script>
@endsection
