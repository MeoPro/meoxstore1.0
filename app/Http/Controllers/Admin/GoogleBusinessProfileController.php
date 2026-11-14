<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\GoogleBusinessProfileException;
use App\Http\Controllers\Controller;
use App\Models\GoogleBusinessProfileConnection;
use App\Services\GoogleBusinessProfileService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class GoogleBusinessProfileController extends Controller
{
    private const OAUTH_STATE_SESSION_KEY = 'google_business_profile.oauth_state';

    public function index(GoogleBusinessProfileService $service): View
    {
        $connection = GoogleBusinessProfileConnection::query()->find(1);
        $availableLocations = [];
        $locationLookupFailed = false;

        if ($service->isConfigured() && $connection !== null) {
            try {
                $availableLocations = $service->availableLocations($connection);
            } catch (ConnectionException|RequestException|GuzzleException|GoogleBusinessProfileException $exception) {
                Log::warning('Google Business Profile locations could not be loaded.', [
                    'exception' => $exception::class,
                ]);
                $locationLookupFailed = true;
            }
        }

        return view('admin.google-business-profile.index', [
            'connection' => $connection,
            'credentialsConfigured' => $service->isConfigured(),
            'availableLocations' => $availableLocations,
            'locationLookupFailed' => $locationLookupFailed,
        ]);
    }

    public function authorize(Request $request, GoogleBusinessProfileService $service): RedirectResponse
    {
        abort_unless($service->isConfigured(), 503, 'Google Business Profile OAuth credentials are not configured.');

        $state = bin2hex(random_bytes(32));
        $request->session()->put(self::OAUTH_STATE_SESSION_KEY, $state);

        return redirect()->away($service->authorizationUrl($state));
    }

    public function callback(Request $request, GoogleBusinessProfileService $service): RedirectResponse
    {
        $expectedState = $request->session()->pull(self::OAUTH_STATE_SESSION_KEY);
        $receivedState = $request->query('state');

        abort_unless(
            is_string($expectedState)
                && is_string($receivedState)
                && hash_equals($expectedState, $receivedState),
            403,
        );

        if ($request->filled('error')) {
            return redirect()
                ->route('admin.google-business-profile.index')
                ->with('error', 'Google authorization was not completed.');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:4096'],
        ]);

        try {
            $tokens = $service->exchangeAuthorizationCode($validated['code']);
        } catch (GuzzleException|GoogleBusinessProfileException $exception) {
            Log::warning('Google Business Profile OAuth exchange failed.', [
                'exception' => $exception::class,
            ]);

            return redirect()
                ->route('admin.google-business-profile.index')
                ->with('error', 'Google authorization failed. Check the application logs and try again.');
        }

        $existingConnection = GoogleBusinessProfileConnection::query()->find(1);
        $refreshToken = $tokens['refresh_token'] ?? $existingConnection?->refresh_token;

        if (! is_string($refreshToken) || trim($refreshToken) === '') {
            return redirect()
                ->route('admin.google-business-profile.index')
                ->with('error', 'Google did not provide a refresh token. Revoke the previous app grant and connect again.');
        }

        GoogleBusinessProfileConnection::query()->updateOrCreate(
            ['id' => 1],
            ['refresh_token' => $refreshToken],
        );

        return redirect()
            ->route('admin.google-business-profile.index')
            ->with('success', 'Google Business Profile connected. Select the location to display its reviews.');
    }

    public function updateLocation(Request $request, GoogleBusinessProfileService $service): RedirectResponse
    {
        $connection = GoogleBusinessProfileConnection::query()->findOrFail(1);
        $availableLocations = $service->availableLocations($connection);
        $locationNames = array_column($availableLocations, 'resource_name');
        $validated = $request->validate([
            'location_name' => ['required', 'string', Rule::in($locationNames)],
        ]);
        $location = collect($availableLocations)->firstWhere('resource_name', $validated['location_name']);

        if (! is_array($location)) {
            abort(422, 'The selected Google Business Profile location is not available.');
        }

        $connection->update([
            'location_name' => $location['resource_name'],
            'location_title' => $location['title'],
        ]);

        return redirect()
            ->route('admin.google-business-profile.index')
            ->with('success', 'Google review location updated successfully.');
    }

    public function disconnect(): RedirectResponse
    {
        GoogleBusinessProfileConnection::query()->whereKey(1)->delete();

        return redirect()
            ->route('admin.google-business-profile.index')
            ->with('success', 'Google Business Profile connection removed from Meoxstore.');
    }
}
