<?php

namespace App\Console;

use App\Console\Commands\SetupTestEnv;
use App\Jobs\NotifyMail;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Mailjob\ActivityLogDay;
use App\Model\Mailjob\CloudEmail as cloudemailsend;
use GuzzleHttp\Client;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Torann\Currency\Console\Cleanup as CurrencyCleanup;
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
        \App\Console\Commands\DropTables::class,
        \App\Console\Commands\InstallDB::class,
        CurrencyManage::class,
        CurrencyCleanup::class,
        \App\Console\Commands\ExpiryCron::class,
        SetupTestEnv::class,
        \App\Console\Commands\SyncDatabaseToLatestVersion::class,
        \App\Console\Commands\RenewalCron::class,
        \App\Console\Commands\AutorenewalExpirymail::class,
        \App\Console\Commands\PostExpiryCron::class,
        \App\Console\Commands\moveImages::class,
        \App\Console\Commands\invoiceDeletion::class,
        \App\Console\Commands\CleanupMsg91Reports::class,

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
        $schedule->command('renewal:cron')->everyFiveMinutes();

        $this->execute($schedule, 'subsExpirymail');
        $this->execute($schedule, 'postExpirymail');
        $this->execute($schedule, 'invoice');
        $this->execute($schedule, 'msg91Reports');

        // Schedule the cloudEmail method
        //Should not be touched unless you are changing something with cloud
        $schedule->call(function () {
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

        if (config('database.DB_INSTALL')) {
            $condition = new \App\Model\Mailjob\Condition();
            $command = $condition->getConditionValue($task = 'cloud');
            $this->getCondition($schedule->job(new NotifyMail), $command);
        }
    }

    public function execute($schedule, $task)
    {
        $env = base_path('.env');
        if (\File::exists($env) && (env('DB_INSTALL') == 1)) {
            $expiryMailStatus = StatusSetting::pluck('expiry_mail')->first();
            $logDeleteStatus = StatusSetting::pluck('activity_log_delete')->first();
            $RenewalexpiryMailStatus = StatusSetting::pluck('subs_expirymail')->first();
            $postExpirystatus = StatusSetting::pluck('post_expirymail')->first();
            $invoiceDeletionstatus = StatusSetting::pluck('invoice_deletion_status')->first();
            $delLogDays = ActivityLogDay::pluck('days')->first();
            if (\Schema::hasColumn('status_settings', 'msg91_report_delete_status')) {
                $msgDeletionStatus = StatusSetting::value('msg91_report_delete_status');
            }
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
                case 'invoice':
                    if ($invoiceDeletionstatus) {
                        return $this->getCondition($schedule->command('invoices:delete'), $command);
                    }
                case 'msg91Reports':
                    if (isset($msgDeletionStatus) && $msgDeletionStatus) {
                        return $this->getCondition($schedule->command('cleanup:msg-reports'), $command);
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

    //This is to send an email to the client when the custom domain has been created properly
    public function cloudEmail()
    {
        try {
            $contact = getContactData();
            $setting = Setting::find(1);
            $mail = new \App\Http\Controllers\Common\PhpMailController();
            $clouds = cloudemailsend::cursor();

            foreach ($clouds as $cloud) {
                if ($this->checkTheAvailabilityOfCustomDomain($cloud->domain, $cloud->counter, $cloud->user)) {
                    $userData = $cloud->result_message.'.<br> Email:'.' '.$cloud->user.'<br>'.'Password:'.' '.$cloud->result_password;
                    $mail->SendEmail($setting->email, $cloud->user, $userData, 'New instance created');
                    cloudemailsend::where('domain', $cloud->domain)->delete();
                }
            }
        } catch(\Exception $e) {
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
        } catch (\Exception $e) {
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
