<?php

use App\FileSystemSettings;
use App\Model\Common\Country;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Order\InstallationDetail;
use App\Model\Payment\Currency;
use App\Model\Payment\Plan;
use App\Model\Payment\TaxByState;
use App\Model\Product\ProductUpload;
use App\Traits\TaxCalculation;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;

function getLocation()
{
    try {
        $location = \GeoIP::getLocation();

        return $location;
    } catch (Exception $ex) {
        app('log')->error($ex->getMessage());
        $location = \Config::get('geoip.default_location');

        return $location;
    }
}

function checkArray($key, $array)
{
    $value = '';
    if (is_array($array) && array_key_exists($key, $array)) {
        $value = $array[$key];
    }

    return $value;
}

function mime($type)
{
    if ($type == 'jpg' ||
        $type == 'png' ||
        $type == 'jpeg' ||
        $type == 'gif' ||
        starts_with($type, 'image')) {
        return 'image';
    }
}

function isInstall()
{
    $check = false;
    $env = base_path('.env');
    if (\File::exists($env) && env('DB_INSTALL') == 1) {
        $check = true;
    }

    return $check;
}

// For API response
/**
 * Format the error message into json error response.
 *
 * @param  string|array  $message  Error message
 * @param  int  $statusCode
 * @return HTTP json response
 */
function errorResponse($message, $statusCode = 400)
{
    return response()->json(['success' => false, 'message' => $message], $statusCode);
}

/**
 * Format success message/data into json success response.
 *
 * @param  string  $message  Success message
 * @param  array|string  $data  Data of the response
 * @param  int  $statusCode
 * @return HTTP json response
 */
function successResponse($message = '', $data = '', $statusCode = 200)
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
 * @param  string  $dateTimeString
 * @param  string  $format
 * @return string
 */
function getTimeInLoggedInUserTimeZone(string $dateTimeString, $format = 'M j, Y, g:i a')
{
    // caching for 4 seconds so for consecutive queries, it will be readily available. And even if someone updates their
    // timezone, it will start showing the new timezone after 4 seconds
    $timezone = Cache::remember('timezone_'.Auth::user()->id, 5, function () {
        return Auth::user()->timezone->name;
    });

    return (new DateTime($dateTimeString))->setTimezone(new DateTimeZone($timezone))->format($format);
}

/**
 * Gets date in a formatted HTML.
 *
 * @param  string|null  $dateTimeString
 * @return string
 */
function getDateHtml(string $dateTimeString = null)
{
    try {
        if (! $dateTimeString) {
            return '--';
        }
        $date = getTimeInLoggedInUserTimeZone($dateTimeString, 'M j, Y');
        $dateTime = getTimeInLoggedInUserTimeZone($dateTimeString);

        return "<label data-toggle='tooltip'style='font-weight:500; margin: 0px' data-placement='top' title='".$dateTime."'>".$date.'</label>';
    } catch (Exception $e) {
        return '--';
    }
}
function getDateHtmlcopy(string $dateTimeString = null)
{
    try {
        if (! $dateTimeString) {
            return '--';
        }
        $date = getTimeInLoggedInUserTimeZone($dateTimeString, 'M j, Y');
        $dateTime = getTimeInLoggedInUserTimeZone($dateTimeString);

        return "<label data-toggle='tooltip' style='font-weight:500; margin: 0px' data-placement='top' title='".$dateTimeString."'>".$date.'</label>';
    } catch (Exception $e) {
        return '--';
    }
}
function getExpiryLabel($expiryDate, $badge = 'badge')
{
    if ($expiryDate < (new Carbon())->toDateTimeString()) {
        return getDateHtml($expiryDate).'&nbsp;<span class="'.$badge.' '.$badge.'-danger"  <label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="Order has Expired">

                         </label>
            Expired</span>';
    } else {
        return getDateHtml($expiryDate);
    }
}

function getVersionAndLabel($productVersion, $productId, $badge = 'label', $path = null)
{
    $latestVersion = \Cache::remember('latest_'.$productId, 10, function () use ($productId) {
        return ProductUpload::where('product_id', $productId)->latest()->value('version');
    });
    if (! $productVersion && $path) {
        $installationDetail = InstallationDetail::where('installation_path', 'like', '%'.$path.'%')->orderBy('id', 'desc')->first();
        $productVersion = $installationDetail ? $installationDetail->version : $latestVersion;
    }
    $status = $productVersion ? ($productVersion < $latestVersion ? 'warning' : 'success') : '';

    return '<span class="'.$badge.' '.$badge.'-'.$status.'"><label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="'.($productVersion ? ($status == 'warning' ? 'Outdated Version' : 'Latest Version') : '').'"></label>'.($productVersion ? $productVersion : '--').'</span>';
}

function getInstallationDetail($ip)
{
    return InstallationDetail::where('installation_path', 'like', '%'.$ip.'%')->first();
}

function tooltip($tootipText = '')
{
    return '<label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title='.$tootipText.'>
             </label>';
}

function getStatusLabel($status, $badge = 'badge')
{
    switch ($status) {
        case 'Success':
            return '<span class='.'"'.$badge.' '.$badge.'-success">Paid</span>';

        case 'Pending':
            return '<span class='.'"'.$badge.' '.$badge.'-danger">Unpaid</span>';

        case 'renewed':
            return '<span class='.'"'.$badge.' '.$badge.'-primary">Renewed</span>';

        default:
            return '<span class='.'"'.$badge.' '.$badge.'-warning">Partially paid</span>';
    }
}

function getCountryByCode($code)
{
    try {
        $country = \App\Model\Common\Country::where('country_code_char2', $code)->first();
        if ($country) {
            return $country->nicename;
        }
    } catch (\Exception $ex) {
        throw new \Exception($ex->getMessage());
    }
}

function findCountryByGeoip($iso)
{
    try {
        $country = \App\Model\Common\Country::where('country_code_char2', $iso)->first();
        if ($country) {
            return $country->country_code_char2;
        } else {
            return '';
        }
    } catch (\Exception $ex) {
        throw new \Exception($ex->getMessage());
    }
}

function findStateByRegionId($iso)
{
    try {
        $states = \App\Model\Common\State::where('country_code_char2', $iso)
            ->pluck('state_subdivision_name', 'state_subdivision_code')->toArray();

        return $states;
    } catch (\Exception $ex) {
        throw new \Exception($ex->getMessage());
    }
}

function getTimezoneByName($name)
{
    try {
        $timezone = \App\Model\Common\Timezone::where('name', $name)->first();
        if ($timezone) {
            $timezone = $timezone->id;
        } else {
            $timezone = '114';
        }

        return $timezone;
    } catch (\Exception $ex) {
        throw new \Exception($ex->getMessage());
    }
}

function checkPlanSession()
{
    try {
        if (Session::has('plan')) {
            return true;
        }

        return false;
    } catch (\Exception $ex) {
        throw new \Exception($ex->getMessage());
    }
}

function getStateByCode($code)
{
    try {
        $result = ['id' => '', 'name' => ''];

        $subregion = \App\Model\Common\State::where('state_subdivision_code', $code)->first();
        if ($subregion) {
            $result = ['id' => $subregion->state_subdivision_code,
                'name' => $subregion->state_subdivision_name, ];
        }

        return $result;
    } catch (\Exception $ex) {
        throw new \Exception($ex->getMessage());
    }
}

function userCurrencyAndPrice($userid, $plan, $productid = '')
{
    try {
        $country = getCountry($userid);

        if (! $country) {
            throw new \Exception(Lang::get('messages.country_notfound'));
        }

        $currencyAndSymbol = getCurrencySymbolAndPriceForPlans($country, $plan);

        // Check if the user is not authenticated
        if (! auth()->check()) {
            echo '<script>';
            echo "localStorage.setItem('currency', '{$currencyAndSymbol['currency']}');";
            echo "localStorage.setItem('symbol', '{$currencyAndSymbol['currency_symbol']}');";
            echo "localStorage.setItem('plan', '".json_encode($currencyAndSymbol['userPlan'])."');";
            echo '</script>';
        }

        return [
            'currency' => $currencyAndSymbol['currency'],
            'symbol' => $currencyAndSymbol['currency_symbol'],
            'plan' => $currencyAndSymbol['userPlan'],
        ];
    } catch (\Exception $ex) {
        return redirect()->back()->with('fails', $ex->getMessage());
    }
}

function getCountry($userid)
{
    if (Auth::check() && empty($userid)) {
        return Auth::user()->country;
    }

    if ($userid) {
        return User::where('id', $userid)->value('country');
    }

    $location = cache()->remember('user_location', 60, function () {
        return getLocation();
    });

    return $location['iso_code'] ? findCountryByGeoip($location['iso_code']) : null;
}

/**
 * Fetches currency and price for a plan. If the country code sent has a price defined for them in a plan then
 * that price will be displayed in the respective currency of that country else the default price for that plan will be displayed along with the default currency.
 *
 * @param  string  $countryCode  Code of the country
 * @param  obj  $plan  Plan for which price is to be fetched
 * @return array Currency, symbol and plan details
 */
function getCurrencySymbolAndPriceForPlans($countryCode, $plan)
{
    $country = Country::where('country_code_char2', $countryCode)->first();
    $userPlan = $plan->planPrice->where('country_id', $country->country_id)->first() ?: $plan->planPrice->where('country_id', 0)->first();
    $currency = $userPlan->currency;
    $currency_symbol = Currency::where('code', $currency)->value('symbol');

    return compact('currency', 'currency_symbol', 'userPlan');
}

/**
 * Get client currency on the basis of country. This is applicable when client logs in to detect his currency.
 *
 * @param  string  $countryCode  The country code('IN','US')
 * @return string The currency code('INR','USD')
 */
function getCurrencyForClient($countryCode)
{
    $defaultCurrency = Setting::first()->default_currency;
    $country = Country::where('country_code_char2', $countryCode)->first();
    $currencyStatus = $country->currency->status;
    if ($currencyStatus) {
        $currency = Currency::where('id', $country->currency_id)->first();

        return $currency->code;
    }

    return $defaultCurrency;
}

function currencyFormat($amount = null, $currency = null, $include_symbol = true)
{
    $amount = rounding($amount);
    if ($currency == 'INR') {
        $symbol = getIndianCurrencySymbol($currency);

        return $symbol.getIndianCurrencyFormat($amount);
    }

    return app('currency')->format($amount, $currency, $include_symbol);
}

function rounding($price)
{
    try {
        $tax_rule = new \App\Model\Payment\TaxOption();
        $rule = $tax_rule->findOrFail(1);
        $rounding = $rule->rounding;
        if ($rounding) {
            return round((int) $price);
        } else {
            return round($price, 2);
        }
    } catch (\Exception $ex) {
    }
}

function userCountryId()
{
    if (\Auth::check()) {
        $country = Country::where('country_code_char2', \Auth::user()->country)->first()->country_id;
    } else {
        $location = getLocation();
        $country = Country::where('country_code_char2', $location['iso_code'])->first()->country_id;
    }

    return $country;
}

function getIndianCurrencySymbol($currency)
{
    return \DB::table('format_currencies')->where('code', $currency)->value('symbol');
}

function getIndianCurrencyFormat($number)
{
    $explrestunits = '';
    $number = explode('.', $number);
    $num = $number[0];
    if (strlen($num) > 3) {
        $lastthree = substr($num, strlen($num) - 3, strlen($num));
        $restunits = substr($num, 0, strlen($num) - 3); // extracts the last three digits
        $restunits = (strlen($restunits) % 2 == 1) ? '0'.$restunits : $restunits; // explodes the remaining digits in 2's formats, adds a zero in the beginning to maintain the 2's grouping.
        $expunit = str_split($restunits, 2);
        for ($i = 0; $i < count($expunit); $i++) {
            // creates each of the 2's group and adds a comma to the end
            if ($i == 0) {
                $explrestunits .= (int) $expunit[$i].','; // if is first value , convert into integer
            } else {
                $explrestunits .= $expunit[$i].',';
            }
        }
        $thecash = $explrestunits.$lastthree;
    } else {
        $thecash = $num;
    }
    if (! empty($number[1])) {
        if (strlen($number[1]) == 1) {
            return $thecash.'.'.$number[1].'0';
        } elseif (strlen($number[1]) == 2) {
            return $thecash.'.'.$number[1];
        } else {
            return 'cannot handle decimal values more than two digits...';
        }
    } else {
        return $thecash;
    }
}

function bifurcateTax($taxName, $taxValue, $currency, $state, $price = '')
{
    if (\Auth::user()->country == 'IN') {
        $gst = TaxByState::where('state_code', $state)->select('c_gst', 's_gst', 'ut_gst')->first();
        if ($taxName == 'CGST+SGST') {
            $html = 'CGST@'.$gst->c_gst.'%<br>SGST@'.$gst->s_gst.'%';

            $cgst_value = currencyFormat(TaxCalculation::taxValue($gst->c_gst, $price), $currency);

            $sgst_value = currencyFormat(TaxCalculation::taxValue($gst->s_gst, $price), $currency);

            return ['html' => $html, 'tax' => $cgst_value.'<br>'.$sgst_value];
        } elseif ($taxName == 'CGST+UTGST') {
            $html = 'CGST@'.$gst->c_gst.'%<br>UTGST@'.$gst->ut_gst.'%';

            $cgst_value = currencyFormat(TaxCalculation::taxValue($gst->c_gst, $price), $currency);
            $utgst_value = currencyFormat(TaxCalculation::taxValue($gst->ut_gst, $price), $currency);

            return ['html' => $html, 'tax' => $cgst_value.'<br>'.$utgst_value];
        } else {
            $html = $taxName.'@'.$taxValue;
            $tax_value = currencyFormat(TaxCalculation::taxValue($taxValue, $price), $currency);

            return ['html' => $html, 'tax' => $tax_value];
        }
    } else {
        $html = $taxName.'@'.$taxValue;
        $tax_value = currencyFormat(TaxCalculation::taxValue($taxValue, $price), $currency);

        return ['html' => $html, 'tax' => $tax_value];
    }
}

/**
 * sets mail config and reloads the config into the container
 * NOTE: this is getting used outside the class to set service config.
 *
 * @return void
 */
function setServiceConfig($emailConfig)
{
    $sendingProtocol = $emailConfig->driver;
    if ($sendingProtocol && $sendingProtocol != 'smtp' && $sendingProtocol != 'mail') {
        $services = \Config::get("services.$sendingProtocol");
        $dynamicServiceConfig = [];

        //loop over it and assign according to the keys given by user
        foreach ($services as $key => $value) {
            $dynamicServiceConfig[$key] = isset($emailConfig[$key]) ? $emailConfig[$key] : $value;
        }

        //setting that service configuration
        \Config::set("services.$sendingProtocol", $dynamicServiceConfig);
    } else {
        \Config::set('mail.sendmail', '/usr/sbin/sendmail -t -i -f'.$emailConfig['email']);

        \Config::set('mail.host', $emailConfig['host']);
        \Config::set('mail.port', $emailConfig['port']);
        \Config::set('mail.password', $emailConfig['password']);
        \Config::set('mail.security', $emailConfig['encryption']);
    }

    //setting mail driver as $sending protocol
    \Config::set('mail.driver', $sendingProtocol);
    \Config::set('mail.from.address', $emailConfig['email']);
    \Config::set('mail.from.name', $emailConfig['company']);
    \Config::set('mail.username', $emailConfig['email']);

    //setting the config again in the service container
    (new \Illuminate\Mail\MailServiceProvider(app()))->register();
}

function persistentCache($key, Closure $closure, $noOfSeconds = 30, array $variables = [])
{
    $keySalt = json_encode($variables);

    return Cache::remember($key.$keySalt, $noOfSeconds, $closure);
}

function emailSendingStatus()
{
    $status = false;
    if (Setting::value('sending_status')) {
        $status = true;
    }

    return $status;
}

function installationStatusLabel($installedPath)
{
    return $installedPath ? "&nbsp;<span class='badge badge-primary' style='background-color:darkcyan !important;' <label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title='Installation is Active'>
                     </label>Active</span>" : "&nbsp;<span class='badge badge-info' <label data-toggle='tooltip' style='font-weight:500;background-color:crimson;' data-placement='top' title='Installation is inactive'>
                    </label>Inactive</span>";
}

//return root url from long url (http://www.domain.com/path/file.php?aa=xx becomes http://www.domain.com/path/), remove scheme, www. and last slash if needed
function getRootUrl($url, $remove_scheme, $remove_www, $remove_path, $remove_last_slash)
{
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $url_array = parse_url($url); //parse URL into arrays like $url_array['scheme'], $url_array['host'], etc

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
            while (substr($url, -1) == '/') { //use cycle in case URL already contained multiple // at the end
                $url = substr($url, 0, -1);
            }
        }
    }

    return trim($url);
}

function getContactData()
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

function cloudSubDomain()
{
    $cloudSubDomain = \App\Model\Common\FaveoCloud::find(1);

    return optional($cloudSubDomain)->cloud_cname;
}

function cloudCentralDomain()
{
    $cloudSubDomain = \App\Model\Common\FaveoCloud::find(1);

    return str_replace('https://', '', optional($cloudSubDomain)->cloud_central_domain);
}

function cloudPopUpDetails()
{
    $cloudPop = \App\CloudPopUp::find(1);

    return $cloudPop;
}

function cloudPopupProducts()
{
    return \App\Model\Product\CloudProducts::pluck('cloud_product')->toArray();
}

function getPreReleaseStatusLabel($status, $badge = 'badge')
{
    switch ($status) {
        case 'official':
            return '<span class='.'"'.$badge.' '.$badge.'-success">Official Release</span>';

        case 'pre_release':
            return '<span class='.'"'.$badge.' '.$badge.'-warning">Pre Release</span>';

        case 'beta':
            return '<span class='.'"'.$badge.' '.$badge.'-info">Beta</span>';
    }
}

/**
 * Creates an empty DB with given name.
 *
 * @param  string  $dbName  name of the DB
 * @return null
 */
function createDB(string $dbName)
{
    try {
        \DB::purge('mysql');
        // removing old db
        \DB::connection('mysql')->getPdo()->exec("DROP DATABASE IF EXISTS `{$dbName}`");

        // Creating testing_db
        \DB::connection('mysql')->getPdo()->exec("CREATE DATABASE `{$dbName}`");
        //disconnecting it will remove database config from the memory so that new database name can be
        // populated
        \DB::disconnect('mysql');
    } catch (\Exception $e) {
        return redirect()->back()->with('fails', $e->getMessage());
    }
}

function isS3Enabled()
{
    $fileSettings = FileSystemSettings::select('disk')->first();

    return $fileSettings->disk === 's3';
}

function setEnvValue($key, $value)
{
    $envFile = app()->environmentFilePath();
    $content = File::get($envFile);

    $keyExists = preg_match("/^{$key}=.*/m", $content);

    if ($keyExists) {
        $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
    } else {
        // Append the new key-value pair
        $content .= "\n{$key}={$value}";
    }

    // Save the updated .env file
    File::put($envFile, $content);
}

function downloadExternalFile($url, $filename)
{
    $client = new Client();
    $response = $client->get($url, ['stream' => true]);

    return response()->stream(function () use ($response) {
        $stream = $response->getBody();
        while (! $stream->eof()) {
            echo $stream->read(1024);
        }
    }, 200, [
        'Content-Type' => 'application/zip',
        'Content-Disposition' => 'attachment; filename="'.basename($filename).'.zip"',
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
function rateLimitForKeyIp($key, $maxAttempts, $decayMinutes, $ip)
{
    $IpKey = $key.':'.$ip;
    $decaySeconds = $decayMinutes * 60;

    // Command 1: Check and handle non-persistent cache.
    if (Cache::getStore() instanceof Illuminate\Cache\ArrayStore) {
        return handleArrayStoreRateLimit($IpKey, $maxAttempts, $decaySeconds);
    }

    // Command 2: Handle persistent cache using RateLimiter.
    if (! RateLimiter::attempt($IpKey, $maxAttempts, function () {
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
function handleArrayStoreRateLimit($IpKey, $maxAttempts, $decaySeconds)
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

function isCaptchaRequired()
{
    $settings = StatusSetting::find(1);
    $status = ($settings->v3_recaptcha_status === 1 || $settings->recaptcha_status === 1) && ! Auth::check();

    return $status ? ['status' => 1, 'is_required' => 'required'] : ['status' => 0, 'is_required' => 'sometimes'];
}

/**
 * Convert a time duration in seconds to a human-readable format.
 * If the time exceeds 60 minutes, return both hours and minutes.
 * Otherwise, return minutes only if the duration is below 60 minutes.
 *
 * @param  int  $seconds  The time in seconds.
 * @return string A human-readable time string (hours and minutes or just minutes).
 */
function formatDuration($seconds)
{
    // Calculate hours, minutes, and remaining seconds
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);

    // If the time exceeds or equals 60 minutes, return both hours and minutes
    if ($seconds >= 3600) {
        return "{$hours} hour".($hours > 1 ? 's' : '')." {$minutes} minute".($minutes > 1 ? 's' : '');
    }

    // If the time is less than 60 minutes, just return minutes
    if ($seconds >= 60) {
        return "{$minutes} minute".($minutes > 1 ? 's' : '');
    }

    // Otherwise, return seconds
    return "{$seconds} second".($seconds > 1 ? 's' : '');
}

function isJson($string)
{
    json_decode($string);

    return json_last_error() === JSON_ERROR_NONE;
}

function getUrl()
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);

    return $protocol.'://'.$host.$path;
}
