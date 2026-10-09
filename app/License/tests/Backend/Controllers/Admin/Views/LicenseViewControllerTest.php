<?php

namespace App\License\tests\Backend\Controllers\Admin\Views;

use App\License\Controllers\Admin\Views\LicenseViewController;
use App\License\tests\Backend\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class LicenseViewControllerTest extends LicenseTestCase
{
    private LicenseViewController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new LicenseViewController;
    }

    #[Test]
    #[Group('license-admin')]
    public function get_license_details_returns_counts_and_related_names(): void
    {
        $product = $this->createProduct(['name' => 'Detail Product']);
        $user = $this->createUser(['email' => 'detail-'.uniqid().'@example.test']);
        $license = $this->createLicense(['product_id' => $product->id, 'user_id' => $user->id]);
        $this->createInstallation(['license' => $license]);
        $this->createLicenseCallback(['license' => $license]);

        $response = $this->controller->getLicenseDetails($license->id);
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame($license->id, $json['data']['id']);
        $this->assertSame($product->name, $json['data']['product_title']);
        $this->assertSame($user->email, $json['data']['client_email']);
        $this->assertSame(1, $json['data']['installation_counts']);
        $this->assertSame(1, $json['data']['call_backs_count']);
    }

    #[Test]
    #[Group('license-admin')]
    public function get_license_installations_returns_installations_for_license(): void
    {
        $license = $this->createLicense();
        $installation = $this->createInstallation([
            'license' => $license,
            'installation_domain' => 'license-install-detail.example.com',
        ]);

        $response = $this->controller->getLicenseInstallations($this->moduleRequest([
            'search_query' => 'license-install-detail.example.com',
        ]), $license->id);
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame($installation->id, $json['data']['data'][0]['id']);
    }

    #[Test]
    #[Group('license-admin')]
    public function get_license_callbacks_returns_callbacks_for_license(): void
    {
        $license = $this->createLicense();
        $callback = $this->createLicenseCallback([
            'license' => $license,
            'callback_domain' => 'license-callback-detail.example.com',
        ]);

        $response = $this->controller->getLicenseCallBacks($this->moduleRequest([
            'search_query' => 'license-callback-detail.example.com',
        ]), $license->id);
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame($callback->id, $json['data']['data'][0]['id']);
    }

    #[Test]
    #[Group('license-admin')]
    public function get_license_installation_logs_returns_logs_for_license(): void
    {
        $license = $this->createLicense();
        $log = $this->createInstallationLog([
            'license' => $license,
            'installation_domain' => 'license-log-detail.example.com',
        ]);

        $response = $this->controller->getLicenseInstallationLogs($this->moduleRequest([
            'search_query' => 'license-log-detail.example.com',
        ]), $license->id);
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame($log->id, $json['data']['data'][0]['id']);
    }
}
