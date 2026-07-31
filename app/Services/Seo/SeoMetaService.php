<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Facades\Attach;
use App\Model\Common\SeoDefaultPage;
use App\Model\Common\Setting;
use App\Model\Front\FrontendPage;
use App\Model\Product\ProductGroup;

/**
 * Resolves server-rendered SEO meta for both SSR shells:
 * - Client: title/description/robots/canonical/OG for the `Route::fallback()`
 *   shell that serves every client-panel URL. Non-authenticated pages
 *   (default pages, Pages module, Product Groups) get admin-editable meta;
 *   everything else falls back to a hardcoded, noindex entry.
 * - Admin: title/description only, via resolveAdmin()/resolveAdminRoutes() —
 *   General SEO -> per-route default (admin_routes.php) -> hardcoded literal.
 *   Same "route map -> general setting -> hardcoded" algorithm as the
 *   client's own fallback(), just against a different route map and Setting
 *   column (favicon_title vs favicon_title_client).
 */
class SeoMetaService
{
    private const INDEX = 'index, follow';

    private const NOINDEX = 'noindex, nofollow';

    private const DEFAULT_PAGE_KEYS = ['login', 'forgot_password', 'reset_password', 'cart'];

    private ?Setting $set = null;

    private bool $setLoaded = false;

    /** @var array<string, SeoDefaultPage>|null */
    private ?array $defaultPages = null;

    /** @var array<string, array{title: string, description: string}>|null */
    private ?array $clientRoutesCache = null;

    /** @var array<string, array{title: string, description: string}>|null */
    private ?array $adminRoutesCache = null;

    /** @var array<string, array{title:string,description:string,robots:string,canonical:string,image:?string,og_title:string,og_description:string}> */
    private array $resolved = [];

    public function __construct(private readonly SeoTemplateFormatter $formatter)
    {
    }

    /**
     * @return array{title:string,description:string,robots:string,canonical:string,image:?string,og_title:string,og_description:string}
     */
    public function resolve(string $path): array
    {
        $path = trim($path, '/');

        // Memoized per path: resolveClientRoutes() resolves ~18 paths per
        // request, and the current page's own path gets resolved again there.
        return $this->resolved[$path] ??= $this->resolveUncached($path);
    }

    /**
     * @return array{title:string,description:string,robots:string,canonical:string,image:?string,og_title:string,og_description:string}
     */
    private function resolveUncached(string $path): array
    {
        $defaultKey = match (true) {
            $path === '', $path === 'login' => 'login', // "/" redirects to the dashboard, which bounces guests to /login
            $path === 'password/reset' => 'forgot_password',
            (bool) preg_match('#^password/reset/.+$#', $path) => 'reset_password',
            $path === 'cart' => 'cart', // guest-accessible like login, not auth-gated
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
        $row = $this->defaultPageRow($key);
        $name = ucwords(str_replace('_', ' ', $key));

        $title = $this->formatter->resolveShortcodes($row?->meta_title, $name) ?: $name;
        $description = $this->formatter->resolveShortcodes($row?->meta_description, $name)
            ?: ($this->formatter->generalDescription() ?: 'Manage your billing, invoices, and subscriptions online.');

        return $this->assemble(
            $title,
            $description,
            match ($key) {
                'reset_password' => self::NOINDEX, // its URL carries a live, single-use token — never index a secret URL
                'cart' => self::NOINDEX, // per-user, constantly-changing contents — already disallowed in robots.txt
                default => self::INDEX,
            },
            $path,
            $this->resolveImage($row?->og_image),
            $this->formatter->resolveShortcodes($row?->og_title, $name) ?: ($this->formatter->generalOgTitle() ?: $title),
            $this->formatter->resolveShortcodes($row?->og_description, $name) ?: ($this->formatter->generalOgDescription() ?: $description),
        );
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

        $title = $this->formatter->resolveShortcodes($page->meta_title, $page->name) ?: $this->formatter->title($page->name);
        $description = $this->formatter->resolveShortcodes($page->meta_description, $page->name) ?: $this->formatter->description($page->name);

        return $this->assemble(
            $title,
            $description,
            self::INDEX,
            $path,
            $this->resolveImage($page->og_image),
            $this->formatter->resolveShortcodes($page->og_title, $page->name) ?: ($this->formatter->generalOgTitle() ?: $title),
            $this->formatter->resolveShortcodes($page->og_description, $page->name) ?: ($this->formatter->generalOgDescription() ?: $description),
        );
    }

    /**
     * @return array{title:string,description:string,robots:string,canonical:string,image:?string,og_title:string,og_description:string}
     */
    private function fromContactUsPage(string $path): array
    {
        // Always a real, public page — unlike fallback() below, stays indexable even with no contactus-type page created yet.
        $page = FrontendPage::where('type', 'contactus')->where('publish', 1)->first();

        $name = $page?->name ?: 'Contact Us';
        $title = $this->formatter->resolveShortcodes($page?->meta_title, $name) ?: $this->formatter->title($name);
        $description = $this->formatter->resolveShortcodes($page?->meta_description, $name) ?: $this->formatter->description($name);

        return $this->assemble(
            $title,
            $description,
            self::INDEX,
            $path,
            $this->resolveImage($page?->og_image),
            $this->formatter->resolveShortcodes($page?->og_title, $name) ?: ($this->formatter->generalOgTitle() ?: $title),
            $this->formatter->resolveShortcodes($page?->og_description, $name) ?: ($this->formatter->generalOgDescription() ?: $description),
        );
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
        $title = $this->formatter->resolveShortcodes($group?->meta_title, $name) ?: $this->formatter->title($name);
        $description = $this->formatter->resolveShortcodes($group?->meta_description, $name)
            ?: ($group?->tagline ?: ($group?->headline ?: $this->formatter->description($name)));

        return $this->assemble(
            $title,
            $description,
            self::INDEX,
            $path,
            $this->resolveImage($group?->og_image),
            $this->formatter->resolveShortcodes($group?->og_title, $name) ?: ($this->formatter->generalOgTitle() ?: $title),
            $this->formatter->resolveShortcodes($group?->og_description, $name) ?: ($this->formatter->generalOgDescription() ?: $description),
        );
    }

    /**
     * Pre-resolved title/description for every static client-SPA route,
     * shipped to client.blade.php so clientRouter.js only ever looks up an
     * already-resolved value (keyed the same way as normalizeRoutePattern()
     * derives from Vue Router's own matched path — see routePattern.js) on
     * SPA navigation, never re-implements this cascade. Dynamic per-instance
     * routes (Pages/:slug, Store/:id) fetch their own data and resolve their
     * own title via PageController/StoreController.
     *
     * @return array<string, array{title: string, description: string}>
     */
    public function resolveClientRoutes(): array
    {
        $paths = [
            'login' => 'login',
            'password/reset' => 'password/reset',
            'password/reset/*' => 'password/reset/x',
            'contact-us' => 'contact-us',
            'cart' => 'cart',
        ];
        foreach (array_keys($this->clientRoutes()) as $key) {
            // Purely-numeric keys ('404') come back from array_keys() as int,
            // not string — PHP's own array-key coercion, unrelated to the data.
            $key = (string) $key;
            // Keys can be wildcard patterns (e.g. 'my-order/*') — substitute a
            // placeholder so resolve() has a real path to match against.
            $paths[$key] = str_replace('*', 'x', $key);
        }

        return array_map(function (string $path): array {
            $resolved = $this->resolve($path);

            return ['title' => $resolved['title'], 'description' => $resolved['description']];
        }, $paths);
    }

    /**
     * @return array{title:string,description:string,robots:string,canonical:string,image:?string,og_title:string,og_description:string}
     */
    private function fallback(string $path): array
    {
        // Noindex routes still get a real title: General (favicon_title_client, admin can add a {name} shortcode) → per-route name → company → app name.
        $cascade = $this->resolveRouteCascade($this->clientRoutes(), $path, $this->setting()?->favicon_title_client);
        $ogTitle = $this->formatter->generalOgTitle() ?: ($cascade['routeTitle'] ?: $cascade['title']);
        $ogDescription = $this->formatter->generalOgDescription() ?: ($cascade['routeDescription'] ?: $cascade['description']);

        return $this->assemble($cascade['title'], $cascade['description'], self::NOINDEX, $path, $this->formatter->generalOgImageUrl(), $ogTitle, $ogDescription);
    }

    /**
     * Admin panel's title/description cascade for a single path — General SEO
     * (Settings > SEO > General) -> per-route default (admin_routes.php) ->
     * hardcoded literal.
     *
     * @return array{title:string,description:string}
     */
    public function resolveAdmin(string $path): array
    {
        $cascade = $this->resolveRouteCascade($this->adminRoutes(), $path, $this->setting()?->favicon_title);

        return ['title' => $cascade['title'], 'description' => $cascade['description']];
    }

    /**
     * Full admin SSR shell meta for the current request's own path —
     * resolveAdmin()'s title/description cascade plus the OG fields. OG
     * fields skip the per-route cascade (General SEO -> hardcoded literal
     * only): the admin panel is always noindex/auth-gated and never
     * crawled, so they only matter for link-preview cards (Slack/Teams/
     * email) when someone shares an admin URL — no need to vary per route.
     *
     * @return array{title:string,description:string,og_title:string,og_description:string,image:?string}
     */
    public function resolveAdminMeta(string $path): array
    {
        $cascade = $this->resolveAdmin($path);

        return [
            'title' => $cascade['title'],
            'description' => $cascade['description'],
            'og_title' => $this->formatter->generalOgTitle() ?: $cascade['title'],
            'og_description' => $this->formatter->generalOgDescription() ?: $cascade['description'],
            'image' => $this->formatter->generalOgImageUrl(),
        ];
    }

    /**
     * Every admin route's title/description, pre-resolved through
     * resolveAdmin() — shipped to admin.blade.php so adminRouter.js/
     * useBreadcrumb.js never re-implement this cascade.
     *
     * @return array<string, array{title: string, description: string}>
     */
    public function resolveAdminRoutes(): array
    {
        $result = [];
        foreach (array_keys($this->adminRoutes()) as $key) {
            // Purely-numeric keys ('404') come back from array_keys() as int,
            // not string — PHP's own array-key coercion, unrelated to the data.
            $key = (string) $key;
            // Keys can be wildcard patterns (e.g. 'orders/*/renew') —
            // substitute a placeholder so resolveAdmin() has a real path to match.
            $result[$key] = $this->resolveAdmin(str_replace('*', 'x', $key));
        }

        return $result;
    }

    /**
     * Shared by fallback() (client) and resolveAdmin() — same "route map ->
     * General SEO -> hardcoded literal" algorithm, just against a different
     * route map and General-title Setting column.
     *
     * @param  array<string, array{title:string,description:string}>  $routes
     * @return array{title:string,description:string,routeTitle:?string,routeDescription:?string}
     */
    private function resolveRouteCascade(array $routes, string $path, ?string $generalTitleFormat): array
    {
        $entry = $this->matchRoute($routes, $path) ?? ($routes[explode('/', $path)[0]] ?? null);
        $routeTitle = $entry ? $this->resolveText($entry['title']) : null;
        $routeDescription = $entry ? $this->resolveText($entry['description']) : null;

        $generalTitle = $this->formatter->resolveShortcodes($generalTitleFormat, $routeTitle ?? '');
        $title = $generalTitle ?: ($routeTitle ?: ($this->setting()?->company ?: 'Faveo Invoicing'));
        $description = $this->formatter->generalDescription() ?: ($routeDescription ?: 'Manage your billing, invoices, and subscriptions online.');

        return ['title' => $title, 'description' => $description, 'routeTitle' => $routeTitle, 'routeDescription' => $routeDescription];
    }

    /**
     * Matches $path against the route patterns in $routes (keys with dynamic
     * segments normalized to '*', e.g. 'users/*\/edit'), returning the entry
     * for the most specific match (fewest wildcards) — an exact literal
     * match always wins over a wildcard one. Returns null if nothing matches.
     *
     * @param  array<string, array{title: string, description: string}>  $routes
     * @return array{title: string, description: string}|null
     */
    private function matchRoute(array $routes, string $path): ?array
    {
        $segments = explode('/', trim($path, '/'));
        $best = null;
        $bestSpecificity = -1;

        foreach ($routes as $pattern => $entry) {
            // Purely-numeric keys ('404') come back as int, not string — PHP's
            // own array-key coercion, unrelated to the data.
            $patternSegments = explode('/', (string) $pattern);
            if (count($patternSegments) !== count($segments)) {
                continue;
            }

            $wildcards = 0;
            $matches = true;
            foreach ($patternSegments as $i => $patternSegment) {
                if ($patternSegment === '*') {
                    $wildcards++;

                    continue;
                }
                if ($patternSegment !== $segments[$i]) {
                    $matches = false;

                    break;
                }
            }

            if ($matches) {
                $specificity = count($patternSegments) - $wildcards;
                if ($specificity > $bestSpecificity) {
                    $bestSpecificity = $specificity;
                    $best = $entry;
                }
            }
        }

        return $best;
    }

    /**
     * $textOrKey is either a lang key (translated via trans() if it exists)
     * or literal text (returned exactly as written if it doesn't).
     */
    private function resolveText(string $textOrKey): string
    {
        if (! trans()->has($textOrKey)) {
            return $textOrKey;
        }

        $translated = __($textOrKey);

        return is_string($translated) ? $translated : $textOrKey;
    }

    /**
     * @return array{title:string,description:string,robots:string,canonical:string,image:?string,og_title:string,og_description:string}
     */
    private function assemble(string $title, string $description, string $robots, string $path, ?string $image, string $ogTitle, string $ogDescription): array
    {
        return [
            'title' => $title,
            'description' => $description,
            'robots' => $robots,
            'canonical' => $this->canonicalUrl($path),
            'image' => $image,
            'og_title' => $ogTitle,
            'og_description' => $ogDescription,
        ];
    }

    /**
     * Resolves a page/group's own Open Graph image, falling back to the
     * General default (Settings → SEO) when it has none of its own.
     */
    private function resolveImage(?string $filename): ?string
    {
        if ($filename) {
            return Attach::getUrlPath('images/'.$filename);
        }

        return $this->formatter->generalOgImageUrl();
    }

    private function canonicalUrl(string $path): string
    {
        return $path === '' ? url('/') : url('/'.$path);
    }

    private function setting(): ?Setting
    {
        if (! $this->setLoaded) {
            $this->set = Setting::find(1);
            $this->setLoaded = true;
        }

        return $this->set;
    }

    /**
     * Batches all 4 default-page rows in one query instead of one per key —
     * resolveClientRoutes() resolves all 4 every request.
     */
    private function defaultPageRow(string $key): ?SeoDefaultPage
    {
        $this->defaultPages ??= SeoDefaultPage::whereIn('page_key', self::DEFAULT_PAGE_KEYS)->get()->keyBy('page_key')->all();

        return $this->defaultPages[$key] ?? null;
    }

    /**
     * @return array<string, array{title: string, description: string}>
     */
    private function clientRoutes(): array
    {
        return $this->clientRoutesCache ??= require __DIR__.'/client_routes.php';
    }

    /**
     * @return array<string, array{title: string, description: string}>
     */
    private function adminRoutes(): array
    {
        return $this->adminRoutesCache ??= require __DIR__.'/admin_routes.php';
    }
}
