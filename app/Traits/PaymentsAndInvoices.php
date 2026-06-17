<?php

namespace App\Traits;

use App\Http\Controllers\User\ClientController;
use App\Model\Order\Invoice;
use App\Model\Order\Payment;
use App\Model\Product\Product;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Input;
use Logger;

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
            $cltCont = new ClientController();
            $invoiceSum = $cltCont->getTotalInvoice($invoices);
            if ($total > $invoiceSum) {
                $diff = $total - $invoiceSum;
                $creditAmt += $diff;
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
        } catch (Exception $exception) {
            Logger::exception($exception);

            return back()->with('fails', $exception->getMessage());
        }
    }

    public function doPayment(
        $payment_method,
        $invoiceid,
        $amount,
       $parent_id = '',
        $userid = '',
        $payment_status = 'pending'
    ): void {
        try {
            if ($amount > 0) {
                if ($userid == '') {
                    $userid = Auth::user()->id;
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
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function getAgents($agents, $productid, $plan)
    {
        if (! $agents) {//If agents is not received in the request in the case when
            // 'modify agent' is not allowed for the Product,get the no of Agents from the Plan Table.
            $planForAgent = Product::find($productid)->planRelation->find($plan);
            if ($planForAgent) {//If Plan Exists For the Product ie not a Product without Plan
                $noOfAgents = $planForAgent->planPrice->first()->no_of_agents;
                $agents = $noOfAgents ?: 0; //If no. of Agents is specified then that,else 0(Unlimited Agents)
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
                $qty = $quantity ?: 1; //If no. of Agents is specified then that,else 0(Unlimited Agents)
            } else {
                $qty = 1;
            }
        }

        return $qty;
    }

    public function updateInvoice($invoiceid): void
    {
        try {
            $invoice = $this->invoice->findOrFail($invoiceid);

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
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function recordPayment(Invoice $invoice, string $gateway): void
    {
        $alreadyPaid = (float) $invoice->payment()->where('payment_status', 'success')->sum('amount');
        $outstanding = max(0, (float) $invoice->grand_total - $alreadyPaid);

        if ($outstanding > 0) {
            Payment::create([
                'invoice_id' => $invoice->id,
                'user_id' => $invoice->user_id,
                'amount' => rounding($outstanding),
                'payment_method' => $gateway,
                'payment_status' => 'success',
                'created_at' => Date::now(),
            ]);
        }

        $invoice->update(['status' => 'success']);
    }

    public function sendmailClientAgent($userid, $invoiceid): void
    {
        try {
            $agent = Input::get('agent');
            $client = Input::get('client');
            if ($agent == 1) {
                $id = Auth::user()->id;
                $this->sendMail($id, $invoiceid);
            }

            if ($client == 1) {
                $this->sendMail($userid, $invoiceid);
            }
        } catch (Exception $exception) {
            Logger::exception($exception);
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
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

            return back();
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    public function getExtraAmtPaid($userId)
    {
        try {
            $amounts = Payment::where('user_id', $userId)->where('invoice_id', 0)->select('amt_to_credit')->get();
            $balance = 0;
            foreach ($amounts as $amount) {
                if ($amount) {
                    $balance += $amount->amt_to_credit;
                }
            }

            return $balance;
        } catch (Exception $exception) {
            Logger::exception($exception);

            return back()->with('fails', $exception->getMessage());
        }
    }

    /**
     * Get total of the Invoices for a User.
     */
    public function getTotalInvoice($invoices): int|float
    {
        $sum = 0;
        foreach ($invoices as $invoice) {
            $sum += $invoice->grand_total;
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
                    $paidSum += (int) $amount->amount;
                    // $credit = $paidSum + $amount->amt_to_credit;
                }
            }

            return $paidSum;
        } catch (Exception $exception) {
            Logger::exception($exception);

            return back()->with('fails', $exception->getMessage());
        }
    }
}
