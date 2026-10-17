<?php

namespace Tests\Unit\Backend\Http\Controllers\Common;

use App\Http\Controllers\Common\SettingsController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\DBTestCase;

class BaseSettingsControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getLoggedInUser('admin');

        $this->withoutMiddleware();
    }

    public function test_get_cron_settings_check_api_structure(): void
    {
        $response = $this->getJson('settings/cron-data');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'cron_path',
                    'exec_enabled',
                    'statuses',
                    'conditions',
                    'days',
                ],
            ]);
    }

    public function test_post_scheduler_status_flags(): void
    {
        $payload = [
            'statuses' => [
                'expiry_cron' => 1,
                'activity' => 1,
                'subs_expirymail' => 1,
                'postsubs_expirymail' => 0,
                'cloud_cron' => 1,
                'invoice_cron' => 0,
                'msg91_cron' => 1,
                'systemlogs_cron' => 1,
                'reoon_cron' => 0,
            ],
            'conditions' => [],
        ];

        $response = $this->patchJson('/settings/cron-data', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.updated-successfully')]);

        $this->assertDatabaseHas('status_settings', [
            'id' => 1,
            'expiry_mail' => 1,
            'activity_log_delete' => 1,
            'subs_expirymail' => 1,
        ]);
    }

    public function test_post_scheduler_with_timing(): void
    {
        $payload = [
            'statuses' => [
                'expiry_cron' => 1,
                'activity' => 1,
                'subs_expirymail' => 1,
                'postsubs_expirymail' => 1,
                'cloud_cron' => 1,
                'invoice_cron' => 1,
                'msg91_cron' => 1,
                'reoon_cron' => 1,
                'systemlogs_cron' => 1,
            ],
            'conditions' => [
                'expiryMail' => 'dailyAt,12:00',
                'deleteLogs' => 'dailyAt,06:00',
                'subsExpirymail' => 'dailyAt,14:00',
                'postExpirymail' => 'dailyAt,16:20',
                'cloud' => 'dailyAt,11:00',
                'invoice' => 'dailyAt,09:10',
                'msg91Reports' => 'dailyAt,08:25',
                'reoon' => 'everyMinute',
                'systemLogs' => 'everyMinute',
            ],
        ];

        $response = $this->patchJson('/settings/cron-data', $payload);

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
            'reoon_deletion_status' => 1,
            'system_log_status' => 1,
        ]);

        foreach ($payload['conditions'] as $job => $value) {
            $this->assertDatabaseHas('conditions', [
                'job' => $job,
                'value' => $value,
            ]);
        }
    }

    public function test_save_cron_days_updates_all_tables_correctly(): void
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

        $response = $this->patchJson('/settings/cron-days', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('message.updated-successfully'),
            ]);

        $this->assertDatabaseHas('activity_log_days', [
            'id' => 1,
            'days' => 180,
        ]);
    }

    public function test_it_returns_success_when_exec_is_enabled_and_php_path_is_valid(): void
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

    public function test_it_returns_error_when_exec_is_disabledq(): void
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
}
