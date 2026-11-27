@extends('admin.layout')

@section('title', 'Expenses')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Expenses</h1>
            <p>Record business spending by quantity and unit price.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif

    <section class="panel p-4 mb-4">
        <form method="POST" action="{{ route('admin.expenses.store') }}" class="row g-3 align-items-end">
            @csrf
            <div class="col-12 col-lg-5">
                <label class="form-label" for="expense-description">Description</label>
                <input class="form-control @error('description') is-invalid @enderror" id="expense-description" name="description" value="{{ old('description') }}" maxlength="255" required>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label" for="expense-quantity">Quantity</label>
                <input class="form-control @error('quantity') is-invalid @enderror" id="expense-quantity" name="quantity" type="number" min="1" max="100000" step="1" value="{{ old('quantity', 1) }}" required>
                @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-6 col-lg-3">
                <label class="form-label" for="expense-unit-price">Unit price (IDR)</label>
                <input class="form-control @error('unit_price') is-invalid @enderror" id="expense-unit-price" name="unit_price" type="number" min="0.01" max="9999999999.99" step="0.01" value="{{ old('unit_price') }}" required>
                @error('unit_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-lg-2">
                <button class="btn btn-accent w-100" type="submit">Add expense</button>
            </div>
        </form>
    </section>

    <section class="panel overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr><th>Description</th><th>Quantity</th><th>Unit price</th><th>Total</th><th>Date</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr>
                            <td>{{ $expense->description }}</td>
                            <td>{{ number_format($expense->quantity) }}</td>
                            <td>Rp {{ number_format((float) $expense->unit_price, 0, ',', '.') }}</td>
                            <td class="fw-semibold">Rp {{ number_format($expense->quantity * (float) $expense->unit_price, 0, ',', '.') }}</td>
                            <td>{{ $expense->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this expense?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="py-5 text-center text-muted" colspan="6">No expenses recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($expenses->hasPages())
            <div class="border-top p-3">{{ $expenses->links('pagination::bootstrap-5') }}</div>
        @endif
    </section>
@endsection
