<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\LoggableCommand;
use App\Services\Cloud\CloudInstanceEmailNotifier;

class SendCloudInstanceEmails extends LoggableCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloud:send-instance-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email new cloud customers their login once their custom domain is live';

    /**
     * Execute the console command.
     */
    public function handleAndLog(CloudInstanceEmailNotifier $notifier): void
    {
        $notifier();
    }
}
