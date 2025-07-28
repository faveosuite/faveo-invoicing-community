<?php

namespace App\Http\Controllers\BillingInstaller;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;

class BillingDependencyController extends Controller
{
    private $extensionCheckFrom;

    public function __construct($extensionCheckFrom)
    {
        $this->extensionCheckFrom = $extensionCheckFrom;
    }

    public function validateDirectory($basePath, &$errorCount)
    {
        try {
            $error = [];
            $this->validateStorageDirectory($basePath, $errorCount, $error);
            $this->validateBootstrapDirectory($basePath, $errorCount, $error);

            return $error;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Validate storage directory.
     */
    private function validateStorageDirectory($basePath, &$errorCount, &$error)
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
                    throw new \Exception($storageMessage);
                }
            }
            array_push($error, ['extensionName' => $basePath.'storage', 'color' => $storagePermissionColor, 'message' => $storageMessage, 'errorCount' => $errorCount]);

            return $error;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Validate bootstrap directory.
     */
    private function validateBootstrapDirectory($basePath, &$errorCount, &$error)
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
                    throw new \Exception($bootStrapMessage);
                }
            }
            array_push($error, ['extensionName' => $basePath.'bootstrap', 'color' => $bootStrapPermissionColor, 'message' => $bootStrapMessage, 'errorCount' => $errorCount]);

            return $error;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function validateRequisites(&$errorCount)
    {
        try {
            $requiredRequisites = json_decode($this->getDependenciesJson())->requisites;
            $arrayOfRequisites = [];
            foreach ($requiredRequisites as $requisite) {
                $requisiteDetails = $this->requisitesWithTheirStatus($arrayOfRequisites, $requisite, $errorCount);
            }

            return $requisiteDetails;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * Get the json content of dependencies.
     */
    private function getDependenciesJson()
    {
        if ($this->extensionCheckFrom == 'probe') {
            return file_get_contents('../storage/billing-dependencies.json');
        } else {
            return file_get_contents(storage_path('billing-dependencies.json'));
        }
    }

    /**
     * Extension that are required for Faveo to run.
     *
     * @param  array  $requiredExtensions  Array of required extensions
     * @param  array  &$error  Array of errors
     */
    private function validateRequiredExtensions(array $requiredExtensions, array &$error, int &$errorCount)
    {
        try {
            foreach ($requiredExtensions as $extension) {
                if (! extension_loaded($extension)) {
                    if ($this->extensionCheckFrom == 'probe') {
                        $errorCount += 1;
                        array_push($error, ['extensionName' => $extension, 'key' => 'required']);
                    } else {
                        $extString = "$extension is not enabled<p>To enable this, please install the extension on your server and  update '".php_ini_loaded_file()."' to enable $extension </p>"
                            .'<a href="https://support.faveohelpdesk.com/show/how-to-enable-required-php-extension-on-different-servers-for-faveo-installation" target="_blank">How to install PHP extensions on my server?</a>';
                        throw new \Exception($extString);
                    }
                } else {
                    if ($this->extensionCheckFrom == 'probe') {
                        array_push($error, ['extensionName' => $extension, 'key' => 'no-error']);
                    }
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Extension that are optional for Faveo to run.
     *
     * @param  array  $requiredExtensions  Array of required extensions
     * @param  array  &$error  Array of errors
     */
    private function validateOptionalExtensions(array $requiredExtensions, array &$error)
    {
        try {
            foreach ($requiredExtensions as $extension) {
                if (! extension_loaded($extension)) {
                    if ($this->extensionCheckFrom == 'probe') {
                        array_push($error, ['extensionName' => $extension, 'key' => 'optional']);
                    }
                } else {
                    if ($this->extensionCheckFrom == 'probe') {
                        array_push($error, ['extensionName' => $extension, 'key' => 'no-error']);
                    }
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Gets the Name and status of the requisites for Faveo.
     *
     * @param  array  &$arrayOfRequisites  Array with name and status
     * @param  string  $requisite  The name of the requisite to be checked
     */
    private function requisitesWithTheirStatus(array &$arrayOfRequisites, $requisite, int &$errorCount)
    {
        try {
            $dependencyObject = json_decode($this->getDependenciesJson());
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
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Check the current PHP version is compatible or not for running Faveo.
     *
     * @param  array  $arrayOfRequisites  Requisite details
     * @param  int  $errorCount  The count of errors occured
     */
    private function PhpVersionCheck(array &$arrayOfRequisites, int &$errorCount, $minPhpVersionRequired)
    {
        try {
            $versionColor = 'green';
            $versionString = phpversion();
            if (version_compare(phpversion(), $minPhpVersionRequired, '>=') != 1) {
                $versionColor = 'red';
                $errorCount += 1;
                $versionString = phpversion().'. Please upgrade PHP Version to'.$minPhpVersionRequired.' or greater version';
                if ($this->extensionCheckFrom == 'auto-update') {//
                    throw new \Exception($versionString);
                }
            }
            array_push($arrayOfRequisites, ['extensionName' => 'PHP Version', 'connection' => $versionString, 'color' => $versionColor, 'errorCount' => $errorCount]);

            return $arrayOfRequisites;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Check PHP exec function is enabled or not.
     *
     * @param  array  $arrayOfRequisites  Requisite details
     * @param  int  $errorCount  The count of errors occured
     */
    private function execFunctionCheck(array &$arrayOfRequisites, int &$errorCount)
    {
        $execColor = 'green';
        $execString = 'Enabled';
        if (! $this->execEnabled()) {
            $execColor = '#F89C0D';
            $execString = 'exec function is not enabled. This is required for taking system backup. Please note system backup functionality will not work without it.';
            if ($this->extensionCheckFrom == 'auto-update') {//
                throw new \Exception($execString);
            }
        }
        array_push($arrayOfRequisites, ['extensionName' => 'PHP exec function', 'connection' => $execString, 'color' => $execColor, 'errorCount' => $errorCount]);

        return $arrayOfRequisites;
    }

    /**
     * Check if exec() function is available.
     *
     * @return bool
     */
    public function execEnabled()
    {
        try {
            // make a small test
            return function_exists('exec') && ! in_array('exec', array_map('trim', explode(', ', ini_get('disable_functions'))));
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * Check .env exists or not.
     *
     * @param  array  $arrayOfRequisites  Requisite details
     * @param  int  $errorCount  The count of errors occured
     */
    private function dotEnvFileCheck(array &$arrayOfRequisites, int &$errorCount)
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
        array_push($arrayOfRequisites, ['extensionName' => '.env file', 'connection' => $envString, 'color' => $envColor, 'errorCount' => $errorCount]);

        return $arrayOfRequisites;
    }

    /**
     * Check maximum execution time.
     *
     * @param  array  $arrayOfRequisites  Requisite details
     * @param  int  $errorCount  The count of errors occured
     */
    private function maxExecutionTimeCheck(array &$arrayOfRequisites, int &$errorCount)
    {
        $executionColor = 'green';
        $executionString = ini_get('max_execution_time').' (Maximum execution time is as per requirement)';
        if ((int) ini_get('max_execution_time') < 120) {
            $executionColor = '#F89C0D';
            $executionString = ini_get('max_execution_time').' (Maximum execution time is too low. Recommended execution time is 120 seconds)';
        }
        array_push($arrayOfRequisites, ['extensionName' => 'Maximum execution time', 'connection' => $executionString, 'color' => $executionColor, 'errorCount' => $errorCount]);

        return $arrayOfRequisites;
    }

    /**
     * Checks allow_url_enabled directive is enabled or not.
     *
     * @param  array  $arrayOfRequisites  Requisite details
     * @param  int  $errorCount  The count of errors occured
     */
    private function allowUrlFopen(array &$arrayOfRequisites, int &$errorCount)
    {
        $color = 'green';
        $messsage = 'Enabled';
        if (! (int) ini_get('allow_url_fopen')) {
            $color = '#F89C0D';
            $messsage = 'Directive is disabled (It is recommended to keep this ON as few features in the system are dependent on this)';
        }
        array_push($arrayOfRequisites, ['extensionName' => 'Allow url fopen', 'connection' => $messsage, 'color' => $color, 'errorCount' => $errorCount]);

        return $arrayOfRequisites;
    }

    /**
     * Checks URL is valid or invalid.
     *
     * @param  array  $arrayOfRequisites  Requisite details
     * @param  int  $errorCount  The count of errors occured
     */
    private function appUrlcheck(array &$arrayOfRequisites, int &$errorCount)
    {
        $color = 'green';
        $infoString = 'Valid';
        if (! filter_var('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], FILTER_VALIDATE_URL)) {
            $errorCount += 1;
            $color = 'red';
            $infoString = "Invalid URL found <p>Make sure your domain/IP doesn't contain any special character other than dash( '-' ) and dot ( '.' )<p>";
            if ($this->extensionCheckFrom == 'auto-update') {//
                throw new \Exception($infoString);
            }
        }
        array_push($arrayOfRequisites, ['extensionName' => 'App URL', 'connection' => $infoString, 'color' => $color, 'errorCount' => $errorCount]);

        return $arrayOfRequisites;
    }

    /**
     * Validate PHP extentions for probe page and auto-update module.
     *
     * @param  string  $extensionCheckFrom  Whether the request is from probe page or auto-update module
     * @return array
     */
    public function validatePHPExtensions(&$errorCount)
    {
        try {
            $error = [];
            $requiredExtensions = json_decode($this->getDependenciesJson())->extensions;
            $this->validateRequiredExtensions($requiredExtensions->required, $error, $errorCount);
            $this->validateOptionalExtensions($requiredExtensions->optional, $error);

            return $error;
        } catch(\Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function checkSSLCertificateOnDomain(array &$arrayOfRequisites, int &$errorCount, $cliAppUrl = null)
    {
        $name = 'Domain SSL Certificate';
        try {
            $color = 'green';
            $infoString = 'Valid SSL certificate found, application can be served securely over HTTPS';
            $stream = stream_context_create(['ssl' => ['capture_peer_cert' => true]]);
            $sslHost = $cliAppUrl.'/cron-test.php';
            if (! $cliAppUrl) {
                $url = preg_replace('#probe.php|api/check-updates#', 'cron-test.php', $_SERVER['REQUEST_URI']);
                $sslHost = 'https://'.$_SERVER['HTTP_HOST'].$url;
            }
            $oldError = error_reporting();
            error_reporting($oldError & ~E_WARNING);
            $read = fopen($sslHost, 'rb', false, $stream);
            error_reporting($oldError);
            if (! $read) {
                throw new \Exception('Unable to open stream');
            }
            $context = stream_context_get_params($read);
            fclose($read);
            $noSSL = is_null($context['options']['ssl']['peer_certificate']);
            if ($noSSL) {
                throw new \Exception($infoString);
            }
            array_push($arrayOfRequisites, ['extensionName' => $name, 'connection' => $infoString, 'color' => $color, 'errorCount' => $errorCount]);

            return $arrayOfRequisites;
        } catch (\Exception $e) {
            $infoString = 'The system can only be opened with secure protocol over HTTPS. Please ensure a valid SSL certificate is installed on the server to serve the application securely over HTTPS.';
            if ($e->getMessage() == 'Unable to open stream') {
                $infoString = 'Failed to open stream: '.$infoString;
            }

            return $this->handleRequisiteErrors($arrayOfRequisites, $errorCount, $name, $infoString);
        }
    }

    private function handleRequisiteErrors(array &$arrayOfRequisites, int &$errorCount, string $name, $infoString)
    {
        $errorCount += 1;
        $color = 'red';
        if ($this->extensionCheckFrom == 'auto-update') {
            throw new \Exception($infoString);
        }
        array_push($arrayOfRequisites, ['extensionName' => 'Domain SSL Certificate', 'connection' => $infoString, 'color' => $color, 'errorCount' => $errorCount]);

        return $arrayOfRequisites;
    }
}
