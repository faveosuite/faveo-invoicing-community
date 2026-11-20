<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Cloud\CloudEvents;
use Illuminate\Console\Command;

/**
 * Sends the due events in the Faveo Cloud outbox (every minute), and with --reconcile queues a
 * fresh license.updated for every active cloud license (daily safety net).
 */
class SendCloudEvents extends Command
{
    protected $signature = 'cloud:events {--reconcile : Queue license.updated for every active cloud license}';

    protected $description = 'Send due events to Faveo Cloud (or queue the daily license resend)';

    public function handle(CloudEvents $events): int
    {
        if ($this->option('reconcile')) {
            $this->info('Queued: '.$events->reconcile());
        }
        $this->info('Accepted by the cloud: '.$events->sendDue());

        return self::SUCCESS;
    }
}
