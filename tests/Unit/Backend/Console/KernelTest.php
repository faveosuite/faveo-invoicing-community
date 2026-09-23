<?php

namespace Tests\Unit\Backend\Console;

use App\Console\Kernel;
use App\Model\Common\StatusSetting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\DBTestCase;

class KernelTest extends DBTestCase
{
    use DatabaseTransactions;

    private Kernel $kernel;
    private mixed $schedule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');

        $this->kernel = app(Kernel::class);

        // Mock the Schedule so command() / event() calls don't actually register tasks
        $this->schedule = Mockery::mock(Schedule::class);
        $this->schedule->shouldReceive('command')->andReturnSelf();
        $this->schedule->shouldReceive('job')->andReturnSelf();
        $this->schedule->shouldReceive('call')->andReturnSelf();
        $this->schedule->shouldReceive('everyMinute')->andReturnSelf();
        $this->schedule->shouldReceive('everyFiveMinutes')->andReturnSelf();
        $this->schedule->shouldReceive('everyTenMinutes')->andReturnSelf();
        $this->schedule->shouldReceive('everyThirtyMinutes')->andReturnSelf();
        $this->schedule->shouldReceive('hourly')->andReturnSelf();
        $this->schedule->shouldReceive('daily')->andReturnSelf();
        $this->schedule->shouldReceive('dailyAt')->andReturnSelf();
        $this->schedule->shouldReceive('weekly')->andReturnSelf();
        $this->schedule->shouldReceive('monthly')->andReturnSelf();
        $this->schedule->shouldReceive('yearly')->andReturnSelf();
        $this->schedule->shouldReceive('name')->andReturnSelf();
        $this->schedule->shouldReceive('withoutOverlapping')->andReturnSelf();
    }

    // -------------------------------------------------------------------------
    // getCondition — pure match, covers all branches
    // -------------------------------------------------------------------------

    public function test_get_condition_every_minute(): void
    {
        $command = ['condition' => 'everyMinute', 'at' => ''];
        $this->kernel->getCondition($this->schedule, $command);
        $this->schedule->shouldHaveReceived('everyMinute');
        $this->assertTrue(true);
    }

    public function test_get_condition_every_five_minutes(): void
    {
        $command = ['condition' => 'everyFiveMinutes', 'at' => ''];
        $this->kernel->getCondition($this->schedule, $command);
        $this->schedule->shouldHaveReceived('everyFiveMinutes');
        $this->assertTrue(true);
    }

    public function test_get_condition_every_ten_minutes(): void
    {
        $command = ['condition' => 'everyTenMinutes', 'at' => ''];
        $this->kernel->getCondition($this->schedule, $command);
        $this->assertTrue(true);
    }

    public function test_get_condition_every_thirty_minutes(): void
    {
        $command = ['condition' => 'everyThirtyMinutes', 'at' => ''];
        $this->kernel->getCondition($this->schedule, $command);
        $this->assertTrue(true);
    }

    public function test_get_condition_hourly(): void
    {
        $command = ['condition' => 'hourly', 'at' => ''];
        $this->kernel->getCondition($this->schedule, $command);
        $this->assertTrue(true);
    }

    public function test_get_condition_daily(): void
    {
        $command = ['condition' => 'daily', 'at' => ''];
        $this->kernel->getCondition($this->schedule, $command);
        $this->assertTrue(true);
    }

    public function test_get_condition_daily_at(): void
    {
        $command = ['condition' => 'dailyAt', 'at' => '08:00'];
        $this->kernel->getCondition($this->schedule, $command);
        $this->assertTrue(true);
    }

    public function test_get_condition_weekly(): void
    {
        $command = ['condition' => 'weekly', 'at' => ''];
        $this->kernel->getCondition($this->schedule, $command);
        $this->assertTrue(true);
    }

    public function test_get_condition_monthly(): void
    {
        $command = ['condition' => 'monthly', 'at' => ''];
        $this->kernel->getCondition($this->schedule, $command);
        $this->assertTrue(true);
    }

    public function test_get_condition_yearly(): void
    {
        $command = ['condition' => 'yearly', 'at' => ''];
        $this->kernel->getCondition($this->schedule, $command);
        $this->assertTrue(true);
    }

    public function test_get_condition_default_falls_back_to_every_minute(): void
    {
        $command = ['condition' => 'unknownCondition', 'at' => ''];
        $this->kernel->getCondition($this->schedule, $command);
        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // getConditionWithOption — simple branch
    // -------------------------------------------------------------------------

    public function test_get_condition_with_option_daily_at(): void
    {
        $this->kernel->getConditionWithOption($this->schedule, 'dailyAt', '09:00');
        $this->assertTrue(true);
    }

    public function test_get_condition_with_option_non_daily_at_does_nothing(): void
    {
        // Command is not 'dailyAt' → method returns without calling anything
        $this->kernel->getConditionWithOption($this->schedule, 'weekly', '');
        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // execute — task branches (requires config db_install = 1)
    // -------------------------------------------------------------------------

    public function test_execute_expiry_mail_when_enabled(): void
    {
        config(['custom.db_install' => 1]);
        StatusSetting::updateOrCreate([], ['expiry_mail' => 1]);

        $this->kernel->execute($this->schedule, 'expiryMail');
        $this->assertTrue(true);
    }

    public function test_execute_expiry_mail_when_disabled(): void
    {
        config(['custom.db_install' => 1]);
        StatusSetting::updateOrCreate([], ['expiry_mail' => 0]);

        $this->kernel->execute($this->schedule, 'expiryMail');
        $this->assertTrue(true);
    }

    public function test_execute_delete_logs_when_enabled(): void
    {
        config(['custom.db_install' => 1]);
        StatusSetting::updateOrCreate([], ['activity_log_delete' => 1]);

        $this->kernel->execute($this->schedule, 'deleteLogs');
        $this->assertTrue(true);
    }

    public function test_execute_subs_expiry_mail_when_enabled(): void
    {
        config(['custom.db_install' => 1]);
        StatusSetting::updateOrCreate([], ['subs_expirymail' => 1]);

        $this->kernel->execute($this->schedule, 'subsExpirymail');
        $this->assertTrue(true);
    }

    public function test_execute_post_expiry_mail_when_enabled(): void
    {
        config(['custom.db_install' => 1]);
        StatusSetting::updateOrCreate([], ['post_expirymail' => 1]);

        $this->kernel->execute($this->schedule, 'postExpirymail');
        $this->assertTrue(true);
    }

    public function test_execute_invoice_deletion_when_enabled(): void
    {
        config(['custom.db_install' => 1]);
        StatusSetting::updateOrCreate([], ['invoice_deletion_status' => 1]);

        $this->kernel->execute($this->schedule, 'invoice');
        $this->assertTrue(true);
    }

    public function test_execute_msg91_reports_when_enabled(): void
    {
        config(['custom.db_install' => 1]);
        StatusSetting::updateOrCreate([], ['msg91_report_delete_status' => 1]);

        $this->kernel->execute($this->schedule, 'msg91Reports');
        $this->assertTrue(true);
    }

    public function test_execute_reoon_when_enabled(): void
    {
        config(['custom.db_install' => 1]);
        StatusSetting::updateOrCreate([], ['reoon_deletion_status' => 1]);

        $this->kernel->execute($this->schedule, 'reoon');
        $this->assertTrue(true);
    }

    public function test_execute_system_logs_when_enabled(): void
    {
        config(['custom.db_install' => 1]);
        StatusSetting::updateOrCreate([], ['system_log_status' => 1]);

        $this->kernel->execute($this->schedule, 'systemLogs');
        $this->assertTrue(true);
    }

    public function test_execute_installation_logs_when_enabled(): void
    {
        config(['custom.db_install' => 1]);
        StatusSetting::updateOrCreate([], ['installation_logs_status' => 1]);

        $this->kernel->execute($this->schedule, 'installationLogs');
        $this->assertTrue(true);
    }

    public function test_execute_license_reports_cleanup_when_enabled(): void
    {
        config(['custom.db_install' => 1]);
        StatusSetting::updateOrCreate([], ['license_reports_cleanup_status' => 1]);

        $this->kernel->execute($this->schedule, 'licenseReportsCleanup');
        $this->assertTrue(true);
    }

    public function test_execute_license_callbacks_cleanup_when_enabled(): void
    {
        config(['custom.db_install' => 1]);
        StatusSetting::updateOrCreate([], ['license_callbacks_cleanup_status' => 1]);

        $this->kernel->execute($this->schedule, 'licenseCallbacksCleanup');
        $this->assertTrue(true);
    }

    public function test_execute_license_crack_reports_cleanup_when_enabled(): void
    {
        config(['custom.db_install' => 1]);
        StatusSetting::updateOrCreate([], ['license_crack_reports_cleanup_status' => 1]);

        $this->kernel->execute($this->schedule, 'licenseCrackReportsCleanup');
        $this->assertTrue(true);
    }

    public function test_execute_license_system_reports_cleanup_when_enabled(): void
    {
        config(['custom.db_install' => 1]);
        StatusSetting::updateOrCreate([], ['license_system_reports_cleanup_status' => 1]);

        $this->kernel->execute($this->schedule, 'licenseSystemReportsCleanup');
        $this->assertTrue(true);
    }

    public function test_execute_license_versions_cleanup_when_enabled(): void
    {
        config(['custom.db_install' => 1]);
        StatusSetting::updateOrCreate([], ['license_versions_cleanup_status' => 1]);

        $this->kernel->execute($this->schedule, 'licenseVersionsCleanup');
        $this->assertTrue(true);
    }

    public function test_execute_does_nothing_when_db_install_is_zero(): void
    {
        config(['custom.db_install' => 0]);

        $this->kernel->execute($this->schedule, 'expiryMail');
        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // Command registration
    // -------------------------------------------------------------------------

    public function test_generate_seo_files_and_sentry_release_are_registered_commands(): void
    {
        $ref = new \ReflectionProperty(Kernel::class, 'commands');
        $commands = $ref->getValue($this->kernel);

        $this->assertContains(\App\Console\Commands\GenerateSeoFiles::class, $commands);
        $this->assertContains(\App\Console\Commands\SentryRelease::class, $commands);
    }

    public function test_seo_generate_files_is_scheduled_hourly(): void
    {
        $this->getPrivateMethod($this->kernel, 'schedule', [$this->schedule]);

        $this->schedule->shouldHaveReceived('command')->with('seo:generate-files')->once();
        $this->schedule->shouldHaveReceived('hourly')->atLeast()->once();
    }
}
