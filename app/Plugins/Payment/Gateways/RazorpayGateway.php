<?php

namespace App\Plugins\Payment\Gateways;

use App\Plugins\Payment\Contracts\PaymentGateway;
use App\Plugins\Payment\Dto\PaymentRequest;
use App\Plugins\Payment\Dto\PaymentResult;
use App\Plugins\Payment\Dto\PaymentSession;
use App\Plugins\Payment\Exceptions\PaymentException;
use App\Plugins\Payment\Exceptions\SignatureVerificationException;
use App\Plugins\Payment\Support\Money;
use Razorpay\Api\Api;
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
final class RazorpayGateway implements PaymentGateway
{
    /** @var array<int, string> */
    private const SUPPORTED = [
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
        private readonly string $keyId,
        private readonly string $keySecret,
        private readonly string $checkoutName = 'Payment',
        private readonly string $webhookSecret = '',
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
        } catch (\Razorpay\Api\Errors\Error $e) {
            throw new PaymentException($e->getMessage(), (int) $e->getCode(), $e);
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
     * @param  array{razorpay_order_id?: string, razorpay_payment_id?: string, razorpay_signature?: string}  $payload
     */
    public function capturePayment(array $payload): PaymentResult
    {
        foreach (['razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature'] as $key) {
            if (empty($payload[$key])) {
                throw new PaymentException("Missing Razorpay field: {$key}.");
            }
        }

        try {
            $this->api()->utility->verifyPaymentSignature([
                'razorpay_order_id' => $payload['razorpay_order_id'],
                'razorpay_payment_id' => $payload['razorpay_payment_id'],
                'razorpay_signature' => $payload['razorpay_signature'],
            ]);
        } catch (SignatureVerificationError $e) {
            throw new SignatureVerificationException($e->getMessage(), (int) $e->getCode(), $e);
        } catch (\Razorpay\Api\Errors\Error $e) {
            throw new PaymentException($e->getMessage(), (int) $e->getCode(), $e);
        }

        return new PaymentResult(
            paid: true,
            gateway: $this->name(),
            reference: $payload['razorpay_payment_id'],
            status: 'captured',
            raw: $payload,
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
        } catch (\Razorpay\Api\Errors\Error $e) {
            throw new PaymentException($e->getMessage(), (int) $e->getCode(), $e);
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
        } catch (\Razorpay\Api\Errors\Error $e) {
            throw new PaymentException($e->getMessage(), (int) $e->getCode(), $e);
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
        return array_map(static fn ($v) => (string) $v, $metadata);
    }
}
