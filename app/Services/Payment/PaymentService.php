<?php

namespace App\Services\Payment;

use App\ApiKey;
use App\Plugins\Payment\Contracts\CardPaymentGateway;
use App\Plugins\Payment\Dto\PaymentRequest;
use App\Plugins\Payment\Dto\PaymentResult;
use App\Plugins\Payment\Dto\PaymentSession;
use App\Plugins\Payment\Exceptions\PaymentException;
use App\Plugins\Payment\Exceptions\SignatureVerificationException;
use App\Plugins\Payment\Gateways\RazorpayGateway;
use App\Plugins\Payment\Gateways\StripeGateway;
use App\Plugins\Payment\PaymentGatewayManager;

/**
 * Generalized payment processing for the application.
 *
 * The single, domain-agnostic bridge between the dependency-pure payment package
 * ({@see \App\Plugins\Payment}) and the application. It knows only how to wire the
 * configured gateways from the {@see ApiKey} model and drive them with plain
 * package value objects — it knows nothing about invoices, open-payment orders,
 * the authenticated user, or order fulfilment.
 *
 * Each caller owns its own domain: it builds a {@see PaymentRequest} for its
 * entity, calls {@see start()} / {@see capture()} / {@see verifyWebhook()}, and
 * decides what a verified payment means for it (e.g. invoice fulfilment lives in
 * {@see \App\Http\Controllers\Front\PaymentController}). This keeps the service
 * reusable across every payment surface — invoices, open payments, and beyond.
 */
class PaymentService
{
    /** Build a manager wired with this application's configured gateways. */
    public function manager(): PaymentGatewayManager
    {
        $keys = ApiKey::find(1);

        return (new PaymentGatewayManager)
            ->register('Stripe', fn () => new StripeGateway(
                (string) ($keys->stripe_secret ?? ''),
                (string) ($keys->stripe_key ?? ''),
                (string) ($keys->stripe_webhook_secret ?? ''),
            ))
            ->register('Razorpay', fn () => new RazorpayGateway(
                (string) ($keys->rzp_key ?? ''),
                (string) ($keys->rzp_secret ?? ''),
                'Faveo Helpdesk',
                (string) ($keys->razorpay_webhook_secret ?? ''),
            ));
    }

    /** Stripe publishable key, for the client to initialise Stripe.js. */
    public function publishableKey(): string
    {
        return (string) (ApiKey::where('id', 1)->value('stripe_key') ?? '');
    }

    /**
     * Open a payment on a gateway and return everything the client SDK needs.
     *
     * @throws PaymentException
     */
    public function start(string $gateway, PaymentRequest $request): PaymentSession
    {
        return $this->manager()->gateway($gateway)->createPayment($request);
    }

    /**
     * Open a payment for a custom in-page card UI (returns a client secret the
     * browser confirms against). Only gateways implementing
     * {@see CardPaymentGateway} support this.
     *
     * @throws PaymentException
     */
    public function startCardPayment(string $gateway, PaymentRequest $request): PaymentSession
    {
        $driver = $this->manager()->gateway($gateway);

        if (! $driver instanceof CardPaymentGateway) {
            throw new PaymentException("Payment gateway [{$gateway}] does not support a custom card UI.");
        }

        return $driver->createCardPayment($request);
    }

    /**
     * Verify a client callback against a gateway and report the outcome.
     *
     * @param  array<string, mixed>  $payload  Raw gateway callback fields.
     *
     * @throws SignatureVerificationException
     * @throws PaymentException
     */
    public function capture(string $gateway, array $payload): PaymentResult
    {
        return $this->manager()->gateway($gateway)->capturePayment($payload);
    }

    /**
     * Authenticate a gateway webhook against its signature. Never throws —
     * returns false when verification fails or no webhook secret is configured.
     */
    public function verifyWebhook(string $gateway, string $rawPayload, string $signature): bool
    {
        return $this->manager()->gateway($gateway)->verifyWebhook($rawPayload, $signature);
    }
}
