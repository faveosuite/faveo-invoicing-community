<?php

namespace CacheDriver;

use App\Http\Controllers\Controller;

class HandleCacheController extends Controller
{

    public static $configFile;

    public function __construct()
    {
        $this::$configFile = parse_ini_file(__DIR__.'/constant.ini');
    }

    /**
     * Modify cache files
     * @param array $options
     * @return void
     * @throws \Throwable
     */
    public function modify(array $options)
    {
        $config = '';

        foreach ($options as $key => $option) {
            $key = strtoupper($key);
            $config .= "$key=$option\n";
        }

        file_put_contents(__DIR__.'/constant.ini', $config);

        throw_unless(array_key_exists('DEFAULT',  $this::$configFile) && $options['default'] == parse_ini_file(__DIR__.'/constant.ini')['DEFAULT'], 'The driver does not updated in the configuration file');
    }

    /**
     * Get Constant values from anyWhere
     * @param $constant
     * @param $default
     * @return mixed|null
     */
    public static function value($constant, $default = null)
    {
        $iniContents = (new static())::$configFile;

        return array_key_exists($constant, $iniContents) ? $iniContents[$constant] : $default;
    }

    /**
     * Get all constants from specific driver
     * @param $driver
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public static function all($driver)
    {
        return config('cache.stores.'.$driver);
    }
}