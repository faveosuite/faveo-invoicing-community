<?php

namespace Razorpay\Api;

class Utility
{
    const SHA256 = 'sha256';

    public function verifyPaymentSignature($attributes)
    {
        $actualSignature = $attributes['razorpay_signature'];

        $paymentId = $attributes['razorpay_payment_id'];

        if (isset($attributes['razorpay_order_id']) === true)
        {
            $orderId = $attributes['razorpay_order_id'];

            $payload = $orderId . '|' . $paymentId;
        }
        else if (isset($attributes['razorpay_subscription_id']) === true)
        {
            $subscriptionId = $attributes['razorpay_subscription_id'];

            $payload = $paymentId . '|' . $subscriptionId;
        }
        else if (isset($attributes['razorpay_payment_link_id']) === true)
        {
            $paymentLinkId     = $attributes['razorpay_payment_link_id'];

            $paymentLinkRefId  = $attributes['razorpay_payment_link_reference_id'];

            $paymentLinkStatus = $attributes['razorpay_payment_link_status'];

            $payload = $paymentLinkId . '|'. $paymentLinkRefId . '|' . $paymentLinkStatus . '|' . $paymentId;
        }
        else
        {
            throw new Errors\SignatureVerificationError(
                'Either razorpay_order_id or razorpay_subscription_id or razorpay_payment_link_id must be present.');
        }

        $secret = Api::getSecret();

        self::verifySignature($payload, $actualSignature, $secret);
    }

    public function verifyWebhookSignature($payload, $actualSignature, $secret)
    {
        self::verifySignature($payload, $actualSignature, $secret);
    }

    public function verifySignature($payload, $actualSignature, $secret)
    {
        $expectedSignature = hash_hmac(self::SHA256, $payload, $secret);

        // Use lang's built-in hash_equals if exists to mitigate timing attacks
        if (function_exists('hash_equals'))
        {
            $verified = hash_equals($expectedSignature, $actualSignature);
        }
        else
        {
            $verified = $this->hashEquals($expectedSignature, $actualSignature);
        }

        if ($verified === false)
        {
            throw new Errors\SignatureVerificationError(
                'Invalid signature passed');
        }
    }

    public function generateOnboardingSignature($data, $secret){
        $jsonStr = json_encode($data);
        return $this->encrypt($jsonStr, $secret);
    }
    
    private function encrypt($dataToEncrypt, $secret) {
        try {
            $key = substr($secret, 0, 16);

            // Generate a fresh random 12-byte nonce per call (fixes AES-GCM nonce reuse).
            // A static IV derived from the key allows keystream recovery and tag forgery
            // (NIST SP 800-38D §8.3 Forbidden Attack) using only two captured ciphertexts.
            $iv = random_bytes(12);

            $cipher = 'aes-128-gcm';
            $tag = '';
            $encryptedData = openssl_encrypt($dataToEncrypt, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);

            if ($encryptedData === false) {
                throw new \Exception('Encryption failed');
            }

            // Output format: iv (12 bytes) || ciphertext || tag (16 bytes), hex-encoded.
            // Receiver must read the first 24 hex chars as the IV before decrypting.
            return bin2hex($iv . $encryptedData . $tag);
        } catch (\Exception $e) {
            throw new \Exception('Encryption failed: ' . $e->getMessage());
        }
    }

    private function hashEquals($expectedSignature, $actualSignature)
    {
        if (strlen($expectedSignature) === strlen($actualSignature))
        {
            $res = $expectedSignature ^ $actualSignature;
            $return = 0;

            for ($i = strlen($res) - 1; $i >= 0; $i--)
            {
                $return |= ord($res[$i]);
            }

            return ($return === 0);
        }

        return false;
    }
}
