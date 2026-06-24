<?php

/**
 * The MIT License
 * Copyright (c) 2007 Andy Smith.
 */

namespace App\Http\Controllers\Common\Twitter;

/**
 * The HMAC-SHA1 signature method uses the HMAC-SHA1 signature algorithm as defined in [RFC2104]
 * where the Signature Base String is the text and the key is the concatenated values (each first
 * encoded per Parameter Encoding) of the Consumer Secret and Token Secret, separated by an '&'
 * character (ASCII code 38) even if empty.
 *   - Chapter 9.2 ("HMAC-SHA1").
 * @codeCoverageIgnore
 */
class HmacSha1 extends SignatureMethod
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'HMAC-SHA1';
    }

    /**
     * {@inheritdoc}
     */
    public function buildSignature(Request $request, Consumer $consumer, ?Token $token = null): string
    {
        $signatureBase = $request->getSignatureBaseString();

        $parts = [$consumer->secret, $token instanceof Token ? $token->secret : ''];

        $parts = Util::urlencodeRfc3986($parts);

        $key = implode('&', (array) $parts);

        return base64_encode(hash_hmac('sha1', $signatureBase, $key, binary: true));
    }
}
