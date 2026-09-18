<?php

namespace App\License\tests\Backend\Controllers\Admin;

use App\License\Controllers\Admin\VersionsController;
use App\License\Models\VersionCallback;
use App\License\tests\Backend\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class VersionsControllerTest extends LicenseTestCase
{
    #[Test]
    #[Group('license-admin')]
    public function show_filters_sorts_and_formats_versions(): void
    {
        $controller = new VersionsController;
        $product = $this->createProduct(['name' => 'Versioned Product']);
        $version = $this->createVersion($product, ['version' => '7.7.7', 'status' => 1]);
        VersionCallback::create([
            'product_id' => $product->id,
            'version_id' => $version->id,
            'callback_type' => 1,
            'callback_ip' => '127.0.0.1',
            'callback_path' => '/var/www/html',
            'callback_date_time' => now(),
            'callback_status' => 1,
        ]);

        $response = $controller->show($this->moduleRequest([
            'search_query' => '7.7.7',
            'sort_field' => 'version',
            'sort_order' => 'asc',
            'perPage' => 5,
        ]));
        $json = $this->assertSuccessfulJson($response);
        $row = $json['data']['data'][0];

        $this->assertSame($version->id, $row['id']);
        $this->assertSame('7.7.7', $row['version_number']);
        $this->assertSame($product->name, $row['product_title']);
        $this->assertSame(1, $row['callback_count']);
    }
}
