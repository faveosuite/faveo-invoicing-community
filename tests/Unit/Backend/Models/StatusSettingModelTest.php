<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Models;

use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class StatusSettingModelTest extends DBTestCase
{
    use DatabaseTransactions;

    public function test_auto_renewal_enabled_for_stripe_requires_both_global_and_gateway_toggle(): void
    {
        Setting::where('id', 1)->update(['autorenewal_status' => 1]);
        StatusSetting::where('id', 1)->update(['stripe_auto_renewal' => 1]);

        $this->assertTrue(StatusSetting::autoRenewalEnabledFor('stripe'));
    }

    public function test_auto_renewal_enabled_for_stripe_false_when_global_toggle_off(): void
    {
        Setting::where('id', 1)->update(['autorenewal_status' => 0]);
        StatusSetting::where('id', 1)->update(['stripe_auto_renewal' => 1]);

        $this->assertFalse(StatusSetting::autoRenewalEnabledFor('stripe'));
    }

    public function test_auto_renewal_enabled_for_razorpay_false_when_gateway_toggle_off(): void
    {
        Setting::where('id', 1)->update(['autorenewal_status' => 1]);
        StatusSetting::where('id', 1)->update(['razorpay_auto_renewal' => 0]);

        $this->assertFalse(StatusSetting::autoRenewalEnabledFor('razorpay'));
    }

    public function test_auto_renewal_enabled_for_unknown_gateway_is_false(): void
    {
        Setting::where('id', 1)->update(['autorenewal_status' => 1]);

        $this->assertFalse(StatusSetting::autoRenewalEnabledFor('paypal'));
    }
}
