<?php

namespace App\Console;

use App\BillingLog\Console\Commands\DeleteLogs;
use App\Console\Commands\AutorenewalExpirymail;
use App\Console\Commands\CleanupActivityLogs;
use App\Console\Commands\CleanupMsg91Reports;
use App\Console\Commands\DropTables;
use App\Console\Commands\ExpiryCron;
use App\Console\Commands\FailedMessageDelivery;
use App\Console\Commands\GenerateSeoFiles;
use App\Console\Commands\Inspire;
use App\Console\Commands\Install;
use App\Console\Commands\InstallDB;
use App\Console\Commands\invoiceDeletion;
use App\Console\Commands\moveImages;
use App\Console\Commands\PostExpiryCron;
use App\Console\Commands\RenewalCron;
use App\Console\Commands\ReoonLogsDeletion;
use App\Console\Commands\SendCloudInstanceEmails;
use App\Console\Commands\SetupTestEnv;
use App\Console\Commands\SyncDatabaseToLatestVersion;
use App\Jobs\DeleteExpiredCloudInstances;
use App\Model\Mailjob\Condition;
use File;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Schema;
use Override;

class Kernel extends ConsoleKernel
{
    /** @var array<mixed> */
    protected $commands = [
        Inspire::class,
        Install::class,
        DropTables::class,
        InstallDB::class,
        ExpiryCron::class,
        SetupTestEnv::class,
        SyncDatabaseToLatestVersion::class,
        RenewalCron::class,
        AutorenewalExpirymail::class,
        PostExpiryCron::class,
        moveImages::class,
        invoiceDeletion::class,
        CleanupMsg91Reports::class,
        DeleteLogs::class,
        ReoonLogsDeletion::class,
        FailedMessageDelivery::class,
        GenerateSeoFiles::class,
        CleanupActivityLogs::class,
        SendCloudInstanceEmails::class,
    ];

    #[Override]
    protected function schedule(Schedule $schedule): void
    {
        // Overlapping runs created two Stripe subscriptions for one renewal.
        $schedule->command('renewal:cron')->everyFiveMinutes()->withoutOverlapping(60)->onOneServer();
        $schedule->command('app:failed-message-delivery')->hourly();
        $schedule->command('seo:generate-files')->hourly();

        // Background: each domain check can take up to 10s and must not hold up the tasks after it.
        $schedule->command('cloud:send-instance-emails')->everyFiveMinutes()->withoutOverlapping(60)->runInBackground();

        $this->scheduleConfigurableTasks($schedule);
    }

    public function scheduleConfigurableTasks(Schedule $schedule): void
    {
        // schedule() also runs when `migrate` boots, before these columns exist.
        if (! $this->isInstalled() || ! Schema::hasColumn('conditions', 'active')) {
            return;
        }

        foreach (Condition::where('active', true)->orderBy('id')->get() as $task) {
            $event = match (true) {
                $task->job === 'cloud' => $schedule->job(new DeleteExpiredCloudInstances), // a queued job, not a command
                $task->command !== null => $schedule->command($task->command),
                default => null,
            };
            if ($event === null) {
                continue;
            }

            $this->getCondition($event, $task->frequency());

            if ($task->is_background) {
                $event->runInBackground();
            }
        }
    }

    /**
     * @param  array<mixed>  $command
     */
    public function getCondition(mixed $schedule, array $command): mixed
    {
        return match ($command['condition']) {
            'everyFiveMinutes' => $schedule->everyFiveMinutes(),
            'everyTenMinutes' => $schedule->everyTenMinutes(),
            'everyThirtyMinutes' => $schedule->everyThirtyMinutes(),
            'hourly' => $schedule->hourly(),
            'daily' => $schedule->daily(),
            'dailyAt' => $schedule->dailyAt($command['at']),
            'weekly' => $schedule->weekly(),
            'monthly' => $schedule->monthly(),
            'yearly' => $schedule->yearly(),
            default => $schedule->everyMinute(),
        };
    }

    #[Override]
    protected function commands(): void
    {
        require base_path('routes/console.php');
    }

    private function isInstalled(): bool
    {
        // The env file actually loaded: .env in production, .env.testing under APP_ENV=testing (CI).
        return File::exists(app()->environmentFilePath()) && config('custom.db_install') == 1;
    }
}
