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
            'favicon_title_client' => 'Acme Client Title',
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

    // --- {company} resolution fallback chain: company -> favicon_title_client -> "Faveo Billing" ---

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

    public function test_company_falls_back_to_faveo_billing_when_both_are_blank(): void
    {
        Setting::find(1)->update(['company' => '', 'favicon_title_client' => '']);

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('X | Faveo Billing', $formatter->resolveShortcodes('X | {company}', ''));
    }

    // --- pagesTitle() / groupsTitle() ---

    public function test_pages_title_uses_the_configured_format(): void
    {
        $this->setSeoSetting('pages_title_format', '{name} - {company} Pages');

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('About - Acme Inc Pages', $formatter->pagesTitle('About'));
    }

    public function test_pages_title_falls_back_to_the_hardcoded_default_format_when_unset(): void
    {
        $formatter = new SeoTemplateFormatter();

        $this->assertSame('About | Acme Inc', $formatter->pagesTitle('About'));
    }

    public function test_groups_title_uses_the_configured_format(): void
    {
        $this->setSeoSetting('groups_title_format', '{name} Store');

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('Widgets Store', $formatter->groupsTitle('Widgets'));
    }

    public function test_groups_title_falls_back_to_the_hardcoded_default_format_when_unset(): void
    {
        $formatter = new SeoTemplateFormatter();

        $this->assertSame('Widgets | Acme Inc', $formatter->groupsTitle('Widgets'));
    }

    // --- pagesDescription() / groupsDescription(): module -> General -> hardcoded cascade ---

    public function test_pages_description_uses_its_own_format_first(): void
    {
        $this->setSeoSetting('pages_description_format', 'Page: {name}');
        $this->setSeoSetting('general_description', 'General desc');

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('Page: About', $formatter->pagesDescription('About'));
    }

    public function test_pages_description_falls_back_to_general_description(): void
    {
        $this->setSeoSetting('general_description', 'General: {name}');

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('General: About', $formatter->pagesDescription('About'));
    }

    public function test_pages_description_falls_back_to_the_hardcoded_default(): void
    {
        $formatter = new SeoTemplateFormatter();

        $this->assertSame('Learn more about About at Acme Inc.', $formatter->pagesDescription('About'));
    }

    public function test_groups_description_falls_back_to_general_description(): void
    {
        $this->setSeoSetting('general_description', 'General: {name}');

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('General: Widgets', $formatter->groupsDescription('Widgets'));
    }

    public function test_groups_description_falls_back_to_the_hardcoded_default(): void
    {
        $formatter = new SeoTemplateFormatter();

        $this->assertSame('Learn more about Widgets at Acme Inc.', $formatter->groupsDescription('Widgets'));
    }

    // --- pagesOgTitle()/groupsOgTitle() and pagesOgDescription()/groupsOgDescription() cascades ---

    public function test_pages_og_title_falls_back_to_the_hardcoded_default_when_nothing_configured(): void
    {
        $formatter = new SeoTemplateFormatter();

        $this->assertSame('About | Acme Inc', $formatter->pagesOgTitle('About'));
    }

    public function test_pages_og_title_falls_back_to_general_og_title_when_set(): void
    {
        $this->setSeoSetting('general_og_title', 'General OG: {name}');

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('General OG: About', $formatter->pagesOgTitle('About'));
    }

    public function test_pages_og_title_prefers_its_own_format_over_general(): void
    {
        $this->setSeoSetting('general_og_title', 'General OG: {name}');
        $this->setSeoSetting('pages_og_title_format', 'Pages OG: {name}');

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('Pages OG: About', $formatter->pagesOgTitle('About'));
    }

    public function test_groups_og_description_falls_back_to_the_hardcoded_default_when_nothing_configured(): void
    {
        $formatter = new SeoTemplateFormatter();

        $this->assertSame('Learn more about Widgets at Acme Inc.', $formatter->groupsOgDescription('Widgets'));
    }

    public function test_groups_og_description_falls_back_to_general_og_description_when_set(): void
    {
        $this->setSeoSetting('general_og_description', 'General OG Desc: {name}');

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('General OG Desc: Widgets', $formatter->groupsOgDescription('Widgets'));
    }

    public function test_groups_og_title_prefers_its_own_format_over_general(): void
    {
        $this->setSeoSetting('general_og_title', 'General OG: {name}');
        $this->setSeoSetting('groups_og_title_format', 'Groups OG: {name}');

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('Groups OG: Widgets', $formatter->groupsOgTitle('Widgets'));
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

    // --- image URL cascades ---

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

    public function test_pages_og_image_url_falls_back_to_general_og_image_url(): void
    {
        Attach::shouldReceive('getUrlPath')->with('images/logo.png')->andReturn('https://cdn.test/logo.png');
        Setting::find(1)->update(['logo' => 'logo.png']);

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('https://cdn.test/logo.png', $formatter->pagesOgImageUrl());
    }

    public function test_pages_og_image_url_uses_its_own_image_when_set(): void
    {
        Attach::shouldReceive('getUrlPath')->with('images/pages.png')->andReturn('https://cdn.test/pages.png');
        $this->setSeoSetting('pages_og_image', 'pages.png');

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('https://cdn.test/pages.png', $formatter->pagesOgImageUrl());
    }

    public function test_groups_og_image_url_falls_back_to_general_og_image_url(): void
    {
        Attach::shouldReceive('getUrlPath')->with('images/logo.png')->andReturn('https://cdn.test/logo.png');
        Setting::find(1)->update(['logo' => 'logo.png']);

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('https://cdn.test/logo.png', $formatter->groupsOgImageUrl());
    }

    public function test_groups_og_image_url_uses_its_own_image_when_set(): void
    {
        Attach::shouldReceive('getUrlPath')->with('images/groups.png')->andReturn('https://cdn.test/groups.png');
        $this->setSeoSetting('groups_og_image', 'groups.png');

        $formatter = new SeoTemplateFormatter();

        $this->assertSame('https://cdn.test/groups.png', $formatter->groupsOgImageUrl());
    }
}
