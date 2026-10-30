<?php

namespace App\License\tests\Backend\Controllers\AfuCallbacks;

use App\Facades\Attach;
use App\License\Controllers\AfuCallbacks\DownloadFileController;
use App\License\Helpers\LicenseValidator;
use App\License\Models\VersionCallback;
use App\License\Services\ProductBundleStampingService;
use App\License\tests\Backend\LicenseTestCase;
use App\Model\Order\Order;
use Illuminate\Http\Response;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

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

        $stampingService = Mockery::mock(ProductBundleStampingService::class);

        $response = new DownloadFileController($validator, $stampingService)->downloadFile($this->moduleRequest([
            'product_id' => $product->id,
            'product_key' => 'AFUKEY3',
            'version_number' => 'missing',
            'user_local_path' => '/var/www/html',
            'script_signature' => 'signature',
        ], 'POST'));

        $this->assertSame('notification_version_not_found', $response->headers->get('notification_case'));
    }

    #[Test]
    #[Group('license-callbacks')]
    public function download_file_returns_archive_not_found_when_attach_cannot_locate_the_file(): void
    {
        $product = $this->createProduct(['product_key' => 'AFUKEY4']);
        $this->createVersion($product, ['version' => '1.0.0', 'file' => 'release.zip', 'status' => 1]);

        $validator = Mockery::mock(LicenseValidator::class);
        $validator->shouldReceive('resolveIp')->once()->andReturn('127.0.0.1');
        $validator->shouldReceive('isValidAfuRequest')->once()->andReturn(true);
        $validator->shouldReceive('isBanned')->once()->andReturn(false);
        $validator->shouldReceive('verifyAfuScriptSignature')->once()->andReturn(true);
        $validator->shouldReceive('isPastDate')->andReturnUsing(fn ($date): bool => new LicenseValidator()->isPastDate($date));

        Attach::shouldReceive('exists')->once()->with('products/release.zip')->andReturn(false);

        $stampingService = Mockery::mock(ProductBundleStampingService::class);
        $stampingService->shouldNotReceive('downloadResponseFor');

        $response = new DownloadFileController($validator, $stampingService)->downloadFile($this->moduleRequest([
            'product_id' => $product->id,
            'product_key' => 'AFUKEY4',
            'version_number' => '1.0.0',
            'user_local_path' => '/var/www/html',
            'script_signature' => 'signature',
        ], 'POST'));

        $this->assertSame('notification_install_archive_not_found', $response->headers->get('notification_case'));
    }

    #[Test]
    #[Group('license-callbacks')]
    public function download_file_returns_a_stamped_download_on_success_and_logs_the_callback(): void
    {
        $product = $this->createProduct(['product_key' => 'AFUKEY5']);
        $version = $this->createVersion($product, ['version' => '1.0.0', 'file' => 'release.zip', 'status' => 1, 'version_install_count' => 0]);

        $validator = Mockery::mock(LicenseValidator::class);
        $validator->shouldReceive('resolveIp')->once()->andReturn('127.0.0.1');
        $validator->shouldReceive('isValidAfuRequest')->once()->andReturn(true);
        $validator->shouldReceive('isBanned')->once()->andReturn(false);
        $validator->shouldReceive('verifyAfuScriptSignature')->once()->andReturn(true);
        $validator->shouldReceive('isPastDate')->andReturnUsing(fn ($date): bool => new LicenseValidator()->isPastDate($date));

        Attach::shouldReceive('exists')->once()->with('products/release.zip')->andReturn(true);

        $stampedResponse = new Response('stamped-zip-bytes');
        $stampingService = Mockery::mock(ProductBundleStampingService::class);
        $stampingService->shouldReceive('downloadResponseFor')
            ->once()
            ->withArgs(fn ($v, $p, $path): bool => $v->is($version) && $p->is($product) && $path === 'products/release.zip')
            ->andReturn($stampedResponse);

        $response = new DownloadFileController($validator, $stampingService)->downloadFile($this->moduleRequest([
            'product_id' => $product->id,
            'product_key' => 'AFUKEY5',
            'version_number' => '1.0.0',
            'user_local_path' => '/var/www/html',
            'script_signature' => 'signature',
        ], 'POST'));

        $this->assertSame('notification_operation_ok', $response->headers->get('notification_case'));
        $this->assertSame('application/octet-stream', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('release.zip', (string) $response->headers->get('Content-Disposition'));
        $this->assertNotEmpty($response->headers->get('notification_server_signature'));

        $this->assertSame(1, $version->fresh()->version_install_count);
        $this->assertSame(1, VersionCallback::where('product_id', $product->id)->where('version_id', $version->id)->count());
    }

    #[Test]
    #[Group('license-callbacks')]
    public function download_file_passes_the_licensed_order_to_stamping_only_for_an_active_license_of_this_product(): void
    {
        $product = $this->createProduct(['product_key' => 'AFUKEY7']);
        $this->createVersion($product, ['version' => '1.0.0', 'file' => 'release.zip', 'status' => 1]);
        $order = Order::factory()->create(['product' => $product->id]);
        $this->createLicense(['license_code' => 'GOODCODE', 'product_id' => $product->id, 'license_status' => 1, 'license_order_number' => $order->number]);
        $this->createLicense(['license_code' => 'OFFCODE', 'product_id' => $product->id, 'license_status' => 0, 'license_order_number' => $order->number]);

        $passedOrders = [];
        $stampingService = Mockery::mock(ProductBundleStampingService::class);
        $stampingService->shouldReceive('downloadResponseFor')
            ->andReturnUsing(function ($v, $p, $path, $passedOrder) use (&$passedOrders): Response {
                $passedOrders[] = $passedOrder?->id;

                return new Response('stamped');
            });

        foreach (['GOODCODE', 'OFFCODE', 'NOSUCHCODE', ''] as $code) {
            $validator = Mockery::mock(LicenseValidator::class);
            $validator->shouldReceive('resolveIp')->andReturn('127.0.0.1');
            $validator->shouldReceive('isValidAfuRequest', 'verifyAfuScriptSignature')->andReturn(true);
            $validator->shouldReceive('isBanned', 'isPastDate')->andReturn(false);
            Attach::shouldReceive('exists')->andReturn(true);

            new DownloadFileController($validator, $stampingService)->downloadFile($this->moduleRequest([
                'product_id' => $product->id,
                'product_key' => 'AFUKEY7',
                'version_number' => '1.0.0',
                'user_local_path' => '/var/www/html',
                'script_signature' => 'signature',
                'license_code' => $code,
            ], 'POST'));
        }

        $this->assertSame([$order->id, null, null, null], $passedOrders);
    }

    #[Test]
    #[Group('license-callbacks')]
    public function download_file_falls_back_to_archive_not_found_when_stamping_throws(): void
    {
        $product = $this->createProduct(['product_key' => 'AFUKEY6']);
        $this->createVersion($product, ['version' => '1.0.0', 'file' => 'release.zip', 'status' => 1]);

        $validator = Mockery::mock(LicenseValidator::class);
        $validator->shouldReceive('resolveIp')->once()->andReturn('127.0.0.1');
        $validator->shouldReceive('isValidAfuRequest')->once()->andReturn(true);
        $validator->shouldReceive('isBanned')->once()->andReturn(false);
        $validator->shouldReceive('verifyAfuScriptSignature')->once()->andReturn(true);
        $validator->shouldReceive('isPastDate')->andReturnUsing(fn ($date): bool => new LicenseValidator()->isPastDate($date));

        Attach::shouldReceive('exists')->once()->with('products/release.zip')->andReturn(true);

        $stampingService = Mockery::mock(ProductBundleStampingService::class);
        $stampingService->shouldReceive('downloadResponseFor')
            ->once()
            ->andThrow(new RuntimeException('Product #1 has no product_key set.'));

        $response = new DownloadFileController($validator, $stampingService)->downloadFile($this->moduleRequest([
            'product_id' => $product->id,
            'product_key' => 'AFUKEY6',
            'version_number' => '1.0.0',
            'user_local_path' => '/var/www/html',
            'script_signature' => 'signature',
        ], 'POST'));

        $this->assertSame('notification_install_archive_not_found', $response->headers->get('notification_case'));
    }
}
