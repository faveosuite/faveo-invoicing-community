<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

// This will trigger when the phpversion is below the required version
if (version_compare(phpversion(), '8.4', '<')) {
    $envFile = '..'.DIRECTORY_SEPARATOR.'.env';

    if (file_exists($envFile)) {
        $env_array = parse_ini_file($envFile, false, INI_SCANNER_RAW);

        foreach ($env_array as $key => $value) {
            if ($key === 'APP_URL' && $value !== 'true') {
                header('Location: '.$value.DIRECTORY_SEPARATOR.'updateToLatestPhp.html');
                exit();
            }
        }
    }
}

/**
 * Laravel - A PHP Framework For Web Artisans.
 *
 * @author   Taylor Otwell <taylor@laravel.com>
 */

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels great to relax.
|
*/

require __DIR__.'/../bootstrap/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
