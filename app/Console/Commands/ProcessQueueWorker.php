<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class ProcessQueueWorker extends Command
{
    protected $signature = 'app:process-queue {--single : Process at most one queued job}';

    protected $description = 'Drain queued jobs until empty, or process one job when requested.';

    public function handle(): int
    {
        $connection = (string) config('queue.default', 'sync');

        if ($connection === 'sync') {
            $this->info('The sync queue does not need a worker.');

            return self::SUCCESS;
        }

        Cache::put('admin.system-status.queue-last-run', now()->timestamp, now()->addDays(30));

        $options = [
            'connection' => $connection,
            '--stop-when-empty' => true,
            '--timeout' => max(1, (int) config("queue.connections.{$connection}.retry_after", 90) - 10),
        ];
        if ($this->option('single')) {
            $options['--max-jobs'] = 1;
            $options['--max-time'] = 20;
        }

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
    }
}
