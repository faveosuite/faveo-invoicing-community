<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Model\Common\SeoDefaultPage;
use App\Model\Common\Setting;
use App\Model\Front\FrontendPage;
use App\Model\Product\ProductGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;
use SimpleXMLElement;

/**
 * Writes public/sitemap.xml, public/robots.txt, and public/llms.txt as
 * physical files. They must be physical files (not Laravel routes) because
 * public/.htaccess serves any file that exists in public/ directly,
 * bypassing Laravel entirely for that request.
 */
class SeoFileGenerator
{
    /**
     * Authenticated/transactional route prefixes excluded from SEO. A single
     * robots.txt Disallow line blocks the whole family (e.g. "/my-order"
     * blocks both /my-orders and /my-order/{id}).
     *
     * @var list<string>
     */
    private const array DISALLOWED_PREFIXES = [
        '/client-dashboard',
        '/my-order',
        '/my-invoice',
        '/my-profile',
        '/cart',
        '/checkout',
        '/place-order',
        '/payment-success',
        '/pricing',
        '/verify',
        '/pay',
        '/admin',
    ];

    public function generateAll(): void
    {
        $this->generateSitemap();
        $this->generateRobots();
        $this->generateLlms();
    }

    public function generateSitemap(): void
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        foreach ($this->sitemapUrls() as $entry) {
            $node = $xml->addChild('url');
            $node->addChild('loc', htmlspecialchars($entry['loc']));
            if ($entry['lastmod']) {
                $node->addChild('lastmod', $entry['lastmod']);
            }
        }

        File::put(public_path('sitemap.xml'), (string) $xml->asXML());
    }

    public function generateRobots(): void
    {
        $lines = ['User-agent: *'];

        foreach (self::DISALLOWED_PREFIXES as $prefix) {
            $lines[] = 'Disallow: '.$prefix;
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.url('/sitemap.xml');

        File::put(public_path('robots.txt'), implode("\n", $lines)."\n");
    }

    public function generateLlms(): void
    {
        $set = Setting::find(1);
        $company = $set?->company ?: ($set?->favicon_title_client ?: 'Faveo Billing');
        $formatter = app(SeoTemplateFormatter::class);

        $defaultPages = SeoDefaultPage::whereIn('page_key', ['login', 'forgot_password'])->get()->keyBy('page_key');
        $login = $defaultPages->get('login');
        $forgotPassword = $defaultPages->get('forgot_password');

        $lines = [
            '# '.$company,
            '',
            '> '.$company.' — subscription billing and invoicing platform.',
            '',
            '## Pages',
            '',
            '- ['.($formatter->resolveShortcodes($login?->meta_title, 'Login') ?: 'Login').']('.url('/login').'): '.($formatter->resolveShortcodes($login?->meta_description, 'Login') ?: 'Sign in to your account.'),
            '- ['.($formatter->resolveShortcodes($forgotPassword?->meta_title, 'Forgot Password') ?: 'Forgot Password').']('.url('/password/reset').'): '.($formatter->resolveShortcodes($forgotPassword?->meta_description, 'Forgot Password') ?: 'Reset your account password.'),
        ];

        $hasContactUsPage = false;

        foreach (FrontendPage::where('publish', 1)->get(['name', 'slug', 'type', 'meta_title', 'meta_description']) as $page) {
            $loc = $page->type === 'contactus' ? url('/contact-us') : url('/pages/'.$page->slug);
            $hasContactUsPage = $hasContactUsPage || $page->type === 'contactus';
            $title = $formatter->resolveShortcodes($page->meta_title, $page->name) ?: $formatter->pagesTitle($page->name);
            $description = $formatter->resolveShortcodes($page->meta_description, $page->name) ?: $formatter->pagesDescription($page->name);
            $lines[] = '- ['.$title.']('.$loc.'): '.$description;
        }

        // /contact-us is always a real, public page (see
        // SeoMetaService::fromContactUsPage) even before an admin creates a
        // "contactus"-type Pages-module entry for it — list it either way.
        if (! $hasContactUsPage) {
            $lines[] = '- [Contact Us]('.url('/contact-us').'): Get in touch with our team.';
        }

        $lines[] = '';
        $lines[] = '## Products';
        $lines[] = '';

        $lines[] = '- ['.$formatter->groupsTitle('Store').']('.url('/store').'): '.$formatter->groupsDescription('Store');

        foreach ($this->visibleGroups(['id', 'name', 'headline', 'tagline', 'meta_title', 'meta_description']) as $group) {
            $title = $formatter->resolveShortcodes($group->meta_title, $group->name) ?: $formatter->groupsTitle($group->name);
            $description = $formatter->resolveShortcodes($group->meta_description, $group->name)
                ?: ($group->tagline ?: ($group->headline ?: $formatter->groupsDescription($group->name)));
            $lines[] = '- ['.$title.']('.url('/store/'.$group->id).'): '.$description;
        }

        File::put(public_path('llms.txt'), implode("\n", $lines)."\n");
    }

    /**
     * @return list<array{loc:string,lastmod:?string}>
     */
    private function sitemapUrls(): array
    {
        $urls = [
            ['loc' => url('/'), 'lastmod' => null],
            ['loc' => url('/login'), 'lastmod' => null],
            ['loc' => url('/password/reset'), 'lastmod' => null],
        ];

        $hasContactUsPage = false;

        foreach (FrontendPage::where('publish', 1)->get(['slug', 'type', 'updated_at']) as $page) {
            $loc = $page->type === 'contactus' ? url('/contact-us') : url('/pages/'.$page->slug);
            $hasContactUsPage = $hasContactUsPage || $page->type === 'contactus';
            $urls[] = ['loc' => $loc, 'lastmod' => optional($page->updated_at)->toAtomString()];
        }

        // /contact-us is always a real, public page (see
        // SeoMetaService::fromContactUsPage) even before an admin creates a
        // "contactus"-type Pages-module entry for it — list it either way.
        if (! $hasContactUsPage) {
            $urls[] = ['loc' => url('/contact-us'), 'lastmod' => null];
        }

        $urls[] = ['loc' => url('/store'), 'lastmod' => null];

        foreach ($this->visibleGroups(['id', 'updated_at']) as $group) {
            $urls[] = ['loc' => url('/store/'.$group->id), 'lastmod' => optional($group->updated_at)->toAtomString()];
        }

        return $urls;
    }

    /**
     * Product Groups eligible for the sitemap/llms.txt — `hidden` is a
     * nullable column, and `!= 1` alone would silently drop any row where
     * it's NULL rather than 0/1 (SQL three-valued logic), even though such
     * a row is otherwise treated as visible (e.g. StoreController).
     *
     * @param  list<string>  $columns
     * @return Collection<int, ProductGroup>
     */
    private function visibleGroups(array $columns): Collection
    {
        return ProductGroup::where(fn ($q) => $q->whereNull('hidden')->orWhere('hidden', '!=', 1))->get($columns);
    }
}
