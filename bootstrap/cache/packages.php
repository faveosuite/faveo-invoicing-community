<?php

return  [
    'anhskohbo/no-captcha' => [
        'providers' => [
            0 => 'Anhskohbo\\NoCaptcha\\NoCaptchaServiceProvider',
        ],
        'aliases' => [
            'NoCaptcha' => 'Anhskohbo\\NoCaptcha\\Facades\\NoCaptcha',
        ],
    ],
    'arcanedev/log-viewer' => [
        'providers' => [
            0 => 'Arcanedev\\LogViewer\\LogViewerServiceProvider',
            1 => 'Arcanedev\\LogViewer\\Providers\\DeferredServicesProvider',
        ],
    ],
    'barryvdh/laravel-debugbar' => [
        'providers' => [
            0 => 'Barryvdh\\Debugbar\\ServiceProvider',
        ],
        'aliases' => [
            'Debugbar' => 'Barryvdh\\Debugbar\\Facades\\Debugbar',
        ],
    ],
    'barryvdh/laravel-dompdf' => [
        'providers' => [
            0 => 'Barryvdh\\DomPDF\\ServiceProvider',
        ],
        'aliases' => [
            'Pdf' => 'Barryvdh\\DomPDF\\Facade\\Pdf',
            'PDF' => 'Barryvdh\\DomPDF\\Facade\\Pdf',
        ],
    ],
    'beyondcode/laravel-query-detector' => [
        'providers' => [
            0 => 'BeyondCode\\QueryDetector\\QueryDetectorServiceProvider',
        ],
    ],
    'cartalyst/stripe-laravel' => [
        'providers' => [
            0 => 'Cartalyst\\Stripe\\Laravel\\StripeServiceProvider',
        ],
        'aliases' => [
            'Stripe' => 'Cartalyst\\Stripe\\Laravel\\Facades\\Stripe',
        ],
    ],
    'coconutcraig/laravel-postmark' => [
        'providers' => [
            0 => 'CraigPaul\\Mail\\PostmarkServiceProvider',
        ],
    ],
    'creativeorange/gravatar' => [
        'providers' => [
            0 => 'Creativeorange\\Gravatar\\GravatarServiceProvider',
        ],
        'aliases' => [
            'Gravatar' => 'Creativeorange\\Gravatar\\Facades\\Gravatar',
        ],
    ],
    'darryldecode/cart' => [
        'providers' => [
            0 => 'Darryldecode\\Cart\\CartServiceProvider',
        ],
        'aliases' => [
            'Cart' => 'Darryldecode\\Cart\\Facades\\CartFacade',
        ],
    ],
    'devio/pipedrive' => [
        'providers' => [
            0 => 'Devio\\Pipedrive\\PipedriveServiceProvider',
        ],
        'aliases' => [
            'Pipedrive' => 'Devio\\Pipedrive\\PipedriveFacade',
        ],
    ],
    'graham-campbell/markdown' => [
        'providers' => [
            0 => 'GrahamCampbell\\Markdown\\MarkdownServiceProvider',
        ],
    ],
    'itsgoingd/clockwork' => [
        'providers' => [
            0 => 'Clockwork\\Support\\Laravel\\ClockworkServiceProvider',
        ],
        'aliases' => [
            'Clockwork' => 'Clockwork\\Support\\Laravel\\Facade',
        ],
    ],
    'laravel/horizon' => [
        'providers' => [
            0 => 'Laravel\\Horizon\\HorizonServiceProvider',
        ],
        'aliases' => [
            'Horizon' => 'Laravel\\Horizon\\Horizon',
        ],
    ],
    'laravel/socialite' => [
        'providers' => [
            0 => 'Laravel\\Socialite\\SocialiteServiceProvider',
        ],
        'aliases' => [
            'Socialite' => 'Laravel\\Socialite\\Facades\\Socialite',
        ],
    ],
    'laravel/tinker' => [
        'providers' => [
            0 => 'Laravel\\Tinker\\TinkerServiceProvider',
        ],
    ],
    'laravel/ui' => [
        'providers' => [
            0 => 'Laravel\\Ui\\UiServiceProvider',
        ],
    ],
    'laravelcollective/html' => [
        'providers' => [
            0 => 'Collective\\Html\\HtmlServiceProvider',
        ],
        'aliases' => [
            'Form' => 'Collective\\Html\\FormFacade',
            'Html' => 'Collective\\Html\\HtmlFacade',
        ],
    ],
    'livewire/livewire' => [
        'providers' => [
            0 => 'Livewire\\LivewireServiceProvider',
        ],
        'aliases' => [
            'Livewire' => 'Livewire\\Livewire',
        ],
    ],
    'maatwebsite/excel' => [
        'providers' => [
            0 => 'Maatwebsite\\Excel\\ExcelServiceProvider',
        ],
        'aliases' => [
            'Excel' => 'Maatwebsite\\Excel\\Facades\\Excel',
        ],
    ],
    'nesbot/carbon' => [
        'providers' => [
            0 => 'Carbon\\Laravel\\ServiceProvider',
        ],
    ],
    'nunomaduro/collision' => [
        'providers' => [
            0 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
        ],
    ],
    'nunomaduro/termwind' => [
        'providers' => [
            0 => 'Termwind\\Laravel\\TermwindServiceProvider',
        ],
    ],
    'pion/laravel-chunk-upload' => [
        'providers' => [
            0 => 'Pion\\Laravel\\ChunkUpload\\Providers\\ChunkUploadServiceProvider',
        ],
    ],
    'pragmarx/google2fa-laravel' => [
        'providers' => [
            0 => 'PragmaRX\\Google2FALaravel\\ServiceProvider',
        ],
        'aliases' => [
            'Google2FA' => 'PragmaRX\\Google2FALaravel\\Facade',
        ],
    ],
    'rachidlaasri/laravel-installer' => [
        'providers' => [
            0 => 'RachidLaasri\\LaravelInstaller\\Providers\\LaravelInstallerServiceProvider',
        ],
    ],
    'shvetsgroup/laravel-email-database-log' => [
        'providers' => [
            0 => 'ShvetsGroup\\LaravelEmailDatabaseLog\\LaravelEmailDatabaseLogServiceProvider',
        ],
    ],
    'simplesoftwareio/simple-qrcode' => [
        'providers' => [
            0 => 'SimpleSoftwareIO\\QrCode\\QrCodeServiceProvider',
        ],
        'aliases' => [
            'QrCode' => 'SimpleSoftwareIO\\QrCode\\Facades\\QrCode',
        ],
    ],
    'spatie/laravel-activitylog' => [
        'providers' => [
            0 => 'Spatie\\Activitylog\\ActivitylogServiceProvider',
        ],
    ],
    'spatie/laravel-ignition' => [
        'providers' => [
            0 => 'Spatie\\LaravelIgnition\\IgnitionServiceProvider',
        ],
        'aliases' => [
            'Flare' => 'Spatie\\LaravelIgnition\\Facades\\Flare',
        ],
    ],
    'spatie/laravel-referer' => [
        'providers' => [
            0 => 'Spatie\\Referer\\RefererServiceProvider',
        ],
    ],
    'torann/currency' => [
        'providers' => [
            0 => 'Torann\\Currency\\CurrencyServiceProvider',
        ],
        'aliases' => [
            'Currency' => 'Torann\\Currency\\Facades\\Currency',
        ],
    ],
    'torann/geoip' => [
        'providers' => [
            0 => 'Torann\\GeoIP\\GeoIPServiceProvider',
        ],
        'aliases' => [
            'GeoIP' => 'Torann\\GeoIP\\Facades\\GeoIP',
        ],
    ],
    'vemcogroup/laravel-sparkpost-driver' => [
        'providers' => [
            0 => 'Vemcogroup\\SparkPostDriver\\SparkPostDriverServiceProvider',
        ],
    ],
    'yajra/laravel-datatables-buttons' => [
        'providers' => [
            0 => 'Yajra\\DataTables\\ButtonsServiceProvider',
        ],
    ],
    'yajra/laravel-datatables-editor' => [
        'providers' => [
            0 => 'Yajra\\DataTables\\EditorServiceProvider',
        ],
    ],
    'yajra/laravel-datatables-export' => [
        'providers' => [
            0 => 'Yajra\\DataTables\\ExportServiceProvider',
        ],
    ],
    'yajra/laravel-datatables-fractal' => [
        'providers' => [
            0 => 'Yajra\\DataTables\\FractalServiceProvider',
        ],
    ],
    'yajra/laravel-datatables-html' => [
        'providers' => [
            0 => 'Yajra\\DataTables\\HtmlServiceProvider',
        ],
    ],
    'yajra/laravel-datatables-oracle' => [
        'providers' => [
            0 => 'Yajra\\DataTables\\DataTablesServiceProvider',
        ],
        'aliases' => [
            'DataTables' => 'Yajra\\DataTables\\Facades\\DataTables',
        ],
    ],
];
