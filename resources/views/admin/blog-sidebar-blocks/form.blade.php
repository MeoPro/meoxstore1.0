@extends('admin.layout')

@php($isEditing = $sidebarBlock->exists)

@section('title', $isEditing ? 'Edit sidebar block' : 'Add sidebar block')

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $isEditing ? 'Edit sidebar block' : 'Add sidebar block' }}</h1>
            <p>Use display order to control where this block appears relative to the others.</p>
        </div>
        <a class="btn btn-light border" href="{{ route('admin.blog-sidebar-blocks.index') }}">Back to sidebar</a>
    </div>

    <form class="panel p-4" method="POST" enctype="multipart/form-data" action="{{ $isEditing ? route('admin.blog-sidebar-blocks.update', $sidebarBlock) : route('admin.blog-sidebar-blocks.store') }}">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif
        <div class="row g-3" style="max-width: 760px">
            <div class="col-12">
                <label class="form-label" for="type">Block type</label>
                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                    <option value="product" @selected(old('type', $sidebarBlock->type) === 'product')>Catalog product</option>
                    <option value="poster" @selected(old('type', $sidebarBlock->type) === 'poster')>Advertisement poster</option>
                    <option value="blog_categories" @selected(old('type', $sidebarBlock->type) === 'blog_categories')>Blog category menu</option>
                    <option value="game_categories" @selected(old('type', $sidebarBlock->type) === 'game_categories')>Game category menu</option>
                    <option value="item_categories" @selected(old('type', $sidebarBlock->type) === 'item_categories')>Item category menu</option>
                </select>
                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="title">Heading</label>
                <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $sidebarBlock->title) }}" required maxlength="255">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="product_id">Catalog product</label>
                <select class="form-select @error('product_id') is-invalid @enderror" id="product_id" name="product_id">
                    <option value="">Choose an active product</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected((string) old('product_id', $sidebarBlock->product_id) === (string) $product->id)>
                            {{ $product->name }}{{ $product->gameCategory ? ' — '.$product->gameCategory->name : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Required only for a catalog product block.</div>
                @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="image">Poster image</label>
                @if ($sidebarBlock->image_path)
                    <div class="mb-2">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($sidebarBlock->image_path) }}" alt="Current poster" style="max-height: 180px; max-width: 100%; border-radius: 8px;">
                    </div>
                @endif
                <input class="form-control @error('image') is-invalid @enderror" id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                <div class="form-text">Required for a new poster block. JPG, PNG, or WebP; maximum 4 MB.</div>
                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="link_url">Poster destination URL</label>
                <input class="form-control @error('link_url') is-invalid @enderror" id="link_url" name="link_url" type="url" value="{{ old('link_url', $sidebarBlock->link_url) }}" maxlength="2048" placeholder="https://example.com">
                <div class="form-text">Optional. Only HTTP and HTTPS links are allowed.</div>
                @error('link_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="sort_order">Display order</label>
                <input class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $sidebarBlock->sort_order ?? 0) }}" required>
                @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="is_active">Status</label>
                <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active" required>
                    <option value="1" @selected((string) old('is_active', $sidebarBlock->is_active ?? true) === '1')>Active</option>
                    <option value="0" @selected((string) old('is_active', $sidebarBlock->is_active ?? true) === '0')>Inactive</option>
                </select>
                @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                <button class="btn btn-accent" type="submit">{{ $isEditing ? 'Save changes' : 'Create block' }}</button>
                <a class="btn btn-light border" href="{{ route('admin.blog-sidebar-blocks.index') }}">Cancel</a>
            </div>
        </div>
    </form>
@endsection
