<?php

namespace App\License\tests\Controllers\Admin;

use App\License\Controllers\Admin\DashboardController;
use App\License\Models\LicenseReport;
use App\License\Models\VersionCallback;
use App\License\tests\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class DashboardControllerTest extends LicenseTestCase
{
    #[Test]
    #[Group('license-admin')]
    public function dashboard_returns_counts_and_latest_module_records(): void
    {
        $product = $this->createProduct(['name' => 'Dashboard Product', 'status' => 1]);
        $version = $this->createVersion($product, ['version' => '4.4.4', 'status' => 1]);
        $license = $this->createLicense(['product_id' => $product->id]);
        $installation = $this->createInstallation(['license' => $license]);
        $callback = $this->createLicenseCallback(['license' => $license]);
        VersionCallback::create([
            'product_id' => $product->id,
            'version_id' => $version->id,
            'callback_type' => 1,
            'callback_ip' => '127.0.0.8',
            'callback_path' => '/var/www/dashboard',
            'callback_date_time' => now(),
            'callback_status' => 1,
        ]);
        LicenseReport::create([
            'product_id' => $product->id,
            'user_id' => $license->user_id,
            'license_code' => $license->license_code,
            'report_text' => 'dashboard report',
            'report_date_time' => now(),
            'report_system' => 0,
            'report_status' => 1,
        ]);

        $response = (new DashboardController())->dashboard();
        $json = $this->assertSuccessfulJson($response);
        $data = $json['data'];

        $this->assertGreaterThanOrEqual(1, $data['productsCount']);
        $this->assertGreaterThanOrEqual(1, $data['versionsCount']);
        $this->assertGreaterThanOrEqual(1, $data['licenseCount']);
        $this->assertGreaterThanOrEqual(2, $data['callbacksCount']);
        $this->assertTrue(collect($data['latestProducts'])->pluck('id')->contains($product->id));
        $this->assertTrue(collect($data['latestVersions'])->pluck('id')->contains($version->id));
        $this->assertTrue(collect($data['latestInstallations'])->pluck('id')->contains($installation->id));
        $this->assertTrue(collect($data['latestCallbacks'])->pluck('id')->contains($callback->id));
        $this->assertTrue(collect($data['latestLicenses'])->pluck('license_id')->contains($license->id));
    }
}
