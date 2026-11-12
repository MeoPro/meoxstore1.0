@extends('admin.layout')

@php($isEditing = $blogCategory->exists)

@section('title', $isEditing ? 'Edit blog category' : 'Add blog category')

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $isEditing ? 'Edit blog category' : 'Add blog category' }}</h1>
            <p>Categories create article groups and public archive pages.</p>
        </div>
        <a class="btn btn-light border" href="{{ route('admin.blog-categories.index') }}">Back to categories</a>
    </div>

    <form class="panel p-4" method="POST" action="{{ $isEditing ? route('admin.blog-categories.update', $blogCategory) : route('admin.blog-categories.store') }}">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif
        <div class="row g-3" style="max-width: 760px">
            <div class="col-12">
                <label class="form-label" for="name">Name</label>
                <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $blogCategory->name) }}" required maxlength="255" autofocus>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="slug">URL slug</label>
                <input class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $blogCategory->slug) }}" maxlength="255" pattern="[A-Za-z0-9-]*">
                <div class="form-text">Leave blank to generate the URL from the category name. Existing URLs stay the same if you only change the name.</div>
                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" maxlength="320">{{ old('description', $blogCategory->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="sort_order">Display order</label>
                <input class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $blogCategory->sort_order ?? 0) }}" required>
                @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="is_active">Status</label>
                <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active" required>
                    <option value="1" @selected((string) old('is_active', $blogCategory->is_active ?? true) === '1')>Active</option>
                    <option value="0" @selected((string) old('is_active', $blogCategory->is_active ?? true) === '0')>Inactive</option>
                </select>
                @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                <button class="btn btn-accent" type="submit">{{ $isEditing ? 'Save changes' : 'Create category' }}</button>
                <a class="btn btn-light border" href="{{ route('admin.blog-categories.index') }}">Cancel</a>
            </div>
        </div>
    </form>
@endsection
