<?php

namespace App\Traits;

use App\Model\Order\CreditTransaction;
use App\Model\Order\Invoice;
use App\Model\Order\Payment;
use App\Services\Payment\CreditBalanceService;
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
        return Invoice::with(['user:id,first_name,last_name,email,mobile,mobile_code,country', 'payments', 'invoiceItem']) // @phpstan-ignore return.type
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

    public function deleteBulkPayments(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('payment_ids', []);

            if (empty($ids)) {
                return errorResponse(__('message.select-a-row'));
            }

            $payments = $this->payment->whereIn('id', $ids)->get(); // @phpstan-ignore property.notFound

            foreach ($payments as $payment) {
                // A payment can be settling several invoices, so every one it
                // touched has to be re-derived once it is gone.
                $invoices = $payment->invoices()->get();

                // A credit-funded payment was drawn from the client's balance,
                // so undoing it has to put the credit back — otherwise deleting
                // the row un-pays the invoice and destroys the credit with it.
                if ($payment->payment_method === 'Credit Balance' && (float) $payment->amount > 0) {
                    app(CreditBalanceService::class)->grant(
                        (int) $payment->user_id,
                        $payment->currency ?: (string) $invoices->first()?->currency,
                        (float) $payment->amount,
                        CreditTransaction::TYPE_MANUAL_GRANT,
                        invoiceId: $invoices->first()?->id,
                        note: 'Reversed on deletion of payment #'.$payment->id,
                    );
                }

                $payment->invoices()->detach();
                $payment->delete();

                foreach ($invoices as $invoice) {
                    $invoice->refreshStatus();
                }
            }

            return successResponse(__('message.deleted-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }
}
