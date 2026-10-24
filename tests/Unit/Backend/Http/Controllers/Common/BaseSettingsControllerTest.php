<?php

namespace Tests\Unit\Backend\Http\Controllers\Common;

use App\Http\Controllers\Common\SettingsController;
use App\Model\Mailjob\Condition;
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
                    'tasks' => ['expiryMail' => ['active', 'condition', 'at', 'days']],
                ],
            ]);
    }

    public function test_post_scheduler_saves_each_tasks_switch_and_frequency(): void
    {
        $payload = ['tasks' => [
            'expiryMail' => ['active' => true, 'value' => 'dailyAt,12:00'],
            'deleteLogs' => ['active' => true, 'value' => 'daily'],
            'invoice' => ['active' => false, 'value' => 'monthly'],
            'reoon' => ['active' => true, 'value' => 'everyMinute'],
        ]];

        $response = $this->patchJson('/settings/cron-data', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.updated-successfully')]);
        foreach ($payload['tasks'] as $job => $task) {
            $this->assertDatabaseHas('conditions', ['job' => $job, 'active' => $task['active'], 'value' => $task['value']]);
        }
    }

    public function test_post_scheduler_keeps_command_and_days(): void
    {
        $this->patchJson('/settings/cron-data', ['tasks' => ['expiryMail' => ['active' => true, 'value' => 'daily']]])
            ->assertStatus(200);

        $this->assertSame('expiry:notification', Condition::where('job', 'expiryMail')->value('command'));
    }

    public function test_save_cron_days_stores_lists_numbers_and_empty(): void
    {
        $payload = ['days' => [
            'subsExpirymail' => [30, 15, 7, 1],
            'deleteLogs' => 180,
            'msg91Reports' => 0,
            'reoon' => '',
        ]];

        $response = $this->patchJson('/settings/cron-days', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.updated-successfully')]);
        $this->assertSame([30, 15, 7, 1], Condition::dayList('subsExpirymail'));
        $this->assertSame(180, Condition::dayCount('deleteLogs'));
        $this->assertSame(0, Condition::dayCount('msg91Reports'));
        $this->assertNull(Condition::dayCount('reoon'));
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

    /** @return list<string> */
    private function formatProperties(array $properties, mixed $event): array
    {
        return (new \ReflectionMethod(SettingsController::class, 'formatProperties'))
            ->invoke(app(SettingsController::class), $properties, $event);
    }

    public function test_format_properties_describes_each_updated_field(): void
    {
        $lines = $this->formatProperties([
            'old' => ['status' => 'Inactive', 'name' => '', 'tags' => ['a'], 'note' => '<b>x</b>', 'gone' => 'y'],
            'attributes' => ['status' => 'Active', 'name' => 'New', 'tags' => ['a', 'b'], 'note' => '"q" & <i>z</i>'],
        ], 'updated');

        $this->assertSame([
            'Updated Status From Inactive To Active',
            'Updated Name From null To New',
            'Updated Tags From [&quot;a&quot;] To [&quot;a&quot;,&quot;b&quot;]',
            'Updated Note From x To &quot;q&quot; &amp; z',
            'Updated Gone From y To null',
        ], $lines);
    }

    public function test_format_properties_describes_created_fields_skipping_empty_and_placeholder_values(): void
    {
        $lines = $this->formatProperties(['attributes' => ['name' => 'Acme', 'blank' => '', 'zero' => 0, 'dash' => '--', 'list' => [1]]], 'created');

        $this->assertSame(['Set Name To Acme', 'Set List To [1]'], $lines);
    }

    public function test_format_properties_is_empty_for_other_events_or_no_data(): void
    {
        $this->assertSame([], $this->formatProperties(['old' => ['a' => 1], 'attributes' => ['a' => 2]], 'deleted'));
        $this->assertSame([], $this->formatProperties([], 'updated'));
        $this->assertSame([], $this->formatProperties([], 'created'));
    }
}
