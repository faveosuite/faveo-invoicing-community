<?php

/**
 * The MIT License
 * Copyright (c) 2007 Andy Smith.
 */

namespace App\Http\Controllers\Common\Twitter;

/**
 * A class for implementing a Signature Method
 * See section 9 ("Signing Requests") in the spec.
 * @codeCoverageIgnore
 */
abstract class SignatureMethod
{
    /**
     * Needs to return the name of the Signature Method (ie HMAC-SHA1).
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Build up the signature
     * NOTE: The output of this function MUST NOT be urlencoded.
     * the encoding is handled in OAuthRequest when the final
     * request is serialized.
     *
     * @return string
     */
    abstract public function buildSignature(Request $request, Consumer $consumer, ?Token $token = null);

    /**
     * Verifies that a given signature is correct.
     *
     * @param  string  $signature
     * @return bool
     */
    public function checkSignature(Request $request, Consumer $consumer, Token $token, $signature)
    {
        $built = $this->buildSignature($request, $consumer, $token);

        // Check for zero length, although unlikely here
        if ((string) $built === '' || (string) $signature === '') {
            return false;
        }

        if (strlen($built) !== strlen($signature)) {
            return false;
        }

        // Avoid a timing leak with a (hopefully) time insensitive compare
        $result = 0;
        for ($i = 0; $i < strlen($signature); $i++) {
            $result |= ord($built[$i]) ^ ord($signature[$i]);
        }

        return $result === 0;
    }
}
