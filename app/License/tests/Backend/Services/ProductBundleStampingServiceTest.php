<?php

namespace App\License\tests\Backend\Services;

use App\Facades\Attach;
use App\License\Models\LicenseScheme;
use App\License\Services\Ed25519SigningService;
use App\License\Services\LicenseFileService;
use App\License\Services\ProductBundleStampingService;
use App\License\tests\Backend\LicenseTestCase;
use App\Model\Configure\ProductPluginGroup;
use App\Model\Order\Order;
use App\Model\Product\Product;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ProductBundleStampingServiceTest extends LicenseTestCase
{
    private ProductBundleStampingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ProductBundleStampingService(
            new Ed25519SigningService,
            new LicenseFileService(new Ed25519SigningService)
        );
    }

    /**
     * The base helper builds a Product in-memory via `new Product()` plus
     * selective property assignment, which never populates every column
     * attribute (e.g. `subscription`). getAplSalt()'s update() call then
     * diffs old-vs-new state for activity logging, and an unpopulated
     * `subscription` column falls through to the same-named relation
     * instead — comparing a Collection against an int and crashing. A real
     * controller always loads Product via a full `SELECT *`, so reload
     * here too rather than working around a test-only gap.
     */
    protected function createProduct(array $attributes = []): Product
    {
        return parent::createProduct($attributes)->fresh();
    }

    #[Test]
    #[Group('product-bundle-stamping')]
    public function throws_when_product_has_no_product_key(): void
    {
        $product = $this->createProduct(['product_key' => null]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('has no product_key set');

        $this->service->stampToLocalFile('some/path.zip', $product, '1.0.0');
    }

    #[Test]
    #[Group('product-bundle-stamping')]
    public function throws_when_canonical_file_is_not_found(): void
    {
        $product = $this->createProduct();
        Attach::shouldReceive('exists')->once()->andReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Canonical build not found');

        $this->service->stampToLocalFile('missing/path.zip', $product, '1.0.0');
    }

    #[Test]
    #[Group('product-bundle-stamping')]
    public function throws_when_config_file_path_contains_a_traversal_segment(): void
    {
        $product = $this->createProduct(['product_key' => 'TRAVERSALKEY', 'config_file_path' => '../evil.ini']);

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

    #[Test]
    #[Group('product-bundle-stamping')]
    public function stamping_failure_cleans_up_the_local_temp_copy(): void
    {
        $product = $this->createProduct();
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

    #[Test]
    #[Group('product-bundle-stamping')]
    public function stamps_core_product_faveoconfig_in_database_mode_and_leaves_other_entries_untouched(): void
    {
        $product = $this->createProduct(['product_key' => 'COREKEY123', 'config_file_path' => 'storage/faveoconfig.ini']);

        $sourceZip = $this->makeZip([
            'storage/faveoconfig.ini' => "APL_SALT=old\nPRODUCT_KEY=old\n",
            'app/Plugins/UnrelatedPlugin/config.php' => "<?php\nreturn ['product_id' => 1, 'product_key' => 'OLD', 'version' => '0.0.1'];\n",
        ]);
        $this->stubAttachToServe($sourceZip);

        $resultPath = $this->service->stampToLocalFile('canonical/path.zip', $product, '1.2.3');

        $config = $this->readZipEntry($resultPath, 'storage/faveoconfig.ini');
        $this->assertStringContainsString('PRODUCT_KEY=COREKEY123', $config);
        $this->assertStringContainsString('APP_VERSION=1.2.3', $config);
        $this->assertStringContainsString('PRODUCT_ID='.$product->id, $config);
        $this->assertStringContainsString('LICENSE_MODE=DATABASE', $config);
        $this->assertStringContainsString('ED25519_PUBLIC_KEY='.new Ed25519SigningService()->publicKey(), $config);
        $this->assertStringContainsString('SERVER_URL='.rtrim((string) config('app.url'), '/')."\n", $config);
        $this->assertMatchesRegularExpression('/^APL_SALT=[0-9a-f]{16}$/m', $config);

        // Nothing is ever removed from the zip — every plugin folder ships
        // regardless of what this product bundles, same as open source.
        $unrelatedPlugin = $this->readZipEntry($resultPath, 'app/Plugins/UnrelatedPlugin/config.php');
        $this->assertStringContainsString("'product_key' => 'OLD'", $unrelatedPlugin);

        $product->refresh();
        $this->assertNotEmpty($product->apl_salt);

        @unlink($resultPath);
        @unlink($sourceZip);
    }

    #[Test]
    #[Group('product-bundle-stamping')]
    public function apl_salt_is_generated_once_and_reused_on_subsequent_stamps(): void
    {
        $product = $this->createProduct(['product_key' => 'COREKEY456', 'config_file_path' => 'storage/faveoconfig.ini']);
        $sourceZip = $this->makeZip(['storage/faveoconfig.ini' => '']);
        $this->stubAttachToServe($sourceZip);

        $first = $this->service->stampToLocalFile('canonical/path.zip', $product, '1.0.0');
        $firstSalt = Product::find($product->id)->apl_salt;

        // Fresh instance, simulating a brand new request/download.
        $productAgain = Product::find($product->id);
        $second = $this->service->stampToLocalFile('canonical/path.zip', $productAgain, '1.0.1');
        $secondSalt = Product::find($product->id)->apl_salt;

        $this->assertNotEmpty($firstSalt);
        $this->assertSame($firstSalt, $secondSalt);

        @unlink($first);
        @unlink($second);
        @unlink($sourceZip);
    }

    #[Test]
    #[Group('product-bundle-stamping')]
    public function bundling_a_plugin_via_product_plugin_group_has_no_effect_on_the_zip(): void
    {
        $product = $this->createProduct(['product_key' => 'COREKEY789', 'config_file_path' => 'storage/faveoconfig.ini']);
        $plugin = $this->createProduct(['product_key' => 'PLUGKEY1', 'name' => 'Adhoc Approval', 'config_file_path' => 'config.php']);
        ProductPluginGroup::create(['product_id' => $product->id, 'plugin_id' => $plugin->id]);

        $sourceZip = $this->makeZip([
            'storage/faveoconfig.ini' => '',
            'app/Plugins/adhoc-approval/config.php' => "<?php\nreturn ['product_id' => 999, 'product_key' => 'OLDKEY', 'version' => '0.0.1'];\n",
        ]);
        $this->stubAttachToServe($sourceZip);

        $resultPath = $this->service->stampToLocalFile('canonical/path.zip', $product, '2.0.0');

        // product_plugin_group is used elsewhere (storefront bundling), but
        // the stamping service no longer reads it at all — the plugin's own
        // file ships exactly as it was in the canonical zip.
        $pluginConfig = $this->readZipEntry($resultPath, 'app/Plugins/adhoc-approval/config.php');
        $this->assertStringContainsString("'product_key' => 'OLDKEY'", $pluginConfig);
        $this->assertStringContainsString("'product_id' => 999", $pluginConfig);

        @unlink($resultPath);
        @unlink($sourceZip);
    }

    #[Test]
    #[Group('product-bundle-stamping')]
    public function no_plugin_folder_is_ever_stripped_regardless_of_bundling(): void
    {
        $product = $this->createProduct(['product_key' => 'COREKEYB']);
        $plugin = $this->createProduct(['product_key' => 'PLUGKEYB', 'name' => 'Bundled One']);
        ProductPluginGroup::create(['product_id' => $product->id, 'plugin_id' => $plugin->id]);

        $sourceZip = $this->makeZip([
            'storage/faveoconfig.ini' => '',
            'app/Plugins/bundled-one/config.php' => "<?php\nreturn ['product_id' => 1, 'product_key' => 'X', 'version' => '0.0.1'];\n",
            'app/Plugins/not-bundled/config.php' => "<?php\nreturn ['product_id' => 2, 'product_key' => 'Y', 'version' => '0.0.1'];\n",
            'app/Plugins/not-bundled/public/index.php' => '<?php',
        ]);
        $this->stubAttachToServe($sourceZip);

        $resultPath = $this->service->stampToLocalFile('canonical/path.zip', $product, '1.0.0');

        $this->assertTrue($this->zipHasEntry($resultPath, 'app/Plugins/bundled-one/config.php'));
        $this->assertTrue($this->zipHasEntry($resultPath, 'app/Plugins/not-bundled/config.php'));
        $this->assertTrue($this->zipHasEntry($resultPath, 'app/Plugins/not-bundled/public/index.php'));

        @unlink($resultPath);
        @unlink($sourceZip);
    }

    #[Test]
    #[Group('product-bundle-stamping')]
    public function a_plugin_with_no_config_file_path_ships_its_config_php_untouched(): void
    {
        $plugin = $this->createProduct(['product_key' => 'STANDALONEKEY']);

        $sourceZip = $this->makeZip([
            'config.php' => "<?php\nreturn ['product_id' => 5, 'product_key' => 'OLD', 'version' => '0.0.1'];\n",
            'public/index.php' => '<?php',
        ]);
        $this->stubAttachToServe($sourceZip);

        $resultPath = $this->service->stampToLocalFile('canonical/path.zip', $plugin, '3.0.0');

        $config = $this->readZipEntry($resultPath, 'config.php');
        $this->assertStringContainsString("'product_id' => 5", $config);
        $this->assertStringContainsString("'product_key' => 'OLD'", $config);
        $this->assertTrue($this->zipHasEntry($resultPath, 'public/index.php'));

        @unlink($resultPath);
        @unlink($sourceZip);
    }

    #[Test]
    #[Group('product-bundle-stamping')]
    public function a_product_with_a_custom_config_file_path_gets_its_identity_written_there(): void
    {
        $product = $this->createProduct(['product_key' => 'THIRDPARTYKEY', 'config_file_path' => 'settings/identity.ini']);

        $sourceZip = $this->makeZip([
            'config.php' => "<?php\nreturn ['product_id' => 5, 'product_key' => 'OLD', 'version' => '0.0.1'];\n",
        ]);
        $this->stubAttachToServe($sourceZip);

        $resultPath = $this->service->stampToLocalFile('canonical/path.zip', $product, '3.0.0');

        $identity = $this->readZipEntry($resultPath, 'settings/identity.ini');
        $this->assertStringContainsString('PRODUCT_KEY=THIRDPARTYKEY', $identity);
        $this->assertStringContainsString('APP_VERSION=3.0.0', $identity);

        // config.php isn't this product's declared path, so it's left alone.
        $config = $this->readZipEntry($resultPath, 'config.php');
        $this->assertStringContainsString("'product_key' => 'OLD'", $config);

        @unlink($resultPath);
        @unlink($sourceZip);
    }

    #[Test]
    #[Group('product-bundle-stamping')]
    public function file_mode_order_embeds_signed_license_and_public_key(): void
    {
        $product = $this->createProduct([
            'product_key' => 'FILEMODEKEY',
            'config_file_path' => 'storage/faveoconfig.ini',
            'license_file_path' => 'public/script/signature/license.json',
        ]);
        $this->seedLicenseSchemes();

        $orderNumber = random_int(10000000, 99999999);
        $order = Order::factory()->create([
            'product' => $product->id,
            'number' => $orderNumber,
            'license_mode' => 'File',
        ]);
        $license = $this->createLicense([
            'product_id' => $product->id,
            'license_order_number' => $orderNumber,
        ]);

        $sourceZip = $this->makeZip(['storage/faveoconfig.ini' => '']);
        $this->stubAttachToServe($sourceZip);

        $resultPath = $this->service->stampToLocalFile('canonical/path.zip', $product, '1.0.0', $order);

        $config = $this->readZipEntry($resultPath, 'storage/faveoconfig.ini');
        $this->assertStringContainsString('LICENSE_MODE=FILE', $config);
        $this->assertStringContainsString('ED25519_PUBLIC_KEY=', $config);

        $licenseJson = $this->readZipEntry($resultPath, 'public/script/signature/license.json');
        $this->assertNotNull($licenseJson);
        $decoded = json_decode($licenseJson, true);
        $payload = json_decode($decoded['license'], true);
        $this->assertSame($license->license_code, $payload['license_code']);
        $this->assertNotEmpty($decoded['signature']);

        @unlink($resultPath);
        @unlink($sourceZip);
    }

    #[Test]
    #[Group('product-bundle-stamping')]
    public function file_mode_order_does_not_embed_a_license_into_any_plugin_folder(): void
    {
        $product = $this->createProduct([
            'product_key' => 'FILEMODECORE',
            'config_file_path' => 'storage/faveoconfig.ini',
            'license_file_path' => 'public/script/signature/license.json',
        ]);
        $plugin = $this->createProduct([
            'product_key' => 'FILEMODEPLUGIN',
            'name' => 'My Plugin',
            'license_file_path' => 'public/script/signature/license.json',
        ]);
        ProductPluginGroup::create(['product_id' => $product->id, 'plugin_id' => $plugin->id]);
        $this->seedLicenseSchemes();

        $orderNumber = random_int(10000000, 99999999);
        $order = Order::factory()->create(['product' => $product->id, 'number' => $orderNumber, 'license_mode' => 'File']);
        $license = $this->createLicense(['product_id' => $product->id, 'license_order_number' => $orderNumber]);
        $license->addonProducts()->attach($plugin->id);

        $sourceZip = $this->makeZip([
            'storage/faveoconfig.ini' => '',
            'app/Plugins/my-plugin/config.php' => "<?php\nreturn ['product_id' => 1, 'product_key' => 'X', 'version' => '0.0.1'];\n",
        ]);
        $this->stubAttachToServe($sourceZip);

        $resultPath = $this->service->stampToLocalFile('canonical/path.zip', $product, '1.0.0', $order);

        // Only the target product's own declared path gets the license —
        // plugin folders are never touched, licensed or not.
        $this->assertTrue($this->zipHasEntry($resultPath, 'public/script/signature/license.json'));
        $this->assertFalse($this->zipHasEntry($resultPath, 'app/Plugins/my-plugin/public/script/signature/license.json'));

        @unlink($resultPath);
        @unlink($sourceZip);
    }

    #[Test]
    #[Group('product-bundle-stamping')]
    public function download_response_for_returns_a_downloadable_response(): void
    {
        $product = $this->createProduct(['product_key' => 'DLKEY', 'name' => 'My Product']);
        $version = $this->createVersion($product, ['file' => 'release-1.0.0.zip', 'version' => '1.0.0']);

        $sourceZip = $this->makeZip(['storage/faveoconfig.ini' => '']);
        $this->stubAttachToServe($sourceZip);

        $response = $this->service->downloadResponseFor($version, $product, 'products/release-1.0.0.zip');

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertStringContainsString('my-product-1.0.0.zip', (string) $response->headers->get('Content-Disposition'));

        // Signed exactly as faveo/license-client checks it: Ed25519 over "product_id|version|sha256" of the stamped file.
        $sha256 = hash_file('sha256', $response->getFile()->getPathname());
        $this->assertSame($sha256, $response->headers->get('archive_sha256'));
        $this->assertTrue(sodium_crypto_sign_verify_detached(
            base64_decode((string) $response->headers->get('archive_signature')),
            $product->id.'|1.0.0|'.$sha256,
            base64_decode(new Ed25519SigningService()->publicKey()),
        ));

        // deleteFileAfterSend() only fires on a real HTTP send, which never
        // happens here — clean up the stamped output file ourselves.
        @unlink($response->getFile()->getPathname());
        @unlink($sourceZip);
    }

    #[Test]
    #[Group('product-bundle-stamping')]
    public function github_zipball_is_stamped_inside_its_top_level_folder(): void
    {
        $product = $this->createProduct(['product_key' => 'GHKEY', 'name' => 'Git Product', 'config_file_path' => 'storage/faveoconfig.ini']);
        $zipball = $this->makeZip(['acme-app-abc1234/' => '', 'acme-app-abc1234/storage/faveoconfig.ini' => '', 'acme-app-abc1234/index.php' => '<?php']);

        $response = $this->service->githubDownloadResponse($zipball, $product, 'v1.2.0');

        $this->assertStringContainsString('git-product-v1.2.0.zip', (string) $response->headers->get('Content-Disposition'));
        $config = (string) $this->readZipEntry($zipball, 'acme-app-abc1234/storage/faveoconfig.ini');
        $this->assertStringContainsString('PRODUCT_KEY=GHKEY', $config);
        $this->assertStringContainsString('APP_VERSION=v1.2.0', $config);
        $this->assertFalse($this->zipHasEntry($zipball, 'storage/faveoconfig.ini')); // not at the zip root
        $this->assertSame('<?php', $this->readZipEntry($zipball, 'acme-app-abc1234/index.php'));

        @unlink($zipball);
    }

    #[Test]
    #[Group('product-bundle-stamping')]
    public function github_zipball_without_config_paths_is_served_untouched_even_with_no_product_key(): void
    {
        $product = $this->createProduct(['product_key' => null, 'config_file_path' => null, 'license_file_path' => null]);
        $zipball = $this->makeZip(['acme-app-abc1234/index.php' => '<?php']);
        $before = md5_file($zipball);

        $response = $this->service->githubDownloadResponse($zipball, $product, 'v1.0.0');

        $this->assertSame($zipball, $response->getFile()->getPathname());
        $this->assertSame($before, md5_file($zipball));

        @unlink($zipball);
    }

    #[Test]
    #[Group('product-bundle-stamping')]
    public function github_stamping_failure_deletes_the_fetched_zipball(): void
    {
        $product = $this->createProduct(['product_key' => null, 'config_file_path' => 'storage/faveoconfig.ini']);
        $zipball = $this->makeZip(['acme-app-abc1234/index.php' => '<?php']);

        try {
            $this->service->githubDownloadResponse($zipball, $product, 'v1.0.0');
            $this->fail('Expected a RuntimeException for the missing product_key.');
        } catch (RuntimeException) {
            $this->assertFileDoesNotExist($zipball);
        }
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

    private function zipHasEntry(string $zipPath, string $entryName): bool
    {
        $zip = new ZipArchive;
        $zip->open($zipPath);
        $has = $zip->locateName($entryName) !== false;
        $zip->close();

        return $has;
    }

    private function stubAttachToServe(string $localZipPath): void
    {
        Attach::shouldReceive('exists')->andReturn(true);
        Attach::shouldReceive('readStream')->andReturnUsing(fn () => fopen($localZipPath, 'rb'));
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
