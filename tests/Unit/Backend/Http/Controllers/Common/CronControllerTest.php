<?php

namespace Tests\Unit\Backend\Http\Controllers\Common;

use App\Http\Controllers\Common\CronController;
use App\User;
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
}
