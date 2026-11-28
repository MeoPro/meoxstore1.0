<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class ProcessQueueWorker extends Command
{
    protected $signature = 'app:process-queue {--single : Process at most one queued job}';

    protected $description = 'Process a bounded batch of queued jobs.';

    public function handle(): int
    {
        $connection = (string) config('queue.default', 'sync');

        if ($connection === 'sync') {
            $this->info('The sync queue does not need a worker.');

            return self::SUCCESS;
        }

        $lock = Cache::lock('app:queue-worker', 480);
        if (! $lock->get()) {
            $this->info('A queue worker is already running.');

            return self::SUCCESS;
        }

        try {
            Cache::put('admin.system-status.queue-last-run', now()->timestamp, now()->addDays(30));

            $options = [
                'connection' => $connection,
                '--stop-when-empty' => true,
                '--max-jobs' => $this->option('single') ? 1 : 10,
                '--max-time' => $this->option('single') ? 20 : 50,
                '--timeout' => max(1, (int) config("queue.connections.{$connection}.retry_after", 90) - 10),
            ];
            $queueName = config("queue.connections.{$connection}.queue");
            if (is_string($queueName) && $queueName !== '') {
                $options['--queue'] = $queueName;
            }

            $exitCode = Artisan::call('queue:work', $options);
            $output = trim(Artisan::output());
            if ($output !== '') {
                $this->line($output);
            }

            return $exitCode;
        } finally {
            $lock->release();
        }
    }
}
