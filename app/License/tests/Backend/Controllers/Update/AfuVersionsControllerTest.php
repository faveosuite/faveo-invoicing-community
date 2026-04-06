<?php

namespace App\License\tests\Backend\Controllers\Update;

use App\License\Controllers\Update\AfuVersionsController;
use App\License\Models\VersionCallback;
use App\License\Requests\VersionRequest;
use App\License\tests\Backend\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class AfuVersionsControllerTest extends LicenseTestCase
{
    private AfuVersionsController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->moduleRequest();
        $this->controller = new AfuVersionsController();
    }

    #[Test]
    #[Group('license-admin')]
    public function version_add_returns_error_payload_for_invalid_required_values(): void
    {
        $request = VersionRequest::create('/api/admin/versions/add', 'POST', [
            'product_id' => 'bad',
            'version_number' => '',
            'version_status' => 9,
        ]);

        $response = json_decode($this->controller->versionAdd($request), true);

        $this->assertSame(0, $response['action_success']);
        $this->assertSame(1, $response['error_detected']);
        $this->assertStringContainsString('Version could not be added', $response['page_message']);
    }

    #[Test]
    #[Group('license-admin')]
    public function version_update_returns_not_found_for_missing_version(): void
    {
        $response = $this->controller->versionUpdate($this->moduleRequest([
            'version_id' => 99999999,
        ], 'POST'));

        $this->assertErrorJson($response, 404);
    }

    #[Test]
    #[Group('license-admin')]
    public function delete_version_removes_version_and_callbacks(): void
    {
        $product = $this->createProduct();
        $version = $this->createVersion($product);
        VersionCallback::create([
            'product_id' => $product->id,
            'version_id' => $version->id,
            'callback_type' => 1,
            'callback_ip' => '127.0.0.1',
            'callback_path' => '/var/www/html',
            'callback_date_time' => now(),
            'callback_status' => 1,
        ]);

        $response = $this->controller->deleteVersion($this->moduleRequest([
            'version_id' => $version->id,
        ], 'POST'));
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame(1, $json['data']);
        $this->assertDatabaseMissing('product_uploads', ['id' => $version->id]);
        $this->assertSame(0, VersionCallback::where('version_id', $version->id)->count());
    }

    #[Test]
    #[Group('license-admin')]
    public function delete_file_directory_removes_requested_file(): void
    {
        $directory = storage_path('app/license-test-'.uniqid());
        mkdir($directory, 0755, true);
        file_put_contents($directory.'/delete-me.txt', 'test');

        $removed = $this->controller->deleteFileDirectory($directory, ['delete-me.txt']);

        $this->assertSame(1, $removed);
        $this->assertFileDoesNotExist($directory.'/delete-me.txt');
        rmdir($directory);
    }
}
