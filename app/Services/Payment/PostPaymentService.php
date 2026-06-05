<?php

namespace App\Services\Payment;

use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\RenewController;
use App\Http\Controllers\Tenancy\CloudExtraActivities;
use App\Http\Controllers\Tenancy\TenantController;
use App\Model\Cart\Cart;
use App\Model\Common\FaveoCloud;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Order\Payment;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Traits\Payment\PostPaymentHandle;
use App\Traits\TaxCalculation;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class PostPaymentService
{
    use TaxCalculation, PostPaymentHandle;

    public function handle(Invoice $invoice, string $gateway): array
    {
        $this->clearCart($invoice);
        $this->recordPayment($invoice, $gateway);

        $metadata = $invoice->metadata ?? [];
        $type = $metadata['type'] ?? 'purchase';

        $result = match ($type) {
            'agent_alteration' => $this->handleAgentAlteration($invoice, $metadata),
            'upgrade_downgrade' => $this->handleUpgradeDowngrade($invoice, $metadata),
            default => $invoice->is_renewed == 1
                                       ? $this->handleRenewal($invoice, $metadata)
                                       : $this->handlePurchase($invoice),
        };

        if ($invoice->grand_total && emailSendingStatus()) {
            $user = User::find($invoice->user_id);
            $productNames = $invoice->invoiceItem()->pluck('product_name')->implode(', ');
            self::sendPaymentSuccessMailtoAdmin($invoice, $invoice->grand_total, $user, $productNames);
        }

        return $result;
    }

    private function clearCart(Invoice $invoice): void
    {
        $cart = Cart::where('user_id', $invoice->user_id)->first();
        if ($cart) {
            $cart->items()->delete();
            $cart->update(['coupon_code' => null, 'coupon_discount' => 0, 'invoice_id' => null]);
        }
    }

    private function handlePurchase(Invoice $invoice): array
    {
        $this->executeOrders($invoice);
        $this->doTheDeed($invoice);

        if (! empty($invoice->cloud_domain)) {
            $orderNumber = Order::whereIn(
                'id',
                OrderInvoiceRelation::where('invoice_id', $invoice->id)->pluck('order_id')
            )->whereIn('product', cloudPopupProducts())->value('number');

            (new TenantController(new Client(), new FaveoCloud()))->createTenant(
                new Request(['orderNo' => $orderNumber, 'domain' => $invoice->cloud_domain])
            );
        }

        return ['status' => 'success'];
    }

    private function handleRenewal(Invoice $invoice, array $metadata): array
    {
        (new RenewController())->successRenew($invoice);

        $this->doTheDeed($invoice);

        // Agent count also changed at renewal time (metadata written by BaseRenewController in Task 4)
        if (! empty($metadata['renewal_agent'])) {
            $ra = $metadata['renewal_agent'];
            $cloud = new CloudExtraActivities(new Client(), new FaveoCloud());
            $cloud->doTheAgentAltering(
                $ra['new_agents'],
                $ra['old_license'],
                $ra['order_id'],
                $ra['installation_path'],
                $ra['product_id'],
            );
        }

        return ['status' => 'success'];
    }

    private function handleAgentAlteration(Invoice $invoice, array $metadata): array
    {
        $cloud = new CloudExtraActivities(new Client(), new FaveoCloud());

        if ($metadata['agent_increase_date'] ?? false) {
            (new RenewController())->successRenew($invoice);
        }

        $this->doTheDeed($invoice);

        $cloud->doTheAgentAltering(
            $metadata['new_agents'],
            $metadata['old_license'],
            $metadata['order_id'],
            $metadata['installation_path'],
            $metadata['product_id'],
        );

        $this->updateSubscriptionPriceIfNeeded($metadata['order_id'], $invoice);

        return ['status' => 'success'];
    }

    private function handleUpgradeDowngrade(Invoice $invoice, array $metadata): array
    {
        $terminatedOrderId = (int) $metadata['old_order_id'];
        $oldLicense = (string) $metadata['old_license'];
        $installationPath = (string) $metadata['installation_path'];
        $discount = isset($metadata['discount']) ? (float) $metadata['discount'] : null;

        $this->executeOrders($invoice);

        $newActiveOrderId = (int) OrderInvoiceRelation::where('invoice_id', $invoice->id)
            ->latest()->value('order_id');
        $newOrder = Order::find($newActiveOrderId);
        if (! $newOrder) {
            throw new \RuntimeException("New order not found for invoice #{$invoice->id} after checkoutAction.");
        }
        $licenseCode = \Crypt::decrypt($newOrder->serial_key);
        $productId = (int) $newOrder->product;

        $this->doTheDeed($invoice);

        $cloud = new \App\Http\Controllers\Tenancy\CloudExtraActivities(new Client(), new FaveoCloud());
        $cloud->doTheProductUpgradeDowngrade(
            $licenseCode,
            $installationPath,
            $productId,
            $oldLicense,
            $terminatedOrderId,
            $newActiveOrderId,
            $discount,
        );

        // Transfer subscription from terminated order to new order
        $termOrderId = \DB::table('terminated_order_upgrade')
            ->where('upgraded_order_id', $newActiveOrderId)->value('terminated_order_id');
        $terminatedOrder = Order::find($termOrderId);
        if ($terminatedOrder) {
            $oldSub = Subscription::where('order_id', $terminatedOrder->id)->first();
            if ($terminatedOrder->order_status === 'Terminated' && $oldSub?->subscribe_id) {
                Subscription::where('order_id', $newActiveOrderId)->update([
                    'subscribe_id' => $oldSub->subscribe_id,
                    'is_subscribed' => $oldSub->is_subscribed,
                    'autoRenew_status' => $oldSub->autoRenew_status,
                    'rzp_subscription' => $oldSub->rzp_subscription,
                ]);
                $this->updateSubscriptionPriceIfNeeded($newActiveOrderId, $invoice);
            } elseif ($terminatedOrder->order_status === 'Terminated' && $oldSub?->is_subscribed === '1') {
                Subscription::where('order_id', $newActiveOrderId)->update([
                    'is_subscribed' => $oldSub->is_subscribed,
                    'autoRenew_status' => $oldSub->autoRenew_status,
                    'rzp_subscription' => $oldSub->rzp_subscription,
                ]);
            }
        }

        return ['status' => 'success'];
    }

    private function executeOrders(Invoice $invoice): void
    {
        $alreadyExecuted = Order::whereIn(
            'id',
            OrderInvoiceRelation::where('invoice_id', $invoice->id)->pluck('order_id')
        )->exists();

        if (! $alreadyExecuted) {
            (new OrderController())->executeOrder($invoice->id);
        }
    }

    private function recordPayment(Invoice $invoice, string $gateway): void
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
                'created_at' => \Carbon\Carbon::now(),
            ]);
        }

        $invoice->update(['status' => 'success']);
    }

    private function doTheDeed(Invoice $invoice): void
    {
        $amt_to_credit = Payment::where('user_id', \Auth::user()->id)
            ->where('payment_status', 'success')
            ->where('payment_method', 'Credit Balance')
            ->value('amt_to_credit');

        if ($amt_to_credit) {
            $amt_to_credit = (int) $amt_to_credit - (int) $invoice->billing_pay;
            Payment::where('user_id', \Auth::user()->id)
                ->where('payment_method', 'Credit Balance')
                ->where('payment_status', 'success')
                ->update(['amt_to_credit' => $amt_to_credit]);
            \App\User::where('id', \Auth::user()->id)->update(['billing_pay_balance' => 0]);

            $payment_id = \DB::table('payments')
                ->where('user_id', \Auth::user()->id)
                ->where('payment_status', 'success')
                ->where('payment_method', 'Credit Balance')
                ->value('id');
            $formattedValue = currencyFormat($invoice->billing_pay, $invoice->currency, true);

            $messageAdmin = 'The payment balance of '.$formattedValue.' has been utilized or adjusted with this invoice.'
                .' You can view the details of the invoice '
                .'<a href="'.config('app.url').'/invoices/show?invoiceid='.$invoice->id.'">'.$invoice->number.'</a>.';
            $messageClient = 'The payment balance of '.$formattedValue.' has been utilized or adjusted with this invoice.'
                .' You can view the details of the invoice '
                .'<a href="'.config('app.url').'/my-invoice/'.$invoice->id.'">'.$invoice->number.'</a>.';

            \DB::table('credit_activity')->insert(['payment_id' => $payment_id, 'text' => $messageAdmin,  'role' => 'admin', 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()]);
            \DB::table('credit_activity')->insert(['payment_id' => $payment_id, 'text' => $messageClient, 'role' => 'user',  'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()]);

            if ($invoice->billing_pay) {
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'user_id' => $invoice->user_id,
                    'amount' => $invoice->billing_pay,
                    'payment_method' => 'Credits',
                    'payment_status' => 'success',
                    'created_at' => \Carbon\Carbon::now(),
                ]);
            }
        }
    }

    /**
     * Check and update the subscription price if necessary.
     *
     * @param  string  $orderId  The order ID associated with the subscription.
     * @param  object  $invoice  The invoice object for the subscription.
     * @return void
     */
    private function updateSubscriptionPriceIfNeeded($orderId, Invoice $invoice): void
    {
        $subscription = Subscription::where('order_id', $orderId)->first();

        if (! $subscription) {
            return; // No subscription found
        }

        $order = Order::find($orderId);
        $product = Product::find($subscription->product_id);

        if ($subscription->is_subscribed != '1') {
            return; // Subscription not active
        }

        if ($subscription->rzp_subscription != '3' && $subscription->autoRenew_status != '3') {
            return; // Subscription not eligible for price check/update
        }

        $plan = Plan::find($subscription->plan_id);
        $countryids = \App\Model\Common\Country::where('country_code_char2', \Auth::user()->country)->first();
        $price = PlanPrice::where('plan_id', $subscription->plan_id)->where('currency', $invoice->currency)->where('country_id', $countryids->country_id)->value('renew_price');
        if (empty($price)) {
            $price = PlanPrice::where('plan_id', $subscription->plan_id)->where('currency', $invoice->currency)->where('country_id', 0)->value('renew_price');
        }
        $amount = $this->getPriceForCloud($order, $price, $subscription->product_id, $invoice->currency, $subscription);
        $renewPrice = intval(calculateUnitCost($invoice->currency, $amount));

        if (! $subscription->subscribe_id) {
            return;
        }

        $gateway = $subscription->rzp_subscription == '3' ? 'Razorpay' : 'Stripe';

        // The gateway driver fetches the live subscription, skips the update when
        // the price/interval already matches or the subscription is inactive, and
        // (Stripe) cancels + flags raw['cancelled'] if the change deactivates it.
        $result = app(\App\Services\Payment\SubscriptionService::class)->updateSubscriptionPrice(
            $gateway,
            $subscription->subscribe_id,
            new \App\Plugins\Payment\Dto\SubscriptionRequest(
                amountMinor: $renewPrice,
                currency: $invoice->currency,
                intervalDays: (int) $plan->days,
                planName: $product->name,
            )
        );

        if (($result->raw['cancelled'] ?? false) === true) {
            Subscription::where('id', $subscription->id)->update([
                'is_subscribed' => '0',
                'autoRenew_status' => '0',
                'subscribe_id' => null,
            ]);
        }
    }

    private function getPriceForCloud($order, $price, $product, $currency, $subscription): float|int
    {
        $numberofAgents = (int) ltrim(substr($order->serial_key, -4), '0');
        $finalPrice = $numberofAgents * $price;
        $controller = new \App\Http\Controllers\Order\InvoiceController();
        $tax = $this->calculateTax($product, \Auth::user()->state, \Auth::user()->country);
        $tax_rate = $tax['value'];
        $cost = rounding($controller->calculateTotal($tax_rate, $finalPrice));

        return $cost;
    }
}
