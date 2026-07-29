<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Plugins\Payment\Gateways;

use App\Plugins\Payment\Exceptions\PaymentException;
use App\Plugins\Payment\Exceptions\SignatureVerificationException;
use App\Plugins\Payment\Gateways\RazorpayGateway;
use PHPUnit\Framework\TestCase;

class RazorpayGatewayTest extends TestCase
{
    private const KEY_SECRET = 'test_secret_123';

    private function gateway(): RazorpayGateway
    {
        return new RazorpayGateway('rzp_test_key', self::KEY_SECRET);
    }

    public function test_capture_payment_verifies_subscription_signature(): void
    {
        $paymentId = 'pay_test_123';
        $subscriptionId = 'sub_test_456';
        $signature = hash_hmac('sha256', $paymentId.'|'.$subscriptionId, self::KEY_SECRET);

        $result = $this->gateway()->capturePayment([
            'razorpay_subscription_id' => $subscriptionId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
        ]);

        $this->assertTrue($result->paid);
        $this->assertSame($paymentId, $result->reference);
    }

    public function test_capture_payment_rejects_invalid_subscription_signature(): void
    {
        $this->expectException(SignatureVerificationException::class);

        $this->gateway()->capturePayment([
            'razorpay_subscription_id' => 'sub_test_456',
            'razorpay_payment_id' => 'pay_test_123',
            'razorpay_signature' => 'not_a_real_signature',
        ]);
    }

    public function test_capture_payment_throws_when_neither_order_nor_subscription_id_present(): void
    {
        $this->expectException(PaymentException::class);

        $this->gateway()->capturePayment([
            'razorpay_payment_id' => 'pay_test_123',
            'razorpay_signature' => 'sig',
        ]);
    }

    /**
     * The order_id branch fetches the order from Razorpay's API after
     * signature verification succeeds (so callers get real, gateway-verified
     * notes/amount — see InvoicePaymentService::referenceBelongsToInvoice() —
     * rather than just the client-supplied payload echoed back). That makes
     * this branch require live network, unlike the subscription branch above,
     * mirroring StripeGateway::capturePayment() (which has always retrieved
     * the PaymentIntent from Stripe's API rather than verifying a signature
     * alone). With fake test credentials the fetch itself fails — this test
     * proves the signature check still passes first (reaching the fetch
     * stage, not rejected as a bad signature) by asserting the resulting
     * exception is a generic PaymentException, not a SignatureVerificationException.
     */
    public function test_capture_payment_still_verifies_order_signature(): void
    {
        $paymentId = 'pay_test_123';
        $orderId = 'order_test_456';
        $signature = hash_hmac('sha256', $orderId.'|'.$paymentId, self::KEY_SECRET);

        try {
            $this->gateway()->capturePayment([
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ]);
            $this->fail('Expected a PaymentException from the order fetch (fake test credentials, no real Razorpay account behind them).');
        } catch (PaymentException $exception) {
            $this->assertNotInstanceOf(SignatureVerificationException::class, $exception);
        }
    }
}
