<?php

namespace App\License\Services;

use App\License\Models\LicenseSigningKey;
use SodiumException;

/**
 * Signs and verifies local license file payloads with Ed25519.
 *
 * The secret key is generated once, stored encrypted, and never leaves this
 * server. Only the public key (see publicKey()) needs to be embedded in the
 * licensed product so it can verify a license file entirely offline.
 */
class Ed25519SigningService
{
    /**
     * @throws SodiumException
     */
    public function sign(string $payload): string
    {
        $key = $this->getOrCreateKeyPair();

        return base64_encode(sodium_crypto_sign_detached($payload, $key->secretKeyRaw()));
    }

    public function verify(string $payload, string $signature): bool
    {
        $decodedSignature = base64_decode($signature, strict: true);
        if ($decodedSignature === false || $decodedSignature === '') {
            return false;
        }

        try {
            return sodium_crypto_sign_verify_detached(
                $decodedSignature,
                $payload,
                $this->getOrCreateKeyPair()->publicKeyRaw()
            );
        } catch (SodiumException) {
            return false;
        }
    }

    /**
     * @throws SodiumException
     */
    public function publicKey(): string
    {
        return $this->getOrCreateKeyPair()->public_key;
    }

    /**
     * @throws SodiumException
     */
    private function getOrCreateKeyPair(): LicenseSigningKey
    {
        return LicenseSigningKey::query()->oldest('id')->first() ?? $this->generateKeyPair();
    }

    /**
     * @throws SodiumException
     */
    private function generateKeyPair(): LicenseSigningKey
    {
        $keyPair = sodium_crypto_sign_keypair();

        return LicenseSigningKey::create([
            'public_key' => base64_encode(sodium_crypto_sign_publickey($keyPair)),
            'secret_key' => base64_encode(sodium_crypto_sign_secretkey($keyPair)),
        ]);
    }
}
