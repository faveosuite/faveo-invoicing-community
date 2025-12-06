<?php

namespace Tests\Unit\Agent\Product;


use App\FileSystemSettings;
use App\Model\Common\StatusSetting;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Storage;
use Tests\DBTestCase;

class ProductControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        $this->getLoggedInUser('admin');
    }

    public function test_it_fetches_all_products_paginated()
    {
        Product::factory()->count(5)->create();

        $response = $this->getJson('/products');


        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        ['id', 'name', 'group', 'license_type', 'created_at']
                    ]
                ]
            ]);
    }

    public function test_products_listing_respects_sort_and_limit()
    {
        Product::factory()->count(20)->create();

        $response = $this->getJson('/products?limit=5&sort-field=id&sort-order=desc');

        $response->assertStatus(200)
            ->assertJsonPath('data.data', fn ($list) => count($list) === 5);
    }

    public function test_get_products_handles_empty_result_gracefully()
    {
        $response = $this->getJson('/products');

        $response->assertStatus(200)
            ->assertJsonPath('data.data', []);
    }

    public function test_get_product_success()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/product/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $product->id]);
    }

    public function test_get_product_throws_not_found()
    {
        $response = $this->getJson("/product/99999");

        $response->assertStatus(400)
            ->assertJsonFragment(['success' => false]);
    }

    public function test_create_product_successfully()
    {
        StatusSetting::updateOrCreate([
            'id' => 1
        ],['license_status' => 0]);

        $payload = [
            'name' => 'Test Product',
            'type' => 1,
            'description' => 'abc',
            'product_description' => 'xyz',
            'product_sku' => 'SKU001',
            'group' => 1,
            'show_agent' => true,
        ];

        $response = $this->putJson('/product', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.saved-successfully')]);

        $this->assertDatabaseHas('products', ['product_sku' => 'SKU001']);
    }

    public function test_product_create_fails_with_missing_fields()
    {
        $response = $this->putJson('/product', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type', 'description']);
    }

    public function test_product_create_fails_if_sku_not_unique()
    {
        Product::factory()->create(['product_sku' => 'SKU123']);

        $payload = [
            'name' => 'New Product',
            'type' => 1,
            'description' => 'x',
            'product_description' => 'y',
            'product_sku' => 'SKU123',
            'group' => 1,
            'show_agent' => true,
        ];

        $response = $this->putJson('/product', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_sku']);
    }

    public function test_update_product_success()
    {
        $product = Product::factory()->create();

        $payload = [
            'name'  => 'Updated',
            'type' => 1,
            'description' => 'abc',
            'product_description' => 'xyz',
            'product_sku' => $product->product_sku,
            'group' => 1,
            'show_agent' => true,
        ];

        $response = $this->patchJson("/product/{$product->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.updated-successfully')]);
    }

    public function test_update_product_fails_with_invalid_image()
    {
        $product = Product::factory()->create();

        $payload = [
            'name' => 'x',
            'type' => 1,
            'description' => 'x',
            'product_description' => 'x',
            'product_sku' => $product->product_sku,
            'group' => 1,
            'show_agent' => true,
            'image' => UploadedFile::fake()->create('file.zip'),
        ];

        $response = $this->patchJson("/product/{$product->id}", $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }
    public function test_delete_bulk_products_success()
    {
        $prodA = Product::factory()->create();
        $prodB = Product::factory()->create();

        StatusSetting::factory()->create(['license_status' => 0]);

        $response = $this->deleteJson('/products', [
            'product_ids' => [$prodA->id, $prodB->id]
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.deleted-successfully')]);

        $this->assertDatabaseMissing('products', ['id' => $prodA->id]);
    }

    public function test_delete_bulk_products_requires_ids()
    {
        $response = $this->deleteJson('/products', []);

        $response->assertStatus(400)
            ->assertJsonFragment(['success' => false]);
    }

    public function test_product_upload_create_success()
    {
        $product = Product::factory()->create();

        $payload = [
            'producttitle' => 'v1',
            'version' => '1.0',
            'description' => 'This is sample description',
            'release_type' => 'official',
            'filename' => 'file.zip',
            'dependencies' => ['php8'],
        ];

        StatusSetting::updateOrCreate([
            'id' => 1
        ],['license_status' => 0]);

        $response = $this->putJson("/product/upload/{$product->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.product_uploaded_successfully')]);

        $this->assertDatabaseHas('product_uploads', [
            'product_id' => $product->id,
            'version' => '1.0',
        ]);
    }

    public function test_product_upload_create_fails_if_missing_fields()
    {
        $product = Product::factory()->create();

        $response = $this->putJson("/product/upload/{$product->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['producttitle', 'version', 'filename']);
    }

    public function test_delete_bulk_product_upload_success()
    {
        Storage::fake('system');

        $upload = ProductUpload::factory()->create(['file' => 'abc.zip']);

        FileSystemSettings::updateOrCreate([
            'disk' => 'system',
            'local_file_storage_path' => storage_path()
        ]);

        Storage::disk('system')->put('abc.zip', 'dummy');

        $response = $this->deleteJson('/product/upload', [
            'product_upload_ids' => [$upload->id]
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('product_uploads', ['id' => $upload->id]);
    }


    public function test_edit_product_fails_if_product_not_found()
    {
        $response = $this->patchJson('/product/99999', [
            'name' => 'x',
            'type' => 1,
            'description' => 'x',
            'product_description' => 'x',
            'product_sku' => 'S1',
            'group' => 1,
            'show_agent' => true,
        ]);

        $response->assertStatus(400);
    }

    public function test_download_permissions_hidden_product_has_no_download_url()
    {
        $product = Product::factory()->create(['invoice_hidden' => 1]);

        $response = $this->getJson('/products?limit=5');

        $response->assertStatus(200)
            ->assertJsonMissing(['download_url']);
    }

    public function test_product_upload_version_updates_product_version()
    {
        $product = Product::factory()->create();

        $payload = [
            'producttitle' => 'v1',
            'version' => '2.0',
            'description' => 'This is sample description',
            'release_type' => 'official',
            'filename' => 'file.zip',
            'dependencies' => ['php8'],
        ];

        $this->putJson("/product/upload/{$product->id}", $payload);

        $this->assertDatabaseHas('product_uploads', [
            'product_id' => $product->id,
            'version' => '2.0',
        ]);
    }

    public function test_get_product_uploads_success()
    {
        $product = Product::factory()->create();
        ProductUpload::factory()->count(3)->create(['product_id' => $product->id]);

        $response = $this->getJson("/product/uploads/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.data');
    }

    public function test_get_product_upload_success()
    {
        $upload = ProductUpload::factory()->create();

        $response = $this->getJson("/product/upload/{$upload->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $upload->id]);
    }

    public function test_get_product_upload_not_found()
    {
        $response = $this->getJson('/product/upload/9999');

        $response->assertStatus(400);
    }

    public function test_delete_bulk_product_upload_requires_ids()
    {
        $response = $this->deleteJson('/product/upload', []);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'success' => false,
                'message' => __('message.select-a-row')
            ]);
    }
}
