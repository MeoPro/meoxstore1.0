@extends('admin.layout')

@section('title', $title)

@section('content')
    <div class="page-heading">
        <div><h1>{{ $title }}</h1></div>
        <a class="btn btn-light border" href="{{ route($routePrefix.'.index') }}">Back to list</a>
    </div>

    <form class="panel p-4" method="POST" enctype="multipart/form-data" action="{{ $category->exists ? route($routePrefix.'.update', $category) : route($routePrefix.'.store') }}">
        @csrf
        @if ($category->exists)
            @method('PUT')
        @endif
        <div class="row g-3" style="max-width: 720px">
            <div class="col-12">
                <label class="form-label" for="name">Name</label>
                <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $category->name) }}" required maxlength="255" autofocus>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @if ($categoryType === 'item')
                <div class="col-12 col-md-6">
                    <label class="form-label" for="game_category_id">Game</label>
                    <select class="form-select @error('game_category_id') is-invalid @enderror" id="game_category_id" name="game_category_id" required>
                        <option value="">Choose a game</option>
                        @foreach ($gameCategories as $gameCategory)
                            <option value="{{ $gameCategory->id }}" @selected((string) old('game_category_id', $selectedGameCategoryId) === (string) $gameCategory->id)>{{ $gameCategory->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">This category can be linked to multiple games.</div>
                    @error('game_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label" for="bobux_type">Bobux pricing type</label>
                    <select class="form-select @error('bobux_type') is-invalid @enderror" id="bobux_type" name="bobux_type">
                        <option value="" @selected(old('bobux_type', $category->bobux_type ?? '') === '')>Regular product (IDR price)</option>
                        <option value="gift_ingame" @selected(old('bobux_type', $category->bobux_type ?? '') === 'gift_ingame')>Bobux gift in-game (rate-based)</option>
                        <option value="gamepass_5_days" @selected(old('bobux_type', $category->bobux_type ?? '') === 'gamepass_5_days')>Bobux Gamepass 5 days (separate rate)</option>
                        <option value="login" @selected(old('bobux_type', $category->bobux_type ?? '') === 'login')>Bobux via login (manual price and Bobux amount)</option>
                    </select>
                    <div class="form-text">Gift in-game and Gamepass prices use their configured rates. Via login uses a manual IDR price and a Bobux amount entered for each variant.</div>
                    @error('bobux_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endif
            <div class="col-12 col-md-6">
                <label class="form-label" for="status">Status</label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                    <option value="active" @selected(old('status', $category->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $category->status ?? 'active') === 'inactive')>Inactive</option>
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="image">{{ ucfirst($categoryType) }} category image</label>
                @if ($category->image)
                    <div class="mb-2"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($category->image) }}" alt="{{ $category->name }}" style="height: 100px; max-width: 180px; object-fit: cover; border-radius: 8px;"></div>
                @endif
                <input class="form-control @error('image') is-invalid @enderror" id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                <div class="form-text">Optional. JPG, PNG, or WebP; maximum 2 MB.</div>
                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 d-flex gap-2 pt-2">
                <button class="btn btn-accent" type="submit">{{ $category->exists ? 'Save changes' : 'Create category' }}</button>
                <a class="btn btn-light border" href="{{ route($routePrefix.'.index') }}">Cancel</a>
            </div>
        </div>
    </form>
@endsection