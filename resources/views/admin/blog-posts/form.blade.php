@extends('admin.layout')

@php($isEditing = $blogPost->exists)

@section('title', $isEditing ? 'Edit blog post' : 'Write a blog post')

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $isEditing ? 'Edit blog post' : 'Write a blog post' }}</h1>
            <p>Write in Markdown, then publish now or schedule all language versions.</p>
        </div>
        <a class="btn btn-light border" href="{{ route('admin.blog-posts.index') }}">Back to blog posts</a>
    </div>

    <form class="panel p-4" method="POST" enctype="multipart/form-data" action="{{ $isEditing ? route('admin.blog-posts.update', $blogPost) : route('admin.blog-posts.store') }}">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif

        @error('translation')
            <div class="alert alert-danger" role="alert">{{ $message }}</div>
        @enderror

        <div class="row g-3" style="max-width: 900px">
            @if ($isEditing)
                <input type="hidden" name="source_locale" value="{{ $blogPost->source_locale }}">
                <div class="col-12">
                    <div class="alert alert-info mb-0">
                        This article is written in {{ strtoupper($blogPost->source_locale) }}. Saving content changes regenerates its other language versions.
                    </div>
                </div>
            @else
                <div class="col-12">
                    <label class="form-label" for="source_locale">Article language</label>
                    <select class="form-select @error('source_locale') is-invalid @enderror" id="source_locale" name="source_locale" required>
                        <option value="id" @selected(old('source_locale', $blogPost->source_locale) === 'id')>Bahasa Indonesia</option>
                        <option value="ms" @selected(old('source_locale', $blogPost->source_locale) === 'ms')>Bahasa Melayu</option>
                        <option value="en" @selected(old('source_locale', $blogPost->source_locale) === 'en')>English</option>
                    </select>
                    <div class="form-text">Google Cloud Translation LLM will generate the other two language versions. All versions share the same draft or published status.</div>
                    @error('source_locale')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endif
            <div class="col-12">
                <label class="form-label" for="title">Article title</label>
                <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $blogPost->title) }}" required maxlength="255" autofocus>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="slug">URL slug</label>
                <input class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $blogPost->slug) }}" maxlength="255" pattern="[A-Za-z0-9-]*" aria-describedby="slug-help">
                <div class="form-text" id="slug-help">Leave blank to create a readable URL from the title. Changing a published slug changes its link.</div>
                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="excerpt">Short summary</label>
                <textarea class="form-control @error('excerpt') is-invalid @enderror" id="excerpt" name="excerpt" rows="3" maxlength="320" required>{{ old('excerpt', $blogPost->excerpt) }}</textarea>
                <div class="form-text">Used on the blog list and as the default search result description. Maximum 320 characters.</div>
                @error('excerpt')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="categories">Categories</label>
                <select class="form-select @error('categories') is-invalid @enderror @error('categories.*') is-invalid @enderror" id="categories" name="categories[]" multiple size="5" aria-describedby="categories-help">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(in_array($category->id, old('categories', $blogPost->categories->modelKeys()), false))>{{ $category->name }}{{ $category->is_active ? '' : ' (inactive)' }}</option>
                    @endforeach
                </select>
                <div class="form-text" id="categories-help">Select one or more categories. Hold Ctrl (Windows) or Command (Mac) to select several.</div>
                @error('categories')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @error('categories.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="body">Article content</label>
                <textarea class="form-control font-monospace @error('body') is-invalid @enderror" id="body" name="body" rows="18" maxlength="100000" required>{{ old('body', $blogPost->body) }}</textarea>
                <div class="form-text">Markdown is supported: use # for headings, **text** for bold, and [label](https://example.com) for links. Raw HTML is not rendered.</div>
                @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="cover_image">Cover image</label>
                @if ($blogPost->cover_image)
                    <div class="mb-2">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($blogPost->cover_image) }}" alt="Current cover for {{ $blogPost->title }}" style="height: 120px; max-width: 220px; object-fit: cover; border-radius: 8px;">
                    </div>
                @endif
                <input class="form-control @error('cover_image') is-invalid @enderror" id="cover_image" name="cover_image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                <div class="form-text">Optional. JPG, PNG, or WebP; maximum 4 MB.</div>
                @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="status">Status</label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                    <option value="draft" @selected(old('status', $blogPost->status ?? 'draft') === 'draft')>Draft</option>
                    <option value="published" @selected(old('status', $blogPost->status ?? 'draft') === 'published')>Published</option>
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="published_at">Publish date and time</label>
                <input class="form-control @error('published_at') is-invalid @enderror" id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at', $blogPost->published_at?->format('Y-m-d\TH:i')) }}">
                <div class="form-text">Leave blank to publish immediately. Future dates keep the article hidden until then.</div>
                @error('published_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <hr class="my-1">
                <h2 class="h5 mb-0">Search appearance</h2>
            </div>
            <div class="col-12">
                <label class="form-label" for="seo_title">SEO title</label>
                <input class="form-control @error('seo_title') is-invalid @enderror" id="seo_title" name="seo_title" value="{{ old('seo_title', $blogPost->seo_title) }}" maxlength="255">
                <div class="form-text">Optional. The article title is used when this is blank.</div>
                @error('seo_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="meta_description">Meta description</label>
                <textarea class="form-control @error('meta_description') is-invalid @enderror" id="meta_description" name="meta_description" rows="2" maxlength="320">{{ old('meta_description', $blogPost->meta_description) }}</textarea>
                <div class="form-text">Optional. The short summary is used when this is blank.</div>
                @error('meta_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                <button class="btn btn-accent" type="submit">{{ $isEditing ? 'Save changes' : 'Create article' }}</button>
                <a class="btn btn-light border" href="{{ route('admin.blog-posts.index') }}">Cancel</a>
            </div>
        </div>
    </form>
@endsection
