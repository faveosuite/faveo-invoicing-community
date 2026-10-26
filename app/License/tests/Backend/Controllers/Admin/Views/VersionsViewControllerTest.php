<?php

namespace App\License\tests\Backend\Controllers\Admin\Views;

use App\License\Controllers\Admin\Views\VersionsViewController;
use App\License\Models\VersionCallback;
use App\License\tests\Backend\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class VersionsViewControllerTest extends LicenseTestCase
{
    private VersionsViewController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new VersionsViewController;
    }

    #[Test]
    #[Group('license-admin')]
    public function get_version_info_returns_version_and_product(): void
    {
        $product = $this->createProduct(['name' => 'Version Detail Product']);
        $version = $this->createVersion($product, ['version' => '5.5.5']);

        $response = $this->controller->getVersionInfo($version->id);
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame($version->id, $json['data']['id']);
        $this->assertSame('5.5.5', $json['data']['version_number']);
        $this->assertSame($product->name, $json['data']['product_title']);
    }

    #[Test]
    #[Group('license-admin')]
    public function get_version_callbacks_returns_callbacks_for_version(): void
    {
        $product = $this->createProduct();
        $version = $this->createVersion($product, ['version' => '6.6.6']);
        $callback = VersionCallback::create([
            'product_id' => $product->id,
            'version_id' => $version->id,
            'callback_type' => 1,
            'callback_ip' => '10.30.40.50',
            'callback_path' => '/var/www/version',
            'callback_date_time' => now(),
            'callback_status' => 1,
        ]);

        $response = $this->controller->getVersionCallbacks($this->moduleRequest([
            'search_query' => '10.30.40.50',
        ]), $version->id);
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame($callback->id, $json['data']['data'][0]['id']);
        $this->assertSame($version->id, $json['data']['data'][0]['version_id']);
    }
}
