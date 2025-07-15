<?php


namespace App\Plugins\Payment_module\Razorpay\Controllers;


use App\ApiKey;
use App\Auto_renewal;
use App\Http\Controllers\Common\CronController;
use App\Http\Controllers\ConcretePostSubscriptionHandleController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Order\BaseRenewController;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Traits\Payment\PostPaymentHandle;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Razorpay\Api\Api;

Class RecurringController extends Controller{
use postPaymentHandle;
    public function processRazorpayOrder($invoice, $regularPayment,$auto_renewal)
    {
        try {
            $apiKey = ApiKey::first();
            $rzp_key = $apiKey->rzp_key;
            $rzp_secret = $apiKey->rzp_secret;

            $user = auth()->user();

            $merchant_orderid = $this->generateMerchantRandomString();

            $cartTotal = $invoice->grand_total;

            // Handle credit balance if applicable
            if ($user->billing_pay_balance && $regularPayment) {
                $amt_to_credit = \DB::table('payments')
                    ->where('user_id', $user->id)
                    ->where('payment_method', 'Credit Balance')
                    ->where('payment_status', 'success')
                    ->where('amt_to_credit', '!=', 0)
                    ->value('amt_to_credit');

                if ($invoice->grand_total <= $amt_to_credit) {
                    $cartTotal = 0;
                } else {
                    $cartTotal = $invoice->grand_total - $amt_to_credit;
                }
            }

            $cartTotal = intval($cartTotal);

            $api = new Api($rzp_key, $rzp_secret);



                $orderData = [
                    'receipt' => '3456',
                    'amount' => round($cartTotal * 100),
                    'currency' => $invoice->currency,
                    'method'=> 'emandate',
                    'payment_capture' => 0,
                ];

                $razorpayOrder = $api->order->create($orderData);
                $razorpayOrderId = $razorpayOrder['id'];

                $data = [
                    'key' => $rzp_key,
                    'name' => 'Faveo Helpdesk',
                    'order_id' => $razorpayOrderId,
                    'description' => 'Order for Invoice No - '.$invoice->number,
                    'prefill' => [
                        'contact' => $user->mobile_code.$user->mobile,
                        'email' => $user->email,
                    ],
                    'notes' => [
                        'First Name' => $user->first_name,
                        'Last Name' => $user->last_name,
                        'Company Name' => $user->company,
                        'Address' => $user->address,
                        'Email' => $user->email,
                        'Country' => $user->country,
                        'State' => $user->state,
                        'City' => $user->town,
                        'Zip' => $user->zip,
                        'Amount Paid' => $cartTotal * 100,
                        'merchant_order_id' => $merchant_orderid,
                    ],
                    'theme' => [
                        'color' => '#F37254',
                    ],
                ];
            return json_encode($data);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode(), $ex->getPrevious());
        }
    }

    protected function generateMerchantRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }


    /**
     *  Setup razorpay , create auto renewal and update auto renewal status.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function enableRzpStatus(Request $request)
    {
        try {
            $currency = getCurrencyForClient(\Auth::user()->country);
            $amount = currencyFormat('1', $currency);
            $orderid = \Session::get('order-id-renewal');
            $subscription = Subscription::where('order_id', $orderid)->first();
            $input = $request->all();
            $error = 'Payment Failed';
            $rzp_key = ApiKey::where('id', 1)->value('rzp_key');
            $rzp_secret = ApiKey::where('id', 1)->value('rzp_secret');
            $api = new Api($rzp_key, $rzp_secret);

            $payment = $api->payment->fetch($input['razorpay_payment_id']);
//            $response = $api->payment->fetch($input['razorpay_payment_id']);
//            $capture = $api->payment->fetch($response->id)->capture(['amount' => $response->amount]);
//            $refund = $api->payment->fetch($response->id)->refund(['amount' => $response->amount, 'speed' => 'normal']);

            $invoice_id = OrderInvoiceRelation::where('order_id', $orderid)->value('invoice_id');
            $number = Invoice::where('id', $invoice_id)->value('number');

            $customer_details = [
                'user_id' => \Auth::user()->id,
                'customer_id' => $payment['customer_id'],
                'payment_method' => 'razorpay',
                'order_id' => $orderid,
            ];
            Auto_renewal::create($customer_details);

            Subscription::where('order_id', $orderid)->update(['is_subscribed' => '1', 'rzp_subscription' => '3', 'subscribe_id'=>$input['razorpay_subscription_id']]);
            \Session::forget('order-id-renewal');
            $mail = new \App\Http\Controllers\Common\PhpMailController();
            $mail->payment_log(\Auth::user()->email, 'Razorpay', 'success', Order::where('id', $orderid)->value('number'), null, $amount, 'Payment method updated');

//            return redirect()->back()->with('success', __('message.card_updated_successfully'));
            return redirect('my-order/'.$orderid.'#auto-renew')->with('success', __('message.card_details_updated_successfully'));

        } catch(\Exception $ex) {
            $result = $ex->getMessage();
            $mail = new \App\Http\Controllers\Common\PhpMailController();
            $mail->payment_log(\Auth::user()->email, 'stripe', 'failed', Order::where('id', $orderid)->value('number'), $result, $amount, 'Payment method updated');

//            return redirect()->back()->with('fails', __('message.payment_declined', ['msg' => $ex->getMessage()]));
            return redirect('my-order/'.$orderid.'#auto-renew')->with('fails', __('message.payment_declined', ['msg' => $ex->getMessage()]));

        }
    }

    public function enableRzpAutorenewalStatus(Request $request){
        $orderid = $request->get('order_id');
        \Session::put('order',$orderid);
        $order=Order::where('id',$orderid)->first();
        $product_details=Product::where('id',$order->product)->first();
        $invoice=Invoice::where('id',$order->invoice_id)->first();
        $cost = $invoice->grand_total;
        $currency = $invoice->currency;
        $subscription=Subscription::where('order_id',$order->id)->first();
        $plan=Plan::where('id',$subscription->plan_id)->first();
        $unit_cost = $this->calculateUnitCost($currency, $cost);

        $key_id = ApiKey::pluck('rzp_key')->first();
        $secret = ApiKey::pluck('rzp_secret')->first();
        $api = new Api($key_id, $secret);
        $user=\Auth::user();
        $customer = $api->customer->create([
            'name' => $user->first_name.' '.$user->last_name,
            'email' => $user->email,
            'contact' => $user->mobile_code.$user->mobile,
            'fail_existing' => 0,
        ]);

        $this->customer_id = $customer['id'];

        $rzp_plan = $api->plan->create(['period' => 'yearly',
                'interval' => 2,
                'item' => [
                    'name' => $product_details->name,
                    'amount' => $unit_cost,
                    'currency' => $currency, ],

            ]
        );

        $rzp_subscriptionLink = $api->subscription->create([
            'plan_id' => $rzp_plan['id'],
            'customer_id'=>$customer['id'],
            'quantity' => 1,
            'end_at' => Carbon::parse($subscription->update_ends_at)->addDays(round((int) $plan->days))->timestamp,
            'start_at' => Carbon::parse($subscription->update_ends_at)->timestamp,
            'customer_notify' => 1,

        ]);
        $data = [
            'key' => $key_id,
            'name' => 'Faveo Helpdesk',
            'currency' => 'INR',
            'prefill' => [
                'contact' => $user->mobile_code.$user->mobile,
                'email' => $user->email,
            ],
            'description' => 'Order for Invoice No'.-$invoice->number,

            'subscription_id'=> $rzp_subscriptionLink['id'],
//            'method'=>'card',
            'recurring'=> true,
            'theme' => [
                'color' => '#F37254',
            ],
            'callback_url'=>url('rzpRenewal-disable'),
        ];
        return response()->json(['data' => $data]);

    }

    public function razorpay_webhook(Request $request)
    {
        $key_id = ApiKey::pluck('rzp_key')->first();
        $secret = ApiKey::pluck('rzp_secret')->first();
        $api = new Api($key_id, $secret);

        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        $razorpay_secret='Santhanu@12';
        try {
            $webCheck = $api->utility->verifyWebhookSignature($payload, $signature, $razorpay_secret);
            $data = json_decode($payload, true);

            switch ($data['event']) {
                case 'subscription.charged':
                    $subscription= $data['payload']['subscription']['entity']['id'];
                    $this->razorpay_success($subscription);
                    break;
                case 'subscription.completed':
                    \Log::debug('subscription.completed', [$data]);
                    break;
                case 'subscription.pending':
                    \Log::debug('subscription.pending', [$data]);
                    break;
                case 'subscription.authenticated':
                    \Log::debug('subscription.authenticated', $data['payload']['subscription']['entity']['id']);
                     break;
                case 'subscription.activated':
                    \Log::debug('subscription.activated', [$data]);
                    break;
//                case 'invoice.paid':
//                    \Log::debug('invoice.paid', $data);
//                    break;
                default:
                    \Log::info("Unhandled Razorpay event: " . $data['event']);
                    break;
            }


        } catch (\Exception $ex) {
            \Log::error('razorpay_webhook_error', [$ex->getMessage()]);
        }
    }

    public function razorpay_success($subscription){
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
            $processingFee=ApiKey::where('id',1)->value('stripe_processing_fee');
            $processingFee = (float)$processingFee / 100;
            $price = $cost + ($cost * $processingFee);
        }
        $renewController = new BaseRenewController();
        $oldcurrency = getCurrencyForClient($user->country);
        $invoice = $renewController->generateInvoice($product_details, $user, $order->id, $subscription->plan_id, $cost, $code = '', $item->agents, $oldcurrency);
        $cost = Invoice::where('id', $invoice->invoice_id)->value('grand_total');
        $plan = Plan::where('id', $subscription->plan_id)->first('days');
        $controller->processRazorpaySubscription($subscription, $currency, $cost, $user, $order, $product_details, $invoice, $plan);

    }


}