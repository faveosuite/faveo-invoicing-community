<?php

namespace App\Plugins\Payment_module\Stripe\Controllers;

use App\ApiKey;
use App\Auto_renewal;
use App\Http\Controllers\Common\CronController;
use App\Http\Controllers\ConcretePostSubscriptionHandleController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Order\BaseRenewController;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Traits\Payment\PostPaymentHandle;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Event as StripeEvent;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class RecurringController extends Controller
{
    use PostPaymentHandle;
    protected int $i = 0;

    public function subscriptionData($invoice)
    {
        $invoiceItem = InvoiceItem::where('invoice_id', $invoice->id)->first();
        $product_name = $invoiceItem->product_name;
        $cost = $invoice->grand_total;
        $currency = $invoice->currency;
        $plan = Plan::where('id', $invoiceItem->plan_id)->first();
        $planDetails = userCurrencyAndPrice(\Auth::user()->id, $plan);
        $renew_cost = $planDetails['plan']->renew_price;
        $renew_unit_cost = $this->calculateUnitCost($currency, $renew_cost);
        $unit_cost = $this->calculateUnitCost($currency, $cost);
        $stripeSecretKey = ApiKey::pluck('stripe_secret')->first();

        $stripe = new \Stripe\StripeClient($stripeSecretKey);
        \Stripe\Stripe::setApiKey($stripeSecretKey);

        $user = \Auth::user();
        $customer = $this->customerCreation($user);
        $customer_id = $customer['id'];
        //create product
        $product = $stripe->products->create([
            'name' => $product_name,
        ]);
        $product_id = $product['id'];

        $price = $this->stripePriceCreation($renew_unit_cost, $currency, $product_id, $stripe, $plan->days, 'recurring');
        $first_price = $this->stripePriceCreation($unit_cost, $currency, $product_id, $stripe, null, null);
        $price_id = $price['id'];
        $url = url('confirm/auto-renewal');
        $trialEnd = strtotime(Carbon::now()->addDays($plan->days));
        $session = $this->stripeSessionCreation($customer_id, $price_id, $first_price['id'], $trialEnd, $url);

        return $session->url;
    }

    public function customerCreation($user)
    {
        return \Stripe\Customer::create([
            'name' => $user->first_name.' '.$user->last_name,
            'email' => $user->email,
            'address' => [
                'line1' => optional($user)->address,
                'postal_code' => optional($user)->zip,
                'city' => optional($user)->town,
                'state' => optional($user)->state,
                'country' => optional($user)->country,
            ],
        ]);
    }

    public function stripePriceCreation($cost, $currency, $product_id, $stripe, $days = null, $type = null)
    {
        if ($type == 'recurring') {
            $price = $stripe->prices->create([
                'unit_amount' => $cost,
                'currency' => $currency,
                'recurring' => ['interval' => 'day', 'interval_count' => $days],
                'product' => $product_id,
            ]);
        } elseif ($type == 'metered') {
            $price = $stripe->prices->create([
                'unit_amount' => $cost,  //keep it 100 not to change it
                'currency' => $currency,
                'recurring' => ['interval' => 'day',
                    'usage_type' => 'metered',
                    'interval_count' => $days,
                    'meter' => 'mtr_test_61TEmuH4eqA9iUXzn41SGb8vHOmu29XU',  //make it dynamic
                ],
                'product' => $product_id,
                'billing_scheme' => 'per_unit',
            ]);
        } else {
            $price = $stripe->prices->create([
                'unit_amount' => $cost,
                'currency' => $currency,
                'product' => $product_id,
            ]);
        }

        return $price;
    }

    public function stripeSessionCreation($customer_id, $price_id1, $price_id2, $trialEnd, $url)
    {
        $session = \Stripe\Checkout\Session::create([
            'mode' => 'subscription',
            'customer' => $customer_id,
            'line_items' => [
                [
                    'price' => $price_id1,
                    'quantity' => 1,
                ],
                [
                    'price' => $price_id2,
                    'quantity' => 1,
                ],
            ],
            'subscription_data' => [
                'trial_end' => $trialEnd,
            ],
            'success_url' => $url.'?session_id={CHECKOUT_SESSION_ID}',
        ]);

        return $session;
    }

    public function usageBasedSubscriptionData($invoice)
    {
        $invoiceItem = InvoiceItem::where('invoice_id', $invoice->id)->first();
        $product_name = $invoiceItem->product_name;
        $cost = $invoice->grand_total;
        $currency = $invoice->currency;
        $plan = Plan::where('id', $invoiceItem->plan_id)->first();
        $planDetails = userCurrencyAndPrice(\Auth::user()->id, $plan);
        $renew_cost = $planDetails['plan']->renew_price;
        $renew_unit_cost = $this->calculateUnitCost($currency, $renew_cost);
        $unit_cost = $this->calculateUnitCost($currency, $cost);
        $stripeSecretKey = ApiKey::pluck('stripe_secret')->first();

        $stripe = new \Stripe\StripeClient($stripeSecretKey);
        \Stripe\Stripe::setApiKey($stripeSecretKey);

        $user = \Auth::user();
        $product = $stripe->products->create([
            'name' => $product_name,
        ]);
        $product_id = $product['id'];

        $first_price = $this->stripePriceCreation($unit_cost, $currency, $product_id, $stripe, null, null);

        $metered_cost = 100;
        $price = $this->stripePriceCreation($metered_cost, $currency, $product_id, $stripe, $plan->days, 'metered');

        $customer = $this->customerCreation($user);

        $price_id = $price['id'];
        $url = url('confirm/auto-renewal');
        $customer_id = $customer['id'];

        $trialEnd = strtotime(Carbon::now()->addDays($plan->days));

        $session = \Stripe\Checkout\Session::create([
            'mode' => 'subscription',
            'customer' => $customer_id,
            'line_items' => [
                [
                    'price' => $first_price['id'],
                    'quantity' => 1,
                ],
                [
                    'price' => $price_id,
                ],
            ],
            'subscription_data' => [
                'metadata' => ['module' => 'api_usage'],

            ],
            'success_url' => $url.'?session_id={CHECKOUT_SESSION_ID}',
        ]);

        return $session->url;
    }

    public function confirmAutoRenewal(Request $request)
    {
        $sessionId = $request->query('session_id');
        if (! $sessionId) {
            return redirect('/')->withErrors('No session ID provided.');
        }

        $stripeSecretKey = ApiKey::pluck('stripe_secret')->first();

        $stripe = new \Stripe\StripeClient($stripeSecretKey);
        \Stripe\Stripe::setApiKey($stripeSecretKey);
        $session = StripeSession::retrieve($sessionId);
        $subscriptionId = $session->subscription;

        // Optionally retrieve subscription
        $subscription = \Stripe\Subscription::retrieve($subscriptionId);
        $currency = getCurrencyForClient(\Auth::user()->country);
        $amount = currencyFormat(1, $currency);

        $stripeSecretKey = ApiKey::pluck('stripe_secret')->first();
        $stripe = new \Stripe\StripeClient($stripeSecretKey);
        $invoice = \Session::get('invoice');
        $order = \Session::get('order');

        try {
            if ($invoice) {
                $currency = $invoice->currency;
                $order = \Session::get('upgradeNewActiveOrder');
                $result = $this->processPaymentSuccess($invoice, $currency);
            }

            $customer_details = [
                'user_id' => \Auth::user()->id,
                'customer_id' => $subscription->customer,
                'payment_method' => 'stripe',
                'order_id' => $order,
                'payment_intent_id' => $subscription->default_payment_method,
            ];
            Auto_renewal::create($customer_details);
            Subscription::where('order_id', $order)->update(['is_subscribed' => '1', 'autoRenew_status' => '3', 'subscribe_id' => $subscription->id, 'credit_refund' => 1]);
            $mail = new \App\Http\Controllers\Common\PhpMailController();

            $mail->payment_log(\Auth::user()->email, 'stripe', 'success', Order::where('id', $order)->value('number'), null, $amount, 'Payment method updated');

            if ($invoice) {
                \Session::forget('upgradeNewActiveOrder');
                \Session::forget('i');
                \Session::forget('invoice');
                \Session::forget('auto-renewal');

                return redirect('checkout')->with($result['status'], $result['message']);
            }

            return redirect('my-order/'.$order.'#auto-renew')->with('success', __('message.card_details_updated_successfully'));
        } catch (\Exception $e) {
            return redirect('my-order/'.$order.'#auto-renew')->with('fails', 'Your Payment was declined. Please try with another card or gateway');
        }
    }

    /**
     * Create new Auto renewal and update auto-renewal status.
     *
     * @param  Request  $request
     * @return array{type:string,message:string}|JsonResponse
     */
    public function enableAutorenewalStatus(Request $request)
    {
        try {
            $orderid = $request->get('order_id');
            \Session::put('order', $orderid);
            $order = Order::where('id', $orderid)->first();
            $product_details = Product::where('id', $order->product)->first();
            $invoice = Invoice::where('id', $order->invoice_id)->first();
            $cost = $invoice->grand_total;
            $currency = $invoice->currency;
            $subscription = Subscription::where('order_id', $order->id)->first();
            $plan = Plan::where('id', $subscription->plan_id)->first();
            $unit_cost = $this->calculateUnitCost($currency, $cost);
            $stripeSecretKey = ApiKey::pluck('stripe_secret')->first();

//            $stripe = new \Stripe\StripeClient($stripeSecretKey);
            $stripe = new \Stripe\StripeClient([
                'api_key' => $stripeSecretKey,
                'stripe_version' => '2020-08-27',
            ]);
            \Stripe\Stripe::setApiKey($stripeSecretKey);

            $user = \Auth::user();
            $customer = $this->customerCreation($user);

            $customer_id = $customer['id'];
            //create product
            $product = $stripe->products->create([
                'name' => $product_details->name,
            ]);
            $product_id = $product['id'];
            $metered_cost = 100;
            $price = $this->stripePriceCreation($metered_cost, $currency, $product_id, $stripe, $plan->days, 'metered');

            $price_id = $price['id'];
            $url = url('confirm/auto-renewal');

            $trialEnd = strtotime($subscription->ends_at);

//            $session = \Stripe\Checkout\Session::create([
//                'mode' => 'setup',
//                'customer' => $customer_id,
//                'payment_method_types' => ['card'],
            ////                'setup_intent_data' => [
            ////                    'usage' => 'off_session', // allows Stripe to use this method for future auto-renewals
            ////                ],
//                'success_url' => $url . '?session_id={CHECKOUT_SESSION_ID}',
//            ]);
            $session = \Stripe\Checkout\Session::create([
                'mode' => 'subscription',
                'customer' => $customer_id,
                'line_items' => [
                    [
                        'price' => $price_id,
                    ],
                ],
                'subscription_data' => [
                    'metadata' => ['module' => 'api_usage'],
                    'billing_cycle_anchor' => $trialEnd,
                ],
                'success_url' => $url.'?session_id={CHECKOUT_SESSION_ID}',
            ]);

            return response()->json(['url' => $session->url]);
        } catch(\Exception $ex) {
            $result = $ex->getMessage();
            $mail = new \App\Http\Controllers\Common\PhpMailController();
            $mail->payment_log(\Auth::user()->email, 'stripe', 'failed', Order::where('id', $orderid)->value('number'), $result, $amount, 'Payment method updated');
            $errorMessage = __('message.something_different_payment');

            return response()->json(['error' => $errorMessage], 500);
        }
    }

    public function stripe_webhook(Request $request)
    {
        $stripeSecretKey = ApiKey::pluck('stripe_secret')->first();
        Stripe::setApiKey($stripeSecretKey);

        $endpoint_secret = 'whsec_Wo1oOd8wChJA96dIoNWCw5PeRrxF1kfj';

        $payload = $request->getContent();
        $sig_header = $request->server('HTTP_STRIPE_SIGNATURE');
        $event = null;

        try {
            if ($endpoint_secret) {
                // Verify signature
                $event = Webhook::constructEvent(
                    $payload,
                    $sig_header,
                    $endpoint_secret
                );
            } else {
                // Fallback (no verification, not recommended in production)
                $event = StripeEvent::constructFrom(
                    json_decode($payload, true)
                );
            }
        } catch (SignatureVerificationException $e) {
            \Log::error('Stripe Webhook signature verification failed', ['error' => $e->getMessage()]);

            return response('Webhook signature verification failed', 400);
        } catch (\UnexpectedValueException $e) {
            \Log::error('Stripe Webhook error while parsing request', ['error' => $e->getMessage()]);

            return response('Webhook parsing failed', 400);
        }
        // Handle the event
        switch ($event->type) {
            case 'invoice.payment_succeeded':
                $invoice = $event->data->object;
                if ($invoice->subscription->id) {
                    $subscription = Subscription::where('subscribe_id', $invoice->subscription->id)->first();
                    $this->invoice_success($subscription, $invoice);
                }
                break;

            case 'invoice.payment_failed':
                $invoice = $event->data->object;
                $subscription = $invoice->subscription;
                \Log::debug('Full Invoice subscription', ['subscription' => $subscription]);
                break;

            default:
                \Log::warning('Received unknown Stripe event type', ['type' => $event->type]);
        }

        return response('Webhook handled', 200);
    }

    public function invoice_success($subscription)
    {
        $cronController = new CronController();
        $concreteController = app()->make(ConcretePostSubscriptionHandleController::class);

        // Pass the concrete controller instance to CronController constructor
        $controller = new \App\Plugins\Payment_module\SubscriptionController($concreteController);
        $order = $cronController->getOrderById($subscription->order_id);
        $oldinvoice = $cronController->getInvoiceByOrderId($subscription->order_id);
        $item = $cronController->getInvoiceItemByInvoiceId($oldinvoice->id);
        $userid = $subscription->user_id;
        $product_details = Product::where('id', $subscription->product_id)->first();
        $payment_method = $subscription->autoRenew_status != '0' ? 'stripe' : ($subscription->rzp_subscription != '0' ? 'razorpay' : null);
        $user = \DB::table('users')->where('id', $userid)->first();
        $countryids = \App\Model\Common\Country::where('country_code_char2', $user->country)->first();
        $currency = getCurrencyForClient($user->country);
        $country = $countryids->country_id;
        $priceRow = PlanPrice::where('plan_id', $subscription->plan_id)
            ->where('currency', $currency)
            ->whereIn('country_id', [$country, 0])
            ->orderByRaw('FIELD(country_id, ?, 0)', [$country])
            ->first();

        $price = $priceRow->renew_price ?? 0;
        if (in_array($subscription->product_id, cloudPopupProducts()) || $product_details->can_modify_agent) {
            $noOfAgents = $priceRow->no_of_agents;
            if ($noOfAgents > 0) {
                $priceForAgents = $price / $noOfAgents;
            } else {
                $priceForAgents = $price;
            }
            $cost = $controller->getPriceforCloud($order, $priceForAgents);
        } else {
            $cost = $price;
        }

        // add processing fee for stripe payment
        if ($payment_method == 'stripe') {
            $processingFee = ApiKey::where('id', 1)->value('stripe_processing_fee');
            $processingFee = (float) $processingFee / 100;
            $price = $cost + ($cost * $processingFee);
        }
        $renewController = new BaseRenewController();
        $oldcurrency = getCurrencyForClient($user->country);
        $invoice = $renewController->generateInvoice($product_details, $user, $order->id, $subscription->plan_id, $cost, $code = '', $item->agents, $oldcurrency);
        $cost = Invoice::where('id', $invoice->invoice_id)->value('grand_total');
        $controller->processStripeSubscription($subscription, $currency, $cost, $user, $order, $product_details,$invoice);
    }
}
