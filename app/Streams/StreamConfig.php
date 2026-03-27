<?php

namespace App\Streams;

class StreamConfig
{
    private static $configFile;

    private static function iniPath()
    {
        return storage_path('app/stream_config.ini');
    }

    private static function load()
    {
        $path = self::iniPath();

        if (! file_exists($path)) {
            file_put_contents($path, '');
        }

        self::$configFile = parse_ini_file($path) ?: [];
    }

    /**
     * Get a config value.
     */
    public static function value($key, $default = null)
    {
        self::load();

        return array_key_exists($key, self::$configFile) && self::$configFile[$key] !== ''
            ? self::$configFile[$key]
            : $default;
    }

    /**
     * Get all config values.
     */
    public static function all()
    {
        self::load();

        return self::$configFile;
    }

    /**
     * Save config values.
     */
    public static function modify(array $options)
    {
        $config = '';

        foreach ($options as $key => $value) {
            $key = strtoupper($key);
            $config .= "$key=$value\n";
        }

        file_put_contents(self::iniPath(), $config);
    }
}
