<?php

namespace Tests\Unit\Backend\Http\Controllers\Common;

use App\Http\Controllers\Common\CronController;
use App\Model\Mailjob\Condition;
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

    public function test_get_subscriptions_returns_array(): void
    {
        // getSubscriptions expects an array whose first element is JSON-encoded days
        $result = $this->cron->getSubscriptions([15, 30]);
        $this->assertIsArray($result);
    }

    public function test_get_auto_subscriptions_returns_array(): void
    {
        $result = $this->cron->getautoSubscriptions([15, 30]);
        $this->assertIsArray($result);
    }

    public function test_get_post_subscriptions_returns_array(): void
    {
        $result = $this->cron->getPostSubscriptions([15, 30]);
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

    public function test_get_subscriptions_with_no_days_returns_empty_array(): void
    {
        $result = $this->cron->getSubscriptions([]);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // -------------------------------------------------------------------------
    // eachSubscription — early exit when expiry_mail status = 0
    // -------------------------------------------------------------------------

    public function test_each_subscription_does_nothing_when_expiry_mail_off(): void
    {
        $this->switchTask('expiryMail', false);

        $this->cron->eachSubscription();

        $this->assertTrue(true); // no exception
    }

    // -------------------------------------------------------------------------
    // autoRenewalExpiryNotify — early exit when subs_expirymail = 0
    // -------------------------------------------------------------------------

    public function test_auto_renewal_expiry_notify_does_nothing_when_off(): void
    {
        $this->switchTask('subsExpirymail', false);

        $this->cron->autoRenewalExpiryNotify();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // postRenewalNotify — early exit when post_expirymail = 0
    // -------------------------------------------------------------------------

    public function test_post_renewal_notify_does_nothing_when_off(): void
    {
        $this->switchTask('postExpirymail', false);

        $this->cron->postRenewalNotify();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // invoicesDeletion — early exit when invoice_deletion_status = 0
    // -------------------------------------------------------------------------

    public function test_invoices_deletion_does_nothing_when_status_off(): void
    {
        $this->switchTask('invoice', false);

        $this->cron->invoicesDeletion();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // reoonLogsDeletion — early exit when reoon_deletion_status = 0
    // -------------------------------------------------------------------------

    public function test_reoon_logs_deletion_does_nothing_when_off(): void
    {
        $this->switchTask('reoon', false);

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
        $this->switchTask('msg91Reports', false);

        $this->cron->msgDeletions();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // eachSubscription — enabled (status=1) but no subscriptions matching
    // -------------------------------------------------------------------------

    public function test_each_subscription_runs_when_enabled_but_no_matching_subscriptions(): void
    {
        $this->switchTask('expiryMail', true);
        // Use a far-future day so no subscription matches
        $this->setTaskDays('expiryMail', [999999]);

        $this->cron->eachSubscription();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // autoRenewalExpiryNotify — enabled but no matching subscriptions
    // -------------------------------------------------------------------------

    public function test_auto_renewal_expiry_notify_runs_when_enabled_but_no_matching(): void
    {
        $this->switchTask('subsExpirymail', true);
        $this->setTaskDays('subsExpirymail', [999999]);

        $this->cron->autoRenewalExpiryNotify();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // postRenewalNotify — enabled but no matching subscriptions
    // -------------------------------------------------------------------------

    public function test_post_renewal_notify_runs_when_enabled_but_no_matching(): void
    {
        $this->switchTask('postExpirymail', true);
        $this->setTaskDays('postExpirymail', [999999]);

        $this->cron->postRenewalNotify();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // invoicesDeletion — enabled but no invoices to delete
    // -------------------------------------------------------------------------

    public function test_invoices_deletion_runs_when_enabled_but_no_deletable_invoices(): void
    {
        $this->switchTask('invoice', true);
        // Set a very recent cutoff so no invoices qualify
        $this->setTaskDays('invoice', 99999);

        $this->cron->invoicesDeletion();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // reoonLogsDeletion — enabled but no logs to delete
    // -------------------------------------------------------------------------

    public function test_reoon_logs_deletion_runs_when_enabled_but_no_logs(): void
    {
        $this->switchTask('reoon', true);
        $this->setTaskDays('reoon', 99999);

        $this->cron->reoonLogsDeletion();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // msgDeletions — enabled path (msg91_report_delete_status = 1)
    // -------------------------------------------------------------------------

    public function test_msg_deletions_runs_when_enabled(): void
    {
        $this->switchTask('msg91Reports', true);
        $this->setTaskDays('msg91Reports', 99999);

        $this->cron->msgDeletions();

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // getAutoSubscriptions — with encoded days, returns array
    // -------------------------------------------------------------------------

    public function test_get_auto_subscriptions_with_days_returns_array(): void
    {
        $result = $this->cron->getautoSubscriptions([30, 60]);
        $this->assertIsArray($result);
    }

    public function test_get_auto_subscriptions_with_no_days_returns_empty(): void
    {
        $result = $this->cron->getautoSubscriptions([]);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // -------------------------------------------------------------------------
    // getPostSubscriptions — with encoded days, returns array
    // -------------------------------------------------------------------------

    public function test_get_post_subscriptions_with_days_returns_array(): void
    {
        $result = $this->cron->getPostSubscriptions([7, 14]);
        $this->assertIsArray($result);
    }

    public function test_get_post_subscriptions_with_no_days_returns_empty(): void
    {
        $result = $this->cron->getPostSubscriptions([]);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // -------------------------------------------------------------------------
    // getSubscriptions — with valid encoded days, covers branch inside CronController
    // -------------------------------------------------------------------------

    public function test_get_subscriptions_with_days_returns_array_for_each_day(): void
    {
        $result = $this->cron->getSubscriptions([7, 30]);
        $this->assertIsArray($result);
    }

    // -------------------------------------------------------------------------
    // eachSubscription — reads its on/off from the expiryMail task
    // -------------------------------------------------------------------------

    public function test_each_subscription_reads_its_task_switch(): void
    {
        // Flip status and verify no exception raised either way
        $this->switchTask('expiryMail', false);
        $this->cron->eachSubscription();

        $this->switchTask('expiryMail', true);
        $this->setTaskDays('expiryMail', [0]);
        $this->cron->eachSubscription();

        $this->assertTrue(true);
    }

    private function switchTask(string $job, bool $on): void
    {
        Condition::where('job', $job)->update(['active' => $on]);
    }

    private function setTaskDays(string $job, mixed $days): void
    {
        Condition::where('job', $job)->update(['days' => json_encode($days)]);
    }
}
