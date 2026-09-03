<?php

namespace App\License\tests\Backend\Services;

use App\License\Models\LicenseSecuritySetting;
use App\License\Services\BannedHostService;
use App\License\tests\Backend\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class BannedHostServiceTest extends LicenseTestCase
{
    #[Test]
    #[Group('license-admin')]
    public function record_failed_licensing_is_a_no_op_when_disabled_by_default(): void
    {
        $service = new BannedHostService;

        $service->recordFailedLicensing('203.0.113.7');

        $this->assertDatabaseMissing('license_failed_licensings', ['failed_licensing_ip' => '203.0.113.7']);
    }

    #[Test]
    #[Group('license-admin')]
    public function record_failed_licensing_increments_attempts_without_banning_below_threshold(): void
    {
        LicenseSecuritySetting::findOrFail(1)->update(['auto_ban_enabled' => true, 'failed_licensings_limit' => 5]);
        $service = new BannedHostService;

        $service->recordFailedLicensing('203.0.113.5');
        $service->recordFailedLicensing('203.0.113.5');

        $this->assertDatabaseHas('license_failed_licensings', [
            'failed_licensing_ip' => '203.0.113.5',
            'failed_licensing_attempts' => 2,
        ]);
        $this->assertDatabaseMissing('license_banned_hosts', ['banned_host_ip' => '203.0.113.5']);
    }

    #[Test]
    #[Group('license-admin')]
    public function record_failed_licensing_auto_bans_once_limit_is_reached(): void
    {
        LicenseSecuritySetting::findOrFail(1)->update(['auto_ban_enabled' => true, 'failed_licensings_limit' => 3]);
        $service = new BannedHostService;

        for ($i = 0; $i < 3; $i++) {
            $service->recordFailedLicensing('203.0.113.6');
        }

        $this->assertDatabaseHas('license_banned_hosts', ['banned_host_ip' => '203.0.113.6']);
    }

    #[Test]
    #[Group('license-admin')]
    public function record_failed_licensing_is_a_no_op_when_limit_set_but_toggle_off(): void
    {
        LicenseSecuritySetting::findOrFail(1)->update(['auto_ban_enabled' => false, 'failed_licensings_limit' => 5]);
        $service = new BannedHostService;

        $service->recordFailedLicensing('203.0.113.8');

        $this->assertDatabaseMissing('license_failed_licensings', ['failed_licensing_ip' => '203.0.113.8']);
    }

    #[Test]
    #[Group('license-admin')]
    public function record_failed_licensing_ignores_invalid_ip(): void
    {
        LicenseSecuritySetting::findOrFail(1)->update(['auto_ban_enabled' => true, 'failed_licensings_limit' => 5]);
        $service = new BannedHostService;

        $service->recordFailedLicensing('not-an-ip');

        $this->assertDatabaseMissing('license_failed_licensings', ['failed_licensing_ip' => 'not-an-ip']);
    }
}
