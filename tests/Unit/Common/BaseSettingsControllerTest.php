<?php

namespace Tests\Unit\Common;

use Mockery;
use App\Http\Controllers\Common\SettingsController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class BaseSettingsControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->getLoggedInUser('admin');

        $this->withoutMiddleware();
    }

    public function test_get_cron_settings_check_api_structure()
    {
        $response = $this->getJson('job-scheduler');
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'cronPath',
                         'commands',
                         'expiryDays',
                         'selectedDays',
                         'delLogDays',
                         'beforeLogDay',
                         'execEnabled',
                         'paths',
                         'Subs_expiry',
                         'Auto_expiryday',
                         'post_expiry',
                         'post_expiryday',
                         'cloudDays',
                         'beforeCloudDay',
                         'invoiceDays',
                         'invoiceDeletionDay',
                         'msg91Days',
                         'msgDeletionDays',
                         'ReeonLogDeletionDays',
                         'reoonDays',
                         'systemLogsDays',
                         'systemLogsDeletionDays',
                     ],
                 ]);
    }

    public function test_post_scheduler_status_flags()
    {
        $payload = [
            'expiry_cron' => 1,
            'activity' => 1,
            'subs_expirymail' => 1,
            'postsubs_expirymail' => 0,
            'cloud_cron' => 1,
            'invoice_cron' => 0,
            'msg91_cron' => 1,
            'systemlogs_cron' => 1,
            'reoon_cron' => 0,
        ];

        $response = $this->patchJson('/post-scheduler', $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => __('message.updated-successfully')]);

        $this->assertDatabaseHas('status_settings', [
            'id' => 1,
            'expiry_mail' => 1,
            'activity_log_delete' => 1,
            'subs_expirymail' => 1,
        ]);
    }

    public function test_post_scheduler_with_timing()
    {
        // REAL payload (based on your POST data)
        $payload = [
            'expiry_cron' => 1,
            'activity' => 1,
            'subs_expirymail' => 1,
            'postsubs_expirymail' => 1,
            'cloud_cron' => 1,
            'invoice_cron' => 1,
            'msg91_cron' => 1,
            'reoon_cron' => 1,
            'systemlogs_cron' => 1,

            // Commands & dailyAt values are not saved in StatusSetting
            'expiry-commands' => 'dailyAt',
            'expiry-dailyAt' => '12:00',

            'activity-commands' => 'dailyAt',
            'activity-dailyAt' => '06:00',

            'subexpiry-commands' => 'dailyAt',
            'subexpiry-dailyAt' => '14:00',

            'postsubexpiry-commands' => 'dailyAt',
            'postsubexpiry-dailyAt' => '16:20',

            'cloud-commands' => 'dailyAt',
            'cloud-dailyAt' => '11:00',

            'invoice-commands' => 'dailyAt',
            'invoice-dailyAt' => '09:10',

            'msg91-commands' => 'dailyAt',
            'msg91-dailyAt' => '08:25',

            'reoon-commands' => 'everyMinute',
            'systemlogs-commands' => 'everyMinute',
        ];

        $response = $this->patchJson('/post-scheduler', $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => __('message.updated-successfully')]);

        $this->assertDatabaseHas('status_settings', [
            'id' => 1,
            'expiry_mail' => 1,
            'activity_log_delete' => 1,
            'subs_expirymail' => 1,
            'post_expirymail' => 1,
            'cloud_mail_status' => 1,
            'invoice_deletion_status' => 1,
            'msg91_report_delete_status' => 1,
            'reoon_deletion_status' => 1,
            'system_log_status' => 1,
        ]);

        $expectedConditions = [
            'expiryMail' => 'dailyAt,12:00',
            'deleteLogs' => 'dailyAt,06:00',
            'subsExpirymail' => 'dailyAt,14:00',
            'postExpirymail' => 'dailyAt,16:20',
            'cloud' => 'dailyAt,11:00',
            'invoice' => 'dailyAt,09:10',
            'msg91Reports' => 'dailyAt,08:25',
            'reoon' => 'everyMinute',
            'systemLogs' => 'everyMinute',
        ];

        foreach ($expectedConditions as $job => $value) {
            $this->assertDatabaseHas('conditions', [
                'job' => $job,
                'value' => $value,
            ]);
        }
    }

    public function test_save_cron_days_updates_all_tables_correctly()
    {
        $payload = [
            'logdelday' => 180,
            'subexpiryday' => [30, 15, 7, 1],
            'cloud_days' => 15,
            'postsubexpiry_days' => [7, 1],
            'invoice_days' => 1,
            'msg91_days' => 0,
            'reoon_days' => 30,
            'system_logs_days' => 0,
        ];

        $response = $this->patchJson('/cron-days', $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'message' => __('message.updated-successfully'),
                 ]);

        $this->assertDatabaseHas('activity_log_days', [
            'id' => 1,
            'days' => 180,
        ]);
    }

    public function test_it_returns_success_when_exec_is_enabled_and_php_path_is_valid()
    {
        $mock = Mockery::mock(
            SettingsController::class
        )->makePartial();

        $mock->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('execEnabled')->once()->andReturn(true);

        $this->app->instance(
            SettingsController::class,
            $mock
        );

        $response = $this->postJson('/verify-php-path', [
            'path' => PHP_BINARY,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => trans('message.valid-php-path'),
            ]);
    }

    public function test_it_returns_error_when_exec_is_disabledq()
    {
        $mock = Mockery::mock(
            SettingsController::class
        )->makePartial();

        // allow mocking protected methods
        $mock->shouldAllowMockingProtectedMethods();

        // exec is DISABLED
        $mock->shouldReceive('execEnabled')
            ->once()
            ->andReturn(false);

        // bind the mocked controller
        $this->app->instance(
            SettingsController::class,
            $mock
        );

        $response = $this->postJson('/verify-php-path', [
            'path' => PHP_BINARY,
        ]);
        // dd($response->getOriginalContent());

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => trans('message.please_enable_php_exec_for_cronjob_check'),
            ]);
    }

    public function test_it_returns_cron_condition_when_condition_exists()
    {
        //expiryMail
        $response = $this->getJson('/cron/condition/expiryMail');
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => __('message.data_retrieved'),
                'data' => [
                    'condition' => 'dailyAt',
                    'at' => '12:00',
                ],
            ]);
        $this->assertDatabaseHas('conditions', [
            'job' => 'expiryMail',
            'value' => 'dailyAt,12:00',
        ]);
        // delete Logs
        $deleteLogs = $this->getJson('/cron/condition/deleteLogs');
        $deleteLogs->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => __('message.data_retrieved'),
                'data' => [
                    'condition' => 'dailyAt',
                    'at' => '06:00',
                ],
            ]);
        $this->assertDatabaseHas('conditions', [
            'job' => 'deleteLogs',
            'value' => 'dailyAt,06:00',
        ]);
    }
}
