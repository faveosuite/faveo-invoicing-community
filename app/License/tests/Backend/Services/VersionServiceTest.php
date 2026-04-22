<?php

namespace App\License\tests\Backend\Services;

use App\License\Services\VersionService;
use App\License\tests\Backend\LicenseTestCase;
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
    public function latest_version_and_version_by_number_finders_return_matching_records(): void
    {
        $product = $this->createProduct();
        $older = $this->createVersion($product, ['version' => '1.0.0', 'status' => 1]);
        $latest = $this->createVersion($product, ['version' => '2.0.0', 'status' => 1]);

        $older->forceFill(['created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)])->save();
        $latest->forceFill(['created_at' => now()->subDay(), 'updated_at' => now()->subDay()])->save();

        $latestActive = $this->service->getLatestVersion($product->id);
        $byNumber = $this->service->getVersionByNumber($product->id, '1.0.0');

        $this->assertSame($latest->id, $latestActive->id);
        $this->assertSame($older->id, $byNumber->id);
    }
}
