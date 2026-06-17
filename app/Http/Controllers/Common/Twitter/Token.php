<?php

/**
 * The MIT License
 * Copyright (c) 2007 Andy Smith.
 */

namespace App\Http\Controllers\Common\Twitter;

use Stringable;

class Token implements Stringable
{
    /**
     * @param  string  $key  The OAuth Token
     * @param  string  $secret  The OAuth Token Secret
     */
    public function __construct(public $key, public $secret)
    {
    }

    /**
     * Generates the basic string serialization of a token that a server
     * would respond to request_token and access_token calls with.
     */
    public function __toString(): string
    {
        return sprintf('oauth_token=%s&oauth_token_secret=%s',
            Util::urlencodeRfc3986($this->key),
            Util::urlencodeRfc3986($this->secret)
        );
    }
}
