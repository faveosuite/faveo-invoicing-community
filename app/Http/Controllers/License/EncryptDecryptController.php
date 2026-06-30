<?php

namespace App\Http\Controllers\License;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Storage;

class EncryptDecryptController extends Controller
{
    /**
     * Encrypts the license data with the generated public key.
     * */
    public function encrypt(mixed $data, string $orderNumber): string
    {
        $pubkey = Storage::disk('public')->get('publicKey-'.$orderNumber.'.txt');
        if (openssl_public_encrypt($data, $encrypted, (string) $pubkey, OPENSSL_PKCS1_OAEP_PADDING)) {
            $data = base64_encode((string) $encrypted);
        } else {
            throw new Exception(__('message.unable_to_encrypt'));
        }

        return $data;
    }

    /**
     * Decrypts the license data with the generated private key.
     * */
    public function decrypt(string $orderNo): mixed
    {
        $privkey = Storage::disk('public')->get('privateKey-'.$orderNo.'.txt');
        $data = Storage::disk('public')->get('faveo-license-{'.$orderNo.'}.txt');
        if (openssl_private_decrypt(base64_decode((string) $data), $decrypted, (string) $privkey, OPENSSL_PKCS1_PADDING)) {
            return $decrypted;
        }

        return '';
    }

    /**
     * Generates the public key and private key for a particular order.
     * */
    public function generateKeys(string $orderNo): void
    {
        $config = [
            'digest_alg' => 'sha512',
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];
        // Create the keypair
        $pair = openssl_pkey_new($config);
        // Get private key
        openssl_pkey_export($pair ?: '', $privatekey);
        // Get public key
        $details = $pair !== false ? openssl_pkey_get_details($pair) : false;
        $publickey = $details !== false ? ($details['key'] ?? '') : '';

        Storage::disk('public')->put('publicKey-'.$orderNo.'.txt', $publickey);
        Storage::disk('public')->put('privateKey-'.$orderNo.'.txt', $privatekey);
    }
}
