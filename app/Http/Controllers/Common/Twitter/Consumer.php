<?php

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
     * @param  null  $callbackUrl
     */
    public function __construct(public $key, public $secret, public $callbackUrl = null)
    {
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return "Consumer[key=$this->key,secret=$this->secret]";
    }
}
