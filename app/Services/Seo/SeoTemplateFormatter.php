<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Facades\Attach;
use App\Model\Common\CommonSettings;
use App\Model\Common\Setting;

/**
 * Fallback resolver for a Pages-module page's or Product Group's own
 * title/description/OG fields when it has none of its own: General SEO
 * (Settings > SEO) setting, then a hardcoded literal built from the
 * instance's own name (e.g. bare "About Us", or "Learn more about About Us
 * at Acme Inc." for the description).
 */
class SeoTemplateFormatter
{
    private const DEFAULT_DESCRIPTION_FORMAT = 'Learn more about {name} at {company}.';

    /** @var array<string, string> */
    private array $settings;

    private string $company;

    private string $appTitle;

    private ?string $generalTitleFormat;

    private ?string $fallbackLogoUrl;

    public function __construct()
    {
        $this->settings = CommonSettings::where('option_name', 'seo')
            ->pluck('option_value', 'optional_field')
            ->all();

        $set = Setting::find(1);
        $this->company = $set?->company ?: ($set?->favicon_title_client ?: 'Faveo Invoicing');
        $this->appTitle = $set?->title ?: '';
        $this->generalTitleFormat = $set?->favicon_title_client;
        $this->fallbackLogoUrl = $set?->logo;
    }

    /**
     * Used for authenticated/unknown routes and as a fallback for default
     * pages without their own meta_description. Empty if unconfigured.
     */
    public function generalDescription(): string
    {
        return $this->apply($this->settings['general_description'] ?? '', '');
    }

    /** og:title fallback for authenticated/unknown routes. Empty if unconfigured. */
    public function generalOgTitle(): string
    {
        return $this->apply($this->settings['general_og_title'] ?? '', '');
    }

    public function generalOgDescription(): string
    {
        return $this->apply($this->settings['general_og_description'] ?? '', '');
    }

    /** Admin-uploaded General Open Graph Image, else the site logo. */
    public function generalOgImageUrl(): ?string
    {
        return $this->imageUrl('general_og_image') ?? $this->fallbackLogoUrl;
    }

    /** General SEO title (favicon_title_client), else the bare name. */
    public function title(string $name): string
    {
        return $this->resolveShortcodes($this->generalTitleFormat, $name) ?: $name;
    }

    /** General SEO description, else a hardcoded sentence built from the name. */
    public function description(string $name): string
    {
        return $this->resolveShortcodes($this->settings['general_description'] ?? null, $name) ?: $this->apply(self::DEFAULT_DESCRIPTION_FORMAT, $name);
    }

    /**
     * Resolves {name}/{company} shortcodes an admin typed into their OWN
     * literal meta_title/meta_description/og_title/og_description. Null-safe
     * since those DB columns are nullable.
     */
    public function resolveShortcodes(?string $text, string $name): ?string
    {
        return $text === null ? null : $this->apply($text, $name);
    }

    private function imageUrl(string $settingKey): ?string
    {
        $filename = $this->settings[$settingKey] ?? null;

        return $filename ? Attach::getUrlPath('images/'.$filename) : null;
    }

    private function apply(string $format, string $name): string
    {
        return str_replace(['{name}', '{company}', '{title}'], [$name, $this->company, $this->appTitle], $format);
    }
}
