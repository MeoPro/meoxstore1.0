@extends('admin.layout')

@section('title', 'Google Reviews')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Google Business Profile</h1>
            <p>Connect a verified business location to show its Google Maps reviews.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <section class="panel p-4">
        @unless ($credentialsConfigured)
            <div class="alert alert-warning mb-0">
                Add <code>GOOGLE_BUSINESS_PROFILE_CLIENT_ID</code> and
                <code>GOOGLE_BUSINESS_PROFILE_CLIENT_SECRET</code>, and
                <code>GOOGLE_BUSINESS_PROFILE_REDIRECT_URI</code> to the server environment.
                Then register this exact OAuth redirect URI in Google Cloud:
                <code>{{ config('services.google_business_profile.redirect_uri') ?: '/admin/google-business-profile/callback' }}</code>
            </div>
        @else
            @if ($connection === null)
                <p>Connect the Google account that manages the verified Meoxstore location. Only the review-management permission is requested.</p>
            @else
                <p>
                    Google Business Profile is connected.
                    @if ($connection->location_title)
                        Current review location: <strong>{{ $connection->location_title }}</strong>.
                    @else
                        Select the location whose reviews should appear on the storefront.
                    @endif
                </p>

                @if ($locationLookupFailed)
                    <div class="alert alert-danger">Locations could not be loaded from Google. Check API access and the application logs.</div>
                @elseif ($availableLocations === [])
                    <div class="alert alert-warning">No locations were returned. Confirm that the Google account can manage a verified location and that the Business Profile APIs are enabled.</div>
                @else
                    <form method="POST" action="{{ route('admin.google-business-profile.location.update') }}" class="mb-3">
                        @csrf
                        @method('PUT')
                        <label class="form-label" for="location_name">Business location</label>
                        <div class="d-flex flex-wrap gap-2">
                            <select class="form-select" id="location_name" name="location_name" required>
                                @foreach ($availableLocations as $location)
                                    <option
                                        value="{{ $location['resource_name'] }}"
                                        @selected($connection->location_name === $location['resource_name'])>
                                        {{ $location['title'] }}
                                    </option>
                                @endforeach
                            </select>
                            <button class="btn btn-accent" type="submit">Save location</button>
                        </div>
                        @error('location_name')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </form>
                @endif
            @endif

            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-accent" href="{{ route('admin.google-business-profile.authorize') }}">
                    {{ $connection === null ? 'Connect Google Business Profile' : 'Reconnect Google account' }}
                </a>
                @if ($connection !== null)
                    <form method="POST" action="{{ route('admin.google-business-profile.disconnect') }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-danger" type="submit">Remove connection</button>
                    </form>
                @endif
            </div>
            <p class="text-muted mt-3 mb-0">
                Google reviews are fetched a page at a time (up to 50 reviews per page). Removing the connection clears the stored token from this application; revoke access in your Google Account if needed.
            </p>
        @endunless
    </section>
@endsection
