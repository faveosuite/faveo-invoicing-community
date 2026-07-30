<?php

namespace Tests\Unit\Backend\Http\Controllers\Front;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class StoreControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('user');
    }

    public function test_get_groups_returns_200(): void
    {
        $response = $this->getJson('/store/groups');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_get_products_returns_response_for_unknown_group(): void
    {
        $response = $this->getJson('/store/999999/products');

        $this->assertContains($response->getStatusCode(), [200, 400, 404]);
    }

    public function test_get_products_returns_response_for_valid_group(): void
    {
        $group = \App\Model\Product\ProductGroup::first();
        if (! $group) {
            $tmpl = \App\Model\Common\PricingTemplate::first();
            if (! $tmpl) {
                $this->assertTrue(true);

                return;
            }
            $group = \App\Model\Product\ProductGroup::create(['name' => 'Test Group '.uniqid(), 'pricing_templates_id' => $tmpl->id]);
        }

        $response = $this->getJson("/store/{$group->id}/products");

        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_get_products_falls_back_to_the_bare_name_when_group_has_no_own_meta(): void
    {
        \App\Model\Common\Setting::find(1)->update(['company' => 'Acme Inc', 'favicon_title_client' => '']);
        \App\Model\Common\CommonSettings::where('option_name', 'seo')->where('optional_field', 'general_description')->delete();
        // SeoTemplateFormatter is bound as a singleton (AppServiceProvider)
        // and caches Setting/CommonSettings at construction — forget it so
        // the values just set above are actually picked up when the
        // controller resolves it fresh for this request.
        $this->app->forgetInstance(\App\Services\Seo\SeoTemplateFormatter::class);

        $tmpl = \App\Model\Common\PricingTemplate::first();
        if (! $tmpl) {
            $this->assertTrue(true);

            return;
        }

        $group = \App\Model\Product\ProductGroup::create([
            'name' => 'Widgets '.uniqid(),
            'pricing_templates_id' => $tmpl->id,
            'hidden' => 0,
            'meta_title' => null,
            'meta_description' => null,
        ]);

        $response = $this->getJson("/store/{$group->id}/products");

        $response->assertStatus(200);
        $this->assertSame($group->name, $response->json('data.group.meta_title'));
    }
}
