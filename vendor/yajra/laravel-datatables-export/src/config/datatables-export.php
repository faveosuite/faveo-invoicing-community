<?php

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

return [

    /*
    |--------------------------------------------------------------------------
    | Method
    |--------------------------------------------------------------------------
    |
    | Method to use to iterate with the query results.
    | Options: lazy, cursor
    |
    | @link https://laravel.com/docs/eloquent#cursors
    | @link https://laravel.com/docs/eloquent#chunking-using-lazy-collections
    |
    */
    'method' => 'lazy',

    /*
    |--------------------------------------------------------------------------
    | Chunk Size
    |--------------------------------------------------------------------------
    |
    | Chunk size to be used when using lazy method.
    |
    */
    'chunk' => 1000,

    /*
    |--------------------------------------------------------------------------
    | Export filesystem disk
    |--------------------------------------------------------------------------
    |
    | Export filesystem disk where generated files will be stored.
    |
    */
    'disk' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Use S3 for final file destination
    |--------------------------------------------------------------------------
    |
    | After generating the file locally, it can be uploaded to s3.
    |
    */
    's3_disk' => '',

    /*
    |--------------------------------------------------------------------------
    | Mail from address
    |--------------------------------------------------------------------------
    |
    | Will be used to email report from this address.
    |
    */
    'mail_from' => env('MAIL_FROM_ADDRESS', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Date Format
    |--------------------------------------------------------------------------
    |
    | Default export format for date.
    |
    */
    'default_date_format' => 'yyyy-mm-dd',

    /*
    |--------------------------------------------------------------------------
    | Valid Date Formats
    |--------------------------------------------------------------------------
    |
    | List of valid date formats to be used for auto-detection.
    |
    */
    'date_formats' => [
        'mm/dd/yyyy',
        ...NumberFormat::DATE_TIME_OR_DATETIME_ARRAY,
        ...NumberFormat::TIME_OR_DATETIME_ARRAY,
    ],

    /*
    |--------------------------------------------------------------------------
    | Valid Text Formats
    |--------------------------------------------------------------------------
    |
    | List of valid text formats to be used.
    |
    */
    'text_formats' => [
        NumberFormat::FORMAT_TEXT,
        NumberFormat::FORMAT_GENERAL,
    ],

    /*
    |--------------------------------------------------------------------------
    | Purge Options
    |--------------------------------------------------------------------------
    |
    | Purge all exported by purge.days old files.
    |
    */
    'purge' => [
        'days' => 1,
    ],
];
