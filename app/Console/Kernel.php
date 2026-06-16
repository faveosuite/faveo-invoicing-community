<?php

namespace App\Console;

use App\BillingLog\Console\Commands\DeleteLogs;
use App\Console\Commands\AutorenewalExpirymail;
use App\Console\Commands\CleanupMsg91Reports;
use App\Console\Commands\DropTables;
use App\Console\Commands\ExpiryCron;
use App\Console\Commands\FailedMessageDelivery;
use App\Console\Commands\Inspire;
use App\Console\Commands\Install;
use App\Console\Commands\InstallDB;
use App\Console\Commands\invoiceDeletion;
use App\Console\Commands\moveImages;
use App\Console\Commands\PostExpiryCron;
use App\Console\Commands\RenewalCron;
use App\Console\Commands\ReoonLogsDeletion;
use App\Console\Commands\SetupTestEnv;
use App\Console\Commands\SyncDatabaseToLatestVersion;
use App\Http\Controllers\Common\PhpMailController;
use App\Jobs\NotifyMail;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Mailjob\ActivityLogDay;
use App\Model\Mailjob\CloudEmail as cloudemailsend;
use App\Model\Mailjob\Condition;
use Config;
use Exception;
use File;
use GuzzleHttp\Client;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Override;
use Schema;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
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
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  Schedule  $schedule
     * @return void
     */
    #[Override]
    protected function schedule(Schedule $schedule)
    {
        $this->execute($schedule, 'expiryMail');
        $this->execute($schedule, 'deleteLogs');
        $schedule->command('renewal:cron')->everyFiveMinutes();
        $schedule->command('app:failed-message-delivery')->hourly();
        $this->execute($schedule, 'subsExpirymail');
        $this->execute($schedule, 'postExpirymail');
        $this->execute($schedule, 'invoice');
        $this->execute($schedule, 'msg91Reports');
        $this->execute($schedule, 'reoon');
        $this->execute($schedule, 'systemLogs');

        // Schedule the cloudEmail method
        //Should not be touched unless you are changing something with cloud
        $schedule->call(function (): void {
            $lockFilePath = storage_path('cloudEmail.lock');

            // Check if the lock file exists
            if (! file_exists($lockFilePath)) {
                // Create the lock file
                file_put_contents($lockFilePath, '');

                // Execute the cloudEmail method
                $this->cloudEmail();

                // Remove the lock file
                unlink($lockFilePath);
            }
        })->everyFiveMinutes()->name('sendCloudEmail');

        $this->execute($schedule, 'installationLogs');
        $this->execute($schedule, 'licenseReportsCleanup');
        $this->execute($schedule, 'licenseCallbacksCleanup');
        $this->execute($schedule, 'licenseCrackReportsCleanup');
        $this->execute($schedule, 'licenseSystemReportsCleanup');
        $this->execute($schedule, 'licenseVersionsCleanup');

        if (config('database.DB_INSTALL')) {
            $condition = new Condition();
            $command = $condition->getConditionValue($task = 'cloud');
            $this->getCondition($schedule->job(new NotifyMail), $command);
        }
    }

    public function execute($schedule, $task)
    {
        $env = base_path('.env');
        if (File::exists($env) && (env('DB_INSTALL') == 1)) {
            $expiryMailStatus = StatusSetting::pluck('expiry_mail')->first();
            $logDeleteStatus = StatusSetting::pluck('activity_log_delete')->first();
            $RenewalexpiryMailStatus = StatusSetting::pluck('subs_expirymail')->first();
            $postExpirystatus = StatusSetting::pluck('post_expirymail')->first();
            $invoiceDeletionstatus = StatusSetting::pluck('invoice_deletion_status')->first();
            $delLogDays = ActivityLogDay::pluck('days')->first();
            if (Schema::hasColumn('status_settings', 'msg91_report_delete_status')) {
                $msgDeletionStatus = StatusSetting::value('msg91_report_delete_status');
            }

            if (Schema::hasColumn('status_settings', 'reoon_deletion_status')) {
                $reoonStatus = StatusSetting::pluck('reoon_deletion_status')->first();
            }

            if (Schema::hasColumn('status_settings', 'system_log_status')) {
                $systemLogsStatus = StatusSetting::pluck('system_log_status')->first();
            }

            if (Schema::hasColumn('status_settings', 'installation_logs_status')) {
                $installationLogsStatus = StatusSetting::value('installation_logs_status');
            }

            if (Schema::hasColumn('status_settings', 'license_reports_cleanup_status')) {
                $licenseReportsStatus = StatusSetting::value('license_reports_cleanup_status');
            }

            if (Schema::hasColumn('status_settings', 'license_callbacks_cleanup_status')) {
                $licenseCallbacksStatus = StatusSetting::value('license_callbacks_cleanup_status');
            }

            if (Schema::hasColumn('status_settings', 'license_crack_reports_cleanup_status')) {
                $licenseCrackStatus = StatusSetting::value('license_crack_reports_cleanup_status');
            }

            if (Schema::hasColumn('status_settings', 'license_system_reports_cleanup_status')) {
                $licenseSystemStatus = StatusSetting::value('license_system_reports_cleanup_status');
            }

            if (Schema::hasColumn('status_settings', 'license_versions_cleanup_status')) {
                $licenseVersionsStatus = StatusSetting::value('license_versions_cleanup_status');
            }

            if ($delLogDays == null) {
                $delLogDays = 99999999;
            }

            Config::set('activitylog.delete_records_older_than_days', $delLogDays);
            $condition = new Condition();
            $command = $condition->getConditionValue($task);
            switch ($task) {
                case 'expiryMail':
                    if ($expiryMailStatus == 1) {
                        return $this->getCondition($schedule->command('expiry:notification'), $command);
                    }

                case 'deleteLogs':
                    if ($logDeleteStatus == 1) {
                        return $this->getCondition($schedule->command('activitylog:clean --force'), $command);
                    }

                case 'subsExpirymail':
                    if ($RenewalexpiryMailStatus) {
                        return $this->getCondition($schedule->command('renewal:notification'), $command);
                    }
                case 'postExpirymail':
                    if ($postExpirystatus) {
                        return $this->getCondition($schedule->command('postexpiry:notification'), $command);
                    }
                case 'invoice':
                    if ($invoiceDeletionstatus) {
                        return $this->getCondition($schedule->command('invoices:delete'), $command);
                    }
                case 'msg91Reports':
                    if (isset($msgDeletionStatus) && $msgDeletionStatus) {
                        return $this->getCondition($schedule->command('cleanup:msg-reports'), $command);
                    }
                case 'reoon':
                    if (isset($reoonStatus) && $reoonStatus) {
                        return $this->getCondition($schedule->command('reoon:logs-deletion'), $command);
                    }
                case 'systemLogs':
                    if (isset($systemLogsStatus) && $systemLogsStatus) {
                        return $this->getCondition($schedule->command('logs:delete'), $command);
                    }
                case 'installationLogs':
                    if (isset($installationLogsStatus) && $installationLogsStatus) {
                        return $this->getCondition($schedule->command('installation:logs'), $command);
                    }
                case 'licenseReportsCleanup':
                    if (isset($licenseReportsStatus) && $licenseReportsStatus) {
                        return $this->getCondition($schedule->command('app:license-reports-cleanup'), $command);
                    }
                case 'licenseCallbacksCleanup':
                    if (isset($licenseCallbacksStatus) && $licenseCallbacksStatus) {
                        return $this->getCondition($schedule->command('app:crack-callback-cleanup'), $command);
                    }
                case 'licenseCrackReportsCleanup':
                    if (isset($licenseCrackStatus) && $licenseCrackStatus) {
                        return $this->getCondition($schedule->command('app:crack-reports-cleanup'), $command);
                    }
                case 'licenseSystemReportsCleanup':
                    if (isset($licenseSystemStatus) && $licenseSystemStatus) {
                        return $this->getCondition($schedule->command('app:system-reports-cleanup'), $command);
                    }
                case 'licenseVersionsCleanup':
                    if (isset($licenseVersionsStatus) && $licenseVersionsStatus) {
                        return $this->getCondition($schedule->command('app:versions-cleanup'), $command);
                    }
            }
        }
    }

    public function getCondition($schedule, $command)
    {
        $condition = $command['condition'];
        $at = $command['at'];

        return match ($condition) {
            'everyMinute' => $schedule->everyMinute(),
            'everyFiveMinutes' => $schedule->everyFiveMinutes(),
            'everyTenMinutes' => $schedule->everyTenMinutes(),
            'everyThirtyMinutes' => $schedule->everyThirtyMinutes(),
            'hourly' => $schedule->hourly(),
            'daily' => $schedule->daily(),
            'dailyAt' => $this->getConditionWithOption($schedule, $condition, $at),
            'weekly' => $schedule->weekly(),
            'monthly' => $schedule->monthly(),
            'yearly' => $schedule->yearly(),
            default => $schedule->everyMinute(),
        };
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
    #[Override]
    protected function commands()
    {
        require base_path('routes/console.php');
    }

    //This is to send an email to the client when the custom domain has been created properly
    public function cloudEmail()
    {
        try {
            $contact = getContactData();
            $setting = Setting::find(1);
            $mail = new PhpMailController();
            $clouds = cloudemailsend::cursor();

            foreach ($clouds as $cloud) {
                if ($this->checkTheAvailabilityOfCustomDomain($cloud->domain, $cloud->counter, $cloud->user)) {
                    $userData = $cloud->result_message.'.<br> Email:'.' '.$cloud->user.'<br>'.'Password:'.' '.$cloud->result_password;
                    $mail->SendEmail($setting->email, $cloud->user, $userData, 'New instance created', 'cloud-instance-created');
                    cloudemailsend::where('domain', $cloud->domain)->delete();
                }
            }
        } catch(Exception $e) {
            $this->googleChat($e->getMessage());
        }
    }

    private function checkTheAvailabilityOfCustomDomain($domain, $counter, $user)
    {
        $client = new Client();
        try {
            $response = $client->get('https://'.$domain);
            $statusCode = $response->getStatusCode();
            if ($statusCode >= 200 && $statusCode < 300) {
                $this->prepareMessages($domain, $counter, $user, true);

                return true;
            }
        } catch (Exception) {
            $this->prepareMessages($domain, $counter, $user);

            // The domain is not reachable or the SSL certificate is invalid.
            return false;
        }

        $this->prepareMessages($domain, $counter, $user);

        return false;
    }

    private function googleChat($text)
    {
        $url = env('GOOGLE_CHAT');
        $message = [
            'text' => $text,
        ];
        $message_headers = [
            'Content-Type' => 'application/json; charset=UTF-8',
        ];
        $client = new Client();
        $client->post($url, [
            'headers' => $message_headers,
            'body' => json_encode($message),
        ]);
    }

    private function prepareMessages($domain, $counter, $user, $success = false)
    {
        $lockFilePath = storage_path('cloudMessage.lock');

        // Check if the lock file exists
        if (! file_exists($lockFilePath)) {
            // Create the lock file
            file_put_contents($lockFilePath, '');

            // Execute the cloudEmail method
            if ($success) {
                $this->googleChat('Hello, It has come to my notice that this domain has been created successfully Domain name:'.$domain.' and this is their email: '.$user."\u{2705}\u{2705}\u{2705}");
            } else {
                cloudemailsend::where('domain', $domain)->increment('counter');
                if ($counter == 30) {
                    $this->googleChat('Hello, It has come to my notice that this domain has not been created successfully Domain name:'.$domain.' and this is their email: '.$user.'&#10060;'."\u{2716}\u{2716}\u{2716}");
                }
            }

            // Remove the lock file
            unlink($lockFilePath);
        }
    }
}
