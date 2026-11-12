@extends('admin.layout')

@section('title', 'Blog sidebar')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Blog sidebar</h1>
            <p>Arrange product cards, posters, and category menus on blog pages.</p>
        </div>
        <a class="btn btn-accent" href="{{ route('admin.blog-sidebar-blocks.create') }}">Add sidebar block</a>
    </div>

    <div class="panel overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Block</th><th>Content</th><th>Order</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse ($sidebarBlocks as $block)
                        <tr>
                            <td>
                                <span class="d-block fw-semibold">{{ $block->title }}</span>
                                <span class="small text-muted">{{ str_replace('_', ' ', ucfirst($block->type)) }}</span>
                            </td>
                            <td>
                                @if ($block->type === \App\Models\BlogSidebarBlock::TYPE_PRODUCT)
                                    {{ $block->product?->name ?? 'Product unavailable' }}
                                @elseif ($block->type === \App\Models\BlogSidebarBlock::TYPE_POSTER)
                                    @if ($block->image_path)
                                        <img class="product-thumb" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($block->image_path) }}" alt="">
                                    @else
                                        Poster not set
                                    @endif
                                @else
                                    Automatic category menu
                                @endif
                            </td>
                            <td>{{ $block->sort_order }}</td>
                            <td><span class="badge rounded-pill {{ $block->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $block->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.blog-sidebar-blocks.edit', $block) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.blog-sidebar-blocks.destroy', $block) }}" onsubmit="return confirm('Delete this sidebar block?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="py-5 text-center text-muted" colspan="5">No sidebar blocks. Add one to start building the sidebar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($sidebarBlocks->hasPages())
            <div class="border-top p-3">{{ $sidebarBlocks->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
