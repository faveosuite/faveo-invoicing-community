<?php

namespace App\Traits\Payment;

use App\ApiKey;
use App\Http\Controllers\Tenancy\TenantController;
use App\Model\Common\FaveoCloud;
use App\Model\Common\Setting;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Order\Payment;
use App\Model\Payment\Currency;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Traits\TaxCalculation;
use App\User;
use Carbon\Carbon;
use DateTime;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Razorpay\Api\Api;

//////////////////////////////////////////////////////////////////////////////
// Handle the post manual payment
//////////////////////////////////////////////////////////////////////////////

trait PostPaymentHandle
{
    use TaxCalculation;

    public static function sendFailedPaymenttoAdmin($invoice, $total, $productName, $exceptionMessage, $user)
    {
        $amount = currencyFormat($total, \Auth::user()->currency);
        $payment = Payment::where('invoice_id', $invoice->id)->first();
        $orderid = OrderInvoiceRelation::where('invoice_id', $invoice->id)->value('order_id');
        $order = Order::find($orderid);
        $setting = Setting::find(1);
        $paymentFailData = 'Payment for'.' '.'of'.' '.\Auth::user()->currency.' '.round($total).' '.'failed by'.' '.\Auth::user()->first_name.' '.\Auth::user()->last_name.' '.'. User Email:'.' '.\Auth::user()->email.'<br>'.'Reason:'.$exceptionMessage;
        $mail = new \App\Http\Controllers\Common\PhpMailController();
        $mail->SendEmail($setting->email, $setting->company_email, $paymentFailData, 'Payment failed ');
        if ($payment) {
            $message = $invoice->is_renewed == 1 ? 'Product renew' : 'Product purchase';
            $mail->payment_log($user->email, $payment->payment_method, $payment->payment_status, $order->number, $exceptionMessage, $amount, $message);
        }
    }

    public static function sendPaymentSuccessMailtoAdmin($invoice, $total, $user, $productName)
    {
        $amount = currencyFormat($total, \Auth::user()->currency);
        $payment = Payment::where('invoice_id', $invoice->id)->first();
        $orderid = OrderInvoiceRelation::where('invoice_id', $invoice->id)->value('order_id');
        $order = Order::find($orderid);
        $setting = Setting::find(1);
        $paymentSuccessdata = 'Payment for '.$productName.' of '.\Auth::user()->currency.' '.round($total).' successful by '.$user->first_name.' '.$user->last_name.' Email: '.$user->email;

        $mail = new \App\Http\Controllers\Common\PhpMailController();
        $mail->SendEmail($setting->email, $setting->company_email, $paymentSuccessdata, 'Payment Successful');
        if ($payment) {
            $message = $invoice->is_renewed == 1 ? 'Product renew' : 'Product purchase';
            $mail->payment_log($user->email, $payment->payment_method, $payment->payment_status, $order->number, null, $amount, $message);
        }
    }

    public function processPaymentSuccess($invoice, $currency)
    {
        try {
            $user = User::find($invoice->user_id);
            $stateCode = \Auth::user()->state;
            $cont = new \App\Http\Controllers\RazorpayController();
            $state = $cont->getState($stateCode);
            $currency = Currency::where('code', $currency)->pluck('symbol')->first();

            $control = new \App\Http\Controllers\Order\RenewController();
            $cloud = new \App\Http\Controllers\Tenancy\CloudExtraActivities(new Client, new FaveoCloud());
            // After Regular Payment
            if ($control->checkRenew($invoice->is_renewed) === false && $invoice->is_renewed == 0 && ! $cloud->checkUpgradeDowngrade()) {
                $checkout_controller = new \App\Http\Controllers\Front\CheckoutController();
                $checkout_controller->checkoutAction($invoice);

                $this->doTheDeed($invoice);

                if (! empty($invoice->cloud_domain)) {
                    $orderNumber = Order::where('invoice_id', $invoice->id)->whereIn('product', cloudPopupProducts())->value('number');
                    (new TenantController(new Client, new FaveoCloud()))->createTenant(new Request(['orderNo' => $orderNumber, 'domain' => $invoice->cloud_domain]));
                }

                $view = $cont->getViewMessageAfterPayment($invoice, $state, $currency);
            } elseif ($cloud->checkAgentAlteration()) {
                if (\Session::has('agentIncreaseDate')) {
                    $control->successRenew($invoice);
                    \Session::forget('agentIncreaseDate');
                }

                $subId = \Session::get('AgentAlteration');
                $newAgents = \Session::get('newAgents');
                $orderId = \Session::get('orderId');
                $installationPath = \Session::get('installation_path');
                $productId = \Session::get('product_id');
                $oldLicense = \Session::get('oldLicense');
                $payment = new \App\Http\Controllers\Order\InvoiceController();
                $payment->postRazorpayPayment($invoice);
                Invoice::where('id', $invoice->id)->update(['status' => 'success']);
                if ($invoice->grand_total && emailSendingStatus()) {
                    $this->sendPaymentSuccessMailtoAdmin($invoice, $invoice->grand_total, $user, $invoice->invoiceItem()->first()->product_name);
                }
                $this->doTheDeed($invoice);
                $view = $cont->getViewMessageAfterRenew($invoice, $state, $currency);
                $cloud->doTheAgentAltering($newAgents, $oldLicense, $orderId, $installationPath, $productId);
                $this->updateSubscriptionPriceIfNeeded($orderId, $invoice); //Check and update the subscription price if necessary
            } elseif ($cloud->checkUpgradeDowngrade()) {
                $checkout_controller = new \App\Http\Controllers\Front\CheckoutController();
                $checkout_controller->checkoutAction($invoice);
                $oldLicense = \Session::get('upgradeOldLicense');
                $installationPath = \Session::get('upgradeInstallationPath');
                $productId = \Session::get('upgradeProductId');
                $licenseCode = \Session::get('upgradeSerialKey');
                $this->doTheDeed($invoice);
                $cloud->doTheProductUpgradeDowngrade($licenseCode, $installationPath, $productId, $oldLicense);
                $view = $cont->getViewMessageAfterPayment($invoice, $state, $currency);
                $orderId = OrderInvoiceRelation::where('invoice_id', $invoice->id)->latest()->value('order_id');
                $term_order_id = \DB::table('terminated_order_upgrade')->where('upgraded_order_id', $orderId)->value('terminated_order_id');
                $terminatedOrder = Order::find($term_order_id);
                if ($terminatedOrder) {
                    $oldSubscription = Subscription::where('order_id', $terminatedOrder->id)->first();
                    if ($terminatedOrder->order_status == 'Terminated' && $oldSubscription->subscribe_id != '' && $oldSubscription->subscribe_id != null) {
                        $newSub = Subscription::where('order_id', $orderId)->update(['subscribe_id' => $oldSubscription->subscribe_id, 'is_subscribed' => $oldSubscription->is_subscribed, 'autoRenew_status' => $oldSubscription->autoRenew_status,
                            'rzp_subscription' => $oldSubscription->rzp_subscription]);
                        $this->updateSubscriptionPriceIfNeeded($orderId, $invoice); //Check and update the subscription price if necessary
                    } elseif ($terminatedOrder->order_status == 'Terminated' && $oldSubscription->is_subscribed == '1') {
                        $newSub = Subscription::where('order_id', $orderId)->update(['is_subscribed' => $oldSubscription->is_subscribed, 'autoRenew_status' => $oldSubscription->autoRenew_status,
                            'rzp_subscription' => $oldSubscription->rzp_subscription]);
                    }
                }
            } else {
                $control->successRenew($invoice);
                $payment = new \App\Http\Controllers\Order\InvoiceController();
                $payment->postRazorpayPayment($invoice);
                if ($invoice->grand_total && emailSendingStatus()) {
                    $this->sendPaymentSuccessMailtoAdmin($invoice, $invoice->grand_total, $user, $invoice->invoiceItem()->first()->product_name);
                }
                $this->doTheDeed($invoice);
                if (\Session::has('AgentAlterationRenew')) {
                    $newAgents = \Session::get('newAgentsRenew');
                    $orderId = \Session::get('orderIdRenew');
                    $installationPath = \Session::get('installation_pathRenew');
                    $productId = \Session::get('product_idRenew');
                    $oldLicense = \Session::get('oldLicenseRenew');
                    $cloud->doTheAgentAltering($newAgents, $oldLicense, $orderId, $installationPath, $productId);
                }
                $view = $cont->getViewMessageAfterRenew($invoice, $state, $currency);
            }

            return [
                'status' => $view['status'],
                'message' => $view['message'],
            ];
        } catch (\Exception $e) {
            return redirect('checkout')->with('fails', 'Your payment was declined. '.$e->getMessage().'. Please try with another card or gateway.');
        }
    }

    public function doTheDeed($invoice)
    {
        $amt_to_credit = Payment::where('user_id', \Auth::user()->id)->where('payment_status', 'success')->where('payment_method', 'Credit Balance')->value('amt_to_credit');
        if ($amt_to_credit) {
            $amt_to_credit = (int) $amt_to_credit - (int) $invoice->billing_pay;
            Payment::where('user_id', \Auth::user()->id)->where('payment_method', 'Credit Balance')->where('payment_status', 'success')->update(['amt_to_credit' => $amt_to_credit]);
            User::where('id', \Auth::user()->id)->update(['billing_pay_balance' => 0]);
            $payment_id = \DB::table('payments')->where('user_id', \Auth::user()->id)->where('payment_status', 'success')->where('payment_method', 'Credit Balance')->value('id');
            $formattedValue = currencyFormat($invoice->billing_pay, $invoice->currency, true);
            $messageAdmin = 'The payment balance of '.$formattedValue.' has been utilized or adjusted with this invoice.'.
                ' You can view the details of the invoice '.
                '<a href="'.config('app.url').'/invoices/show?invoiceid='.$invoice->id.'">'.$invoice->number.'</a>.';

            $messageClient = 'The payment balance of '.$formattedValue.' has been utilized or adjusted with this invoice.'.
                ' You can view the details of the invoice '.
                '<a href="'.config('app.url').'/my-invoice/'.$invoice->id.'">'.$invoice->number.'</a>.';

            \DB::table('credit_activity')->insert(['payment_id' => $payment_id, 'text' => $messageAdmin, 'role' => 'admin', 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()]);
            \DB::table('credit_activity')->insert(['payment_id' => $payment_id, 'text' => $messageClient, 'role' => 'user', 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()]);
            if ($invoice->billing_pay) {
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'user_id' => $invoice->user_id,
                    'amount' => $invoice->billing_pay,
                    'payment_method' => 'Credits',
                    'payment_status' => 'success',
                    'created_at' => Carbon::now(),
                ]);
            }
        }
    }

    public function getViewMessageAfterPayment($invoice, $state, $currency)
    {
        $orders = Order::where('invoice_id', $invoice->id)->get();
        $invoiceItems = InvoiceItem::where('invoice_id', $invoice->id)->get();
        \Cart::clear();
        $status = 'Success';
        $message = view('themes.default1.front.postPaymentTemplate', compact('invoice', 'orders',
            'invoiceItems', 'state', 'currency'))->render();

        return ['status' => $status, 'message' => $message];
    }

    public function getViewMessageAfterRenew($invoice, $state, $currency)
    {
        $order = OrderInvoiceRelation::where('invoice_id', $invoice->id)->value('order_id');
        $order_number = Order::where('id', $order)->value('number');
        $invoiceItem = InvoiceItem::where('invoice_id', $invoice->id)->first();
        $product = Product::where('name', $invoiceItem->product_name)->first();
        $date1 = new DateTime($invoiceItem->created_at);
        $date = $date1->format('M j, Y, g:i a ');
        \Cart::clear();
        $status = 'Success';
        $message = view('themes.default1.front.postRenewTemplate', compact('invoice', 'date',
            'product', 'invoiceItem', 'state', 'currency', 'order_number'))->render();

        return ['status' => $status, 'message' => $message];
    }

    public function calculateUnitCost($currency, $cost)
    {
        $decimalPlaces = [
            'BIF' => 0, 'CLP' => 0, 'DJF' => 0, 'GNF' => 0, 'JPY' => 0,
            'KMF' => 0, 'KRW' => 0, 'MGA' => 0, 'PYG' => 0, 'RWF' => 0,
            'UGX' => 0, 'VND' => 0, 'VUV' => 0, 'XAF' => 0, 'XOF' => 0,
            'XPF' => 0, 'BHD' => 3, 'JOD' => 3, 'KWD' => 3, 'OMR' => 3,
            'TND' => 3,
        ];

        $decimalPlacesForCurrency = $decimalPlaces[$currency] ?? 2;

        if ($decimalPlacesForCurrency === 0) {
            $unit_cost = round((int) $cost);
        } elseif ($decimalPlacesForCurrency === 3) {
            $unit_cost = round((int) $cost) * 1000;
        } else {
            $unit_cost = round((int) $cost) * 100;
        }

        return $unit_cost;
    }

    /**
     * Check and update the subscription price if necessary.
     *
     * @param  string  $orderId  The order ID associated with the subscription.
     * @param  object  $invoice  The invoice object for the subscription.
     * @return void
     */
    public function updateSubscriptionPriceIfNeeded($orderId, $invoice)
    {
        $subscription = Subscription::where('order_id', $orderId)->first();
        $order = Order::find($orderId);
        $product = Product::find($subscription->product_id);

        if (! $subscription) {
            return; // No subscription found
        }

        if ($subscription->is_subscribed != '1') {
            return; // Subscription not active
        }

        if ($subscription->rzp_subscription != '3' && $subscription->autoRenew_status != '3') {
            return; // Subscription not eligible for price check/update
        }

        $plan = Plan::find($subscription->plan_id);
        $days = intval(round((int) $plan->days / 30));
        $countryids = \App\Model\Common\Country::where('country_code_char2', \Auth::user()->country)->first();
        $price = PlanPrice::where('plan_id', $subscription->plan_id)->where('currency', $invoice->currency)->where('country_id', $countryids->country_id)->value('renew_price');
        if (empty($price)) {
            $price = PlanPrice::where('plan_id', $subscription->plan_id)->where('currency', $invoice->currency)->where('country_id', 0)->value('renew_price');
        }
        $amount = $this->getPriceforCloud($order, $price, $subscription->product_id, $invoice->currency, $subscription);
        $renewPrice = intval($this->calculateUnitCost($invoice->currency, $amount));

        if ($subscription->rzp_subscription == '3' && $subscription->subscribe_id) {
            $key_id = ApiKey::pluck('rzp_key')->first();
            $secret = ApiKey::pluck('rzp_secret')->first();
            $api = new Api($key_id, $secret);

            $fetchSub = $api->subscription->fetch($subscription->subscribe_id);
            $fetchPlan = $api->plan->fetch($fetchSub['plan_id']);

            if ($fetchPlan->item->amount == $renewPrice &&
                $fetchPlan->item->currency == $invoice->currency &&
                $fetchPlan->interval == $days) {
                return; // Subscription price already matches, no update needed
            }

            if ($fetchSub['status'] == 'active') {
                $updatePlan = $api->plan->create([
                    'period' => 'monthly',
                    'interval' => $days,
                    'item' => [
                        'name' => $product->name,
                        'amount' => $renewPrice,
                        'currency' => $invoice->currency,
                    ],
                ]);

                $updateSubscription = $api->subscription->fetch($subscription->subscribe_id)->update([
                    'plan_id' => $updatePlan['id'],
                    'quantity' => 1,
                    'remaining_count' => $fetchSub['remaining_count'],
                    'customer_notify' => 1,
                    'schedule_change_at' => 'cycle_end',
                ]);
            }
        } elseif ($subscription->autoRenew_status == '3' && $subscription->subscribe_id) {
            $stripeSecretKey = ApiKey::pluck('stripe_secret')->first();
            $stripe = new \Stripe\StripeClient($stripeSecretKey);
            \Stripe\Stripe::setApiKey($stripeSecretKey);

            $fetchSub = $stripe->subscriptions->retrieve($subscription->subscribe_id, []);

            if ($fetchSub->status == 'active') {
                if ($fetchSub->plan->amount == $renewPrice) {
                    return; // Subscription price already matches, no update needed
                } else {
                    $product = $stripe->products->create([
                        'name' => $product->name,
                    ]);
                    $product_id = $product['id'];

                    $price = $stripe->prices->create([
                        'unit_amount' => $renewPrice,
                        'currency' => $invoice->currency,
                        'recurring' => ['interval' => 'day', 'interval_count' => $plan->days],
                        'product' => $product_id,
                    ]);
                    $updateSub = $stripe->subscriptions->update(
                        $subscription->subscribe_id,
                        [
                            'items' => [
                                [
                                    'id' => $fetchSub->items->data[0]->id,
                                    'price' => $price['id'],
                                ],
                            ],
                            'proration_behavior' => 'none', // Disable proration
                        ]
                    );
                    if ($updateSub->status != 'active') {
                        $stripe->subscriptions->cancel($updateSub->id, []);
                        Subscription::where('id', $subscription->id)->update(['is_subscribed' => '0', 'autoRenew_status' => '0', 'subscribe_id' => null]);
                    }
                }
            }
        }
    }

    public function getPriceforCloud($order, $price, $product, $currency, $subscription)
    {
        $numberofAgents = (int) ltrim(substr($order->serial_key, -4), '0');
        $finalPrice = $numberofAgents * $price;
        $controller = new \App\Http\Controllers\Order\InvoiceController();
        $tax = $this->calculateTax($product, \Auth::user()->state, \Auth::user()->country);
        $tax_rate = $tax->getValue();
        $cost = rounding($controller->calculateTotal($tax_rate, $finalPrice));

        return $cost;
    }
}
