<?php

namespace App\Services\Payment;

use App\ApiKey;
use App\Model\Order\Invoice;
use App\Plugins\Payment\Dto\Customer;
use App\Plugins\Payment\Dto\PaymentRequest;
use App\Plugins\Payment\Dto\PaymentSession;
use App\Plugins\Payment\Gateways\RazorpayGateway;
use App\Plugins\Payment\Gateways\StripeGateway;
use App\Plugins\Payment\PaymentGatewayManager;
use App\Traits\Payment\PostPaymentHandle;

/**
 * Application bridge between the standalone payment package and this app.
 *
 * This is the ONLY place the dependency-pure package
 * ({@see \App\Plugins\Payment}) meets the application: it sources gateway
 * credentials from the {@see ApiKey} model, turns an {@see Invoice} (+ the
 * authenticated user) into a package {@see PaymentRequest}, and on a verified
 * payment runs the application's order-fulfilment ({@see PostPaymentHandle}).
 *
 * Controllers should depend on this service, never on the gateway drivers
 * directly — that keeps the package portable and the app wiring in one spot.
 */
class InvoicePaymentService
{
    use PostPaymentHandle;

    /** Build a manager wired with this application's configured gateways. */
    public function manager(): PaymentGatewayManager
    {
        $keys = ApiKey::find(1);

        return (new PaymentGatewayManager)
            ->register('Stripe', fn () => new StripeGateway(
                (string) ($keys->stripe_secret ?? ''),
                (string) ($keys->stripe_key ?? ''),
                (string) config('open_payment.stripe_webhook_secret', ''),
            ))
            ->register('Razorpay', fn () => new RazorpayGateway(
                (string) ($keys->rzp_key ?? ''),
                (string) ($keys->rzp_secret ?? ''),
                'Faveo Helpdesk',
                (string) config('open_payment.razorpay_webhook_secret', ''),
            ));
    }

    /** Stripe publishable key, for the SPA to initialise Stripe.js. */
    public function publishableKey(): string
    {
        return (string) (ApiKey::where('id', 1)->value('stripe_key') ?? '');
    }

    /**
     * Open a payment for an invoice and return the gateway's client config.
     *
     * @param  float  $amount  Fee-inclusive amount due, in major currency units.
     */
    public function start(Invoice $invoice, string $gateway, float $amount): PaymentSession
    {
        return $this->manager()->gateway($gateway)->createPayment(
            new PaymentRequest(
                amount: $amount,
                currency: $invoice->currency,
                reference: (string) $invoice->number,
                customer: $this->customer(),
                description: 'Payment for Invoice No - '.$invoice->number,
                metadata: [
                    'invoice_id' => (int) $invoice->id,
                    'user_id' => (int) $invoice->user_id,
                ],
            )
        );
    }

    /**
     * Verify a gateway callback for an invoice and, when paid, fulfil the order.
     *
     * Idempotent: a fully-paid invoice short-circuits to true so a repeated
     * callback / refresh cannot double-fulfil. Package exceptions
     * (PaymentException / SignatureVerificationException) propagate to the caller.
     *
     * @param  array<string, mixed>  $payload  Raw gateway callback fields.
     */
    public function confirm(Invoice $invoice, string $gateway, array $payload): bool
    {
        $paid = (float) $invoice->payment()->sum('amount');
        if (max(0, (float) $invoice->grand_total - $paid) <= 0) {
            return true;
        }

        $result = $this->manager()->gateway($gateway)->capturePayment($payload);
        if (! $result->paid) {
            return false;
        }

        // PostPaymentHandle records + fulfils against the session-stored method.
        \Session::put('payment_method', $gateway);
        $outcome = $this->processPaymentSuccess($invoice, strtolower($invoice->currency));
        if (! is_array($outcome)) {
            // processPaymentSuccess swallows its own errors into a JsonResponse.
            throw new \RuntimeException(__('message.payment_declined_try_other_gateway'));
        }

        \Session::forget(['items', 'code', 'codevalue', 'totalToBePaid', 'invoice', 'cart_currency']);

        return true;
    }

    /** Map the authenticated user onto the package's Customer value object. */
    private function customer(): Customer
    {
        $user = auth()->user();

        return new Customer(
            name: trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: null,
            email: $user->email ?? null,
            phone: trim(($user->mobile_code ?? '').($user->mobile ?? '')) ?: null,
            line1: $user->address ?? null,
            city: $user->town ?? null,
            state: $user->state ?? null,
            postalCode: $user->zip ?? null,
            country: $user->country ?? null,
        );
    }
}
