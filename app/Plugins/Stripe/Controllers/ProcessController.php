<?php

namespace App\Plugins\Stripe\Controllers;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Plugins\Razorpay\Model\RazorpayPayment;
use App\Plugins\Stripe\Model\StripePayment;
use App\Traits\Payment\PostPaymentHandle;
use Carbon\Carbon;
use Darryldecode\Cart\CartCondition;
use Illuminate\Http\Request;
use Razorpay\Api\Api;

class ProcessController extends Controller
{
    use PostPaymentHandle;

    protected $stripe;

    public function __construct()
    {
        $stripe = new StripePayment();
        $this->stripe = $stripe;

        $product = new Product();
        $this->product = $product;

        $invoiceItem = new InvoiceItem();
        $this->invoiceItem = $invoiceItem;

        $razorpay = new RazorpayPayment();
        $this->razorpay = $razorpay;
    }

//    public function PassToPayment($requests)
//    {
//        try {
//            $request = $requests['request'];
//            $invoice = $requests['invoice'];
//            $auto_renewal=$request['auto-renewal'] !=null?$request['auto-renewal']:0;
//            $cart = \Cart::getContent();
//            if (! $cart->count()) {
//                \Cart::clear();
//            } else {
//
//                $processingFee = $this->getProcessingFee($request->input('payment_gateway'), $invoice->currency);
//                $this->updateFinalPrice(new Request(['processing_fee' => $processingFee]));
//                $invoice->grand_total = \Cart::getTotal();
//            }
//            if ($request->input('payment_gateway') == 'Stripe') {
//                if (! \Schema::hasTable('stripe')) {
//                    throw new \Exception(__('message.stripe_not_configured'));
//                }
//                $stripe = $this->stripe->where('id', 1)->first();
//                if (! $stripe) {
//                    throw new \Exception(__('message.stripe_fields_not_given'));
//                }
//                \Session::put('invoice', $invoice);
//                \Session::save();
//                $url='';
//                if($request->input('auto-renewal')) {
//                    \Session::put('auto-renewal', true);
//                    $url = $this->subscriptionData($invoice);
//                }
//                $this->middlePage($request->input('payment_gateway'),['auto_renewal'=>$auto_renewal,'url'=>$url]);
//            } elseif ($request->input('payment_gateway') == 'Razorpay') {
//                if (! \Schema::hasTable('razorpay')) {
//                    throw new \Exception(__('message.razorpay_not_configured'));
//                }
//                $stripe = $this->razorpay->where('id', 1)->first();
//                if (! $stripe) {
//                    throw new \Exception(__('message.razorpay_fields_not_given'));
//                }
//                \Session::put('invoice', $invoice);
//                if($request->input('auto-renewal')) {
//                    \Session::put('auto-renewal', true);
//                }
//                \Session::save();
//                $regularPayment = \Cart::getTotal() ? true : false;
//                $json = $this->processRazorpayOrder($invoice, $regularPayment,$request->input('auto-renewal'));
//                $this->middlePage($request->input('payment_gateway'), ['json' => $json,'auto_renewal'=>0,'url'=>'']);
//            }
//        } catch (\Exception $ex) {
//            throw new \Exception($ex->getMessage(), $ex->getCode(), $ex->getPrevious());
//        }
//    }

//    public function subscriptionData($invoice){
//        $invoiceItem=InvoiceItem::where('invoice_id',$invoice->id)->first();
//        $product_name=$invoiceItem->product_name;
//        $cost = $invoice->grand_total;
//        $currency = $invoice->currency;
//        $plan=Plan::where('id',$invoiceItem->plan_id)->first();
//        $unit_cost = $this->calculateUnitCost($currency, $cost);
//        $stripeSecretKey = ApiKey::pluck('stripe_secret')->first();
//
//        $stripe = new \Stripe\StripeClient($stripeSecretKey);
//        \Stripe\Stripe::setApiKey($stripeSecretKey);
//
//        $user=\Auth::user();
//        $customer = \Stripe\Customer::create([
//            'name' => $user->first_name.' '.$user->last_name,
//            'email' => $user->email,
//            'address' => [
//                'line1' => optional($user)->address,
//                'postal_code' => optional($user)->zip,
//                'city' => optional($user)->town,
//                'state' => optional($user)->state,
//                'country' => optional($user)->country,
//            ],
//        ]);
//        $customer_id = $customer['id'];
//        //create product
//        $product = $stripe->products->create([
//            'name' => $product_name,
//        ]);
//        $product_id = $product['id'];
//        $days=Carbon::now()->addDays($plan->days);
//        //define product price and recurring interval
//
//        $price = $stripe->prices->create([
//            'unit_amount' => $unit_cost,
//            'currency' => $currency,
//            'recurring' => ['interval' => 'day', 'interval_count' => $plan->days],
//            'product' => $product_id,
//        ]);
//
//
//
//        $price_id = $price['id'];
//        $url = url('confirm/auto-renewal');
//        $start_date=strtotime(Carbon::now());
//        $session = \Stripe\Checkout\Session::create([
//            'mode' => 'subscription',
//            'customer' => $customer_id,
//            'line_items' => [
//                [
//                    'price' => $price_id,
//                    'quantity' => 1,
//                ]],
//            'success_url' => $url. '?session_id={CHECKOUT_SESSION_ID}',
    ////                'cancel_url' => route('checkout.cancel'),
//        ]);
//        return $session->url;
//    }

//    public function middlePage($gateway, $data = [])
//    {
//        try {
//            $rzp_key = ApiKey::where('id', 1)->value('rzp_key');
//            $rzp_secret = ApiKey::where('id', 1)->value('rzp_secret');
//            $stripe_key = ApiKey::where('id', 1)->value('stripe_key');
//            $apilayer_key = ApiKey::where('id', 1)->value('apilayer_key');
//            $path = app_path().'/Plugins/Stripe/views';
//            $total = intval(\Cart::getTotal());
//            $payment_method = \Session::get('payment_method');
//            $regularPayment = true;
//            $invoice = \Session::get('invoice');
//            if (! $total) {
//                $paid = 0;
//                // $total = \Session::get('totalToBePaid');
//                $regularPayment = false;
//                $items = $invoice->invoiceItem()->get();
//                $product = $this->product($invoice->id);
//                $processingFee = $this->getProcessingFee($payment_method, $invoice->currency);
//                $invoice->processing_fee = $processingFee;
//                $invoice->grand_total = intval($invoice->grand_total * (1 + $processingFee / 100));
//                $amount = rounding($invoice->grand_total);
//                $creditBalance = $invoice->billing_pay;
//                if (empty($creditBalance)) {
//                    $creditBalance = 0;
//                }
//                if (count($invoice->payment()->get())) {//If partial payment is made
//                    $paid = array_sum($invoice->payment()->pluck('amount')->toArray());
//                    $amount = rounding($invoice->grand_total - $paid);
//                }
//                \Session::put('totalToBePaid', $amount);
//                \View::addNamespace('plugins', $path);
//                echo view('plugins::middle-page', compact('total', 'invoice', 'regularPayment', 'items', 'product', 'amount', 'paid', 'creditBalance', 'gateway', 'rzp_key', 'rzp_secret', 'apilayer_key', 'stripe_key', 'data','processingFee'));
//            } else {
//                $pay = $this->payment($payment_method, $status = 'pending');
//                $payment_method = $pay['payment'];
//                $invoice_no = $invoice->number;
//                $status = $pay['status'];
//                $processingFee = $this->getProcessingFee($payment_method, $invoice->currency);
//    //                $this->updateFinalPrice(new Request(['processing_fee' => $processingFee]));
//                $amount = rounding(\Cart::getTotal());
//                \View::addNamespace('plugins', $path);
//
//                echo view('plugins::middle-page', compact('invoice', 'amount', 'invoice_no', 'payment_method', 'invoice', 'regularPayment', 'gateway', 'rzp_key', 'rzp_secret', 'apilayer_key', 'stripe_key', 'data','processingFee'))->render();
//            }
//        } catch (\Exception $ex) {
//            throw new \Exception($ex->getMessage());
//        }
//    }

    public static function updateFinalPrice(Request $request)
    {
        $value = '0%';
        if ($request->input('processing_fee')) {
            $value = $request->input('processing_fee').'%';
        }

        $updateValue = new CartCondition([
            'name' => 'Processing fee',
            'type' => 'fee',
            'target' => 'total',
            'value' => $value,
        ]);
        \Cart::condition($updateValue);
    }

    public function payment($payment_method, $status)
    {
        if (! $payment_method) {
            $payment_method = '';
            $status = 'success';
        }

        return ['payment' => $payment_method, 'status' => $status];
    }

    public function product($invoiceid)
    {
        try {
            $invoice = $this->invoiceItem->where('invoice_id', $invoiceid)->first();
            $name = $invoice->product_name;
            $product = $this->product->where('name', $name)->first();

            return $product;
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            throw new \Exception($ex->getMessage());
        }
    }

    private function getProcessingFee($paymentMethod, $currency)
    {
        try {
            if ($paymentMethod) {
                return $paymentMethod == 'razorpay' ? ApiKey::find(1)->value('razorpay_processing_fee') : ApiKey::find(1)->value('stripe_processing_fee');
            }
        } catch (\Exception $e) {
            throw new \Exception(__('message.invalid_modification'));
        }
    }

    public function response(Request $request)
    {
        $id = '';
        $url = 'checkout';
        if (\Session::has('invoiceid')) {
            $invoiceid = \Session::get('invoiceid');
            $url = 'paynow/'.$id;
        }
        // if (\Cart::getContent()->count() > 0) {
        //     \Cart::clear();
        // }
        if ($invoiceid) {
            $control = new \App\Http\Controllers\Order\RenewController();
            $invoice = new \App\Model\Order\Invoice();
            $invoice = $invoice->findOrFail($invoiceid);
            if ($control->checkRenew($invoice->is_renewed) === false) {
                $checkout_controller = new \App\Http\Controllers\Front\CheckoutController();
                $state = \Auth::user()->state;
                $currency = \Auth::user()->currency_symbol;
                $checkout_controller->checkoutAction($invoice);
                $cont = new \App\Http\Controllers\RazorpayController();
                $view = $cont->getViewMessageAfterPayment($invoice, $state, $currency);
                $status = $view['status'];
                $message = $view['message'];
                \Session::forget('items');
                \Session::forget('code');
                \Session::forget('codevalue');
            } else {
                $control->/* @scrutinizer ignore-call */
                successRenew($invoice);
                $payment = new \App\Http\Controllers\Order\InvoiceController();
                $payment->postRazorpayPayment($invoice->id, $invoice->grand_total);
                $state = \Auth::user()->state;
                $currency = \Auth::user()->currency_symbol;
                $cont = new \App\Http\Controllers\RazorpayController();
                $view = $cont->getViewMessageAfterRenew($invoice, $state, $currency);
                $status = $view['status'];
                $message = $view['message'];
            }

            return redirect()->back()->with($status, $message);
            \Cart::clear();
        }
    }

    public function cancel(Request $request)
    {
        $url = 'checkout';
        if (\Session::has('invoiceid')) {
            $id = \Session::get('invoiceid');
            $url = 'paynow/'.$id;
        }
        \Session::forget('invoiceid');

        return redirect($url)->with('fails', __('message.order_transaction_declined'));
    }

//    protected function processRazorpayOrder($invoice, $regularPayment,$auto_renewal)
//    {
//        try {
//            $apiKey = ApiKey::first();
//            $rzp_key = $apiKey->rzp_key;
//            $rzp_secret = $apiKey->rzp_secret;
//
//            $user = auth()->user();
//
//            $merchant_orderid = $this->generateMerchantRandomString();
//
//            $cartTotal = $invoice->grand_total;
//
//            // Handle credit balance if applicable
//            if ($user->billing_pay_balance && $regularPayment) {
//                $amt_to_credit = \DB::table('payments')
//                    ->where('user_id', $user->id)
//                    ->where('payment_method', 'Credit Balance')
//                    ->where('payment_status', 'success')
//                    ->where('amt_to_credit', '!=', 0)
//                    ->value('amt_to_credit');
//
//                if ($invoice->grand_total <= $amt_to_credit) {
//                    $cartTotal = 0;
//                } else {
//                    $cartTotal = $invoice->grand_total - $amt_to_credit;
//                }
//            }
//
//            $cartTotal = intval($cartTotal);
//
//            $api = new Api($rzp_key, $rzp_secret);
//
//
//         if($auto_renewal){
//             $invoice_item = InvoiceItem::where('invoice_id', $invoice->id)->first();
//             $product_details = Product::where('id', $invoice_item->product_id)->first();
//             $cost = $invoice->grand_total;
//             $currency = $invoice->currency;
//             $plan = Plan::where('id', $invoice_item->plan_id)->first();
//             $unit_cost = $this->calculateUnitCost($currency, $cost);
//
//             $key_id = ApiKey::pluck('rzp_key')->first();
//             $secret = ApiKey::pluck('rzp_secret')->first();
//             $api = new Api($key_id, $secret);
//             $user = \Auth::user();
//             $customer = $api->customer->create([
//                 'name' => $user->first_name . ' ' . $user->last_name,
//                 'email' => $user->email,
//                 'contact' => $user->mobile_code . $user->mobile,
//                 'fail_existing' => 0,
//             ]);
//
//            $period=($plan->days<365)?'monthly':'yearly';
//             $rzp_plan = $api->plan->create(['period' => $period,
//                     'interval' => 1,
//                     'item' => [
//                         'name' => $product_details->name,
//                         'amount' => $unit_cost,
//                         'currency' => $currency,],
//
//                 ]
//             );
//             $start_date = Carbon::now();
//             $end_date = $start_date->addDays($plan->days);
//             $rzp_subscriptionLink = $api->subscription->create([
//                 'plan_id' => $rzp_plan['id'],
//                 'customer_id' => $customer['id'],
//                 'total_count'=>5,
//                 'quantity' => 1,
    ////                 'end_at' => Carbon::parse($end_date)->timestamp,
//                 'start_at' => Carbon::parse($start_date)->timestamp,
//                 'customer_notify' => 1,
//                 'addons' => [['item' => [
//                     'name' => $product_details->name,
//                     'amount' => $unit_cost,
//                     'currency' => $currency]]],
//             ]);
//             $data = [
//                 'key' => $key_id,
//                 'name' => 'Faveo Helpdesk',
//                 'currency' => 'INR',
//                 'prefill' => [
//                     'contact' => $user->mobile_code . $user->mobile,
//                     'email' => $user->email,
//                 ],
//                 'description' => 'Order for Invoice No' . -$invoice->number,
//
//                 'subscription_id' => $rzp_subscriptionLink['id'],
//                 'method' => 'card',
//                 'recurring' => true,
//                 'theme' => [
//                     'color' => '#F37254',
//                 ],
//             ];
//         }else{
//             $orderData = [
//                'receipt' => '3456',
//                'amount' => round($cartTotal * 100),
//                'currency' => $invoice->currency,
//                'payment_capture' => 0,
//            ];
//
//            $razorpayOrder = $api->order->create($orderData);
//            $razorpayOrderId = $razorpayOrder['id'];
//
//            $data = [
//                'key' => $rzp_key,
//                'name' => 'Faveo Helpdesk',
//                'order_id' => $razorpayOrderId,
//                'description' => 'Order for Invoice No - '.$invoice->number,
//                'prefill' => [
//                    'contact' => $user->mobile_code.$user->mobile,
//                    'email' => $user->email,
//                ],
//                'notes' => [
//                    'First Name' => $user->first_name,
//                    'Last Name' => $user->last_name,
//                    'Company Name' => $user->company,
//                    'Address' => $user->address,
//                    'Email' => $user->email,
//                    'Country' => $user->country,
//                    'State' => $user->state,
//                    'City' => $user->town,
//                    'Zip' => $user->zip,
//                    'Amount Paid' => $cartTotal * 100,
//                    'merchant_order_id' => $merchant_orderid,
//                ],
//                'theme' => [
//                    'color' => '#F37254',
//                ],
//            ];
//         }
//            return json_encode($data);
//        } catch (\Exception $ex) {
//            throw new \Exception($ex->getMessage(), $ex->getCode(), $ex->getPrevious());
//        }
//    }
//
//    protected function generateMerchantRandomString($length = 10)
//    {
//        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
//        $charactersLength = strlen($characters);
//        $randomString = '';
//        for ($i = 0; $i < $length; $i++) {
//            $randomString .= $characters[rand(0, $charactersLength - 1)];
//        }
//
//        return $randomString;
//    }
}
