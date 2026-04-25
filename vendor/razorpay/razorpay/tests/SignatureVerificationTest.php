<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;

class SignatureVerificationTest extends TestCase
{
    private static $subscriptionId;

    public function setUp(): void
    {
        parent::setUp();
    }
    
    /**
     * Verify Payment verification
     */
    public function testPaymentVerification()
    {
        $orderId = 'order_IEIaMR65cu6nz3';
        $paymentId = 'pay_IH4NVgf4Dreq1l';
        $signature = '97f18ee6577a33ca7c37b949912de807b379afb3f39ccb571ffd76017463f8e5';

        $this->assertNull($this->api->utility->verifyPaymentSignature(array(
          'razorpay_order_id' => $orderId,
          'razorpay_payment_id' => $paymentId,
          'razorpay_signature' => $signature
        )));
    }
    
    /**
     * Verify PaymentLink verification
     */
    public function testPaymentLinkVerification()
    {
        $paymentLinkId = 'plink_IH3cNucfVEgV68';
        $paymentId = 'pay_IH3d0ara9bSsjQ';
        $paymentLinkReferenceId = 'TSsd1989';
        $paymentLinkStatus = 'paid';
        $signature = '57bab821bfe7ebcf41b32e362d16aa23d408b76c36317f960ae99a9301e4d364';

        $this->assertNull($this->api->utility->verifyPaymentSignature(array(
          'razorpay_payment_link_id' => $paymentLinkId,
          'razorpay_payment_link_reference_id' => $paymentLinkReferenceId,
          'razorpay_payment_link_status' => $paymentLinkStatus,
          'razorpay_payment_id' => $paymentId,
          'razorpay_signature' => $signature
        )));
    }

    /**
     * Verify Subscription verification
     */
    public function testSubscriptionVerification()
    {
        $subscriptionId = 'sub_ID6MOhgkcoHj9I';
        $paymentId = 'pay_IDZNwZZFtnjyym';
        $signature = 'cbbaabf163d61fc9346b794b5f906bc2f6b0d944be71bc0e6b5c35fa21eade44';

        $this->assertNull($this->api->utility->verifyPaymentSignature(array(
          'razorpay_subscription_id' => $subscriptionId,
          'razorpay_payment_id' => $paymentId,
          'razorpay_signature' => $signature
        )));
    }
}