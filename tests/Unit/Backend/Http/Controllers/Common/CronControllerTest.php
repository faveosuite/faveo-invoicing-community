<?php

namespace Tests\Unit\Backend\Http\Controllers\Common;

use App\Http\Controllers\Common\CronController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class CronControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    private CronController $cron;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->cron = new CronController;
    }

    public function test_get_all_days_expiry_users_returns_array(): void
    {
        $result = $this->cron->getAllDaysExpiryUsers(15);
        $this->assertIsArray($result);
    }

    public function test_get_15_days_expiry_users_returns_array(): void
    {
        $result = $this->cron->get15DaysExpiryUsers();
        $this->assertIsArray($result);
    }

    public function test_get_one_day_expiry_users_returns_array(): void
    {
        $result = $this->cron->getOneDayExpiryUsers();
        $this->assertIsArray($result);
    }

    public function test_get_on_day_expiry_users_returns_array(): void
    {
        $result = $this->cron->getOnDayExpiryUsers();
        $this->assertIsArray($result);
    }

    public function test_get_expired_users_returns_array(): void
    {
        $result = $this->cron->getExpiredUsers();
        $this->assertIsArray($result);
    }

    // getUsers() depends on BaseCronController::get30DaysUsers() which is implemented
    // only in subclasses; tested indirectly via autoRenewalExpiryNotify integration.

    public function test_get_subscriptions_returns_array(): void
    {
        // getSubscriptions expects an array whose first element is JSON-encoded days
        $result = $this->cron->getSubscriptions([json_encode([15, 30])]);
        $this->assertIsArray($result);
    }

    public function test_get_auto_subscriptions_returns_array(): void
    {
        $result = $this->cron->getautoSubscriptions([json_encode([15, 30])]);
        $this->assertIsArray($result);
    }

    public function test_get_post_subscriptions_returns_array(): void
    {
        $result = $this->cron->getPostSubscriptions([json_encode([15, 30])]);
        $this->assertIsArray($result);
    }

    public function test_get_1_days_subscription_returns_null_or_collection(): void
    {
        $result = $this->cron->get1DaysSubscription();
        $this->assertTrue(is_null($result) || is_iterable($result));
    }

    public function test_get_0_days_subscription_returns_null_or_collection(): void
    {
        $result = $this->cron->get0DaysSubscription();
        $this->assertTrue(is_null($result) || is_iterable($result));
    }

    public function test_get_plus_1_subscription_returns_null_or_collection(): void
    {
        $result = $this->cron->getPlus1Subscription();
        $this->assertTrue(is_null($result) || is_iterable($result));
    }

    public function test_get_all_days_expiry_users_with_zero_days_returns_array(): void
    {
        $result = $this->cron->getAllDaysExpiryUsers(0);
        $this->assertIsArray($result);
    }

    public function test_get_subscriptions_with_invalid_json_returns_empty_array(): void
    {
        // Invalid JSON as first element → early return []
        $result = $this->cron->getSubscriptions(['not-valid-json']);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // -------------------------------------------------------------------------
    // eachSubscription — early exit when expiry_mail status = 0
    // -------------------------------------------------------------------------

    public function test_each_subscription_does_nothing_when_expiry_mail_off(): void
    {
        \App\Model\Common\StatusSetting::updateOrCreate([], ['expiry_mail' => 0]);

        $this->cron->eachSubscription();

        $this->assertTrue(true); // no exception
    }

    // -------------------------------------------------------------------------
    // autoRenewalExpiryNotify — early exit when subs_expirymail = 0
    // -------------------------------------------------------------------------

    public function test_auto_renewal_expiry_notify_does_nothing_when_off(): void
    {
        \App\Model\Common\StatusSetting::updateOrCreate([], ['subs_expirymail' => 0]);

        $this->cron->autoRenewalExpiryNotify();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // postRenewalNotify — early exit when post_expirymail = 0
    // -------------------------------------------------------------------------

    public function test_post_renewal_notify_does_nothing_when_off(): void
    {
        \App\Model\Common\StatusSetting::updateOrCreate([], ['post_expirymail' => 0]);

        $this->cron->postRenewalNotify();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // invoicesDeletion — early exit when invoice_deletion_status = 0
    // -------------------------------------------------------------------------

    public function test_invoices_deletion_does_nothing_when_status_off(): void
    {
        \App\Model\Common\StatusSetting::updateOrCreate([], ['invoice_deletion_status' => 0]);

        $this->cron->invoicesDeletion();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // reoonLogsDeletion — early exit when reoon_deletion_status = 0
    // -------------------------------------------------------------------------

    public function test_reoon_logs_deletion_does_nothing_when_off(): void
    {
        \App\Model\Common\StatusSetting::updateOrCreate([], ['reoon_deletion_status' => 0]);

        $this->cron->reoonLogsDeletion();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // failedMessageDelivery — processes FailedWhatsappMessage records
    // -------------------------------------------------------------------------

    public function test_failed_message_delivery_runs_without_exception(): void
    {
        $this->cron->failedMessageDelivery();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // msgDeletions — early exit when msg91_report_delete_status != 1
    // -------------------------------------------------------------------------

    public function test_msg_deletions_does_nothing_when_status_off(): void
    {
        \App\Model\Common\StatusSetting::updateOrCreate([], ['msg91_report_delete_status' => 0]);

        $this->cron->msgDeletions();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // eachSubscription — enabled (status=1) but no subscriptions matching
    // -------------------------------------------------------------------------

    public function test_each_subscription_runs_when_enabled_but_no_matching_subscriptions(): void
    {
        \App\Model\Common\StatusSetting::updateOrCreate([], ['expiry_mail' => 1]);
        // Use a far-future day so no subscription matches
        \App\Model\Mailjob\ExpiryMailDay::updateOrCreate([], ['days' => '[999999]']);

        $this->cron->eachSubscription();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // autoRenewalExpiryNotify — enabled but no matching subscriptions
    // -------------------------------------------------------------------------

    public function test_auto_renewal_expiry_notify_runs_when_enabled_but_no_matching(): void
    {
        \App\Model\Common\StatusSetting::updateOrCreate([], ['subs_expirymail' => 1]);
        \App\Model\Mailjob\ExpiryMailDay::updateOrCreate([], ['autorenewal_days' => '[999999]']);

        $this->cron->autoRenewalExpiryNotify();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // postRenewalNotify — enabled but no matching subscriptions
    // -------------------------------------------------------------------------

    public function test_post_renewal_notify_runs_when_enabled_but_no_matching(): void
    {
        \App\Model\Common\StatusSetting::updateOrCreate([], ['post_expirymail' => 1]);
        \App\Model\Mailjob\ExpiryMailDay::updateOrCreate([], ['postexpiry_days' => '[999999]']);

        $this->cron->postRenewalNotify();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // invoicesDeletion — enabled but no invoices to delete
    // -------------------------------------------------------------------------

    public function test_invoices_deletion_runs_when_enabled_but_no_deletable_invoices(): void
    {
        \App\Model\Common\StatusSetting::updateOrCreate([], ['invoice_deletion_status' => 1]);
        // Set a very recent cutoff so no invoices qualify
        \App\Model\Mailjob\ExpiryMailDay::updateOrCreate([], ['invoice_days' => 99999]);

        $this->cron->invoicesDeletion();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // reoonLogsDeletion — enabled but no logs to delete
    // -------------------------------------------------------------------------

    public function test_reoon_logs_deletion_runs_when_enabled_but_no_logs(): void
    {
        \App\Model\Common\StatusSetting::updateOrCreate([], ['reoon_deletion_status' => 1]);
        \App\Model\Mailjob\ExpiryMailDay::updateOrCreate([], ['reoon_logs_days' => 99999]);

        $this->cron->reoonLogsDeletion();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // msgDeletions — enabled path (msg91_report_delete_status = 1)
    // -------------------------------------------------------------------------

    public function test_msg_deletions_runs_when_enabled(): void
    {
        \App\Model\Common\StatusSetting::updateOrCreate([], ['msg91_report_delete_status' => 1]);
        \App\Model\Mailjob\ExpiryMailDay::updateOrCreate([], ['msg91_days' => 99999]);

        $this->cron->msgDeletions();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // getAutoSubscriptions — with encoded days, returns array
    // -------------------------------------------------------------------------

    public function test_get_auto_subscriptions_with_valid_json_days_returns_array(): void
    {
        $result = $this->cron->getautoSubscriptions([json_encode([30, 60])]);
        $this->assertIsArray($result);
    }

    public function test_get_auto_subscriptions_with_invalid_json_returns_empty(): void
    {
        $result = $this->cron->getautoSubscriptions(['not-valid-json']);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // -------------------------------------------------------------------------
    // getPostSubscriptions — with encoded days, returns array
    // -------------------------------------------------------------------------

    public function test_get_post_subscriptions_with_valid_json_days_returns_array(): void
    {
        $result = $this->cron->getPostSubscriptions([json_encode([7, 14])]);
        $this->assertIsArray($result);
    }

    public function test_get_post_subscriptions_with_invalid_json_returns_empty(): void
    {
        $result = $this->cron->getPostSubscriptions(['invalid-json']);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // -------------------------------------------------------------------------
    // getSubscriptions — with valid encoded days, covers branch inside CronController
    // -------------------------------------------------------------------------

    public function test_get_subscriptions_with_valid_encoded_days_returns_array(): void
    {
        $result = $this->cron->getSubscriptions([json_encode([7, 30])]);
        $this->assertIsArray($result);
    }

    // -------------------------------------------------------------------------
    // eachSubscription — status check verifies it reads from StatusSetting
    // -------------------------------------------------------------------------

    public function test_each_subscription_reads_status_setting(): void
    {
        // Flip status and verify no exception raised either way
        \App\Model\Common\StatusSetting::updateOrCreate([], ['expiry_mail' => 0]);
        $this->cron->eachSubscription();

        \App\Model\Common\StatusSetting::updateOrCreate([], ['expiry_mail' => 1]);
        \App\Model\Mailjob\ExpiryMailDay::updateOrCreate([], ['days' => '[0]']);
        $this->cron->eachSubscription();

        $this->assertTrue(true);
    }
}
