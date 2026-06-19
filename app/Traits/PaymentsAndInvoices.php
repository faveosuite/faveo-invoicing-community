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
    public function paymentTotalChange(Request $request): ?\Illuminate\Http\RedirectResponse
    {
        try {
            $invoice = new Invoice();
            $total = $request->input('total');
            if ($total == '') {
                $total = 0;
            }

            $paymentid = $request->input('id');
            $creditAmtUserId = $this->payment->where('id', $paymentid)->value('user_id'); // @phpstan-ignore property.notFound
            $creditAmt = $this->payment->where('user_id', $creditAmtUserId) // @phpstan-ignore property.notFound
              ->where('invoice_id', '=', 0)->value('amt_to_credit');
            $invoices = $invoice->where('user_id', $creditAmtUserId)->orderBy('created_at', 'desc')->get();
            $cltCont = new ClientController();
            $invoiceSum = $cltCont->getTotalInvoice($invoices); // @phpstan-ignore argument.type
            if ($total > $invoiceSum) {
                $diff = $total - $invoiceSum;
                $creditAmt += $diff;
                $total = $invoiceSum;
            }

            $payment = $this->payment->where('id', $paymentid)->update(['amount' => $total]); // @phpstan-ignore property.notFound

            /** @var \App\Model\Order\Payment $creditAmtInvoiceId */
            $creditAmtInvoiceId = $this->payment->where('user_id', $creditAmtUserId) // @phpstan-ignore property.notFound
        ->where('invoice_id', '!=', 0)->first();
            $invoiceId = $creditAmtInvoiceId->invoice_id;
            /** @var \App\Model\Order\Invoice $invoiceRecord */
            $invoiceRecord = $invoice->where('id', $invoiceId)->first();
            $grand_total = $invoiceRecord->grand_total;
            $diffSum = $grand_total - $total;

            $finalAmt = $creditAmt + $diffSum;
            $updatedAmt = $this->payment->where('user_id', $creditAmtUserId) // @phpstan-ignore property.notFound
        ->where('invoice_id', '=', 0)->update(['amt_to_credit' => $creditAmt]);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return back()->with('fails', $exception->getMessage());
        }

        return null;
    }

    public function doPayment(
        string $payment_method,
        int $invoiceid,
        float|int $amount,
        int|string $parent_id = '',
        int|string $userid = '',
        string $payment_status = 'pending'
    ): void {
        try {
            if ($amount > 0) {
                if ($userid == '') {
                    /** @var \App\User $authUser */
                    $authUser = Auth::user();
                    $userid = $authUser->id;
                }

                if ($amount == 0) {
                    $payment_status = 'success';
                }

                $this->payment->create([ // @phpstan-ignore property.notFound
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

    public function getAgents(mixed $agents, int $productid, int $plan): int
    {
        if (! $agents) {//If agents is not received in the request in the case when
            // 'modify agent' is not allowed for the Product,get the no of Agents from the Plan Table.
            /** @var \App\Model\Product\Product $productForAgent */
            $productForAgent = Product::find($productid);
            $planForAgent = $productForAgent->planRelation->find($plan);
            if ($planForAgent) {//If Plan Exists For the Product ie not a Product without Plan
                /** @var \App\Model\Payment\PlanPrice $planPriceAgent */
                $planPriceAgent = $planForAgent->planPrice->first();
                $noOfAgents = $planPriceAgent->no_of_agents;
                $agents = $noOfAgents ?: 0; //If no. of Agents is specified then that,else 0(Unlimited Agents)
            } else {
                $agents = 0;
            }
        }

        return $agents;
    }

    public function getQuantity(mixed $qty, int $productid, int $plan): int
    {
        if (! $qty) {//If quantity is not received in the request in the case when 'modify quantity' is not allowed for the Product,get the Product qUANTITY from the Plan Table.
            /** @var \App\Model\Product\Product $productForQty */
            $productForQty = Product::find($productid);
            $planForQty = $productForQty->planRelation->find($plan);
            if ($planForQty) {
                /** @var \App\Model\Payment\Plan $planForQtyObj */
                $planForQtyObj = $productForQty->planRelation->find($plan);
                /** @var \App\Model\Payment\PlanPrice $planPriceQty */
                $planPriceQty = $planForQtyObj->planPrice->first();
                $quantity = $planPriceQty->product_quantity;
                $qty = $quantity ?: 1; //If no. of Agents is specified then that,else 0(Unlimited Agents)
            } else {
                $qty = 1;
            }
        }

        return $qty;
    }

    public function updateInvoice(int $invoiceid): void
    {
        try {
            $invoice = $this->invoice->findOrFail($invoiceid); // @phpstan-ignore property.notFound

            $payment = $this->payment->where('invoice_id', $invoiceid) // @phpstan-ignore property.notFound
            ->where('payment_status', 'success')->pluck('amount')->toArray();
            $total = array_sum($payment);
            if ($total < $invoice->grand_total) {
                $invoice->status = 'pending';
            }

            if ($total >= $invoice->grand_total) {
                $invoice->status = 'success';
            }

            if ($total > $invoice->grand_total) {
                /** @var \App\User $user */
                $user = $invoice->user()->first();
                $balance = $total - (float) $invoice->grand_total;
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

    public function sendmailClientAgent(int $userid, int $invoiceid): void
    {
        try {
            $agent = Input::get('agent');
            $client = Input::get('client');
            if ($agent == 1) {
                /** @var \App\User $authUser */
                $authUser = Auth::user();
                $id = $authUser->id;
                $this->sendMail($id, $invoiceid); // @phpstan-ignore method.notFound
            }

            if ($client == 1) {
                $this->sendMail($userid, $invoiceid); // @phpstan-ignore method.notFound
            }
        } catch (Exception $exception) {
            Logger::exception($exception);
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function payment(Request $request): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        try {
            if ($request->has('invoiceid')) {
                $invoice_id = $request->input('invoiceid');
                /** @var \App\Model\Order\Invoice $invoice */
                $invoice = $this->invoice->find($invoice_id); // @phpstan-ignore property.notFound
                $userid = $invoice->user_id;
                //dd($invoice);
                $invoice_status = '';
                $payment_status = '';
                $payment_method = '';
                $domain = '';
                if ($invoice) { // @phpstan-ignore if.alwaysTrue
                    $invoice_status = $invoice->status;
                    $items = $invoice->invoiceItem()->first();
                    if ($items) {
                        $domain = $items->domain;
                    }
                }

                $payment = $this->payment->where('invoice_id', $invoice_id)->first(); // @phpstan-ignore property.notFound
                if ($payment) {
                    $payment_status = $payment->payment_status;
                    $payment_method = $payment->payment_method;
                }

                return view(
                    'themes.default1.invoice.payment', // @phpstan-ignore argument.type
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

    public function getExtraAmtPaid(int $userId): int|float|\Illuminate\Http\RedirectResponse
    {
        try {
            $amounts = Payment::where('user_id', $userId)->where('invoice_id', 0)->select('amt_to_credit')->get();
            $balance = 0;
            foreach ($amounts as $amount) {
                if ($amount) { // @phpstan-ignore if.alwaysTrue
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
     *
     * @param  \Illuminate\Support\Collection<int|string, mixed>  $invoices
     */
    public function getTotalInvoice(\Illuminate\Support\Collection $invoices): int|float
    {
        $sum = 0;
        foreach ($invoices as $invoice) {
            $sum += $invoice->grand_total;
        }

        return $sum;
    }

    public function getAmountPaid(int $userId): int|\Illuminate\Http\RedirectResponse
    {
        try {
            $amounts = Payment::where('user_id', $userId)->select('amount', 'amt_to_credit')->get();
            $paidSum = 0;
            foreach ($amounts as $amount) {
                if ($amount) { // @phpstan-ignore if.alwaysTrue
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
