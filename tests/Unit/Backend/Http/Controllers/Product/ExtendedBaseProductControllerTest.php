<?php

namespace Tests\Unit\Backend\Http\Controllers\Product;

use App\Facades\Attach;
use App\Http\Controllers\Github\GithubApiController;
use App\Http\Controllers\Product\ExtendedBaseProductController;
use App\License\Services\ProductBundleStampingService;
use App\Model\License\LicensePermission;
use App\Model\License\LicenseType;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Exception;
use Illuminate\Http\Response;
use Mockery;
use Tests\DBTestCase;

class ExtendedBaseProductControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
    }

    private function controller(?ProductBundleStampingService $stampingService = null): ExtendedBaseProductController
    {
        return new ExtendedBaseProductController($stampingService ?? Mockery::mock(ProductBundleStampingService::class));
    }

    public function test_download_throws_when_github_product_has_no_resolvable_tag(): void
    {
        $product = Product::factory()->create(['github_owner' => 'faveo', 'github_repository' => 'helpdesk']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageIsOrContains(trans('message.file_not_exist'));

        $this->controller()->download($product);
    }

    public function test_github_download_is_fetched_server_side_and_stamped_with_the_order(): void
    {
        $product = Product::factory()->create(['github_owner' => 'faveo', 'github_repository' => 'helpdesk']);
        $order = Order::factory()->create(['product' => $product->id]);

        $github = Mockery::mock(GithubApiController::class);
        $github->shouldReceive('downloadZipball')->once()->with('faveo', 'helpdesk', 'v2.0.0')->andReturn('/tmp/zipball');
        $this->app->instance(GithubApiController::class, $github);

        $stampedResponse = new Response('stamped-bytes');
        $stampingService = Mockery::mock(ProductBundleStampingService::class);
        $stampingService->shouldReceive('githubDownloadResponse')
            ->once()
            ->withArgs(fn ($path, $p, $tag, $passedOrder): bool => $path === '/tmp/zipball' && $p->is($product) && $tag === 'v2.0.0' && $passedOrder->is($order))
            ->andReturn($stampedResponse);

        $this->assertSame($stampedResponse, $this->controller($stampingService)->download($product, tag: 'v2.0.0', order: $order));
    }

    public function test_download_throws_when_version_resolves_to_no_file(): void
    {
        $product = Product::factory()->create();
        $version = ProductUpload::factory()->create(['product_id' => $product->id, 'file' => '']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageIsOrContains(trans('message.file_not_exist'));

        $this->controller()->download($product, $version);
    }

    public function test_download_throws_when_resolved_file_is_not_found_in_storage(): void
    {
        $product = Product::factory()->create();
        $version = ProductUpload::factory()->create(['product_id' => $product->id, 'file' => 'release.zip']);

        Attach::shouldReceive('exists')->once()->with('products/release.zip')->andReturn(false);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageIsOrContains(trans('message.file_not_exist'));

        $this->controller()->download($product, $version);
    }

    public function test_download_delegates_to_stamping_service_and_returns_its_response(): void
    {
        $product = Product::factory()->create();
        $version = ProductUpload::factory()->create(['product_id' => $product->id, 'file' => 'release.zip']);

        Attach::shouldReceive('exists')->once()->with('products/release.zip')->andReturn(true);

        $stampedResponse = new Response('stamped-bytes');
        $stampingService = Mockery::mock(ProductBundleStampingService::class);
        $stampingService->shouldReceive('downloadResponseFor')
            ->once()
            ->withArgs(fn ($v, $p, $path, $order): bool => $v->is($version) && $p->is($product) && $path === 'products/release.zip' && $order === null)
            ->andReturn($stampedResponse);

        $response = $this->controller($stampingService)->download($product, $version);

        $this->assertSame($stampedResponse, $response);
    }

    public function test_download_passes_the_order_through_to_the_stamping_service(): void
    {
        $product = Product::factory()->create();
        $version = ProductUpload::factory()->create(['product_id' => $product->id, 'file' => 'release.zip']);
        $order = Order::factory()->create(['product' => $product->id]);

        Attach::shouldReceive('exists')->once()->andReturn(true);

        $stampingService = Mockery::mock(ProductBundleStampingService::class);
        $stampingService->shouldReceive('downloadResponseFor')
            ->once()
            ->withArgs(fn ($v, $p, $path, $passedOrder): bool => $passedOrder !== null && $passedOrder->is($order))
            ->andReturn(new Response('stamped-bytes'));

        $this->controller($stampingService)->download($product, $version, order: $order);
    }

    public function test_admin_download_returns_error_when_download_permission_is_denied(): void
    {
        $product = Product::factory()->create();
        // No license type / permissions attached, so downloadPermission resolves to 0.

        $response = $this->controller()->adminDownload($product->id);

        $data = json_decode((string) $response->getContent(), associative: true);
        $this->assertFalse($data['success']);
    }

    public function test_admin_download_delegates_to_download_when_permission_is_granted(): void
    {
        $permission = LicensePermission::create(['permissions' => 'Can be Downloaded']);
        $type = LicenseType::factory()->create();
        $type->permissions()->attach($permission->id);

        $product = Product::factory()->create(['type' => $type->id]);
        ProductUpload::factory()->create(['product_id' => $product->id, 'file' => 'release.zip', 'release_type' => 'official', 'is_private' => 0]);

        Attach::shouldReceive('exists')->once()->with('products/release.zip')->andReturn(true);

        $stampedResponse = new Response('stamped-bytes');
        $stampingService = Mockery::mock(ProductBundleStampingService::class);
        $stampingService->shouldReceive('downloadResponseFor')->once()->andReturn($stampedResponse);

        $response = $this->controller($stampingService)->adminDownload($product->id);

        $this->assertSame($stampedResponse, $response);
    }

    /**
     * Uploads $uploads in order (last = newest) and returns which file
     * adminDownload serves for $release.
     */
    private function fileServedFor(string $release, array $uploads): string
    {
        $permission = LicensePermission::create(['permissions' => 'Can be Downloaded']);
        $type = LicenseType::factory()->create();
        $type->permissions()->attach($permission->id);
        $product = Product::factory()->create(['type' => $type->id]);

        foreach ($uploads as $file => $releaseType) {
            ProductUpload::factory()->create(['product_id' => $product->id, 'file' => $file, 'release_type' => $releaseType, 'is_private' => 0]);
        }

        Attach::shouldReceive('exists')->andReturn(true);
        $served = null;
        $stampingService = Mockery::mock(ProductBundleStampingService::class);
        $stampingService->shouldReceive('downloadResponseFor')->once()
            ->andReturnUsing(function ($version) use (&$served): Response {
                $served = $version->file;

                return new Response('stamped');
            });

        $this->controller($stampingService)->adminDownload($product->id, $release);

        return (string) $served;
    }

    public function test_admin_download_beta_channel_gets_beta_build_only_even_when_rc_is_newer(): void
    {
        $this->assertSame('beta.zip', $this->fileServedFor('beta', ['beta.zip' => 'beta', 'rc.zip' => 'pre_release', 'official.zip' => 'official']));
    }

    public function test_admin_download_rc_channel_skips_beta_builds_and_accepts_rc_alias(): void
    {
        $this->assertSame('rc.zip', $this->fileServedFor('rc', ['rc.zip' => 'pre_release', 'beta.zip' => 'beta']));
    }

    public function test_admin_download_official_channel_never_gets_prerelease_builds(): void
    {
        $this->assertSame('official.zip', $this->fileServedFor('official', ['official.zip' => 'official', 'rc.zip' => 'pre_release', 'beta.zip' => 'beta']));
    }

    /**
     * Returns the GitHub tag adminDownload downloads for $release.
     */
    private function githubTagServedFor(string $release): string
    {
        $permission = LicensePermission::create(['permissions' => 'Can be Downloaded']);
        $type = LicenseType::factory()->create();
        $type->permissions()->attach($permission->id);
        $product = Product::factory()->create(['type' => $type->id, 'github_owner' => 'faveo', 'github_repository' => 'helpdesk']);

        $served = null;
        $github = Mockery::mock(GithubApiController::class);
        $github->shouldReceive('releases')->with('faveo', 'helpdesk')->andReturn([
            ['tag_name' => 'v9.4.4.0', 'prerelease' => false, 'draft' => true, 'created_at' => '2026-10-26T00:00:00Z'],
            ['tag_name' => 'v9.4.3.8.RC.3', 'prerelease' => true, 'draft' => false, 'created_at' => '2026-10-20T00:00:00Z'],
            ['tag_name' => 'v9.4.3.8', 'prerelease' => false, 'draft' => false, 'created_at' => '2026-10-25T12:00:00Z'],
            ['tag_name' => 'v9.4.3.9.RC.1', 'prerelease' => true, 'draft' => false, 'created_at' => '2026-10-25T00:00:00Z'],
            ['tag_name' => 'v9.4.3.7', 'prerelease' => false, 'draft' => false, 'created_at' => '2026-10-01T00:00:00Z'],
        ]);
        $github->shouldReceive('downloadZipball')->andReturnUsing(function ($owner, $repo, $tag) use (&$served): string {
            $served = $tag;

            return '/tmp/zipball';
        });
        $this->app->instance(GithubApiController::class, $github);

        $stampingService = Mockery::mock(ProductBundleStampingService::class);
        $stampingService->shouldReceive('githubDownloadResponse')->once()->andReturn(new Response('stamped'));

        $this->controller($stampingService)->adminDownload($product->id, $release);

        return (string) $served;
    }

    public function test_admin_download_github_official_channel_gets_newest_stable_tag(): void
    {
        $this->assertSame('v9.4.3.8', $this->githubTagServedFor('official'));
    }

    public function test_admin_download_github_rc_channel_gets_newest_prerelease_even_when_a_stable_tag_is_newer(): void
    {
        $this->assertSame('v9.4.3.9.RC.1', $this->githubTagServedFor('rc'));
    }
}
