<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Facades\Attach;
use App\Model\Common\CommonSettings;
use App\Model\Common\Setting;

/**
 * Admin-configured default title/description templates (CommonSettings,
 * option_name='seo') for Pages-module pages and Product Groups that don't
 * have their own meta_title/meta_description — e.g. "{name} | {company}"
 * instead of just the bare page/group name. $type is always 'pages' or
 * 'groups', matching the *_title_format/*_og_image etc setting key prefixes.
 */
class SeoTemplateFormatter
{
    private const DEFAULT_TITLE_FORMAT = '{name} | {company}';

    private const DEFAULT_DESCRIPTION_FORMAT = 'Learn more about {name} at {company}.';

    /** @var array<string, string> */
    private array $settings;

    private string $company;

    private ?string $fallbackLogoUrl;

    public function __construct()
    {
        $this->settings = CommonSettings::where('option_name', 'seo')
            ->pluck('option_value', 'optional_field')
            ->all();

        $set = Setting::find(1);
        $this->company = $set?->company ?: ($set?->favicon_title_client ?: 'Faveo Invoicing');
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

    /** Own {$type}_og_image, else General, else the site logo. */
    public function ogImageUrl(string $type): ?string
    {
        return $this->imageUrl($type.'_og_image') ?? $this->generalOgImageUrl();
    }

    public function title(string $type, string $name): string
    {
        return $this->apply($this->cascadeFormat($type.'_title_format', '', self::DEFAULT_TITLE_FORMAT), $name);
    }

    /** Module format setting -> General SEO description -> hardcoded default. */
    public function description(string $type, string $name): string
    {
        return $this->apply($this->cascadeFormat($type.'_description_format', $this->settings['general_description'] ?? '', self::DEFAULT_DESCRIPTION_FORMAT), $name);
    }

    /** Separate from title() so social-share copy can differ from the SERP title. */
    public function ogTitle(string $type, string $name): string
    {
        return $this->apply($this->cascadeFormat($type.'_og_title_format', $this->settings['general_og_title'] ?? '', self::DEFAULT_TITLE_FORMAT), $name);
    }

    public function ogDescription(string $type, string $name): string
    {
        return $this->apply($this->cascadeFormat($type.'_og_description_format', $this->settings['general_og_description'] ?? '', self::DEFAULT_DESCRIPTION_FORMAT), $name);
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

    /** Module format setting → general format (if given) → hardcoded fallback. */
    private function cascadeFormat(string $moduleSettingKey, string $generalFormat, string $fallbackFormat): string
    {
        return ($this->settings[$moduleSettingKey] ?? '') ?: ($generalFormat ?: $fallbackFormat);
    }

    private function imageUrl(string $settingKey): ?string
    {
        $filename = $this->settings[$settingKey] ?? null;

        return $filename ? Attach::getUrlPath('images/'.$filename) : null;
    }

    private function apply(string $format, string $name): string
    {
        return str_replace(['{name}', '{company}'], [$name, $this->company], $format);
    }
}
