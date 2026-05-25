<?php

/*
|--------------------------------------------------------------------------
| CSS and JS link in our billing
|--------------------------------------------------------------------------
 */

return [

    'image' => [
    ],

    'css' => [
        'bootstrap'         => 'themes/default/common/bootstrap/css/bootstrap.min.css',
        'fontawesome'       => 'themes/default/common/fontawesome/css/all.min.css',
        'AdminLTE'          => 'themes/default/admin/adminlte/css/adminlte.css',
        'overlayscrollbars' => 'themes/default/common/overlayscrollbars/css/overlayscrollbars.min.css',
        'apexcharts'        => 'themes/default/common/apexcharts/css/apexcharts.css',
        'jsvectormap'       => 'themes/default/common/jsvectormap/css/jsvectormap.min.css',

        // Porto client theme — Porto-specific vendor libs
        'porto-animate'      => 'themes/default/client/porto/vendor/animate/animate.compat.css',
        'porto-simple-icons' => 'themes/default/client/porto/vendor/simple-line-icons/css/simple-line-icons.min.css',
        'porto-owl'          => 'themes/default/client/porto/vendor/owl.carousel/assets/owl.carousel.min.css',
        'porto-owl-theme'    => 'themes/default/client/porto/vendor/owl.carousel/assets/owl.theme.default.min.css',
        'porto-magnific'     => 'themes/default/client/porto/vendor/magnific-popup/magnific-popup.min.css',
        // bootstrap + fontawesome reuse common/ keys (no duplication)
        'porto-theme'    => 'themes/default/client/porto/css/theme.css',
        'porto-elements' => 'themes/default/client/porto/css/theme-elements.css',
        'porto-custom'   => 'themes/default/client/porto/css/custom.css',
    ],
    'js' => [
        'bootstrap'         => 'themes/default/common/bootstrap/js/bootstrap.bundle.min.js',
        'overlayscrollbars' => 'themes/default/common/overlayscrollbars/js/overlayscrollbars.browser.es6.min.js',
        'AdminLTE'          => 'themes/default/admin/adminlte/js/adminlte.min.js',
        'apexcharts'        => 'themes/default/common/apexcharts/js/apexcharts.min.js',
        'jsvectormap'       => 'themes/default/common/jsvectormap/js/jsvectormap.min.js',
        'jsvectormap-world' => 'themes/default/common/jsvectormap/js/world.js',
        'sortablejs'        => 'themes/default/common/sortablejs/js/Sortable.min.js',

        // Porto client theme — paths match original Porto folder structure
        'porto-modernizr'  => 'themes/default/client/porto/vendor/animated-headline/js/modernizr.js',
        'porto-theme'      => 'themes/default/client/porto/js/theme.js',
        'porto-theme-init' => 'themes/default/client/porto/js/theme.init.js',
    ],

];
