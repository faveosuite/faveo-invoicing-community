<?php

namespace Tests\Unit\Backend\Http\Controllers\License;

use App\License\Models\License;
use App\License\Services\Ed25519SigningService;
use App\Model\Order\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Storage;
use Tests\DBTestCase;

class LocalizedLicenseControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    public function test_download_file_returns_401_when_not_authenticated(): void
    {
        auth()->logout();
        $response = $this->get('/downloadLicenseFile?orderNo=ORD001');
        $response->assertStatus(401);
    }

    public function test_download_file_returns_file_when_authenticated(): void
    {
        // Covers lines 35-39: authenticated user downloads license file
        $orderNo = 'TEST_DL_'.uniqid();
        $filePath = storage_path('app/public/faveo-license-{'.$orderNo.'}.txt');
        file_put_contents($filePath, 'license-content');

        $response = $this->get('/downloadLicenseFile?orderNo='.$orderNo);

        @unlink($filePath);

        $response->assertStatus(200);
    }

    public function test_download_file_admin_returns_file(): void
    {
        // Covers lines 50-54: admin downloads license file
        $fileName = 'faveo-license-{TEST_ADMIN}.txt';
        $filePath = storage_path('app/public/'.$fileName);
        file_put_contents($filePath, 'admin-license-content');

        $response = $this->get('/LocalizedLicense/downloadLicense/'.$fileName);

        @unlink($filePath);

        $response->assertStatus(200);
    }

    public function test_download_private_returns_file(): void
    {
        // Covers lines 58-62: download private key
        $orderNo = 'TEST_PRIV_'.uniqid();
        $filePath = storage_path('app/public/privateKey-'.$orderNo.'.txt');
        file_put_contents($filePath, 'private-key-content');

        $response = $this->get('/downloadPrivate/'.$orderNo);

        @unlink($filePath);

        $response->assertStatus(200);
    }

    public function test_download_private_key_admin_returns_file(): void
    {
        // Covers lines 68-74: admin downloads private key
        $orderNo = 'TESTADM';
        $fileName = 'faveo-license-{'.$orderNo.'}.txt';
        $keyPath = storage_path('app/public/privateKey-'.$orderNo.'.txt');
        file_put_contents($keyPath, 'private-key-admin');

        $response = $this->get('/LocalizedLicense/downloadPrivateKey/'.$fileName);

        @unlink($keyPath);

        $response->assertStatus(200);
    }

    public function test_temp_order_link_generates_signed_url_when_authenticated(): void
    {
        // Covers line 173: tempOrderLink returns signed URL when user is authenticated
        $controller = new \App\Http\Controllers\License\LocalizedLicenseController(
            $this->app->make(\App\License\Services\InstallationService::class),
            $this->app->make(Ed25519SigningService::class)
        );

        $url = $controller->tempOrderLink('ORD001', 1);

        $this->assertIsString($url);
        $this->assertStringContainsString('downloadLicenseFile', $url);
    }

    public function test_temp_order_link_aborts_401_when_user_id_is_zero(): void
    {
        $controller = new \App\Http\Controllers\License\LocalizedLicenseController(
            $this->app->make(\App\License\Services\InstallationService::class),
            $this->app->make(Ed25519SigningService::class)
        );

        try {
            $controller->tempOrderLink('ORD001', 0);
            $this->fail('Expected HttpException was not thrown');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(401, $e->getStatusCode());
        }
    }

    public function test_it_sets_license_mode_to_file(): void
    {
        Storage::fake('public');

        $order = Order::factory()->withRelations([
            'number' => '90000123',
            'license_mode' => 'Database',
        ])->create();

        $response = $this->postJson('/switch-license-mode', [
            'orderNo' => '90000123',
            'choose' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('message.status_change_successfully'),
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'license_mode' => 'File',
        ]);

        // No matching License row yet, so there's nothing to build a file from.
        Storage::disk('public')->assertMissing('faveo-license-{90000123}.txt');
    }

    public function test_switching_to_file_mode_generates_a_signed_downloadable_license_file(): void
    {
        Storage::fake('public');

        $order = Order::factory()->withRelations([
            'number' => '90000124',
            'license_mode' => 'Database',
            'is_downloadable' => 0,
        ])->create();

        License::create([
            'product_id' => $order->product,
            'user_id' => $order->client,
            'license_code' => 'LIC-90000124',
            'license_order_number' => $order->number,
            'license_expire_date' => '2027-06-01',
            'license_status' => 1,
        ]);

        $response = $this->postJson('/switch-license-mode', [
            'orderNo' => '90000124',
            'choose' => true,
        ]);

        $response->assertStatus(200);

        Storage::disk('public')->assertExists('faveo-license-{90000124}.txt');

        $file = json_decode(Storage::disk('public')->get('faveo-license-{90000124}.txt'), true);
        $this->assertArrayHasKey('license', $file);
        $this->assertArrayHasKey('signature', $file);

        $this->assertTrue(
            resolve(Ed25519SigningService::class)->verify($file['license'], $file['signature'])
        );

        $fields = json_decode($file['license'], true);
        $this->assertSame('LIC-90000124', $fields['license_code']);
        $this->assertSame('2027-06-01', $fields['license_expire_date']);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'is_downloadable' => 1,
        ]);
    }

    public function test_files_api_returns_paginated_list(): void
    {
        Storage::fake('public');

        // Create some license files
        Storage::disk('public')->put('faveo-license-{ORD001}.txt', 'data1');
        Storage::disk('public')->put('faveo-license-{ORD002}.txt', 'data2');
        Storage::disk('public')->put('other-file.txt', 'not a license');

        $response = $this->getJson('/localized-license/files');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(2, $data['data']);
    }

    public function test_files_api_filters_by_search_query(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('faveo-license-{ORD001}.txt', 'data');
        Storage::disk('public')->put('faveo-license-{ORD002}.txt', 'data');

        $response = $this->getJson('/localized-license/files?search-query=ORD001');

        $response->assertStatus(200);
        $items = $response->json('data.data');
        $this->assertCount(1, $items);
        $this->assertStringContainsString('ORD001', $items[0]['file_name']);
    }

    public function test_delete_file_api_removes_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('faveo-license-{ORD999}.txt', 'data');

        $response = $this->deleteJson('/localized-license/files', [
            'file_name' => 'faveo-license-{ORD999}.txt',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertFalse(Storage::disk('public')->exists('faveo-license-{ORD999}.txt'));
    }

    public function test_delete_file_api_rejects_invalid_filename(): void
    {
        Storage::fake('public');

        $response = $this->deleteJson('/localized-license/files', [
            'file_name' => 'some-other-file.txt',
        ]);

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_files_api_sorts_results(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('faveo-license-{ZZZ}.txt', 'data');
        Storage::disk('public')->put('faveo-license-{AAA}.txt', 'data');

        $response = $this->getJson('/localized-license/files?sort-field=file_name&sort-order=asc');

        $response->assertStatus(200);
        $items = $response->json('data.data');
        $this->assertCount(2, $items);
        $this->assertStringContainsString('AAA', $items[0]['file_name']);
    }

    public function test_it_sets_license_mode_to_database_and_deletes_files(): void
    {
        Storage::fake('public');

        $order = Order::factory()->withRelations([
            'number' => '90000999',
            'license_mode' => 'File',
        ])->create();

        Storage::disk('public')->put('faveo-license-{90000999}.txt', 'dummy');

        $this->assertTrue(Storage::disk('public')->exists('faveo-license-{90000999}.txt'));

        $response = $this->postJson('/switch-license-mode', [
            'orderNo' => '90000999',
            'choose' => false,
        ]);

        $response->assertStatus(200);

        $this->assertFalse(Storage::disk('public')->exists('faveo-license-{90000999}.txt'));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'license_mode' => 'Database',
        ]);
    }
}
