@extends('admin.layout')

@section('title', 'System Status')

@section('content')
    <div class="page-heading">
        <div>
            <h1>System Status</h1>
            <p>Check email, Telegram, and background queue activity.</p>
        </div>
        <a href="{{ route('admin.system-status.index') }}" class="btn btn-outline-secondary">Refresh</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif

    @foreach (['email', 'telegram', 'queue'] as $errorKey)
        @if ($errors->has($errorKey))
            <div class="alert alert-danger" role="alert">{{ $errors->first($errorKey) }}</div>
        @endif
    @endforeach

    <div class="row g-3">
        <div class="col-12 col-xl-6">
            <section class="panel p-4 h-100">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h2 class="h5 mb-1">Email</h2>
                        <p class="text-muted mb-0">Mailer: <strong>{{ $mailStatus['mailer'] }}</strong></p>
                    </div>
                    <span class="badge {{ $mailStatus['ready'] ? 'text-bg-success' : 'text-bg-warning' }}">
                        {{ $mailStatus['ready'] ? 'Configured' : 'Needs configuration' }}
                    </span>
                </div>
                <ul class="list-unstyled mb-3">
                    <li>Sender address: {{ $mailStatus['sender_configured'] ? 'configured' : 'missing or invalid' }}</li>
                    <li>Admin recipient: {{ $mailStatus['recipient_configured'] ? 'configured' : 'missing or invalid' }}</li>
                    @if ($mailStatus['delivery_disabled'])
                        <li class="text-warning-emphasis">The current transport may log email instead of delivering it.</li>
                    @endif
                </ul>
                <form method="POST" action="{{ route('admin.system-status.email-test') }}">
                    @csrf
                    <button class="btn btn-primary" type="submit" @disabled(! $mailStatus['ready'])>
                        Send test email
                    </button>
                </form>
            </section>
        </div>

        <div class="col-12 col-xl-6">
            <section class="panel p-4 h-100">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h2 class="h5 mb-1">Telegram bot</h2>
                        <p class="text-muted mb-0">
                            @if ($telegramStatus['username'])
                                {{ '@'.$telegramStatus['username'] }}
                            @else
                                Bot token {{ $telegramStatus['bot_configured'] ? 'configured' : 'not configured' }}
                            @endif
                        </p>
                    </div>
                    @if ($telegramStatus['connected'])
                        <span class="badge text-bg-success">Connected</span>
                    @elseif ($telegramStatus['bot_configured'])
                        <span class="badge text-bg-danger">Connection failed</span>
                    @else
                        <span class="badge text-bg-warning">Not configured</span>
                    @endif
                </div>
                <ul class="list-unstyled mb-3">
                    <li>Webhook: {{ $telegramStatus['webhook_registered'] ? 'registered' : 'not registered' }}</li>
                    <li>Pending Telegram updates: {{ $telegramStatus['pending_updates'] ?? 'unknown' }}</li>
                    <li class="text-break">Webhook URL: <code>{{ $telegramStatus['webhook_url'] }}</code></li>
                </ul>
                @if ($telegramStatus['connection_error'])
                    <div class="alert alert-warning py-2">{{ $telegramStatus['connection_error'] }}</div>
                @endif
                @if ($telegramStatus['last_webhook_error'])
                    <div class="alert alert-warning py-2">Telegram webhook: {{ $telegramStatus['last_webhook_error'] }}</div>
                @endif
                <form method="POST" action="{{ route('admin.system-status.telegram-webhook') }}">
                    @csrf
                    <button class="btn btn-primary" type="submit" @disabled(! $telegramStatus['can_register_webhook'])>
                        Register webhook
                    </button>
                </form>
            </section>
        </div>

        <div class="col-12">
            <section class="panel p-4">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h2 class="h5 mb-1">Email and Telegram queue</h2>
                        <p class="text-muted mb-0">Queue connection: <strong>{{ $queueStatus['connection'] }}</strong></p>
                    </div>
                    @if ($queueStatus['connection'] === 'sync')
                        <span class="badge text-bg-secondary">No worker required</span>
                    @elseif ($queueStatus['scheduler_active'])
                        <span class="badge text-bg-success">cPanel Cron active</span>
                    @else
                        <span class="badge text-bg-warning">Cron heartbeat not seen</span>
                    @endif
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6 col-lg-3">
                        <div class="text-muted small">Pending jobs</div>
                        <strong>{{ $queueStatus['pending_jobs'] ?? 'unavailable' }}</strong>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="text-muted small">Failed jobs</div>
                        <strong>{{ $queueStatus['failed_jobs'] ?? 'unavailable' }}</strong>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="text-muted small">Last worker run</div>
                        <strong>
                            {{ $queueStatus['last_run'] ? \Illuminate\Support\Carbon::createFromTimestamp($queueStatus['last_run'])->diffForHumans() : 'not recorded yet' }}
                        </strong>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.system-status.process-queue') }}" class="mb-4">
                    @csrf
                    <button class="btn btn-primary" type="submit" @disabled($queueStatus['connection'] === 'sync')>
                        Process one job now
                    </button>
                    <span class="small text-muted ms-2">Runs a single job in this request; long-running jobs can take several minutes.</span>
                </form>

                <div class="border-top pt-3">
                    <h3 class="h6">Automatic processing on cPanel</h3>
                    <p class="text-muted mb-2">Add this command in cPanel → Cron Jobs and set it to run once every minute:</p>
                    <code class="d-block text-break user-select-all">{{ $cronCommand }}</code>
                    <p class="small text-muted mt-2 mb-0">
                        The scheduler processes up to 10 jobs per run and stops automatically. It does not keep a permanent worker running.
                    </p>
                </div>
            </section>
        </div>
    </div>
@endsection
