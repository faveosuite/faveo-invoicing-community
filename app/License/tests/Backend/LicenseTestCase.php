<?php

namespace App\License\tests\Backend;

use App\License\Models\Installation;
use App\License\Models\InstallationLog;
use App\License\Models\License;
use App\License\Models\LicenseCallback;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\DBTestCase;

abstract class LicenseTestCase extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
    }

    protected function moduleRequest(array $data = [], string $method = 'GET', string $uri = '/license-test'): Request
    {
        $request = Request::create($uri, $method, $data, [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_REFERER' => 'https://example.test',
        ]);

        app()->instance('request', $request);

        return $request;
    }

    protected function jsonContent($response): array
    {
        return json_decode((string) $response->getContent(), associative: true) ?: [];
    }

    protected function assertSuccessfulJson($response, int $status = 200): array
    {
        $this->assertSame($status, $response->getStatusCode());

        $json = $this->jsonContent($response);
        $this->assertTrue($json['success']);

        return $json;
    }

    protected function assertErrorJson($response, int $status = 400): array
    {
        $this->assertSame($status, $response->getStatusCode());

        $json = $this->jsonContent($response);
        $this->assertFalse($json['success']);

        return $json;
    }

    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'user',
            'active' => 1,
        ], $attributes));
    }

    protected function createProduct(array $attributes = []): Product
    {
        $defaults = [
            'name' => 'License Product '.uniqid(),
            'description' => 'Product for license module tests',
            'product_sku' => 'LIC-SKU-'.uniqid(),
            'group' => 1,
            'require_domain' => 0,
            'product_key' => 'PRODKEY'.uniqid(),
        ];

        $product = new Product;
        foreach (array_merge($defaults, $attributes) as $column => $value) {
            if (Schema::hasColumn('products', $column)) {
                $product->{$column} = $value;
            }
        }

        $product->save();

        return $product;
    }

    protected function createLicense(array $attributes = []): License
    {
        $productId = $attributes['product_id'] ?? $this->createProduct()->id;
        $userId = array_key_exists('user_id', $attributes) ? $attributes['user_id'] : $this->createUser()->id;

        return License::create(array_merge([
            'product_id' => $productId,
            'user_id' => $userId,
            'license_code' => 'LIC'.strtoupper(str_replace('.', '', uniqid('', more_entropy: true))),
            'license_order_number' => random_int(100000, 999999),
            'license_ip' => '127.0.0.1',
            'license_domain' => 'example.test',
            'license_require_domain' => 0,
            'license_limit' => 2,
            'license_date' => now(),
            'license_expire_date' => now()->addMonth()->format('Y-m-d'),
            'license_expire_email_date' => now()->addMonth()->format('Y-m-d'),
            'license_updates_date' => now()->addMonth()->format('Y-m-d'),
            'license_updates_email_date' => now()->addMonth()->format('Y-m-d'),
            'license_support_date' => now()->addMonth()->format('Y-m-d'),
            'license_support_email_date' => now()->addMonth()->format('Y-m-d'),
            'license_comments' => 'Test license',
            'license_status' => 1,
        ], $attributes));
    }

    protected function createInstallation(array $attributes = []): Installation
    {
        $license = $attributes['license'] ?? $this->createLicense();
        unset($attributes['license']);

        return Installation::create(array_merge([
            'product_id' => $license->product_id,
            'user_id' => $license->user_id,
            'license_code' => $license->license_code,
            'installation_ip' => '127.0.0.1',
            'installation_domain' => 'example.test',
            'installation_path' => '/var/www/html',
            'installation_date' => now(),
            'installation_status' => 1,
            'installation_hash' => hash('sha256', 'installation'),
            'installation_disable_ip_verification' => 0,
            'version' => '1.0.0',
        ], $attributes));
    }

    protected function createLicenseCallback(array $attributes = []): LicenseCallback
    {
        $license = $attributes['license'] ?? $this->createLicense();
        unset($attributes['license']);

        return LicenseCallback::create(array_merge([
            'product_id' => $license->product_id,
            'user_id' => $license->user_id,
            'license_code' => $license->license_code,
            'callback_ip' => '127.0.0.1',
            'callback_domain' => 'https://example.test',
            'callback_date_time' => now(),
            'callback_status' => 1,
        ], $attributes));
    }

    protected function createInstallationLog(array $attributes = []): InstallationLog
    {
        $license = $attributes['license'] ?? $this->createLicense();
        unset($attributes['license']);

        return InstallationLog::create(array_merge([
            'license_code' => $license->license_code,
            'version_number' => '1.0.0',
            'installation_ip' => '127.0.0.1',
            'installation_domain' => 'example.test',
            'installation_last_active_date' => now(),
            'installation_status' => 1,
        ], $attributes));
    }

    protected function createVersion(?Product $product = null, array $attributes = []): ProductUpload
    {
        $product ??= $this->createProduct();

        return ProductUpload::create(array_merge([
            'product_id' => $product->id,
            'title' => 'Version '.uniqid(),
            'description' => 'Version for tests',
            'version' => '1.0.'.random_int(1, 999),
            'file' => 'release.zip',
            'version_expire_date' => now()->addMonth()->format('Y-m-d'),
            'version_install_count' => 0,
            'status' => 1,
        ], $attributes));
    }
}
