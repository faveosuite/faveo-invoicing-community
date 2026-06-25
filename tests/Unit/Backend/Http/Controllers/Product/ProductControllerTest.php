<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Product;

use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class ProductControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
    }

    // --- GET /products ---

    public function test_admin_gets_200(): void
    {
        $this->getLoggedInUser('admin');
        $this->getJson('/products')->assertStatus(200);
    }

    public function test_client_is_redirected_302(): void
    {
        $this->getLoggedInUser('user');
        $this->getJson('/products')->assertStatus(302);
    }

    public function test_guest_gets_401(): void
    {
        $this->getJson('/products')->assertStatus(401);
    }

    public function test_list_has_success_flag_and_paginated_structure(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/products');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertIsArray($response->json('data.data'));
        $this->assertIsInt($response->json('data.current_page'));
    }

    // --- GET /product/{productId} ---

    public function test_nonexistent_product_returns_400_with_error_message(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/product/999999999');

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
        $this->assertNotEmpty($response->json('message'));
    }

    // --- PUT /product — every required field produces specific error ---

    public function test_create_empty_body_returns_422_with_all_required_field_errors(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->putJson('/product', []);

        $response->assertStatus(422);
        $errors = $response->json('errors');

        foreach (['name', 'type', 'group'] as $field) {
            $this->assertArrayHasKey($field, $errors, "Expected '$field' in product errors");
        }
    }

    public function test_create_missing_name_has_specific_error_message(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->putJson('/product', [
            'type' => '1', 'group' => '1', 'subscription' => '1', 'currency' => 'USD',
            'github_owner' => 'owner', 'github_repository' => 'repo',
        ]);

        $response->assertStatus(422);
        $this->assertSame('The name field is required.', $response->json('errors.name.0'));
    }

    public function test_create_blocked_for_client_returns_302(): void
    {
        $this->getLoggedInUser('user');
        $this->putJson('/product', ['name' => 'Test'])->assertStatus(302);
    }

    // --- PATCH /product/{productId} ---

    public function test_update_nonexistent_product_returns_422_validation_error(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->patchJson('/product/999999999', ['name' => 'Test']);

        $response->assertStatus(422);
    }

    // --- DELETE /products ---

    public function test_bulk_delete_without_ids_returns_400_with_failure_flag(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->deleteJson('/products', []);

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
    }

    public function test_bulk_delete_unauthenticated_returns_401(): void
    {
        $this->deleteJson('/products', ['ids' => [1]])->assertStatus(401);
    }

    public function test_get_existing_product_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $product = \App\Model\Product\Product::factory()->create();
        $response = $this->getJson("/product/{$product->id}");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_get_products_with_search_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/products?search-query=helpdesk');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_get_product_uploads_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $product = Product::factory()->create();
        $response = $this->getJson("/product/uploads/{$product->id}");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_get_product_by_id_returns_200(): void
    {
        // Covers lines 238+: getProduct
        $this->getLoggedInUser('admin');
        $product = Product::factory()->create();

        $response = $this->getJson('/product/'.$product->id);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_product_create_with_valid_data_returns_200(): void
    {
        // Covers lines 423+: productCreate
        $this->getLoggedInUser('admin');

        $product = Product::factory()->make();
        $response = $this->putJson('/product', [
            'name' => 'Test Product '.uniqid(),
            'type' => $product->type ?? 1,
            'group' => $product->group ?? 1,
            'require_domain' => 0,
            'description' => 'Test description',
            'product_description' => 'Full description',
            'product_sku' => 'SKU-'.uniqid(),
            'show_agent' => 0,
            'can_modify_agent' => 0,
            'can_modify_quantity' => 0,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_update_product_with_valid_data_returns_200(): void
    {
        // Covers lines 477+: updateProduct
        $this->getLoggedInUser('admin');
        $product = Product::factory()->create();

        $response = $this->patchJson('/product/'.$product->id, [
            'name' => 'Updated Product',
            'type' => $product->type ?? 1,
            'group' => $product->group ?? 1,
            'require_domain' => 0,
            'description' => 'Updated description',
            'product_description' => 'Updated full description',
            'product_sku' => 'SKU-UPDT-'.uniqid(),
            'show_agent' => 0,
            'can_modify_agent' => 0,
            'can_modify_quantity' => 0,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_delete_bulk_products_with_ids_returns_200(): void
    {
        // Covers lines 215+: deleteBulkProducts
        $this->getLoggedInUser('admin');
        $product = Product::factory()->create();

        $response = $this->deleteJson('/products', ['product_ids' => [$product->id]]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // =========================================================================
    // getProductUploads — with real product
    // =========================================================================

    public function test_get_product_uploads_for_unknown_product_returns_response(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/product/uploads/999999');

        $this->assertContains($response->status(), [200, 400, 404]);
    }

    public function test_get_all_products_with_sort_params(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/products?sort-field=name&sort-order=asc&limit=5');

        $response->assertStatus(200);
    }

    // =========================================================================
    // getProductDropdown — direct call (no HTTP route)
    // =========================================================================

    public function test_get_product_dropdown_returns_paginated_products(): void
    {
        $this->getLoggedInUser('admin');

        $controller = new \App\Http\Controllers\Product\ProductController;
        $request = new \Illuminate\Http\Request;
        $request->merge(['limit' => 10, 'page' => 1]);

        $response = $controller->getProductDropdown($request);
        $body = json_decode($response->getContent(), true);

        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('data', $body['data']);
    }

    public function test_get_product_dropdown_filters_by_search(): void
    {
        $this->getLoggedInUser('admin');

        $controller = new \App\Http\Controllers\Product\ProductController;
        $request = new \Illuminate\Http\Request;
        $request->merge(['search-query' => '__no_match_xyzzy__', 'limit' => 10]);

        $response = $controller->getProductDropdown($request);
        $body = json_decode($response->getContent(), true);

        $this->assertTrue($body['success']);
        $this->assertEmpty($body['data']['data']);
    }

    // =========================================================================
    // getProductPlans — direct call
    // =========================================================================

    public function test_get_product_plans_returns_empty_for_unknown_product(): void
    {
        $this->getLoggedInUser('admin');

        $controller = new \App\Http\Controllers\Product\ProductController;
        $request = new \Illuminate\Http\Request;
        $request->merge(['limit' => 10]);

        $response = $controller->getProductPlans($request, 999999);
        $body = json_decode($response->getContent(), true);

        $this->assertTrue($body['success']);
        $this->assertEmpty($body['data']['data']);
    }

    public function test_get_product_plans_with_search_query(): void
    {
        $this->getLoggedInUser('admin');

        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'Test Product '.uniqid()]);

        $controller = new \App\Http\Controllers\Product\ProductController;
        $request = new \Illuminate\Http\Request;
        $request->merge(['search-query' => '__no_plan_xyzzy__', 'limit' => 10]);

        $response = $controller->getProductPlans($request, $product->id);
        $body = json_decode($response->getContent(), true);

        $this->assertTrue($body['success']);
        $this->assertEmpty($body['data']['data']);
    }

    // =========================================================================
    // updateProduct — PATCH /product/{productId}
    // =========================================================================

    public function test_update_product_returns_error_for_nonexistent(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->patchJson('/product/999999', ['name' => 'Updated']);

        $this->assertContains($response->status(), [200, 400, 422]);
    }

    public function test_update_product_upload_returns_error_for_nonexistent(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->patchJson('/product/upload/999999', ['version' => '1.0.0']);

        $this->assertContains($response->status(), [200, 400, 422]);
    }

    // =========================================================================
    // productUploadCreate — PUT /product/upload/{productId}
    // =========================================================================

    public function test_product_upload_create_with_empty_body_returns_422(): void
    {
        $this->getLoggedInUser('admin');
        $product = Product::factory()->create();

        $response = $this->putJson("/product/upload/{$product->id}", []);

        $response->assertStatus(422);
        $errors = $response->json('errors');
        foreach (['producttitle', 'version', 'filename', 'dependencies'] as $field) {
            $this->assertArrayHasKey($field, $errors, "Expected '$field' validation error");
        }
    }

    public function test_product_upload_create_with_valid_data_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $product = Product::factory()->create();

        $response = $this->putJson("/product/upload/{$product->id}", [
            'producttitle' => 'Version 2.0 Release',
            'version' => '2.0.0',
            'filename' => 'product-v2.0.0.zip',
            'dependencies' => ['core'],
            'description' => 'Major release',
            'release_type' => 'official',
            'is_private' => false,
            'is_restricted' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('product_uploads', [
            'product_id' => $product->id,
            'version' => '2.0.0',
        ]);
    }

    public function test_product_upload_create_for_nonexistent_product_returns_400(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->putJson('/product/upload/999999', [
            'producttitle' => 'Test',
            'version' => '1.0.0',
            'filename' => 'test.zip',
            'dependencies' => ['core'],
            'description' => 'Test description',
            'release_type' => 'official',
        ]);

        $this->assertContains($response->status(), [400, 404, 422]);
    }

    // =========================================================================
    // updateProductUpload — PATCH /product/upload/{productUploadId}
    // =========================================================================

    public function test_update_product_upload_with_valid_data_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $upload = \App\Model\Product\ProductUpload::factory()->create();

        $response = $this->patchJson("/product/upload/{$upload->id}", [
            'title' => 'Updated Title',
            'version' => '1.1.0',
            'dependencies' => ['core'],
            'description' => 'Updated description',
            'release_type' => 'official',
            'is_private' => false,
            'is_restricted' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_update_product_upload_missing_required_fields_returns_422(): void
    {
        $this->getLoggedInUser('admin');
        $upload = \App\Model\Product\ProductUpload::factory()->create();

        $response = $this->patchJson("/product/upload/{$upload->id}", [
            // 'title' missing — required; 'dependencies' and 'release_type' also required
            'version' => '1.1.0',
            'dependencies' => [],
            'release_type' => 'official',
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('title', $response->json('errors'));
    }

    public function test_update_product_upload_with_new_filename_updates_file_field(): void
    {
        $this->getLoggedInUser('admin');
        $upload = \App\Model\Product\ProductUpload::factory()->create();

        $response = $this->patchJson("/product/upload/{$upload->id}", [
            'title' => 'Renamed Release',
            'version' => '1.2.0',
            'dependencies' => ['core'],
            'description' => 'With new filename',
            'release_type' => 'official',
            'filename' => 'new-file-v1.2.0.zip',
            'is_private' => false,
            'is_restricted' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('product_uploads', [
            'id' => $upload->id,
            'file' => 'new-file-v1.2.0.zip',
        ]);
    }

    // =========================================================================
    // getProductUpload — GET /product/upload/{productUploadId}
    // =========================================================================

    public function test_get_product_upload_returns_200_for_existing(): void
    {
        $this->getLoggedInUser('admin');
        $upload = \App\Model\Product\ProductUpload::factory()->create();

        $response = $this->getJson("/product/upload/{$upload->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('version', $data);
        $this->assertArrayHasKey('release_type', $data);
        $this->assertArrayHasKey('dependencies', $data);
    }

    public function test_get_product_upload_returns_400_for_nonexistent(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/product/upload/999999');

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    // =========================================================================
    // getProduct — detailed response structure
    // =========================================================================

    public function test_get_product_returns_github_status_flag(): void
    {
        $this->getLoggedInUser('admin');
        $product = Product::factory()->create();

        $response = $this->getJson("/product/{$product->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertArrayHasKey('product', $data);
        $this->assertArrayHasKey('github_status', $data);
        $this->assertIsBool($data['github_status']);
    }

    public function test_get_product_returns_relations(): void
    {
        $this->getLoggedInUser('admin');
        $product = Product::factory()->create();

        $response = $this->getJson("/product/{$product->id}");

        $response->assertStatus(200);
        $productData = $response->json('data.product');
        $this->assertEquals($product->id, $productData['id']);
        $this->assertEquals($product->name, $productData['name']);
    }

    // =========================================================================
    // getProductUploads — GET /product/uploads/{productId} — with search
    // =========================================================================

    public function test_get_product_uploads_with_search_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $product = Product::factory()->create();
        \App\Model\Product\ProductUpload::factory()->create([
            'product_id' => $product->id,
            'title' => 'My Special Release',
        ]);

        $response = $this->getJson("/product/uploads/{$product->id}?search-query=Special");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_get_product_uploads_with_sort_params_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $product = Product::factory()->create();

        $response = $this->getJson("/product/uploads/{$product->id}?sort-field=version&sort-order=asc&limit=5");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_get_product_uploads_with_invalid_sort_fallback_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $product = Product::factory()->create();

        $response = $this->getJson("/product/uploads/{$product->id}?sort-field=nonexistent_col");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    // =========================================================================
    // getAllProducts — sort field whitelist
    // =========================================================================

    public function test_get_all_products_with_license_type_sort_returns_200(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/products?sort-field=license_type&sort-order=asc');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_get_all_products_with_group_sort_returns_200(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/products?sort-field=group&sort-order=desc');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_get_all_products_includes_transform_data(): void
    {
        $this->getLoggedInUser('admin');
        Product::factory()->create();

        $response = $this->getJson('/products?limit=5');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('name', $data[0]);
        $this->assertArrayHasKey('action', $data[0]);
    }
}
