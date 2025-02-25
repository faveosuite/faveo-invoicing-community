<?php

use Illuminate\Support\Facades\Facade;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    */

    'name' => env('APP_NAME', 'Laravel'),

    'version' => 'v4.0.2.3',

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services your application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', true),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------

'App\Plugins\Stripe\ServiceProvider',
'App\Plugins\Razorpay\ServiceProvider',//////
    | This key is used by the Illuminate encrypter service and should be set
//

    */

    'key' => 'SomeRandomString',
    'cipher' => 'AES-128-CBC',

    /*
      |---------------------------------------------------------------------------------
      | Bugsnag error reporting
      |-----------------------------------------------------------------------------------
      |Accepts true or false as a value. It decides whether to send the error
      |to AGORA developers  when any exception/error occurs or not. True value of this variable will
      |allow application to send error reports to AGORA team's bugsnag log.
     */
    'bugsnag_reporting' => env('APP_BUGSNAG', true),
    /*

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log settings for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Settings: "single", "daily", "syslog", "errorlog"
    |
    */

    // 'log' => env('APP_LOG', 'daily'),

    // 'log_level' => env('APP_LOG_LEVEL', 'debug'),

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        //

        App\Plugins\Stripe\ServiceProvider::class,
        App\Plugins\Razorpay\ServiceProvider::class,
        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        Bugsnag\BugsnagLaravel\BugsnagServiceProvider::class,
        Arcanedev\LogViewer\LogViewerServiceProvider::class,
        Torann\GeoIP\GeoIPServiceProvider::class,
        /*
         * Package Service Providers...
         */
        Laravel\Tinker\TinkerServiceProvider::class,

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\HorizonServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\CustomValidationProvider::class,

        Collective\Html\HtmlServiceProvider::class,
        Barryvdh\DomPDF\ServiceProvider::class,
        // Illuminate\Support\Facades\Input::class,

        Yajra\DataTables\HtmlServiceProvider::class,
        Yajra\DataTables\DataTablesServiceProvider::class,
        Spatie\Activitylog\ActivitylogServiceProvider::class,
        \Torann\Currency\CurrencyServiceProvider::class,
        Devio\Pipedrive\PipedriveServiceProvider::class,
        Spatie\Referer\RefererServiceProvider::class,
        Cartalyst\Stripe\Laravel\StripeServiceProvider::class,
        PragmaRX\Google2FALaravel\ServiceProvider::class,
        Darryldecode\Cart\CartServiceProvider::class,
        // Voerro\Laravel\VisitorTracker\VisitorTrackerServiceProvider::class,
        Creativeorange\Gravatar\GravatarServiceProvider::class,
        // Symfony\Component\Mailer\MailerInterface::class,
        GrahamCampbell\Markdown\MarkdownServiceProvider::class,
        App\Providers\ImageUploadHelperServiceProvider::class,
        Laravel\Socialite\SocialiteServiceProvider::class,
        Maatwebsite\Excel\ExcelServiceProvider::class,
        \App\Providers\AttachmentHelperServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => Facade::defaultAliases()->merge([
        'Activity' => Spatie\Activitylog\ActivitylogFacade::class,
        'Bugsnag' => Bugsnag\BugsnagLaravel\Facades\Bugsnag::class,
        'Cart' => Darryldecode\Cart\Facades\CartFacade::class,
        'Currency' => \Torann\Currency\Facades\Currency::class,
        'DataTables' => Yajra\DataTables\Facades\DataTables::class,
        'Form' => Collective\Html\FormFacade::class,
        'GeoIP' => \Torann\GeoIP\Facades\GeoIP::class,
        'Google2FA' => PragmaRX\Google2FALaravel\Facade::class,
        'HTML' => Collective\Html\HtmlFacade::class,
        'Input' => Illuminate\Support\Facades\Input::class,
        'PDF' => Barryvdh\DomPDF\Facade::class,
        'Pipedrive' => Devio\Pipedrive\PipedriveFacade::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Stripe' => Cartalyst\Stripe\Laravel\Facades\Stripe::class,
        'Gravatar' => Creativeorange\Gravatar\Facades\Gravatar::class,
        'Markdown' => GrahamCampbell\Markdown\Facades\Markdown::class,
        'Socialite' => Laravel\Socialite\Facades\Socialite::class,
        'Excel' => Maatwebsite\Excel\Facades\Excel::class,

    ])->toArray(),

];
