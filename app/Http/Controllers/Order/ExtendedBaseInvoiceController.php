<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Model\Order\Invoice;
use App\Model\Order\Payment;
use App\Model\Payment\Currency;
use App\Services\Payment\CreditBalanceService;
use App\Services\Payment\UnappliedPaymentService;
use App\User;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Logger;

class ExtendedBaseInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['pdf']]);
    }

    public function newPayment(Request $request): JsonResponse
    {
        try {
            $clientid = $request->input('clientid');
            $this->user->where('id', $clientid)->firstOrFail(); // @phpstan-ignore property.notFound

            // Raw numbers, never currencyFormat() — the form does arithmetic on
            // these (allocate, distribute, cap) and a locale-grouped "1,234.50"
            // parses as 1 in JavaScript, quietly allocating 1.00 to the invoice
            // and banking the rest as credit.
            $invoices = Invoice::where('user_id', $clientid)
                ->where('status', '!=', 'Success')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn ($inv): array => [
                    'id' => $inv->id,
                    'number' => $inv->number,
                    'date' => $inv->date,
                    'grand_total' => (float) $inv->grand_total,
                    'pending' => $inv->outstanding(),
                    'status' => $inv->status,
                    'currency' => $inv->currency,
                ])
                ->filter(fn ($inv): bool => $inv['pending'] > 0)
                ->values();

            // Supported currencies = only the enabled ones (currency's own status = 1).
            // Note: the model's global scope filters by active country, not currency status.
            $currencies = Currency::where('status', 1)->orderBy('name')->get(['code', 'symbol', 'name'])->map(fn ($c): array => [
                'code' => $c->code,
                'symbol' => $c->symbol,
                'name' => $c->name,
            ]);

            return successResponse('', [
                'invoices' => $invoices,
                'currencies' => $currencies,
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function postNewPayment(int $clientid, Request $request): JsonResponse
    {
        $this->validate($request, [
            'payment_date' => 'required',
            'payment_method' => 'required',
            'amount' => 'required',
        ],
            [
                'payment_date.required' => __('validation.payment.payment_date_required'),
                'payment_method.required' => __('validation.payment.payment_method_required'),
                'amount.required' => __('validation.payment.amount_required'),
            ]);

        try {
            $payment = new Payment;
            $payment->payment_status = 'success';
            $payment->user_id = $clientid;
            $payment->fill($request->all())->save();

            return successResponse(__('message.saved-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function postNewMultiplePayment(int $clientid, Request $request): JsonResponse
    {
        $this->validate($request, [
            'payment_date' => 'required',
            'payment_method' => 'required',
            'totalAmt' => 'required|numeric|not_in:0',
        ], [
            'payment_date.required' => __('validation.payment.payment_date_required'),
            'payment_method.required' => __('validation.payment.payment_method_required'),
            'totalAmt.required' => __('validation.amt_required'),
            'totalAmt.numeric' => __('validation.amt_numeric'),
        ]);

        try {
            // "Credit Balance" isn't money arriving — it's the client spending
            // credit they already hold, which is exactly what the Edit Payment
            // flow does. Route it there so it actually draws down the ledger
            // instead of conjuring payment rows out of nothing.
            if ($request->input('payment_method') === 'Credit Balance') {
                $this->updatePaymentByInvoice(
                    $clientid,
                    $request->input('invoiceChecked', []),
                    'Credit Balance',
                    Date::parse($request->input('payment_date')),
                    $request->input('invoiceAmount', []),
                    'success'
                );

                return successResponse(__('message.payment_updated_succcessfully'));
            }

            $this->multiplePayment(
                $clientid,
                $request->input('invoiceChecked', []),
                $request->input('payment_method'),
                Date::parse($request->input('payment_date')),
                $request->input('totalAmt'),
                $request->input('invoiceAmount', []),
                'success',
                $request->input('currency')
            );

            return successResponse(__('message.payment_updated_succcessfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage(), 500);
        }
    }

    /**
     * Record money actually received from the client: allocate it across the
     * selected invoices, and bank whatever is left over as credit.
     *
     * The split is derived here, not trusted from the form — the browser's
     * arithmetic is a preview, and every number in it (which invoices, how
     * much each, how much is left) is re-checked against the invoices
     * themselves. The whole receipt is one transaction: an allocation that
     * doesn't add up rolls back the rest rather than leaving money half-landed.
     *
     * @param  array<mixed>  $invoicAmount
     * @param  array<mixed>  $invoiceChecked
     */
    public function multiplePayment(int $clientid, array $invoiceChecked, string $payment_method,
        Carbon $payment_date, float|int $totalAmt, array $invoicAmount, string $payment_status, ?string $currency = null): void
    {
        try {
            DB::transaction(function () use ($clientid, $invoiceChecked, $payment_method, $payment_date, $totalAmt, $invoicAmount, $payment_status, $currency): void {
                $receiptCurrency = $currency ?: null;
                $allocations = [];
                $applied = 0.0;

                foreach ($invoiceChecked as $key => $value) {
                    if (empty($value)) {
                        continue;
                    }

                    $amount = (float) ($invoicAmount[$key] ?? 0);
                    if ($amount <= 0) {
                        continue;
                    }

                    $invoice = $this->clientInvoice($clientid, $value);

                    // One receipt is in one currency; money never crosses
                    // currencies, so neither may an allocation.
                    $receiptCurrency ??= $invoice->currency;
                    if ($invoice->currency !== $receiptCurrency) {
                        throw new Exception(__('message.payment_currency_mismatch'));
                    }

                    // Checked against what is owed right now, inside the loop,
                    // so the same invoice listed twice can't be overpaid.
                    if (round($amount, 2) > round($invoice->outstanding(), 2)) {
                        throw new Exception(__('message.amount_exceeds_invoice_due'));
                    }

                    $allocations[] = ['invoice' => $invoice, 'amount' => $amount];
                    $applied += $amount;
                }

                // Everything received is ONE payment. What it settles goes in
                // the pivot; whatever no invoice claimed simply stays unapplied
                // on it. Not credit — credit is something we grant, this is the
                // client's own money, and recording it as credit would erase
                // that real money arrived, by what method, on what date.
                if (round((float) $totalAmt - $applied, 2) < 0) {
                    throw new Exception(__('message.amount_exceeds_invoice_due'));
                }

                $payment = Payment::create([
                    'invoice_id' => 0,
                    'parent_id' => 0,
                    'user_id' => $clientid,
                    'amount' => $totalAmt,
                    'amt_to_credit' => 0,
                    'payment_method' => $payment_method,
                    'payment_status' => $payment_status,
                    'created_at' => $payment_date,
                    'currency' => $receiptCurrency ?: getCurrencyForClient(User::find($clientid)?->country),
                ]);

                foreach ($allocations as $allocation) {
                    $payment->invoices()->attach($allocation['invoice']->id, ['amount' => $allocation['amount']]);
                    $allocation['invoice']->refreshStatus();
                }
            });
        } catch (Exception $exception) {
            Logger::exception($exception);

            // Re-throw so the calling action returns a proper JSON error response.
            throw $exception;
        }
    }

    /**
     * Apply ONE unapplied payment to the chosen invoices — the action behind
     * the "apply payment" screen reached from a payment row.
     *
     * Scoped to that payment on purpose: the admin is looking at a specific
     * receipt ("the client sent us 30 by check"), so that is the money being
     * allocated, not whatever else the client happens to have lying around.
     */
    public function applyPaymentToInvoices(int $paymentId, Request $request): JsonResponse
    {
        $this->validate($request, [
            'payment_date' => 'required',
            'invoiceChecked' => 'required|array|min:1',
        ], [
            'payment_date.required' => __('validation.payment_date_required'),
            'invoiceChecked.required' => __('validation.invoice_link_required'),
            'invoiceChecked.array' => __('validation.invoice_link_required'),
            'invoiceChecked.min' => __('validation.invoice_link_required'),
        ]);

        try {
            /** @var Payment $payment */
            $payment = Payment::findOrFail($paymentId);
            $clientid = (int) $payment->user_id;
            $invoiceAmounts = $request->input('invoiceAmount', []);
            $date = Date::parse($request->input('payment_date'));

            DB::transaction(function () use ($payment, $clientid, $request, $invoiceAmounts, $date): void {
                $applied = 0.0;

                foreach ($request->input('invoiceChecked', []) as $key => $value) {
                    if (empty($value)) {
                        continue;
                    }

                    $amount = (float) ($invoiceAmounts[$key] ?? 0);
                    if ($amount <= 0) {
                        continue;
                    }

                    $invoice = $this->clientInvoice($clientid, $value);

                    if ($invoice->currency !== $payment->currency) {
                        throw new Exception(__('message.payment_currency_mismatch'));
                    }

                    if (round($amount, 2) > round($invoice->outstanding(), 2)) {
                        throw new Exception(__('message.amount_exceeds_invoice_due'));
                    }

                    app(UnappliedPaymentService::class)->apply(
                        $clientid,
                        (string) $payment->currency,
                        $amount,
                        (int) $invoice->id,
                        (string) $payment->payment_method,
                        $date,
                        paymentId: (int) $payment->id,
                    );

                    $invoice->refreshStatus();
                    $applied += $amount;
                }

                if ($applied <= 0) {
                    throw new Exception(__('message.amount_to_credit'));
                }
            });

            return successResponse(__('message.payment_updated_succcessfully'));
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse($exception->getMessage(), 500);
        }
    }

    /**
     * An invoice, but only if it really is this client's — `Invoice::find()`
     * alone would happily let one client's payment land on another's invoice.
     */
    private function clientInvoice(int $clientid, mixed $invoiceId): Invoice
    {
        /** @var Invoice|null $invoice */
        $invoice = Invoice::where('id', $invoiceId)->where('user_id', $clientid)->first();

        if (! $invoice) {
            throw new Exception(__('message.no_records_found'));
        }

        return $invoice;
    }

    /**
     * Spend credit the client already holds against their open invoices — the
     * admin-side counterpart to the client checkout's "use my credit balance".
     */
    public function updateNewMultiplePayment(int $clientid, Request $request): JsonResponse
    {
        $this->validate($request, [
            'payment_date' => 'required',
            'payment_method' => 'required',
            'invoiceChecked' => 'required|array|min:1',
        ], [
            'payment_date.required' => __('validation.payment_date_required'),
            'payment_method.required' => __('validation.payment_method_required'),
            'invoiceChecked.required' => __('validation.invoice_link_required'),
            'invoiceChecked.array' => __('validation.invoice_link_required'),
            'invoiceChecked.min' => __('validation.invoice_link_required'),
        ]);

        try {
            $this->updatePaymentByInvoice(
                $clientid,
                $request->input('invoiceChecked', []),
                $request->input('payment_method'),
                Date::parse($request->input('payment_date')),
                $request->input('invoiceAmount', []),
                'success'
            );

            return successResponse(__('message.payment_updated_succcessfully'));
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse($exception->getMessage(), 500);
        }
    }

    /**
     * Spend the client's credit balance against the selected invoices: for
     * each one, draw the amount from that invoice's own currency balance
     * (see CreditBalanceService — credit never crosses currencies), record a
     * payment row for it, and refresh the invoice's status.
     *
     * @param  array<mixed>  $invoicAmount
     * @param  array<mixed>  $invoiceChecked
     */
    public function updatePaymentByInvoice(int $clientid, array $invoiceChecked, string $payment_method,
        Carbon $payment_date, array $invoicAmount, string $payment_status): void
    {
        try {
            DB::transaction(function () use ($clientid, $invoiceChecked, $payment_method, $payment_date, $invoicAmount, $payment_status): void {
                $applied = 0.0;

                foreach ($invoiceChecked as $key => $value) {
                    if (empty($value)) {
                        continue;
                    }

                    $amount = (float) ($invoicAmount[$key] ?? 0);
                    if ($amount <= 0) {
                        continue;
                    }

                    $invoice = $this->clientInvoice($clientid, $value);

                    if (round($amount, 2) > round($invoice->outstanding(), 2)) {
                        throw new Exception(__('message.amount_exceeds_invoice_due'));
                    }

                    // Throws insufficient_credit_balance if this invoice's currency can't cover it.
                    app(CreditBalanceService::class)->apply($clientid, $invoice->currency, $amount, (int) $invoice->id);

                    // Spending credit is still money settling an invoice, so it
                    // gets a payment of its own with a single allocation.
                    Payment::create([
                        'invoice_id' => 0,
                        'parent_id' => 0,
                        'user_id' => $clientid,
                        'amount' => $amount,
                        'amt_to_credit' => 0,
                        'payment_method' => $payment_method,
                        'payment_status' => $payment_status,
                        'created_at' => $payment_date,
                        'currency' => $invoice->currency,
                    ])->invoices()->attach($invoice->id, ['amount' => $amount]);

                    $invoice->refreshStatus();
                    $applied += $amount;
                }

                if ($applied <= 0) {
                    throw new Exception(__('message.amount_to_credit'));
                }
            });
        } catch (Exception $exception) {
            Logger::exception($exception);

            // Re-throw so the calling action returns a proper JSON error response.
            throw $exception;
        }
    }
}
