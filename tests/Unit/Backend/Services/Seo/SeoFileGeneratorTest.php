<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Services\Seo;

use App\Model\Common\CommonSettings;
use App\Model\Common\SeoDefaultPage;
use App\Model\Common\Setting;
use App\Model\Front\FrontendPage;
use App\Model\Product\ProductGroup;
use App\Services\Seo\SeoFileGenerator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Mockery;
use Tests\DBTestCase;

class SeoFileGeneratorTest extends DBTestCase
{
    use DatabaseTransactions;

    private SeoFileGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::updateOrCreate(['id' => 1], [
            'company' => 'Acme Inc',
            'favicon_title_client' => '',
            'logo' => null,
        ]);
        CommonSettings::where('option_name', 'seo')->delete();
        SeoDefaultPage::query()->delete();
        FrontendPage::query()->delete();

        $this->generator = new SeoFileGenerator();
    }

    // --- generateSitemap() ---

    public function test_generate_sitemap_includes_the_home_login_and_password_reset_urls(): void
    {
        File::shouldReceive('put')
            ->once()
            ->with(public_path('sitemap.xml'), Mockery::on(function (string $xml): bool {
                return str_contains($xml, '<loc>'.htmlspecialchars(url('/')).'</loc>')
                    && str_contains($xml, '<loc>'.htmlspecialchars(url('/login')).'</loc>')
                    && str_contains($xml, '<loc>'.htmlspecialchars(url('/password/reset')).'</loc>');
            }))
            ->andReturn(true);

        $this->generator->generateSitemap();
    }

    public function test_generate_sitemap_includes_a_published_page_with_its_lastmod(): void
    {
        $page = FrontendPage::factory()->create(['slug' => 'about-us', 'publish' => 1]);

        File::shouldReceive('put')
            ->once()
            ->with(public_path('sitemap.xml'), Mockery::on(function (string $xml) use ($page): bool {
                return str_contains($xml, '<loc>'.htmlspecialchars(url('/pages/about-us')).'</loc>')
                    && str_contains($xml, '<lastmod>'.$page->updated_at->toAtomString().'</lastmod>');
            }))
            ->andReturn(true);

        $this->generator->generateSitemap();
    }

    public function test_generate_sitemap_excludes_unpublished_pages(): void
    {
        FrontendPage::factory()->create(['slug' => 'draft-page', 'publish' => 0]);

        File::shouldReceive('put')
            ->once()
            ->with(public_path('sitemap.xml'), Mockery::on(function (string $xml): bool {
                return ! str_contains($xml, 'draft-page');
            }))
            ->andReturn(true);

        $this->generator->generateSitemap();
    }

    public function test_generate_sitemap_includes_a_visible_product_group(): void
    {
        $group = ProductGroup::factory()->create(['hidden' => 0]);

        File::shouldReceive('put')
            ->once()
            ->with(public_path('sitemap.xml'), Mockery::on(function (string $xml) use ($group): bool {
                return str_contains($xml, '<loc>'.htmlspecialchars(url('/store/'.$group->id)).'</loc>');
            }))
            ->andReturn(true);

        $this->generator->generateSitemap();
    }

    public function test_generate_sitemap_excludes_a_hidden_product_group(): void
    {
        $group = ProductGroup::factory()->create(['hidden' => 1]);

        File::shouldReceive('put')
            ->once()
            ->with(public_path('sitemap.xml'), Mockery::on(function (string $xml) use ($group): bool {
                return ! str_contains($xml, '/store/'.$group->id.'</loc>');
            }))
            ->andReturn(true);

        $this->generator->generateSitemap();
    }

    public function test_generate_sitemap_always_includes_contact_us_even_without_a_contactus_page(): void
    {
        File::shouldReceive('put')
            ->once()
            ->with(public_path('sitemap.xml'), Mockery::on(function (string $xml): bool {
                return str_contains($xml, '<loc>'.htmlspecialchars(url('/contact-us')).'</loc>');
            }))
            ->andReturn(true);

        $this->generator->generateSitemap();
    }

    public function test_generate_sitemap_does_not_duplicate_contact_us_when_a_contactus_page_exists(): void
    {
        FrontendPage::factory()->create(['type' => 'contactus', 'publish' => 1]);

        File::shouldReceive('put')
            ->once()
            ->with(public_path('sitemap.xml'), Mockery::on(function (string $xml): bool {
                return substr_count($xml, htmlspecialchars(url('/contact-us')).'</loc>') === 1;
            }))
            ->andReturn(true);

        $this->generator->generateSitemap();
    }

    // --- generateRobots() ---

    public function test_generate_robots_disallows_authenticated_route_prefixes(): void
    {
        File::shouldReceive('put')
            ->once()
            ->with(public_path('robots.txt'), Mockery::on(function (string $content): bool {
                return str_starts_with($content, "User-agent: *\n")
                    && str_contains($content, "Disallow: /client-dashboard\n")
                    && str_contains($content, "Disallow: /admin\n");
            }))
            ->andReturn(true);

        $this->generator->generateRobots();
    }

    public function test_generate_robots_points_to_the_sitemap_url(): void
    {
        File::shouldReceive('put')
            ->once()
            ->with(public_path('robots.txt'), Mockery::on(function (string $content): bool {
                return str_contains($content, 'Sitemap: '.url('/sitemap.xml'));
            }))
            ->andReturn(true);

        $this->generator->generateRobots();
    }

    // --- generateLlms() ---

    public function test_generate_llms_uses_the_company_name_as_the_heading(): void
    {
        File::shouldReceive('put')
            ->once()
            ->with(public_path('llms.txt'), Mockery::on(function (string $content): bool {
                return str_starts_with($content, "# Acme Inc\n");
            }))
            ->andReturn(true);

        $this->generator->generateLlms();
    }

    public function test_generate_llms_lists_login_and_forgot_password(): void
    {
        File::shouldReceive('put')
            ->once()
            ->with(public_path('llms.txt'), Mockery::on(function (string $content): bool {
                return str_contains($content, '('.url('/login').')')
                    && str_contains($content, '('.url('/password/reset').')');
            }))
            ->andReturn(true);

        $this->generator->generateLlms();
    }

    public function test_generate_llms_resolves_shortcodes_in_a_default_pages_seeded_meta(): void
    {
        SeoDefaultPage::factory()->create([
            'page_key' => 'login',
            'meta_title' => 'Sign in to {company}',
            'meta_description' => 'Access your {company} account',
        ]);

        File::shouldReceive('put')
            ->once()
            ->with(public_path('llms.txt'), Mockery::on(function (string $content): bool {
                return str_contains($content, '[Sign in to Acme Inc]')
                    && str_contains($content, 'Access your Acme Inc account');
            }))
            ->andReturn(true);

        $this->generator->generateLlms();
    }

    public function test_generate_llms_lists_a_published_frontend_page(): void
    {
        FrontendPage::factory()->create([
            'slug' => 'about-us',
            'name' => 'About Us',
            'publish' => 1,
            'meta_title' => null,
            'meta_description' => null,
        ]);

        File::shouldReceive('put')
            ->once()
            ->with(public_path('llms.txt'), Mockery::on(function (string $content): bool {
                return str_contains($content, url('/pages/about-us'));
            }))
            ->andReturn(true);

        $this->generator->generateLlms();
    }

    public function test_generate_llms_lists_the_store_and_visible_groups(): void
    {
        $group = ProductGroup::factory()->create(['name' => 'Widgets', 'hidden' => 0]);

        File::shouldReceive('put')
            ->once()
            ->with(public_path('llms.txt'), Mockery::on(function (string $content) use ($group): bool {
                return str_contains($content, '## Products')
                    && str_contains($content, url('/store'))
                    && str_contains($content, url('/store/'.$group->id));
            }))
            ->andReturn(true);

        $this->generator->generateLlms();
    }

    // --- generateAll() ---

    public function test_generate_all_writes_all_three_files(): void
    {
        File::shouldReceive('put')->once()->with(public_path('sitemap.xml'), Mockery::any())->andReturn(true);
        File::shouldReceive('put')->once()->with(public_path('robots.txt'), Mockery::any())->andReturn(true);
        File::shouldReceive('put')->once()->with(public_path('llms.txt'), Mockery::any())->andReturn(true);

        $this->generator->generateAll();
    }
}
