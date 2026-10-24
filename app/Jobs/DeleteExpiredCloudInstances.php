<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Cloud\ExpiredCloudInstanceCleaner;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * The "cloud" cron task, queued by the scheduler. Unique, so a run that outlasts the schedule
 * interval cannot overlap the next one and email the same customers twice.
 */
class DeleteExpiredCloudInstances implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /** Seconds before the uniqueness lock is released if a worker dies mid-run. */
    public int $uniqueFor = 3600;

    /**
     * Execute the job.
     */
    public function handle(ExpiredCloudInstanceCleaner $cleaner): void
    {
        $cleaner();
    }
}
