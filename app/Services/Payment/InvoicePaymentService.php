<?php

namespace App\Services\Payment;

use App\Http\Controllers\Common\SettingsController;
use App\Http\Controllers\Order\InvoiceController;
use App\Http\Controllers\User\AdvanceSearchController;
use App\Model\Common\StatusSetting;
use App\Model\Order\Invoice;
use App\Plugins\Payment\Dto\Customer;
use App\Plugins\Payment\Dto\PaymentRequest;
use App\Plugins\Payment\Dto\PaymentResult;
use App\Plugins\Payment\Dto\PaymentSession;
use App\Plugins\Payment\Exceptions\PaymentException;
use App\User;
use Exception;
use Illuminate\Support\Facades\Date;
use Logger;
use Throwable;

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
    public function __construct(
        private readonly PaymentService $payments,
        private readonly PostPaymentService $postPayment,
        private readonly AutoRenewalActivationService $autoRenewal,
    ) {
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

    /** Client's spendable credit balance — SUM of their invoice_id = 0 rows. */
    public function availableCredit(int $userId): float
    {
        return (float) app(AdvanceSearchController::class)->getExtraAmt($userId);
    }

    /** How much credit will be auto-applied to this invoice: the full balance, capped at what's owed. */
    public function creditApplied(Invoice $invoice): float
    {
        return min($this->outstanding($invoice), $this->availableCredit((int) $invoice->user_id));
    }

    /**
     * Pay an invoice entirely out of the client's own credit balance — the full
     * available amount is used automatically, up to what's owed; there's no
     * partial/manual amount to choose (mirrors a coupon: no per-payment
     * negotiation). Reuses the same ledger operation the admin "Edit Payment"
     * tool uses (ExtendedBaseInvoiceController::updatePaymentByInvoice).
     *
     * If the credit fully covers the invoice, fulfilment runs immediately (same
     * pipeline a real gateway payment triggers) since no gateway step follows.
     *
     * @return array{paid_in_full: bool}
     */
    public function applyCredit(Invoice $invoice): array
    {
        $toApply = $this->creditApplied($invoice);

        if ($toApply <= 0) {
            throw new Exception(__('message.insufficient_credit_balance'));
        }

        app(InvoiceController::class)->updatePaymentByInvoice(
            (int) $invoice->user_id,
            [$invoice->id],
            'Credit Balance',
            Date::now(),
            [$toApply],
            'success'
        );

        $invoice->refresh();
        $paidInFull = $this->outstanding($invoice) <= 0;

        if ($paidInFull) {
            $this->postPayment->handle($invoice, 'Credit Balance');
        }

        return ['paid_in_full' => $paidInFull];
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

            return array_map(fn ($name): array => [
                'name' => $name,
                'processing_fee' => ProcessingFee::percent($name) ?: null,
            ], $names);
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Open a payment for an invoice and return the gateway's client config.
     *
     * $useCredit is a single yes/no choice ("use my credit balance or not") —
     * there's no partial amount to pick. When on, the full available credit is
     * applied, up to what's owed (mirrors a coupon: it just discounts, no
     * negotiation). It's embedded in the gateway session as metadata and only
     * ever really spent once {@see confirm} sees the gateway payment succeed;
     * nothing is written to the credit ledger here, so the same balance stays
     * free to use on another invoice right up until this one is actually paid.
     *
     * @throws PaymentException
     */
    public function start(Invoice $invoice, string $gateway, bool $useCredit = false): PaymentSession
    {
        $request = $this->invoiceRequest($invoice, $gateway, $useCredit ? $this->creditApplied($invoice) : 0);

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
     * If the session was opened with a credit preview (see {@see start}), that
     * amount is spent for real now — atomically with the gateway payment being
     * recorded — never before. Re-clamped against the CURRENT balance (not just
     * trusted from the session) in case it was spent elsewhere on another
     * invoice in the meantime; the gateway already collected the rest either way.
     *
     * @param  array<string, mixed>  $payload  Raw gateway callback fields.
     */
    public function confirm(Invoice $invoice, string $gateway, array $payload): bool
    {
        if ($this->outstanding($invoice) <= 0) {
            return true;
        }

        $result = $this->payments->capture($gateway, $payload);
        if (! $result->paid) {
            return false;
        }

        // A verified/captured payment only proves the reference is real — not
        // that it was ever meant for THIS invoice. Without this, a completed
        // payment for a cheap invoice could be replayed against a different,
        // more expensive invoice the same user owns (same gateway signature
        // check passes either way, since it's a genuine payment, just for
        // something else). invoiceRequest() always stamps invoice_id into the
        // gateway metadata/notes at creation time, so it must match here.
        if (! $this->referenceBelongsToInvoice($result, $invoice)) {
            Logger::exception(new Exception(sprintf(
                'Payment reference %s (%s) does not carry invoice_id=%d in its metadata — refusing to fulfil.',
                $result->reference, $gateway, $invoice->id
            )));

            return false;
        }

        $creditApplied = (float) ($payload['credit_applied'] ?? $result->raw['metadata']['credit_applied'] ?? 0);
        $creditApplied = min($creditApplied, $this->availableCredit((int) $invoice->user_id));

        if ($creditApplied > 0) {
            app(InvoiceController::class)->updatePaymentByInvoice(
                (int) $invoice->user_id,
                [$invoice->id],
                'Credit Balance',
                Date::now(),
                [$creditApplied],
                'success'
            );
            $invoice->refresh();
        }

        // Persist the processing fee that was actually charged, so the invoice
        // + payment records match the card charge (parity with the legacy flow).
        $this->applyProcessingFee($invoice, $gateway);

        $this->postPayment->handle($invoice, $gateway);

        if ($invoice->metadata['auto_renew_opt_in'] ?? false) {
            $this->activateAutoRenewalOptIn($invoice, $gateway, $result);
        }

        return true;
    }

    /**
     * Turn a checkout-time auto-renewal opt-in into an active one, using the
     * card just saved on this purchase's PaymentIntent (setup_future_usage was
     * requested in {@see invoiceRequest} when the invoice carries the flag).
     * Razorpay can't save a card from a one-time payment the way Stripe can —
     * its side of this is handled separately, right after order fulfilment
     * (see {@see PostPaymentService::handlePurchase}).
     *
     * Never lets a failure here fail the purchase itself — auto-renewal is a
     * bonus, not a requirement of a successful purchase.
     */
    private function activateAutoRenewalOptIn(Invoice $invoice, string $gateway, PaymentResult $result): void
    {
        // Re-checked here, not just trusted from the flag captured at checkout
        // time — an admin could disable auto-renewal (globally or for this
        // gateway) in the time between checkout and the payment completing.
        if (strtolower($gateway) !== 'stripe' || ! StatusSetting::autoRenewalEnabledFor('stripe')) {
            return;
        }

        try {
            $user = User::find($invoice->user_id);
            $order = $invoice->orders()->whereHas('subscription')->first();

            if (! $user || ! $order || ! $result->reference) {
                return;
            }

            $this->autoRenewal->activate($order, $user, 'stripe', $result->reference);
        } catch (Throwable $throwable) {
            Logger::exception($throwable);
        }
    }

    /**
     * Stripe carries it under `metadata`, Razorpay under `notes` — both were
     * stamped with `invoice_id` at creation time by {@see invoiceRequest}.
     * Fails closed (false) if it's missing entirely, not just on a mismatch —
     * an unverifiable reference is not a safe one to fulfil an invoice from.
     */
    private function referenceBelongsToInvoice(PaymentResult $result, Invoice $invoice): bool
    {
        $metadata = $result->raw['metadata'] ?? $result->raw['notes'] ?? null;
        if (! is_array($metadata) || ! isset($metadata['invoice_id'])) {
            return false;
        }

        return (int) $metadata['invoice_id'] === (int) $invoice->id;
    }

    /** Build the package payment request for an invoice on a given gateway. */
    private function invoiceRequest(Invoice $invoice, string $gateway, float $creditApplied = 0): PaymentRequest
    {
        return new PaymentRequest(
            amount: $this->amountDue($invoice, $gateway, $creditApplied),
            currency: $invoice->currency,
            reference: (string) $invoice->number,
            customer: $this->customer(),
            description: 'Payment for Invoice No - '.$invoice->number,
            metadata: [
                'invoice_id' => (int) $invoice->id,
                'user_id' => (int) $invoice->user_id,
                'credit_applied' => $creditApplied,
            ],
            saveForFutureUse: (bool) ($invoice->metadata['auto_renew_opt_in'] ?? false),
        );
    }

    /** Amount actually payable now: outstanding balance (less any previewed credit) plus the gateway's processing fee. */
    private function amountDue(Invoice $invoice, string $gateway, float $creditApplied = 0): float
    {
        return ProcessingFee::addTo(max(0, $this->outstanding($invoice) - $creditApplied), $gateway);
    }

    /**
     * Record the gateway's processing fee on the invoice so its grand_total
     * (and the recorded payment) match what the card was charged. grand_total
     * is stored fee-inclusive, matching the legacy convention. Idempotent and a
     * no-op for fee-less gateways (e.g. a gateway configured with a 0% fee).
     *
     * Fee applies only to the portion actually run through the gateway — any
     * credit payment already recorded against the invoice (see {@see confirm})
     * is excluded, since credit isn't card-processed and carries no fee.
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

        $alreadyPaid = (float) $invoice->payment()->where('payment_status', 'success')->sum('amount');
        $invoice->processing_fee = ProcessingFee::label($fee);
        $invoice->grand_total = (string) ($alreadyPaid + ProcessingFee::addTo(max(0, (float) $invoice->grand_total - $alreadyPaid), $gateway));
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
