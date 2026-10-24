<?php

namespace App\License\tests\Backend\Controllers;

use App\License\Controllers\LicenseApiController;
use App\License\Services\LicenseService;
use App\License\tests\Backend\LicenseTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class LicenseApiControllerTest extends LicenseTestCase
{
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
    public function reissue_license_cloud_deletes_installations_through_service(): void
    {
        $service = Mockery::mock(LicenseService::class);
        $service->shouldReceive('reissueLicenseCloud')->once()->with('LIC-API')->andReturn(2);

        $response = new LicenseApiController($service)->reissueLicenseCloud($this->moduleRequest([
            'license_code' => 'LIC-API',
        ], 'POST'));
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame(1, $json['data']);
    }
}
