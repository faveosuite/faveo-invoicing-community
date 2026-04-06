<?php

namespace App\License\tests\Backend\Controllers\AfuCallbacks;

use App\License\Controllers\AfuCallbacks\DownloadFileController;
use App\License\Helpers\LicenseValidator;
use App\License\tests\Backend\LicenseTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class DownloadFileControllerTest extends LicenseTestCase
{
    #[Test]
    #[Group('license-callbacks')]
    public function download_file_returns_version_not_found_for_missing_requested_version(): void
    {
        $product = $this->createProduct(['product_key' => 'AFUKEY3']);
        $validator = Mockery::mock(LicenseValidator::class);
        $validator->shouldReceive('resolveIp')->once()->andReturn('127.0.0.1');
        $validator->shouldReceive('isValidAfuRequest')->once()->andReturn(true);
        $validator->shouldReceive('isBanned')->once()->andReturn(false);

        $response = (new DownloadFileController($validator))->downloadFile($this->moduleRequest([
            'product_id' => $product->id,
            'product_key' => 'AFUKEY3',
            'version_number' => 'missing',
            'user_local_path' => '/var/www/html',
            'script_signature' => 'signature',
        ], 'POST'));

        $this->assertSame('notification_version_not_found', $response->headers->get('notification_case'));
    }
}
