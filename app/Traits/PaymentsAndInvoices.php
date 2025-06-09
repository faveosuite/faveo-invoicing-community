<?php

namespace App\Traits;

use App\Model\Order\Invoice;
use App\Model\Order\Payment;
use App\Model\Product\Product;
use Illuminate\Http\Request;

//////////////////////////////////////////////////////////////////////////////
// PAYMENTS AND EXTRA FUNCTIONALITIES FOR INVOICES
//////////////////////////////////////////////////////////////////////////////

trait PaymentsAndInvoices
{
    /*
    *Edit payment Total.
    */
    public function paymentTotalChange(Request $request)
    {
        try {
            $invoice = new Invoice();
            $total = $request->input('total');
            if ($total == '') {
                $total = 0;
            }
            $paymentid = $request->input('id');
            $creditAmtUserId = $this->payment->where('id', $paymentid)->value('user_id');
            $creditAmt = $this->payment->where('user_id', $creditAmtUserId)
              ->where('invoice_id', '=', 0)->value('amt_to_credit');
            $invoices = $invoice->where('user_id', $creditAmtUserId)->orderBy('created_at', 'desc')->get();
            $cltCont = new \App\Http\Controllers\User\ClientController();
            $invoiceSum = $cltCont->getTotalInvoice($invoices);
            if ($total > $invoiceSum) {
                $diff = $total - $invoiceSum;
                $creditAmt = $creditAmt + $diff;
                $total = $invoiceSum;
            }
            $payment = $this->payment->where('id', $paymentid)->update(['amount' => $total]);

            $creditAmtInvoiceId = $this->payment->where('user_id', $creditAmtUserId)
        ->where('invoice_id', '!=', 0)->first();
            $invoiceId = $creditAmtInvoiceId->invoice_id;
            $invoice = $invoice->where('id', $invoiceId)->first();
            $grand_total = $invoice->grand_total;
            $diffSum = $grand_total - $total;

            $finalAmt = $creditAmt + $diffSum;
            $updatedAmt = $this->payment->where('user_id', $creditAmtUserId)
        ->where('invoice_id', '=', 0)->update(['amt_to_credit' => $creditAmt]);
        } catch (\Exception $ex) {
            app('log')->info($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function doPayment(
        $payment_method,
        $invoiceid,
        $amount,
       $parent_id = '',
        $userid = '',
        $payment_status = 'pending'
    ) {
        try {
            if ($amount > 0) {
                if ($userid == '') {
                    $userid = \Auth::user()->id;
                }
                if ($amount == 0) {
                    $payment_status = 'success';
                }
                $this->payment->create([
                    'parent_id' => $parent_id,
                    'invoice_id' => $invoiceid,
                    'user_id' => $userid,
                    'amount' => $amount,
                    'payment_method' => $payment_method,
                    'payment_status' => $payment_status,
                ]);
                $this->updateInvoice($invoiceid);
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function getAgents($agents, $productid, $plan)
    {
        if (! $agents) {//If agents is not received in the request in the case when
            // 'modify agent' is not allowed for the Product,get the no of Agents from the Plan Table.
            $planForAgent = Product::find($productid)->planRelation->find($plan);
            if ($planForAgent) {//If Plan Exists For the Product ie not a Product without Plan
                $noOfAgents = $planForAgent->planPrice->first()->no_of_agents;
                $agents = $noOfAgents ? $noOfAgents : 0; //If no. of Agents is specified then that,else 0(Unlimited Agents)
            } else {
                $agents = 0;
            }
        }

        return $agents;
    }

    public function getQuantity($qty, $productid, $plan)
    {
        if (! $qty) {//If quantity is not received in the request in the case when 'modify quantity' is not allowed for the Product,get the Product qUANTITY from the Plan Table.
            $planForQty = Product::find($productid)->planRelation->find($plan);
            if ($planForQty) {
                $quantity = Product::find($productid)->planRelation->find($plan)->planPrice->first()->product_quantity;
                $qty = $quantity ? $quantity : 1; //If no. of Agents is specified then that,else 0(Unlimited Agents)
            } else {
                $qty = 1;
            }
        }

        return $qty;
    }

    public function updateInvoice($invoiceid)
    {
        try {
            $invoice = $this->invoice->findOrFail($invoiceid);
            foreach (\Cart::getConditionsByType('fee') as $value) {
                $invoice->processing_fee = $value->getValue();
            }
            $payment = $this->payment->where('invoice_id', $invoiceid)
            ->where('payment_status', 'success')->pluck('amount')->toArray();
            $total = array_sum($payment);
            if ($total < $invoice->grand_total) {
                $invoice->status = 'pending';
            }
            if ($total >= $invoice->grand_total) {
                $invoice->status = 'success';
            }
            if ($total > $invoice->grand_total) {
                $user = $invoice->user()->first();
                $balance = $total - $invoice->grand_total;
                $user->debit = $balance;
                $user->save();
            }

            $invoice->save();
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function postRazorpayPayment($invoice)
    {
        try {
            $payment_method = \Session::get('payment_method');
            $totalPayment = ($payment_method == 'Credits')
                ? $invoice->billing_pay
                : (empty($invoice->billing_pay) ?
                    $invoice->grand_total :
                    $invoice->grand_total - $invoice->billing_pay);

            if (count($invoice->payment()->get())) {//If partial payment is made
                $paid = array_sum($invoice->payment()->pluck('amount')->toArray());
                $totalPayment = $invoice->grand_total - $paid;
            }
            $payment_status = 'success';
            $payment_date = \Carbon\Carbon::now()->toDateTimeString();
            $paymentRenewal = $this->updateInvoicePayment(
                $invoice->id,
                $payment_method,
                $payment_status,
                $payment_date,
                intval($totalPayment)
            );

            return redirect()->back()->with('success', 'Payment Accepted Successfully');
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function sendmailClientAgent($userid, $invoiceid)
    {
        try {
            $agent = \Input::get('agent');
            $client = \Input::get('client');
            if ($agent == 1) {
                $id = \Auth::user()->id;
                $this->sendMail($id, $invoiceid);
            }
            if ($client == 1) {
                $this->sendMail($userid, $invoiceid);
            }
        } catch (\Exception $ex) {
            app('log')->info($ex->getMessage());
            throw new \Exception($ex->getMessage());
        }
    }

    public function payment(Request $request)
    {
        try {
            if ($request->has('invoiceid')) {
                $invoice_id = $request->input('invoiceid');
                $invoice = $this->invoice->find($invoice_id);
                $userid = $invoice->user_id;
                //dd($invoice);
                $invoice_status = '';
                $payment_status = '';
                $payment_method = '';
                $domain = '';
                if ($invoice) {
                    $invoice_status = $invoice->status;
                    $items = $invoice->invoiceItem()->first();
                    if ($items) {
                        $domain = $items->domain;
                    }
                }
                $payment = $this->payment->where('invoice_id', $invoice_id)->first();
                if ($payment) {
                    $payment_status = $payment->payment_status;
                    $payment_method = $payment->payment_method;
                }

                return view(
                    'themes.default1.invoice.payment',
                    compact(
                        'invoice_status',
                        'payment_status',
                        'payment_method',
                        'invoice_id',
                        'domain',
                        'invoice',
                        'userid'
                    )
                );
            }

            return redirect()->back();
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getExtraAmtPaid($userId)
    {
        try {
            $amounts = Payment::where('user_id', $userId)->where('invoice_id', 0)->select('amt_to_credit')->get();
            $balance = 0;
            foreach ($amounts as $amount) {
                if ($amount) {
                    $balance = $balance + $amount->amt_to_credit;
                }
            }

            return $balance;
        } catch (\Exception $ex) {
            app('log')->info($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Get total of the Invoices for a User.
     */
    public function getTotalInvoice($invoices)
    {
        $sum = 0;
        foreach ($invoices as $invoice) {
            $sum = $sum + $invoice->grand_total;
        }

        return $sum;
    }

    public function getAmountPaid($userId)
    {
        try {
            $amounts = Payment::where('user_id', $userId)->select('amount', 'amt_to_credit')->get();
            $paidSum = 0;
            foreach ($amounts as $amount) {
                if ($amount) {
                    $paidSum = $paidSum + (int) $amount->amount;
                    // $credit = $paidSum + $amount->amt_to_credit;
                }
            }

            return $paidSum;
        } catch (\Exception $ex) {
            app('log')->info($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }
}
