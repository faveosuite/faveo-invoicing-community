<?php

namespace App\License\tests\Controllers\AfuCallbacks;

use App\License\Controllers\AfuCallbacks\GetVersionsController;
use App\License\Helpers\LicenseValidator;
use App\License\Models\VersionCallback;
use App\License\tests\LicenseTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class GetVersionsControllerTest extends LicenseTestCase
{
    #[Test]
    #[Group('license-callbacks')]
    public function get_versions_returns_latest_active_version_without_sensitive_product_key(): void
    {
        $product = $this->createProduct(['product_key' => 'AFUKEY1']);
        $this->createVersion($product, ['version' => '1.0.0', 'status' => 1]);
        $latest = $this->createVersion($product, ['version' => '2.0.0', 'status' => 1]);
        $validator = Mockery::mock(LicenseValidator::class);
        $validator->shouldReceive('resolveIp')->once()->andReturn('127.0.0.1');
        $validator->shouldReceive('isValidAfuRequest')->once()->andReturn(true);
        $validator->shouldReceive('isBanned')->once()->andReturn(false);
        $validator->shouldReceive('verifyAfuScriptSignature')->once()->andReturn(true);
        $validator->shouldReceive('verifyDateTime')->once()->andReturn(true);

        $response = (new GetVersionsController($validator))->getVersions($this->moduleRequest([
            'product_id' => $product->id,
            'product_key' => 'AFUKEY1',
            'user_local_path' => '/var/www/html',
            'script_signature' => 'signature',
        ], 'POST'));
        $json = $this->jsonContent($response);

        $this->assertSame('notification_operation_ok', $response->headers->get('notification_case'));
        $this->assertSame($latest->version, $json['version']);
        $this->assertArrayNotHasKey('product_key', $json);
        $this->assertSame(1, VersionCallback::where('version_id', $latest->id)->count());
    }
}
