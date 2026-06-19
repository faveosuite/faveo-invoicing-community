<?php

namespace App\Http\Controllers\Order;

use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Payment\TaxOption;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Logger;
use Session;

class BaseInvoiceController extends ExtendedBaseInvoiceController
{
    public function getExpiryStatus(?string $start, ?string $end, string $now): ?string
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

        return null;
    }

    public function whenDateNotSet(?string $start, ?string $end): ?string
    {
        //both not set, always true
        if (($start == null || $start === '0000-00-00 00:00:00') &&
         ($end == null || $end === '0000-00-00 00:00:00')) {
            return 'success';
        }

        return null;
    }

    public function whenStartDateSet(?string $start, ?string $end, string $now): ?string
    {
        if ($start == null) {
            return null;
        }

        if ($end != null && $end !== '0000-00-00 00:00:00') {
            return null;
        }

        if ($start <= $now) {
            return 'success';
        }

        return null;
    }

    public function whenEndDateSet(?string $start, ?string $end, string $now): ?string
    {
        if ($end == null) {
            return null;
        }

        if ($start != null && $start !== '0000-00-00 00:00:00') {
            return null;
        }

        if ($end >= $now) {
            return 'success';
        }

        return null;
    }

    public function whenBothSet(?string $start, ?string $end, string $now): ?string
    {
        if ($end == null && $start == '0000-00-00 00:00:00') {
            return null;
        }

        if ($start == null) {
            return null;
        }

        if ($end >= $now && $start <= $now) {
            return 'success';
        }

        return null;
    }

    public function postPayment(int $invoiceid, Request $request): \Illuminate\Http\RedirectResponse
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
            $payment = $this->updateInvoicePayment( // @phpstan-ignore method.notFound
                $invoiceid,
                $payment_method,
                $payment_status,
                $payment_date,
                $amount
            );

            return back()->with('success', __('message.payment_accepted_succcessfully'));
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    public function domain(string $id): ?string
    {
        try {
            if (Session::has('domain'.$id)) {
                return Session::get('domain'.$id);
            }

            return '';
        } catch (Exception) {
        }

        return null;
    }

    /*
    *Edit Invoice Total.
    */
    public function invoiceTotalChange(Request $request): void
    {
        $total = $request->input('total');
        if ($total == '') {
            $total = 0;
        }

        $number = $request->input('number');
        $invoiceId = Invoice::where('number', $number)->value('id');
        InvoiceItem::where('invoice_id', $invoiceId)->update(['subtotal' => $total]);
        Invoice::where('number', $number)->update(['grand_total' => $total]);
    }

    public function calculateTotal(string $rate, int|float $total): float
    {
        try {
            $total = intval($total);
            $rates = explode(',', $rate);
            $rule = new TaxOption();
            $rule = $rule->findOrFail(1);
            if ($rule->inclusive == 0) {
                foreach ($rates as $rate1) {
                    if ($rate1 !== '') {
                        $rateTotal = str_replace('%', '', $rate1);
                        $total += $total * ((float) $rateTotal / 100);
                    }
                }
            }

            return intval(round($total));
        } catch (Exception $exception) {
            Logger::exception($exception);

            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Check if Session has Code and Value of Code.
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     *
     * @date   2019-02-22T13:10:50+0530
     *
     * @return array<mixed>
     */
    protected function getCodeFromSession(): array
    {
        $code = '';
        $codevalue = '';
        if (Session::has('code')) {//If coupon code is applied get it here from Session
            $code = Session::get('code');
            $codevalue = Session::get('codevalue');
        }

        return ['code' => $code, 'codevalue' => $codevalue];
    }
}
