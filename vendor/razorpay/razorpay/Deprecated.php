<?php
/**
 * Backwards compatibility layer for Requests.
 *
 * Allows for Composer to autoload the old PSR-0 classes via the custom autoloader.
 * This prevents issues with _extending final classes_ (which was the previous solution).
 *
 * Please see the Changelog for the 2.0.4 release for upgrade notes.
 *
 * @package Requests
 *
 * @deprecated 2.0.4 Use the PSR-4 class names instead.
 */
define("REQUESTS_SILENCE_PSR0_DEPRECATIONS",true);

if (class_exists('WpOrg\Requests\Autoload') === false) {
	require_once __DIR__. 'libs/Requests-2.0.4/src/Autoload.php';
}

WpOrg\Requests\Autoload::register();