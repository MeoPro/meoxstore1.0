@extends('admin.layout')

@section('title', 'Blog categories')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Blog categories</h1>
            <p>Organize articles into sections on the public news page.</p>
        </div>
        <a class="btn btn-accent" href="{{ route('admin.blog-categories.create') }}">Add category</a>
    </div>

    <form class="panel mb-3 p-3" method="GET" action="{{ route('admin.blog-categories.index') }}">
        <div class="row g-2">
            <div class="col-sm-8 col-lg-5">
                <label class="visually-hidden" for="blog-category-search">Search blog categories</label>
                <input class="form-control" id="blog-category-search" name="search" value="{{ $search }}" placeholder="Search categories">
            </div>
            <div class="col-auto"><button class="btn btn-outline-secondary h-100" type="submit">Search</button></div>
            @if ($search !== '')
                <div class="col-auto"><a class="btn btn-light h-100" href="{{ route('admin.blog-categories.index') }}">Clear</a></div>
            @endif
        </div>
    </form>

    <div class="panel overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Category</th><th>Articles</th><th>Order</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>
                                <span class="d-block fw-semibold">{{ $category->name }}</span>
                                <span class="small text-muted">/blog/category/{{ $category->slug }}</span>
                            </td>
                            <td>{{ $category->blog_posts_count }}</td>
                            <td>{{ $category->sort_order }}</td>
                            <td><span class="badge rounded-pill {{ $category->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.blog-categories.edit', $category) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.blog-categories.destroy', $category) }}" onsubmit="return confirm('Delete this category? Articles will remain and be uncategorized.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="py-5 text-center text-muted" colspan="5">No blog categories found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($categories->hasPages())
            <div class="border-top p-3">{{ $categories->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
