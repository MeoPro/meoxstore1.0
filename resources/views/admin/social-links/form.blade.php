@extends('admin.layout')

@section('title', $title)

@section('content')
    <div class="page-heading">
        <div><h1>{{ $title }}</h1></div>
        <a class="btn btn-outline-secondary" href="{{ route('admin.social-links.index') }}">Back to social media</a>
    </div>
    <form class="panel p-4" method="POST" action="{{ $action }}" enctype="multipart/form-data" style="max-width: 760px">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif
        <div class="mb-3">
            <label class="form-label" for="name">Social media name</label>
            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $socialLink->name) }}" required maxlength="100">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="url">Profile URL</label>
            <input class="form-control @error('url') is-invalid @enderror" id="url" name="url" type="url" value="{{ old('url', $socialLink->url) }}" placeholder="https://..." required maxlength="2048">
            <div class="form-text">Use a full public http:// or https:// link.</div>
            @error('url')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="logo">Social media logo</label>
            <input class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" {{ $socialLink->logo_path === null ? 'required' : '' }}>
            <div class="form-text">PNG, JPG, or WEBP, up to 2 MB. The logo is displayed as a clickable footer icon.</div>
            @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @if ($socialLink->logo_path)
                <img class="mt-3 rounded border" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($socialLink->logo_path) }}" alt="Current social media logo" style="height: 48px; max-width: 72px; object-fit: contain; width: 72px">
            @endif
        </div>
        <div class="mb-3">
            <label class="form-label" for="sort_order">Display order</label>
            <input class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" type="number" min="0" max="9999" value="{{ old('sort_order', $socialLink->sort_order ?? 0) }}" required>
            @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <input type="hidden" name="is_active" value="0">
        <div class="form-check mb-4">
            <input class="form-check-input" id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $socialLink->is_active ?? true))>
            <label class="form-check-label" for="is_active">Show this link in the storefront footer</label>
        </div>
        <button class="btn btn-accent" type="submit">Save social media link</button>
    </form>
@endsection
