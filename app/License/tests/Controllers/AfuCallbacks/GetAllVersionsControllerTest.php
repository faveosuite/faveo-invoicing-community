<?php

namespace App\License\tests\Controllers\AfuCallbacks;

use App\License\Controllers\AfuCallbacks\GetAllVersionsController;
use App\License\Helpers\LicenseValidator;
use App\License\tests\LicenseTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class GetAllVersionsControllerTest extends LicenseTestCase
{
    #[Test]
    #[Group('license-callbacks')]
    public function get_all_versions_returns_all_versions_without_sensitive_fields(): void
    {
        $product = $this->createProduct(['product_key' => 'AFUKEY2']);
        $this->createVersion($product, ['version' => '1.0.0', 'status' => 1]);
        $this->createVersion($product, ['version' => '2.0.0', 'status' => 1]);
        $validator = Mockery::mock(LicenseValidator::class);
        $validator->shouldReceive('resolveIp')->once()->andReturn('127.0.0.1');
        $validator->shouldReceive('isValidAfuRequest')->once()->andReturn(true);
        $validator->shouldReceive('isBanned')->once()->andReturn(false);
        $validator->shouldReceive('verifyAfuScriptSignature')->once()->andReturn(true);

        $response = (new GetAllVersionsController($validator))->getAllVersions($this->moduleRequest([
            'product_id' => $product->id,
            'product_key' => 'AFUKEY2',
            'user_local_path' => '/var/www/html',
            'script_signature' => 'signature',
        ], 'POST'));
        $json = $this->jsonContent($response);

        $this->assertSame('notification_operation_ok', $response->headers->get('notification_case'));
        $this->assertCount(2, $json['product_versions']);
        $this->assertArrayNotHasKey('product_key', $json);
    }
}
