<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class SentryRelease extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sentry:release {--environment=production} {--auth-token=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Sentry release for the current app.version, associate commits, finalize it, and mark a deploy';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $token = $this->option('auth-token') ?: env('SENTRY_AUTH_TOKEN');

        if (! $token) {
            $this->error('Sentry auth token is required: pass --auth-token= or set SENTRY_AUTH_TOKEN env var (Sentry -> Settings -> Auth Tokens).');

            return self::FAILURE;
        }

        $version = config('app.version');

        if (! $version) {
            $this->error('app.version is not set in config/app.php.');

            return self::FAILURE;
        }

        $env = [
            'SENTRY_AUTH_TOKEN' => $token,
            'SENTRY_ORG' => env('SENTRY_ORG', 'ladybird-web-solution-pvt-ltd'),
            'SENTRY_PROJECT' => env('SENTRY_PROJECT', 'faveo-invoicing'),
        ];

        $environment = $this->option('environment');

        $this->info("Creating Sentry release {$version} for {$env['SENTRY_ORG']}/{$env['SENTRY_PROJECT']}...");

        $steps = [
            ['npx', '--yes', '@sentry/cli', 'releases', 'new', $version],
            ['npx', '--yes', '@sentry/cli', 'releases', 'set-commits', $version, '--auto'],
            ['npx', '--yes', '@sentry/cli', 'releases', 'finalize', $version],
            ['npx', '--yes', '@sentry/cli', 'releases', 'deploys', $version, 'new', '-e', $environment],
        ];

        foreach ($steps as $command) {
            $result = Process::forever()
                ->env($env)
                ->run($command, function (string $type, string $output): void {
                    $this->output->write($output);
                });

            if ($result->failed()) {
                $this->error('Failed: '.implode(' ', $command));

                return self::FAILURE;
            }
        }

        $this->info("Done: release {$version} created, commits associated, deploy marked as {$environment}.");

        return self::SUCCESS;
    }
}
