<?php

namespace App\License\tests\Backend\Controllers\Admin;

use App\License\Controllers\Admin\CallBackController;
use App\License\Models\LicenseCallback;
use App\License\Models\VersionCallback;
use App\License\tests\Backend\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class CallBackControllerTest extends LicenseTestCase
{
    private CallBackController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new CallBackController;
    }

    #[Test]
    #[Group('license-admin')]
    public function license_callbacks_filters_and_formats_rows(): void
    {
        $product = $this->createProduct(['name' => 'Callback Product']);
        $user = $this->createUser(['email' => 'callback-'.uniqid().'@example.test']);
        $license = $this->createLicense(['product_id' => $product->id, 'user_id' => $user->id]);
        $callback = $this->createLicenseCallback([
            'license' => $license,
            'callback_domain' => 'callbacksearch.example.com',
        ]);

        $response = $this->controller->licneseCallbacks($this->moduleRequest([
            'search_query' => 'callbacksearch.example.com',
            'perPage' => 5,
        ]));
        $json = $this->assertSuccessfulJson($response);
        $row = $json['data']['data'][0];

        $this->assertSame($callback->id, $row['id']);
        $this->assertSame($product->name, $row['product_title']);
        $this->assertSame($user->email, $row['client_email']);
        $this->assertSame($license->id, $row['license_id']);
    }

    #[Test]
    #[Group('license-admin')]
    public function update_callbacks_filters_and_formats_rows(): void
    {
        $product = $this->createProduct(['name' => 'Update Callback Product']);
        $version = $this->createVersion($product, ['version' => '8.8.8']);
        $callback = VersionCallback::create([
            'product_id' => $product->id,
            'version_id' => $version->id,
            'callback_type' => 1,
            'callback_ip' => '192.168.10.10',
            'callback_path' => '/var/www/update',
            'callback_date_time' => now(),
            'callback_status' => 1,
        ]);

        $response = $this->controller->updateCallbacks($this->moduleRequest([
            'search_query' => '8.8.8',
            'perPage' => 5,
        ]));
        $json = $this->assertSuccessfulJson($response);
        $row = $json['data']['data'][0];

        $this->assertSame($callback->id, $row['id']);
        $this->assertSame($product->name, $row['product_title']);
        $this->assertSame($version->version, $row['version_number']);
    }

    #[Test]
    #[Group('license-admin')]
    public function callbacks_delete_removes_license_callbacks_by_product_id(): void
    {
        $license = $this->createLicense();
        $this->createLicenseCallback(['license' => $license]);

        $response = $this->controller->callbacksDelete($this->moduleRequest([
            'call' => [$license->product_id],
            'isLicense' => true,
        ], 'POST'));
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame(1, $json['data']);
        $this->assertSame(0, LicenseCallback::where('product_id', $license->product_id)->count());
    }
}
