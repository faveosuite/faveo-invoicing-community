<?php

namespace App\Http\Controllers\License;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class EncryptDecryptController extends Controller
{
    /**
     * Encrypts the license data with the generated public key.
     * */
    public function encrypt($data, $orderNumber)
    {
        $pubkey = Storage::disk('public')->get('publicKey-'.$orderNumber.'.txt');
        if (openssl_public_encrypt($data, $encrypted, $pubkey, OPENSSL_PKCS1_PADDING)) {
            $data = base64_encode($encrypted);
        } else {
            throw new \Exception( __('message.unable_to_encrypt'));
        }

        return $data;
    }

    /**
     * Decrypts the license data with the generated private key.
     * */
    public function decrypt($orderNo)
    {
        $privkey = Storage::disk('public')->get('privateKey-'.$orderNo.'.txt');
        $data = Storage::disk('public')->get('faveo-license-{'.$orderNo.'}.txt');
        if (openssl_private_decrypt(base64_decode($data), $decrypted, $privkey, OPENSSL_PKCS1_PADDING)) {
            $data = $decrypted;
        } else {
            $data = '';
        }

        return $data;
    }

    /**
     * Generates the public key and private key for a particular order.
     * */
    public function generateKeys($orderNo)
    {
        $config = [
            'digest_alg' => 'sha512',
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];
        // Create the keypair
        $pair = openssl_pkey_new($config);
        // Get private key
        openssl_pkey_export($pair, $privatekey);
        // Get public key
        $publickey = openssl_pkey_get_details($pair)['key'];

        Storage::disk('public')->put('publicKey-'.$orderNo.'.txt', $publickey);
        Storage::disk('public')->put('privateKey-'.$orderNo.'.txt', $privatekey);
    }
}
