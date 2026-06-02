<?php

namespace App\Services\Payment;

use App\Http\Controllers\Common\SettingsController;
use App\Model\Order\Invoice;
use App\Plugins\Payment\Dto\Customer;
use App\Plugins\Payment\Dto\PaymentRequest;
use App\Plugins\Payment\Dto\PaymentSession;
use App\Traits\Payment\PostPaymentHandle;

/**
 * Invoice-payment domain logic.
 *
 * Sits between the controllers and the generalized {@see PaymentService}. It
 * owns everything that makes a payment an *invoice* payment: turning an
 * {@see Invoice} (+ the authenticated user) into a package {@see PaymentRequest},
 * computing the fee-inclusive amount due server-side, listing the gateways active
 * for a currency, and — on a verified payment — running order fulfilment via
 * {@see PostPaymentHandle}.
 *
 * The gateway mechanics themselves are delegated to {@see PaymentService}, which
 * stays domain-agnostic and reusable by other payment surfaces. Controllers
 * (Front\PaymentController, RazorpayController) depend on THIS service so the
 * invoice flow has one home and no controller calls another.
 *
 * The flow is invoice-id driven and stateless — the amount payable is always
 * recomputed from the invoice, never trusted from the client or session.
 */
class InvoicePaymentService
{
    use PostPaymentHandle;

    public function __construct(private readonly PaymentService $payments)
    {
    }

    /** Stripe publishable key, for the SPA to initialise Stripe.js. */
    public function publishableKey(): string
    {
        return $this->payments->publishableKey();
    }

    /** Outstanding balance on an invoice (grand total less payments recorded). */
    public function outstanding(Invoice $invoice): float
    {
        $paid = (float) $invoice->payment()->sum('amount');

        return max(0, (float) $invoice->grand_total - $paid);
    }

    /**
     * Gateways active for a currency, each with its processing fee.
     *
     * @return array<int, array{name: string, processing_fee: float|null}>
     */
    public function gatewaysFor(string $currency): array
    {
        try {
            $names = SettingsController::checkPaymentGateway($currency);
            if (! is_array($names)) {
                return [];
            }

            return array_map(fn ($name) => [
                'name' => $name,
                'processing_fee' => ProcessingFee::percent($name) ?: null,
            ], $names);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Open a payment for an invoice and return the gateway's client config.
     *
     * @throws \App\Plugins\Payment\Exceptions\PaymentException
     */
    public function start(Invoice $invoice, string $gateway): PaymentSession
    {
        $request = $this->invoiceRequest($invoice, $gateway);

        // Stripe uses a PaymentIntent so the SPA can collect card details in its
        // own UI; Razorpay opens its Checkout via the standard createPayment.
        return strtolower($gateway) === 'stripe'
            ? $this->payments->startCardPayment($gateway, $request)
            : $this->payments->start($gateway, $request);
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
        if ($this->outstanding($invoice) <= 0) {
            return true;
        }

        if (! $this->payments->capture($gateway, $payload)->paid) {
            return false;
        }

        // Persist the processing fee that was actually charged, so the invoice
        // + payment records match the card charge (parity with the legacy flow).
        $this->applyProcessingFee($invoice, $gateway);

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

    /** Build the package payment request for an invoice on a given gateway. */
    private function invoiceRequest(Invoice $invoice, string $gateway): PaymentRequest
    {
        return new PaymentRequest(
            amount: $this->amountDue($invoice, $gateway),
            currency: $invoice->currency,
            reference: (string) $invoice->number,
            customer: $this->customer(),
            description: 'Payment for Invoice No - '.$invoice->number,
            metadata: [
                'invoice_id' => (int) $invoice->id,
                'user_id' => (int) $invoice->user_id,
            ],
        );
    }

    /** Amount actually payable now: outstanding balance plus the gateway's processing fee. */
    private function amountDue(Invoice $invoice, string $gateway): float
    {
        return ProcessingFee::addTo($this->outstanding($invoice), $gateway);
    }

    /**
     * Record the gateway's processing fee on the invoice so its grand_total
     * (and the recorded payment) match what the card was charged. grand_total
     * is stored fee-inclusive, matching the legacy convention. Idempotent and a
     * no-op for fee-less gateways (e.g. a gateway configured with a 0% fee).
     */
    private function applyProcessingFee(Invoice $invoice, string $gateway): void
    {
        if ($invoice->processing_fee) {
            return; // already applied
        }

        $fee = ProcessingFee::percent($gateway);
        if ($fee <= 0) {
            return;
        }

        $invoice->processing_fee = ProcessingFee::label($fee);
        $invoice->grand_total = ProcessingFee::addTo((float) $invoice->grand_total, $gateway);
        $invoice->save();
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
