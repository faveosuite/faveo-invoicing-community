<?php

declare(strict_types=1);

return [

    'razor_key' => '', // Shared by Razorpay

    'razor_secret' => '', // Shared by Razorpay

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

    // Private IPs/ranges the deploy wizard may connect to, e.g. "192.168.1.50,10.0.0.0/24".
    // Empty (default) = public servers only. For dev/QA VMs; leave empty in production.
    'whitelist_deploy_ip' => array_filter(array_map(trim(...), explode(',', (string) env('WHITELIST_DEPLOY_IP', '')))),
];
