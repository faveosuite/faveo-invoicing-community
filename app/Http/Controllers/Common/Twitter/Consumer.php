<?php

declare(strict_types=1);

/**
 * The MIT License
 * Copyright (c) 2007 Andy Smith.
 */

namespace App\Http\Controllers\Common\Twitter;

use Stringable;

class Consumer implements Stringable
{
    /**
     * @param  string  $key
     * @param  string  $secret
     */
    public function __construct(public $key, public $secret, public mixed $callbackUrl = null) {}

    public function __toString(): string
    {
        return sprintf('Consumer[key=%s,secret=%s]', $this->key, $this->secret);
    }
}
