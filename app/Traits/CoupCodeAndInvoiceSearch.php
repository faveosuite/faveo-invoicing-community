<?php

namespace App\Traits;

use App\Model\Order\Invoice;
use App\Model\Order\Payment;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

// ////////////////////////////////////////////////////////////////////////////
// ADVANCE SEARCH FOR INVOICE AND COUPON CODE CALCULATION
// ////////////////////////////////////////////////////////////////////////////

trait CoupCodeAndInvoiceSearch
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder<Model>
     * @return \Illuminate\Database\Eloquent\Builder<Model>
     */
    public function advanceSearch(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        return Invoice::with(['user:id,first_name,last_name,email,mobile,mobile_code,country', 'payment', 'invoiceItem']) // @phpstan-ignore return.type
            ->when($request->name, function ($query, $name): void {
                $query->whereHas('user', function (Builder $q) use ($name): void {
                    $q->whereRaw('CONCAT(first_name, " ", last_name) LIKE ?', [sprintf('%%%s%%', $name)]);
                });
            })
            ->when($request->invoice_no, fn ($query, $invoice_no) => $query->where('number', $invoice_no)
            )
            ->when($request->status, fn ($query, $status) => $query->where('status', $status)
            )
            ->when($request->currency, fn ($query, $currency) => $query->where('currency', $currency)
            )
            ->when($request->from_date && $request->to_date, function ($query) use ($request): void {
                $from = Date::parse($request->from_date)->startOfDay();
                $to = Date::parse($request->to_date)->endOfDay();
                $query->whereBetween('date', [$from, $to]);
            });
    }

    public function updateInvoicePayment(int $invoiceid, string $payment_method, string $payment_status, string $payment_date, float $amount): Payment
    {
        try {
            /** @var Invoice $invoice */
            $invoice = Invoice::find($invoiceid);
            $processingFee = '';

            $invoice_status = 'pending';

            $payment = $this->payment->create([ // @phpstan-ignore property.notFound
                'invoice_id' => $invoiceid,
                'user_id' => $invoice->user_id,
                'amount' => $amount,
                'payment_method' => $payment_method,
                'payment_status' => $payment_status,
                'created_at' => $payment_date,
            ]);
            $all_payments = $this->payment // @phpstan-ignore property.notFound
                ->where('invoice_id', $invoiceid)
                ->where('payment_status', 'success')
                ->pluck('amount')->toArray();

            $total_paid = array_sum($all_payments);
            if ($total_paid >= $invoice->grand_total) {
                $invoice_status = 'success';
            }

            if ($invoice) { // @phpstan-ignore if.alwaysTrue
                $sessionValue = $this->getCodeFromSession(); // @phpstan-ignore method.notFound
                $code = $sessionValue['code'];
                $codevalue = $sessionValue['codevalue'];
                $invoice->discount = $codevalue;
                $invoice->coupon_code = $code;
                $invoice->processing_fee = $processingFee;
                $invoice->status = $invoice_status;
            }

            return $payment;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteBulkInvoices(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('invoice_ids', []);

            if (empty($ids)) {
                return errorResponse(__('message.select-a-row'));
            }

            $this->invoice->whereIn('id', $ids)->delete(); // @phpstan-ignore property.notFound

            return successResponse(__('message.deleted-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function deletePayment(Request $request): void
    {
        try {
            $ids = $request->input('select');
            if (! empty($ids)) {
                foreach ($ids as $id) {
                    $payment = $this->payment->where('id', $id)->first(); // @phpstan-ignore property.notFound
                    if ($payment) {
                        $invoice = $this->invoice->find($payment->invoice_id); // @phpstan-ignore property.notFound
                        if ($invoice) {
                            $invoice->status = 'pending';
                            $invoice->save();
                        }

                        $payment->delete();
                    } else {
                        echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".(string) __('message.alert').'!</b> 
                    '.(string) __('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.(string) __('message.no-record').'
                </div>';
                    }
                }

                echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".(string) __('message.alert').'!</b> '.
                    (string) __('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.(string) __('message.deleted-successfully').'
                </div>';
            } else {
                echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".(string) __('message.alert').'!</b> '.
                    (string) __('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.(string) __('message.select-a-row').'
                </div>';
            }
        } catch (Exception $exception) {
            echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".(string) __('message.alert').'!</b> '.
                    (string) __('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$exception->getMessage().'
                </div>';
        }
    }

    /**
     * JSON bulk-delete for payments (SPA). Deletes the given payment rows and
     * recomputes the status of any invoice they were linked to.
     */
    public function deleteBulkPayments(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('payment_ids', []);

            if (empty($ids)) {
                return errorResponse(__('message.select-a-row'));
            }

            $payments = $this->payment->whereIn('id', $ids)->get(); // @phpstan-ignore property.notFound

            foreach ($payments as $payment) {
                $invoiceId = $payment->invoice_id;
                $payment->delete();

                if ($invoiceId) {
                    $invoice = $this->invoice->find($invoiceId); // @phpstan-ignore property.notFound
                    if ($invoice) {
                        $paid = $this->payment->where('invoice_id', $invoiceId) // @phpstan-ignore property.notFound
                            ->where('payment_status', 'success')
                            ->sum('amount');

                        if ($paid >= $invoice->grand_total) {
                            $invoice->status = 'success';
                        } elseif ($paid > 0) {
                            $invoice->status = 'partially paid';
                        } else {
                            $invoice->status = 'pending';
                        }

                        $invoice->save();
                    }
                }
            }

            return successResponse(__('message.deleted-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }
}
