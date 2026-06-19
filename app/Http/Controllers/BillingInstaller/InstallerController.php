<?php

namespace App\Http\Controllers\BillingInstaller;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SyncBillingToLatestVersion;
use App\Http\Requests\StoreLanguageRequest;
use App\Model\Common\Setting;
use App\Model\Common\Timezone;
use App\Model\Mailjob\QueueService;
use App\User;
use Artisan;
use DB;
use Exception;
use Hash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Logger;
use Predis\Client;
use Schema;
use Validator;

class InstallerController extends Controller
{
    /**
     * Post configurationcheck
     * checking prerequisites.
     */
    public function configurationcheck(Request $request): RedirectResponse
    {
        Cache::forever('config-check', 'config-check');
        $inputs = $request->only([
            'host', 'databasename', 'username', 'password', 'port',
            'db_ssl_key', 'db_ssl_cert', 'db_ssl_ca', 'db_ssl_verify',
        ]);
        Session::put(array_merge($inputs, ['default' => 'mysql', 'db_ssl_key' => $inputs['db_ssl_key'] ?? null, 'db_ssl_cert' => $inputs['db_ssl_cert'] ?? null, 'db_ssl_ca' => $inputs['db_ssl_ca'] ?? null, 'db_ssl_verify' => $inputs['db_ssl_verify'] ?? null]));

        return to_route('database');
    }

    public function checkPreInstall(): JsonResponse
    {
        Artisan::call('key:generate', ['--force' => true]);

        $url = url('migrate');

        $result = ['success' => \Lang::get('installer_messages.pre_migration_success'), 'next' => \Lang::get('installer_messages.migrating_tables'), 'api' => $url];

        return response()->json(compact('result'));
    }

    public function migrate(): JsonResponse
    {
        $db_install_method = '';
        try {
            if (Cache::get('databasename') != config('database.connections.mysql.database')) {
                throw new Exception(__('installer_messages.db_connection_error'), 500);
            }

            $tableNames = Schema::getTableListing(
                schema: DB::getDatabaseName(),
                schemaQualified: false
            );
            // allowing migrations table in db as it does not get removed on "migrate:reset"
            $tableNames = array_unique(array_merge(['migrations'], $tableNames));
            if (count($tableNames) === 1) {
                $this->rollBackMigration();
                new SyncBillingToLatestVersion()->sync();

                if (Cache::get('dummy_data_installation')) {
                    $path = base_path().DIRECTORY_SEPARATOR.'DB'.DIRECTORY_SEPARATOR.'dummy-data.sql';
                    DB::unprepared((string) file_get_contents($path));
                }
            }
        } catch (Exception $exception) {
            // $this->rollBackMigration();
            $result = ['error' => $exception->getMessage()];

            return response()->json(compact('result'), 500);
        }

        $message = \Lang::get('installer_messages.database_setup_success');
        $result = ['success' => $message];

        return response()->json(compact('result'));
    }

    public function rollBackMigration(): ?JsonResponse
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            //            shell_exec('php ../artisan passport:install');
            // Artisan::call('passport:install', ['--force' => true]);
        } catch (Exception $exception) {
            $result = ['error' => $exception->getMessage()];

            return response()->json(compact('result'), 500);
        }

        return null;
    }

    public function createEnv(bool $api = true): ?JsonResponse
    {
        try {
            $default = request()->get('default', Session::get('default'));
            $host = request()->get('host', Session::get('host'));
            $database = request()->get('databasename', Session::get('databasename'));
            $dbusername = request()->get('username', Session::get('username'));
            $dbpassword = request()->get('password', Session::get('password'));
            $port = request()->get('port', Session::get('port'));
            $sslKey = request()->get('db_ssl_key', Session::get('db_ssl_key'));
            $sslCert = request()->get('db_ssl_cert', Session::get('db_ssl_cert'));
            $sslCa = request()->get('db_ssl_ca', Session::get('db_ssl_ca'));
            $sslVerify = request()->get('db_ssl_verify', Session::get('db_ssl_verify'));

            $this->env($default, $host, $port, $database, $dbusername, $dbpassword, sslKey: $sslKey, sslCert: $sslCert, sslCa: $sslCa, sslVerify: $sslVerify);
        } catch (Exception $exception) {
            return response()->json(['result' => $exception->getMessage()], 500);
        }

        if ($api) {
            Cache::forever('databasename', $database);
            $url = url('preinstall/check');
            $result = [
                'success' => \Lang::get('installer_messages.env_file_created'),
                'next' => \Lang::get('installer_messages.pre_migration_test'),
                'api' => $url,
            ];

            return response()->json(compact('result'));
        }

        return null;
    }

    public function env(string $default, string $host, string $port, string $database, string $dbusername, string $dbpassword, ?string $appUrl = null, ?string $sslKey = null, ?string $sslCert = null, ?string $sslCa = null, ?string $sslVerify = null): void
    {
        $ENV = [
            'APP_NAME' => 'Agora:'.md5(uniqid()),
            'APP_DEBUG' => 'false',
            'APP_BUGSNAG' => 'true',
            'APP_URL' => $appUrl ?? url('/'),
            'APP_KEY' => 'base64:6bir20aYSpt+tJUiTu3D/QRwddjvwAupLPAfu14uUmk=',
            'QUERY_DETECTOR_ENABLED' => 'false',
            'APP_LOG_LEVEL' => 'debug',
            'DB_CONNECTION' => $default,
            'DB_HOST' => $host,
            'DB_PORT' => $port,
            'DB_INSTALL' => 0,
            'DB_DATABASE' => $database,
            'DB_USERNAME' => $dbusername,
            'DB_PASSWORD' => str_replace('"', '\"', $dbpassword),
            'DB_ENGINE' => 'InnoDB',
            'CACHE_DRIVER' => 'file',
            'SESSION_DRIVER' => 'file',
            'SESSION_COOKIE_NAME' => 'agora_'.random_int(0, 10000),
            'QUEUE_CONNECTION' => 'sync',
            'PROBE_PASS_PHRASE' => md5(uniqid()),
            'BROADCAST_DRIVER' => 'pusher',
            'PUSHER_APP_ID' => Str::random(16),
            'PUSHER_APP_KEY' => md5(uniqid()),
            'PUSHER_APP_SECRET' => md5(uniqid()),
            'PUSHER_APP_CLUSTER' => 'mt1',
            'MAIL_DRIVER' => '',
            'MAIL_HOST' => '',
            'MAIL_PORT' => '',
            'MAIL_USERNAME' => '',
            'MAIL_PASSWORD' => '',
            'MAIL_ENCRYPTION' => '',
            'NOCAPTCHA_SECRET' => '00',
            'NOCAPTCHA_SITEKEY' => '00',
            'DB_SSL_KEY' => $sslKey,
            'DB_SSL_CERT' => $sslCert,
            'DB_SSL_CA' => $sslCa,
            'DB_SSL_VERIFY' => $sslVerify,
        ];

        $config = collect($ENV)
            ->map(fn ($val, string $key): string => sprintf('%s=%s', $key, $val))
            ->implode("\n");

        $envPath = base_path('.env');
        $exampleEnvPath = base_path('example.env');

        // Remove old .env file if it exists
        if (is_file($envPath)) {
            unlink($envPath); // nosemgrep: php.lang.security.unlink-use.unlink-use
        }

        // Create a new example.env file if it doesn't exist
        if (! is_file($exampleEnvPath)) {
            touch($exampleEnvPath);
        }

        // Write new environment configuration to example.env
        file_put_contents($exampleEnvPath, $config);

        // Rename example.env to .env
        rename($exampleEnvPath, $envPath);
    }

    /**
     * @param  array<mixed>  $redisConfig
     */
    public function updateInstallEnv(string $environment, ?string $driver = null, array $redisConfig = []): ?JsonResponse
    {
        $env = base_path().DIRECTORY_SEPARATOR.'.env';
        if (! is_file($env)) {
            return errorResponse('.env not found', 400);
        }

        $txt1 = '
APP_ENV='.$environment;
        file_put_contents($env, str_replace('DB_INSTALL='. 0, 'DB_INSTALL='. 1, (string) file_get_contents($env)));
        file_put_contents($env, $txt1.PHP_EOL, FILE_APPEND | LOCK_EX);

        foreach ($redisConfig as $key => $value) {
            $line = strtoupper((string) $key).'='.$value.PHP_EOL;
            file_put_contents($env, $line, FILE_APPEND | LOCK_EX);
        }

        // If Redis is used as cache driver, update .env and relevant database records
        if ($driver === 'redis') {
            // Update .env file to set CACHE_DRIVER to 'redis'
            file_put_contents($env, str_replace('CACHE_DRIVER='.getenv('CACHE_DRIVER'), 'CACHE_DRIVER=redis', (string) file_get_contents($env)));

            // Disable all active QueueServices
            QueueService::where('status', 1)->update(['status' => 0]);

            // Enable the Redis QueueService
            /** @var QueueService $queue */
            $queue = QueueService::where('short_name', 'redis')->firstOrFail();
            $queue->status = 1;
            $queue->save();

            // Update or create extra field relations for the QueueService
            $queue->extraFieldRelation()->updateOrCreate(['key' => 'driver'], ['key' => 'driver', 'value' => 'redis']);
            $queue->extraFieldRelation()->updateOrCreate(['key' => 'queue'], ['key' => 'queue', 'value' => 'default']);
        }

        return null;
    }

    /**
     * Post accountcheck
     * checking prerequisites.
     */
    public function accountcheck(Request $request): JsonResponse|RedirectResponse
    {
        // Validation rules and custom messages
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:20',
            'last_name' => 'required|string|max:20',
            'user_name' => [
                'required',
                'regex:/^[a-zA-Z0-9 _\-@.]{3,20}$/',
                'unique:users,user_name',
            ],
            'email' => 'required|string|max:50|email|unique:users,email',
            'password' => [
                'required',
                'regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[~*!@$#%_+.?:,{ }])[A-Za-z\d~*!@$#%_+.?:,{ }]{8,16}$/',
            ],
            'cache_driver' => 'required|string',
            'redis_host' => 'nullable|required_if:cache_driver,redis|string',
            'redis_password' => 'nullable|string',
            'redis_port' => 'nullable|required_if:cache_driver,redis|numeric',
            'environment' => 'required|string',
        ], [
            'user_name.regex' => \Lang::get('installer_messages.user_name_regex'),
            'password.regex' => \Lang::get('installer_messages.password_regex'),
            'redis_host.required_if' => \Lang::get('installer_messages.redis_host_required'),
            'redis_port.required_if' => \Lang::get('installer_messages.redis_port_required'),
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return errorResponse($validator->errors(), 400);
        }

        try {
            // Create the user
            // Redis configuration based on environment
            if ($request->input('cache_driver') === 'redis') {
                $redisConfig = array_filter([
                    'redis_host' => $request->input('redis_host'),
                    'redis_password' => $request->input('redis_password'),
                    'redis_port' => $request->input('redis_port'),
                ]);

                try {
                    $redis = new Client([
                        'scheme' => 'tcp',
                        'host' => $redisConfig['redis_host'] ?? '',
                        'password' => $redisConfig['redis_password'] ?? null,
                        'port' => $redisConfig['redis_port'] ?? 6379,
                    ]);

                    $redis->ping();
                } catch (Exception $exception) {
                    return errorResponse($exception->getMessage(), 400);
                }

                $this->updateInstallEnv($request->input('environment'), $request->input('cache_driver'), $redisConfig);
            }

            $timezone = $request->input('timezone');
            $language = $request->input('language');
            $changed = $this->changeLanguage($language);
            $timeZoneId = Timezone::where('name', $timezone)->value('id');

            if (! $changed) {
                return back()->with('fails', 'Invalid language');
            }

            $user = User::where('id', 1)->update([
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'user_name' => strtolower((string) $request->input('user_name')),
                'email' => strtolower((string) $request->input('email')),
                'password' => Hash::make($request->input('password')),
                'active' => 1,
                'role' => 'admin',
                'mobile_verified' => 1,
                'email_verified' => 1,
            ]);

            // Update the initial company settings
            DB::transaction(function () use ($timeZoneId): void {
                Setting::where('id', 1)
                    ->update([
                        'title' => 'Agora Invoicing',
                        'favicon_title' => 'Agora Invoicing',
                        'favicon_title_client' => 'Agora Invoicing',
                        'admin_logo' => null,
                        'logo' => null,
                        'fav_icon' => null,
                        'timezone_id' => $timeZoneId,
                    ]);
            });

            // checking if the user have been created
            if ($user) {
                Cache::forever('getting-started', 'getting-started');
                Cache::forever('env', $request->input('environment'));
            }

            // Return success response
            return successResponse(__('installer_messages.setup_completed'), '201');
        } catch (Exception $exception) {
            // Return error response in case of exception
            return errorResponse($exception->getMessage(), 400);
        }
    }

    /**
     * @return array{id: mixed, name: non-falsy-string}[]
     */
    public function getTimeZoneDropDown(): array
    {
        $timezonesList = Timezone::get();
        $display = [];
        foreach ($timezonesList as $timezone) {
            $location = $timezone->location;
            if ($location) {
                $start = strpos((string) $location, '(');
                $end = strpos((string) $location, ')', $start + 1);
                $length = $end - $start;
                $result = substr((string) $location, $start + 1, $length - 1);
                $display[] = ['id' => $timezone->id, 'name' => '('.$result.')'.' '.$timezone->name];
            }
        }

        return $display;
    }

    public function getLang(): JsonResponse
    {
        $language = Cache::get('language', config('app.locale'));
        $lang = Lang::get('installer_messages', [], $language);
        $currentLang = $language;

        return successResponse('', [
            'lang' => $lang,
            'currentLang' => $currentLang,
        ]);
    }

    public function languageList(): JsonResponse
    {
        try {
            $languageList = array_map(basename(...), File::directories(lang_path()));
            $languages = [];

            foreach ($languageList as $key => $langLocale) {
                $language = [];
                $language['id'] = $key;
                $language['locale'] = $langLocale;
                $languageArray = \Config::get('languages.'.$langLocale, ['', '']);
                $language['name'] = $languageArray[0];
                $language['translation'] = $languageArray[1];
                $languages[] = $language;
            }

            return successResponse('', collect($languages)->sortBy('name')->values()->all());
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse($exception->getMessage());
        }
    }

    public function storeLanguage(StoreLanguageRequest $request): JsonResponse
    {
        try {
            $language = $request->input('language');
            if (! Auth::check()) {
                Session::put('language', $language);
                Cache::forever('language', $language);

                return successResponse('Language set successfully');
            }

            /** @var User $user */
            $user = Auth::user();
            $user->language = $language;
            $user->save();

            return successResponse('Language set successfully');
        } catch (Exception) {
            return errorResponse('error could not change the language');
        }
    }

    public function dbsetup(Request $request): RedirectResponse
    {
        // server requirements error checking
        if (Session::has('fails')) {
            Session::flush();
        }

        $errorCount = $request->input('count');

        if ($errorCount == '0' && $errorCount == 0) {
            Cache::forever('pre-db', 'pre-db');

            return to_route('db-setup');
        }

        return back();
    }

    public function database(Request $request): View|RedirectResponse
    {
        // checking if the installation is running for the first time or not
        if (Cache::get('config-check') == 'config-check') {
            return view('themes.default1.installer.databaseMigration');
        }

        return to_route('config-check');
    }

    public function databasePage(Request $request): View|RedirectResponse
    {
        Session::flush();
        // Database Setup Page
        if (Cache::get('pre-db') == 'pre-db') {
            return view('themes.default1.installer.dbSetup');
        }

        return redirect()->to('/probe.php');
    }

    public function account(Request $request): View|RedirectResponse
    {
        // checking if the installation is running for the first time or not,getting-started page
        if (Cache::get('config-check') == 'config-check') {
            Cache::put('timezone', $request['timezone']);

            return view('themes.default1.installer.view5');
        }

        return to_route('db-setup');
    }

    public function finalize(): View|RedirectResponse
    {
        // final page -> login url
        if (Cache::get('getting-started') == 'getting-started') {
            $environment = Cache::get('env');
            $this->updateInstallEnv($environment);
            Session::flush();

            return view('themes.default1.installer.finalPage');
        }

        return to_route('get-start');
    }

    private function changeLanguage(string $lang): bool
    {
        $path = base_path('lang');  // Path to check available language packages
        if (array_key_exists($lang, Config::get('languages')) && in_array($lang, scandir($path))) {
            Cache::forever('lang', $lang);
            DB::table('settings')->where('id', '=', 1)
                ->update(['content' => $lang]);

            return true;
        }

        return false;
    }

    public function storeLanguageForUsers(StoreLanguageRequest $request): JsonResponse
    {
        try {
            $language = $request->input('language');
            Session::put('language', $language);
            if (! Auth::check()) {
                return successResponse('Language set successfully');
            }

            /** @var User $user */
            $user = Auth::user();
            $user->language = $language;

            $user->save();

            return successResponse('Language set successfully');
        } catch (Exception) {
            return errorResponse('error could not change the language');
        }
    }
}
