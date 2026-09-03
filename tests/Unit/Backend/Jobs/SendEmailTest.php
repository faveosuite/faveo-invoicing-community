<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Jobs;

use App\Http\Controllers\Common\PhpMailController;
use App\Jobs\SendEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SendEmailTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeJob(mixed $logIdentifier = 0): SendEmail
    {
        return new SendEmail(
            from: 'from@example.com',
            to: 'to@example.com',
            template_data: 'Hello, World!', // string to match mailing(string $data)
            template_name: 'welcome',
            replace: [],
            type: 'transactional',
            bcc: [],
            fromname: 'System',
            toname: 'User',
            cc: [],
            attach: [],
            logIdentifier: $logIdentifier,
        );
    }

    /** @return PhpMailController&MockInterface */
    private function mockMailer(): MockInterface
    {
        /** @var PhpMailController&MockInterface $mock */
        $mock = Mockery::mock(PhpMailController::class);

        return $mock;
    }

    // --- Contract ---

    public function test_implements_should_queue(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, $this->makeJob());
    }

    public function test_max_tries_is_five(): void
    {
        $this->assertSame(5, $this->makeJob()->tries);
    }

    // --- handle(): logIdentifier=0 → no matching log → calls mailing ---

    public function test_handle_calls_mailing_when_no_matching_log_record(): void
    {
        // logIdentifier=0 — MailLog::whereId(0)->value('status') returns null, not 'sent' → mailing runs
        $mailer = $this->mockMailer();
        $mailer->shouldReceive('mailing')->once()->withAnyArgs()->andReturn('');

        $this->makeJob(0)->handle($mailer);
        // Mockery verifies ->once() in tearDown; addToAssertionCount satisfies PHPUnit
        $this->addToAssertionCount(1);
    }

    // --- handle(): idempotency — 'sent' log skips mailing ---

    public function test_handle_skips_mailing_when_mail_log_already_sent(): void
    {
        $logId = (int) \DB::table('mail_logs')->insertGetId([
            'status' => 'sent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mailer = $this->mockMailer();
        $mailer->shouldReceive('mailing')->never();

        $this->makeJob($logId)->handle($mailer);
        $this->assertTrue(true); // reached here → mailing was correctly skipped
    }

    // --- handle(): pending log → calls mailing ---

    public function test_handle_calls_mailing_when_log_status_is_pending(): void
    {
        $logId = (int) \DB::table('mail_logs')->insertGetId([
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mailer = $this->mockMailer();
        $mailer->shouldReceive('mailing')->once()->withAnyArgs()->andReturn('');

        $this->makeJob($logId)->handle($mailer);
        $this->addToAssertionCount(1); // Mockery verifies ->once() in tearDown
    }

    // --- handle(): idempotency across two calls ---

    public function test_handle_called_twice_only_sends_once_when_first_call_marks_sent(): void
    {
        $logId = (int) \DB::table('mail_logs')->insertGetId([
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $callCount = 0;
        $mailer = $this->mockMailer();
        $mailer->shouldReceive('mailing')
            ->withAnyArgs()
            ->andReturnUsing(function () use ($logId, &$callCount): string {
                $callCount++;
                \DB::table('mail_logs')->where('id', $logId)->update(['status' => 'sent']);

                return '';
            });

        $job = $this->makeJob($logId);
        $job->handle($mailer);  // first call — sends, marks 'sent'
        $job->handle($mailer);  // second call — log is 'sent' → skipped

        $this->assertSame(1, $callCount, 'mailing() must be called exactly once');
    }

    // --- Recipients and subject routing ---

    public function test_handle_passes_correct_recipient_to_mailer(): void
    {
        $mailer = $this->mockMailer();
        $mailer->shouldReceive('mailing')
            ->once()
            ->withArgs(function (string $from, string $to): bool {
                return $from === 'sender@example.com' && $to === 'recipient@example.com';
            })
            ->andReturn('');

        (new SendEmail(
            from: 'sender@example.com',
            to: 'recipient@example.com',
            template_data: 'body',
            template_name: 'template',
            logIdentifier: 0,
        ))->handle($mailer);
        $this->addToAssertionCount(1); // Mockery verifies ->once() + withArgs in tearDown
    }
}
