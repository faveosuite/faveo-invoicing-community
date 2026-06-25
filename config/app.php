<?php

use App\BillingLog\LaravelLogViewerServiceProvider;
use App\Facades\Log;
use App\License\LicenseServiceProvider;
use App\Plugins\Mailchimp\MailchimpServiceProvider;
use App\Plugins\Recaptcha\RecaptchaServiceProvider;
use App\Plugins\Stripe\ServiceProvider;
use App\Plugins\Zoho\Providers\ZohoServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\AttachmentHelperServiceProvider;
use App\Providers\ConfigServiceProvider;
use App\Providers\CustomValidationProvider;
use App\Providers\EventServiceProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\ImageUploadHelperServiceProvider;
use App\Providers\LogServiceProvider;
use App\Providers\RouteServiceProvider;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Creativeorange\Gravatar\Facades\Gravatar;
use Creativeorange\Gravatar\GravatarServiceProvider;
use Illuminate\Auth\AuthServiceProvider;
use Illuminate\Auth\Passwords\PasswordResetServiceProvider;
use Illuminate\Broadcasting\BroadcastServiceProvider;
use Illuminate\Bus\BusServiceProvider;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Cookie\CookieServiceProvider;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Encryption\EncryptionServiceProvider;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Foundation\Providers\ConsoleSupportServiceProvider;
use Illuminate\Foundation\Providers\FoundationServiceProvider;
use Illuminate\Hashing\HashServiceProvider;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Notifications\NotificationServiceProvider;
use Illuminate\Pagination\PaginationServiceProvider;
use Illuminate\Pipeline\PipelineServiceProvider;
use Illuminate\Queue\QueueServiceProvider;
use Illuminate\Redis\RedisServiceProvider;
use Illuminate\Session\SessionServiceProvider;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Request;
use Illuminate\Translation\TranslationServiceProvider;
use Illuminate\Validation\ValidationServiceProvider;
use Illuminate\View\ViewServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\SocialiteServiceProvider;
use Laravel\Tinker\TinkerServiceProvider;
use Maatwebsite\Excel\ExcelServiceProvider;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\Activitylog\Facades\Activity;
use Spatie\Html\Facades\Html;
use Spatie\Html\HtmlServiceProvider;
use Spatie\Referer\RefererServiceProvider;
use Torann\GeoIP\Facades\GeoIP;
use Torann\GeoIP\GeoIPServiceProvider;

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

    'version' => 'v4.0.2.6',

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

    'debug' => env('APP_DEBUG', default: false),

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

'App\Plugins\Stripe\ServiceProvider',//
    | This key is used by the Illuminate encrypter service and should be set
//

    */

    'key' => env('APP_KEY', 'base64:G4WSQduFNvk9rYtoLS1ozg=='),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', 'base64:G4WSQduFNvk9rYtoLS1ozg=='))
        ),
    ],

    'cipher' => 'AES-128-CBC',

    /*
      |---------------------------------------------------------------------------------
      | Bugsnag error reporting
      |-----------------------------------------------------------------------------------
      |Accepts true or false as a value. It decides whether to send the error
      |to AGORA developers  when any exception/error occurs or not. True value of this variable will
      |allow application to send error reports to AGORA team's bugsnag log.
     */
    'sentry_reporting' => env('APP_SENTRY', default: true),
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

        ServiceProvider::class,
        App\Plugins\Razorpay\ServiceProvider::class,
        /*
         * Laravel Framework Service Providers...
         */
        AuthServiceProvider::class,
        BroadcastServiceProvider::class,
        BusServiceProvider::class,
        CacheServiceProvider::class,
        ConsoleSupportServiceProvider::class,
        CookieServiceProvider::class,
        DatabaseServiceProvider::class,
        EncryptionServiceProvider::class,
        FilesystemServiceProvider::class,
        FoundationServiceProvider::class,
        HashServiceProvider::class,
        MailServiceProvider::class,
        NotificationServiceProvider::class,
        PaginationServiceProvider::class,
        PipelineServiceProvider::class,
        QueueServiceProvider::class,
        RedisServiceProvider::class,
        PasswordResetServiceProvider::class,
        SessionServiceProvider::class,
        TranslationServiceProvider::class,
        ValidationServiceProvider::class,
        ViewServiceProvider::class,
        GeoIPServiceProvider::class,
        /*
         * Package Service Providers...
         */
        TinkerServiceProvider::class,
        IdeHelperServiceProvider::class,

        /*
         * Application Service Providers...
         */
        AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        EventServiceProvider::class,
        HorizonServiceProvider::class,
        RouteServiceProvider::class,
        CustomValidationProvider::class,
        LicenseServiceProvider::class,
        // Illuminate\Support\Facades\Input::class,
        ActivitylogServiceProvider::class,
        RefererServiceProvider::class,
        PragmaRX\Google2FALaravel\ServiceProvider::class,
        // Voerro\Laravel\VisitorTracker\VisitorTrackerServiceProvider::class,
        GravatarServiceProvider::class,
        // Symfony\Component\Mailer\MailerInterface::class,
        ImageUploadHelperServiceProvider::class,
        SocialiteServiceProvider::class,
        ExcelServiceProvider::class,
        AttachmentHelperServiceProvider::class,
        HtmlServiceProvider::class,
        LaravelLogViewerServiceProvider::class,
        LogServiceProvider::class,
        RecaptchaServiceProvider::class,
        ZohoServiceProvider::class,
        MailchimpServiceProvider::class,
        ConfigServiceProvider::class,
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

        'Activity' => Activity::class,

        'GeoIP' => GeoIP::class,
        'Google2FA' => PragmaRX\Google2FALaravel\Facade::class,
        'Html' => Html::class,
        'Input' => Request::class,
        'Redis' => Redis::class,
        'Gravatar' => Gravatar::class,
        'Socialite' => Socialite::class,
        'Excel' => Excel::class,
        'Logger' => Log::class,

    ])->toArray(),

];
