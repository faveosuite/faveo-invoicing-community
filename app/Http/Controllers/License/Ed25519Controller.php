<?php

namespace App\Http\Controllers\License;

use App\Http\Controllers\Controller;
use Exception;

class Ed25519Controller extends Controller
{
    /**
     * Generate a new Ed25519 key pair.
     *
     * @return array ['publicKey' => string, 'privateKey' => string]
     */
    public function generateKeyPair(): array
    {
        $keyPair = sodium_crypto_sign_keypair();

        return [
            'publicKey' => base64_encode(sodium_crypto_sign_publickey($keyPair)),
            'privateKey' => base64_encode(sodium_crypto_sign_secretkey($keyPair)),
        ];
    }

    /**
     * Sign a message with the private key.
     *
     * @param string $message The message to sign
     * @param string $privateKey Base64 encoded private key
     * @return string Base64 encoded signature
     */
    public function sign(string $message, string $privateKey): string
    {
        $secretKey = base64_decode($privateKey);
        $signature = sodium_crypto_sign_detached($message, $secretKey);

        return base64_encode($signature);
    }

    /**
     * Verify a signature with the public key.
     *
     * @param string $message The original message
     * @param string $signature Base64 encoded signature
     * @param string $publicKey Base64 encoded public key
     * @return bool True if signature is valid
     */
    public function verify(string $message, string $signature, string $publicKey): bool
    {
        $sig = base64_decode($signature);
        $pubKey = base64_decode($publicKey);

        return sodium_crypto_sign_verify_detached($sig, $message, $pubKey);
    }

    /**
     * Sign a message and return the signed message (message + signature).
     *
     * @param string $message The message to sign
     * @param string $privateKey Base64 encoded private key
     * @return string Base64 encoded signed message
     */
    public function signMessage(string $message, string $privateKey): string
    {
        $secretKey = base64_decode($privateKey);
        $signedMessage = sodium_crypto_sign($message, $secretKey);

        return base64_encode($signedMessage);
    }

    /**
     * Open a signed message and return the original message.
     *
     * @param string $signedMessage Base64 encoded signed message
     * @param string $publicKey Base64 encoded public key
     * @return string|false Original message or false if verification fails
     */
    public function openSignedMessage(string $signedMessage, string $publicKey)
    {
        $signed = base64_decode($signedMessage);
        $pubKey = base64_decode($publicKey);

        return sodium_crypto_sign_open($signed, $pubKey);
    }
}
