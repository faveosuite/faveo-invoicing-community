<?php

namespace App\Jobs;

use App\Http\Controllers\Common\PhpMailController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyMail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PhpMailController $phpMailController)
    {
        $p = $phpMailController->NotifyMailing();

        return $p;
    }
}
