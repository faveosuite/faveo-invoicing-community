<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Common\SettingsController;
use App\Http\Controllers\Controller;
use App\Model\Order\Invoice;
use App\Model\Payment\Currency;
use App\Services\Payment\InvoicePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Invoice-driven SPA payment.
 *
 * The flow is fully invoice-id driven and stateless — the amount payable is
 * always recomputed from the invoice server-side, never trusted from the client
 * or the session.
 *
 *  - payInit       : everything the pay page needs to render (invoice, items,
 *                    amount due, active gateways, Stripe publishable key).
 *  - stripeSession : create an embedded Stripe Checkout Session for the invoice.
 *  - stripeConfirm : authoritatively confirm a completed Stripe session + fulfil.
 *  - razorpayOrder : create a Razorpay Order for the invoice (Checkout config).
 *
 * Razorpay verification + fulfilment is handled by RazorpayController::payment
 * (route POST /payment/{invoice}). All gateway processing lives in the
 * standalone package (App\Plugins\Payment); this controller talks to it only
 * through the application bridge (App\Services\Payment\InvoicePaymentService).
 */
class PaymentController extends Controller
{
    public function __construct(private readonly InvoicePaymentService $payments)
    {
    }

    public function payInit(Request $request, int $invoice)
    {
        $model = $this->authorizedInvoice($request, $invoice);

        $paid = (float) $model->payment()->sum('amount');
        $due = max(0, (float) $model->grand_total - $paid);

        return successResponse('', [
            'invoice' => [
                'id'          => $model->id,
                'number'      => $model->number,
                'grand_total' => (float) $model->grand_total,
                'currency'    => $model->currency,
                'status'      => $model->status,
            ],
            'items'      => $model->invoiceItem()->get()->map(function ($item) {
                $data = $item->toArray();
                $data['image'] = \App\Model\Product\Product::find($item->product_id)?->image;

                return $data;
            }),
            'paid'            => $paid,
            'amount'          => $due,
            'currency'        => $model->currency,
            'currency_symbol' => Currency::where('code', $model->currency)->value('symbol'),
            'gateways'        => $this->gateways($model->currency),
            'stripe_key'      => $this->stripePublishableKey(),
        ]);
    }

    /**
     * Create an embedded Stripe Checkout Session for an invoice.
     *
     * Returns the session client secret + id and the publishable key. The SPA
     * mounts Stripe's embedded checkout with the client secret and, in the
     * checkout's onComplete callback, calls stripeConfirm with the session id.
     */
    public function stripeSession(Request $request, int $invoice)
    {
        $model = $this->authorizedInvoice($request, $invoice);
        $amount = $this->amountDue($model, 'Stripe');

        try {
            $session = $this->payments->start($model, 'Stripe', $amount);

            return successResponse('', $session->clientConfig);
        } catch (\Throwable $e) {
            return errorResponse($e->getMessage());
        }
    }

    /**
     * Confirm a completed Stripe Checkout Session and fulfil the invoice.
     * The session is re-fetched from Stripe server-side; the client-supplied
     * session id is only a pointer, never the source of truth.
     */
    public function stripeConfirm(Request $request, int $invoice)
    {
        $model = $this->authorizedInvoice($request, $invoice);

        try {
            $paid = $this->payments->confirm($model, 'Stripe', ['session_id' => $request->input('session_id')]);

            return $paid
                ? successResponse('success', [])
                : errorResponse(__('message.payment_declined_try_other_gateway'));
        } catch (\Throwable $e) {
            return errorResponse($e->getMessage());
        }
    }

    /**
     * Create a Razorpay Order for an invoice and return the Checkout config the
     * SPA passes to `new Razorpay(options)`.
     */
    public function razorpayOrder(Request $request, int $invoice)
    {
        $model = $this->authorizedInvoice($request, $invoice);
        $amount = $this->amountDue($model, 'Razorpay');

        try {
            $session = $this->payments->start($model, 'Razorpay', $amount);

            return successResponse('', ['razorpay' => $session->clientConfig]);
        } catch (\Throwable $e) {
            return errorResponse($e->getMessage());
        }
    }

    /** Amount actually payable now: outstanding balance plus the gateway's processing fee. */
    private function amountDue(Invoice $model, string $gateway): float
    {
        $paid = (float) $model->payment()->sum('amount');
        $due  = max(0, (float) $model->grand_total - $paid);
        $fee  = (float) ($this->processingFee($gateway, $model->currency) ?? 0);

        return (float) rounding($due * (1 + $fee / 100));
    }

    private function authorizedInvoice(Request $request, int $invoiceId): Invoice
    {
        $invoice = Invoice::find($invoiceId);
        abort_if(! $invoice, 404, 'Invoice not found.');
        abort_if((int) $invoice->user_id !== (int) $request->user()->getAuthIdentifier(), 403, 'Forbidden');

        return $invoice;
    }

    /**
     * @return array<int, array{name: string, processing_fee: float|null}>
     */
    private function gateways(string $currency): array
    {
        try {
            $names = SettingsController::checkPaymentGateway($currency);
            if (! is_array($names)) {
                return [];
            }

            return array_map(fn ($name) => [
                'name'           => $name,
                'processing_fee' => $this->processingFee($name, $currency),
            ], $names);
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function processingFee(string $gateway, string $currency): ?float
    {
        try {
            $fee = DB::table(strtolower($gateway))
                ->where('currencies', $currency)
                ->value('processing_fee');

            return $fee !== null ? (float) $fee : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function stripePublishableKey(): string
    {
        return (string) (DB::table('api_keys')->where('id', 1)->value('stripe_key') ?? '');
    }
}
