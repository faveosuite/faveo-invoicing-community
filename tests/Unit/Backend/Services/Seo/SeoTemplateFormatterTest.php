<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Services\Seo;

use App\Facades\Attach;
use App\Model\Common\CommonSettings;
use App\Model\Common\Setting;
use App\Services\Seo\SeoTemplateFormatter;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class SeoTemplateFormatterTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Deterministic base state — rolled back by DatabaseTransactions.
        Setting::updateOrCreate(['id' => 1], [
            'company' => 'Acme Inc',
            'favicon_title_client' => '',
            'logo' => null,
        ]);
        CommonSettings::where('option_name', 'seo')->delete();
    }

    private function setSeoSetting(string $field, string $value): void
    {
        CommonSettings::upsert(
            [['option_name' => 'seo', 'optional_field' => $field, 'option_value' => $value, 'status' => '']],
            ['option_name', 'optional_field'],
            ['option_value']
        );
    }

    // --- resolveShortcodes() ---

    public function test_resolve_shortcodes_substitutes_name_and_company(): void
    {
        $formatter = new SeoTemplateFormatter();

        $this->assertSame('My Page | Acme Inc', $formatter->resolveShortcodes('{name} | {company}', 'My Page'));
    }

    public function test_resolve_shortcodes_returns_null_for_null_text(): void
    {
        $formatter = new SeoTemplateFormatter();

        $this->assertNull($formatter->resolveShortcodes(null, 'My Page'));
    }

    public function test_resolve_shortcodes_leaves_plain_text_without_placeholders_untouched(): void
    {
        $formatter = new SeoTemplateFormatter();

        $this->assertSame('Just plain text', $formatter->resolveShortcodes('Just plain text', 'My Page'));
    }

    public function test_resolve_shortcodes_returns_empty_string_untouched(): void
    {
        $formatter = new SeoTemplateFormatter();

        $this->assertSame('', $formatter->resolveShortcodes('', 'My Page'));
    }

    // --- {title} shortcode: Setting::title ---

    public function test_resolve_shortcodes_substitutes_title(): void
    {
        Setting::find(1)->update(['title' => 'Acme App']);

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('X | Acme App', $formatter->resolveShortcodes('X | {title}', ''));
    }

    public function test_resolve_shortcodes_substitutes_title_as_empty_when_unset(): void
    {
        Setting::find(1)->update(['title' => '']);

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('X | ', $formatter->resolveShortcodes('X | {title}', ''));
    }

    // --- {company} resolution fallback chain: company -> favicon_title_client -> "Faveo Invoicing" ---

    public function test_company_uses_the_company_field_when_set(): void
    {
        $formatter = new SeoTemplateFormatter();

        $this->assertSame('X | Acme Inc', $formatter->resolveShortcodes('X | {company}', ''));
    }

    public function test_company_falls_back_to_favicon_title_client_when_company_is_blank(): void
    {
        Setting::find(1)->update(['company' => '', 'favicon_title_client' => 'Fallback Title']);

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('X | Fallback Title', $formatter->resolveShortcodes('X | {company}', ''));
    }

    public function test_company_falls_back_to_faveo_invoicing_when_both_are_blank(): void
    {
        Setting::find(1)->update(['company' => '', 'favicon_title_client' => '']);

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('X | Faveo Invoicing', $formatter->resolveShortcodes('X | {company}', ''));
    }

    // --- title(): General SEO title (favicon_title_client) -> bare name ---

    public function test_title_falls_back_to_the_bare_name_when_favicon_title_client_is_unset(): void
    {
        $formatter = new SeoTemplateFormatter();

        $this->assertSame('About', $formatter->title('About'));
    }

    public function test_title_uses_favicon_title_client_when_set(): void
    {
        Setting::find(1)->update(['favicon_title_client' => '{name} - {company} Pages']);

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('About - Acme Inc Pages', $formatter->title('About'));
    }

    public function test_title_resolves_shortcodes_in_favicon_title_client(): void
    {
        Setting::find(1)->update(['favicon_title_client' => '{name} Store']);

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('Widgets Store', $formatter->title('Widgets'));
    }

    // --- description(): General SEO description -> hardcoded default ---

    public function test_description_falls_back_to_general_description(): void
    {
        $this->setSeoSetting('general_description', 'General: {name}');

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('General: About', $formatter->description('About'));
    }

    public function test_description_falls_back_to_the_hardcoded_default_when_unset(): void
    {
        $formatter = new SeoTemplateFormatter();

        $this->assertSame('Learn more about About at Acme Inc.', $formatter->description('About'));
    }

    public function test_description_falls_back_to_the_hardcoded_default_for_a_different_name(): void
    {
        $formatter = new SeoTemplateFormatter();

        $this->assertSame('Learn more about Widgets at Acme Inc.', $formatter->description('Widgets'));
    }

    // --- general*() — always resolved against an empty name ---

    public function test_general_description_resolves_with_an_empty_name(): void
    {
        $this->setSeoSetting('general_description', 'Desc [{name}] end');

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('Desc [] end', $formatter->generalDescription());
    }

    public function test_general_description_is_empty_string_when_unset(): void
    {
        $formatter = new SeoTemplateFormatter();

        $this->assertSame('', $formatter->generalDescription());
    }

    public function test_general_og_title_is_empty_string_when_unset(): void
    {
        $formatter = new SeoTemplateFormatter();

        $this->assertSame('', $formatter->generalOgTitle());
    }

    public function test_general_og_description_is_empty_string_when_unset(): void
    {
        $formatter = new SeoTemplateFormatter();

        $this->assertSame('', $formatter->generalOgDescription());
    }

    // --- image URL cascade ---

    public function test_general_og_image_url_falls_back_to_the_stock_logo_asset_when_nothing_configured(): void
    {
        Setting::find(1)->update(['logo' => null]);

        $formatter = new SeoTemplateFormatter();

        $this->assertSame(asset('images/agora-invoicing.png'), $formatter->generalOgImageUrl());
    }

    public function test_general_og_image_url_falls_back_to_the_configured_site_logo(): void
    {
        Attach::shouldReceive('getUrlPath')->with('images/logo.png')->andReturn('https://cdn.test/logo.png');
        Setting::find(1)->update(['logo' => 'logo.png']);

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('https://cdn.test/logo.png', $formatter->generalOgImageUrl());
    }

    public function test_general_og_image_url_uses_the_configured_general_og_image_when_set(): void
    {
        Attach::shouldReceive('getUrlPath')->with('images/general.png')->andReturn('https://cdn.test/general.png');
        $this->setSeoSetting('general_og_image', 'general.png');

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('https://cdn.test/general.png', $formatter->generalOgImageUrl());
    }
}
