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
        // Empty url: a page with one configured is a link, and is listed at
        // that url instead of /pages/{slug}.
        $page = FrontendPage::factory()->create(['slug' => 'about-us', 'publish' => 1, 'url' => '']);

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
            'url' => '',
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

    /** Runs generateLlms() and returns what it would write to llms.txt. */
    private function llmsContent(): string
    {
        $written = null;
        File::shouldReceive('put')->once()->with(public_path('llms.txt'), Mockery::on(function (string $content) use (&$written): bool {
            $written = $content;

            return true;
        }))->andReturn(true);

        $this->generator->generateLlms();

        return (string) $written;
    }

    public function test_generate_llms_writes_the_exact_pages_section(): void
    {
        SeoDefaultPage::create(['page_key' => 'login', 'meta_title' => 'Sign in to {company}', 'meta_description' => '']);
        FrontendPage::create(['name' => 'Terms', 'slug' => 'terms', 'content' => 'x', 'publish' => 1, 'type' => 'custom', 'meta_title' => '{name} page', 'meta_description' => 'All the {name}']);
        FrontendPage::create(['name' => 'About', 'slug' => 'about', 'url' => 'https://elsewhere.test/about', 'content' => 'x', 'publish' => 1, 'type' => 'custom']);
        FrontendPage::create(['name' => 'Draft', 'slug' => 'draft', 'content' => 'x', 'publish' => 0, 'type' => 'custom']);

        $content = $this->llmsContent();

        $this->assertSame(implode("\n", [
            '# Acme Inc',
            '',
            '> Acme Inc — subscription billing and invoicing platform.',
            '',
            '## Pages',
            '',
            '- [Sign in to Acme Inc]('.url('/login').'): Sign in to your account.',
            '- [Forgot Password]('.url('/password/reset').'): Reset your account password.',
            '- [Terms page]('.url('/pages/terms').'): All the Terms',
            '- [About](https://elsewhere.test/about): Learn more about About at Acme Inc.',
            '- [Contact Us]('.url('/contact-us').'): Get in touch with our team.',
            '',
            '## Products',
            '',
            '- [Store]('.url('/store').'): Learn more about Store at Acme Inc.',
        ]), substr($content, 0, strpos($content, "\n", strpos($content, ']('.url('/store').')'))));
        $this->assertStringEndsWith("\n", $content);
    }

    public function test_generate_llms_does_not_add_contact_us_when_a_contactus_page_exists(): void
    {
        FrontendPage::create(['name' => 'Reach us', 'slug' => 'reach', 'content' => 'x', 'publish' => 1, 'type' => 'contactus']);

        $this->assertStringNotContainsString('[Contact Us]', $this->llmsContent());
    }

    public function test_generate_llms_describes_each_group_by_meta_then_tagline_then_headline(): void
    {
        $own = ProductGroup::factory()->create(['name' => 'Own', 'hidden' => 0, 'meta_title' => '{name} T', 'meta_description' => '{name} D', 'tagline' => 'tag', 'headline' => 'head']);
        $tag = ProductGroup::factory()->create(['name' => 'Tag', 'hidden' => null, 'tagline' => 'Tagline', 'headline' => 'Headline']);
        $head = ProductGroup::factory()->create(['name' => 'Head', 'hidden' => 0, 'tagline' => '', 'headline' => 'Headline']);
        $bare = ProductGroup::factory()->create(['name' => 'Bare', 'hidden' => 0, 'tagline' => null, 'headline' => null]);
        $hidden = ProductGroup::factory()->create(['name' => 'Hidden', 'hidden' => 1]);

        $content = $this->llmsContent();

        $this->assertStringContainsString("\n- [Own T](".url('/store/'.$own->id).'): Own D', $content);
        $this->assertStringContainsString("\n- [Tag](".url('/store/'.$tag->id).'): Tagline', $content);
        $this->assertStringContainsString("\n- [Head](".url('/store/'.$head->id).'): Headline', $content);
        $this->assertStringContainsString("\n- [Bare](".url('/store/'.$bare->id).'): Learn more about Bare at Acme Inc.', $content);
        $this->assertStringNotContainsString(url('/store/'.$hidden->id).')', $content);
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
