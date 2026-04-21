<?php

namespace App\License\tests\Services;

use App\License\Models\VersionCallback;
use App\License\Models\VersionInstallation;
use App\License\Services\VersionService;
use App\License\tests\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class VersionServiceTest extends LicenseTestCase
{
    private VersionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new VersionService();
    }

    #[Test]
    #[Group('license-service')]
    public function create_and_update_version_records(): void
    {
        $product = $this->createProduct();

        $version = $this->service->create([
            'product_id' => $product->id,
            'version_number' => '1.2.3',
            'version_changelog' => 'Initial release',
            'version_install_file' => 'release-1.2.3.zip',
            'release_type' => 'beta',
            'is_private' => 1,
            'is_restricted' => 1,
            'version_expire_date' => '',
        ]);
        $updated = $this->service->update($version->id, [
            'title' => 'Updated title',
            'status' => 0,
        ]);

        $version->refresh();

        $this->assertSame($product->id, (int) $version->product_id);
        $this->assertSame('1.2.3', $version->version);
        $this->assertSame('Initial release', $version->description);
        $this->assertSame('release-1.2.3.zip', $version->file);
        $this->assertSame('beta', $version->release_type);
        $this->assertNull($version->version_expire_date);
        $this->assertTrue($updated);
        $this->assertSame('Updated title', $version->title);
        $this->assertSame(0, (int) $version->status);
    }

    #[Test]
    #[Group('license-service')]
    public function getters_return_product_versions_latest_active_version_and_download_file(): void
    {
        $product = $this->createProduct();
        $older = $this->createVersion($product, ['version' => '1.0.0', 'file' => 'old.zip', 'status' => 1]);
        $inactive = $this->createVersion($product, ['version' => '9.0.0', 'file' => 'inactive.zip', 'status' => 0]);
        $latest = $this->createVersion($product, ['version' => '2.0.0', 'file' => 'latest.zip', 'status' => 1]);

        $older->forceFill(['created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)])->save();
        $latest->forceFill(['created_at' => now()->subDay(), 'updated_at' => now()->subDay()])->save();
        $inactive->forceFill(['created_at' => now(), 'updated_at' => now()])->save();

        $versions = $this->service->getByProductId($product->id);
        $latestActive = $this->service->getLatestVersion($product->id);
        $byNumber = $this->service->getVersionByNumber($product->id, '1.0.0');

        $this->assertTrue($versions->contains('id', $older->id));
        $this->assertTrue($versions->contains('id', $latest->id));
        $this->assertSame($latest->id, $latestActive->id);
        $this->assertSame($older->id, $byNumber->id);
        $this->assertSame('latest.zip', $this->service->getDownloadFile($latest->id));
        $this->assertNull($this->service->getDownloadFile(99999999));
    }

    #[Test]
    #[Group('license-service')]
    public function update_availability_reports_no_versions_available_and_current_cases(): void
    {
        $productWithNoVersions = $this->createProduct();
        $product = $this->createProduct();
        $this->createVersion($product, ['version' => '2.0.0', 'file' => 'latest.zip', 'description' => 'Latest changes', 'status' => 1]);

        $none = $this->service->isUpdateAvailable($productWithNoVersions->id, '1.0.0');
        $available = $this->service->isUpdateAvailable($product->id, '1.5.0');
        $current = $this->service->isUpdateAvailable($product->id, '2.0.0');

        $this->assertFalse($none['available']);
        $this->assertSame('No versions found', $none['message']);
        $this->assertTrue($available['available']);
        $this->assertSame('2.0.0', $available['latest_version']);
        $this->assertSame('latest.zip', $available['install_file']);
        $this->assertFalse($current['available']);
        $this->assertNull($current['install_file']);
    }

    #[Test]
    #[Group('license-service')]
    public function register_installation_and_log_callback_create_tracking_rows(): void
    {
        $this->moduleRequest();
        $product = $this->createProduct();
        $version = $this->createVersion($product, ['version' => '4.0.0']);

        $installation = $this->service->registerInstallation($product->id, $version->id, '10.0.0.8', '/var/www/app');
        $callback = $this->service->logCallback($product->id, $version->id, 'check', null, '/var/www/app');

        $this->assertInstanceOf(VersionInstallation::class, $installation);
        $this->assertSame($product->id, (int) $installation->product_id);
        $this->assertSame($version->id, (int) $installation->version_id);
        $this->assertSame(1, (int) $installation->installation_status);
        $this->assertInstanceOf(VersionCallback::class, $callback);
        $this->assertSame('check', $callback->callback_type);
        $this->assertSame('127.0.0.1', $callback->callback_ip);
        $this->assertSame('/var/www/app', $callback->callback_path);
        $this->assertDatabaseHas('version_installations', ['id' => $installation->id]);
        $this->assertDatabaseHas('version_callbacks', ['id' => $callback->id]);
    }
}
