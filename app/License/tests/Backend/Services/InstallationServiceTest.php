<?php

namespace App\License\tests\Backend\Services;

use App\License\Models\Installation;
use App\License\Models\InstallationLog;
use App\License\Services\InstallationService;
use App\License\tests\Backend\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class InstallationServiceTest extends LicenseTestCase
{
    private InstallationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new InstallationService();
    }

    #[Test]
    #[Group('license-service')]
    public function register_creates_and_updates_matching_installation(): void
    {
        $license = $this->createLicense();

        $installation = $this->service->register([
            'product_id' => $license->product_id,
            'user_id' => $license->user_id,
            'license_code' => $license->license_code,
            'installation_ip' => '10.0.0.1',
            'installation_domain' => 'service-install.test',
            'installation_hash' => 'hash-one',
        ]);
        $updated = $this->service->register([
            'product_id' => $license->product_id,
            'user_id' => $license->user_id,
            'license_code' => $license->license_code,
            'installation_ip' => '10.0.0.1',
            'installation_domain' => 'service-install.test',
            'installation_hash' => 'hash-two',
            'installation_status' => 0,
        ]);

        $this->assertSame($installation->id, $updated->id);
        $this->assertSame('hash-two', $updated->installation_hash);
        $this->assertSame(0, (int) $updated->installation_status);
        $this->assertSame(1, Installation::where('license_code', $license->license_code)->count());
    }

    #[Test]
    #[Group('license-service')]
    public function update_logs_uses_expected_payload_and_domain_normalization(): void
    {
        $license = $this->createLicense();
        $this->createInstallationLog([
            'license' => $license,
            'version_number' => '1.0.0',
            'installation_last_active_date' => now()->subDay(),
        ]);
        $this->createInstallationLog([
            'license' => $license,
            'version_number' => '2.0.0',
            'installation_domain' => 'new-log.test',
            'installation_last_active_date' => now(),
        ]);

        $this->moduleRequest();
        $updated = $this->service->updateLogs([
            'license_code' => $license->license_code,
            'root_url' => 'https://www.example.test/path',
            'version_number' => '3.0.0',
        ]);

        $this->assertSame(1, $updated['api_action_success']);
        $this->assertSame('Installation Logs updated successfully', $updated['page_message']);
        $this->assertDatabaseHas('installation_logs', [
            'license_code' => $license->license_code,
            'installation_domain' => 'example.test',
            'version_number' => '3.0.0',
            'installation_ip' => '127.0.0.1',
            'installation_status' => 1,
        ]);
        $this->assertSame(2, InstallationLog::where('license_code', $license->license_code)->count());
    }

    #[Test]
    #[Group('license-service')]
    public function installation_getters_return_license_and_product_filtered_data(): void
    {
        $product = $this->createProduct();
        $otherProduct = $this->createProduct();
        $license = $this->createLicense(['product_id' => $product->id]);
        $matching = $this->createInstallation([
            'license' => $license,
            'product_id' => $product->id,
            'installation_domain' => 'matching-install.test',
            'installation_ip' => '10.0.0.5',
            'installation_date' => '2026-04-21',
            'installation_status' => 1,
        ]);
        $this->createInstallation([
            'license' => $license,
            'product_id' => $otherProduct->id,
            'installation_domain' => 'other-install.test',
        ]);

        $byLicense = $this->service->getByLicenseCode($license->license_code);
        $details = $this->service->getInstallationsByProduct($license->license_code, $product->id);

        $this->assertTrue($byLicense->contains('id', $matching->id));
        $this->assertSame(['matching-install.test'], $details['installed_path']);
        $this->assertSame(['10.0.0.5'], $details['installed_ip']);
        $this->assertSame([1], array_map(intval(...), $details['installation_status']));
    }
}
