<?php

namespace App\Plugins\Payment\Gateways;

use App\Plugins\Payment\Contracts\PaymentGateway;
use App\Plugins\Payment\Contracts\SubscriptionGateway;
use App\Plugins\Payment\Dto\PaymentRequest;
use App\Plugins\Payment\Dto\PaymentResult;
use App\Plugins\Payment\Dto\PaymentSession;
use App\Plugins\Payment\Dto\SubscriptionRequest;
use App\Plugins\Payment\Dto\SubscriptionResult;
use App\Plugins\Payment\Exceptions\PaymentException;
use App\Plugins\Payment\Exceptions\SignatureVerificationException;
use App\Plugins\Payment\Support\Money;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\Error;
use Razorpay\Api\Errors\SignatureVerificationError;

/**
 * Razorpay driver — Orders flow.
 *
 * createPayment() opens a server-side Order (auto-captured: payment_capture = 1)
 * and returns the Checkout options the browser passes to `new Razorpay(options)`.
 * capturePayment() verifies the signed Checkout handler response (HMAC-SHA256
 * over "order_id|payment_id") before reporting success — an unverified response
 * is never trusted and raises a {@see SignatureVerificationException}.
 *
 * Depends only on razorpay/razorpay. Construct it with the key id and secret,
 * an optional Checkout display name, and optionally the webhook secret (only
 * needed for verifyWebhook()).
 *
 * Usage (standalone):
 *   $razorpay = new RazorpayGateway($keyId, $keySecret, 'My Store');
 *   $session  = $razorpay->createPayment(new PaymentRequest(500, 'INR', 'INV-1001'));
 *   // open Checkout with $session->clientConfig; on the handler callback:
 *   $result   = $razorpay->capturePayment($handlerResponse); // throws on a bad signature
 */
final readonly class RazorpayGateway implements PaymentGateway, SubscriptionGateway
{
    /** @var array<int, string> */
    private const array SUPPORTED = [
        'AED', 'ALL', 'AMD', 'AUD', 'AZN', 'BBD', 'BDT', 'BHD', 'BIF', 'BMD', 'BND', 'BOB',
        'BAM', 'BWP', 'BZD', 'BSD', 'BRL', 'BGN', 'CAD', 'CHF', 'CLP', 'CNY', 'CVE', 'CRC',
        'CUP', 'CZK', 'DJF', 'DKK', 'DOP', 'EGP', 'ETB', 'EUR', 'FJD', 'GBP', 'GHS', 'GIP',
        'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'IQD', 'ISK',
        'JMD', 'JOD', 'JPY', 'KES', 'KHR', 'KGS', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LSL',
        'LRD', 'LKR', 'MAD', 'MDL', 'MKD', 'MMK', 'MNT', 'MOP', 'MGA', 'MWK', 'MYR', 'MVR',
        'MUR', 'MXN', 'NAD', 'NGN', 'NIO', 'NOK', 'NZD', 'OMR', 'PEN', 'PHP', 'PKR', 'PLN',
        'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SCR', 'SEK', 'SGD', 'SLL', 'SOS',
        'SZL', 'THB', 'TND', 'TRY', 'TTD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VND',
        'VUV', 'XAF', 'XCD', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMW',
    ];

    public function __construct(
        private string $keyId,
        private string $keySecret,
        private string $checkoutName = 'Payment',
        private string $webhookSecret = '',
    ) {
    }

    public function name(): string
    {
        return 'Razorpay';
    }

    public function createPayment(PaymentRequest $request): PaymentSession
    {
        try {
            $order = $this->api()->order->create([
                'receipt' => $request->reference,
                'amount' => Money::toMinor($request->amount, $request->currency),
                'currency' => $request->currency,
                'payment_capture' => 1,
                'notes' => $this->stringMetadata($request->metadata),
            ]);
        } catch (Error $error) {
            throw new PaymentException($error->getMessage(), (int) $error->getCode(), $error);
        }

        $config = [
            'key' => $this->keyId,
            'order_id' => $order['id'],
            'amount' => $order['amount'],
            'currency' => $order['currency'],
            'name' => $this->checkoutName,
            'description' => $request->description ?: $request->reference,
            'prefill' => array_filter([
                'name' => $request->customer?->name,
                'email' => $request->customer?->email,
                'contact' => $request->customer?->phone,
            ]),
            'notes' => $this->stringMetadata($request->metadata),
            'theme' => ['color' => '#F37254'],
        ];

        return new PaymentSession(
            gateway: $this->name(),
            id: $order['id'],
            clientConfig: $config,
            raw: $order->toArray(),
        );
    }

    /**
     * Verifies either a one-time Order payment (razorpay_order_id) or a
     * Subscription authorization (razorpay_subscription_id) — the Razorpay
     * SDK's verifyPaymentSignature() already branches on which one is present
     * and hashes the correct payload for each, so one method covers both.
     *
     * @param  array{razorpay_order_id?: string, razorpay_subscription_id?: string, razorpay_payment_id?: string, razorpay_signature?: string}  $payload
     */
    public function capturePayment(array $payload): PaymentResult
    {
        if (empty($payload['razorpay_payment_id']) || empty($payload['razorpay_signature'])) {
            throw new PaymentException('Missing Razorpay field: razorpay_payment_id or razorpay_signature.');
        }

        if (empty($payload['razorpay_order_id']) && empty($payload['razorpay_subscription_id'])) {
            throw new PaymentException('Missing Razorpay field: razorpay_order_id or razorpay_subscription_id.');
        }

        try {
            $this->api()->utility->verifyPaymentSignature(array_filter([
                'razorpay_order_id' => $payload['razorpay_order_id'] ?? null,
                'razorpay_subscription_id' => $payload['razorpay_subscription_id'] ?? null,
                'razorpay_payment_id' => $payload['razorpay_payment_id'],
                'razorpay_signature' => $payload['razorpay_signature'],
            ]));
        } catch (SignatureVerificationError $e) {
            throw new SignatureVerificationException($e->getMessage(), (int) $e->getCode(), $e);
        } catch (Error $e) {
            throw new PaymentException($e->getMessage(), (int) $e->getCode(), $e);
        }

        // A verified signature only proves this (payment_id, order_id) pair is
        // a real, completed Razorpay payment — not what it was *for*. Fetch the
        // order itself (not just echo back the client-supplied payload) so the
        // caller can check the notes/amount it was actually created with —
        // e.g. InvoicePaymentService::confirm() cross-checks notes.invoice_id
        // before fulfilling, so a real payment for a cheap invoice can't be
        // replayed here to fulfil a different, more expensive one.
        $orderData = [];
        if (! empty($payload['razorpay_order_id'])) {
            try {
                $orderData = $this->api()->order->fetch($payload['razorpay_order_id'])->toArray();
            } catch (Error $error) {
                throw new PaymentException($error->getMessage(), (int) $error->getCode(), $error);
            }
        }

        return new PaymentResult(
            paid: true,
            gateway: $this->name(),
            reference: $payload['razorpay_payment_id'],
            status: 'captured',
            raw: $orderData ?: $payload,
        );
    }

    public function refundPayment(string $reference, ?float $amount = null): PaymentResult
    {
        try {
            $payment = $this->api()->payment->fetch($reference);
            $params = $amount !== null
                ? ['amount' => Money::toMinor($amount, (string) $payment['currency'])]
                : [];
            $refund = $payment->refund($params);
        } catch (Error $error) {
            throw new PaymentException($error->getMessage(), (int) $error->getCode(), $error);
        }

        return new PaymentResult(
            paid: false,
            gateway: $this->name(),
            reference: $refund['id'] ?? null,
            status: (string) ($refund['status'] ?? 'refunded'),
            raw: $refund->toArray(),
        );
    }

    public function getPaymentStatus(string $reference): string
    {
        try {
            return (string) $this->api()->payment->fetch($reference)['status'];
        } catch (Error $error) {
            throw new PaymentException($error->getMessage(), (int) $error->getCode(), $error);
        }
    }

    public function verifyWebhook(string $rawPayload, string $signature): bool
    {
        if ($this->webhookSecret === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $rawPayload, $this->webhookSecret);

        return hash_equals($expected, $signature);
    }

    public function supportedCurrencies(): array
    {
        return self::SUPPORTED;
    }

    public function createSubscription(SubscriptionRequest $request): SubscriptionResult
    {
        try {
            $api = $this->api();

            ['period' => $period, 'interval' => $interval, 'totalCount' => $totalCount] =
                $this->planPeriodAndCount($request->intervalDays, $request->totalCount);

            $plan = $api->plan->create([
                'period' => $period,
                'interval' => $interval,
                'item' => [
                    'name' => $request->planName,
                    'amount' => $request->amountMinor,
                    'currency' => $request->currency,
                ],
            ]);

            $subscription = $api->subscription->create(array_filter([
                'plan_id' => $plan['id'],
                'total_count' => $totalCount,
                'quantity' => 1,
                'expire_by' => $request->expireBy,
                'start_at' => $request->startAt,
                'customer_notify' => 1,
                // A one-time addon charged immediately on authorization — only
                // correct when the current cycle's payment is actually due now.
                'addons' => $request->includeUpfrontCharge ? [['item' => [
                    'name' => $request->planName,
                    'amount' => $request->amountMinor,
                    'currency' => $request->currency,
                ]]] : null,
            ], static fn ($v): bool => $v !== null));

            return new SubscriptionResult(
                gateway: $this->name(),
                id: $subscription['id'],
                status: (string) $subscription['status'],
                raw: $subscription->toArray(),
            );
        } catch (Error $error) {
            throw new PaymentException($error->getMessage(), (int) $error->getCode(), $error);
        }
    }

    /**
     * Picks the Plan's period/interval and caps the mandate's total span at
     * 20 years, regardless of how long the underlying billing period is.
     *
     * Plans a year or longer map onto Razorpay's own "yearly" period instead
     * of "monthly" with an inflated interval (e.g. 96 for an 8-year plan) —
     * more natural, and keeps interval values small regardless of plan length.
     *
     * The cap itself exists because $requestedTotalCount's raw default (100
     * cycles) combined with a long interval (e.g. an annual plan, 12 months)
     * asks the card network to register a mandate valid for 100 years, which
     * real banks reject. Confirmed in production: a real card mandate
     * registration failed with a generic SERVER_ERROR at Razorpay's
     * card_mandate_process step for exactly this reason.
     *
     * @return array{period: string, interval: int, totalCount: int}
     */
    private function planPeriodAndCount(int $intervalDays, int $requestedTotalCount): array
    {
        if ($intervalDays >= 300) {
            $period = 'yearly';
            $interval = max(1, (int) round($intervalDays / 365));
            $maxSpanUnits = 20; // years
        } else {
            $period = 'monthly';
            $interval = max(1, (int) round($intervalDays / 30));
            $maxSpanUnits = 20 * 12; // months
        }

        $totalCount = max(1, (int) min($requestedTotalCount, intdiv($maxSpanUnits, $interval)));

        return ['period' => $period, 'interval' => $interval, 'totalCount' => $totalCount];
    }

    public function getSubscriptionStatus(string $subscriptionId): string
    {
        try {
            return (string) $this->api()->subscription->fetch($subscriptionId)['status'];
        } catch (Error $error) {
            throw new PaymentException($error->getMessage(), (int) $error->getCode(), $error);
        }
    }

    public function cancelSubscription(string $subscriptionId): SubscriptionResult
    {
        try {
            $subscription = $this->api()->subscription->fetch($subscriptionId)->cancel();

            return new SubscriptionResult(
                gateway: $this->name(),
                id: $subscription['id'] ?? $subscriptionId,
                status: (string) ($subscription['status'] ?? 'cancelled'),
                raw: $subscription->toArray(),
            );
        } catch (Error $error) {
            throw new PaymentException($error->getMessage(), (int) $error->getCode(), $error);
        }
    }

    public function updateSubscriptionPrice(string $subscriptionId, SubscriptionRequest $request): SubscriptionResult
    {
        try {
            $api = $this->api();
            $current = $api->subscription->fetch($subscriptionId);
            $currentPlan = $api->plan->fetch($current['plan_id']);
            $interval = (int) round($request->intervalDays / 30);

            // Already billing the requested price/interval — nothing to do.
            if ((int) $currentPlan->item->amount === $request->amountMinor
                && $currentPlan->item->currency === $request->currency
                && (int) $currentPlan->interval === $interval) {
                return new SubscriptionResult($this->name(), $current['id'], (string) $current['status'], $current->toArray());
            }

            // Razorpay can only re-plan an active subscription.
            if ($current['status'] !== 'active') {
                return new SubscriptionResult($this->name(), $current['id'], (string) $current['status'], $current->toArray());
            }

            $plan = $api->plan->create([
                'period' => 'monthly',
                'interval' => $interval,
                'item' => [
                    'name' => $request->planName,
                    'amount' => $request->amountMinor,
                    'currency' => $request->currency,
                ],
            ]);

            $updated = $api->subscription->fetch($subscriptionId)->update([
                'plan_id' => $plan['id'],
                'quantity' => 1,
                'remaining_count' => $current['remaining_count'],
                'customer_notify' => 1,
                'schedule_change_at' => 'cycle_end',
            ]);

            return new SubscriptionResult($this->name(), $updated['id'], (string) $updated['status'], $updated->toArray());
        } catch (Error $error) {
            throw new PaymentException($error->getMessage(), (int) $error->getCode(), $error);
        }
    }

    private function api(): Api
    {
        if ($this->keyId === '' || $this->keySecret === '') {
            throw new PaymentException('Razorpay credentials are not configured.');
        }

        return new Api($this->keyId, $this->keySecret);
    }

    /**
     * @param  array<string, scalar>  $metadata
     * @return array<string, string>
     */
    private function stringMetadata(array $metadata): array
    {
        return array_map(static fn (bool|float|int|string $v): string => (string) $v, $metadata);
    }
}
