<?php

namespace App\License\tests\Backend\Controllers\AfuCallbacks;

use App\License\Controllers\AfuCallbacks\GetVersionsController;
use App\License\Helpers\LicenseValidator;
use App\License\Models\VersionCallback;
use App\License\Models\VersionNotification;
use App\License\tests\Backend\LicenseTestCase;
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

        $response = new GetVersionsController($validator)->getVersions($this->moduleRequest([
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

    #[Test]
    #[Group('license-callbacks')]
    public function get_versions_replaces_product_and_version_shortcodes_in_notification_text(): void
    {
        $product = $this->createProduct(['product_key' => 'AFUKEY2', 'name' => 'Faveo Helpdesk']);
        $version = $this->createVersion($product, [
            'version' => '3.0.0',
            'status' => 1,
            'version_expire_date' => now()->subDay()->format('Y-m-d'),
        ]);
        // notificationResponse() reads VersionNotification::first(), so update the existing
        // row (seeded by LicenseModuleSeeder) rather than creating a second, ignored one.
        (VersionNotification::first() ?? VersionNotification::create())->update([
            'notification_version_expired' => '%PRODUCT_TITLE% version %VERSION_NUMBER% expired on %VERSION_EXPIRE_DATE%',
        ]);

        $validator = Mockery::mock(LicenseValidator::class);
        $validator->shouldReceive('resolveIp')->once()->andReturn('127.0.0.1');
        $validator->shouldReceive('isValidAfuRequest')->once()->andReturn(true);
        $validator->shouldReceive('isBanned')->once()->andReturn(false);
        $validator->shouldReceive('verifyAfuScriptSignature')->once()->andReturn(true);
        $validator->shouldReceive('verifyDateTime')->once()->andReturn(true);

        $response = new GetVersionsController($validator)->getVersions($this->moduleRequest([
            'product_id' => $product->id,
            'product_key' => 'AFUKEY2',
            'version_number' => '3.0.0',
            'user_local_path' => '/var/www/html',
            'script_signature' => 'signature',
        ], 'POST'));

        $this->assertSame('notification_version_expired', $response->headers->get('notification_case'));
        $this->assertSame('Faveo Helpdesk version 3.0.0 expired on '.$version->refresh()->version_expire_date, $response->headers->get('notification_text'));
    }
}
