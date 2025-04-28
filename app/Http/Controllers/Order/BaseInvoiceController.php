<?php

namespace App\Http\Controllers\Order;

use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Payment\Promotion;
use App\Model\Payment\TaxOption;
use Illuminate\Http\Request;

class BaseInvoiceController extends ExtendedBaseInvoiceController
{
    public function getExpiryStatus($start, $end, $now)
    {
        $whenDateNotSet = $this->whenDateNotSet($start, $end);
        if ($whenDateNotSet) {
            return $whenDateNotSet;
        }
        $whenStartDateSet = $this->whenStartDateSet($start, $end, $now);
        if ($whenStartDateSet) {
            return $whenStartDateSet;
        }
        $whenEndDateSet = $this->whenEndDateSet($start, $end, $now);
        if ($whenEndDateSet) {
            return $whenEndDateSet;
        }
        $whenBothAreSet = $this->whenBothSet($start, $end, $now);
        if ($whenBothAreSet) {
            return $whenBothAreSet;
        }
    }

    public function whenDateNotSet($start, $end)
    {
        //both not set, always true
        if (($start == null || $start == '0000-00-00 00:00:00') &&
         ($end == null || $end == '0000-00-00 00:00:00')) {
            return 'success';
        }
    }

    public function whenStartDateSet($start, $end, $now)
    {
        //only starting date set, check the date is less or equel to today
        if (($start != null || $start != '0000-00-00 00:00:00')
         && ($end == null || $end == '0000-00-00 00:00:00')) {
            if ($start <= $now) {
                return 'success';
            }
        }
    }

    public function whenEndDateSet($start, $end, $now)
    {
        //only ending date set, check the date is greater or equel to today
        if (($end != null || $end != '0000-00-00 00:00:00') && ($start == null || $start == '0000-00-00 00:00:00')) {
            if ($end >= $now) {
                return 'success';
            }
        }
    }

    public function whenBothSet($start, $end, $now)
    {
        //both set
        if (($end != null || $start != '0000-00-00 00:00:00') && ($start != null || $start != '0000-00-00 00:00:00')) {
            if ($end >= $now && $start <= $now) {
                return 'success';
            }
        }
    }

    public function postPayment($invoiceid, Request $request)
    {
        $this->validate($request, [
            'payment_method' => 'required',
            'amount' => 'required|numeric',
            'payment_date' => 'required|date_format:Y-m-d',
        ],
            [
                'payment_method.required' => __('validation.payment.payment_method_required'),
                'amount.required' => __('validation.payment.amount_required'),
                'amount.numeric' => __('validation.amt_numeric'),
                'payment_date.required' => __('validation.payment.payment_date_required'),
                'payment_date.date_format' => __('message.payment-date-format'),
            ]);

        try {
            $payment_method = $request->input('payment_method');
            $payment_status = 'success';
            $payment_date = $request->input('payment_date');
            $amount = $request->input('amount');
            $payment = $this->updateInvoicePayment(
                $invoiceid,
                $payment_method,
                $payment_status,
                $payment_date,
                $amount
            );

            return redirect()->back()->with('success', __('message.payment_accepted_succcessfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function domain($id)
    {
        try {
            if (\Session::has('domain'.$id)) {
                $domain = \Session::get('domain'.$id);
            } else {
                $domain = '';
            }

            return $domain;
        } catch (\Exception $ex) {
        }
    }

    public function checkExpiry($code = '')
    {
        try {
            if ($code != '') {
                $promotion = Promotion::where('code', $code)->first();

                $start = $promotion->start;
                $end = $promotion->expiry;
                $now = \Carbon\Carbon::now();
                $getExpiryStatus = $this->getExpiryStatus($start, $end, $now);

                return $getExpiryStatus;
            }
        } catch (\Exception $ex) {
            throw new \Exception(\Lang::get('message.check-expiry'));
        }
    }

    /*
    *Edit Invoice Total.
    */
    public function invoiceTotalChange(Request $request)
    {
        $total = $request->input('total');
        if ($total == '') {
            $total = 0;
        }
        $number = $request->input('number');
        $invoiceId = Invoice::where('number', $number)->value('id');
        $invoiceItem = InvoiceItem::where('invoice_id', $invoiceId)->update(['subtotal' => $total]);
        $invoices = Invoice::where('number', $number)->update(['grand_total' => $total]);
    }

    public function calculateTotal($rate, $total)
    {
        try {
            $total = intval($total);
            $rates = explode(',', $rate);
            $rule = new TaxOption();
            $rule = $rule->findOrFail(1);
            if ($rule->inclusive == 0) {
                foreach ($rates as $rate1) {
                    if ($rate1 != '') {
                        $rateTotal = str_replace('%', '', $rate1);
                        $total += $total * ($rateTotal / 100);
                    }
                }
            }

            return intval(round($total));
        } catch (\Exception $ex) {
            app('log')->warning($ex->getMessage());

            throw new \Exception($ex->getMessage());
        }
    }

    /**
     * Check if Session has Code and Value of Code.
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     *
     * @date   2019-02-22T13:10:50+0530
     *
     * @return array
     */
    protected function getCodeFromSession()
    {
        $code = '';
        $codevalue = '';
        if (\Session::has('code')) {//If coupon code is applied get it here from Session
            $code = \Session::get('code');
            $codevalue = \Session::get('codevalue');
        }

        return ['code' => $code, 'codevalue' => $codevalue];
    }
}
