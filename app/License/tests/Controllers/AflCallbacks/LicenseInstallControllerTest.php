<?php

namespace App\License\tests\Controllers\AflCallbacks;

use App\License\Controllers\AflCallbacks\LicenseInstallController;
use App\License\Helpers\LicenseValidator;
use App\License\Models\LicenseCallback;
use App\License\Services\InstallationService;
use App\License\tests\LicenseTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class LicenseInstallControllerTest extends LicenseTestCase
{
    #[Test]
    #[Group('license-callbacks')]
    public function license_install_returns_unknown_error_for_invalid_request(): void
    {
        $validator = Mockery::mock(LicenseValidator::class);
        $validator->shouldReceive('resolveIp')->once()->andReturn('127.0.0.1');
        $validator->shouldReceive('isValidLicenseRequest')->once()->andReturn(false);
        $service = Mockery::mock(InstallationService::class);

        $response = (new LicenseInstallController($validator, $service))->licenseInstall($this->moduleRequest([
            'product_id' => 1,
        ], 'POST'));

        $this->assertSame('notification_unknown_error', $response->headers->get('notification_case'));
    }

    #[Test]
    #[Group('license-callbacks')]
    public function license_install_registers_installation_and_logs_callback_when_valid(): void
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

        $service = Mockery::mock(InstallationService::class);
        $service->shouldReceive('register')->once();
        $service->shouldReceive('updateLogs')->once();

        $response = (new LicenseInstallController($validator, $service))->licenseInstall($this->moduleRequest([
            'product_id' => $product->id,
            'root_url' => 'https://example.com/helpdesk',
            'client_email' => 'client@example.com',
            'license_code' => $license->license_code,
            'installation_hash' => 'hash',
            'license_signature' => 'signature',
            'version_number' => '1.0.0',
            'client_id' => $license->user_id,
        ], 'POST'));

        $this->assertSame('notification_license_ok', $response->headers->get('notification_case'));
        $this->assertSame(1, LicenseCallback::where('license_code', $license->license_code)->count());
    }
}
