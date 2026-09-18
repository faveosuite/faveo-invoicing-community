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

    /**
     * @return array{period: string, interval: int, totalCount: int}
     */
    private function planPeriodAndCount(int $intervalDays, int $requestedTotalCount = 100): array
    {
        $method = new \ReflectionMethod(RazorpayGateway::class, 'planPeriodAndCount');

        return $method->invoke($this->gateway(), $intervalDays, $requestedTotalCount);
    }

    // -------------------------------------------------------------------------
    // planPeriodAndCount — period/interval selection + 20-year mandate cap
    //
    // Regression coverage for a real production bug: createSubscription()
    // used to always request period=monthly with $requestedTotalCount (100)
    // cycles untouched. For a long-interval plan (e.g. an annual plan,
    // interval=12 months) that asks Razorpay to register a card mandate
    // valid for 100 years — real banks reject that, which is exactly what
    // caused a live "card_mandate_process" SERVER_ERROR failure.
    // -------------------------------------------------------------------------

    public function test_short_plan_keeps_monthly_period_and_original_total_count(): void
    {
        $result = $this->planPeriodAndCount(30); // 1 Month

        $this->assertSame('monthly', $result['period']);
        $this->assertSame(1, $result['interval']);
        $this->assertSame(100, $result['totalCount']); // unchanged — already well under 20 years
    }

    public function test_quarterly_plan_stays_monthly_but_total_count_is_capped_at_20_years(): void
    {
        $result = $this->planPeriodAndCount(90); // 3 Months — raw default would span 25 years

        $this->assertSame('monthly', $result['period']);
        $this->assertSame(3, $result['interval']);
        $this->assertSame(80, $result['totalCount']); // 80 * 3 months = exactly 20 years
    }

    public function test_annual_plan_switches_to_yearly_period_with_small_interval(): void
    {
        $result = $this->planPeriodAndCount(366); // 1 Year

        $this->assertSame('yearly', $result['period']);
        $this->assertSame(1, $result['interval']);
        $this->assertSame(20, $result['totalCount']); // 20 * 1 year = exactly 20 years
    }

    public function test_multi_year_plan_never_exceeds_the_20_year_mandate_cap(): void
    {
        $result = $this->planPeriodAndCount(2920); // 8 Years

        $this->assertSame('yearly', $result['period']);
        $this->assertSame(8, $result['interval']);
        $this->assertLessThanOrEqual(20, $result['totalCount'] * $result['interval']);
    }

    public function test_total_count_never_drops_below_one(): void
    {
        // An extreme interval (e.g. a 50-year plan) must still request at
        // least one billing cycle, never zero.
        $result = $this->planPeriodAndCount(18250);

        $this->assertGreaterThanOrEqual(1, $result['totalCount']);
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
