<?php

namespace App\License\tests\Backend\Controllers\AfuCallbacks;

use App\License\Controllers\AfuCallbacks\LicensedPluginsController;
use App\License\Helpers\LicenseValidator;
use App\License\Models\License;
use App\License\Models\LicensePlugin;
use App\License\Services\LicenseService;
use App\License\tests\Backend\LicenseTestCase;
use App\Model\Product\Product;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class LicensedPluginsControllerTest extends LicenseTestCase
{
    private Product $product;

    private Product $addon;

    private License $license;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = $this->createProduct(['product_key' => 'MAINKEY']);
        $this->addon = $this->createProduct(['product_type' => 'addon', 'product_key' => 'ADDONKEY', 'product_path' => 'Calendar']);
        $this->createVersion($this->addon, ['version' => 'v2.0.1', 'status' => 1]);
        $this->license = $this->createLicense(['product_id' => $this->product->id, 'license_status' => 1]);
        LicensePlugin::create(['license_id' => $this->license->id, 'product_id' => $this->addon->id]);
    }

    private function requestPlugins(string $licenseCode): mixed
    {
        $validator = Mockery::mock(LicenseValidator::class);
        $validator->shouldReceive('resolveIp')->andReturn('127.0.0.1');
        $validator->shouldReceive('isValidAfuRequest')->andReturn(true);
        $validator->shouldReceive('isBanned')->andReturn(false);
        $validator->shouldReceive('verifyAfuScriptSignature')->andReturn(true);

        return new LicensedPluginsController($validator, app(LicenseService::class))->licensedPlugins($this->moduleRequest([
            'product_id' => $this->product->id,
            'product_key' => 'MAINKEY',
            'user_local_path' => '/var/www/html',
            'script_signature' => 'signature',
            'license_code' => $licenseCode,
        ], 'POST'));
    }

    #[Test]
    #[Group('license-callbacks')]
    public function it_lists_the_add_ons_the_licence_can_install(): void
    {
        $response = $this->requestPlugins($this->license->license_code);

        $this->assertSame('notification_operation_ok', $response->headers->get('notification_case'));
        $this->assertSame([[
            'product_id' => $this->addon->id,
            'product_name' => $this->addon->name,
            'product_key' => 'ADDONKEY',
            'product_description' => $this->addon->fresh()->product_description,
            'version' => 'v2.0.1',
            'license_code' => $this->license->license_code,
            'path' => 'Calendar',
            'dependency' => null,
        ]], $this->jsonContent($response)['plugins']);
    }

    #[Test]
    #[Group('license-callbacks')]
    public function it_refuses_a_licence_of_another_product(): void
    {
        $other = $this->createLicense(['license_status' => 1]);

        $response = $this->requestPlugins($other->license_code);

        $this->assertSame('notification_license_not_found', $response->headers->get('notification_case'));
        $this->assertSame([], $this->jsonContent($response));
    }

    #[Test]
    #[Group('license-callbacks')]
    public function it_leaves_out_an_add_on_already_installed_on_that_licence(): void
    {
        $this->createInstallation(['license' => $this->license, 'product_id' => $this->addon->id]);

        $this->assertSame([], $this->jsonContent($this->requestPlugins($this->license->license_code))['plugins']);
    }
}
