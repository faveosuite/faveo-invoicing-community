<?php

// Set up error reporting:
if (! defined('E_DEPRECATED')) {
    define('E_DEPRECATED', 8192);
}
error_reporting(E_ALL | E_STRICT | E_DEPRECATED);

// Set default timezone to hide warnings:
date_default_timezone_set('Europe/London');

// Set encoding for manipulation of multi byte strings:
mb_internal_encoding('UTF-8');

// Set up path variables.
$ROOT = dirname(dirname(__FILE__));
$DATA = $ROOT.'/tests/data/twitter-text-conformance';

// Include required classes.
require_once $ROOT.'/lib/Twitter/Autolink.php';
require_once $ROOT.'/lib/Twitter/Extractor.php';
require_once $ROOT.'/lib/Twitter/HitHighlighter.php';
require_once $ROOT.'/tests/spyc/spyc.php';
