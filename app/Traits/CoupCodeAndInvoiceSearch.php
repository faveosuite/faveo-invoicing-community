<?php

namespace App\Traits;

use App\Model\Order\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;

//////////////////////////////////////////////////////////////////////////////
// ADVANCE SEARCH FOR INVOICE AND COUPON CODE CALCULATION
//////////////////////////////////////////////////////////////////////////////

trait CoupCodeAndInvoiceSearch
{
    public function advanceSearch($request)
    {
        return Invoice::with(['user:id,first_name,last_name,email', 'payment'])
            ->when($request->name, function ($query, $name) {
                $query->whereHas('user', function ($q) use ($name) {
                    $q->whereRaw('CONCAT(first_name, " ", last_name) LIKE ?', ["%{$name}%"]);
                });
            })
            ->when($request->invoice_no, fn ($query, $invoice_no) => $query->where('number', $invoice_no)
            )
            ->when($request->status, fn ($query, $status) => $query->where('status', $status)
            )
            ->when($request->currency, fn ($query, $currency) => $query->where('currency', $currency)
            )
            ->when($request->from_date && $request->to_date, function ($query) use ($request) {
                $from = Carbon::parse($request->from_date)->startOfDay();
                $to = Carbon::parse($request->to_date)->endOfDay();
                $query->whereBetween('date', [$from, $to]);
            });
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
     */
    public function deleteBulkInvoices(Request $request)
    {
        try {
            $ids = $request->input('invoice_ids', []);

            if (empty($ids)) {
                return errorResponse(__('message.select-a-row'));
            }

            $this->invoice->whereIn('id', $ids)->delete();

            return successResponse(__('message.deleted-successfully'));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
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
