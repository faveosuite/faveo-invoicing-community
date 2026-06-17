<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Controllers\License\LicensePermissionsController;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceTaxLine;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Payment\Currency;
use App\Model\Payment\TaxOption;
use App\Model\Product\Product;
use App\Services\Payment\InvoicePaymentService;
use App\Services\Payment\OpenPaymentService;
use App\Services\Payment\ProcessingFee;
use Illuminate\Http\Request;
use Throwable;

/**
 * Invoice-driven SPA payment (HTTP layer).
 *
 * Thin controller: authorises the invoice, delegates to {@see InvoicePaymentService}
 * for all payment logic, and shapes the JSON response. The flow is fully
 * invoice-id driven and stateless — the amount payable is recomputed from the
 * invoice server-side, never trusted from the client or session.
 *
 *  - payInit         : everything the pay page needs to render.
 *  - stripeSession   : create an embedded Stripe Checkout Session for the invoice.
 *  - stripeConfirm   : authoritatively confirm a completed Stripe session + fulfil.
 *  - razorpayOrder   : create a Razorpay Order for the invoice (Checkout config).
 *  - stripeWebhook   : gateway webhook — handles all Stripe payment types.
 *  - razorpayWebhook : gateway webhook — handles all Razorpay payment types.
 *
 * Razorpay verification + fulfilment is handled by RazorpayController::payment,
 * which delegates to the same InvoicePaymentService.
 */
class PaymentController extends Controller
{
    public function __construct(
        private readonly InvoicePaymentService $invoices,
        private readonly OpenPaymentService $webhooks,
    ) {
    }

    public function payInit(Request $request, int $invoice)
    {
        $model = $this->authorizedInvoice($request, $invoice);

        $items = $model->invoiceItem()->get();
        $outstanding = $this->invoices->outstanding($model);

        // Each gateway carries its processing fee; surface the fee amount and the
        // resulting payable total so the pay page shows exactly what's charged.
        $gateways = array_map(fn (array $gateway): array => $gateway + [
            'fee_amount' => ProcessingFee::amount($outstanding, $gateway['name']),
            'payable' => ProcessingFee::addTo($outstanding, $gateway['name']),
        ], $this->invoices->gatewaysFor($model->currency));

        return successResponse('', [
            'invoice' => [
                'id' => $model->id,
                'number' => $model->number,
                'grand_total' => (float) $model->grand_total,
                'currency' => $model->currency,
                'status' => $model->status,
            ],
            'items' => $items->map(function ($item) {
                $data = $item->toArray();
                $data['image'] = Product::find($item->product_id)?->image;

                return $data;
            }),
            'summary' => $this->invoiceSummary($model, $items),
            'paid' => (float) $model->payment()->sum('amount'),
            'amount' => $outstanding,
            'currency' => $model->currency,
            'currency_symbol' => Currency::where('code', $model->currency)->value('symbol'),
            'gateways' => $gateways,
            'stripe_key' => $this->invoices->publishableKey(),
        ]);
    }

    /**
     * Subtotal + per-tax breakdown for an invoice, from the persisted
     * invoice_tax_lines (every invoice — cart, admin, renewal, and historical
     * via backfill — has these). Grouped per tax label.
     *
     * @return array{subtotal: float, taxes: array<int, array{label:string, amount:float}>, tax_total: float, grand_total: float}
     */
    private function invoiceSummary(Invoice $model, $items): array
    {
        $subtotal = round((float) $items->sum(fn ($i): float => (float) $i->subtotal), 2);

        $taxes = InvoiceTaxLine::where('invoice_id', $model->id)->get()
            ->groupBy('label')
            ->map(fn ($group): array => [
                'label' => $group->first()->label,
                'rate' => (float) $group->first()->rate,
                'amount' => round((float) $group->sum('amount'), 2),
            ])->values()->all();

        $taxTotal = round((float) collect($taxes)->sum('amount'), 2);
        $pricesIncludeTax = (int) TaxOption::find(1)?->inclusive === 1;

        return [
            'subtotal' => $subtotal,
            'subtotal_ex_tax' => $pricesIncludeTax ? round($subtotal - $taxTotal, 2) : $subtotal,
            'prices_include_tax' => $pricesIncludeTax,
            'tax_label' => collect($taxes)->pluck('label')->unique()->implode(' + '),
            'taxes' => $taxes,
            'tax_total' => $taxTotal,
            'discount' => round((float) $model->discount, 2),
            'coupon_code' => $model->coupon_code,
            'grand_total' => (float) $model->grand_total,
        ];
    }

    /**
     * Post-payment success summary for an invoice (invoice + orders + payment),
     * for the SPA success page. Stateless + refreshable; derived from the paid
     * invoice, not the session.
     */
    public function paySuccess(Request $request, int $invoice)
    {
        $model = $this->authorizedInvoice($request, $invoice);

        $payment = $model->payment()->latest()->first();
        $orderIds = OrderInvoiceRelation::where('invoice_id', $model->id)->pluck('order_id');
        $permissions = resolve(LicensePermissionsController::class);
        $cloudProducts = cloudPopupProducts();

        $orders = Order::whereIn('id', $orderIds)->get()->map(function ($order) use ($permissions, $cloudProducts): array {
            $product = Product::select('id', 'name', 'type')->find($order->product);

            $downloadable = false;
            try {
                $downloadable = $product
                    && ! in_array($product->id, $cloudProducts)
                    && ($permissions->getPermissionsForProduct($order->product)['downloadPermission'] ?? 0) == 1;
            } catch (Throwable) {
                $downloadable = false;
            }

            return [
                'number' => $order->number,
                'product_id' => $product?->id,
                'product_name' => $product?->name,
                'qty' => $order->qty,
                'price' => (float) $order->price_override,
                'downloadable' => $downloadable,
                'download_url' => $downloadable ? url('product/download/'.$order->product) : null,
            ];
        });

        return successResponse('', [
            'invoice' => [
                'number' => $model->number,
                'grand_total' => (float) $model->grand_total,
                'currency' => $model->currency,
                'currency_symbol' => Currency::where('code', $model->currency)->value('symbol'),
                'status' => $model->status,
                'date' => $model->created_at,
            ],
            'payment_method' => $payment?->payment_method,
            'orders' => $orders,
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

        try {
            return successResponse('', $this->invoices->start($model, 'Stripe')->clientConfig);
        } catch (Throwable $throwable) {
            return errorResponse($throwable->getMessage());
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
            $paid = $this->invoices->confirm($model, 'Stripe', ['payment_intent' => $request->input('payment_intent')]);

            return $paid
                ? successResponse('success', [])
                : errorResponse(__('message.payment_declined_try_other_gateway'));
        } catch (Throwable $throwable) {
            return errorResponse($throwable->getMessage());
        }
    }

    /**
     * Create a Razorpay Order for an invoice and return the Checkout config the
     * SPA passes to `new Razorpay(options)`.
     */
    public function razorpayOrder(Request $request, int $invoice)
    {
        $model = $this->authorizedInvoice($request, $invoice);

        try {
            return successResponse('', ['razorpay' => $this->invoices->start($model, 'Razorpay')->clientConfig]);
        } catch (Throwable $throwable) {
            return errorResponse($throwable->getMessage());
        }
    }

    /**
     * Stripe webhook — entry point for all Stripe payment events.
     * Handles purchases, renewals, agent alterations, upgrade/downgrades,
     * open payments, and subscription auto-renewals.
     */
    public function stripeWebhook(Request $request)
    {
        $processed = $this->webhooks->handleWebhook(
            'Stripe',
            $request->getContent(),
            (string) $request->header('Stripe-Signature'),
        );

        return $processed
            ? successResponse('Webhook processed')
            : errorResponse('Invalid signature', 400);
    }

    /**
     * Razorpay webhook — entry point for all Razorpay payment events.
     */
    public function razorpayWebhook(Request $request)
    {
        $processed = $this->webhooks->handleWebhook(
            'Razorpay',
            $request->getContent(),
            (string) $request->header('X-Razorpay-Signature'),
        );

        return $processed
            ? successResponse('Webhook processed')
            : errorResponse('Invalid signature', 400);
    }

    private function authorizedInvoice(Request $request, int $invoiceId): Invoice
    {
        $invoice = Invoice::find($invoiceId);
        abort_if(! $invoice, 404, 'Invoice not found.');
        abort_if((int) $invoice->user_id !== (int) $request->user()->getAuthIdentifier(), 403, 'Forbidden');

        return $invoice;
    }
}
