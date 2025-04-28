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
            $invoice = new Invoice();
            $order = new Order();
            $invoices = $invoice->where('user_id', $clientid)->where('status', '!=', 'Success')->orderBy('created_at', 'desc')->get();
            $cltCont = new \App\Http\Controllers\User\ClientController();
            $invoiceSum = $cltCont->getTotalInvoice($invoices);
            $amountReceived = $cltCont->getAmountPaid($clientid);
            $pendingAmount = $invoiceSum - $amountReceived;
            $client = $this->user->where('id', $clientid)->first();
            $currency = $client->currency;
            $symbol = Currency::where('code', $currency)->pluck('symbol')->first();
            $orders = $order->where('client', $clientid)->get();

            return view('themes.default1.invoice.newpayment', compact('clientid', 'client', 'invoices', 'orders',
                'invoiceSum', 'amountReceived', 'pendingAmount', 'currency', 'symbol'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
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
            $order = Order::where('invoice_id', $invoiceid)->update(['price_override' => $total]);

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function postNewMultiplePayment($clientid, Request $request)
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
            $payment_date = \Carbon\Carbon::parse($request->payment_date);
            $payment_method = $request->payment_method;
            $totalAmt = $request->totalAmt;
            $invoiceChecked = $request->invoiceChecked;
            $invoicAmount = $request->invoiceAmount;
            $amtToCredit = $request->amtToCredit;
            $payment_status = 'success';
            $payment = $this->multiplePayment($clientid, $invoiceChecked, $payment_method,
                $payment_date, $totalAmt, $invoicAmount, $amtToCredit, $payment_status);
            $response = ['type' => 'success', 'message' =>  __('message.payment_updated_succcessfully')];

            return response()->json($response);
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            $result = [$ex->getMessage()];

            return response()->json(compact('result'), 500);

            // return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function multiplePayment($clientid, $invoiceChecked, $payment_method,
             $payment_date, $totalAmt, $invoicAmount, $amtToCredit, $payment_status)
    {
        try {
            foreach ($invoiceChecked as $key => $value) {
                if ($key != 0) {//If Payment is linked to Invoice
                    $invoice = Invoice::find($value);
                    $invoice_status = 'pending';
                    $invoicAmount[$key] = $invoicAmount[$key] == '' ? 0 : $invoicAmount[$key];
                    $payment = Payment::where('invoice_id', $value)->create([
                        'invoice_id' => $value,
                        'user_id' => $clientid,
                        'amount' => $invoicAmount[$key],
                        'amt_to_credit' => $amtToCredit,
                        'payment_method' => $payment_method,
                        'payment_status' => $payment_status,
                        'created_at' => $payment_date,
                    ]);
                    $totalPayments = $this->payment
            ->where('invoice_id', $value)
            ->where('payment_status', 'success')
            ->pluck('amount')->toArray();
                    $total_paid = array_sum($totalPayments);

                    if ($invoice) {
                        if ($total_paid >= $invoice->grand_total) {
                            $invoice_status = 'success';
                        } elseif ($total_paid > 0) {
                            $invoice_status = 'partially paid';
                        }
                        $invoice->status = $invoice_status;
                        $invoice->save();
                    }
                } elseif (count($invoiceChecked) == 1 || $amtToCredit > 0) {//If Payment is not linked to any invoice and is to be credited to User Accunt
                    $totalExtraSum = Payment::where('user_id', $clientid)->where('invoice_id', 0)
                    ->pluck('amt_to_credit')->first(); //Get the total Extra Amt Paid
                    if ($totalExtraSum) {
                        $amtToCredit = $totalExtraSum + $amtToCredit; //Add the total extra amt to the existing extra amt paid before deleting
                        Payment::where('user_id', $clientid)->delete();
                    }
                    $payment = Payment::updateOrCreate([
                        'invoice_id' => $value,
                        'user_id' => $clientid,
                        'amt_to_credit' => $amtToCredit,
                        'payment_method' => $payment_method,
                        'payment_status' => $payment_status,
                        'created_at' => $payment_date,
                    ]);
                }
            }
            // return $payment;
        } catch (Exception $ex) {
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /*
    * Editing  and Updating the Payment By linking with Invoice
    */
    public function updateNewMultiplePayment($clientid, Request $request)
    {
        $this->validate($request, [
            'payment_date' => 'required',
            'payment_method' => 'required',
            'totalAmt' => 'required|numeric',
            'invoiceChecked' => 'required',
        ],
            [
                'payment_date.required' => __('validation.payment_date_required'),
                'payment_method.required' => __('validation.payment_method_required'),
                'totalAmt.required' => __('validation.total_amount_required'),
                'totalAmt.numeric' => __('validation.total_amount_numeric'),
                'invoiceChecked.required' => __('validation.invoice_link_required'),
            ]);

        try {
            $payment_date = \Carbon\Carbon::parse($request->payment_date);
            $payment_method = $request->payment_method;
            $totalAmt = $request->totalAmt;
            $invoiceChecked = $request->invoiceChecked;
            $invoicAmount = $request->invoiceAmount;
            $amtToCredit = $request->amtToCredit;
            $payment_status = 'success';
            $payment = $this->updatePaymentByInvoice($clientid, $invoiceChecked, $payment_method,
                $payment_date, $totalAmt, $invoicAmount, $amtToCredit, $payment_status);
            $response = ['type' => 'success', 'message' => __('message.payment_updated_succcessfully')];

            return response()->json($response);
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());
            $result = [$ex->getMessage()];

            return response()->json(compact('result'), 500);
        }
    }

    public function updatePaymentByInvoice($clientid, $invoiceChecked, $payment_method,
             $payment_date, $totalAmt, $invoicAmount, $amtToCredit, $payment_status)
    {
        try {
            $sum = 0;
            foreach ($invoiceChecked as $key => $value) {
                if ($key != 0) {//If Payment is linked to Invoice
                    $invoice = Invoice::find($value);
                    Payment::where('user_id', $clientid)->where('invoice_id', 0)
                     ->update(['amt_to_credit' => $amtToCredit]);
                    $invoice_status = 'pending';
                    $payment = Payment::create([
                        'invoice_id' => $value,
                        'user_id' => $clientid,
                        'amount' => $invoicAmount[$key],
                        'payment_method' => $payment_method,
                        'payment_status' => $payment_status,
                        'created_at' => $payment_date,
                    ]);
                    $totalPayments = $this->payment
            ->where('invoice_id', $value)
            ->where('payment_status', 'success')
            ->pluck('amount')->toArray();
                    $total_paid = array_sum($totalPayments);
                    if ($total_paid >= $invoice->grand_total) {
                        $invoice_status = 'success';
                    }
                    if ($invoice) {
                        $invoice->status = $invoice_status;
                        $invoice->save();
                    }
                }
                // else{//If Payment is not linked to any invoice and is to be credited to User Accunt
        //     dd('as');
        //     $payment = Payment::create([
        //         'invoice_id'     => $value,
        //         'user_id'       => $clientid,
        //         'amount'         =>$invoicAmount[$key],
        //         'amt_to_credit'  =>$amtToCredit,
        //         'payment_method' => $payment_method,
        //         'payment_status' => $payment_status,
        //         'created_at'     => $payment_date,
        //     ]);
                // }
            }

            // return $payment;
        } catch (Exception $ex) {
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }
}
