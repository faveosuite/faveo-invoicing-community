<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Facades\Attach;
use App\Model\Common\SeoDefaultPage;
use App\Model\Common\Setting;
use App\Model\Front\FrontendPage;
use App\Model\Product\ProductGroup;

/**
 * Resolves server-rendered SEO meta (title/description/robots/canonical)
 * for the single `Route::fallback()` shell that serves every client-panel URL,
 * based on the requested path. Non-authenticated pages (default pages, Pages
 * module, Product Groups) get admin-editable meta; everything else falls back
 * to a hardcoded, noindex entry.
 */
class SeoMetaService
{
    private const INDEX = 'index, follow';

    private const NOINDEX = 'noindex, nofollow';

    public function __construct(private readonly SeoTemplateFormatter $formatter)
    {
    }

    /**
     * @return array{title:string,description:string,robots:string,canonical:string,image:?string,og_title:string,og_description:string}
     */
    public function resolve(string $path): array
    {
        $path = trim($path, '/');

        $defaultKey = match (true) {
            // "/" always redirects to the dashboard, which bounces an
            // unauthenticated visitor (i.e. any crawler) straight to
            // /login — so it's treated as the same page, not a distinct
            // "home" page with its own content.
            $path === '', $path === 'login' => 'login',
            $path === 'password/reset' => 'forgot_password',
            (bool) preg_match('#^password/reset/.+$#', $path) => 'reset_password',
            default => null,
        };

        if ($defaultKey !== null) {
            return $this->fromDefaultPage($defaultKey, $path);
        }

        if (preg_match('#^pages/([^/]+)$#', $path, $matches)) {
            return $this->fromFrontendPage($matches[1], $path);
        }

        if ($path === 'contact-us') {
            return $this->fromContactUsPage($path);
        }

        if ($path === 'store' || preg_match('#^store/(\d+)$#', $path, $matches)) {
            return $this->fromProductGroup($matches[1] ?? null, $path);
        }

        return $this->fallback($path);
    }

    /**
     * @return array{title:string,description:string,robots:string,canonical:string,image:?string,og_title:string,og_description:string}
     */
    private function fromDefaultPage(string $key, string $path): array
    {
        $row = SeoDefaultPage::where('page_key', $key)->first();
        $name = ucwords(str_replace('_', ' ', $key));

        $title = $this->formatter->resolveShortcodes($row?->meta_title, $name) ?: $name;

        $description = $this->formatter->resolveShortcodes($row?->meta_description, $name)
            ?: ($this->formatter->generalDescription() ?: 'Manage your billing, invoices, and subscriptions online.');

        return [
            'title' => $title,
            'description' => $description,
            // reset_password carries a live, single-use token in its own URL
            // segment (/password/reset/{token}) — never index/canonicalize a
            // page whose URL is itself a secret. login/forgot_password have
            // no such secret and stay indexable.
            'robots' => $key === 'reset_password' ? self::NOINDEX : self::INDEX,
            'canonical' => $this->canonicalUrl($path),
            'image' => $this->resolveImage($row?->og_image, 'general'),
            'og_title' => $this->formatter->resolveShortcodes($row?->og_title, $name) ?: ($this->formatter->generalOgTitle() ?: $title),
            'og_description' => $this->formatter->resolveShortcodes($row?->og_description, $name) ?: ($this->formatter->generalOgDescription() ?: $description),
        ];
    }

    /**
     * @return array{title:string,description:string,robots:string,canonical:string,image:?string,og_title:string,og_description:string}
     */
    private function fromFrontendPage(string $slug, string $path): array
    {
        $page = FrontendPage::where('slug', $slug)->where('publish', 1)->first();

        if (! $page) {
            return $this->fallback($path);
        }

        $title = $this->formatter->resolveShortcodes($page->meta_title, $page->name) ?: $this->formatter->pagesTitle($page->name);
        $description = $this->formatter->resolveShortcodes($page->meta_description, $page->name) ?: $this->formatter->pagesDescription($page->name);

        return [
            'title' => $title,
            'description' => $description,
            'robots' => self::INDEX,
            'canonical' => $this->canonicalUrl($path),
            'image' => $this->resolveImage($page->og_image),
            'og_title' => $this->formatter->resolveShortcodes($page->og_title, $page->name) ?: $this->formatter->pagesOgTitle($page->name),
            'og_description' => $this->formatter->resolveShortcodes($page->og_description, $page->name) ?: $this->formatter->pagesOgDescription($page->name),
        ];
    }

    /**
     * @return array{title:string,description:string,robots:string,canonical:string,image:?string,og_title:string,og_description:string}
     */
    private function fromContactUsPage(string $path): array
    {
        // /contact-us is always a real, public page (route: clientRouter.js
        // requiresAuth:false) even before an admin creates a "contactus"-type
        // Pages-module entry for it, so it must stay indexable either way —
        // unlike the shared fallback() below, which is for auth-only/unknown
        // routes and defaults to noindex.
        $page = FrontendPage::where('type', 'contactus')->where('publish', 1)->first();

        $name = $page?->name ?: 'Contact Us';
        $title = $this->formatter->resolveShortcodes($page?->meta_title, $name) ?: $this->formatter->pagesTitle($name);
        $description = $this->formatter->resolveShortcodes($page?->meta_description, $name) ?: $this->formatter->pagesDescription($name);

        return [
            'title' => $title,
            'description' => $description,
            'robots' => self::INDEX,
            'canonical' => $this->canonicalUrl($path),
            'image' => $this->resolveImage($page?->og_image),
            'og_title' => $this->formatter->resolveShortcodes($page?->og_title, $name) ?: $this->formatter->pagesOgTitle($name),
            'og_description' => $this->formatter->resolveShortcodes($page?->og_description, $name) ?: $this->formatter->pagesOgDescription($name),
        ];
    }

    /**
     * @return array{title:string,description:string,robots:string,canonical:string,image:?string,og_title:string,og_description:string}
     */
    private function fromProductGroup(?string $id, string $path): array
    {
        $group = $id !== null
            ? ProductGroup::where(fn ($q) => $q->whereNull('hidden')->orWhere('hidden', '!=', 1))->find($id)
            : null;

        if ($id !== null && ! $group) {
            return $this->fallback($path);
        }

        $name = $group?->name ?: 'Store';
        $title = $this->formatter->resolveShortcodes($group?->meta_title, $name) ?: $this->formatter->groupsTitle($name);
        $description = $this->formatter->resolveShortcodes($group?->meta_description, $name)
            ?: ($group?->tagline ?: ($group?->headline ?: $this->formatter->groupsDescription($name)));

        return [
            'title' => $title,
            'description' => $description,
            'robots' => self::INDEX,
            'canonical' => $this->canonicalUrl($path),
            'image' => $this->resolveImage($group?->og_image, 'groups'),
            'og_title' => $this->formatter->resolveShortcodes($group?->og_title, $name) ?: $this->formatter->groupsOgTitle($name),
            'og_description' => $this->formatter->resolveShortcodes($group?->og_description, $name) ?: $this->formatter->groupsOgDescription($name),
        ];
    }

    /**
     * Hardcoded, non-editable copy for authenticated/transactional routes and
     * anything unrecognized. These are excluded from the sitemap and
     * disallowed in robots.txt, so `noindex` here is defense-in-depth.
     *
     * @return array{title:string,description:string,robots:string,canonical:string,image:?string,og_title:string,og_description:string}
     */
    private function fallback(string $path): array
    {
        $map = [
            'client-dashboard' => ['Dashboard', 'Your account dashboard — track orders, invoices, and subscriptions.'],
            'my-orders' => ['My Orders', 'View and manage your order history.'],
            'my-order' => ['Order Details', 'Order details and status.'],
            'my-invoices' => ['My Invoices', 'View, download, and pay your invoices.'],
            'my-invoice' => ['Invoice Details', 'Invoice details and payment options.'],
            'my-profile' => ['My Profile', 'Manage your account profile and settings.'],
            'cart' => ['Shopping Cart', 'Review items in your shopping cart.'],
            'checkout' => ['Checkout', 'Complete your purchase securely.'],
            'place-order' => ['Place Order', 'Confirm and place your order.'],
            'payment-success' => ['Payment Successful', 'Your payment was successful.'],
            'pricing' => ['Pricing', 'Review pricing and plans.'],
            'verify' => ['Verify Email', 'Verify your email address.'],
            'verify-2fa' => ['Two-Factor Authentication', 'Verify your identity with two-factor authentication.'],
            'pay' => ['Secure Payment', 'Secure payment page.'],
            'admin' => ['Admin', 'Administration panel.'],
        ];

        $prefix = explode('/', $path)[0];
        [$mapTitle, $mapDescription] = $map[$prefix] ?? [null, null];

        // These routes have no real per-page admin setting and no module —
        // only General (admin-configurable) and this hardcoded map (a
        // last-resort default baked into the code). So the cascade here is
        // General → hardcoded map → ultimate literal default. These pages
        // are noindex anyway, so a uniform site title (not a per-route name)
        // is the intended default — but an admin can opt into a per-route
        // title by putting a {name} shortcode in favicon_title_client
        // (e.g. "{name} | Faveo Billing"), which resolves to $mapTitle here.
        $set = Setting::find(1);
        $clientTitle = $this->formatter->resolveShortcodes($set?->favicon_title_client, $mapTitle ?? '');
        $title = $clientTitle ?: ($mapTitle ?: ($set?->company ?: 'Faveo Billing'));
        $description = $this->formatter->generalDescription() ?: ($mapDescription ?: 'Manage your billing, invoices, and subscriptions online.');
        $ogTitle = $this->formatter->generalOgTitle() ?: ($mapTitle ?: $title);
        $ogDescription = $this->formatter->generalOgDescription() ?: ($mapDescription ?: $description);

        return [
            'title' => $title,
            'description' => $description,
            'robots' => self::NOINDEX,
            'canonical' => $this->canonicalUrl($path),
            'image' => $this->formatter->generalOgImageUrl(),
            'og_title' => $ogTitle,
            'og_description' => $ogDescription,
        ];
    }

    /**
     * Resolves a page/group's own Open Graph image, falling back to the
     * site-wide Pages/Groups/General default (Settings → SEO) when it has
     * none of its own.
     */
    private function resolveImage(?string $filename, string $type = 'pages'): ?string
    {
        if ($filename) {
            return Attach::getUrlPath('images/'.$filename);
        }

        return match ($type) {
            'groups' => $this->formatter->groupsOgImageUrl(),
            'general' => $this->formatter->generalOgImageUrl(),
            default => $this->formatter->pagesOgImageUrl(),
        };
    }

    private function canonicalUrl(string $path): string
    {
        return $path === '' ? url('/') : url('/'.$path);
    }
}
