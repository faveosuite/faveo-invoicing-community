<?php

namespace Razorpay\Api;

class OAuth extends Api {

    protected static $baseUrl = 'https://auth.razorpay.com';
    
    public function __construct(){}

    public static function getBaseUrl()
    {
        return static::$baseUrl;
    }

    public static function getFullUrl($relativeUrl, $apiVersion = null)
    {
        return static::getBaseUrl() . $apiVersion . "/". $relativeUrl;
    }

}