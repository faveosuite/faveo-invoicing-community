<?php

namespace App\Console;

use App\Console\Commands\SetupTestEnv;
use App\Jobs\CloudEmail;
use App\Model\Common\StatusSetting;
use App\Model\Mailjob\ActivityLogDay;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Schema;
use Torann\Currency\Console\Manage as CurrencyManage;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        \App\Console\Commands\Inspire::class,
        \App\Console\Commands\Install::class,
        CurrencyManage::class,
        \App\Console\Commands\ExpiryCron::class,
        SetupTestEnv::class,
        \App\Console\Commands\SyncDatabaseToLatestVersion::class,
        \App\Console\Commands\RenewalCron::class,
        \App\Console\Commands\AutorenewalExpirymail::class,
        \App\Console\Commands\PostExpiryCron::class,

    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $this->execute($schedule, 'expiryMail');
        $this->execute($schedule, 'deleteLogs');
        $schedule->command('renewal:cron')
                 ->daily();
        $this->execute($schedule, 'subsExpirymail');
        $this->execute($schedule, 'postExpirymail');
        $schedule->job(new CloudEmail)->everyMinute();
    }

    public function execute($schedule, $task)
    {
        $env = base_path('.env');
        if (\File::exists($env) && (env('DB_INSTALL') == 1)) {
            if (Schema::hasColumn('expiry_mail', 'activity_log_delete', 'subs_expirymail', 'post_expirymail', 'days')) {
                $expiryMailStatus = StatusSetting::pluck('expiry_mail')->first();
                $logDeleteStatus = StatusSetting::pluck('activity_log_delete')->first();
                $RenewalexpiryMailStatus = StatusSetting::pluck('subs_expirymail')->first();
                $postExpirystatus = StatusSetting::pluck('post_expirymail')->first();
                $delLogDays = ActivityLogDay::pluck('days')->first();
                if ($delLogDays == null) {
                    $delLogDays = 99999999;
                }
                \Config::set('activitylog.delete_records_older_than_days', $delLogDays);
                $condition = new \App\Model\Mailjob\Condition();
                $command = $condition->getConditionValue($task);
                switch ($task) {
                    case 'expiryMail':
                        if ($expiryMailStatus == 1) {
                            return $this->getCondition($schedule->command('expiry:notification'), $command);
                        }

                    case 'deleteLogs':
                        if ($logDeleteStatus == 1) {
                            return $this->getCondition($schedule->command('activitylog:clean'), $command);
                        }

                    case 'subsExpirymail':
                        if ($RenewalexpiryMailStatus) {
                            return $this->getCondition($schedule->command('renewal:notification'), $command);
                        }
                    case 'postExpirymail':
                        if ($postExpirystatus) {
                            return $this->getCondition($schedule->command('postexpiry:notification'), $command);
                        }
                }
            }
        }
    }

    public function getCondition($schedule, $command)
    {
        $condition = $command['condition'];
        $at = $command['at'];
        switch ($condition) {
            case 'everyMinute':
                return $schedule->everyMinute();
            case 'everyFiveMinutes':
                return $schedule->everyFiveMinutes();
            case 'everyTenMinutes':
                return $schedule->everyTenMinutes();
            case 'everyThirtyMinutes':
                return $schedule->everyThirtyMinutes();
            case 'hourly':
                return $schedule->hourly();
            case 'daily':
                return $schedule->daily();
            case 'dailyAt':
                return $this->getConditionWithOption($schedule, $condition, $at);
            case 'weekly':
                return $schedule->weekly();
            case 'monthly':
                return $schedule->monthly();
            case 'yearly':
                return $schedule->yearly();
            default:
                return $schedule->everyMinute();
        }
    }

    public function getConditionWithOption($schedule, $command, $at)
    {
        switch ($command) {
            case 'dailyAt':
                return $schedule->dailyAt($at);
        }
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
