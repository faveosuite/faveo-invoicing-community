<?php

namespace Tests\Unit\Backend\Http\Controllers\Front;

use App\Model\Front\FrontendPage;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class PageControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    public function test_get_all_pages_success(): void
    {
        FrontendPage::factory()->count(5)->create();

        $response = $this->getJson('/pages');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data.data');
    }

    public function test_get_all_pages_empty(): void
    {
        $response = $this->getJson('/pages');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data.data');
    }

    // Test cases for DELETE /pages
    public function test_delete_bulk_pages_success(): void
    {
        $pages = FrontendPage::factory()->count(3)->create();
        $pageIds = $pages->pluck('id')->toArray();

        $response = $this->deleteJson('/pages', ['page_ids' => $pageIds]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.deleted-successfully')]);

        foreach ($pageIds as $id) {
            $this->assertDatabaseMissing('frontend_pages', ['id' => $id]);
        }
    }

    public function test_delete_bulk_pages_validation_error(): void
    {
        $response = $this->deleteJson('/pages', ['page_ids' => []]);

        $response->assertStatus(400)->assertJsonFragment([
            'message' => __('message.select-a-row'),
        ]);
    }

    public function test_get_page_success(): void
    {
        $page = FrontendPage::factory()->create();

        $response = $this->getJson('/page/'.$page->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['type' => $page->type]);
    }

    public function test_get_page_not_found(): void
    {
        $response = $this->getJson('/page/9999');

        $response->assertStatus(400);
    }

    public function test_update_page_success(): void
    {
        $page = FrontendPage::factory()->create();
        $updateData = [
            'type' => 'New type',
            'content' => 'New Content',
        ];

        $response = $this->putJson('/page/'.$page->id, $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.updated-successfully')]);

        $this->assertDatabaseHas('frontend_pages', array_merge(['id' => $page->id], $updateData));
    }

    public function test_update_page_not_found(): void
    {
        $response = $this->putJson('/page/9999');

        $response->assertStatus(400);
    }

    public function test_save_demo_page_success(): void
    {
        $response = $this->postJson('/save/demo', ['status' => 1]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.data_updated_successfully')]);

        $this->assertDatabaseHas('demo_pages', ['status' => 1]);
    }

    public function test_save_demo_page_validation_error(): void
    {
        $response = $this->postJson('/save/demo', ['status' => '']);

        $response->assertStatus(422);
    }

    // =========================================================================
    // pageBySlug — returns page content or null
    // =========================================================================

    public function test_page_by_slug_returns_200_with_null_for_unknown_slug(): void
    {
        $this->getLoggedInUser('admin');
        $this->withoutMiddleware();

        $response = $this->getJson('/page-content/nonexistent-slug-xyzzy');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
        // No page found → data is null
        $this->assertNull($response->json('data'));
    }

    public function test_page_by_slug_returns_page_when_exists(): void
    {
        $this->getLoggedInUser('admin');
        $this->withoutMiddleware();

        $page = \App\Model\Front\FrontendPage::create([
            'name' => 'Test Page',
            'slug' => 'test-page-'.uniqid(),
            'content' => '<p>Hello</p>',
            'publish' => 1,
            'type' => 'custom',
        ]);

        $response = $this->getJson('/page-content/'.$page->slug);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertNotNull($data);
        $this->assertSame($page->slug, $data['slug']);
        $this->assertSame('Test Page', $data['name']);
    }

    public function test_page_by_slug_applies_the_pages_title_format_when_page_has_no_own_meta(): void
    {
        \App\Model\Common\Setting::find(1)->update(['company' => 'Acme Inc', 'favicon_title_client' => '']);
        \App\Model\Common\CommonSettings::where('option_name', 'seo')->where('optional_field', 'pages_title_format')->delete();
        \App\Model\Common\CommonSettings::where('option_name', 'seo')->where('optional_field', 'pages_description_format')->delete();
        \App\Model\Common\CommonSettings::where('option_name', 'seo')->where('optional_field', 'general_description')->delete();
        // SeoTemplateFormatter is bound as a singleton (AppServiceProvider)
        // and caches Setting/CommonSettings at construction — forget it so
        // the values just set above are actually picked up when the
        // controller resolves it fresh for this request.
        $this->app->forgetInstance(\App\Services\Seo\SeoTemplateFormatter::class);

        $page = \App\Model\Front\FrontendPage::create([
            'name' => 'Refund Policy',
            'slug' => 'refund-policy-'.uniqid(),
            'content' => '<p>...</p>',
            'publish' => 1,
            'type' => 'custom',
            'meta_title' => null,
            'meta_description' => null,
        ]);

        $response = $this->getJson('/page-content/'.$page->slug);

        $response->assertStatus(200);
        $this->assertSame('Refund Policy | Acme Inc', $response->json('data.meta_title'));
        $this->assertSame('Learn more about Refund Policy at Acme Inc.', $response->json('data.meta_description'));
    }

    // =========================================================================
    // getDemoStatus — GET /demo
    // =========================================================================

    public function test_get_demo_status_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $this->withoutMiddleware();

        $response = $this->getJson('/demo');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    // =========================================================================
    // getPriceDescription — direct call, unknown product → empty string
    // =========================================================================

    public function test_get_price_description_returns_empty_for_unknown_product(): void
    {
        $this->getLoggedInUser('admin');
        $this->withoutMiddleware();

        $controller = new \App\Http\Controllers\Front\PageController;
        $result = $controller->getPriceDescription(999999);

        $this->assertSame('', $result);
    }

    public function test_get_price_description_for_known_product_returns_string(): void
    {
        $this->getLoggedInUser('admin');
        $this->withoutMiddleware();

        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'Test Product '.uniqid()]);

        $controller = new \App\Http\Controllers\Front\PageController;
        $result = $controller->getPriceDescription($product->id);

        $this->assertIsString($result);
    }

    // =========================================================================
    // transform / keyArray / valueArray — pure logic, no DB
    // =========================================================================

    public function test_transform_returns_empty_string_for_empty_transform_array(): void
    {
        $controller = new \App\Http\Controllers\Front\PageController;
        $result = $controller->transform('invoice', 'test data', []);

        $this->assertSame('', $result);
    }

    public function test_key_array_returns_array_of_keys(): void
    {
        $controller = new \App\Http\Controllers\Front\PageController;
        $result = $controller->keyArray(['key1' => 'val1', 'key2' => 'val2']);

        $this->assertIsArray($result);
        $this->assertEquals(['key1', 'key2'], $result);
    }

    public function test_value_array_returns_array_of_values(): void
    {
        $controller = new \App\Http\Controllers\Front\PageController;
        $result = $controller->valueArray(['key1' => 'val1', 'key2' => 'val2']);

        $this->assertIsArray($result);
        $this->assertEquals(['val1', 'val2'], $result);
    }
}
