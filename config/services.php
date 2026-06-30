<?php

declare(strict_types=1);

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
        'client_id' => '',
        'client_secret' => '',
        'redirect' => '',
    ],
    'google' => [
        'client_id' => '',
        'client_secret' => '',
        'redirect' => '',
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
