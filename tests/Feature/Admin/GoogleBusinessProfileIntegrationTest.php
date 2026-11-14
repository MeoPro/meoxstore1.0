<?php

use App\Models\GoogleBusinessProfileConnection;
use App\Models\User;
use Google\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('restricts Google Business Profile settings to administrators', function () {
    $this->get(route('admin.google-business-profile.index'))
        ->assertRedirectToRoute('login');

    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->get(route('admin.google-business-profile.index'))
        ->assertForbidden();

    config([
        'services.google_business_profile.client_id' => 'client-id',
        'services.google_business_profile.client_secret' => 'client-secret',
        'services.google_business_profile.redirect_uri' => 'https://meoxstore.test/admin/google-business-profile/callback',
    ]);

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get(route('admin.google-business-profile.index'))
        ->assertOk()
        ->assertSee('Connect Google Business Profile');
});

it('starts Google OAuth with a session-bound business review scope', function () {
    config([
        'services.google_business_profile.client_id' => 'client-id',
        'services.google_business_profile.client_secret' => 'client-secret',
        'services.google_business_profile.redirect_uri' => 'https://meoxstore.test/admin/google-business-profile/callback',
    ]);

    $response = $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get(route('admin.google-business-profile.authorize'));

    $response->assertRedirect();
    parse_str((string) parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);

    expect($query['scope'])->toBe('https://www.googleapis.com/auth/business.manage')
        ->and($query['access_type'])->toBe('offline')
        ->and($query['redirect_uri'])->toBe('https://meoxstore.test/admin/google-business-profile/callback')
        ->and($query['state'])->toBeString()
        ->and(strlen($query['state']))->toBe(64);

    $response->assertSessionHas('google_business_profile.oauth_state', $query['state']);
});

it('rejects forged OAuth callbacks and stores valid refresh tokens encrypted', function () {
    config([
        'services.google_business_profile.client_id' => 'client-id',
        'services.google_business_profile.client_secret' => 'client-secret',
        'services.google_business_profile.redirect_uri' => 'https://meoxstore.test/admin/google-business-profile/callback',
    ]);

    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)
        ->get(route('admin.google-business-profile.callback', [
            'state' => 'forged-state',
            'code' => 'authorization-code',
        ]))
        ->assertForbidden();

    $googleClient = Mockery::mock(Client::class);
    $googleClient->shouldReceive('setClientId')->once()->with('client-id');
    $googleClient->shouldReceive('setClientSecret')->once()->with('client-secret');
    $googleClient->shouldReceive('setRedirectUri')->once()->with('https://meoxstore.test/admin/google-business-profile/callback');
    $googleClient->shouldReceive('fetchAccessTokenWithAuthCode')
        ->once()
        ->with('authorization-code')
        ->andReturn([
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
            'expires_in' => 3600,
        ]);
    $this->app->instance(Client::class, $googleClient);

    $this->actingAs($admin)
        ->withSession(['google_business_profile.oauth_state' => 'valid-state'])
        ->get(route('admin.google-business-profile.callback', [
            'state' => 'valid-state',
            'code' => 'authorization-code',
        ]))
        ->assertRedirectToRoute('admin.google-business-profile.index');

    $connection = GoogleBusinessProfileConnection::query()->findOrFail(1);
    expect($connection->refresh_token)->toBe('refresh-token');
    $this->assertNotSame('refresh-token', DB::table('google_business_profile_connections')->where('id', 1)->value('refresh_token'));
});

it('lists accessible locations and saves the selected verified location', function () {
    config([
        'services.google_business_profile.client_id' => 'client-id',
        'services.google_business_profile.client_secret' => 'client-secret',
        'services.google_business_profile.redirect_uri' => 'https://meoxstore.test/admin/google-business-profile/callback',
    ]);

    GoogleBusinessProfileConnection::factory()->create([
        'id' => 1,
        'refresh_token' => 'refresh-token',
        'location_name' => null,
        'location_title' => null,
    ]);

    $googleClient = Mockery::mock(Client::class);
    $googleClient->shouldReceive('setClientId')->once()->with('client-id');
    $googleClient->shouldReceive('setClientSecret')->once()->with('client-secret');
    $googleClient->shouldReceive('setRedirectUri')->once()->with('https://meoxstore.test/admin/google-business-profile/callback');
    $googleClient->shouldReceive('fetchAccessTokenWithRefreshToken')
        ->once()
        ->with('refresh-token')
        ->andReturn(['access_token' => 'access-token', 'expires_in' => 3600]);
    $this->app->instance(Client::class, $googleClient);

    Http::preventStrayRequests();
    Http::fake([
        'mybusinessaccountmanagement.googleapis.com/v1/accounts*' => Http::response([
            'accounts' => [['name' => 'accounts/123456789']],
        ]),
        'mybusinessbusinessinformation.googleapis.com/v1/accounts/123456789/locations*' => Http::response([
            'locations' => [[
                'name' => 'locations/987654321',
                'title' => 'Meoxstore Bali',
            ]],
        ]),
    ]);

    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)
        ->get(route('admin.google-business-profile.index'))
        ->assertOk()
        ->assertSee('Meoxstore Bali');

    $this->put(route('admin.google-business-profile.location.update'), [
        'location_name' => 'accounts/123456789/locations/987654321',
    ])->assertRedirectToRoute('admin.google-business-profile.index');

    $connection = GoogleBusinessProfileConnection::query()->findOrFail(1);
    expect($connection->location_name)->toBe('accounts/123456789/locations/987654321')
        ->and($connection->location_title)->toBe('Meoxstore Bali');

    Http::assertSent(fn (Request $request): bool => str_contains($request->url(), 'mybusinessbusinessinformation.googleapis.com/v1/accounts/123456789/locations')
        && $request->hasHeader('Authorization', 'Bearer access-token'));
});

it('shows paginated Google reviews and safely renders their text', function () {
    config([
        'services.google_business_profile.client_id' => 'client-id',
        'services.google_business_profile.client_secret' => 'client-secret',
        'services.google_business_profile.redirect_uri' => 'https://meoxstore.test/admin/google-business-profile/callback',
    ]);

    GoogleBusinessProfileConnection::factory()->create([
        'id' => 1,
        'refresh_token' => 'refresh-token',
        'location_name' => 'accounts/123456789/locations/987654321',
    ]);

    $googleClient = Mockery::mock(Client::class);
    $googleClient->shouldReceive('setClientId')->once()->with('client-id');
    $googleClient->shouldReceive('setClientSecret')->once()->with('client-secret');
    $googleClient->shouldReceive('setRedirectUri')->once()->with('https://meoxstore.test/admin/google-business-profile/callback');
    $googleClient->shouldReceive('fetchAccessTokenWithRefreshToken')
        ->once()
        ->with('refresh-token')
        ->andReturn(['access_token' => 'access-token', 'expires_in' => 3600]);
    $this->app->instance(Client::class, $googleClient);

    Http::preventStrayRequests();
    Http::fake([
        'mybusiness.googleapis.com/v4/accounts/123456789/locations/987654321/reviews*' => Http::response([
            'reviews' => [[
                'reviewer' => ['displayName' => 'Dina'],
                'starRating' => 'FIVE',
                'comment' => '<script>alert("review")</script>',
                'createTime' => '2026-10-03T08:30:00Z',
                'reviewReply' => ['comment' => 'Thank you for your review.'],
            ]],
            'averageRating' => 4.8,
            'totalReviewCount' => 101,
            'nextPageToken' => 'next-page-token',
        ]),
    ]);

    $this->get(route('reviews.index', ['google_page_token' => 'current-page-token']))
        ->assertOk()
        ->assertSee(__('store.google_reviews_heading'))
        ->assertSee('4.8')
        ->assertSee(__('store.review_count', ['count' => '101']))
        ->assertSee('Dina')
        ->assertSee('<script>alert("review")</script>')
        ->assertDontSee('<script>alert("review")</script>', false)
        ->assertSee('Thank you for your review.')
        ->assertSee(route('reviews.index', ['google_page_token' => 'next-page-token']), false);

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://mybusiness.googleapis.com/v4/accounts/123456789/locations/987654321/reviews?pageSize=50&orderBy=updateTime%20desc&pageToken=current-page-token'
        && $request->hasHeader('Authorization', 'Bearer access-token'));
});

it('shows an explicit unavailable state when Google reviews cannot be fetched', function () {
    config([
        'services.google_business_profile.client_id' => 'client-id',
        'services.google_business_profile.client_secret' => 'client-secret',
        'services.google_business_profile.redirect_uri' => 'https://meoxstore.test/admin/google-business-profile/callback',
    ]);

    GoogleBusinessProfileConnection::factory()->create([
        'id' => 1,
        'refresh_token' => 'refresh-token',
        'location_name' => 'accounts/123456789/locations/987654321',
    ]);

    $googleClient = Mockery::mock(Client::class);
    $googleClient->shouldReceive('setClientId')->once()->with('client-id');
    $googleClient->shouldReceive('setClientSecret')->once()->with('client-secret');
    $googleClient->shouldReceive('setRedirectUri')->once()->with('https://meoxstore.test/admin/google-business-profile/callback');
    $googleClient->shouldReceive('fetchAccessTokenWithRefreshToken')
        ->once()
        ->with('refresh-token')
        ->andReturn(['access_token' => 'access-token', 'expires_in' => 3600]);
    $this->app->instance(Client::class, $googleClient);

    Http::preventStrayRequests();
    Http::fake([
        'mybusiness.googleapis.com/v4/accounts/123456789/locations/987654321/reviews*' => Http::response([], 503),
    ]);

    $this->get(route('reviews.index'))
        ->assertOk()
        ->assertSee(__('store.google_reviews_unavailable'));
});

it('shows an explicit unavailable state when Google returns malformed review data', function () {
    config([
        'services.google_business_profile.client_id' => 'client-id',
        'services.google_business_profile.client_secret' => 'client-secret',
        'services.google_business_profile.redirect_uri' => 'https://meoxstore.test/admin/google-business-profile/callback',
    ]);

    GoogleBusinessProfileConnection::factory()->create([
        'id' => 1,
        'refresh_token' => 'refresh-token',
        'location_name' => 'accounts/123456789/locations/987654321',
    ]);

    $googleClient = Mockery::mock(Client::class);
    $googleClient->shouldReceive('setClientId')->once()->with('client-id');
    $googleClient->shouldReceive('setClientSecret')->once()->with('client-secret');
    $googleClient->shouldReceive('setRedirectUri')->once()->with('https://meoxstore.test/admin/google-business-profile/callback');
    $googleClient->shouldReceive('fetchAccessTokenWithRefreshToken')
        ->once()
        ->with('refresh-token')
        ->andReturn(['access_token' => 'access-token', 'expires_in' => 3600]);
    $this->app->instance(Client::class, $googleClient);

    Http::preventStrayRequests();
    Http::fake([
        'mybusiness.googleapis.com/v4/accounts/123456789/locations/987654321/reviews*' => Http::response([
            'reviews' => [['starRating' => 'UNKNOWN']],
            'averageRating' => 4.8,
            'totalReviewCount' => 1,
        ]),
    ]);

    $this->get(route('reviews.index'))
        ->assertOk()
        ->assertSee(__('store.google_reviews_unavailable'));
});
