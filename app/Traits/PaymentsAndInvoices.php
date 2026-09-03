<?php

namespace App\Traits;

use App\Model\Order\Payment;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use Exception;
use Illuminate\Support\Collection;
use Logger;

// ////////////////////////////////////////////////////////////////////////////
// PAYMENTS AND EXTRA FUNCTIONALITIES FOR INVOICES
// ////////////////////////////////////////////////////////////////////////////

trait PaymentsAndInvoices
{
    public function getAgents(mixed $agents, int $productid, ?int $plan): int
    {
        if (! $agents) {// If agents is not received in the request in the case when
            // 'modify agent' is not allowed for the Product,get the no of Agents from the Plan Table.
            /** @var Product $productForAgent */
            $productForAgent = Product::find($productid);
            $planForAgent = $productForAgent->planRelation->find($plan);
            if ($planForAgent) {// If Plan Exists For the Product ie not a Product without Plan
                /** @var PlanPrice $planPriceAgent */
                $planPriceAgent = $planForAgent->planPrice->first();
                $noOfAgents = $planPriceAgent->no_of_agents;
                $agents = $noOfAgents ?: 0; // If no. of Agents is specified then that,else 0(Unlimited Agents)
            } else {
                $agents = 0;
            }
        }

        return $agents;
    }

    public function getQuantity(mixed $qty, int $productid, ?int $plan): int
    {
        if (! $qty) {// If quantity is not received in the request in the case when 'modify quantity' is not allowed for the Product,get the Product qUANTITY from the Plan Table.
            /** @var Product $productForQty */
            $productForQty = Product::find($productid);
            $planForQty = $productForQty->planRelation->find($plan);
            if ($planForQty) {
                /** @var Plan $planForQtyObj */
                $planForQtyObj = $productForQty->planRelation->find($plan);
                /** @var PlanPrice $planPriceQty */
                $planPriceQty = $planForQtyObj->planPrice->first();
                $quantity = $planPriceQty->product_quantity;
                $qty = $quantity ?: 1; // If no. of Agents is specified then that,else 0(Unlimited Agents)
            } else {
                $qty = 1;
            }
        }

        return $qty;
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
     * What the client has actually paid us. Excludes the pre-ledger credit
     * deposits (invoice_id = 0), which are a balance the client holds, not
     * money settled against an invoice — counting them inflated this figure by
     * their whole deposit history.
     */
    public function getAmountPaid(int $userId): float
    {
        try {
            return (float) Payment::where('user_id', $userId)
                ->where('invoice_id', '!=', 0)
                ->where('payment_status', 'success')
                ->sum('amount');
        } catch (Exception $exception) {
            Logger::exception($exception);

            return 0;
        }
    }
}
