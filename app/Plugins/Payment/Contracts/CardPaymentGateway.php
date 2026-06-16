<?php

namespace App\Plugins\Payment\Contracts;

use App\Plugins\Payment\Dto\PaymentRequest;
use App\Plugins\Payment\Dto\PaymentSession;
use App\Plugins\Payment\Exceptions\PaymentException;

/**
 * Contract for gateways that support a custom, in-page card UI.
 *
 * Separate from {@see PaymentGateway} (whose createPayment opens the gateway's
 * own hosted/redirect surface — e.g. Stripe Checkout, the Razorpay popup),
 * because not every gateway exposes a primitive the host can wrap in its own
 * card fields. A driver that does (Stripe, via a PaymentIntent) implements this
 * so the application can collect card details in its own UI while card data
 * still goes straight to the gateway — never the host server.
 *
 * The returned {@see PaymentSession} carries the client secret the browser
 * confirms against; verification of the result still goes through
 * {@see PaymentGateway::capturePayment()}.
 */
interface CardPaymentGateway
{
    /**
     * Open a payment to be completed by the gateway's client-side SDK against a
     * custom card UI, returning the client secret + key the browser needs.
     *
     * @throws PaymentException
     */
    public function createCardPayment(PaymentRequest $request): PaymentSession;
}
