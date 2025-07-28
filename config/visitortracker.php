<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Don't record requests with the following field values
    |--------------------------------------------------------------------------
    |
    | The available fields are:
    | - ip
    | - method
    | - is_ajax
    | - url
    | - user_agent
    | - is_mobile
    | - is_bot
    | - bot
    | - os_family
    | - os
    | - browser_family
    | - browser
    | - is_login_attempt
    | - browser_language_family
    | - browser_language
    |
    */

    'dont_record' => [
        // Example 1:
        // ['ip' => '127.0.0.1'],
        //
        // Example 2 (all the listed fields fields have to have the specified values):
        // [
        //     'method' => 'GET',
        //     'is_ajax' => true,
        // ]
        //
        // Example 3 (at least one of the fields have to have the specified value):
        // ['method' => 'POST'],
        // ['is_ajax' => true],
    ],

    /*
    |--------------------------------------------------------------------------
    | Don't record visits with the following geoip values
    |--------------------------------------------------------------------------
    |
    | The available fields are:
    | - lat
    | - long
    | - country
    | - country_code
    | - city
    |
    */

    'dont_record_geoip' => [
        // Example:
        // [
        //     'country_code' => 'RU',
        //     'city' => 'Moscow'
        // ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Don't track certain groups of users
    |--------------------------------------------------------------------------
    */

    'dont_track_authenticated_users' => true,

    'dont_track_anonymous_users' => false,

    /*
    |--------------------------------------------------------------------------
    | Don't track requests from users with the following field values
    |--------------------------------------------------------------------------
    |
    | Specify any fields your users model has
    |
    */

    'dont_track_users' => [
        // Examples:
        // ['id' => 1],
        // ['email' => 'admin@example.com'],
        // ['is_admin' => true],
        // ['role_id' => 1]
    ],

    /*
    |--------------------------------------------------------------------------
    | Tracking login attempts
    |--------------------------------------------------------------------------
    |
    | Describe what a login attempt would look like
    |
    */

    'login_attempt' => [
        'url' => '/login',
        'method' => 'POST',
        'is_ajax' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | geoip
    |--------------------------------------------------------------------------
    |
    | Should the geoip data be collected?
    |
    | Set the geoip driver.
    | Supported: 'userinfo.io', 'ipstack.com'
    |
    */

    'geoip_on' => false,

    'geoip_driver' => 'ipstack.com',

    'ipstack_key' => '',

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    |
    | What table the users are stored in?
    |
    */

    'users_table' => 'users',

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    |
    | What layout should the views extend from?
    | What is the name of the main content section inside that layout?
    | How many results per page should be displayed?
    |
    */

    'layout' => 'themes.default1.common.stats',

    'section_content' => 'content',

    'results_per_page' => 15,

    /*
    |--------------------------------------------------------------------------
    | Views - Date & time
    |--------------------------------------------------------------------------
    |
    | What is the preferred format for the date and time?
    | What is your timezone?
    |
    */

    'datetime_format' => 'd M Y, H:i:s',

    'timezone' => 'Asia/Kolkata',
];
