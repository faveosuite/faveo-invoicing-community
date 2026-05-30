<?php

namespace App\Plugins\Payment\Gateways;

use App\Plugins\Payment\Contracts\PaymentGateway;
use App\Plugins\Payment\Dto\PaymentRequest;
use App\Plugins\Payment\Dto\PaymentResult;
use App\Plugins\Payment\Dto\PaymentSession;
use App\Plugins\Payment\Exceptions\PaymentException;
use App\Plugins\Payment\Support\Money;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Stripe\Webhook;

/**
 * Stripe driver — embedded Checkout Session.
 *
 * Checkout is Stripe's recommended integration surface; embedded mode keeps the
 * payer inside the host page. Dynamic payment methods are enabled by NOT passing
 * `payment_method_types` (methods are configured from the Stripe Dashboard), and
 * no raw card data (PAN) ever reaches the server, so there is no PCI surface and
 * no deprecated Tokens/Charges API usage.
 *
 * Depends only on stripe/stripe-php. Construct it with a secret key, the
 * publishable key (passed back to the client for Stripe.js), and optionally the
 * webhook signing secret (only needed for verifyWebhook()).
 *
 * Usage (standalone):
 *   $stripe  = new StripeGateway($secretKey, $publishableKey);
 *   $session = $stripe->createPayment(new PaymentRequest(49.99, 'USD', 'INV-1001'));
 *   // hand $session->clientConfig to the browser; later, on completion:
 *   $result  = $stripe->capturePayment(['session_id' => $session->id]);
 *   if ($result->paid) { ... }
 */
final class StripeGateway implements PaymentGateway
{
    /** @var array<int, string> */
    private const SUPPORTED = [
        'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD',
        'BDT', 'BGN', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BWP', 'BYN', 'BZD', 'CAD',
        'CDF', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD',
        'EGP', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD',
        'HKD', 'HNL', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'ISK', 'JMD', 'JPY', 'KES', 'KGS',
        'KHR', 'KMF', 'KRW', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'MAD', 'MDL',
        'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD',
        'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG',
        'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SEK', 'SGD', 'SHP', 'SOS',
        'SRD', 'SZL', 'THB', 'TJS', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD',
        'UYU', 'UZS', 'VND', 'VUV', 'WST', 'XAF', 'XCD', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMW',
    ];

    public function __construct(
        private readonly string $secretKey,
        private readonly string $publishableKey = '',
        private readonly string $webhookSecret = '',
    ) {
    }

    public function name(): string
    {
        return 'Stripe';
    }

    public function createPayment(PaymentRequest $request): PaymentSession
    {
        try {
            $session = $this->client()->checkout->sessions->create([
                'mode' => 'payment',
                'ui_mode' => 'embedded',
                // Completion is handled by the embedded checkout onComplete
                // callback, not a redirect — keeps the payer in the host page.
                'redirect_on_completion' => 'never',
                'customer_email' => $request->customer?->email,
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => strtolower($request->currency),
                        'unit_amount' => Money::toMinor($request->amount, $request->currency),
                        'product_data' => [
                            'name' => $request->description ?: $request->reference,
                        ],
                    ],
                ]],
                'payment_intent_data' => array_filter([
                    'description' => $request->description,
                ]),
                'metadata' => $this->stringMetadata($request->metadata),
            ], [
                // Stable across retries for the same reference + amount, so a
                // double submit reuses one session instead of creating duplicates.
                'idempotency_key' => 'pay_'.$request->reference.'_'.md5($request->currency.'|'.$request->amount),
            ]);

            return new PaymentSession(
                gateway: $this->name(),
                id: $session->id,
                clientConfig: [
                    'client_secret' => $session->client_secret,
                    'session_id' => $session->id,
                    'publishable_key' => $this->publishableKey,
                ],
                raw: $session->toArray(),
            );
        } catch (ApiErrorException $e) {
            throw new PaymentException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * @param  array{session_id?: string}  $payload
     */
    public function capturePayment(array $payload): PaymentResult
    {
        $sessionId = $payload['session_id'] ?? null;
        if (! $sessionId) {
            throw new PaymentException('Missing Stripe session_id.');
        }

        try {
            $session = $this->client()->checkout->sessions->retrieve($sessionId);
        } catch (ApiErrorException $e) {
            throw new PaymentException($e->getMessage(), (int) $e->getCode(), $e);
        }

        return new PaymentResult(
            paid: $session->payment_status === 'paid',
            gateway: $this->name(),
            reference: $this->paymentIntentId($session->payment_intent),
            status: (string) $session->payment_status,
            raw: $session->toArray(),
        );
    }

    public function refundPayment(string $reference, ?float $amount = null): PaymentResult
    {
        try {
            $params = ['payment_intent' => $reference];

            if ($amount !== null) {
                // Refund amount must be in the original PaymentIntent's currency.
                $intent = $this->client()->paymentIntents->retrieve($reference);
                $params['amount'] = Money::toMinor($amount, (string) $intent->currency);
            }

            $refund = $this->client()->refunds->create($params);
        } catch (ApiErrorException $e) {
            throw new PaymentException($e->getMessage(), (int) $e->getCode(), $e);
        }

        return new PaymentResult(
            paid: false,
            gateway: $this->name(),
            reference: $refund->id,
            status: (string) $refund->status,
            raw: $refund->toArray(),
        );
    }

    public function getPaymentStatus(string $reference): string
    {
        try {
            return (string) $this->client()->paymentIntents->retrieve($reference)->status;
        } catch (ApiErrorException $e) {
            throw new PaymentException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function verifyWebhook(string $rawPayload, string $signature): bool
    {
        if ($this->webhookSecret === '') {
            return false;
        }

        try {
            Webhook::constructEvent($rawPayload, $signature, $this->webhookSecret);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function supportedCurrencies(): array
    {
        return self::SUPPORTED;
    }

    private function client(): StripeClient
    {
        if ($this->secretKey === '') {
            throw new PaymentException('Stripe secret key is not configured.');
        }

        return new StripeClient($this->secretKey);
    }

    /** A Checkout Session's payment_intent may be an id string or an expanded object. */
    private function paymentIntentId(mixed $paymentIntent): ?string
    {
        if (is_string($paymentIntent)) {
            return $paymentIntent;
        }

        return $paymentIntent->id ?? null;
    }

    /**
     * Stripe metadata values must be strings.
     *
     * @param  array<string, scalar>  $metadata
     * @return array<string, string>
     */
    private function stringMetadata(array $metadata): array
    {
        return array_map(static fn ($v) => (string) $v, $metadata);
    }
}
