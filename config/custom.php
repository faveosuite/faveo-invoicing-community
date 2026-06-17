<?php

declare(strict_types=1);

return [

    'razor_key' => '', //Shared by Razorpay

    'razor_secret' => '', //Shared by Razorpay

    'displayCurrency' => '',

    'db_install' => env('DB_INSTALL', 0),
    'db_engine' => env('DB_ENGINE', 'InnoDB'),

    'google_chat' => env('GOOGLE_CHAT'),

    'storage_path' => env('STORAGE_PATH'),

    'cloud_job_url' => env('CLOUD_JOB_URL'),
    'cloud_job_url_normal' => env('CLOUD_JOB_URL_NORMAL'),
    'cloud_user' => env('CLOUD_USER'),
    'cloud_auth' => env('CLOUD_AUTH'),
    'cloud_oauth_token' => env('CLOUD_OAUTH_TOKEN'),
    'cloud_delete_job_url_normal' => env('CLOUD__DELETE_JOB_URL_NORMAL'),
    'cloud_delete_job_url_custom' => env('CLOUD__DELETE_JOB_URL_CUSTOM'),
];
