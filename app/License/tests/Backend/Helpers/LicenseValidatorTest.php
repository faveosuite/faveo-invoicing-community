<?php

namespace App\License\tests\Backend\Helpers;

use App\License\Helpers\LicenseValidator;
use App\License\tests\Backend\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class LicenseValidatorTest extends LicenseTestCase
{
    #[Test]
    #[Group('license-callbacks')]
    public function validate_license_rejects_ip_based_root_url_when_require_domain_is_enabled(): void
    {
        $license = $this->createLicense([
            'license_ip' => null,
            'license_domain' => null,
            'license_require_domain' => 1,
        ]);

        $result = new LicenseValidator()->validateLicense($license, $license->product_id, 'client@example.com', '127.0.0.1', 'http://127.0.0.1/app');

        $this->assertFalse($result['valid']);
        $this->assertSame('domain_required', $result['error']);
    }

    #[Test]
    #[Group('license-callbacks')]
    public function validate_license_allows_ip_based_root_url_when_require_domain_is_disabled(): void
    {
        $license = $this->createLicense([
            'license_ip' => null,
            'license_domain' => null,
            'license_require_domain' => 0,
        ]);

        $result = new LicenseValidator()->validateLicense($license, $license->product_id, 'client@example.com', '127.0.0.1', 'http://127.0.0.1/app');

        $this->assertTrue($result['valid']);
    }
}
