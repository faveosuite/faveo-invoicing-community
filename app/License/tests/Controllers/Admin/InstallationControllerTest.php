<?php

namespace App\License\tests\Controllers\Admin;

use App\License\Controllers\Admin\InstallationController;
use App\License\Models\Installation;
use App\License\Models\LicensePlugin;
use App\License\tests\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class InstallationControllerTest extends LicenseTestCase
{
    private InstallationController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new InstallationController();
    }

    #[Test]
    #[Group('license-admin')]
    public function installation_add_creates_installation_from_license(): void
    {
        $license = $this->createLicense();
        $request = $this->moduleRequest([
            'license_code' => $license->license_code,
            'installation_domain' => 'install.example.com',
            'installation_status' => 1,
            'installation_hash' => 'hash-123',
        ], 'POST');

        $response = $this->controller->installationAdd($request);
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame($license->license_code, $json['data']['license_code']);
        $this->assertDatabaseHas('installations', [
            'license_code' => $license->license_code,
            'installation_domain' => 'install.example.com',
            'installation_hash' => 'hash-123',
        ]);
    }

    #[Test]
    #[Group('license-admin')]
    public function installation_add_rejects_unknown_license(): void
    {
        $response = $this->controller->installationAdd($this->moduleRequest([
            'license_code' => 'missing-license',
        ], 'POST'));

        $this->assertErrorJson($response, 404);
    }

    #[Test]
    #[Group('license-admin')]
    public function installation_update_changes_ip_and_status(): void
    {
        $installation = $this->createInstallation([
            'installation_domain' => 'editable-install.example.com',
        ]);

        $response = $this->controller->installationUpdate($this->moduleRequest([
            'id' => $installation->id,
            'installation_ip' => '127.0.0.9',
            'installation_disable_ip' => 1,
            'installation_status' => 2,
        ], 'POST'));
        $json = $this->jsonContent($response);

        $this->assertSame(1, $json['action_success']);
        $installation->refresh();
        $this->assertSame('127.0.0.9', $installation->installation_ip);
        $this->assertSame(2, $installation->installation_status);
        $this->assertSame(1, $installation->installation_disable_ip_verification);
    }

    #[Test]
    #[Group('license-admin')]
    public function installation_update_delete_record_removes_plugin_installations_for_same_license(): void
    {
        $license = $this->createLicense();
        $plugin = $this->createProduct();
        LicensePlugin::create(['license_id' => $license->id, 'product_id' => $plugin->id]);
        $installation = $this->createInstallation(['license' => $license]);
        $pluginInstallation = $this->createInstallation([
            'license' => $license,
            'product_id' => $plugin->id,
            'installation_domain' => 'plugin-install.example.com',
        ]);

        $response = $this->controller->installationUpdate($this->moduleRequest([
            'id' => $installation->id,
            'delete_record' => 1,
        ], 'POST'));
        $json = $this->jsonContent($response);

        $this->assertSame(1, $json['action_success']);
        $this->assertDatabaseMissing('installations', ['id' => $installation->id]);
        $this->assertDatabaseMissing('installations', ['id' => $pluginInstallation->id]);
    }

    #[Test]
    #[Group('license-admin')]
    public function show_filters_and_formats_installations(): void
    {
        $product = $this->createProduct(['name' => 'Installation Product']);
        $user = $this->createUser(['email' => 'installation-'.uniqid().'@example.test']);
        $license = $this->createLicense(['product_id' => $product->id, 'user_id' => $user->id]);
        $installation = $this->createInstallation([
            'license' => $license,
            'installation_domain' => 'search-install.example.com',
        ]);

        $response = $this->controller->show($this->moduleRequest([
            'search_query' => 'search-install.example.com',
            'perPage' => 5,
        ]));
        $json = $this->assertSuccessfulJson($response);
        $row = $json['data']['data'][0];

        $this->assertSame($installation->id, $row['id']);
        $this->assertSame($product->name, $row['product_title']);
        $this->assertSame($user->email, $row['client_email']);
    }

    #[Test]
    #[Group('license-admin')]
    public function edit_returns_installation_payload(): void
    {
        $installation = $this->createInstallation();

        $response = $this->controller->edit($installation->id);
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame($installation->id, $json['data']['installation']['id']);
    }

    #[Test]
    #[Group('license-admin')]
    public function delete_installations_removes_single_record(): void
    {
        $installation = $this->createInstallation();

        $response = $this->controller->deleteInstallations($this->moduleRequest([
            'id' => $installation->id,
        ], 'POST'));
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame(1, $json['data']);
        $this->assertDatabaseMissing('installations', ['id' => $installation->id]);
    }

    #[Test]
    #[Group('license-admin')]
    public function remove_unwanted_installations_deletes_by_domain_path(): void
    {
        $installation = $this->createInstallation(['installation_domain' => 'remove-me.example.com']);

        $deleted = $this->controller->removeUnwantedInstallations($this->moduleRequest([
            'installation_path' => 'remove-me.example.com',
        ], 'POST'));

        $this->assertSame(1, $deleted);
        $this->assertSame(0, Installation::where('id', $installation->id)->count());
    }

    #[Test]
    #[Group('license-admin')]
    public function update_the_license_code_deletes_matching_installations(): void
    {
        $license = $this->createLicense();
        $this->createInstallation(['license' => $license]);

        $deleted = $this->controller->updateTheLicenseCode($this->moduleRequest([
            'old_license_code' => $license->license_code,
        ], 'POST'));

        $this->assertSame(1, $deleted);
        $this->assertSame(0, Installation::where('license_code', $license->license_code)->count());
    }
}
