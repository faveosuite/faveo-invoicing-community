<?php

namespace App\Traits;

use App\Model\Order\Invoice;
use Illuminate\Http\Request;

//////////////////////////////////////////////////////////////////////////////
// ADVANCE SEARCH FOR INVOICE AND COUPON CODE CALCULATION
//////////////////////////////////////////////////////////////////////////////

trait CoupCodeAndInvoiceSearch
{
    public function advanceSearch($name = '', $invoice_no = '', $currency = '', $status = '', $from = '', $till = '')
    {
        $join = Invoice::leftJoin('users', 'invoices.user_id', '=', 'users.id')
        ->join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
           ->select(
               'invoices.id',
               'first_name',
               'invoices.created_at',
               'invoices.date',
               'invoices.currency',
               'user_id',
               'invoices.grand_total',
               'number',
               'status',
               'invoice_items.product_name',
           )->orderBy('created_at', 'desc');

        $this->name($name, $join);
        $this->invoice_no($invoice_no, $join);
        $this->status($status, $join);
        $this->searchcurrency($currency, $join);
        $this->invoiceFromTo($join, $from, $till);

        return $join;
    }

    public function invoiceFromTo($join, $reg_from, $reg_till)
    {
        if ($reg_from && $reg_till) {
            $fromDateStart = date_create($reg_from)->format('Y-m-d').' 00:00:00';
            $tillDateEnd = date_create($reg_till)->format('Y-m-d').' 23:59:59';

            $join = $join->whereBetween('date', [$fromDateStart, $tillDateEnd]);
        }

        return $join;
    }

    public function name($name, $join)
    {
        if ($name) {
            $join = $join->where('first_name', $name);

            return $join;
        }
    }

    public function invoice_no($invoice_no, $join)
    {
        if ($invoice_no) {
            $join = $join->where('number', $invoice_no);

            return $join;
        }
    }

    public function status($status, $join)
    {
        if ($status) {
            $join = $join->where('status', $status);

            return $join;
        }
    }

    public function searchcurrency($currency, $join)
    {
        if ($currency) {
            $join = $join->where('invoices.currency', $currency);

            return $join;
        }
    }

    public function invoice_from($from, $till, $join)
    {
        if ($from) {
            $fromdate = date_create($from);
            $from = date_format($fromdate, 'Y-m-d H:m:i');
            $tills = date('Y-m-d H:m:i');
            $tillDate = $this->getTillDate($from, $till, $tills);
            $join = $join->whereBetween('invoices.date', [$from, $tillDate]);

            return $join;
        }

        return $join;
    }

    public function till_date($till, $from, $join)
    {
        if ($till) {
            $tilldate = date_create($till);
            $till = date_format($tilldate, 'Y-m-d H:m:i');
            $froms = Invoice::first()->date;
            $fromDate = $this->getFromDate($from, $froms);
            $join = $join->whereBetween('invoices.date', [$fromDate, $till]);

            return $join;
        }
    }

    public function getTillDate($from, $till, $tills)
    {
        if ($till) {
            $todate = date_create($till);
            $tills = date_format($todate, 'Y-m-d H:m:i');
        }

        return $tills;
    }

    public function getFromDate($from, $froms)
    {
        if ($from) {
            $fromdate = date_create($from);
            $froms = date_format($fromdate, 'Y-m-d H:m:i');
        }

        return $froms;
    }

    public function updateInvoicePayment($invoiceid, $payment_method, $payment_status, $payment_date, $amount)
    {
        try {
            $invoice = Invoice::find($invoiceid);
            $processingFee = '';
            foreach (\Cart::getConditionsByType('fee') as $processFee) {
                $processingFee = $processFee->getValue();
            }
            $invoice_status = 'pending';

            $payment = $this->payment->create([
                'invoice_id' => $invoiceid,
                'user_id' => $invoice->user_id,
                'amount' => $amount,
                'payment_method' => $payment_method,
                'payment_status' => $payment_status,
                'created_at' => $payment_date,
            ]);
            $all_payments = $this->payment
            ->where('invoice_id', $invoiceid)
            ->where('payment_status', 'success')
            ->pluck('amount')->toArray();

            $total_paid = array_sum($all_payments);
            if ($total_paid >= $invoice->grand_total) {
                $invoice_status = 'success';
            }
            if ($invoice) {
                $sessionValue = $this->getCodeFromSession();
                $code = $sessionValue['code'];
                $codevalue = $sessionValue['codevalue'];
                $invoice->discount = $codevalue;
                $invoice->coupon_code = $code;
                $invoice->processing_fee = $processingFee;
                $invoice->status = $invoice_status;
            }

            return $payment;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Response
     */
    public function destroy(Request $request)
    {
        try {
            $ids = $request->input('select');
            if (! empty($ids)) {
                foreach ($ids as $id) {
                    $invoice = $this->invoice->where('id', $id)->first();
                    if ($invoice) {
                        $invoice->delete();
                    } else {
                        echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */
                    \Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.no-record').'
                </div>';
                        //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                    }
                }
                echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-check'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */
                    \Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.deleted-successfully').'
                </div>';
            } else {
                echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.select-a-row').'
                </div>';
                //echo \Lang::get('message.select-a-row');
            }
        } catch (\Exception $e) {
            echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$e->getMessage().'
                </div>';
        }
    }

    public function deletePayment(Request $request)
    {
        try {
            $ids = $request->input('select');
            if (! empty($ids)) {
                foreach ($ids as $id) {
                    $payment = $this->payment->where('id', $id)->first();
                    if ($payment) {
                        $invoice = $this->invoice->find($payment->invoice_id);
                        if ($invoice) {
                            $invoice->status = 'pending';
                            $invoice->save();
                        }
                        $payment->delete();
                    } else {
                        echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */ \Lang::get('message.alert').'!</b> 
                    './* @scrutinizer ignore-type */\Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.no-record').'
                </div>';
                        //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                    }
                }
                echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */
                    \Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.deleted-successfully').'
                </div>';
            } else {
                echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.select-a-row').'
                </div>';
                //echo \Lang::get('message.select-a-row');
            }
        } catch (\Exception $e) {
            echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */ \Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$e->getMessage().'
                </div>';
        }
    }
}
