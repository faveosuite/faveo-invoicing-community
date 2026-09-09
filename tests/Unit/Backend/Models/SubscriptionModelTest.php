<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Models;

use App\Model\Product\Subscription;
use PHPUnit\Framework\TestCase;

class SubscriptionModelTest extends TestCase
{
    public function test_auto_renew_state_inactive_when_not_subscribed(): void
    {
        $subscription = new Subscription(['is_subscribed' => 0]);

        $this->assertSame('inactive', $subscription->autoRenewState());
    }

    public function test_auto_renew_state_enabled_when_opted_in_but_no_gateway_subscription_yet(): void
    {
        $subscription = new Subscription(['is_subscribed' => 1, 'autoRenew_status' => '1']);

        $this->assertSame('enabled', $subscription->autoRenewState());
    }

    public function test_auto_renew_state_pending_when_stripe_subscription_awaits_confirmation(): void
    {
        $subscription = new Subscription(['is_subscribed' => 1, 'autoRenew_status' => '2']);

        $this->assertSame('pending', $subscription->autoRenewState());
    }

    public function test_auto_renew_state_active_when_stripe_confirmed(): void
    {
        $subscription = new Subscription(['is_subscribed' => 1, 'autoRenew_status' => '3']);

        $this->assertSame('active', $subscription->autoRenewState());
    }

    public function test_auto_renew_state_enabled_when_razorpay_opted_in_but_no_subscription_yet(): void
    {
        $subscription = new Subscription(['is_subscribed' => 1]);
        $subscription->rzp_subscription = '1';

        $this->assertSame('enabled', $subscription->autoRenewState());
    }

    public function test_auto_renew_state_pending_when_razorpay_awaiting_authorization(): void
    {
        $subscription = new Subscription(['is_subscribed' => 1]);
        $subscription->rzp_subscription = '2';

        $this->assertSame('pending', $subscription->autoRenewState());
    }

    public function test_auto_renew_state_active_when_razorpay_authorized(): void
    {
        $subscription = new Subscription(['is_subscribed' => 1]);
        $subscription->rzp_subscription = '3';

        $this->assertSame('active', $subscription->autoRenewState());
    }
}
