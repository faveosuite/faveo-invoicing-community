<?php

namespace Tests\Unit\Backend\Http\Controllers\Auth;

use Tests\DBTestCase;

class SecurityTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
    }

    /**
     * List of URLs to test.
     */
    protected array $urls = [
        '/login',
        'show/cart',
        'password/reset',
        'group/1/1',
    ];

}
