<?php

namespace App\License\tests\Controllers;

use App\License\Controllers\LicenseApiController;
use App\License\Services\LicenseService;
use App\License\tests\LicenseTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class LicenseApiControllerTest extends LicenseTestCase
{
    #[Test]
    #[Group('license-api')]
    public function license_info_returns_service_payload(): void
    {
        $service = Mockery::mock(LicenseService::class);
        $service->shouldReceive('getLicenseInfo')->once()->with('LIC-API')->andReturn([
            'license' => ['license_code' => 'LIC-API'],
        ]);

        $response = (new LicenseApiController($service))->licenseInfo($this->moduleRequest([
            'license_code' => 'LIC-API',
        ]));
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame('LIC-API', $json['data']['license']['license_code']);
    }

    #[Test]
    #[Group('license-api')]
    public function license_info_returns_404_when_service_has_no_license(): void
    {
        $service = Mockery::mock(LicenseService::class);
        $service->shouldReceive('getLicenseInfo')->once()->with('missing')->andReturn(null);

        $response = (new LicenseApiController($service))->licenseInfo($this->moduleRequest([
            'license_code' => 'missing',
        ]));

        $this->assertErrorJson($response, 404);
    }

    #[Test]
    #[Group('license-api')]
    public function individual_license_info_and_order_delegate_to_service(): void
    {
        $service = Mockery::mock(LicenseService::class);
        $service->shouldReceive('getIndividualLicenseInfo')->once()->with('LIC-API')->andReturn([
            ['key' => 'edition', 'value' => 'enterprise'],
        ]);
        $service->shouldReceive('getOrderNumber')->once()->with('LIC-API')->andReturn('ORDER-1');

        $controller = new LicenseApiController($service);

        $info = $this->assertSuccessfulJson($controller->individualLicenseInfo($this->moduleRequest([
            'license_code' => 'LIC-API',
        ])));
        $order = $this->assertSuccessfulJson($controller->getOrder($this->moduleRequest([
            'license_code' => 'LIC-API',
        ])));

        $this->assertSame('edition', $info['data'][0]['key']);
        $this->assertSame('ORDER-1', $order['data']);
    }

    #[Test]
    #[Group('license-api')]
    public function plugin_license_accepts_json_string_codes(): void
    {
        $service = Mockery::mock(LicenseService::class);
        $service->shouldReceive('getPluginLicenses')->once()->with(['LIC-A', 'LIC-B'])->andReturn([
            ['product_id' => 10],
        ]);

        $response = (new LicenseApiController($service))->pluginLicense($this->moduleRequest([
            'license_code' => json_encode(['LIC-A', 'LIC-B']),
        ], 'POST'));
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame(10, $json['data'][0]['product_id']);
    }

    #[Test]
    #[Group('license-api')]
    public function reissue_license_cloud_deletes_installations_through_service(): void
    {
        $service = Mockery::mock(LicenseService::class);
        $service->shouldReceive('reissueLicenseCloud')->once()->with('LIC-API')->andReturn(2);

        $response = (new LicenseApiController($service))->reissueLicenseCloud($this->moduleRequest([
            'license_code' => 'LIC-API',
        ], 'POST'));
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame(1, $json['data']);
    }
}
