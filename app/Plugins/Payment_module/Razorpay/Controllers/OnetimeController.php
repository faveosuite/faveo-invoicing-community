<?php

namespace App\Plugins\Payment_module\Razorpay\Controllers;


use App\ApiKey;
use App\Auto_renewal;
use App\Http\Controllers\Controller;
use App\Model\Common\State;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Payment\TaxByState;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Plugins\Stripe\Controllers\SettingsController;
use App\Traits\Payment\PostPaymentHandle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Razorpay\Api\Api;


class OnetimeController extends Controller{

    use postPaymentHandle;
    public function processRazorpayOrder($invoice)
    {
        try {
                $invoice_item = InvoiceItem::where('invoice_id', $invoice->id)->first();
                $product_details = Product::where('id', $invoice_item->product_id)->first();
                $cost = $invoice->grand_total;
                $currency = $invoice->currency;
                $plan = Plan::where('id', $invoice_item->plan_id)->first();
            $planDetails = userCurrencyAndPrice(\Auth::user()->id, $plan);
            $renew_cost = $planDetails['plan']->renew_price;
            $renew_unit_cost=$this->calculateUnitCost($currency,$renew_cost);
                $unit_cost = $this->calculateUnitCost($currency, $cost);

                $key_id = ApiKey::pluck('rzp_key')->first();
                $secret = ApiKey::pluck('rzp_secret')->first();
                $api = new Api($key_id, $secret);
                $user = \Auth::user();

                $customer=$this->razorpayCustomerCreation($api,$user);
                $period=($plan->days<=365)?'monthly':'yearly';
                $interval=1;
                if($period == 'monthly'){
                    $interval=intval(ceil($plan->days/30));
                }else{
                    $interval=intval(ceil($plan->days/365));
                }

            $rzp_plan=$this->razorpayPlanCreation($api,$period,$interval,$product_details->name,$renew_unit_cost,$currency);
            $start_date = Carbon::now();
                $end_date = $start_date->addDays($plan->days);
            $rzp_subscriptionLink = $this->razorpaySubscriptionCreation($api,$rzp_plan['id'],$customer['id'],$start_date,$product_details->name,$unit_cost,$currency);

        $data= $this->razorpayData($key_id,$user,$invoice->number,$rzp_subscriptionLink['id']);
            return json_encode($data);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode(), $ex->getPrevious());
        }
    }

    public function razorpayPlanCreation($api,$period,$interval,$product_name,$cost,$currency){
        return $api->plan->create(['period' => $period,
                'interval' => $interval,
                'item' => [
                    'name' => $product_name,
                    'amount' => $cost,
                    'currency' => $currency,],

            ]
        );

    }

    public function razorpayCustomerCreation($api,$user){
        return $api->customer->create([
            'name' => $user->first_name . ' ' . $user->last_name,
            'email' => $user->email,
            'contact' => $user->mobile_code . $user->mobile,
            'fail_existing' => 0,
        ]);
    }

    public function razorpaySubscriptionCreation($api,$rzp_plan_id,$customer_id,$start_date,$product_name,$cost,$currency){

        return $api->subscription->create([
            'plan_id' => $rzp_plan_id,
            'customer_id' => $customer_id,
            'total_count'=>5,
            'quantity' => 1,
            'start_at' => Carbon::parse($start_date)->timestamp,
            'customer_notify' => 1,
            'addons' => [['item' => [
                'name' => $product_name,
                'amount' => $cost,
                'currency' => $currency]]],
        ]);
    }


    public function razorpayData($key_id,$user,$invoice_number,$rzp_subscription_id){
        return [
            'key' => $key_id,
            'name' => 'Faveo Helpdesk',
            'currency' => 'INR',
            'prefill' => [
                'contact' => $user->mobile_code . $user->mobile,
                'email' => $user->email,
            ],
            'description' => 'Order for Invoice No' . -$invoice_number,

            'subscription_id' => $rzp_subscription_id,
            'method' => 'card',
            'recurring' => true,
            'theme' => [
                'color' => '#F37254',
            ],
        ];
    }

    public function payment($invoice, Request $request)
    {
        $userId = Invoice::find($invoice)->user_id;
        if (\Auth::user()->role != 'admin' && $userId != \Auth::user()->id) {
            return errorResponse('Payment cannot be initiated. Invalid modification of data');
        }
        //Input items of form
        $input = $request->all();
        $error = 'Payment Failed';
        $rzp_key = ApiKey::where('id', 1)->value('rzp_key');
        $rzp_secret = ApiKey::where('id', 1)->value('rzp_secret');
        $invoice = Invoice::where('id', $invoice)->first();
        if (count($input) && ! empty($input['razorpay_payment_id'])) { //Verify Razorpay Payment Id and Signature
            //Fetch payment information by razorpay_payment_id
            try {
                $api = new Api($rzp_key, $rzp_secret);
                $payment = $api->payment->fetch($input['razorpay_payment_id']);
//                $response = $api->payment->fetch($input['razorpay_payment_id']);
//                $capture = $api->payment->fetch($response->id)->capture(['amount' => $response->amount]);

                $stateCode = \Auth::user()->state;
                $state = $this->getState($stateCode);
//                $currency = $this->getCurrency();
                $currency =$invoice->currency;

                $result = $this->processPaymentSuccess($invoice, $currency);
                $orderId=\Session::get('upgradeNewActiveOrder');
                if(\Session::has('auto-renewal')) {
                    $customer_details = [
                        'user_id' => \Auth::user()->id,
                        'customer_id' => $payment['customer_id'],
                        'payment_method' => 'razorpay',
                        'order_id' => $orderId,
                    ];
                    Auto_renewal::create($customer_details);
                    subscription::where('order_id', $orderId)->update(['is_subscribed' => '1', 'rzp_subscription' => '3', 'subscribe_id'=>$input['razorpay_subscription_id'],'credit_refund'=>1]);
                    $amount = currencyFormat('1', $currency);
                    $mail = new \App\Http\Controllers\Common\PhpMailController();
                    $mail->payment_log(\Auth::user()->email, 'Razorpay', 'success', Order::where('id', $orderId)->value('number'), null, $amount, 'Payment method updated');
                }
                \Cart::removeCartCondition('Processing fee');
                \Session::forget(['items', 'code', 'codevalue', 'totalToBePaid', 'invoice', 'cart_currency','auto-renewal']);

                return redirect('checkout')->with($result['status'], $result['message']);
            } catch (\Razorpay\Api\Errors\SignatureVerificationError|\Razorpay\Api\Errors\BadRequestError|\Razorpay\Api\Errors\GatewayError|\Razorpay\Api\Errors\ServerError $e) {
                SettingsController::sendFailedPaymenttoAdmin($invoice, $invoice->grand_total, $invoice->invoiceItem()->first()->product_name, $e->getMessage(), \Auth::user());

                return redirect('checkout')->with('fails', 'Your Payment was declined. '.$e->getMessage().'. Please try with another card or gateway');
            }
        }
    }

    public function getCurrency()
    {
        $symbol = \Auth::user()->currency_symbol;

        return $symbol;
    }

    public function getState($stateCode)
    {
        if (\Auth::user()->country != 'IN') {
            $state = State::where('state_subdivision_code', $stateCode)->pluck('state_subdivision_name')->first();
        } else {
            $state = TaxByState::where('state_code', \Auth::user()->state)->pluck('state')->first();
        }

        return $state;
    }

}