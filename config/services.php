<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'mandrill' => [
        'secret' => '',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => 'User',
        'secret' => '',
    ],
    'github' => [
        'client_id' => '', //Iv1.f2bdc32a0799a0a0
        'client_secret' => '', //92957c0954dc3cd476c855c0ecc3fac69e5f8196
        'redirect' => '', //https://developers.productdemourl.com/Agurmeen/agoraBilling/public/auth/callback
    ],
    'google' => [
        'client_id' => '', //15644360529-pufkmq6kgrcj0aqe4e6i23tjvb02a5mg.apps.googleusercontent.com
        'client_secret' => '', //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'
        'redirect' => '', //https://developers.productdemourl.com/Agurmeen/agoraBilling/public/auth/callback
    ],
    'twitter' => [
        'client_id' => '',
        'client_secret' => '',
        'redirect' => '',
    ],
    'linkedin' => [
        'client_id' => '',
        'client_secret' => '',
        'redirect' => '',
    ],
];
