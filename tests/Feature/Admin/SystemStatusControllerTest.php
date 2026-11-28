<?php

use App\Console\Commands\ProcessQueueWorker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

it('restricts the system status page and its actions to administrators', function () {
    $this->get(route('admin.system-status.index'))
        ->assertRedirectToRoute('login');

    $nonAdmin = User::factory()->create(['is_admin' => false]);
    $this->actingAs($nonAdmin)
        ->get(route('admin.system-status.index'))
        ->assertForbidden();
    $this->post(route('admin.system-status.process-queue'))
        ->assertForbidden();

    config(['services.telegram.bot_token' => null]);

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get(route('admin.system-status.index'))
        ->assertOk()
        ->assertSee('System Status')
        ->assertSee('Send test email')
        ->assertSee('Automatic processing on cPanel');
});

it('shows Telegram connectivity and webhook details without exposing the bot token', function () {
    Http::preventStrayRequests();
    config([
        'services.telegram.bot_token' => '123:test-token',
        'services.telegram.webhook_secret' => 'webhook-secret',
        'services.telegram.admin_ids' => ['123'],
        'services.telegram.channel_id' => '-100123',
    ]);
    URL::forceRootUrl('https://meoxstore.test');
    URL::forceScheme('https');

    $webhookUrl = route('telegram.webhook');
    Http::fake([
        'api.telegram.org/bot123:test-token/getMe' => Http::response([
            'ok' => true,
            'result' => ['id' => 123, 'username' => 'meoxstore_test_bot'],
        ]),
        'api.telegram.org/bot123:test-token/getWebhookInfo' => Http::response([
            'ok' => true,
            'result' => [
                'url' => $webhookUrl,
                'pending_update_count' => 2,
            ],
        ]),
    ]);

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get(route('admin.system-status.index'))
        ->assertOk()
        ->assertSee('@meoxstore_test_bot')
        ->assertSee('Connected')
        ->assertSee('registered')
        ->assertDontSee('123:test-token');

    Http::assertSentCount(2);
});

it('reports when the Telegram bot API cannot be reached', function () {
    Http::preventStrayRequests();
    config(['services.telegram.bot_token' => '123:test-token']);
    Http::fake([
        'api.telegram.org/bot123:test-token/getMe' => Http::response([
            'ok' => false,
            'description' => 'Unauthorized',
        ], 401),
    ]);

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get(route('admin.system-status.index'))
        ->assertOk()
        ->assertSee('Connection failed')
        ->assertSee('HTTP 401');

    Http::assertSentCount(1);
});

it('registers the Telegram webhook from the admin page', function () {
    Http::preventStrayRequests();
    config([
        'services.telegram.bot_token' => '123:test-token',
        'services.telegram.webhook_secret' => 'webhook-secret',
        'services.telegram.admin_ids' => ['123'],
        'services.telegram.channel_id' => '-100123',
    ]);
    URL::forceRootUrl('https://meoxstore.test');
    URL::forceScheme('https');
    Http::fake([
        'api.telegram.org/bot123:test-token/setWebhook' => Http::response([
            'ok' => true,
            'result' => true,
        ]),
    ]);

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->post(route('admin.system-status.telegram-webhook'))
        ->assertRedirectToRoute('admin.system-status.index')
        ->assertSessionHas('status');

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.telegram.org/bot123:test-token/setWebhook'
        && $request->data()['url'] === route('telegram.webhook')
        && $request->data()['secret_token'] === 'webhook-secret');
});

it('sends a test email to the configured admin address using a real mail transport', function () {
    config([
        'mail.default' => 'smtp',
        'mail.mailers.smtp.transport' => 'smtp',
        'mail.mailers.smtp.host' => 'smtp.example.test',
        'mail.from.address' => 'sender@example.test',
        'store.admin_email' => 'admin@example.test',
    ]);

    Mail::shouldReceive('raw')
        ->once()
        ->withArgs(function (string $content, callable $callback): bool {
            $message = Mockery::mock(Message::class);
            $message->shouldReceive('to')->once()->with('admin@example.test')->andReturnSelf();
            $message->shouldReceive('subject')->once()->with('Meoxstore email connection test')->andReturnSelf();
            $callback($message);

            return $content === 'This is a test email from the Meoxstore admin panel.';
        });

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->post(route('admin.system-status.email-test'))
        ->assertRedirectToRoute('admin.system-status.index')
        ->assertSessionHas('status', 'Test email sent to the configured store admin address.');
});

it('does not claim to send a test email through a non-delivering mailer', function (string $mailer) {
    config([
        'mail.default' => $mailer,
        'mail.from.address' => 'sender@example.test',
        'store.admin_email' => 'admin@example.test',
    ]);
    Mail::shouldReceive('raw')->never();

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->post(route('admin.system-status.email-test'))
        ->assertRedirect()
        ->assertSessionHasErrors('email');
})->with([
    'log transport' => 'log',
    'array transport' => 'array',
    'failover with log fallback' => 'failover',
]);

it('runs a single queue job from the admin page', function () {
    Artisan::shouldReceive('call')
        ->once()
        ->with(ProcessQueueWorker::class, ['--single' => true])
        ->andReturn(0);
    Artisan::shouldReceive('output')
        ->once()
        ->andReturn('Queue run complete.');

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->post(route('admin.system-status.process-queue'))
        ->assertRedirectToRoute('admin.system-status.index')
        ->assertSessionHas('status', 'Queue run complete.');
});

it('limits a manual queue run to one job', function () {
    config(['queue.default' => 'database']);
    $cacheKey = 'system-status-queue-worker-test';
    Cache::forget($cacheKey);

    dispatch(function () use ($cacheKey): void {
        Cache::increment($cacheKey);
    });
    dispatch(function () use ($cacheKey): void {
        Cache::increment($cacheKey);
    });

    $this->artisan('app:process-queue', ['--single' => true])
        ->assertExitCode(0);

    expect(Cache::get($cacheKey))->toBe(1);
    $this->assertDatabaseCount('jobs', 1);
});

it('limits scheduled queue runs to ten jobs', function () {
    config(['queue.default' => 'database']);
    $cacheKey = 'system-status-scheduled-queue-worker-test';
    Cache::forget($cacheKey);

    foreach (range(1, 11) as $_) {
        dispatch(function () use ($cacheKey): void {
            Cache::increment($cacheKey);
        });
    }

    $this->artisan('app:process-queue')
        ->assertExitCode(0);

    expect(Cache::get($cacheKey))->toBe(10);
    $this->assertDatabaseCount('jobs', 1);
});
