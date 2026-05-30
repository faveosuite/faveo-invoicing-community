<?php

namespace App\Plugins\Payment\Contracts;

use App\Plugins\Payment\Dto\PaymentRequest;
use App\Plugins\Payment\Dto\PaymentResult;
use App\Plugins\Payment\Dto\PaymentSession;

/**
 * Contract every payment gateway driver implements.
 *
 * Drivers in this package are deliberately application-agnostic: they are
 * constructed with their own credentials, take a plain {@see PaymentRequest}
 * value object, and return plain value objects ({@see PaymentSession},
 * {@see PaymentResult}). They depend only on their vendor SDK and PHP — never
 * on the host application's models, config, session or auth — so the package
 * can be lifted out and reused on its own.
 *
 * Lifecycle (the lowest common denominator of modern gateways):
 *   createPayment()    — open a payment and hand the client what its SDK needs.
 *   capturePayment()   — verify a client callback against the gateway and report
 *                        whether money was captured.
 *   refundPayment()    — refund a captured payment, fully or partially.
 *   getPaymentStatus() — read a payment's current status from the gateway.
 *   verifyWebhook()    — authenticate a raw webhook body against its signature.
 *
 * Every method that talks to the gateway throws a {@see \App\Plugins\Payment\Exceptions\PaymentException}
 * on failure (so "no exception" means success); only verifyWebhook returns a
 * plain bool rather than throwing.
 */
interface PaymentGateway
{
    /** Machine + display name of the gateway, e.g. "Stripe", "Razorpay". */
    public function name(): string;

    /**
     * Open a payment and return everything the client SDK needs to proceed.
     *
     * @throws \App\Plugins\Payment\Exceptions\PaymentException
     */
    public function createPayment(PaymentRequest $request): PaymentSession;

    /**
     * Verify a client callback against the gateway and report the outcome.
     *
     * @param  array<string, mixed>  $payload  Raw gateway callback fields.
     *
     * @throws \App\Plugins\Payment\Exceptions\SignatureVerificationException
     * @throws \App\Plugins\Payment\Exceptions\PaymentException
     */
    public function capturePayment(array $payload): PaymentResult;

    /**
     * Refund a captured payment, in full when $amount is null, otherwise the
     * given amount in MAJOR currency units.
     *
     * @param  string  $reference  Gateway transaction id (Stripe PaymentIntent id, Razorpay payment id).
     *
     * @throws \App\Plugins\Payment\Exceptions\PaymentException
     */
    public function refundPayment(string $reference, ?float $amount = null): PaymentResult;

    /**
     * Read a payment's current status from the gateway (the gateway's own
     * status string, e.g. Stripe "succeeded", Razorpay "captured").
     *
     * @throws \App\Plugins\Payment\Exceptions\PaymentException
     */
    public function getPaymentStatus(string $reference): string;

    /**
     * Authenticate a webhook: true when $rawPayload genuinely came from the
     * gateway (verified against $signature). Returns false — never throws — when
     * verification fails or no webhook secret is configured.
     *
     * @param  string  $rawPayload  The exact raw request body (not re-encoded).
     */
    public function verifyWebhook(string $rawPayload, string $signature): bool;

    /**
     * ISO-4217 currency codes this gateway accepts.
     *
     * @return array<int, string>
     */
    public function supportedCurrencies(): array;
}
