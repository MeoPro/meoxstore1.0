@extends('admin.layout')

@section('title', 'Blog posts')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Blog posts</h1>
            <p>Create articles to share news and help customers find your store through search.</p>
        </div>
        <a class="btn btn-accent" href="{{ route('admin.blog-posts.create') }}">Write an article</a>
    </div>

    <form class="panel mb-3 p-3" method="GET" action="{{ route('admin.blog-posts.index') }}">
        <div class="row g-2">
            <div class="col-sm-7 col-lg-5">
                <label class="visually-hidden" for="blog-post-search">Search blog posts</label>
                <input class="form-control" id="blog-post-search" name="search" value="{{ $search }}" placeholder="Search title or URL slug">
            </div>
            <div class="col-sm-3 col-lg-2">
                <label class="visually-hidden" for="blog-post-status">Filter by status</label>
                <select class="form-select" id="blog-post-status" name="status">
                    <option value="">All statuses</option>
                    <option value="draft" @selected($status === 'draft')>Draft</option>
                    <option value="published" @selected($status === 'published')>Published</option>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-outline-secondary h-100" type="submit">Filter</button></div>
            @if ($search !== '' || $status !== '')
                <div class="col-auto"><a class="btn btn-light h-100" href="{{ route('admin.blog-posts.index') }}">Clear</a></div>
            @endif
        </div>
    </form>

    <div class="panel overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Article</th>
                        <th>Status</th>
                        <th>Published</th>
                        <th>Last updated</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($blogPosts as $blogPost)
                        <tr>
                            <td>
                                <span class="d-block fw-semibold">{{ $blogPost->title }}</span>
                                <span class="small text-muted">/blog/{{ $blogPost->slug }}</span>
                                @if ($blogPost->categories->isNotEmpty())
                                    <span class="d-flex flex-wrap gap-1 mt-1">
                                        @foreach ($blogPost->categories as $category)
                                            <span class="badge rounded-pill text-bg-light border">{{ $category->name }}</span>
                                        @endforeach
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if ($blogPost->isPubliclyAvailable())
                                    <span class="badge rounded-pill text-bg-success">Published</span>
                                @elseif ($blogPost->status === 'published')
                                    <span class="badge rounded-pill text-bg-warning">Scheduled</span>
                                @else
                                    <span class="badge rounded-pill text-bg-secondary">Draft</span>
                                @endif
                            </td>
                            <td>{{ $blogPost->published_at?->format('M j, Y') ?? '—' }}</td>
                            <td>{{ $blogPost->updated_at->format('M j, Y') }}</td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    @if ($blogPost->isPubliclyAvailable())
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('blog.show', $blogPost->slug) }}" target="_blank" rel="noopener">View</a>
                                    @endif
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.blog-posts.edit', $blogPost) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.blog-posts.destroy', $blogPost) }}" onsubmit="return confirm('Delete this article and all of its language versions?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="py-5 text-center text-muted" colspan="5">No blog posts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($blogPosts->hasPages())
            <div class="border-top p-3">{{ $blogPosts->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
