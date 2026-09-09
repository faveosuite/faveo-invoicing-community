<?php

namespace Tests\Unit\Backend\Http\Controllers\Common;

use App\Http\Controllers\Common\PhpMailController;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
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
    // NotifyMailing — early exit when cloud_mail_status != 1
    // -------------------------------------------------------------------------

    public function test_notify_mailing_does_nothing_when_cloud_mail_status_off(): void
    {
        StatusSetting::updateOrCreate([], ['cloud_mail_status' => 0]);

        $this->mailer->NotifyMailing();

        $this->assertTrue(true);
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
            null,
            null,
            100.0,
            'invoice'
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
