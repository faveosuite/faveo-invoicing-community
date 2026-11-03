<?php

namespace Tests\Unit\Backend\Http\Controllers\Common;

use App\Http\Controllers\Common\PhpMailController;
use App\Model\Common\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class PhpMailControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    private PhpMailController $mailer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
        $this->mailer = new PhpMailController;

        Setting::firstOrCreate(['id' => 1], [
            'email' => 'noreply@test.local',
            'title' => 'Test',
        ]);
    }

    // -------------------------------------------------------------------------
    // setQueue — sets the queue manager driver
    // -------------------------------------------------------------------------

    public function test_set_queue_does_not_throw(): void
    {
        $this->mailer->setQueue();
        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // sendEmail — a failed mail-log write must not abort the caller
    // -------------------------------------------------------------------------

    public function test_send_email_still_queues_the_mail_when_the_log_write_fails(): void
    {
        // Registration once broke here: logging returned null, then ->id on null threw.
        \App\Facades\Log::partialMock()->shouldReceive('logMailByCategory')->once()->andReturnNull();
        \Illuminate\Support\Facades\Bus::fake();

        $this->mailer->sendEmail('noreply@test.local', 'user@test.local', '<p>Hi</p>', 'Subject', 'test', []);

        \Illuminate\Support\Facades\Bus::assertDispatched(\App\Jobs\SendEmail::class);
    }

    // -------------------------------------------------------------------------
    // payment_log — logs payment info
    // -------------------------------------------------------------------------

    public function test_payment_log_does_not_throw(): void
    {
        $this->mailer->payment_log(
            'noreply@test.local',
            'stripe',
            'success',
            order: null,
            exception: null,
            amount: 100.0,
            payment_type: 'invoice'
        );

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // setMailConfig — covers switch branches
    // -------------------------------------------------------------------------

    public function test_set_mail_config_with_smtp_driver(): void
    {
        $setting = (object) [
            'driver' => 'smtp',
            'host' => 'smtp.test.local',
            'port' => 587,
            'encryption' => 'tls',
            'email' => 'noreply@test.local',
            'password' => 'secret',
        ];

        $result = $this->mailer->setMailConfig($setting);

        // Returns a mailer object or 'invalid mail configuration'
        $this->assertNotNull($result);
    }

    public function test_set_mail_config_with_unknown_driver_returns_false(): void
    {
        $setting = (object) [
            'driver' => 'unknown_driver_xyz',
        ];

        $result = $this->mailer->setMailConfig($setting);

        $this->assertFalse((bool) $result);
    }
}
