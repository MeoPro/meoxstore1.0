@extends('admin.layout')

@section('title', 'Social Media')

@section('content')
    <div class="page-heading">
        <div><h1>Social Media</h1></div>
        <a class="btn btn-accent" href="{{ route('admin.social-links.create') }}">Add social media link</a>
    </div>
    <div class="panel overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Logo</th><th>Name</th><th>URL</th><th>Order</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse ($socialLinks as $socialLink)
                        <tr>
                            <td>
                                <img
                                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($socialLink->logo_path) }}"
                                    alt=""
                                    style="height: 36px; max-width: 56px; object-fit: contain; width: 56px">
                            </td>
                            <td class="fw-semibold">{{ $socialLink->name }}</td>
                            <td><a href="{{ $socialLink->url }}" target="_blank" rel="noopener noreferrer">{{ $socialLink->url }}</a></td>
                            <td>{{ $socialLink->sort_order }}</td>
                            <td><span class="badge {{ $socialLink->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $socialLink->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.social-links.edit', $socialLink) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.social-links.destroy', $socialLink) }}" onsubmit="return confirm('Delete this social media link?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="py-5 text-center text-muted" colspan="6">No social media links have been added.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($socialLinks->hasPages())
            <div class="border-top p-3">{{ $socialLinks->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
