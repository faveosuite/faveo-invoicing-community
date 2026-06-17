<?php

declare(strict_types=1);

$theme = 'adminlte';
$useCdn = false;
$cdnBase = rtrim((string) env('CDN_URL', 'https://your-cdn-server.com'), '/');

// Named asset registry — keyed by alias, resolved to local path or CDN URL at runtime.
// Add new assets here; reference them by alias in admin.blade.php.
$themes = [
    'adminlte' => [
        // ── CSS ────────────────────────────────────────────────────────────────
        'bootstrap-icons-css' => 'themes/adminlte/plugins/bootstrap-icons/bootstrap-icons.min.css',
        'adminlte-css' => 'themes/adminlte/css/adminlte.min.css',
        'fontawesome-css' => 'themes/common/fonts/fontawesome/css/all.min.css',
        'flag-icons-css' => 'themes/common/flag-icons/css/flag-icons.min.css',
        // async (non-critical — only used on dashboard / reports)
        'overlayscrollbars-css' => 'themes/adminlte/plugins/overlayscrollbars/overlayscrollbars.min.css',
        'apexcharts-css' => 'themes/adminlte/plugins/apexcharts/apexcharts.css',
        'jsvectormap-css' => 'themes/adminlte/plugins/jsvectormap/jsvectormap.min.css',

        // ── JS ─────────────────────────────────────────────────────────────────
        'popper-js' => 'themes/adminlte/plugins/popperjs/popper.min.js',
        'bootstrap-js' => 'themes/adminlte/plugins/bootstrap/bootstrap.min.js',
        'overlayscrollbars-js' => 'themes/adminlte/plugins/overlayscrollbars/overlayscrollbars.browser.es6.min.js',
        'adminlte-js' => 'themes/adminlte/js/adminlte.min.js',
        // deferred (page-specific heavy libs)
        'sortable-js' => 'themes/adminlte/plugins/sortablejs/Sortable.min.js',
        'apexcharts-js' => 'themes/adminlte/plugins/apexcharts/apexcharts.min.js',
        'jsvectormap-js' => 'themes/adminlte/plugins/jsvectormap/jsvectormap.min.js',
        'jsvectormap-world' => 'themes/adminlte/plugins/jsvectormap/world.js',
    ],
    'theme2' => [
        'theme2-css' => 'themes/theme2/css/theme2.min.css',
        'theme2-js' => 'themes/theme2/js/theme2.min.js',
    ],
];

return [
    'active' => $theme,
    'use_cdn' => $useCdn,
    'cdn_host' => $useCdn ? (parse_url($cdnBase, PHP_URL_SCHEME).'://'.parse_url($cdnBase, PHP_URL_HOST)) : null,
    'cdn_base' => $cdnBase,
    'assets' => $themes[$theme] ?? [],
];
