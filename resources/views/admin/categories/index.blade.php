@extends('admin.layout')

@section('title', $title)

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $title }}</h1>
        </div>
        <a class="btn btn-accent" href="{{ route($routePrefix.'.create') }}">Add category</a>
    </div>

    <form class="panel mb-3 p-3" method="GET" action="{{ route($routePrefix.'.index') }}">
        <div class="row g-2">
            <div class="col-sm-8 col-lg-5">
                <label class="visually-hidden" for="category-search">Search categories</label>
                <input class="form-control" id="category-search" name="search" value="{{ request('search') }}" placeholder="Search categories">
            </div>
            <div class="col-auto"><button class="btn btn-outline-secondary h-100" type="submit">Search</button></div>
            @if (request()->filled('search'))
                <div class="col-auto"><a class="btn btn-light h-100" href="{{ route($routePrefix.'.index') }}">Clear</a></div>
            @endif
        </div>
    </form>

    <form class="panel mb-3 p-3" id="bulk-category-form" method="POST" action="{{ route($routePrefix.'.bulk') }}">
        @csrf
        <div class="align-items-end d-flex flex-wrap gap-2">
            <div>
                <label class="form-label small mb-1" for="bulk-category-action">Selected categories</label>
                <select class="form-select" id="bulk-category-action" name="action" required>
                    <option value="">Choose an action</option>
                    <option value="archive">Archive / deactivate</option>
                    <option value="delete">Permanently delete</option>
                </select>
            </div>
            <button class="btn btn-outline-danger" type="submit">Apply to selected</button>
            <span class="small text-muted">Selections apply to categories on this page.</span>
        </div>
    </form>

    <div class="panel overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th><input class="form-check-input" id="select-all-categories" type="checkbox" aria-label="Select all categories on this page"></th><th>Image</th><th>Name</th><th>Status</th><th>Created</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td><input class="form-check-input" form="bulk-category-form" name="ids[]" type="checkbox" value="{{ $category->id }}" aria-label="Select {{ $category->name }}"></td>
                        <td>
                            @if ($category->image)
                                <img class="product-thumb" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($category->image) }}" alt="{{ $category->name }}">
                            @else
                                <span class="product-thumb d-inline-flex align-items-center justify-content-center text-muted">—</span>
                            @endif
                        </td>
                        <td class="fw-semibold">{{ $category->name }}</td>
                        <td><span class="badge rounded-pill badge-status {{ $category->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ ucfirst($category->status) }}</span></td>
                        <td>{{ $category->created_at->format('M j, Y') }}</td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route($routePrefix.'.edit', $category) }}">Edit</a>
                                <form method="POST" action="{{ route($routePrefix.'.status', $category) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm {{ $category->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}" type="submit">
                                        {{ $category->status === 'active' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route($routePrefix.'.destroy', $category) }}" onsubmit="return confirm('Permanently delete this category? Categories used by products cannot be deleted.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="py-5 text-center text-muted" colspan="6">No categories found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($categories->hasPages())
            <div class="border-top p-3">{{ $categories->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
    <script>
        (() => {
            const form = document.getElementById('bulk-category-form');
            const selectAll = document.getElementById('select-all-categories');
            const checkboxes = [...document.querySelectorAll('input[name="ids[]"][form="bulk-category-form"]')];

            selectAll?.addEventListener('change', () => {
                checkboxes.forEach((checkbox) => { checkbox.checked = selectAll.checked; });
            });

            checkboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    if (selectAll) {
                        selectAll.checked = checkboxes.length > 0 && checkboxes.every((item) => item.checked);
                        selectAll.indeterminate = checkboxes.some((item) => item.checked) && !selectAll.checked;
                    }
                });
            });

            form?.addEventListener('submit', (event) => {
                if (!checkboxes.some((checkbox) => checkbox.checked)) {
                    event.preventDefault();
                    window.alert('Select at least one category.');
                    return;
                }

                if (document.getElementById('bulk-category-action').value === 'delete' &&
                    !window.confirm('Permanently delete the selected categories? Categories linked to products will be kept.')) {
                    event.preventDefault();
                }
            });
        })();
    </script>
@endsection