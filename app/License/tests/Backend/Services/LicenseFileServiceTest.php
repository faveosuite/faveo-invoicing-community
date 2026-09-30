<?php

namespace App\License\tests\Backend\Services;

use App\License\Models\LicenseScheme;
use App\License\Services\Ed25519SigningService;
use App\License\Services\LicenseFileService;
use App\License\tests\Backend\LicenseTestCase;
use App\Model\Order\Order;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class LicenseFileServiceTest extends LicenseTestCase
{
    private LicenseFileService $service;

    private Ed25519SigningService $signingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signingService = new Ed25519SigningService;
        $this->service = new LicenseFileService($this->signingService);
    }

    #[Test]
    #[Group('license-file-service')]
    public function build_for_order_returns_null_when_no_license_matches_the_order(): void
    {
        $product = $this->createProduct();
        $order = Order::factory()->create(['product' => $product->id, 'number' => 999999]);

        $this->assertNull($this->service->buildForOrder($order, $product));
    }

    #[Test]
    #[Group('license-file-service')]
    public function build_for_order_builds_the_main_product_payload(): void
    {
        $this->seedLicenseSchemes();
        $product = $this->createProduct(['product_key' => 'MAINKEY']);
        $orderNumber = random_int(10000000, 99999999);
        $order = Order::factory()->create(['product' => $product->id, 'number' => $orderNumber]);
        $license = $this->createLicense([
            'product_id' => $product->id,
            'license_order_number' => $orderNumber,
            'license_domain' => 'client.example.test',
            'license_machine_id' => 'MACHINE-1',
        ]);

        $file = $this->service->buildForOrder($order, $product);

        $this->assertNotNull($file);
        $decoded = json_decode($file, true);
        $payload = json_decode($decoded['license'], true);

        $this->assertSame($license->license_code, $payload['license_code']);
        $this->assertSame($product->id, $payload['product_id']);
        $this->assertSame('client.example.test', $payload['license_domain']);
        $this->assertSame('MACHINE-1', $payload['license_machine_id']);
        $this->assertArrayHasKey('product_schema', $payload['scheme_query']);
        $this->assertArrayNotHasKey('plugin_create_schema', $payload['scheme_query']);

        $this->assertTrue($this->signingService->verify($decoded['license'], $decoded['signature']));
    }

    #[Test]
    #[Group('license-file-service')]
    public function build_for_order_builds_the_plugin_payload_when_product_is_an_attached_addon(): void
    {
        $this->seedLicenseSchemes();
        $mainProduct = $this->createProduct(['product_key' => 'MAINKEY2']);
        $plugin = $this->createProduct(['product_key' => 'PLUGINKEY2']);
        $orderNumber = random_int(10000000, 99999999);
        $order = Order::factory()->create(['product' => $mainProduct->id, 'number' => $orderNumber]);
        $license = $this->createLicense([
            'product_id' => $mainProduct->id,
            'license_order_number' => $orderNumber,
        ]);
        $license->addonProducts()->attach($plugin->id);

        $file = $this->service->buildForOrder($order, $plugin);

        $this->assertNotNull($file);
        $decoded = json_decode($file, true);
        $payload = json_decode($decoded['license'], true);

        $this->assertSame($plugin->id, $payload['product_id']);
        $this->assertArrayHasKey('plugin_create_schema', $payload['scheme_query']);
        $this->assertArrayHasKey('plugin_update_schema', $payload['scheme_query']);
        $this->assertArrayNotHasKey('product_schema', $payload['scheme_query']);
    }

    #[Test]
    #[Group('license-file-service')]
    public function build_for_order_returns_null_when_product_is_a_plugin_not_attached_to_the_license(): void
    {
        $this->seedLicenseSchemes();
        $mainProduct = $this->createProduct(['product_key' => 'MAINKEY3']);
        $unattachedPlugin = $this->createProduct(['product_key' => 'PLUGINKEY3']);
        $orderNumber = random_int(10000000, 99999999);
        $order = Order::factory()->create(['product' => $mainProduct->id, 'number' => $orderNumber]);
        $this->createLicense([
            'product_id' => $mainProduct->id,
            'license_order_number' => $orderNumber,
        ]);
        // $unattachedPlugin is intentionally never attached via addonProducts().

        $this->assertNull($this->service->buildForOrder($order, $unattachedPlugin));
    }

    #[Test]
    #[Group('license-file-service')]
    public function build_signed_license_file_returns_null_when_main_scheme_is_missing(): void
    {
        LicenseScheme::query()->delete();

        $license = $this->createLicense();

        $this->assertNull($this->service->buildSignedLicenseFile($license));
    }

    #[Test]
    #[Group('license-file-service')]
    public function build_signed_license_file_returns_null_when_main_scheme_is_inactive(): void
    {
        LicenseScheme::query()->delete();
        LicenseScheme::unguarded(fn () => LicenseScheme::create(['id' => 1, 'scheme_query' => 'select 1', 'scheme_status' => 0]));

        $license = $this->createLicense();

        $this->assertNull($this->service->buildSignedLicenseFile($license));
    }

    #[Test]
    #[Group('license-file-service')]
    public function build_signed_license_file_returns_null_for_a_plugin_when_either_plugin_scheme_is_missing(): void
    {
        LicenseScheme::query()->delete();
        LicenseScheme::unguarded(fn () => LicenseScheme::create(['id' => 2, 'scheme_query' => 'select 1', 'scheme_status' => 1]));
        // scheme id 3 intentionally left missing.

        $product = $this->createProduct();
        $plugin = $this->createProduct();
        $license = $this->createLicense(['product_id' => $product->id]);
        $license->addonProducts()->attach($plugin->id);

        $this->assertNull($this->service->buildSignedLicenseFile($license, $plugin->id));
    }

    private function seedLicenseSchemes(): void
    {
        LicenseScheme::query()->delete();
        LicenseScheme::unguarded(function (): void {
            LicenseScheme::create(['id' => 1, 'scheme_query' => 'select 1 as product_schema', 'scheme_status' => 1]);
            LicenseScheme::create(['id' => 2, 'scheme_query' => 'select 1 as create_schema', 'scheme_status' => 1]);
            LicenseScheme::create(['id' => 3, 'scheme_query' => 'select 1 as update_schema', 'scheme_status' => 1]);
        });
    }
}
