<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Services\Seo;

use App\Model\Common\CommonSettings;
use App\Model\Common\SeoDefaultPage;
use App\Model\Common\Setting;
use App\Model\Front\FrontendPage;
use App\Model\Product\ProductGroup;
use App\Services\Seo\SeoMetaService;
use App\Services\Seo\SeoTemplateFormatter;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class SeoMetaServiceTest extends DBTestCase
{
    use DatabaseTransactions;

    private SeoMetaService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::updateOrCreate(['id' => 1], [
            'company' => 'Acme Inc',
            'favicon_title_client' => 'Acme Client Title',
            'logo' => null,
        ]);
        CommonSettings::where('option_name', 'seo')->delete();
        SeoDefaultPage::query()->delete();

        $this->service = app(SeoMetaService::class);
    }

    // --- Default pages: login / forgot_password / reset_password ---

    public function test_resolve_empty_path_treats_it_as_login(): void
    {
        $result = $this->service->resolve('');

        $this->assertSame('Login', $result['title']);
        $this->assertSame('index, follow', $result['robots']);
        $this->assertSame(url('/'), $result['canonical']);
    }

    public function test_resolve_login_uses_its_own_seeded_meta_when_present(): void
    {
        SeoDefaultPage::factory()->create([
            'page_key' => 'login',
            'meta_title' => 'Sign In - {company}',
            'meta_description' => 'Access your account',
        ]);

        $result = $this->service->resolve('login');

        $this->assertSame('Sign In - Acme Inc', $result['title']);
        $this->assertSame('Access your account', $result['description']);
        $this->assertSame('index, follow', $result['robots']);
    }

    public function test_resolve_forgot_password(): void
    {
        $result = $this->service->resolve('password/reset');

        $this->assertSame('Forgot Password', $result['title']);
        $this->assertSame('index, follow', $result['robots']);
    }

    public function test_resolve_reset_password_is_noindex_because_the_url_carries_a_secret_token(): void
    {
        $result = $this->service->resolve('password/reset/some-secret-token');

        $this->assertSame('Reset Password', $result['title']);
        $this->assertSame('noindex, nofollow', $result['robots']);
        $this->assertSame(url('/password/reset/some-secret-token'), $result['canonical']);
    }

    public function test_resolve_cart_uses_its_own_seeded_meta_when_present(): void
    {
        SeoDefaultPage::factory()->create([
            'page_key' => 'cart',
            'meta_title' => 'Your Cart - {company}',
            'meta_description' => 'Review your items',
        ]);

        $result = $this->service->resolve('cart');

        $this->assertSame('Your Cart - Acme Inc', $result['title']);
        $this->assertSame('Review your items', $result['description']);
    }

    public function test_resolve_cart_is_noindex_since_contents_are_per_user(): void
    {
        $result = $this->service->resolve('cart');

        $this->assertSame('Cart', $result['title']);
        $this->assertSame('noindex, nofollow', $result['robots']);
    }

    // --- Pages module ---

    public function test_resolve_pages_slug_for_a_published_page_uses_its_own_meta(): void
    {
        FrontendPage::factory()->create([
            'slug' => 'about-us',
            'name' => 'About Us',
            'publish' => 1,
            'meta_title' => 'About {company}',
            'meta_description' => 'Learn about us',
        ]);

        $result = $this->service->resolve('pages/about-us');

        $this->assertSame('About Acme Inc', $result['title']);
        $this->assertSame('Learn about us', $result['description']);
        $this->assertSame('index, follow', $result['robots']);
    }

    public function test_resolve_pages_slug_falls_back_to_the_general_seo_title_when_no_own_meta(): void
    {
        FrontendPage::factory()->create([
            'slug' => 'faq',
            'name' => 'FAQ',
            'publish' => 1,
            'meta_title' => null,
            'meta_description' => null,
        ]);

        $result = $this->service->resolve('pages/faq');

        $this->assertSame('Acme Client Title', $result['title']);
    }

    public function test_resolve_pages_slug_falls_back_to_the_bare_name_when_no_own_meta_and_no_general_title(): void
    {
        Setting::find(1)->update(['favicon_title_client' => '']);
        // SeoTemplateFormatter is bound as a singleton (AppServiceProvider) and
        // caches Setting at construction — forget it so the update above is
        // actually picked up on re-resolve.
        $this->app->forgetInstance(SeoTemplateFormatter::class);
        $this->service = app(SeoMetaService::class);
        FrontendPage::factory()->create([
            'slug' => 'faq',
            'name' => 'FAQ',
            'publish' => 1,
            'meta_title' => null,
            'meta_description' => null,
        ]);

        $result = $this->service->resolve('pages/faq');

        $this->assertSame('FAQ', $result['title']);
    }

    public function test_resolve_pages_slug_falls_back_to_the_generic_fallback_when_page_not_found(): void
    {
        // Unknown "pages" prefix isn't in fallback()'s hardcoded map, and with
        // favicon_title_client blank, title falls all the way through to the
        // company name.
        Setting::find(1)->update(['favicon_title_client' => '']);

        $result = $this->service->resolve('pages/does-not-exist');

        $this->assertSame('Acme Inc', $result['title']);
        $this->assertSame('noindex, nofollow', $result['robots']);
    }

    public function test_resolve_pages_slug_falls_back_when_page_exists_but_is_unpublished(): void
    {
        FrontendPage::factory()->create(['slug' => 'draft-page', 'publish' => 0]);

        $result = $this->service->resolve('pages/draft-page');

        $this->assertSame('noindex, nofollow', $result['robots']);
    }

    // --- /contact-us ---

    public function test_resolve_contact_us_uses_the_contactus_type_page_when_it_exists(): void
    {
        FrontendPage::factory()->create([
            'type' => 'contactus',
            'name' => 'Get In Touch',
            'publish' => 1,
            'meta_title' => 'Contact {company}',
            'meta_description' => null,
        ]);

        $result = $this->service->resolve('contact-us');

        $this->assertSame('Contact Acme Inc', $result['title']);
        // Always indexable, unlike the generic fallback() path.
        $this->assertSame('index, follow', $result['robots']);
    }

    public function test_resolve_contact_us_stays_indexable_even_without_a_contactus_page(): void
    {
        Setting::find(1)->update(['favicon_title_client' => '']);
        $this->app->forgetInstance(SeoTemplateFormatter::class);
        $this->service = app(SeoMetaService::class);

        $result = $this->service->resolve('contact-us');

        $this->assertSame('Contact Us', $result['title']);
        $this->assertSame('index, follow', $result['robots']);
    }

    // --- /store and /store/{id} ---

    public function test_resolve_store_index_falls_back_to_the_bare_name_when_no_general_title(): void
    {
        Setting::find(1)->update(['favicon_title_client' => '']);
        $this->app->forgetInstance(SeoTemplateFormatter::class);
        $this->service = app(SeoMetaService::class);

        $result = $this->service->resolve('store');

        $this->assertSame('Store', $result['title']);
        $this->assertSame('index, follow', $result['robots']);
    }

    public function test_resolve_store_group_uses_its_own_meta_when_present(): void
    {
        $group = ProductGroup::factory()->create([
            'name' => 'Widgets',
            'hidden' => 0,
            'meta_title' => 'Buy {name}',
            'meta_description' => 'Great widgets',
        ]);

        $result = $this->service->resolve('store/'.$group->id);

        $this->assertSame('Buy Widgets', $result['title']);
        $this->assertSame('Great widgets', $result['description']);
    }

    public function test_resolve_store_group_description_falls_back_to_tagline_then_headline(): void
    {
        $group = ProductGroup::factory()->create([
            'name' => 'Gadgets',
            'hidden' => 0,
            'meta_description' => null,
            'tagline' => 'The best gadgets',
            'headline' => 'Gadget headline',
        ]);

        $result = $this->service->resolve('store/'.$group->id);

        $this->assertSame('The best gadgets', $result['description']);
    }

    public function test_resolve_store_group_falls_back_to_the_generic_fallback_when_group_not_found(): void
    {
        $result = $this->service->resolve('store/999999');

        $this->assertSame('noindex, nofollow', $result['robots']);
    }

    public function test_resolve_store_group_falls_back_when_group_is_hidden(): void
    {
        $group = ProductGroup::factory()->create(['hidden' => 1]);

        $result = $this->service->resolve('store/'.$group->id);

        $this->assertSame('noindex, nofollow', $result['robots']);
    }

    // --- fallback(): authenticated/transactional/unknown routes ---

    public function test_resolve_unknown_path_falls_back_to_the_company_title(): void
    {
        Setting::find(1)->update(['favicon_title_client' => '']);

        $result = $this->service->resolve('some/unknown/path');

        $this->assertSame('Acme Inc', $result['title']);
        $this->assertSame('noindex, nofollow', $result['robots']);
    }

    public function test_resolve_client_dashboard_uses_the_hardcoded_map_title_by_default(): void
    {
        Setting::find(1)->update(['favicon_title_client' => '']);

        $result = $this->service->resolve('client-dashboard');

        $this->assertSame('Dashboard', $result['title']);
        $this->assertSame('noindex, nofollow', $result['robots']);
    }

    public function test_resolve_client_dashboard_lets_an_admin_opt_into_a_per_route_title_via_shortcode(): void
    {
        Setting::find(1)->update(['favicon_title_client' => '{name} | Faveo Invoicing']);

        $result = $this->service->resolve('client-dashboard');

        $this->assertSame('Dashboard | Faveo Invoicing', $result['title']);
    }

    public function test_resolve_admin_path_is_noindex_defense_in_depth(): void
    {
        $result = $this->service->resolve('admin');

        $this->assertSame('noindex, nofollow', $result['robots']);
    }

    public function test_resolve_general_description_is_used_when_configured(): void
    {
        CommonSettings::upsert(
            [['option_name' => 'seo', 'optional_field' => 'general_description', 'option_value' => 'General site description', 'status' => '']],
            ['option_name', 'optional_field'],
            ['option_value']
        );
        // SeoTemplateFormatter is bound as a singleton (AppServiceProvider) and
        // caches CommonSettings at construction — forget it so the new value
        // set above is actually picked up on re-resolve.
        $this->app->forgetInstance(SeoTemplateFormatter::class);
        $this->service = app(SeoMetaService::class);

        $result = $this->service->resolve('checkout');

        $this->assertSame('General site description', $result['description']);
    }

    public function test_every_resolved_shape_has_all_expected_keys(): void
    {
        $result = $this->service->resolve('login');

        $this->assertArrayHasKeys(
            ['title', 'description', 'robots', 'canonical', 'image', 'og_title', 'og_description'],
            $result
        );
    }

    public function test_resolve_client_routes_matches_resolve_for_every_key(): void
    {
        $routes = $this->service->resolveClientRoutes();

        $this->assertSame($this->service->resolve('login')['title'], $routes['login']['title']);
        $this->assertSame($this->service->resolve('contact-us')['title'], $routes['contact-us']['title']);
        $this->assertSame($this->service->resolve('client-dashboard')['title'], $routes['client-dashboard']['title']);
        $this->assertSame($this->service->resolve('checkout')['description'], $routes['checkout']['description']);
        $this->assertSame($this->service->resolve('cart')['title'], $routes['cart']['title']);
        $this->assertArrayHasKey('pay', $routes);
        $this->assertArrayNotHasKey('store', $routes);
    }
}
