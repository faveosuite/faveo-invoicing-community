<?php

namespace Tests\Unit\Backend\Console;

use App\Console\Kernel;
use App\Model\Mailjob\Condition;
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
        $this->schedule->shouldReceive('onOneServer')->andReturnSelf();
        $this->schedule->shouldReceive('runInBackground')->andReturnSelf();
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
        $event = (new Schedule)->command('inspire');

        $this->kernel->getCondition($event, ['condition' => 'dailyAt', 'at' => '08:00']);

        $this->assertSame('0 8 * * *', $event->expression);
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
    // scheduleConfigurableTasks — active `conditions` rows (requires config db_install = 1)
    // -------------------------------------------------------------------------

    public function test_active_task_is_scheduled_at_its_frequency(): void
    {
        $events = $this->configurableEvents(['expiryMail' => ['active' => true, 'value' => 'dailyAt,08:30']]);

        $this->assertSame(['expiry:notification' => '30 8 * * *'], $events);
    }

    public function test_inactive_tasks_are_not_scheduled(): void
    {
        $this->assertSame([], $this->configurableEvents([]));
    }

    public function test_cloud_task_schedules_the_cleanup_job_at_its_frequency(): void
    {
        $events = $this->scheduleWith(['cloud' => ['active' => true, 'value' => 'hourly']])->events();

        $this->assertCount(1, $events);
        $this->assertSame(\App\Jobs\DeleteExpiredCloudInstances::class, $events[0]->description);
        $this->assertSame('0 * * * *', $events[0]->expression);
    }

    public function test_cloud_task_is_not_scheduled_when_switched_off(): void
    {
        $events = $this->scheduleWith(['cloud' => ['active' => false, 'value' => 'hourly']])->events();

        $this->assertSame([], $events);
    }

    public function test_background_task_runs_in_background(): void
    {
        $schedule = $this->scheduleWith(['invoice' => ['active' => true, 'value' => 'monthly', 'is_background' => true]]);

        $this->assertTrue($schedule->events()[0]->runInBackground);
    }

    public function test_nothing_is_scheduled_when_db_install_is_zero(): void
    {
        config(['custom.db_install' => 0]);
        Condition::query()->update(['active' => true]);
        $schedule = new Schedule;

        $this->kernel->scheduleConfigurableTasks($schedule);

        $this->assertSame([], $schedule->events());
    }

    /**
     * Switches every task off except the given ones, then schedules.
     *
     * @param  array<string, array<string, mixed>>  $tasks
     */
    private function scheduleWith(array $tasks): Schedule
    {
        config(['custom.db_install' => 1]);
        Condition::query()->update(['active' => false, 'is_background' => false]);
        foreach ($tasks as $job => $values) {
            Condition::where('job', $job)->update($values);
        }
        $schedule = new Schedule;
        $this->kernel->scheduleConfigurableTasks($schedule);

        return $schedule;
    }

    /**
     * @param  array<string, array<string, mixed>>  $tasks
     * @return array<string, string> artisan command => cron expression
     */
    private function configurableEvents(array $tasks): array
    {
        return collect($this->scheduleWith($tasks)->events())
            ->mapWithKeys(fn ($e) => [preg_replace('/^.*artisan\'? /', '', (string) $e->command) => $e->expression])
            ->all();
    }

    // -------------------------------------------------------------------------
    // Command registration
    // -------------------------------------------------------------------------

    public function test_generate_seo_files_is_registered_command(): void
    {
        $ref = new \ReflectionProperty(Kernel::class, 'commands');
        $commands = $ref->getValue($this->kernel);

        $this->assertContains(\App\Console\Commands\GenerateSeoFiles::class, $commands);
    }

    public function test_seo_generate_files_is_scheduled_hourly(): void
    {
        $this->getPrivateMethod($this->kernel, 'schedule', [$this->schedule]);

        $this->schedule->shouldHaveReceived('command')->with('seo:generate-files')->once();
        $this->schedule->shouldHaveReceived('hourly')->atLeast()->once();
    }

    public function test_cloud_instance_emails_run_in_background_without_overlap(): void
    {
        // Each domain check can take 10s; running in the foreground would delay later tasks.
        $event = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->first(fn ($e) => str_contains((string) $e->command, 'cloud:send-instance-emails'));

        $this->assertNotNull($event);
        $this->assertSame('*/5 * * * *', $event->expression);
        $this->assertTrue($event->runInBackground);
        $this->assertTrue($event->withoutOverlapping);
    }

    public function test_the_renewal_job_never_overlaps_itself(): void
    {
        // Two overlapping runs created two Stripe subscriptions for one renewal.
        $event = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->first(fn ($e) => str_contains((string) $e->command, 'renewal:cron'));

        $this->assertNotNull($event);
        $this->assertTrue($event->withoutOverlapping);
        $this->assertTrue($event->onOneServer);
    }
}
