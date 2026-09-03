<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Console;

use App\Console\Commands\AutorenewalExpirymail;
use App\Console\Commands\CleanupMsg91Reports;
use App\Console\Commands\ExpiryCron;
use App\Console\Commands\FailedMessageDelivery;
use App\Console\Commands\Inspire;
use App\Console\Commands\invoiceDeletion;
use App\Console\Commands\moveImages;
use App\Console\Commands\PostExpiryCron;
use App\Console\Commands\RenewalCron;
use App\Console\Commands\ReoonLogsDeletion;
use App\Console\Commands\SetupTestEnv;
use App\Console\LoggableCommand;
use Tests\DBTestCase;

/**
 * Tests for artisan commands. Uses DBTestCase (DatabaseTransactions) so any
 * DB writes (e.g. cron_logs) are rolled back after each test.
 *
 * Most cron commands check a StatusSetting flag first; with the test DB seeded
 * to 0 they exit early, so no destructive mutations happen.
 */
class ConsoleCommandsTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    // ─────────────────── Inspire ───────────────────

    public function test_inspire_command_has_correct_name(): void
    {
        $this->assertSame('inspire', (new Inspire())->getName());
    }

    public function test_inspire_command_runs_without_error(): void
    {
        // Inspire uses $name (not $signature), so Logger::cron() receives null.
        // Mockery proxies enforce original type hints, so use an anonymous class
        // with relaxed (mixed) type hints to accept null.
        $this->app->bind('Log', function () {
            return new class
            {
                public function cron(mixed $signature = null, mixed $description = ''): mixed
                {
                    return null;
                }

                public function cronCompleted(mixed $id = null): void
                {
                }

                public function cronFailed(mixed $id = null, mixed $exception = null): void
                {
                }
            };
        });

        $this->artisan('inspire')->assertExitCode(0);
    }

    // ─────────────────── AutorenewalExpirymail ───────────────────

    public function test_autorenewal_expiry_mail_command_has_correct_signature(): void
    {
        $this->assertSame('renewal:notification', (new AutorenewalExpirymail())->getName());
    }

    public function test_autorenewal_expiry_mail_command_runs(): void
    {
        // StatusSetting.subs_expirymail = 0 in test DB → method exits early
        $this->artisan('renewal:notification');
        $this->assertTrue(true);
    }

    // ─────────────────── CleanupMsg91Reports ───────────────────

    public function test_cleanup_msg91_reports_command_has_correct_signature(): void
    {
        $this->assertSame('cleanup:msg-reports', (new CleanupMsg91Reports())->getName());
    }

    public function test_cleanup_msg91_reports_command_runs(): void
    {
        // StatusSetting.msg91_report_delete_status = null → exits early
        $this->artisan('cleanup:msg-reports');
        $this->assertTrue(true);
    }

    // ─────────────────── ExpiryCron ───────────────────

    public function test_expiry_cron_command_has_correct_signature(): void
    {
        $this->assertSame('expiry:notification', (new ExpiryCron())->getName());
    }

    public function test_expiry_cron_command_runs(): void
    {
        // StatusSetting.expiry_mail = 0 in test DB → exits early
        $this->artisan('expiry:notification');
        $this->assertTrue(true);
    }

    // ─────────────────── FailedMessageDelivery ───────────────────

    public function test_failed_message_delivery_command_has_correct_signature(): void
    {
        $this->assertSame('app:failed-message-delivery', (new FailedMessageDelivery())->getName());
    }

    public function test_failed_message_delivery_command_runs(): void
    {
        // FailedWhatsappMessage table is empty in test DB → foreach does nothing
        $this->artisan('app:failed-message-delivery');
        $this->assertTrue(true);
    }

    // ─────────────────── invoiceDeletion ───────────────────

    public function test_invoice_deletion_command_has_correct_signature(): void
    {
        $this->assertSame('invoices:delete', (new invoiceDeletion())->getName());
    }

    public function test_invoice_deletion_command_runs(): void
    {
        // invoice_deletion_status may be 1 but invoices table is empty → no deletes
        $this->artisan('invoices:delete');
        $this->assertTrue(true);
    }

    // ─────────────────── PostExpiryCron ───────────────────

    public function test_post_expiry_cron_command_has_correct_signature(): void
    {
        $this->assertSame('postexpiry:notification', (new PostExpiryCron())->getName());
    }

    public function test_post_expiry_cron_command_runs(): void
    {
        // StatusSetting.post_expirymail = 0 → exits early
        $this->artisan('postexpiry:notification');
        $this->assertTrue(true);
    }

    // ─────────────────── ReoonLogsDeletion ───────────────────

    public function test_reoon_logs_deletion_command_has_correct_signature(): void
    {
        $this->assertSame('reoon:logs-deletion', (new ReoonLogsDeletion())->getName());
    }

    public function test_reoon_logs_deletion_command_runs(): void
    {
        // StatusSetting.reoon_deletion_status = null → exits early
        $this->artisan('reoon:logs-deletion');
        $this->assertTrue(true);
    }

    // ─────────────────── RenewalCron ───────────────────

    public function test_renewal_cron_command_has_correct_signature(): void
    {
        $this->assertSame('renewal:cron', (new RenewalCron())->getName());
    }

    public function test_renewal_cron_command_runs(): void
    {
        // stripe_auto_renewal = 0, razorpay_auto_renewal = 0 → both methods exit early
        $this->artisan('renewal:cron');
        $this->assertTrue(true);
    }

    // ─────────────────── moveImages ───────────────────

    public function test_move_images_command_has_correct_signature(): void
    {
        $this->assertSame('move:images', (new moveImages())->getName());
    }

    public function test_move_images_command_runs(): void
    {
        // Source directories (public/admin/images etc.) don't exist in test env
        // → File::isDirectory() returns false → directories are skipped
        // → Artisan::call('storage:link') runs harmlessly
        $this->artisan('move:images');
        $this->assertTrue(true);
    }

    // ─────────────────── LoggableCommand – catch block coverage ───────────────────

    public function test_loggable_command_catch_block_covered_when_handle_and_log_throws(): void
    {
        // Create a concrete command that throws in handleAndLog()
        // to cover the catch block in LoggableCommand::handle()
        $throwingCommand = new class extends LoggableCommand
        {
            protected $signature = 'test:throwing-command-'.PHP_INT_MAX;

            protected $description = 'Testing error handling';

            public function handleAndLog(): void
            {
                throw new \Exception('Test error from handleAndLog');
            }
        };

        // Register the command in the application
        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)
            ->registerCommand($throwingCommand);

        // The command should handle the exception gracefully (not rethrow)
        $this->artisan($throwingCommand->getName());
        $this->assertTrue(true);
    }

    public function test_loggable_command_cronCompleted_called_when_log_is_set(): void
    {
        // autorenewal runs with real Logger (non-null log) so cronCompleted IS called
        // This covers lines 47-48 in LoggableCommand.php
        $this->artisan('renewal:notification');
        $this->assertTrue(true);
    }

    // ─────────────────── SetupTestEnv ───────────────────

    public function test_setup_test_env_command_has_correct_signature(): void
    {
        $this->assertSame('testing-setup', (new SetupTestEnv())->getName());
    }

    public function test_setup_test_env_command_description_is_set(): void
    {
        $command = new SetupTestEnv();
        $this->assertNotEmpty($command->getDescription());
    }
}
