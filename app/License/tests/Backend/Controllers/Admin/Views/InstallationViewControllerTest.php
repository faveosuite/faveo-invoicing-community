<?php

namespace App\License\tests\Backend\Controllers\Admin\Views;

use App\License\Controllers\Admin\Views\InstallationViewController;
use App\License\tests\Backend\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class InstallationViewControllerTest extends LicenseTestCase
{
    private InstallationViewController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new InstallationViewController;
    }

    #[Test]
    #[Group('license-admin')]
    public function get_installation_returns_formatted_installation_details(): void
    {
        $product = $this->createProduct(['name' => 'Installation Detail Product']);
        $user = $this->createUser(['email' => 'installation-detail-'.uniqid().'@example.test']);
        $license = $this->createLicense(['product_id' => $product->id, 'user_id' => $user->id]);
        $installation = $this->createInstallation(['license' => $license]);

        $response = $this->controller->getInstallation($installation->id);
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame($installation->id, $json['data']['id']);
        $this->assertSame($product->name, $json['data']['product_title']);
        $this->assertSame($user->email, $json['data']['client_email']);
        $this->assertSame($license->id, $json['data']['license_id']);
    }

    #[Test]
    #[Group('license-admin')]
    public function get_installation_callbacks_returns_callbacks_for_domain(): void
    {
        $installation = $this->createInstallation([
            'installation_domain' => 'installation-callback-detail.example.com',
        ]);
        $callback = $this->createLicenseCallback([
            'license_code' => $installation->license_code,
            'product_id' => $installation->product_id,
            'user_id' => $installation->user_id,
            'callback_domain' => 'installation-callback-detail.example.com',
            'callback_ip' => '10.20.30.40',
        ]);

        $response = $this->controller->getInstallationCallbacks($this->moduleRequest([
            'search_query' => '10.20.30.40',
        ]), $installation->id);
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame($callback->id, $json['data']['data'][0]['id']);
    }
}
