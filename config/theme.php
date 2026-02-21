<?php

$theme  = 'adminlte';
$useCdn = false;

$cdnBase = env('CDN_URL', 'https://your-cdn-server.com');

$themes = [
    'adminlte' => [
        'local_css' => [
            "themes/adminlte/plugins/overlayscrollbars/overlayscrollbars.min.css",
            "themes/adminlte/plugins/bootstrap-icons/bootstrap-icons.min.css",
            "themes/adminlte/plugins/apexcharts/apexcharts.css",
            "themes/adminlte/plugins/jsvectormap/jsvectormap.min.css",
            "themes/adminlte/css/adminlte.min.css",
        ],
        'local_js' => [
            "themes/adminlte/plugins/popperjs/popper.min.js",
            "themes/adminlte/plugins/bootstrap/bootstrap.min.js",
            "themes/adminlte/plugins/overlayscrollbars/overlayscrollbars.browser.es6.min.js",
            "themes/adminlte/plugins/sortablejs/Sortable.min.js",
            "themes/adminlte/plugins/apexcharts/apexcharts.min.js",
            "themes/adminlte/plugins/jsvectormap/jsvectormap.min.js",
            "themes/adminlte/plugins/jsvectormap/world.js",
            "themes/adminlte/js/adminlte.min.js",
        ],
        'cdn_css' => [
            "{$cdnBase}/themes/adminlte/plugins/overlayscrollbars/overlayscrollbars.min.css",
            "{$cdnBase}/themes/adminlte/plugins/bootstrap-icons/bootstrap-icons.min.css",
            "{$cdnBase}/themes/adminlte/plugins/apexcharts/apexcharts.css",
            "{$cdnBase}/themes/adminlte/plugins/jsvectormap/jsvectormap.min.css",
            "{$cdnBase}/themes/adminlte/css/adminlte.min.css",
        ],
        'cdn_js' => [
            "{$cdnBase}/themes/adminlte/plugins/popperjs/popper.min.js",
            "{$cdnBase}/themes/adminlte/plugins/bootstrap/bootstrap.min.js",
            "{$cdnBase}/themes/adminlte/plugins/overlayscrollbars/overlayscrollbars.browser.es6.min.js",
            "{$cdnBase}/themes/adminlte/plugins/sortablejs/Sortable.min.js",
            "{$cdnBase}/themes/adminlte/plugins/apexcharts/apexcharts.min.js",
            "{$cdnBase}/themes/adminlte/plugins/jsvectormap/jsvectormap.min.js",
            "{$cdnBase}/themes/adminlte/plugins/jsvectormap/world.js",
            "{$cdnBase}/themes/adminlte/js/adminlte.min.js",
        ],
    ],
    'theme2' => [
        'local_css' => ["themes/theme2/css/theme2.min.css"],
        'local_js'  => ["themes/theme2/js/theme2.min.js"],
        'cdn_css'   => ["{$cdnBase}/themes/theme2/css/theme2.min.css"],
        'cdn_js'    => ["{$cdnBase}/themes/theme2/js/theme2.min.js"],
    ],
];

return [
    'active'  => $theme,
    'use_cdn' => $useCdn,
    'css'     => $useCdn ? ($themes[$theme]['cdn_css'] ?? []) : ($themes[$theme]['local_css'] ?? []),
    'js'      => $useCdn ? ($themes[$theme]['cdn_js'] ?? []) : ($themes[$theme]['local_js'] ?? []),
    'images'  => $useCdn ? "{$cdnBase}/themes/common/images" : "themes/common/images",
];
