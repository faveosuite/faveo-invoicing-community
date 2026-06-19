<?php

namespace App\Http\Controllers\BillingInstaller;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Request;

class BillingDependencyController extends Controller
{
    public function __construct(private mixed $extensionCheckFrom)
    {
    }

    public function validateDirectory(string $basePath, int &$errorCount): mixed
    {
        try {
            $error = [];
            $this->validateStorageDirectory($basePath, $errorCount, $error);
            $this->validateBootstrapDirectory($basePath, $errorCount, $error);

            return $error;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Validate storage directory.
     *
     * @param  array<mixed>  $error
     * @return array<mixed>
     */
    private function validateStorageDirectory(string $basePath, int &$errorCount, array &$error): array
    {
        try {
            $storagePermission = is_readable($basePath.DIRECTORY_SEPARATOR.'storage') && is_writable($basePath.DIRECTORY_SEPARATOR.'storage');
            $storagePermissionColor = 'green';
            $storageMessage = 'Read/Write';
            if (! $storagePermission) {
                $storagePermissionColor = 'red';
                $errorCount += 1;
                $storageMessage = 'Directory should be readable and writable by your web server. Give preferred permissions as 755 for directory and 644 for files and owner as your web server user';
                if ($this->extensionCheckFrom == 'auto-update') {//
                    throw new Exception($storageMessage);
                }
            }

            $error[] = ['extensionName' => $basePath.'storage', 'color' => $storagePermissionColor, 'message' => $storageMessage, 'errorCount' => $errorCount];

            return $error;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Validate bootstrap directory.
     *
     * @param  array<mixed>  $error
     * @return array<mixed>
     */
    private function validateBootstrapDirectory(string $basePath, int &$errorCount, array &$error): array
    {
        try {
            $bootstrapPermission = is_readable($basePath.DIRECTORY_SEPARATOR.'bootstrap') && is_writable($basePath.DIRECTORY_SEPARATOR.'bootstrap');
            $bootStrapPermissionColor = 'green';
            $bootStrapMessage = 'Read/Write';
            if (! $bootstrapPermission) {
                $bootStrapPermissionColor = 'red';
                $errorCount += 1;
                $bootStrapMessage = 'This directory should be readable and writable by your web server. Give preferred permissions as 755 for directory and 644 for files and owner as your web server user';
                if ($this->extensionCheckFrom == 'auto-update') {//
                    throw new Exception($bootStrapMessage);
                }
            }

            $error[] = ['extensionName' => $basePath.'bootstrap', 'color' => $bootStrapPermissionColor, 'message' => $bootStrapMessage, 'errorCount' => $errorCount];

            return $error;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function validateRequisites(int &$errorCount): mixed
    {
        try {
            $requiredRequisites = json_decode((string) $this->getDependenciesJson())->requisites;
            $arrayOfRequisites = [];
            $requisiteDetails = null;
            foreach ($requiredRequisites as $requisite) {
                $requisiteDetails = $this->requisitesWithTheirStatus($arrayOfRequisites, $requisite, $errorCount);
            }

            return $requisiteDetails;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Get the json content of dependencies.
     */
    private function getDependenciesJson(): string|false
    {
        if ($this->extensionCheckFrom == 'probe') {
            return file_get_contents('../storage/billing-dependencies.json');
        }

        return file_get_contents(storage_path('billing-dependencies.json'));
    }

    /**
     * Extension that are required for Faveo to run.
     *
     * @param  array<mixed>  $requiredExtensions  Array of required extensions
     * @param  array  &$error  Array of errors
     */
    private function validateRequiredExtensions(array $requiredExtensions, array &$error, int &$errorCount): void
    {
        try {
            foreach ($requiredExtensions as $extension) {
                if (! extension_loaded($extension)) {
                    if ($this->extensionCheckFrom == 'probe') {
                        $errorCount += 1;
                        $error[] = ['extensionName' => $extension, 'key' => 'required'];
                    } else {
                        $extString = $extension." is not enabled<p>To enable this, please install the extension on your server and  update '".php_ini_loaded_file().sprintf("' to enable %s </p>", $extension)
                            .'<a href="https://support.faveohelpdesk.com/show/how-to-enable-required-php-extension-on-different-servers-for-faveo-installation" target="_blank">How to install PHP extensions on my server?</a>';
                        throw new Exception($extString);
                    }
                } elseif ($this->extensionCheckFrom == 'probe') {
                    $error[] = ['extensionName' => $extension, 'key' => 'no-error'];
                }
            }
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Extension that are optional for Faveo to run.
     *
     * @param  array<mixed>  $requiredExtensions  Array of required extensions
     * @param  array  &$error  Array of errors
     */
    private function validateOptionalExtensions(array $requiredExtensions, array &$error): void
    {
        try {
            foreach ($requiredExtensions as $extension) {
                if (! extension_loaded($extension)) {
                    if ($this->extensionCheckFrom == 'probe') {
                        $error[] = ['extensionName' => $extension, 'key' => 'optional'];
                    }
                } elseif ($this->extensionCheckFrom == 'probe') {
                    $error[] = ['extensionName' => $extension, 'key' => 'no-error'];
                }
            }
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Gets the Name and status of the requisites for Faveo.
     *
     * @param  array  &$arrayOfRequisites  Array with name and status
     * @param  string  $requisite  The name of the requisite to be checked
     * @return array<mixed>
     */
    private function requisitesWithTheirStatus(array &$arrayOfRequisites, $requisite, int &$errorCount): array
    {
        try {
            $dependencyObject = json_decode((string) $this->getDependenciesJson());
            switch ($requisite) {
                case 'PHP Version':
                    $minPhpVersionRequired = $dependencyObject->min_php_version;
                    $this->PhpVersionCheck($arrayOfRequisites, $errorCount, $minPhpVersionRequired);
                    break;

                case 'PHP exec function':
                    $this->execFunctionCheck($arrayOfRequisites, $errorCount);
                    break;

                case 'env':
                    if ($this->extensionCheckFrom == 'probe') {
                        $this->dotEnvFileCheck($arrayOfRequisites, $errorCount);
                    }

                    break;

                case 'max_execution_time':
                    if ($this->extensionCheckFrom == 'probe') {
                        $this->maxExecutionTimeCheck($arrayOfRequisites, $errorCount);
                    }

                    break;

                case 'allow_url_fopen':
                    if ($this->extensionCheckFrom == 'probe') {
                        $this->allowUrlFopen($arrayOfRequisites, $errorCount);
                    }

                    break;

                case 'app_url':
                    $this->appUrlcheck($arrayOfRequisites, $errorCount);

                    break;

                case 'ssl_certificate' :
                    $this->checkSSLCertificateOnDomain($arrayOfRequisites, $errorCount);
                    break;

                default:

                    break;
            }

            return $arrayOfRequisites;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Check the current PHP version is compatible or not for running Faveo.
     *
     * @param  array<mixed>  $arrayOfRequisites  Requisite details
     * @param  int  $errorCount  The count of errors occured
     * @return array<mixed>
     */
    private function PhpVersionCheck(array &$arrayOfRequisites, int &$errorCount, string $minPhpVersionRequired): array
    {
        try {
            $versionColor = 'green';
            $versionString = phpversion();
            if (version_compare(phpversion(), $minPhpVersionRequired, '>=') != 1) {
                $versionColor = 'red';
                $errorCount += 1;
                $versionString = phpversion().'. Please upgrade PHP Version to'.$minPhpVersionRequired.' or greater version';
                if ($this->extensionCheckFrom == 'auto-update') {//
                    throw new Exception($versionString);
                }
            }

            $arrayOfRequisites[] = ['extensionName' => 'PHP Version', 'connection' => $versionString, 'color' => $versionColor, 'errorCount' => $errorCount];

            return $arrayOfRequisites;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Check PHP exec function is enabled or not.
     *
     * @param  array<mixed>  $arrayOfRequisites  Requisite details
     * @param  int  $errorCount  The count of errors occured
     * @return array<mixed>
     */
    private function execFunctionCheck(array &$arrayOfRequisites, int &$errorCount): array
    {
        $execColor = 'green';
        $execString = 'Enabled';
        if (! $this->execEnabled()) {
            $execColor = '#F89C0D';
            $execString = 'exec function is not enabled. This is required for taking system backup. Please note system backup functionality will not work without it.';
            if ($this->extensionCheckFrom == 'auto-update') {//
                throw new Exception($execString);
            }
        }

        $arrayOfRequisites[] = ['extensionName' => 'PHP exec function', 'connection' => $execString, 'color' => $execColor, 'errorCount' => $errorCount];

        return $arrayOfRequisites;
    }

    /**
     * Check if exec() function is available.
     */
    public function execEnabled(): bool
    {
        try {
            // make a small test
            return function_exists('exec') && ! in_array('exec', array_map(trim(...), explode(', ', (string) ini_get('disable_functions'))));
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Check .env exists or not.
     *
     * @param  array<mixed>  $arrayOfRequisites  Requisite details
     * @param  int  $errorCount  The count of errors occured
     * @return array<mixed>
     */
    private function dotEnvFileCheck(array &$arrayOfRequisites, int &$errorCount): array
    {
        $env = '../.env';
        $envFound = is_file($env);
        $envColor = 'green';
        $envString = 'Not found';
        if ($envFound) {
            $errorCount += 1;
            $envColor = 'red';
            $envString = 'Yes Found. <p>Please delete .env file from your root directory.</p>';
        }

        $arrayOfRequisites[] = ['extensionName' => '.env file', 'connection' => $envString, 'color' => $envColor, 'errorCount' => $errorCount];

        return $arrayOfRequisites;
    }

    /**
     * Check maximum execution time.
     *
     * @param  array<mixed>  $arrayOfRequisites  Requisite details
     * @param  int  $errorCount  The count of errors occured
     * @return array<mixed>
     */
    private function maxExecutionTimeCheck(array &$arrayOfRequisites, int &$errorCount): array
    {
        $executionColor = 'green';
        $executionString = ini_get('max_execution_time').' (Maximum execution time is as per requirement)';
        if ((int) ini_get('max_execution_time') < 120) {
            $executionColor = '#F89C0D';
            $executionString = ini_get('max_execution_time').' (Maximum execution time is too low. Recommended execution time is 120 seconds)';
        }

        $arrayOfRequisites[] = ['extensionName' => 'Maximum execution time', 'connection' => $executionString, 'color' => $executionColor, 'errorCount' => $errorCount];

        return $arrayOfRequisites;
    }

    /**
     * Checks allow_url_enabled directive is enabled or not.
     *
     * @param  array<mixed>  $arrayOfRequisites  Requisite details
     * @param  int  $errorCount  The count of errors occured
     * @return array<mixed>
     */
    private function allowUrlFopen(array &$arrayOfRequisites, int &$errorCount): array
    {
        $color = 'green';
        $messsage = 'Enabled';
        if ((int) ini_get('allow_url_fopen') === 0) {
            $color = '#F89C0D';
            $messsage = 'Directive is disabled (It is recommended to keep this ON as few features in the system are dependent on this)';
        }

        $arrayOfRequisites[] = ['extensionName' => 'Allow url fopen', 'connection' => $messsage, 'color' => $color, 'errorCount' => $errorCount];

        return $arrayOfRequisites;
    }

    /**
     * Checks URL is valid or invalid.
     *
     * @param  array<mixed>  $arrayOfRequisites  Requisite details
     * @param  int  $errorCount  The count of errors occured
     * @return array<mixed>
     */
    private function appUrlcheck(array &$arrayOfRequisites, int &$errorCount): array
    {
        $color = 'green';
        $infoString = 'Valid';
        if (! filter_var('https://'.(string) Request::server('HTTP_HOST').(string) Request::server('REQUEST_URI'), FILTER_VALIDATE_URL)) {
            $errorCount += 1;
            $color = 'red';
            $infoString = "Invalid URL found <p>Make sure your domain/IP doesn't contain any special character other than dash( '-' ) and dot ( '.' )<p>";
            if ($this->extensionCheckFrom == 'auto-update') {//
                throw new Exception($infoString);
            }
        }

        $arrayOfRequisites[] = ['extensionName' => 'App URL', 'connection' => $infoString, 'color' => $color, 'errorCount' => $errorCount];

        return $arrayOfRequisites;
    }

    /**
     * Validate PHP extentions for probe page and auto-update module.
     *
     * @return array<mixed>     */
    public function validatePHPExtensions(int &$errorCount)
    {
        try {
            $error = [];
            $requiredExtensions = json_decode((string) $this->getDependenciesJson())->extensions;
            $this->validateRequiredExtensions($requiredExtensions->required, $error, $errorCount);
            $this->validateOptionalExtensions($requiredExtensions->optional, $error);

            return $error;
        } catch(Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @param  array<mixed>  $arrayOfRequisites
     * @return array<mixed>
     */
    public function checkSSLCertificateOnDomain(array &$arrayOfRequisites, int &$errorCount, mixed $cliAppUrl = null): array
    {
        $name = 'Domain SSL Certificate';
        try {
            $color = 'green';
            $infoString = 'Valid SSL certificate found, application can be served securely over HTTPS';
            $stream = stream_context_create(['ssl' => ['capture_peer_cert' => true]]);
            $sslHost = $cliAppUrl.'/cron-test.php';
            if (! $cliAppUrl) {
                $url = preg_replace('#probe.php|api/check-updates#', 'cron-test.php', (string) Request::server('REQUEST_URI'));
                $sslHost = 'https://'.(string) Request::server('HTTP_HOST').$url;
            }

            $oldError = error_reporting();
            error_reporting($oldError & ~E_WARNING);
            $read = fopen($sslHost, 'rb', use_include_path: false, context: $stream);
            error_reporting($oldError);
            if (! $read) {
                throw new Exception('Unable to open stream');
            }

            $context = stream_context_get_params($read);
            fclose($read);
            $noSSL = is_null($context['options']['ssl']['peer_certificate']);
            if ($noSSL) {
                throw new Exception($infoString);
            }

            $arrayOfRequisites[] = ['extensionName' => $name, 'connection' => $infoString, 'color' => $color, 'errorCount' => $errorCount];

            return $arrayOfRequisites;
        } catch (Exception $exception) {
            $infoString = 'The system can only be opened with secure protocol over HTTPS. Please ensure a valid SSL certificate is installed on the server to serve the application securely over HTTPS.';
            if ($exception->getMessage() === 'Unable to open stream') {
                $infoString = 'Failed to open stream: '.$infoString;
            }

            return $this->handleRequisiteErrors($arrayOfRequisites, $errorCount, $infoString);
        }
    }

    /**
     * @param  array<mixed>  $arrayOfRequisites
     * @return array<mixed>
     */
    private function handleRequisiteErrors(array &$arrayOfRequisites, int &$errorCount, string $infoString): array
    {
        $errorCount += 1;
        $color = 'red';
        if ($this->extensionCheckFrom == 'auto-update') {
            throw new Exception($infoString);
        }

        $arrayOfRequisites[] = ['extensionName' => 'Domain SSL Certificate', 'connection' => $infoString, 'color' => $color, 'errorCount' => $errorCount];

        return $arrayOfRequisites;
    }
}
