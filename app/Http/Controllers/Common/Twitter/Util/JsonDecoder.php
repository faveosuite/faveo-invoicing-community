<?php

namespace App\Http\Controllers\Common\Twitter\Util;

/**
 * @author louis <louis@systemli.org>
 *
 * @codeCoverageIgnore
 */
class JsonDecoder
{
    /**
     * Decodes a JSON string to stdObject or associative array.
     *
     * @param  string  $string
     * @param  bool  $asArray
     * @return array<mixed>|object
     */
    public static function decode($string, $asArray): mixed
    {
        if (PHP_VERSION_ID >= 50400 && ! (defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) { // @phpstan-ignore greaterOrEqual.alwaysTrue
            return json_decode($string, $asArray, 512, JSON_BIGINT_AS_STRING);
        }

        return json_decode($string, $asArray);
    }
}
