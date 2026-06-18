<?php

use App\CloudPopUp;
use App\FileSystemSettings;
use App\Http\Controllers\Common\PaymentSettingsController;
use App\License\Models\Installation;
use App\Model\Common\CommonSettings;
use App\Model\Common\Country;
use App\Model\Common\FaveoCloud;
use App\Model\Common\Setting;
use App\Model\Common\State;
use App\Model\Common\Timezone;
use App\Model\Payment\Currency;
use App\Model\Payment\PlanPrice;
use App\Model\Payment\TaxOption;
use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\Traits\TaxCalculation;
use App\User;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use GuzzleHttp\Client;
use Illuminate\Cache\ArrayStore;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Sleep;
use Spatie\Activitylog\Support\ActivityLogger;
use Spatie\Activitylog\Support\ActivityLogStatus;

function getLocation(?string $ip = null): mixed
{
    try {
        return GeoIP::getLocation($ip);
    } catch (Exception $exception) {
        Logger::exception($exception);

        return Config::get('geoip.default_location');
    }
}

function checkArray(string $key, array $array): mixed
{
    if (is_array($array) && array_key_exists($key, $array)) { // @phpstan-ignore function.alreadyNarrowedType
        return $array[$key];
    }

    return '';
}

function mime(string $type): ?string
{
    if (in_array($type, ['jpg', 'png', 'jpeg', 'gif']) ||
        \Illuminate\Support\Str::startsWith($type, 'image')) {
        return 'image';
    }

    return null;
}

function isInstall(): bool
{
    $check = false;
    $env = base_path('.env');
    if (File::exists($env) && config('custom.db_install') == 1) {
        return true;
    }

    return $check;
}

// For API response
/**
 * Format the error message into json error response.
 *
 * @param  string|array  $message  Error message
 * @param  int  $statusCode
 * @return JsonResponse json response
 */
function errorResponse(string|array $message, int $statusCode = 400): JsonResponse
{
    return response()->json(['success' => false, 'message' => $message], $statusCode);
}

/**
 * Format success message/data into json success response.
 *
 * @param  string  $message  Success message
 * @param  mixed  $data  Data of the response
 * @param  int  $statusCode
 * @return \Illuminate\Http\JsonResponse
 */
function successResponse(string $message = '', mixed $data = '', int $statusCode = 200): JsonResponse
{
    $response = ['success' => true];

    // if message given
    if (! empty($message)) {
        $response['message'] = $message;
    }

    // If data given
    if (! empty($data)) {
        $response['data'] = $data;
    }

    return response()->json($response, $statusCode);
}

/**
 * Gets time in logged in user's timezone.
 *
 * @param  string  $format
 */
function getTimeInLoggedInUserTimeZone(string $dateTimeString, ?string $format = null): string
{
    try {
        $date = new DateTime($dateTimeString, new DateTimeZone('UTC'));

        $user = Auth::user();
        $cacheKey = 'user_timezone_'.(Auth::id() ?? session()->getId());

        $tz = Cache::remember(
            $cacheKey,
            5,
            fn () => $user->timezone->name ?? systemTimezone() // nosemgrep: php.lang.security.exec-use.exec-use
        );

        try {
            $timezone = new DateTimeZone($tz);
        } catch (Exception) {
            $timezone = new DateTimeZone('UTC');
        }

        return $date->setTimezone($timezone)->format($format ?? systemDateTimeFormat()); // nosemgrep: php.lang.security.exec-use.exec-use
    } catch (Exception) {
        return $dateTimeString;
    }
}

/**
 * Returns the system-level date+time format string from settings (cached).
 */
function systemDateTimeFormat(): string
{
    return Cache::remember('system_datetime_format', 60, function (): string {
        $setting = Setting::select('date_format', 'time_format')->first();

        return ($setting->date_format ?? 'd/m/Y').' '.($setting->time_format ?? 'H:i');
    });
}

/**
 * Returns the system-level timezone name from settings (cached).
 */
function systemTimezone(): string
{
    return Cache::remember('system_timezone', 60, function () {
        $setting = Setting::with('timezone:id,name')->select('timezone_id')->first();

        return $setting->timezone->name ?? 'UTC';
    });
}

/**
 * Gets date in a formatted HTML.
 */
function getDateHtml(?string $dateTimeString = null): string
{
    try {
        if (! $dateTimeString) {
            return '--';
        }

        $date = getTimeInLoggedInUserTimeZone($dateTimeString, 'M j, Y');
        $dateTime = getTimeInLoggedInUserTimeZone($dateTimeString);

        return "<label data-toggle='tooltip'style='font-weight:500; margin: 0px' data-placement='top' title='".$dateTime."'>".$date.'</label>';
    } catch (Exception) {
        return '--';
    }
}

function getDateHtmlcopy(?string $dateTimeString = null): string
{
    try {
        if (! $dateTimeString) {
            return '--';
        }

        $date = getTimeInLoggedInUserTimeZone($dateTimeString, 'M j, Y');
        $dateTime = getTimeInLoggedInUserTimeZone($dateTimeString);

        return "<label data-toggle='tooltip' style='font-weight:500; margin: 0px' data-placement='top' title='".$dateTimeString."'>".$date.'</label>';
    } catch (Exception) {
        return '--';
    }
}

function getExpiryLabel(string $expiryDate, string $badge = 'badge'): array
{
    $expiry = Date::parse($expiryDate);
    $now = Date::now();

    return [
        'date' => $expiry,
        'status' => $expiry->lt($now) ? __('message.expired') : null,
    ];
}

function getVersionAndLabel(mixed $productVersion, string $productId, ?string $path = null): mixed
{
    // Get latest version from cache
    $latestVersion = Cache::remember('latest_'.$productId, 10, fn () => ProductUpload::where('product_id', $productId)->latest()->value('version'));

    // Fallback to installation detail if version not provided
    if (! $productVersion && $path) {
        $installationDetail = Installation::where('installation_path', 'like', '%'.$path.'%')->latest('id')->first();
        $productVersion = $installationDetail ? $installationDetail->version : $latestVersion;
    }

    // Return version value or '--' if not available
    return $productVersion ?? $latestVersion ?? null;
}

function getInstallationDetail(string $ip): ?Installation
{
    return Installation::where('installation_path', 'like', '%'.$ip.'%')->first();
}

function tooltip(string $tootipText = ''): string
{
    return '<label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title='.$tootipText.'>
             </label>';
}

function getStatusLabel(mixed $status): string|array
{
    return match ($status) {
        'Success' => __('message.paid'),
        'Pending' => __('message.unpaid'),
        'renewed' => __('message.renewed'),
        default => __('message.partially_paid'),
    };
}

function getCountryByCode(string $code): ?string
{
    try {
        $country = Country::where('country_code_char2', $code)->first();
        if ($country) {
            return $country->country_name;
        }
    } catch (Exception $exception) {
        throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
    }

    return null;
}

function findCountryByGeoip(string $iso): string
{
    try {
        $country = Country::where('country_code_char2', $iso)->first();
        if ($country) {
            return $country->country_code_char2;
        }

        return '';
    } catch (Exception $exception) {
        throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
    }
}

function findStateByRegionId(string $iso): array
{
    try {
        return State::where('country_code', $iso)
            ->pluck('state_subdivision_name', 'iso2')->toArray();
    } catch (Exception $exception) {
        throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
    }
}

function getTimezoneByName(string $name): string
{
    try {
        $timezone = Timezone::where('name', $name)->first();
        if ($timezone) {
            return (string) $timezone->id;
        }

        return '114';
    } catch (Exception $exception) {
        throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
    }
}

function checkPlanSession(): bool
{
    try {
        return (bool) Session::has('plan');
    } catch (Exception $exception) {
        throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
    }
}

function getStateByCode(string $country, string $state): array
{
    try {
        $result = ['id' => '', 'name' => ''];

        $subregion = State::where('country_code', $country)
            ->where('iso2', $state)
            ->first();

        if ($subregion) {
            return [
                'id' => $subregion->iso2,
                'name' => $subregion->state_subdivision_name,
            ];
        }

        return $result;
    } catch (Exception $exception) {
        throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
    }
}

function userCurrencyAndPrice(mixed $userid, mixed $plan, string $productid = ''): array
{
    try {
        $country = getCountry($userid);

        if (! $country) {
            throw new Exception(Lang::get('message.country_notfound'));
        }

        $currencyAndSymbol = getCurrencySymbolAndPriceForPlans($country, $plan);

        return [
            'currency' => $currencyAndSymbol['currency'],
            'symbol' => $currencyAndSymbol['currency_symbol'],
            'plan' => $currencyAndSymbol['userPlan'],
        ];
    } catch (Exception) {
        return [
            'currency' => '',
            'symbol' => '',
            'plan' => '',
        ];
    }
}

function getCountry(mixed $userid): mixed
{
    if (Auth::check() && empty($userid)) {
        return Auth::user()->country;
    }

    if ($userid) {
        return User::where('id', $userid)->value('country');
    }

    $location = cache()->remember('user_location', 60, fn () => getLocation());

    return $location['iso_code'] ? findCountryByGeoip($location['iso_code']) : null;
}

/**
 * Fetches currency and price for a plan. If the country code sent has a price defined for them in a plan then
 * that price will be displayed in the respective currency of that country else the default price for that plan will be displayed along with the default currency.
 *
 * @param  string  $countryCode  Code of the country
 * @param  mixed  $plan  Plan for which price is to be fetched
 * @return array Currency, symbol and plan details
 */
function getCurrencySymbolAndPriceForPlans(string $countryCode, mixed $plan): array
{
    $userCurrency = getCurrencyForClient($countryCode);

    $userPlan = $plan->planPrice->firstWhere('currency', $userCurrency);

    $currency = $userCurrency;
    $currencySymbol = Currency::where('code', $currency)->value('symbol');

    return [
        'currency' => $currency,
        'currency_symbol' => $currencySymbol,
        'userPlan' => $userPlan,
    ];
}

/**
 * Get client currency on the basis of country. This is applicable when client logs in to detect his currency.
 *
 * @param  string  $countryCode  The country code('IN','US')
 * @return string The currency code('INR','USD')
 */
function getCurrencyForClient(string $countryCode): mixed
{
    $country = Country::with(['currency' => function ($query): void {
        $query->where('status', 1);
    }])->where('country_code_char2', $countryCode)->first();

    return $country && isset($country->currency) ? $country->currency->code : Setting::value('default_currency');
}

function currencyFormat(mixed $amount = null, ?string $currency = null, bool $includeSymbol = true, bool $shouldRound = false): mixed
{
    try {
        if ($shouldRound) {
            $amount = rounding($amount);
        }

        $locale = getLocalesByCurrency($currency);
        $precision = getCurrencyPrecision($currency);

        if (! $includeSymbol) {
            return Number::format(
                $amount,
                precision: $precision,
                locale: $locale
            );
        }

        return Number::currency($amount, $currency, $locale);
    } catch (Throwable) {
        return $amount;
    }
}

function getLocalesByCurrency(string $currencyCode): string
{
    return cache()->rememberForever('currency_locale_'.$currencyCode, function () use ($currencyCode) {
        $firstMatch = null;
        foreach (ResourceBundle::getLocales('') as $locale) {
            try {
                $fmt = new NumberFormatter($locale, NumberFormatter::CURRENCY);
                $defaultCurrency = $fmt->getTextAttribute(NumberFormatter::CURRENCY_CODE);
                if ($defaultCurrency === $currencyCode) {
                    if ($firstMatch === null) {
                        $firstMatch = $locale;
                    }

                    if (str_starts_with($locale, 'en_')) {
                        return $locale;
                    }
                }
            } catch (Throwable) {
            }
        }

        return $firstMatch ?? 'en';
    });
}

function getCurrencyPrecision(string $currency): int
{
    $formatter = new NumberFormatter('en', NumberFormatter::CURRENCY);
    $formatter->setTextAttribute(NumberFormatter::CURRENCY_CODE, $currency);

    return $formatter->getAttribute(NumberFormatter::FRACTION_DIGITS);
}

function rounding(mixed $price): ?float
{
    try {
        $tax_rule = new TaxOption();
        $rule = $tax_rule->findOrFail(1);
        $rounding = $rule->rounding;
        if ($rounding) {
            return round($price);
        }

        return round($price, 2);
    } catch (Exception) {
    }

    return null;
}

function userCountryId(): mixed
{
    if (Auth::check()) {
        return Country::where('country_code_char2', Auth::user()->country)->first()->country_id;
    }

    $location = getLocation();

    return Country::where('country_code_char2', $location['iso_code'])->first()->country_id;
}

//function getIndianCurrencySymbol($currency)
//{
//    return \DB::table('format_currencies')->where('code', $currency)->value('symbol');
//}

function getIndianCurrencyFormat(array $number): string
{
    $explrestunits = '';
    $number = explode('.', (string) $number); // @phpstan-ignore cast.string
    $num = $number[0];
    if (strlen($num) > 3) {
        $lastthree = substr($num, strlen($num) - 3, strlen($num));
        $restunits = substr($num, 0, strlen($num) - 3); // extracts the last three digits
        $restunits = (strlen($restunits) % 2 === 1) ? '0'.$restunits : $restunits; // explodes the remaining digits in 2's formats, adds a zero in the beginning to maintain the 2's grouping.
        $expunit = str_split($restunits, 2);
        $counter = count($expunit);
        for ($i = 0; $i < $counter; $i++) {
            // creates each of the 2's group and adds a comma to the end
            if ($i === 0) {
                $explrestunits .= (int) $expunit[$i].','; // if is first value , convert into integer
            } else {
                $explrestunits .= $expunit[$i].',';
            }
        }

        $thecash = $explrestunits.$lastthree;
    } else {
        $thecash = $num;
    }

    if (isset($number[1]) && ($number[1] !== '' && $number[1] !== '0')) {
        if (strlen($number[1]) === 1) {
            return $thecash.'.'.$number[1].'0';
        }

        if (strlen($number[1]) === 2) {
            return $thecash.'.'.$number[1];
        }

        return 'cannot handle decimal values more than two digits...';
    }

    return $thecash;
}

/**
 * Render a single tax for display. Tax is now a generic named rate (no
 * CGST/SGST/IGST split), so this simply formats name@rate and the amount.
 */
function bifurcateTax(string $taxName, string $taxValue, mixed $currency, string $state = '', mixed $price = ''): array
{
    $html = $taxName.'@'.$taxValue;
    $tax_value = currencyFormat(TaxCalculation::taxValue($taxValue, $price), $currency);

    return ['html' => $html, 'tax' => $tax_value];
}

/**
 * Structured tax breakdown for display. One generic entry per tax.
 */
function bifurcate(string $taxName, string $taxValue, mixed $currency, string $state = '', mixed $price = ''): array
{
    return [
        [
            'name' => $taxName,
            'rate' => $taxValue,
            'value' => TaxCalculation::taxValue($taxValue, $price, round: false),
        ],
    ];
}

/**
 * sets mail config and reloads the config into the container
 * NOTE: this is getting used outside the class to set service config.
 */
function setServiceConfig(object $emailConfig): void
{
    $sendingProtocol = $emailConfig->driver;
    if ($sendingProtocol && $sendingProtocol != 'smtp' && $sendingProtocol != 'mail') {
        $services = Config::get('services.'.$sendingProtocol);
        $dynamicServiceConfig = [];

        //loop over it and assign according to the keys given by user
        foreach ($services as $key => $value) {
            $dynamicServiceConfig[$key] = $emailConfig[$key] ?? $value;
        }

        //setting that service configuration
        Config::set('services.'.$sendingProtocol, $dynamicServiceConfig);
    } else {
        Config::set('mail.sendmail', '/usr/sbin/sendmail -t -i -f'.$emailConfig['email']);

        Config::set('mail.host', $emailConfig['host']);
        Config::set('mail.port', $emailConfig['port']);
        Config::set('mail.password', $emailConfig['password']);
        Config::set('mail.security', $emailConfig['encryption']);
    }

    //setting mail driver as $sending protocol
    Config::set('mail.driver', $sendingProtocol);
    Config::set('mail.from.address', $emailConfig['email']);
    Config::set('mail.from.name', $emailConfig['company']);
    Config::set('mail.username', $emailConfig['email']);

    //setting the config again in the service container
    new MailServiceProvider(app())->register();
}

function persistentCache(string $key, Closure $closure, int $noOfSeconds = 30, array $variables = []): mixed
{
    $keySalt = json_encode($variables);

    return Cache::remember($key.$keySalt, $noOfSeconds, $closure);
}

function emailSendingStatus(): bool
{
    return (bool) Setting::value('sending_status');
}

function installationStatusLabel(mixed $installedPath): string
{
    return $installedPath ? "&nbsp;<span class='badge badge-primary' style='background-color:darkcyan !important;' <label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title='".__('message.installation_is_active')."'>
                     </label>".__('message.active').'</span>' : "&nbsp;<span class='badge badge-info' <label data-toggle='tooltip' style='font-weight:500;background-color:crimson;' data-placement='top' title='".__('message.installation_is_inactive')."'>
                    </label>".__('message.inactive').'</span>';
}

//return root url from long url (http://www.domain.com/path/file.php?aa=xx becomes http://www.domain.com/path/), remove scheme, www. and last slash if needed
function getRootUrl(mixed $url, int $remove_scheme, int $remove_www, int $remove_path, int $remove_last_slash): string
{
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $url_array = parse_url((string) $url); //parse URL into arrays like $url_array['scheme'], $url_array['host'], etc

        $url = str_ireplace($url_array['scheme'].'://', '', $url); //make URL without scheme, so no :// is included when searching for first or last /

        if ($remove_path == 1) { //remove everything after FIRST / in URL, so it becomes "real" root URL
            $first_slash_position = stripos($url, '/'); //find FIRST slash - the end of root URL
            if ($first_slash_position > 0) { //cut URL up to FIRST slash
                $url = substr($url, 0, $first_slash_position + 1);
            }
        } else { //remove everything after LAST / in URL, so it becomes "normal" root URL
            $last_slash_position = strripos($url, '/'); //find LAST slash - the end of root URL
            if ($last_slash_position > 0) { //cut URL up to LAST slash
                $url = substr($url, 0, $last_slash_position + 1);
            }
        }

        if ($remove_scheme != 1) { //scheme was already removed, add it again
            $url = $url_array['scheme'].'://'.$url;
        }

        if ($remove_www == 1) { //remove www.
            $url = str_ireplace('www.', '', $url);
        }

        if ($remove_last_slash == 1) { //remove / from the end of URL if it exists
            while (str_ends_with($url, '/')) { //use cycle in case URL already contained multiple // at the end
                $url = substr($url, 0, -1);
            }
        }
    }

    return trim((string) $url);
}

function getContactData(): array
{
    $setting = Setting::first();
    $countryCode = Country::where('country_code_char2', $setting->country)->value('phonecode');
    $logo = '<img style="max-width: 20%;height: auto;" src="'.$setting->logo.'" />';
    $billingContact = '
    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-family: Arial, sans-serif; font-size: 11px; color: #333; padding-left: 25px;">
            <tr>
            <td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0;">BILLING CONTACT</td>
            </tr>
        <tr>
            <td valign="top">
                <p style="line-height: 20px;">'.$setting->company.'<br />
                Email: <a href="mailto:'.$setting->company_email.'">'.$setting->company_email.'</a><br />
                Website: <a href="https://www.faveohelpdesk.com">'.$setting->website.'</a><br />
                Tel: +'.$countryCode.' '.$setting->phone.'</p>
            </td>
        </tr>
    </table>';

    return ['logo' => $logo, 'contact' => $billingContact];
}

function cloudSubDomain(): ?string
{
    $cloudSubDomain = FaveoCloud::find(1);

    return $cloudSubDomain?->cloud_cname;
}

function cloudCentralDomain(): string
{
    $cloudSubDomain = FaveoCloud::find(1);

    return str_replace('https://', '', $cloudSubDomain?->cloud_central_domain);
}

function cloudPopUpDetails(): ?CloudPopUp
{
    return CloudPopUp::find(1);
}

function cloudPopupProducts(): array
{
    return CloudProducts::pluck('cloud_product')->toArray();
}

function getPreReleaseStatusLabel(mixed $status, string $badge = 'badge'): ?string
{
    switch ($status) {
        case 'official':
            return '<span class="'.$badge.' '.$badge.'-success">'.__('message.official_release').'</span>';

        case 'pre_release':
            return '<span class="'.$badge.' '.$badge.'-warning">'.__('message.pre_release').'</span>';

        case 'beta':
            return '<span class="'.$badge.' '.$badge.'-info">'.__('message.beta').'</span>';
    }

    return null;
}

/**
 * Creates an empty DB with given name.
 *
 * @param  string  $dbName  name of the DB
 */
function createDB(string $dbName): mixed
{
    try {
        DB::purge('mysql');
        // removing old db
        DB::connection('mysql')->getPdo()->exec(sprintf('DROP DATABASE IF EXISTS `%s`', $dbName));

        // Creating testing_db
        DB::connection('mysql')->getPdo()->exec(sprintf('CREATE DATABASE `%s`', $dbName));
        //disconnecting it will remove database config from the memory so that new database name can be
        // populated
        DB::disconnect('mysql');
    } catch (Exception $exception) {
        return back()->with('fails', $exception->getMessage());
    }

    return null;
}

function isS3Enabled(): bool
{
    $fileSettings = FileSystemSettings::select('disk')->first();

    return $fileSettings->disk === 's3';
}

/**
 * Update or append key-value pairs in the .env file.
 *
 * This function reads the current environment file, updates existing keys,
 * or appends new ones if they do not exist.
 *
 * @param  array  $data  An associative array where the key is the environment
 *                       variable name, and the value is the new value to set.
 *
 * @throws FileNotFoundException If the .env file is not found.
 */
function setEnvValue(array $data): void
{
    $envFile = app()->environmentFilePath();
    $content = File::get($envFile);

    foreach ($data as $key => $value) {
        // Check if the key exists in the .env file
        if (preg_match(sprintf('/^%s=.*/m', $key), $content)) {
            // Update existing key
            $content = preg_replace(sprintf('/^%s=.*/m', $key), sprintf('%s=%s', $key, $value), $content);
        } else {
            // Append new key-value pair
            $content .= sprintf('%s%s=%s', PHP_EOL, $key, $value);
        }
    }

    // Save the updated .env file
    File::put($envFile, $content);
}

function downloadExternalFile(string $url, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
{
    $client = new Client();
    $response = $client->get($url, ['stream' => true]);

    return response()->stream(function () use ($response): void {
        $stream = $response->getBody();
        while (! $stream->eof()) {
            echo $stream->read(1024);
        }
    }, 200, [
        'Content-Type' => 'application/zip',
        'Content-Disposition' => 'attachment; filename="'.basename((string) $filename).'.zip"',
        'Expires' => 0,
        'Cache-Control' => 'no-cache',
    ]);
}

/**
 * Apply rate limiting based on a unique key and IP address.
 *
 * @param  string  $key  The base key for rate limiting.
 * @param  int  $maxAttempts  Maximum number of allowed attempts.
 * @param  int  $decayMinutes  Time (in minutes) before the rate limit resets.
 * @param  string  $ip  The IP address of the client.
 * @return array Returns an array with rate limit status and remaining time.
 */
function rateLimitForKeyIp(string $key, int $maxAttempts, int $decayMinutes, string $ip): array
{
    $IpKey = $key.':'.$ip;
    $decaySeconds = $decayMinutes * 60;

    // Command 1: Check and handle non-persistent cache.
    if (Cache::getStore() instanceof ArrayStore) {
        return handleArrayStoreRateLimit($IpKey, $maxAttempts, $decaySeconds);
    }

    // Command 2: Handle persistent cache using RateLimiter.
    if (! RateLimiter::attempt($IpKey, $maxAttempts, function (): void {
    }, $decaySeconds)) {
        $remainingTime = RateLimiter::availableIn($IpKey);

        return ['status' => true, 'remainingTime' => formatDuration($remainingTime)];
    }

    return ['status' => false, 'remainingTime' => 0];
}

/**
 * Handle rate limiting for ArrayStore cache driver.
 *
 * @param  string  $IpKey  The unique key for rate limiting.
 * @param  int  $maxAttempts  Maximum number of allowed attempts.
 * @param  int  $decaySeconds  Time (in seconds) before the rate limit resets.
 * @return array Returns an array with rate limit status and remaining time.
 */
function handleArrayStoreRateLimit(string $IpKey, int $maxAttempts, int $decaySeconds): array
{
    $attempts = session()->get($IpKey, 0);
    $lastAttemptTime = session()->get($IpKey.'_time', 0);
    $elapsedTime = time() - $lastAttemptTime;

    // Reset attempts if the decay time has passed.
    if ($elapsedTime > $decaySeconds) {
        session()->put($IpKey, 0);
        session()->put($IpKey.'_time', time());
        $attempts = 0;
    }

    if ($attempts >= $maxAttempts) {
        $remainingTime = $decaySeconds - $elapsedTime;

        return ['status' => true, 'remainingTime' => formatDuration(max($remainingTime, 0))];
    }

    // Increment attempts and update time.
    session()->put($IpKey, $attempts + 1);
    session()->put($IpKey.'_time', time());

    return ['status' => false, 'remainingTime' => 0];
}

/**
 * Convert a time duration in seconds to a human-readable format.
 * If the time exceeds 60 minutes, return both hours and minutes.
 * Otherwise, return minutes only if the duration is below 60 minutes.
 *
 * @param  int  $seconds  The time in seconds.
 * @return string A human-readable time string (hours and minutes or just minutes).
 *
 * @throws Exception
 */
function formatDuration(int $seconds): string
{
    return CarbonInterval::seconds($seconds)
        ->cascade()
        ->forHumans([
            'short' => false,
            'minimumUnit' => 'second',
        ]);
}

function isJson(mixed $string): bool
{
    json_decode((string) $string);

    return json_last_error() === JSON_ERROR_NONE;
}

function getUrl(): string
{
    $protocol = (isset($_SERVER['HTTPS']) && \Illuminate\Support\Facades\Request::server('HTTPS') === 'on') ? 'https' : 'http';
    $host = \Illuminate\Support\Facades\Request::server('HTTP_HOST');
    $path = dirname((string) \Illuminate\Support\Facades\Request::server('SCRIPT_NAME'));

    return $protocol.'://'.$host.$path;
}

/**
 * Check if the current locale is RTL (Right-to-Left) or LTR (Left-to-Right).
 *
 * @return bool True if the locale is RTL, false otherwise.
 */
function isRtlForLang(): bool
{
    return in_array(app()->getLocale(), ['ar', 'he']);
}

/**
 * Honeypot field metadata for JS/SPA clients. Mirrors honeypotField() but returns
 * the parts as data so a Vue component can render the hidden field and submit the
 * expected { pXxx: '', tYyy: <encrypted time> } shape validated by App\Rules\Honeypot.
 *
 * @return array{pot:string,time:string,token:string}
 */
function honeypotData(): array
{
    return [
        'pot' => 'p'.Str::random(),
        'time' => 't'.Str::random(),
        'token' => Crypt::encrypt(time()),
    ];
}

function honeypotField(string $name = 'honeypot'): string
{
    $potFieldName = 'p'.Str::random();
    $timeFieldName = 't'.Str::random();
    $encryptedTime = Crypt::encrypt(time());

    return sprintf(
        '<div style="display:none">
            <label for="%s">Do not fill this field</label>
            <input type="text" name="%s[%s]" id="%s" autocomplete="off">
            <input type="hidden" name="%s[%s]" value="%s">
        </div>',
        $potFieldName,
        $name,
        $potFieldName,
        $potFieldName,
        $name,
        $timeFieldName,
        $encryptedTime
    );
}

function createUrl(string $path): string
{
    $baseUrl = getUrl();

    return rtrim($baseUrl, '/').'/'.ltrim($path, '/');
}

function isAgentAllowed(mixed $productId, mixed $planId): bool
{
    $planAgents = PlanPrice::where('plan_id', $planId)
        ->value('no_of_agents');

    // No agents configured → immediately false
    if (empty($planAgents)) {
        return false;
    }

    // Cloud popup products are always allowed
    if (in_array($productId, cloudPopupProducts(), strict: true)) {
        return true;
    }

    return (bool) Product::find($productId)
        ->value('can_modify_agent');
}

function isCurrencySupportedForPayments(string $currency, array|string $paymentMethods): bool
{
    $currency = strtoupper($currency);
    $methods = is_array($paymentMethods) ? $paymentMethods : [$paymentMethods];
    $pluginMap = (new PaymentSettingsController)->getPaymentPluginMap();

    foreach ($methods as $method) {
        $method = strtolower((string) $method);
        if (! isset($pluginMap[$method]) || ! array_key_exists($currency, $pluginMap[$method]['supported_currencies'])) {
            return false;
        }
    }

    return true;
}

function getMinimumAmountForPayments(string $currency, string $paymentMethod): float
{
    $method = strtolower($paymentMethod);

    if (! isCurrencySupportedForPayments($currency, $method)) {
        throw new InvalidArgumentException('Currency not supported for payments');
    }

    $pluginMap = new PaymentSettingsController()->getPaymentPluginMap();

    return (float) ($pluginMap[$method]['supported_currencies'][$currency] ?? 1);
}

function calculateUnitCost(string $currency, int|float $cost): int
{
    $decimalPlaces = [
        // 0 decimal places
        'BIF' => 0, 'CLP' => 0, 'DJF' => 0, 'GNF' => 0, 'ISK' => 0, 'JPY' => 0, 'KMF' => 0,
        'KRW' => 0, 'PYG' => 0, 'RWF' => 0, 'UGX' => 0, 'UYI' => 0, 'VND' => 0, 'VUV' => 0,
        'XAF' => 0, 'XOF' => 0, 'XPF' => 0,

        // 1 decimal place
        'MGA' => 1, 'MRU' => 1,

        // 3 decimal places
        'BHD' => 3, 'IQD' => 3, 'JOD' => 3, 'KWD' => 3, 'LYD' => 3, 'OMR' => 3, 'TND' => 3,

        // 4 decimal places
        'CLF' => 4,
    ];

    // Default to 2 decimals if currency not listed
    $decimals = $decimalPlaces[$currency] ?? 2;

    return ($decimals === 0) ? (int) round($cost) : (int) round($cost * 10 ** $decimals);
}

/**
 * log the actions in log files.
 *
 * @param  string  $level
 * @param  array  $array
 */
function loging(string $context, string $message, string $level = 'error', array $array = []): void
{
    Log::$level($message.':-:-:-'.$context, $array);
}

/**
 * Deletes all user sessions except the current session.
 *
 * This function checks the session driver and deletes sessions based on the user ID.
 * For file-based sessions, it reads session files and deletes those that match the user ID.
 * For other drivers (like database or Redis), it uses the logoutOtherDevices method.
 *
 * @param  int  $userId  The ID of the user whose sessions are to be deleted.
 * @param  string  $password  The user's password for authentication.
 */
function deleteUserSessions(int $userId, string $password): void
{
    if (config('session.driver') !== 'file') {
        Auth::logoutOtherDevices($password);

        return;
    }

    $sessionPath = storage_path('framework/sessions');
    $currentSessionId = session()->getId();

    // Find sessions to keep (not belonging to user + current session)
    $sessionsToKeep = File::filterFiles($sessionPath, function ($file) use ($userId, $currentSessionId): bool {
        $fileName = $file->getFilename();

        // Always keep current session
        if ($fileName === $currentSessionId) {
            return true;
        }

        // Check if session belongs to the user
        $sessionData = File::safeGet($file->getPathname(), true);
        if (! $sessionData) {
            return false;
        }

        // Look for user login data
        foreach ($sessionData as $key => $value) {
            if (str_starts_with($key, 'login_web_') && $value == $userId) {
                return false;
            }
        }

        return true;
    });

    // Clean directory keeping only selected sessions
    $keepFiles = $sessionsToKeep->map(fn ($file) => $file->getFilename())->all();
    File::cleanDirectoryFiles($sessionPath, $keepFiles);
}

/**
 * Format a given datetime string to UTC timezone.
 *
 * @param  string|null  $datetime  The datetime string to format.
 * @return Carbon|null The formatted datetime in UTC or null if input is invalid.
 */
function toFormatDateAndTime(?string $datetime): ?Carbon
{
    if (! $datetime) {
        return null;
    }

    // Decode if URL encoded
    $datetime = urldecode($datetime);

    // Parse using app timezone
    $carbon = Date::parse($datetime, config('app.timezone'));

    // Return in UTC
    return $carbon->clone()->setTimezone('UTC');
}

/**
 * Convert days to human-readable format using match.
 *
 * @return string|null
 */
function formatDays(int $days): ?string
{
    return match (true) {
        $days <= 0 => null,
        $days < 30 => $days.' Days',
        $days < 365 => intval($days / 30).(intval($days / 30) > 1 ? ' Months' : ' Month'),
        default => intval($days / 365).(intval($days / 365) > 1 ? ' Years' : ' Year'),
    };
}

/**
 * Generate an HTML hyperlink.
 *
 * @param  string  $href  The URL for the hyperlink.
 * @param  string  $value  The display text for the hyperlink.
 * @return string The generated HTML anchor tag.
 */
function hyperLinkGenerator(string $href, string $value): string
{
    return "<a href='".url($href)."'>".$value.'</a>';
}

/**
 * Log activity in a standard format across the system.
 */
function logActivity(
    string $message,
    string $event,
    ?string $module = null,
    ?User $user = null,
    array $properties = []
): void {
    $defaultLogName = config('activitylog.default_log_name');
    $logStatus = resolve(ActivityLogStatus::class);

    $actor = $user ?? (Auth::check() ? Auth::user() : null);

    $log = resolve(ActivityLogger::class)
        ->useLog($module ?? $defaultLogName)
        ->setLogStatus($logStatus)
        ->event($event)
        ->withProperties($properties);

    $actor ? $log->causedBy($actor) : $log->causedByAnonymous(); // @phpstan-ignore argument.type

    $log->log($message);
}

function getUserStateWithCountry(?string $country = null, ?string $state = null): string
{
    $user = auth()->user();

    $country ??= $user->country ?? '';
    $state ??= $user->state ?? '';

    return trim(sprintf('%s-%s', $country, $state), '-');
}

/**
 *Get Supported Countries for IntlInput Plugins.
 */
function getSupportedCountriesForIntlInput(): array
{
    $countries = Country::pluck('country_name', 'country_code_char2')->toArray();

    $unsupportedIso = ['BV', 'PN', 'GS', 'UM', 'HM'];

    return collect($countries)->reject(fn ($name, $iso): bool => in_array(strtoupper((string) $iso), $unsupportedIso, strict: true))->toArray();
}

/**
 * Checks if the request is coming from api or web.
 */
function isV3Api(): bool
{
    return str_contains(str_replace(Request::root().'/', '', URL::current()), 'v3/');
}

/**
 * Resolve a named theme asset to its URL (local or CDN).
 *
 * Usage in Blade:
 *   themeAsset('adminlte-css')   → public/themes/adminlte/css/adminlte.min.css (local)
 *                                → https://cdn.example.com/themes/... (CDN)
 *
 * Asset aliases are defined in config/theme.php under 'assets'.
 */
function themeAsset(string $key): string
{
    $path = config('theme.assets.'.$key, '');

    if (config('theme.use_cdn')) {
        return rtrim((string) config('theme.cdn_base', ''), '/').'/'.ltrim((string) $path, '/');
    }

    return asset($path);
}

function throttleApiRequest(string $url, int $maxRequests = 60, int $perSeconds = 60, bool $perSite = true): void
{
    $identifier = $perSite
        ? parse_url($url, PHP_URL_HOST)
        : parse_url($url, PHP_URL_HOST).parse_url($url, PHP_URL_PATH);

    $key = 'api_rate_next_allowed_'.md5($identifier);

    $interval = $perSeconds / $maxRequests; // spacing between requests

    $waitSeconds = 0;

    try {
        Cache::lock($key.'_lock', 5)->block(3, function () use ($key, $interval, &$waitSeconds): void {
            $now = microtime(as_float: true);

            // next allowed execution time
            $nextAllowed = Cache::get($key, $now);

            // if previous requests already reserved future slots
            if ($nextAllowed > $now) {
                $waitSeconds = $nextAllowed - $now;
                $nextAllowed += $interval;
            } else {
                $nextAllowed = $now + $interval;
            }

            // reserve next slot
            Cache::put($key, $nextAllowed, 300);
        });
    } catch (Throwable) {
        // NEVER fail API because limiter failed
        return;
    }

    // each request waits its OWN turn
    if ($waitSeconds > 0) {
        Sleep::usleep((int) ($waitSeconds * 1_000_000));
    }
}

/**
 * Check if the authenticated user owns the resource.
 *
 * @param  bool  $allowAdmin  If true, admin users can access the resource.
 */
function authorizeOwnership(int $userID, bool $allowAdmin = false): bool
{
    if ($allowAdmin && auth()->user()?->role === 'admin') {
        return true;
    }

    return $userID === auth()->id();
}

/**
 * Format exception response with exception details.
 *
 * @param  Exception  $exception  Exception instance
 * @return JsonResponse json response
 */
function exceptionResponse(Throwable $exception): JsonResponse
{
    return response()->json(
        [
            'success' => false,
            'message' => $exception->getMessage(),
            'file' => $exception->getFile().' ('.$exception->getLine().')',
            'trace' => $exception->getTraceAsString(),
        ],
        500
    );
}

/**
 * This function returns an asset link based on link.php settings.
 */
function assetLink(string $type, string $key): string
{
    // if request is language, it should append & language to it
    return asset(Config::get('link.'.$type.'.'.$key));
}

/**
 * Gives the bundle URL after appending the version number to it.
 */
function bundleLink(string $url): string
{
    $baseUrl = asset($url).'?version='.Config::get('app.tags');

    // if the call is for a language file, we should append language too in the url
    // REASON: we are sending cache headers while sending language response, which will improve performance since the browser
    // will cache it. But as soon as the language changes, language in cache will be the same and will cause conflicts
    // adding language to argument will cause the browser to request a fresh response as soon as the language changes
    // appending all activated plugin names too with the URL, so that if a plugin is activated, it requests a new
    // language file
    if (str_contains($url, 'js/lang')) {
        return $baseUrl.'&lang='.App::getLocale();
    }

    return $baseUrl;
}

function commonSettings(string $option, string $optionField, string $returnColumn = 'option_value'): mixed
{
    return CommonSettings::where('option_name', $option)
        ->where('optional_field', $optionField)
        ->value($returnColumn);
}
