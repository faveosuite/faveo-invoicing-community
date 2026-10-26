<?php

namespace App\License\tests\Backend\Controllers\AflCallbacks;

use App\License\Controllers\AflCallbacks\LicenseInstallController;
use App\License\Helpers\LicenseValidator;
use App\License\Models\LicenseCallback;
use App\License\Models\LicenseNotification;
use App\License\Services\BannedHostService;
use App\License\Services\InstallationService;
use App\License\tests\Backend\LicenseTestCase;
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

        $response = new LicenseInstallController($validator, $service, new BannedHostService)->licenseInstall($this->moduleRequest([
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

        $response = new LicenseInstallController($validator, $service, new BannedHostService)->licenseInstall($this->moduleRequest([
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

    #[Test]
    #[Group('license-callbacks')]
    public function license_install_replaces_product_and_license_shortcodes_in_notification_text(): void
    {
        $product = $this->createProduct(['name' => 'Faveo Helpdesk']);
        // notificationResponse() reads LicenseNotification::first(), so update the existing
        // row (seeded by LicenseModuleSeeder) rather than creating a second, ignored one.
        (LicenseNotification::first() ?? LicenseNotification::create())->update([
            'notification_license_not_found' => 'License %LICENSE_CODE% not found for %PRODUCT_TITLE%',
        ]);

        $validator = Mockery::mock(LicenseValidator::class);
        $validator->shouldReceive('resolveIp')->once()->andReturn('127.0.0.1');
        $validator->shouldReceive('isValidLicenseRequest')->once()->andReturn(true);
        $validator->shouldReceive('validateInstallationHash')->once()->andReturn(true);
        $validator->shouldReceive('verifyScriptSignature')->once()->andReturn(true);
        $validator->shouldReceive('isBanned')->once()->andReturn(false);
        $validator->shouldReceive('validateProduct')->once()->andReturn($product);
        $validator->shouldReceive('findLicense')->once()->andReturn(null);

        $service = Mockery::mock(InstallationService::class);

        $response = new LicenseInstallController($validator, $service, new BannedHostService)->licenseInstall($this->moduleRequest([
            'product_id' => $product->id,
            'root_url' => 'https://example.com/helpdesk',
            'client_email' => 'client@example.com',
            'license_code' => 'MISSING-CODE',
            'installation_hash' => 'hash',
            'license_signature' => 'signature',
        ], 'POST'));

        $this->assertSame('notification_license_not_found', $response->headers->get('notification_case'));
        $this->assertSame('License MISSING-CODE not found for Faveo Helpdesk', $response->headers->get('notification_text'));
    }

    /**
     * Runs licenseInstall for $license from 127.0.0.1 on https://example.com/helpdesk
     * (installation domain "example.com/helpdesk") with every request check passing,
     * and returns the notification case. $registers: whether the install is expected to be saved.
     */
    private function installCase(\App\License\Models\License $license, bool $registers, mixed $clientId = null): ?string
    {
        $validator = Mockery::mock(LicenseValidator::class)->makePartial();
        $validator->shouldReceive('resolveIp')->andReturn('127.0.0.1');
        $validator->shouldReceive('isValidLicenseRequest', 'validateInstallationHash', 'verifyScriptSignature')->andReturn(true);
        $validator->shouldReceive('isBanned')->andReturn(false);
        $validator->shouldReceive('validateProduct')->andReturn(\App\Model\Product\Product::find($license->product_id));
        $validator->shouldReceive('findLicense')->andReturn($license);
        $validator->shouldReceive('validateLicense')->andReturn(['valid' => true, 'license' => $license]);

        $service = Mockery::mock(InstallationService::class);
        $service->shouldReceive('register')->times($registers ? 1 : 0);
        $service->shouldReceive('updateLogs')->times($registers ? 1 : 0);

        return new LicenseInstallController($validator, $service, new BannedHostService)->licenseInstall($this->moduleRequest([
            'product_id' => $license->product_id,
            'root_url' => 'https://example.com/helpdesk',
            'client_email' => 'client@example.com',
            'license_code' => $license->license_code,
            'installation_hash' => 'hash',
            'license_signature' => 'signature',
            'client_id' => $clientId,
        ], 'POST'))->headers->get('notification_case');
    }

    private function installationHere(\App\License\Models\License $license, array $attributes = []): void
    {
        $this->createInstallation(['license' => $license, 'installation_ip' => '127.0.0.1', 'installation_domain' => 'example.com/helpdesk'] + $attributes);
    }

    #[Test]
    #[Group('license-callbacks')]
    public function license_install_rejects_a_domain_installed_under_another_license_code(): void
    {
        $license = $this->createLicense(['license_limit' => 0]);
        $this->installationHere($license, ['license_code' => 'SOMEONE-ELSE']);

        $this->assertSame('notification_domain_in_use', $this->installCase($license, registers: false));
    }

    #[Test]
    #[Group('license-callbacks')]
    public function license_install_rejects_a_domain_installed_by_another_client(): void
    {
        $license = $this->createLicense(['license_limit' => 0]);
        $this->installationHere($license);

        $this->assertSame('notification_domain_in_use', $this->installCase($license, registers: false, clientId: $license->user_id + 1));
    }

    #[Test]
    #[Group('license-callbacks')]
    public function license_install_reinstall_by_the_same_client_without_limit_succeeds(): void
    {
        $license = $this->createLicense(['license_limit' => 0]);
        $this->installationHere($license);

        $this->assertSame('notification_license_ok', $this->installCase($license, registers: true, clientId: $license->user_id));
    }

    #[Test]
    #[Group('license-callbacks')]
    public function license_install_rejects_when_other_installations_reach_the_limit(): void
    {
        $license = $this->createLicense(['license_limit' => 1]);
        $this->createInstallation(['license' => $license, 'installation_domain' => 'elsewhere.test']);

        $this->assertSame('notification_license_limit', $this->installCase($license, registers: false));
        // A rejected install is reported as an error, not a success.
        $report = \App\License\Models\LicenseReport::where('license_code', $license->license_code)->latest('id')->first();
        $this->assertSame(0, $report->report_status);
        $this->assertSame(0, $report->report_system); // a License Report, not a System Report
    }

    #[Test]
    #[Group('license-callbacks')]
    public function license_install_rejects_when_the_clients_total_installations_exceed_the_limit(): void
    {
        $license = $this->createLicense(['license_limit' => 1]);
        $this->installationHere($license);
        // Same client, another code: not an "other installation" of this code, but counts toward the total.
        $this->createInstallation(['license' => $license, 'license_code' => 'OTHER-CODE', 'installation_domain' => 'elsewhere.test']);

        $this->assertSame('notification_license_limit', $this->installCase($license, registers: false, clientId: $license->user_id));
    }

    #[Test]
    #[Group('license-callbacks')]
    public function license_install_within_the_limit_succeeds(): void
    {
        $license = $this->createLicense(['license_limit' => 2]);
        $this->installationHere($license);

        $this->assertSame('notification_license_ok', $this->installCase($license, registers: true, clientId: $license->user_id));
    }
}
