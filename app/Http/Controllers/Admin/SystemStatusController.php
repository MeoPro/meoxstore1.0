<?php

namespace App\Http\Controllers\Admin;

use App\Console\Commands\ProcessQueueWorker;
use App\Console\Commands\TelegramSetWebhook;
use App\Http\Controllers\Controller;
use App\Services\TelegramBotApi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class SystemStatusController extends Controller
{
    private const SCHEDULER_HEARTBEAT_CACHE_KEY = 'admin.system-status.scheduler-last-seen';

    private const QUEUE_LAST_RUN_CACHE_KEY = 'admin.system-status.queue-last-run';

    public function index(TelegramBotApi $telegramBotApi): View
    {
        return view('admin.system-status.index', [
            'mailStatus' => $this->mailStatus(),
            'telegramStatus' => $this->telegramStatus($telegramBotApi),
            'queueStatus' => $this->queueStatus(),
            'cronCommand' => '* * * * * cd '.escapeshellarg(base_path()).' && php artisan schedule:run >> /dev/null 2>&1',
        ]);
    }

    public function sendTestEmail(): RedirectResponse
    {
        $mailer = config('mail.default', 'log');
        $sender = config('mail.from.address');
        $recipient = config('store.admin_email');

        if (! is_string($mailer) || $this->hasNonDeliveringTransport($mailer)) {
            return back()->withErrors([
                'email' => 'Email is not being delivered: choose a real mail transport instead of log or array.',
            ]);
        }

        if (! is_string($sender) || filter_var($sender, FILTER_VALIDATE_EMAIL) === false) {
            return back()->withErrors(['email' => 'Configure a valid MAIL_FROM_ADDRESS before sending a test email.']);
        }

        if (! is_string($recipient) || filter_var($recipient, FILTER_VALIDATE_EMAIL) === false) {
            return back()->withErrors(['email' => 'Configure a valid STORE_ADMIN_EMAIL before sending a test email.']);
        }

        try {
            Mail::raw('This is a test email from the Meoxstore admin panel.', function (Message $message) use ($recipient): void {
                $message->to($recipient)->subject('Meoxstore email connection test');
            });
        } catch (TransportExceptionInterface $exception) {
            Log::warning('The admin email connectivity test failed.', [
                'mailer' => $mailer,
                'exception' => $exception::class,
            ]);

            return back()->withErrors([
                'email' => 'The test email could not be sent. Check the mail settings and application log.',
            ]);
        }

        return redirect()->route('admin.system-status.index')
            ->with('status', 'Test email sent to the configured store admin address.');
    }

    public function registerTelegramWebhook(): RedirectResponse
    {
        try {
            $exitCode = Artisan::call(TelegramSetWebhook::class);
        } catch (RuntimeException $exception) {
            Log::warning('The admin Telegram webhook registration failed.', [
                'exception' => $exception::class,
            ]);

            return back()->withErrors(['telegram' => $exception->getMessage()]);
        }

        $output = trim(Artisan::output());
        if ($exitCode !== 0) {
            Log::error('The Telegram webhook registration command failed.', ['output' => $output]);

            return back()->withErrors([
                'telegram' => $output !== '' ? $output : 'Telegram webhook registration failed. Check the application log.',
            ]);
        }

        return redirect()->route('admin.system-status.index')
            ->with('status', $output !== '' ? $output : 'Telegram webhook registered.');
    }

    public function processQueue(): RedirectResponse
    {
        $exitCode = Artisan::call(ProcessQueueWorker::class, ['--single' => true]);
        $output = trim(Artisan::output());

        if ($exitCode !== 0) {
            Log::error('The admin queue worker command failed.', ['output' => $output]);

            return back()->withErrors([
                'queue' => $output !== '' ? $output : 'Queue processing failed. Check the application log.',
            ]);
        }

        return redirect()->route('admin.system-status.index')
            ->with('status', $output !== '' ? $output : 'Queue processing finished.');
    }

    /**
     * @return array{
     *     mailer: string,
     *     transport: string,
     *     sender_configured: bool,
     *     recipient_configured: bool,
     *     delivery_disabled: bool,
     *     ready: bool
     * }
     */
    private function mailStatus(): array
    {
        $mailer = (string) config('mail.default', 'log');
        $transport = (string) config("mail.mailers.{$mailer}.transport", $mailer);
        $sender = config('mail.from.address');
        $recipient = config('store.admin_email');
        $senderConfigured = is_string($sender) && filter_var($sender, FILTER_VALIDATE_EMAIL) !== false;
        $recipientConfigured = is_string($recipient) && filter_var($recipient, FILTER_VALIDATE_EMAIL) !== false;
        $hasNonDeliveringTransport = $this->hasNonDeliveringTransport($mailer);

        return [
            'mailer' => $mailer,
            'transport' => $transport,
            'sender_configured' => $senderConfigured,
            'recipient_configured' => $recipientConfigured,
            'delivery_disabled' => $hasNonDeliveringTransport,
            'ready' => ! $hasNonDeliveringTransport
                && $senderConfigured
                && $recipientConfigured,
        ];
    }

    /**
     * @param  list<string>  $visitedMailers
     */
    private function hasNonDeliveringTransport(string $mailer, array $visitedMailers = []): bool
    {
        if (in_array($mailer, $visitedMailers, true)) {
            return true;
        }

        $visitedMailers[] = $mailer;
        $mailerConfig = config("mail.mailers.{$mailer}", []);
        $transport = is_array($mailerConfig) && is_string($mailerConfig['transport'] ?? null)
            ? $mailerConfig['transport']
            : $mailer;

        if (in_array($transport, ['log', 'array'], true)) {
            return true;
        }

        if (in_array($transport, ['failover', 'roundrobin'], true)) {
            $fallbackMailers = is_array($mailerConfig['mailers'] ?? null)
                ? $mailerConfig['mailers']
                : [];

            if ($fallbackMailers === []) {
                return true;
            }

            foreach ($fallbackMailers as $fallbackMailer) {
                if (! is_string($fallbackMailer) || $this->hasNonDeliveringTransport($fallbackMailer, $visitedMailers)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return array{
     *     bot_configured: bool,
     *     can_register_webhook: bool,
     *     connected: bool,
     *     username: ?string,
     *     webhook_registered: bool,
     *     webhook_url: string,
     *     pending_updates: ?int,
     *     last_webhook_error: ?string,
     *     connection_error: ?string
     * }
     */
    private function telegramStatus(TelegramBotApi $telegramBotApi): array
    {
        $token = config('services.telegram.bot_token');
        $webhookSecret = config('services.telegram.webhook_secret');
        $adminIds = config('services.telegram.admin_ids', []);
        $channelId = config('services.telegram.channel_id');
        $webhookUrl = route('telegram.webhook');
        $botConfigured = is_string($token) && $token !== '';
        $connected = false;
        $bot = [];
        $webhook = [];
        $connectionError = null;

        if ($botConfigured) {
            try {
                $bot = $telegramBotApi->getMe();
                $connected = true;
            } catch (RuntimeException $exception) {
                $connectionError = $exception->getMessage();
            }

            if ($connected) {
                try {
                    $webhook = $telegramBotApi->getWebhookInfo();
                } catch (RuntimeException $exception) {
                    $connectionError = $exception->getMessage();
                }
            }
        }

        $registeredWebhookUrl = $webhook['url'] ?? null;

        return [
            'bot_configured' => $botConfigured,
            'can_register_webhook' => $botConfigured
                && is_string($webhookSecret)
                && $webhookSecret !== ''
                && is_array($adminIds)
                && $adminIds !== []
                && is_string($channelId)
                && $channelId !== ''
                && str_starts_with($webhookUrl, 'https://'),
            'connected' => $connected,
            'username' => is_string($bot['username'] ?? null) ? $bot['username'] : null,
            'webhook_registered' => is_string($registeredWebhookUrl)
                && rtrim($registeredWebhookUrl, '/') === rtrim($webhookUrl, '/'),
            'webhook_url' => $webhookUrl,
            'pending_updates' => is_int($webhook['pending_update_count'] ?? null)
                ? $webhook['pending_update_count']
                : null,
            'last_webhook_error' => is_string($webhook['last_error_message'] ?? null)
                ? $webhook['last_error_message']
                : null,
            'connection_error' => $connectionError,
        ];
    }

    /**
     * @return array{
     *     connection: string,
     *     pending_jobs: ?int,
     *     failed_jobs: ?int,
     *     scheduler_active: bool,
     *     last_run: ?int
     * }
     */
    private function queueStatus(): array
    {
        $connection = (string) config('queue.default', 'sync');
        $queueConfig = config("queue.connections.{$connection}", []);
        $queueName = is_array($queueConfig) && is_string($queueConfig['queue'] ?? null)
            ? $queueConfig['queue']
            : 'default';
        $pendingJobs = null;
        $queueTable = is_array($queueConfig) ? ($queueConfig['table'] ?? null) : null;

        if (
            $connection === 'database'
            && is_string($queueTable)
            && Schema::hasTable($queueTable)
        ) {
            $pendingJobs = DB::table($queueTable)->where('queue', $queueName)->count();
        }

        $failedJobs = null;
        $failedJobsTable = config('queue.failed.table', 'failed_jobs');
        if (is_string($failedJobsTable) && Schema::hasTable($failedJobsTable)) {
            $failedJobs = DB::table($failedJobsTable)->count();
        }

        $schedulerHeartbeat = Cache::get(self::SCHEDULER_HEARTBEAT_CACHE_KEY);
        $lastRun = Cache::get(self::QUEUE_LAST_RUN_CACHE_KEY);

        return [
            'connection' => $connection,
            'pending_jobs' => $pendingJobs,
            'failed_jobs' => $failedJobs,
            'scheduler_active' => is_int($schedulerHeartbeat)
                && $schedulerHeartbeat >= now()->subMinutes(3)->timestamp,
            'last_run' => is_int($lastRun) ? $lastRun : null,
        ];
    }
}
