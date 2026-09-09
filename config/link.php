<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CSS and JS link in our billing
|--------------------------------------------------------------------------
*/
return [

    'image' => [
    ],

    'css' => [
        'bootstrap' => 'themes/default/common/bootstrap/css/bootstrap.min.css',
        'bootstrap-rtl' => 'themes/default/common/bootstrap/css/bootstrap.rtl.min.css',
        'fontawesome' => 'themes/default/common/fontawesome/css/all.min.css',
        'AdminLTE' => 'themes/default/admin/adminlte/css/adminlte.css',
        'AdminLTE-rtl' => 'themes/default/admin/adminlte/css/adminlte.rtl.min.css',
        'overlayscrollbars' => 'themes/default/common/overlayscrollbars/css/overlayscrollbars.min.css',
        'apexcharts' => 'themes/default/common/apexcharts/css/apexcharts.css',
        'jsvectormap' => 'themes/default/common/jsvectormap/css/jsvectormap.min.css',

        // Porto client theme — Porto-specific vendor libs
        'porto-animate' => 'themes/default/client/porto/vendor/animate/animate.compat.css',
        'porto-simple-icons' => 'themes/default/client/porto/vendor/simple-line-icons/css/simple-line-icons.min.css',
        'porto-owl' => 'themes/default/client/porto/vendor/owl.carousel/assets/owl.carousel.min.css',
        'porto-owl-theme' => 'themes/default/client/porto/vendor/owl.carousel/assets/owl.theme.default.min.css',
        'porto-magnific' => 'themes/default/client/porto/vendor/magnific-popup/magnific-popup.min.css',
        // bootstrap + fontawesome reuse common/ keys (no duplication)
        'porto-theme' => 'themes/default/client/porto/css/theme.css',
        'porto-theme-rtl' => 'themes/default/client/porto/css/rtl-theme.css',
        'porto-elements' => 'themes/default/client/porto/css/theme-elements.css',
        'porto-elements-rtl' => 'themes/default/client/porto/css/rtl-theme-elements.css',
        'porto-blog' => 'themes/default/client/porto/css/theme-blog.css',
        'porto-blog-rtl' => 'themes/default/client/porto/css/rtl-theme-blog.css',
        'porto-shop' => 'themes/default/client/porto/css/theme-shop.css',
        'porto-shop-rtl' => 'themes/default/client/porto/css/rtl-theme-shop.css',
        'porto-skin' => 'themes/default/client/porto/css/skins/default.css',
        'porto-custom' => 'themes/default/client/porto/css/custom.css',
    ],
    'js' => [
        'bootstrap' => 'themes/default/common/bootstrap/js/bootstrap.bundle.min.js',
        'overlayscrollbars' => 'themes/default/common/overlayscrollbars/js/overlayscrollbars.browser.es6.min.js',
        'AdminLTE' => 'themes/default/admin/adminlte/js/adminlte.min.js',
        'apexcharts' => 'themes/default/common/apexcharts/js/apexcharts.min.js',
        'jsvectormap' => 'themes/default/common/jsvectormap/js/jsvectormap.min.js',
        'jsvectormap-world' => 'themes/default/common/jsvectormap/js/world.js',
        'sortablejs' => 'themes/default/common/sortablejs/js/Sortable.min.js',

        // Porto client theme — paths match original Porto folder structure
        'porto-modernizr' => 'themes/default/client/porto/vendor/animated-headline/js/modernizr.js',
        'porto-plugins' => 'themes/default/client/porto/js/plugins.min.js',
        'porto-theme' => 'themes/default/client/porto/js/theme.js',
        'porto-theme-init' => 'themes/default/client/porto/js/theme.init.js',
    ],

];
