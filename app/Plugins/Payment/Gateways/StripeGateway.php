<?php

namespace App\Plugins\Payment\Gateways;

use App\Plugins\Payment\Contracts\CardPaymentGateway;
use App\Plugins\Payment\Contracts\PaymentGateway;
use App\Plugins\Payment\Contracts\SubscriptionGateway;
use App\Plugins\Payment\Dto\Customer;
use App\Plugins\Payment\Dto\PaymentRequest;
use App\Plugins\Payment\Dto\PaymentResult;
use App\Plugins\Payment\Dto\PaymentSession;
use App\Plugins\Payment\Dto\SubscriptionRequest;
use App\Plugins\Payment\Dto\SubscriptionResult;
use App\Plugins\Payment\Exceptions\PaymentException;
use App\Plugins\Payment\Support\Money;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Stripe\Webhook;
use Throwable;

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
final readonly class StripeGateway implements CardPaymentGateway, PaymentGateway, SubscriptionGateway
{
    /** @var array<int, string> */
    private const array SUPPORTED = [
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
        private string $secretKey,
        private string $publishableKey = '',
        private string $webhookSecret = '',
    ) {
    }

    public function name(): string
    {
        return 'Stripe';
    }

    public function createPayment(PaymentRequest $request): PaymentSession
    {
        try {
            $session = $this->client()->checkout->sessions->create([ // @phpstan-ignore argument.type
                'mode' => 'payment',
                'ui_mode' => 'embedded',
                // Completion is handled by the embedded checkout onComplete
                // callback, not a redirect — keeps the payer in the host page.
                'redirect_on_completion' => 'never',
                'customer_email' => $request->customer?->email,
                // India export regulations require the customer's name + address;
                // Checkout collects the billing address for export transactions.
                'billing_address_collection' => 'required',
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
                    'setup_future_usage' => $request->saveForFutureUse ? 'off_session' : null,
                ]),
                'customer_creation' => $request->saveForFutureUse ? 'always' : null,
                'metadata' => $this->stringMetadata($request->metadata),
            ], [
                // Stable across retries for the same reference + amount, so a
                // double submit reuses one session instead of creating duplicates.
                // saveForFutureUse is part of the key too — it changes the actual
                // params sent (customer + setup_future_usage), and Stripe rejects
                // reusing a key with different params (e.g. re-checking out the
                // same invoice with the auto-renew checkbox toggled differently).
                'idempotency_key' => 'pay_'.$request->reference.'_'.md5($request->currency.'|'.$request->amount.'|'.($request->saveForFutureUse ? 1 : 0)),
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
        } catch (ApiErrorException $apiErrorException) {
            throw new PaymentException($apiErrorException->getMessage(), (int) $apiErrorException->getCode(), $apiErrorException);
        }
    }

    /**
     * Open a card payment via a PaymentIntent, for a custom in-page card UI.
     *
     * Card-only (no redirect-based methods), so the browser can confirm it with
     * Stripe Elements + confirmCardPayment; 3D Secure is handled by that call.
     */
    public function createCardPayment(PaymentRequest $request): PaymentSession
    {
        try {
            $params = [
                'amount' => Money::toMinor($request->amount, $request->currency),
                'currency' => strtolower($request->currency),
                'description' => $request->description ?: $request->reference,
                'payment_method_types' => ['card'],
                'receipt_email' => $request->customer?->email,
                'metadata' => $this->stringMetadata($request->metadata),
            ];

            // Saving the card for a later off-session charge (e.g. auto-renewal)
            // requires a Customer object to attach the payment method to —
            // setup_future_usage alone is not reusable without one.
            if ($request->saveForFutureUse) {
                $params['customer'] = $this->client()->customers->create(array_filter([
                    'name' => $request->customer?->name,
                    'email' => $request->customer?->email,
                ]))->id;
                $params['setup_future_usage'] = 'off_session';
            }

            // India export regulations require the customer's name + address on
            // export transactions; supply it as shipping (the description above
            // covers the required goods/services description). Stripe declines an
            // export charge without it.
            if ($shipping = $this->shippingFrom($request->customer)) {
                $params['shipping'] = $shipping;
            }

            $intent = $this->client()->paymentIntents->create($params, [ // @phpstan-ignore argument.type
                // Stable across retries for the same reference + amount, so a double
                // submit reuses one intent instead of creating duplicates. saveForFutureUse
                // is part of the key too — see createPayment()'s idempotency_key comment.
                'idempotency_key' => 'pi_'.$request->reference.'_'.md5($request->currency.'|'.$request->amount.'|'.($request->saveForFutureUse ? 1 : 0)),
            ]);

            return new PaymentSession(
                gateway: $this->name(),
                id: $intent->id,
                clientConfig: [
                    'client_secret' => $intent->client_secret,
                    'payment_intent_id' => $intent->id,
                    'publishable_key' => $this->publishableKey,
                    // So the client can skip re-confirming an intent that an
                    // earlier (idempotent) attempt already completed.
                    'status' => (string) $intent->status,
                ],
                raw: $intent->toArray(),
            );
        } catch (ApiErrorException $apiErrorException) {
            throw new PaymentException($apiErrorException->getMessage(), (int) $apiErrorException->getCode(), $apiErrorException);
        }
    }

    /**
     * Verify a completed payment. Accepts either a Checkout Session
     * ({session_id}) or a PaymentIntent ({payment_intent}, custom card UI).
     *
     * @param  array{session_id?: string, payment_intent?: string}  $payload
     */
    public function capturePayment(array $payload): PaymentResult
    {
        try {
            if (! empty($payload['payment_intent'])) {
                $intent = $this->client()->paymentIntents->retrieve($payload['payment_intent']);

                return new PaymentResult(
                    paid: $intent->status === 'succeeded',
                    gateway: $this->name(),
                    reference: $intent->id,
                    status: (string) $intent->status,
                    raw: $intent->toArray(),
                );
            }

            $sessionId = $payload['session_id'] ?? null;
            if (! $sessionId) {
                throw new PaymentException('Missing Stripe session_id or payment_intent.');
            }

            $session = $this->client()->checkout->sessions->retrieve($sessionId);
        } catch (ApiErrorException $apiErrorException) {
            throw new PaymentException($apiErrorException->getMessage(), (int) $apiErrorException->getCode(), $apiErrorException);
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
        } catch (ApiErrorException $apiErrorException) {
            throw new PaymentException($apiErrorException->getMessage(), (int) $apiErrorException->getCode(), $apiErrorException);
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
        } catch (ApiErrorException $apiErrorException) {
            throw new PaymentException($apiErrorException->getMessage(), (int) $apiErrorException->getCode(), $apiErrorException);
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
        } catch (Throwable) {
            return false;
        }
    }

    public function supportedCurrencies(): array
    {
        return self::SUPPORTED;
    }

    public function createSubscription(SubscriptionRequest $request): SubscriptionResult
    {
        try {
            $client = $this->client();

            // paymentMethodReference is the id of the PaymentIntent that saved the
            // card (verification charge or an opted-in purchase, both created with
            // setup_future_usage=off_session) — retrieve it for the customer +
            // payment method it attached, which become the subscription's default.
            $intent = $client->paymentIntents->retrieve((string) $request->paymentMethodReference);

            $product = $client->products->create(['name' => $request->planName]);

            $price = $client->prices->create([
                'unit_amount' => $request->amountMinor,
                'currency' => strtolower($request->currency),
                'recurring' => ['interval' => 'day', 'interval_count' => $request->intervalDays],
                'product' => $product->id,
            ]);

            $subscription = $client->subscriptions->create([ // @phpstan-ignore argument.type
                'customer' => $intent->customer,
                'items' => [['price' => $price->id]],
                'default_payment_method' => $intent->payment_method,
                'metadata' => $this->stringMetadata($request->metadata),
            ]);

            return new SubscriptionResult(
                gateway: $this->name(),
                id: $subscription->id,
                status: (string) $subscription->status,
                raw: $subscription->toArray(),
            );
        } catch (ApiErrorException $apiErrorException) {
            throw new PaymentException($apiErrorException->getMessage(), (int) $apiErrorException->getCode(), $apiErrorException);
        }
    }

    public function getSubscriptionStatus(string $subscriptionId): string
    {
        try {
            return (string) $this->client()->subscriptions->retrieve($subscriptionId)->status;
        } catch (ApiErrorException $apiErrorException) {
            throw new PaymentException($apiErrorException->getMessage(), (int) $apiErrorException->getCode(), $apiErrorException);
        }
    }

    public function cancelSubscription(string $subscriptionId): SubscriptionResult
    {
        try {
            $subscription = $this->client()->subscriptions->cancel($subscriptionId, []);

            return new SubscriptionResult(
                gateway: $this->name(),
                id: $subscription->id,
                status: (string) $subscription->status,
                raw: $subscription->toArray(),
            );
        } catch (ApiErrorException $apiErrorException) {
            throw new PaymentException($apiErrorException->getMessage(), (int) $apiErrorException->getCode(), $apiErrorException);
        }
    }

    public function updateSubscriptionPrice(string $subscriptionId, SubscriptionRequest $request): SubscriptionResult
    {
        try {
            $client = $this->client();
            $current = $client->subscriptions->retrieve($subscriptionId, []);

            // Only touch an active subscription, and only when the price differs.
            if ($current->status !== 'active' || (int) $current->plan->amount === $request->amountMinor) { // @phpstan-ignore property.notFound
                return new SubscriptionResult($this->name(), $current->id, (string) $current->status, $current->toArray());
            }

            $product = $client->products->create(['name' => $request->planName]);
            $price = $client->prices->create([
                'unit_amount' => $request->amountMinor,
                'currency' => strtolower($request->currency),
                'recurring' => ['interval' => 'day', 'interval_count' => $request->intervalDays],
                'product' => $product->id,
            ]);

            $updated = $client->subscriptions->update($subscriptionId, [
                'items' => [['id' => $current->items->data[0]->id, 'price' => $price->id]],
                'proration_behavior' => 'none',
            ]);

            // A price change that leaves the subscription inactive is cancelled;
            // flag it so the application can unsubscribe locally.
            if ($updated->status !== 'active') {
                $client->subscriptions->cancel($updated->id, []);

                return new SubscriptionResult($this->name(), $updated->id, (string) $updated->status, $updated->toArray() + ['cancelled' => true]);
            }

            return new SubscriptionResult($this->name(), $updated->id, (string) $updated->status, $updated->toArray());
        } catch (ApiErrorException $apiErrorException) {
            throw new PaymentException($apiErrorException->getMessage(), (int) $apiErrorException->getCode(), $apiErrorException);
        }
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
        return array_map(static fn (bool|float|int|string $v): string => (string) $v, $metadata);
    }

    /**
     * Build a Stripe shipping object (name + address) from a customer, for
     * India export compliance. Returns null when there isn't enough to be useful
     * (a name and at least a street line) so domestic charges aren't sent a
     * malformed shipping block.
     *
     * @return array{name: string, address: array<string, string>}|null
     */
    private function shippingFrom(?Customer $customer): ?array
    {
        if (! $customer || ! $customer->name || ! $customer->line1) {
            return null;
        }

        return [
            'name' => $customer->name,
            'address' => array_filter([
                'line1' => $customer->line1,
                'city' => $customer->city,
                'state' => $customer->state,
                'postal_code' => $customer->postalCode,
                'country' => $customer->country,
            ], static fn (?string $v): bool => $v !== null && $v !== ''),
        ];
    }
}
