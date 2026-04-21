<?php

namespace App\License\tests\Services;

use App\License\Models\Installation;
use App\License\Models\InstallationLog;
use App\License\Services\InstallationService;
use App\License\tests\LicenseTestCase;
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
    public function update_returns_original_response_shape_for_success_and_missing_rows(): void
    {
        $installation = $this->createInstallation(['installation_status' => 1]);

        $success = $this->service->update($installation->id, [
            'installation_domain' => 'updated-install.test',
            'installation_status' => 0,
        ]);
        $missing = $this->service->update(99999999, ['installation_status' => 0]);

        $installation->refresh();
        $this->assertSame(1, $success['api_action_success']);
        $this->assertSame(0, $success['api_error_detected']);
        $this->assertSame('Installation updated successfully', $success['page_message']);
        $this->assertSame('updated-install.test', $installation->installation_domain);
        $this->assertSame(0, (int) $installation->installation_status);
        $this->assertSame(0, $missing['api_action_success']);
        $this->assertSame(1, $missing['api_error_detected']);
        $this->assertSame('Installation not found', $missing['page_message']);
    }

    #[Test]
    #[Group('license-service')]
    public function license_code_updates_deactivate_counts_remove_and_delete_installations(): void
    {
        $license = $this->createLicense();
        $active = $this->createInstallation(['license' => $license, 'installation_status' => 1]);
        $inactive = $this->createInstallation([
            'license' => $license,
            'installation_domain' => 'inactive-install.test',
            'installation_status' => 0,
        ]);

        $this->assertSame(1, $this->service->countActiveInstallations($license->license_code));
        $this->assertTrue($this->service->deactivate($active->id));
        $this->assertSame(0, (int) $active->refresh()->installation_status);
        $this->assertTrue($this->service->updateByLicenseCode($license->license_code, ['installation_status' => 1]));
        $this->assertFalse($this->service->updateByLicenseCode('missing-license', ['installation_status' => 0]));
        $this->assertSame(2, $this->service->countActiveInstallations($license->license_code));

        Installation::whereKey($inactive->id)->update(['installation_status' => 0]);
        $this->assertSame(1, $this->service->removeUnwanted($license->license_code));
        $this->assertDatabaseMissing('installations', ['id' => $inactive->id]);
        $this->assertSame(1, Installation::where('license_code', $license->license_code)->count());
        $this->assertSame(1, $this->service->deleteByLicenseCode($license->license_code));
        $this->assertSame(0, Installation::where('license_code', $license->license_code)->count());
    }

    #[Test]
    #[Group('license-service')]
    public function reissue_deletes_installation_by_domain(): void
    {
        $installation = $this->createInstallation(['installation_domain' => 'reissue-install.test']);

        $this->assertTrue($this->service->reissue('reissue-install.test'));
        $this->assertDatabaseMissing('installations', ['id' => $installation->id]);
        $this->assertFalse($this->service->reissue('missing-install.test'));
    }

    #[Test]
    #[Group('license-service')]
    public function get_logs_and_update_logs_use_expected_payload_and_domain_normalization(): void
    {
        $license = $this->createLicense();
        $oldLog = $this->createInstallationLog([
            'license' => $license,
            'version_number' => '1.0.0',
            'installation_last_active_date' => now()->subDay(),
        ]);
        $newLog = $this->createInstallationLog([
            'license' => $license,
            'version_number' => '2.0.0',
            'installation_domain' => 'new-log.test',
            'installation_last_active_date' => now(),
        ]);

        $logs = $this->service->getLogs($license->license_code);
        $this->moduleRequest();
        $updated = $this->service->updateLogs([
            'license_code' => $license->license_code,
            'root_url' => 'https://www.example.test/path',
            'version_number' => '3.0.0',
        ]);

        $this->assertSame(1, $logs['api_action_success']);
        $this->assertSame($newLog->id, $logs['page_message'][0]['id']);
        $this->assertSame($oldLog->id, $logs['page_message'][1]['id']);
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
    public function installation_getters_return_license_user_and_product_filtered_data(): void
    {
        $product = $this->createProduct();
        $otherProduct = $this->createProduct();
        $user = $this->createUser();
        $license = $this->createLicense(['product_id' => $product->id, 'user_id' => $user->id]);
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
        $byUser = $this->service->getByUserId($user->id);
        $details = $this->service->getInstallationsByProduct($license->license_code, $product->id);

        $this->assertTrue($byLicense->contains('id', $matching->id));
        $this->assertTrue($byUser->contains('id', $matching->id));
        $this->assertSame(['matching-install.test'], $details['installed_path']);
        $this->assertSame(['10.0.0.5'], $details['installed_ip']);
        $this->assertSame([1], array_map('intval', $details['installation_status']));
    }
}
