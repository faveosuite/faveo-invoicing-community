<?php

namespace App\Traits;

use App\Model\Order\PaymentInvoice;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Collection;
use Logger;

// ////////////////////////////////////////////////////////////////////////////
// PAYMENTS AND EXTRA FUNCTIONALITIES FOR INVOICES
// ////////////////////////////////////////////////////////////////////////////

trait PaymentsAndInvoices
{
    /** The quantity asked for, or 1 when the product doesn't let the customer change it. */
    public function getQuantity(mixed $qty): int
    {
        return (int) $qty ?: 1;
    }

    /**
     * Get total of the Invoices for a User.
     *
     * @param  Collection<int|string, mixed>  $invoices
     */
    public function getTotalInvoice(Collection $invoices): int|float
    {
        $sum = 0;
        foreach ($invoices as $invoice) {
            $sum += $invoice->grand_total;
        }

        return $sum;
    }

    /**
     * What the client has actually paid against their invoices — read from the
     * payment_invoice allocations, the same source as Invoice::paidTotal(), so
     * this total is exactly the sum of the invoices' paid amounts.
     *
     * The legacy payments.invoice_id column is NOT usable here: a payment taken
     * as an advance keeps invoice_id = 0 and records what it settles only in the
     * pivot, so summing by that column reported a fully paid invoice as unpaid.
     * Money not allocated to any invoice stays out of this figure on purpose —
     * it is reported separately as the unapplied balance.
     */
    public function getAmountPaid(int $userId): float
    {
        try {
            return (float) PaymentInvoice::whereHas('payment', function (Builder $query) use ($userId): void {
                $query->where('user_id', $userId)->where('payment_status', 'success');
            })->sum('amount');
        } catch (Exception $exception) {
            Logger::exception($exception);

            return 0;
        }
    }
}
