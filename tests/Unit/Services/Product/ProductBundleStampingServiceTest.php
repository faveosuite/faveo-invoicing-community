<?php

namespace Tests\Unit\Services\Product;

use App\Facades\Attach;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\Services\Product\ProductBundleStampingService;
use Mockery;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;
use ZipArchive;

class ProductBundleStampingServiceTest extends TestCase
{
    private ProductBundleStampingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ProductBundleStampingService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_throws_when_product_has_no_product_key(): void
    {
        $product = $this->makeProduct(['product_key' => null]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('has no product_key set');

        $this->service->stampToLocalFile('some/path.zip', $product, '1.0.0');
    }

    public function test_throws_when_canonical_file_is_not_found(): void
    {
        $product = $this->makeProduct();
        Attach::shouldReceive('exists')->once()->andReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Canonical build not found');

        $this->service->stampToLocalFile('missing/path.zip', $product, '1.0.0');
    }

    public function test_throws_when_config_file_path_contains_a_traversal_segment(): void
    {
        $product = $this->makeProduct(['config_file_path' => '../evil.ini']);
        $sourceZip = $this->makeZip(['storage/faveoconfig.ini' => '']);
        $this->stubAttachToServe($sourceZip);

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('unsafe zip-internal path');

            $this->service->stampToLocalFile('canonical/path.zip', $product, '1.0.0');
        } finally {
            @unlink($sourceZip);
        }
    }

    public function test_stamping_failure_cleans_up_the_local_temp_copy(): void
    {
        $product = $this->makeProduct();
        $garbageSource = tempnam(sys_get_temp_dir(), 'garbage_source_');
        file_put_contents($garbageSource, 'not a real zip');
        $this->stubAttachToServe($garbageSource);

        $before = glob(sys_get_temp_dir().'/product_bundle_*');

        try {
            $this->service->stampToLocalFile('some/path.zip', $product, '1.0.0');
            $this->fail('Expected RuntimeException was not thrown.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Unable to open canonical build for stamping', $e->getMessage());
        }

        $after = glob(sys_get_temp_dir().'/product_bundle_*');
        $this->assertSame($before, $after);

        @unlink($garbageSource);
    }

    public function test_stamps_config_and_leaves_other_zip_entries_untouched(): void
    {
        $product = $this->makeProduct(['id' => 42, 'product_key' => 'COREKEY123', 'name' => 'Core Product', 'config_file_path' => 'storage/faveoconfig.ini']);
        $sourceZip = $this->makeZip([
            'storage/faveoconfig.ini' => "APL_SALT=old\nPRODUCT_KEY=old\n",
            'app/Plugins/UnrelatedPlugin/config.php' => "<?php\nreturn ['product_id' => 1, 'product_key' => 'OLD', 'version' => '0.0.1'];\n",
        ]);
        $this->stubAttachToServe($sourceZip);

        $resultPath = $this->service->stampToLocalFile('canonical/path.zip', $product, '1.2.3');

        $config = $this->readZipEntry($resultPath, 'storage/faveoconfig.ini');
        $this->assertStringContainsString('PRODUCT_KEY=COREKEY123', $config);
        $this->assertStringContainsString('APP_NAME=Core Product', $config);
        $this->assertStringContainsString('APP_VERSION=1.2.3', $config);
        $this->assertStringContainsString('PRODUCT_ID=42', $config);
        $this->assertStringContainsString('LICENSE_MODE=DATABASE', $config);
        $this->assertMatchesRegularExpression('/^APL_SALT=[0-9a-f]{16}$/m', $config);

        // Nothing is ever removed from the zip — the unrelated plugin folder
        // ships exactly as it was in the canonical build.
        $unrelatedPlugin = $this->readZipEntry($resultPath, 'app/Plugins/UnrelatedPlugin/config.php');
        $this->assertStringContainsString("'product_key' => 'OLD'", $unrelatedPlugin);

        @unlink($resultPath);
        @unlink($sourceZip);
    }

    public function test_apl_salt_is_generated_once_and_reused_on_subsequent_stamps(): void
    {
        $product = $this->makeProduct(['product_key' => 'COREKEY456', 'config_file_path' => 'storage/faveoconfig.ini']);
        $sourceZip = $this->makeZip(['storage/faveoconfig.ini' => '']);
        $this->stubAttachToServe($sourceZip);

        $first = $this->service->stampToLocalFile('canonical/path.zip', $product, '1.0.0');
        $firstSalt = $product->apl_salt;

        $second = $this->service->stampToLocalFile('canonical/path.zip', $product, '1.0.1');
        $secondSalt = $product->apl_salt;

        $this->assertNotEmpty($firstSalt);
        $this->assertSame($firstSalt, $secondSalt);

        @unlink($first);
        @unlink($second);
        @unlink($sourceZip);
    }

    public function test_download_response_for_returns_a_downloadable_response(): void
    {
        $product = $this->makeProduct(['product_key' => 'DLKEY', 'name' => 'My Product']);
        $version = new ProductUpload(['file' => 'release-1.0.0.zip', 'version' => '1.0.0']);

        $sourceZip = $this->makeZip(['storage/faveoconfig.ini' => '']);
        $this->stubAttachToServe($sourceZip);

        $response = $this->service->downloadResponseFor($version, $product, 'products/release-1.0.0.zip');

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertStringContainsString('my-product-1.0.0.zip', (string) $response->headers->get('Content-Disposition'));

        // deleteFileAfterSend() only fires on a real HTTP send, which never
        // happens here — clean up the stamped output file ourselves.
        @unlink($response->getFile()->getPathname());
        @unlink($sourceZip);
    }

    /**
     * Builds a Product with update() stubbed to persist in-memory instead of
     * hitting a real DB — this repo's tests mock the model layer rather than
     * writing to a real database.
     */
    private function makeProduct(array $attributes = []): Product
    {
        $product = Mockery::mock(Product::class)->makePartial();
        $product->forceFill(array_merge([
            'id' => 1,
            'name' => 'Test Product',
            'product_key' => 'TESTKEY',
        ], $attributes));

        $product->shouldReceive('update')->andReturnUsing(function (array $attrs) use ($product) {
            $product->forceFill($attrs);

            return true;
        })->byDefault();

        return $product;
    }

    /**
     * @param  array<string, string>  $entries
     */
    private function makeZip(array $entries): string
    {
        $path = tempnam(sys_get_temp_dir(), 'source_build_');

        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);
        foreach ($entries as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();

        return $path;
    }

    private function readZipEntry(string $zipPath, string $entryName): ?string
    {
        $zip = new ZipArchive;
        $zip->open($zipPath);
        $content = $zip->getFromName($entryName);
        $zip->close();

        return $content === false ? null : $content;
    }

    private function stubAttachToServe(string $localZipPath): void
    {
        Attach::shouldReceive('exists')->andReturn(true);
        Attach::shouldReceive('readStream')->andReturnUsing(fn () => fopen($localZipPath, 'rb'));
    }
}
