<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Http\Controllers\Common\PhpMailController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyMail implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PhpMailController $phpMailController): void
    {
        $phpMailController->NotifyMailing();
    }
}
