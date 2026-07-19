<?php

namespace Tests\Unit\Backend\Console\Commands;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Process;
use Tests\DBTestCase;

class SentryReleaseTest extends DBTestCase
{
    use DatabaseTransactions;

    public function test_fails_when_no_auth_token_is_available(): void
    {
        config(['services.sentry.auth_token' => null]);

        $this->artisan('sentry:release')
            ->expectsOutputToContain('Sentry auth token is required')
            ->assertExitCode(1);
    }

    public function test_falls_back_to_the_configured_auth_token_when_no_option_is_passed(): void
    {
        config(['services.sentry.auth_token' => 'configured-token', 'app.version' => '1.2.3']);
        Process::fake();

        $this->artisan('sentry:release')->assertExitCode(0);

        Process::assertRan(fn ($process) => ($process->environment['SENTRY_AUTH_TOKEN'] ?? null) === 'configured-token');
    }

    public function test_fails_when_app_version_is_not_set(): void
    {
        config(['app.version' => null]);

        $this->artisan('sentry:release', ['--auth-token' => 'test-token'])
            ->expectsOutputToContain('app.version is not set')
            ->assertExitCode(1);
    }

    public function test_runs_all_release_steps_on_success(): void
    {
        config(['app.version' => '1.2.3']);
        Process::fake();

        $this->artisan('sentry:release', ['--auth-token' => 'test-token', '--environment' => 'staging'])
            ->assertExitCode(0);

        Process::assertRan(fn ($process) => ($process->command[4] ?? null) === 'new' && in_array('1.2.3', $process->command, true));
        Process::assertRan(fn ($process) => ($process->command[4] ?? null) === 'set-commits');
        Process::assertRan(fn ($process) => ($process->command[4] ?? null) === 'finalize');
        Process::assertRan(fn ($process) => ($process->command[4] ?? null) === 'deploys' && in_array('staging', $process->command, true));
    }

    public function test_stops_and_fails_when_a_step_fails(): void
    {
        config(['app.version' => '1.2.3']);
        Process::fake([
            '*set-commits*' => Process::result(exitCode: 1, errorOutput: 'boom'),
            '*' => Process::result(),
        ]);

        $this->artisan('sentry:release', ['--auth-token' => 'test-token'])
            ->assertExitCode(1);

        Process::assertRan(fn ($process) => ($process->command[4] ?? null) === 'new');
        Process::assertRan(fn ($process) => ($process->command[4] ?? null) === 'set-commits');
        Process::assertNotRan(fn ($process) => ($process->command[4] ?? null) === 'finalize');
        Process::assertNotRan(fn ($process) => ($process->command[4] ?? null) === 'deploys');
    }
}
