<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Payment\Currency;
use Exception;
use Illuminate\Http\Request;

class ExtendedBaseInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['pdf']]);
    }

    public function newPayment(Request $request)
    {
        try {
            $clientid = $request->input('clientid');
            $this->user->where('id', $clientid)->firstOrFail();

            $invoices = Invoice::where('user_id', $clientid)
                ->where('status', '!=', 'Success')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($inv) {
                    $paid = Payment::where('invoice_id', $inv->id)
                        ->where('payment_status', 'success')
                        ->sum('amount');

                    return [
                        'id'          => $inv->id,
                        'number'      => $inv->number,
                        'date'        => $inv->date,
                        'grand_total' => $inv->grand_total,
                        'pending'     => max(0, $inv->grand_total - $paid),
                        'status'      => $inv->status,
                        'currency'    => $inv->currency,
                    ];
                })
                ->values();

            // Supported currencies = only the enabled ones (currency's own status = 1).
            // Note: the model's global scope filters by active country, not currency status.
            $currencies = Currency::where('status', 1)->orderBy('name')->get(['code', 'symbol', 'name'])->map(fn ($c) => [
                'code'   => $c->code,
                'symbol' => $c->symbol,
                'name'   => $c->name,
            ]);

            return successResponse('', [
                'invoices'   => $invoices,
                'currencies' => $currencies,
            ]);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function postNewPayment($clientid, Request $request)
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
            $payment = new Payment();
            $payment->payment_status = 'success';
            $payment->user_id = $clientid;
            $payment->invoice_id = '--';
            $paymentReceived = $payment->fill($request->all())->save();

            return redirect()->back()->with('success', \Lang::get('message.saved-successfully'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function edit($invoiceid, Request $request)
    {
        $totalSum = '0';
        $invoice = Invoice::where('id', $invoiceid)->first();
        $date = date('m/d/Y', strtotime($invoice->date));
        $payment = Payment::where('invoice_id', $invoiceid)->pluck('amount')->toArray();
        if ($payment) {
            $totalSum = array_sum($payment);
        }

        return view('themes.default1.invoice.editInvoice', compact('date', 'invoiceid', 'invoice', 'totalSum'));
    }

    public function postEdit($invoiceid, Request $request)
    {
        $this->validate($request, [
            'date' => 'required',
            'total' => 'required',
            'status' => 'required',
        ],
            [
                'date.required' => __('validation.custom_date.date_required'),
                'total.required' => __('validation.custom_date.total_required'),
                'status.required' => __('validation.custom_date.status_required'),
            ]);

        try {
            $total = $request->input('total');
            $status = $request->input('status');
            $paid = $request->input('paid');
            $invoice = Invoice::where('id', $invoiceid)->update(['grand_total' => $total, 'status' => $status,
                'date' => \Carbon\Carbon::parse($request->input('date')), ]);
            $order = Order::whereIn('id', \App\Model\Order\OrderInvoiceRelation::where('invoice_id', $invoiceid)->pluck('order_id'))->update(['price_override' => $total]);

            return successResponse(__('message.updated-successfully'));
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function postNewMultiplePayment($clientid, Request $request)
    {
        $this->validate($request, [
            'payment_date'   => 'required',
            'payment_method' => 'required',
            'totalAmt'       => 'required|numeric|not_in:0',
        ], [
            'payment_date.required'   => __('validation.payment.payment_date_required'),
            'payment_method.required' => __('validation.payment.payment_method_required'),
            'totalAmt.required'       => __('validation.amt_required'),
            'totalAmt.numeric'        => __('validation.amt_numeric'),
        ]);

        try {
            $this->multiplePayment(
                $clientid,
                $request->input('invoiceChecked', []),
                $request->input('payment_method'),
                \Carbon\Carbon::parse($request->input('payment_date')),
                $request->input('totalAmt'),
                $request->input('invoiceAmount', []),
                (float) $request->input('amtToCredit', 0),
                'success',
                $request->input('currency')
            );

            return successResponse(__('message.payment_updated_succcessfully'));
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage(), 500);
        }
    }

    public function multiplePayment($clientid, $invoiceChecked, $payment_method,
             $payment_date, $totalAmt, $invoicAmount, $amtToCredit, $payment_status, $currency = null)
    {
        try {
            // 1) Record a brand-new payment row against each selected invoice and
            //    refresh that invoice's status from its own running total.
            foreach ($invoiceChecked as $key => $value) {
                if (empty($value)) {
                    continue;
                }

                $amount  = (isset($invoicAmount[$key]) && $invoicAmount[$key] !== '') ? $invoicAmount[$key] : 0;
                $invoice = Invoice::find($value);

                Payment::create([
                    'invoice_id'     => $value,
                    'user_id'        => $clientid,
                    'amount'         => $amount,
                    'amt_to_credit'  => 0,
                    'payment_method' => $payment_method,
                    'payment_status' => $payment_status,
                    'created_at'     => $payment_date,
                    'currency'       => $invoice?->currency ?: $currency,
                ]);

                if ($invoice) {
                    $total_paid = Payment::where('invoice_id', $value)
                        ->where('payment_status', 'success')
                        ->sum('amount');

                    if ($total_paid >= $invoice->grand_total) {
                        $invoice->status = 'success';
                    } elseif ($total_paid > 0) {
                        $invoice->status = 'partially paid';
                    } else {
                        $invoice->status = 'pending';
                    }
                    $invoice->save();
                }
            }

            // 2) Bank any leftover as its own new credit-balance row (invoice_id = 0).
            //    Each deposit is a separate record; the client's running credit balance
            //    is the SUM of these rows (see AdvanceSearchController::getExtraAmt).
            if ($amtToCredit > 0) {
                Payment::create([
                    'invoice_id'     => 0,
                    'user_id'        => $clientid,
                    'amount'         => $amtToCredit,
                    'amt_to_credit'  => $amtToCredit,
                    'payment_method' => $payment_method,
                    'payment_status' => $payment_status,
                    'created_at'     => $payment_date,
                    'currency'       => $currency,
                ]);
            }
        } catch (Exception $ex) {
            \Logger::exception($ex);

            // Re-throw so the calling action returns a proper JSON error response.
            throw $ex;
        }
    }

    /**
     * Add an amount to the client's consolidated "Credit Balance" row.
     *
     * Unlike the admin "New Payment" flow (which records each deposit as its own
     * row), the online-checkout credit-spend logic expects the Credit Balance to
     * live in a SINGLE row it reads/decrements via ->value(). This helper keeps
     * that one row intact, so internal grants (e.g. product downgrades) stay
     * compatible with how that balance is later consumed.
     */
    public function mergeCreditBalance($userId, $amount, $payment_date, $payment_status = 'pending')
    {
        $existing = Payment::where('user_id', $userId)
            ->where('invoice_id', 0)
            ->where('payment_method', 'Credit Balance');

        $current  = (float) (clone $existing)->sum('amt_to_credit');
        $currency = (clone $existing)->orderBy('id', 'desc')->value('currency')
            ?: \App\User::where('id', $userId)->value('currency');
        $existing->delete();

        $total = $current + (float) $amount;

        return Payment::create([
            'invoice_id'     => 0,
            'user_id'        => $userId,
            'amount'         => $total,
            'amt_to_credit'  => $total,
            'payment_method' => 'Credit Balance',
            'payment_status' => $payment_status,
            'created_at'     => $payment_date,
            'currency'       => $currency,
        ]);
    }

    /*
     * Apply a client's accumulated credit balance to their pending invoices.
     */
    public function updateNewMultiplePayment($clientid, Request $request)
    {
        $this->validate($request, [
            'payment_date'    => 'required',
            'payment_method'  => 'required',
            'invoiceChecked'  => 'required|array|min:1',
        ], [
            'payment_date.required'   => __('validation.payment_date_required'),
            'payment_method.required' => __('validation.payment_method_required'),
            'invoiceChecked.required' => __('validation.invoice_link_required'),
            'invoiceChecked.array'    => __('validation.invoice_link_required'),
            'invoiceChecked.min'      => __('validation.invoice_link_required'),
        ]);

        try {
            $this->updatePaymentByInvoice(
                $clientid,
                $request->input('invoiceChecked', []),
                $request->input('payment_method'),
                \Carbon\Carbon::parse($request->input('payment_date')),
                $request->input('invoiceAmount', []),
                'success'
            );

            return successResponse(__('message.payment_updated_succcessfully'));
        } catch (\Exception $ex) {
            \Logger::exception($ex);

            return errorResponse($ex->getMessage(), 500);
        }
    }

    /**
     * Spend the client's credit balance against the selected invoices:
     *  - record one payment row per invoice for the amount drawn from credit,
     *  - refresh each invoice's status, and
     *  - reduce the credit balance by the total applied.
     *
     * The credit balance is the SUM of the client's invoice_id = 0 rows
     * (see AdvanceSearchController::getExtraAmt). Once spent, those rows are
     * consolidated into a single remaining-balance row so the sum stays exact.
     */
    public function updatePaymentByInvoice($clientid, $invoiceChecked, $payment_method,
             $payment_date, $invoicAmount, $payment_status)
    {
        try {
            // Snapshot the current credit balance, and the method/currency it is held under.
            $creditQuery    = Payment::where('user_id', $clientid)->where('invoice_id', 0);
            $creditBefore   = (float) $creditQuery->sum('amt_to_credit');
            $creditMethod   = (clone $creditQuery)->orderBy('id', 'desc')->value('payment_method') ?? $payment_method;
            $creditCurrency = (clone $creditQuery)->orderBy('id', 'desc')->value('currency');

            $applied = 0;

            foreach ($invoiceChecked as $key => $value) {
                if (empty($value)) {
                    continue;
                }

                $amount = (isset($invoicAmount[$key]) && $invoicAmount[$key] !== '') ? (float) $invoicAmount[$key] : 0;
                if ($amount <= 0) {
                    continue;
                }

                $invoice = Invoice::find($value);

                Payment::create([
                    'invoice_id'     => $value,
                    'user_id'        => $clientid,
                    'amount'         => $amount,
                    'amt_to_credit'  => 0,
                    'payment_method' => $payment_method,
                    'payment_status' => $payment_status,
                    'created_at'     => $payment_date,
                    'currency'       => $invoice?->currency ?: $creditCurrency,
                ]);
                $applied += $amount;

                if ($invoice) {
                    $total_paid = Payment::where('invoice_id', $value)
                        ->where('payment_status', 'success')
                        ->sum('amount');

                    if ($total_paid >= $invoice->grand_total) {
                        $invoice->status = 'success';
                    } elseif ($total_paid > 0) {
                        $invoice->status = 'partially paid';
                    } else {
                        $invoice->status = 'pending';
                    }
                    $invoice->save();
                }
            }

            if ($applied <= 0) {
                throw new Exception(__('message.amount_to_credit'));
            }

            if ($applied > $creditBefore) {
                throw new Exception(__('message.insufficient_credit_balance'));
            }

            // Reduce the credit balance by what was applied, consolidating into one row.
            $remaining = max(0, $creditBefore - $applied);
            $creditQuery->delete();
            if ($remaining > 0) {
                Payment::create([
                    'invoice_id'     => 0,
                    'user_id'        => $clientid,
                    'amount'         => $remaining,
                    'amt_to_credit'  => $remaining,
                    'payment_method' => $creditMethod,
                    'payment_status' => 'success',
                    'created_at'     => $payment_date,
                    'currency'       => $creditCurrency,
                ]);
            }
        } catch (Exception $ex) {
            \Logger::exception($ex);

            // Re-throw so the calling action returns a proper JSON error response.
            throw $ex;
        }
    }
}
