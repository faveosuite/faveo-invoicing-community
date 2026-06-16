<?php

use Spatie\Referer\Sources\UtmSource;
use Spatie\Referer\Sources\RequestHeader;

return [

    /*
     * The key that will be used to remember the referer in the session.
     */
    'session_key' => 'referer',

    /*
     * The sources used to determine the referer.
     */
    'sources' => [
        UtmSource::class,
        RequestHeader::class,
    ],
];
