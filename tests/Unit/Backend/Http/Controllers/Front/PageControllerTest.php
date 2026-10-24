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
        $response = $this->postJson('/page-settings', ['status' => 1]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.data_updated_successfully')]);

        $this->assertDatabaseHas('demo_pages', ['status' => 1]);
    }

    public function test_save_demo_page_validation_error(): void
    {
        $response = $this->postJson('/page-settings', ['status' => '']);

        $response->assertStatus(412);
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

    public function test_page_by_slug_falls_back_to_the_bare_name_when_page_has_no_own_meta(): void
    {
        \App\Model\Common\Setting::find(1)->update(['company' => 'Acme Inc', 'favicon_title_client' => '']);
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
        $this->assertSame('Refund Policy', $response->json('data.meta_title'));
        $this->assertSame('Learn more about Refund Policy at Acme Inc.', $response->json('data.meta_description'));
    }

    /** Sets the company + General SEO OG values, then makes the formatter singleton re-read them. */
    private function seoSettings(string $ogTitle, string $ogDescription): void
    {
        \App\Model\Common\Setting::find(1)->update(['company' => 'Acme Inc', 'favicon_title_client' => '']);
        \App\Model\Common\CommonSettings::where('option_name', 'seo')->whereIn('optional_field', ['general_description', 'general_og_title', 'general_og_description'])->delete();
        foreach (['general_og_title' => $ogTitle, 'general_og_description' => $ogDescription] as $field => $value) {
            \App\Model\Common\CommonSettings::create(['option_name' => 'seo', 'optional_field' => $field, 'option_value' => $value]);
        }
        $this->app->forgetInstance(\App\Services\Seo\SeoTemplateFormatter::class);
    }

    /** @param  array<string, string|null>  $meta */
    private function publishedPage(array $meta): \App\Model\Front\FrontendPage
    {
        return \App\Model\Front\FrontendPage::create([
            'name' => 'Refund Policy',
            'slug' => 'refund-policy-'.uniqid(),
            'content' => '<p>...</p>',
            'publish' => 1,
            'type' => 'custom',
        ] + $meta);
    }

    public function test_page_by_slug_resolves_shortcodes_in_the_pages_own_meta(): void
    {
        $this->seoSettings('General OG', 'General OG desc');
        $page = $this->publishedPage([
            'meta_title' => '{name} | {company}',
            'meta_description' => 'About {name}',
            'og_title' => 'OG {name}',
            'og_description' => 'OG about {company}',
        ]);

        $data = $this->getJson('/page-content/'.$page->slug)->assertOk()->json('data');

        $this->assertSame('Refund Policy | Acme Inc', $data['meta_title']);
        $this->assertSame('About Refund Policy', $data['meta_description']);
        $this->assertSame('OG Refund Policy', $data['og_title']);
        $this->assertSame('OG about Acme Inc', $data['og_description']);
    }

    public function test_page_by_slug_og_falls_back_to_general_og_settings(): void
    {
        $this->seoSettings('General OG {company}', 'General OG desc');
        $page = $this->publishedPage(['meta_title' => 'Own title', 'meta_description' => 'Own desc', 'og_title' => null, 'og_description' => '']);

        $data = $this->getJson('/page-content/'.$page->slug)->assertOk()->json('data');

        $this->assertSame('General OG Acme Inc', $data['og_title']);
        $this->assertSame('General OG desc', $data['og_description']);
    }

    public function test_page_by_slug_og_falls_back_to_resolved_meta_when_no_general_og(): void
    {
        $this->seoSettings('', '');
        $page = $this->publishedPage(['meta_title' => '{name} title', 'meta_description' => null, 'og_title' => null, 'og_description' => null]);

        $data = $this->getJson('/page-content/'.$page->slug)->assertOk()->json('data');

        $this->assertSame('Refund Policy title', $data['og_title']);
        $this->assertSame('Learn more about Refund Policy at Acme Inc.', $data['og_description']);
    }

    public function test_page_by_slug_hides_unpublished_pages(): void
    {
        $page = $this->publishedPage(['meta_title' => 'x']);
        $page->update(['publish' => 0]);

        $this->assertNull($this->getJson('/page-content/'.$page->slug)->assertOk()->json('data'));
    }

    // =========================================================================
    // getPageSettings — GET /page-settings
    // =========================================================================

    public function test_get_demo_status_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $this->withoutMiddleware();

        $response = $this->getJson('/page-settings');

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

    /** Product with one active plan of $days days priced $price (null = no price row). */
    private function pricedProduct(array $product, int $days, ?float $price, ?int $agents = null, ?string $description = 'One Time'): \App\Model\Product\Product
    {
        $product = \App\Model\Product\Product::create($product + ['name' => 'Price '.uniqid()]);
        $plan = \App\Model\Payment\Plan::create(['name' => 'P'.uniqid(), 'product' => $product->id, 'days' => $days, 'status' => 1]);
        if ($price !== null) {
            \App\Model\Payment\PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'USD', 'add_price' => $price, 'renew_price' => $price, 'no_of_agents' => $agents, 'price_description' => $description]);
        }

        return $product;
    }

    private function priceDescription(\App\Model\Product\Product $product, array $days = [365, 366]): string
    {
        return (new \App\Http\Controllers\Front\PageController)->getPriceDescription($product->id, $days);
    }

    public function test_get_price_description_for_an_active_product(): void
    {
        $this->assertSame('free', $this->priceDescription($this->pricedProduct(['status' => 1], 365, 0)));
        $this->assertSame('per month', $this->priceDescription($this->pricedProduct(['status' => 1], 366, 10)));
        $this->assertSame('per month for <strong>5 agent</strong>', $this->priceDescription($this->pricedProduct(['status' => 1], 365, 10, agents: 5)));
        $this->assertSame('per month', $this->priceDescription($this->pricedProduct(['status' => 1], 30, 10), [30, 31]));
    }

    public function test_get_price_description_for_an_inactive_product_uses_the_plans_own_text(): void
    {
        $this->assertSame('One Time', $this->priceDescription($this->pricedProduct(['status' => 0], 365, 10)));
        // No plan of the asked length: falls back to the first plan's text.
        $this->assertSame('Per Hour', $this->priceDescription($this->pricedProduct(['status' => 0], 30, 10, description: 'Per Hour')));
        $this->assertSame('', $this->priceDescription($this->pricedProduct(['status' => 0], 30, null)));
    }

    public function test_get_price_description_is_empty_for_contact_sales_or_no_matching_plan(): void
    {
        $this->assertSame('', $this->priceDescription($this->pricedProduct(['status' => 1, 'add_to_contact' => 1], 365, 10)));
        $this->assertSame('', $this->priceDescription($this->pricedProduct(['status' => 1], 30, 10)));
        $this->assertSame('', $this->priceDescription($this->pricedProduct(['status' => 1], 365, null)));
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
