<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Facades\Attach;
use App\Model\Common\CommonSettings;
use App\Model\Common\Setting;

/**
 * Applies the admin-configured default title/description templates
 * (CommonSettings, option_name='seo') for Pages-module pages and Product
 * Groups that don't have their own specific meta_title/meta_description —
 * e.g. "{name} | {company}" instead of just the bare page/group name.
 *
 * Settings and the company name are fetched once per instance, not per
 * call, so looping over many pages/groups (llms.txt generation) doesn't
 * re-query the DB for each one.
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
        $this->company = $set?->company ?: ($set?->favicon_title_client ?: 'Faveo Billing');
        $this->fallbackLogoUrl = $set?->logo;
    }

    /**
     * The site-wide description used for authenticated/unknown routes and
     * as a secondary fallback for default pages without their own
     * meta_description. Empty string if not configured by the admin.
     */
    public function generalDescription(): string
    {
        return $this->apply($this->generalDescriptionFormat(), '');
    }

    /**
     * og:title fallback for authenticated/unknown routes. Empty string if
     * not configured — callers fall back further to the resolved title.
     */
    public function generalOgTitle(): string
    {
        return $this->apply($this->generalOgTitleFormat(), '');
    }

    public function generalOgDescription(): string
    {
        return $this->apply($this->generalOgDescriptionFormat(), '');
    }

    /**
     * Raw (unapplied) template strings, used as the middle cascade tier —
     * per-item value → module format (Pages/Groups) → General → hardcoded
     * default — by pagesX()/groupsX() below, applied against that item's
     * own {name} rather than pre-resolved against an empty one.
     */
    private function generalDescriptionFormat(): string
    {
        return $this->settings['general_description'] ?? '';
    }

    private function generalOgTitleFormat(): string
    {
        return $this->settings['general_og_title'] ?? '';
    }

    private function generalOgDescriptionFormat(): string
    {
        return $this->settings['general_og_description'] ?? '';
    }

    /**
     * The site-wide Open Graph image for authenticated/unknown routes —
     * the admin-uploaded general_og_image if set, otherwise the site logo.
     */
    public function generalOgImageUrl(): ?string
    {
        $filename = $this->settings['general_og_image'] ?? null;

        return $filename ? Attach::getUrlPath('images/'.$filename) : $this->fallbackLogoUrl;
    }

    /**
     * The site-wide Open Graph image for Pages-module pages and anything
     * else without its own image (default pages, unknown/authenticated
     * routes) — the admin-uploaded pages_og_image if set, otherwise the
     * General Open Graph Image, otherwise the site logo, so og:image is
     * never empty. Same "module → General → hardcoded default" cascade the
     * text fields below use.
     */
    public function pagesOgImageUrl(): ?string
    {
        $filename = $this->settings['pages_og_image'] ?? null;

        return $filename ? Attach::getUrlPath('images/'.$filename) : $this->generalOgImageUrl();
    }

    /**
     * The site-wide Open Graph image for Product Groups without their own
     * og_image, falling back to the General Open Graph Image, then the site
     * logo.
     */
    public function groupsOgImageUrl(): ?string
    {
        $filename = $this->settings['groups_og_image'] ?? null;

        return $filename ? Attach::getUrlPath('images/'.$filename) : $this->generalOgImageUrl();
    }

    public function pagesTitle(string $name): string
    {
        return $this->apply(($this->settings['pages_title_format'] ?? '') ?: self::DEFAULT_TITLE_FORMAT, $name);
    }

    /**
     * Cascade: Pages Description Format → General Description → hardcoded
     * default — same "per-module then general" tier used by the og_*
     * variants below.
     */
    public function pagesDescription(string $name): string
    {
        $format = ($this->settings['pages_description_format'] ?? '') ?: $this->generalDescriptionFormat();

        return $this->apply($format ?: self::DEFAULT_DESCRIPTION_FORMAT, $name);
    }

    public function groupsTitle(string $name): string
    {
        return $this->apply(($this->settings['groups_title_format'] ?? '') ?: self::DEFAULT_TITLE_FORMAT, $name);
    }

    public function groupsDescription(string $name): string
    {
        $format = ($this->settings['groups_description_format'] ?? '') ?: $this->generalDescriptionFormat();

        return $this->apply($format ?: self::DEFAULT_DESCRIPTION_FORMAT, $name);
    }

    /**
     * Used as the og:title fallback for a Page that has neither its own
     * og_title nor meta_title set — kept separate from pagesTitle() so the
     * social-share copy can be styled differently from the SERP title.
     * Cascade: Pages Open Graph Title Format → General Open Graph Title →
     * hardcoded default.
     */
    public function pagesOgTitle(string $name): string
    {
        $format = ($this->settings['pages_og_title_format'] ?? '') ?: $this->generalOgTitleFormat();

        return $this->apply($format ?: self::DEFAULT_TITLE_FORMAT, $name);
    }

    public function pagesOgDescription(string $name): string
    {
        $format = ($this->settings['pages_og_description_format'] ?? '') ?: $this->generalOgDescriptionFormat();

        return $this->apply($format ?: self::DEFAULT_DESCRIPTION_FORMAT, $name);
    }

    public function groupsOgTitle(string $name): string
    {
        $format = ($this->settings['groups_og_title_format'] ?? '') ?: $this->generalOgTitleFormat();

        return $this->apply($format ?: self::DEFAULT_TITLE_FORMAT, $name);
    }

    public function groupsOgDescription(string $name): string
    {
        $format = ($this->settings['groups_og_description_format'] ?? '') ?: $this->generalOgDescriptionFormat();

        return $this->apply($format ?: self::DEFAULT_DESCRIPTION_FORMAT, $name);
    }

    /**
     * Resolves the {name}/{company} shortcodes an admin may have typed into
     * their OWN literal meta_title/meta_description/og_title/og_description
     * (as opposed to apply()'s use below, which resolves the *format*
     * settings like "Pages Title Format" against an item that has none of
     * its own). Null-safe since these fields are nullable.
     */
    public function resolveShortcodes(?string $text, string $name): ?string
    {
        return $text === null ? null : $this->apply($text, $name);
    }

    private function apply(string $format, string $name): string
    {
        return str_replace(['{name}', '{company}'], [$name, $this->company], $format);
    }
}
