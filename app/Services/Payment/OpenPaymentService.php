<?php

namespace App\Services\Payment;

use App\Http\Controllers\Common\PhpMailController;
use App\Model\Common\Setting;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\Payment\OpenPaymentOrder;
use App\Plugins\Payment\Dto\Customer;
use App\Plugins\Payment\Dto\PaymentRequest;
use App\Plugins\Payment\Dto\PaymentResult;
use App\Plugins\Payment\Dto\PaymentSession;
use App\Plugins\Payment\Exceptions\PaymentException;
use App\Plugins\Payment\Support\Money;
use Exception;
use Logger;

/**
 * Open-payment domain logic.
 *
 * Open payments are standalone, ad-hoc charges (an {@see OpenPaymentOrder}: a
 * name/email/amount with no invoice and no order fulfilment) — so "fulfilment"
 * here is simply marking the order paid. Like {@see InvoicePaymentService}, this
 * sits over the generalized {@see PaymentService}: it turns an order into a
 * package {@see PaymentRequest}, opens / verifies the payment through the same
 * gateways the rest of the app uses, and updates the order's status.
 *
 * The order id is threaded through the gateway metadata so an asynchronous
 * webhook can later locate and settle the same order.
 */
class OpenPaymentService
{
    private const DISPLAY_DATETIME = 'd M Y H:i';

    public function __construct(private readonly PaymentService $payments)
    {
    }

    /** Stripe publishable key, for the client to initialise Stripe.js. */
    public function publishableKey(): string
    {
        return $this->payments->publishableKey();
    }

    /**
     * Open a payment for an order and return the gateway's client config.
     * Records the gateway's session/order id so a later callback can be matched.
     *
     * @throws PaymentException
     */
    public function start(OpenPaymentOrder $order): PaymentSession
    {
        $session = $this->payments->start($order->gateway, $this->orderRequest($order));

        $order->update(['gateway_transaction_id' => $session->id]);

        return $session;
    }

    /**
     * Open a Stripe PaymentIntent for the custom card UI (same approach as the
     * invoice panel's PlaceOrderPage). Returns clientConfig with client_secret
     * and payment_intent_id for the browser to call confirmCardPayment.
     *
     * @throws PaymentException
     */
    public function startCardPayment(OpenPaymentOrder $order): PaymentSession
    {
        $session = $this->payments->startCardPayment($order->gateway, $this->orderRequest($order));

        $order->update(['gateway_transaction_id' => $session->id]);

        return $session;
    }

    /**
     * Verify a client callback for an order and mark it paid on success.
     *
     * Idempotent: an already-paid order short-circuits to true. Package
     * exceptions (PaymentException / SignatureVerificationException) propagate.
     *
     * @param  array<string, mixed>  $payload  Raw gateway callback fields.
     */
    public function confirm(OpenPaymentOrder $order, array $payload): bool
    {
        if ($order->isPaid()) {
            return true;
        }

        // Browser callback: never let request data switch the gateway into webhook mode
        // (which skips the signature check — see InvoicePaymentService::confirm).
        $payload['is_webhook'] = false;
        $result = $this->payments->capture($order->gateway, $payload);

        // A genuine gateway payment is not necessarily one for THIS order: without this,
        // paying a cheap open order and posting its payment id against a big one marks the
        // big one paid (and an unpaid id would mark any order failed). orderRequest() stamps
        // order_id as Stripe `metadata` / Razorpay `notes`, and the order's own amount.
        if (! $this->paymentIsForOrder($result, $order)) {
            Logger::exception(new Exception(sprintf(
                'Open payment %s (%s) does not carry order_id=%d — refusing to settle it.',
                $result->reference, $order->gateway, $order->id
            )));

            return false;
        }

        if (! $result->paid) {
            $order->update(['payment_status' => 'failed']);
            $this->sendFailureEmail($order);

            return false;
        }

        if (! $this->capturedCovers($result, $order)) {
            Logger::exception(new Exception(sprintf(
                'Open payment %s for order %d captured less than %s %s — refusing to settle; reconcile manually.',
                $result->reference, $order->id, $order->amount, $order->currency
            )));

            return false;
        }

        $this->markPaid($order, $result->reference);

        return true;
    }

    private function paymentIsForOrder(PaymentResult $result, OpenPaymentOrder $order): bool
    {
        $stamped = $result->raw['metadata'] ?? $result->raw['notes'] ?? null;

        return is_array($stamped) && (int) ($stamped['order_id'] ?? 0) === (int) $order->id;
    }

    /** Captured ≥ the order's amount, same currency. Gateways report minor units (see InvoicePaymentService). */
    private function capturedCovers(PaymentResult $result, OpenPaymentOrder $order): bool
    {
        $raw = $result->raw;
        $captured = $raw['amount_received'] ?? $raw['amount_paid'] ?? $raw['amount_total'] ?? null;

        return $captured !== null
            && strtoupper((string) ($raw['currency'] ?? '')) === strtoupper((string) $order->currency)
            && (int) $captured >= Money::toMinor((float) $order->amount, (string) $order->currency);
    }

    /**
     * Authenticate and process a gateway webhook for open payments.
     *
     * Returns true when the webhook was genuine and processed; false when the
     * signature could not be verified. Signature verification is delegated to the
     * package; the verified payload is then parsed for the order it settles.
     */
    public function handleWebhook(string $gateway, string $rawPayload, string $signature): bool
    {
        if (! $this->payments->verifyWebhook($gateway, $rawPayload, $signature)) {
            return false;
        }

        $event = json_decode($rawPayload, associative: true) ?: [];

        strtolower($gateway) === 'stripe'
            ? $this->handleStripeEvent($event)
            : $this->handleRazorpayEvent($event);

        return true;
    }

    /**
     * @param  array<mixed>  $event
     */
    private function handleStripeEvent(array $event): void
    {
        WebhookDispatcher::stripe()->dispatch($event['type'] ?? '', $event);
    }

    /**
     * @param  array<mixed>  $event
     */
    private function handleRazorpayEvent(array $event): void
    {
        WebhookDispatcher::razorpay()->dispatch($event['event'] ?? '', $event);
    }

    /** Mark an order paid, recording the gateway's transaction reference, and send emails. */
    public function markPaid(OpenPaymentOrder $order, ?string $gatewayReference): void
    {
        $order->update([
            'payment_status' => 'completed',
            'gateway_transaction_id' => $gatewayReference ?: $order->gateway_transaction_id,
            'paid_at' => now(),
        ]);

        /** @var OpenPaymentOrder $freshOrder */
        $freshOrder = $order->fresh() ?? $order;
        $this->sendSuccessEmails($freshOrder);
    }

    /** Send payment failure notification to client. */
    public function notifyFailure(OpenPaymentOrder $order): void
    {
        $this->sendFailureEmail($order);
    }

    /** Send success email to client + admin notification. */
    private function sendSuccessEmails(OpenPaymentOrder $order): void
    {
        if (! emailSendingStatus()) {
            return;
        }

        $setting = Setting::find(1);
        $fromEmail = $setting->email ?? config('mail.from.address');
        $adminEmail = $setting->company_email ?? $fromEmail;
        $date = $order->paid_at?->format(self::DISPLAY_DATETIME) ?? now()->format(self::DISPLAY_DATETIME);
        $contact = getContactData();

        // Client success email
        $clientTemplate = TemplateType::getSelectedTemplate('open_payment_success');
        if ($clientTemplate instanceof Template) {
            $replace = [
                'name' => $order->name,
                'transaction_id' => $order->transaction_id,
                'amount' => currencyFormat($order->amount, $order->currency),
                'gateway' => $order->gateway,
                'date' => $date,
                'logo' => $contact['logo'],
                'contact' => $contact['contact'],
            ];
            (new PhpMailController)->sendEmail(
                $fromEmail, $order->email,
                $clientTemplate->data, $clientTemplate->name,
                'open_payment_success', $replace, 'open_payment_success',
                [], '', $order->name
            );
        }

        // Admin notification email
        $adminTemplate = TemplateType::getSelectedTemplate('open_payment_admin_success');
        if ($adminTemplate instanceof Template) {
            $replace = [
                'name' => $order->name,
                'company' => $order->company,
                'email' => $order->email,
                'base_amount' => currencyFormat($order->base_amount, $order->currency),
                'processing_fee' => currencyFormat($order->processing_fee, $order->currency),
                'fee_rate' => $order->processing_fee_rate,
                'amount' => currencyFormat($order->amount, $order->currency),
                'gateway' => $order->gateway,
                'transaction_id' => $order->transaction_id,
                'date' => $date,
                'logo' => $contact['logo'],
                'contact' => $contact['contact'],
            ];
            (new PhpMailController)->sendEmail(
                $fromEmail, $adminEmail,
                $adminTemplate->data, $adminTemplate->name,
                'open_payment_admin_success', $replace, 'open_payment_admin_success',
                [], '', 'Admin'
            );
        }
    }

    /** Send payment failure emails to client and admin. */
    private function sendFailureEmail(OpenPaymentOrder $order): void
    {
        if (! emailSendingStatus()) {
            return;
        }

        $setting = Setting::find(1);
        $fromEmail = $setting->email ?? config('mail.from.address');
        $adminEmail = $setting->company_email ?? $fromEmail;
        $date = now()->format(self::DISPLAY_DATETIME);
        $contact = getContactData();

        // Client failure email
        $clientTemplate = TemplateType::getSelectedTemplate('open_payment_failed');
        if ($clientTemplate instanceof Template) {
            $replace = [
                'name' => $order->name,
                'amount' => currencyFormat($order->base_amount, $order->currency),
                'gateway' => $order->gateway,
                'logo' => $contact['logo'],
                'contact' => $contact['contact'],
            ];
            (new PhpMailController)->sendEmail(
                $fromEmail, $order->email,
                $clientTemplate->data, $clientTemplate->name,
                'open_payment_failed', $replace, 'open_payment_failed',
                [], '', $order->name
            );
        }

        // Admin failure notification
        $adminTemplate = TemplateType::getSelectedTemplate('open_payment_admin_failed');
        if ($adminTemplate instanceof Template) {
            $replace = [
                'name' => $order->name,
                'company' => $order->company,
                'email' => $order->email,
                'amount' => currencyFormat($order->base_amount, $order->currency),
                'gateway' => $order->gateway,
                'date' => $date,
                'logo' => $contact['logo'],
                'contact' => $contact['contact'],
            ];
            (new PhpMailController)->sendEmail(
                $fromEmail, $adminEmail,
                $adminTemplate->data, $adminTemplate->name,
                'open_payment_admin_failed', $replace, 'open_payment_admin_failed',
                [], '', 'Admin'
            );
        }
    }

    /** Build the package payment request for an open-payment order. */
    private function orderRequest(OpenPaymentOrder $order): PaymentRequest
    {
        return new PaymentRequest(
            amount: (float) $order->amount,
            currency: $order->currency,
            reference: (string) $order->transaction_id,
            customer: new Customer(
                name: $order->name ?: null,
                email: $order->email ?: null,
                phone: $order->mobile ?: null,
                line1: $order->address ?: null,
                city: $order->city ?: null,
                state: $order->state ?: null,
                postalCode: $order->zip ?: null,
                country: $order->country ?: null,
            ),
            description: $order->description ?: 'Open Payment',
            metadata: ['order_id' => (int) $order->id],
        );
    }
}
