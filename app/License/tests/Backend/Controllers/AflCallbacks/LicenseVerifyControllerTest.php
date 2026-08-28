<?php

namespace App\License\tests\Backend\Controllers\AflCallbacks;

use App\License\Controllers\AflCallbacks\LicenseVerifyController;
use App\License\Helpers\LicenseValidator;
use App\License\Services\BannedHostService;
use App\License\Services\InstallationService;
use App\License\tests\Backend\LicenseTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class LicenseVerifyControllerTest extends LicenseTestCase
{
    #[Test]
    #[Group('license-callbacks')]
    public function license_verify_returns_installation_not_found_when_valid_license_has_no_installation(): void
    {
        $product = $this->createProduct();
        $license = $this->createLicense(['product_id' => $product->id]);
        $validator = Mockery::mock(LicenseValidator::class);
        $validator->shouldReceive('resolveIp')->once()->andReturn('127.0.0.1');
        $validator->shouldReceive('isValidLicenseRequest')->once()->andReturn(true);
        $validator->shouldReceive('validateInstallationHash')->once()->andReturn(true);
        $validator->shouldReceive('verifyScriptSignature')->once()->andReturn(true);
        $validator->shouldReceive('isBanned')->once()->andReturn(false);
        $validator->shouldReceive('validateProduct')->once()->andReturn($product);
        $validator->shouldReceive('findLicense')->once()->andReturn($license);
        $validator->shouldReceive('validateLicense')->once()->andReturn(['valid' => true, 'license' => $license]);
        $validator->shouldReceive('validateIntegerValue')->zeroOrMoreTimes()->andReturn(true);
        $service = Mockery::mock(InstallationService::class);

        $response = new LicenseVerifyController($validator, $service, new BannedHostService)->licenseVerify($this->moduleRequest([
            'product_id' => $product->id,
            'root_url' => 'https://example.com/helpdesk',
            'client_email' => 'client@example.com',
            'license_code' => $license->license_code,
            'installation_hash' => 'hash',
            'license_signature' => 'signature',
            'client_id' => $license->user_id,
        ], 'POST'));

        $this->assertSame('notification_installation_not_found', $response->headers->get('notification_case'));
    }
}
