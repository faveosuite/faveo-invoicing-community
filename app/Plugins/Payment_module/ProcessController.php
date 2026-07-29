<?php

namespace App\Plugins\Payment_module;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Order\ExtendedBaseInvoiceController;
use App\Model\Common\CreditActivity;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Plugins\Razorpay\Model\RazorpayPayment;
use App\Plugins\Stripe\Model\StripePayment;
use App\Traits\Payment\PostPaymentHandle;
use App\User;
use Carbon\Carbon;
use Darryldecode\Cart\CartCondition;
use Illuminate\Http\JsonResponse;
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

    public function PassToPayment($requests)
    {
        try {
            $request = $requests['request'];
            $invoice = $requests['invoice'];
            $auto_renewal = $request['auto-renewal'] != null ? $request['auto-renewal'] : 0;
            $cart = \Cart::getContent();
            if (! $cart->count()) {
                \Cart::clear();
            } else {
                $processingFee = $this->getProcessingFee($request->input('payment_gateway'), $invoice->currency);
                $this->updateFinalPrice(new Request(['processing_fee' => $processingFee]));
                $invoice->grand_total = \Cart::getTotal();
            }
            if ($request->input('payment_gateway') == 'Stripe') {
                $this->redirectToStripe($invoice, $auto_renewal, $request->input('payment_gateway'));
            } elseif ($request->input('payment_gateway') == 'Razorpay') {
                $this->redirectToRazorpay($invoice, $auto_renewal, $request->input('payment_gateway'));
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode(), $ex->getPrevious());
        }
    }

    public function redirectToStripe($invoice, $auto_renewal, $payment_gateway)
    {
        if (! \Schema::hasTable('stripe')) {
            throw new \Exception(__('message.stripe_not_configured'));
        }
        $stripe = $this->stripe->where('id', 1)->first();
        if (! $stripe) {
            throw new \Exception(__('message.stripe_fields_not_given'));
        }
        \Session::put('invoice', $invoice);
        \Session::save();
        $url = '';

        if ($auto_renewal) {
            \Session::put('auto-renewal', true);
            $stripeController = new Stripe\Controllers\RecurringController();
//                    $url=$stripeController->usageBasedSubscriptionData($invoice);
            $url = $stripeController->subscriptionData($invoice);
        }
        $this->middlePage($payment_gateway, ['auto_renewal' => $auto_renewal, 'url' => $url]);
    }

    public function redirectToRazorpay($invoice, $auto_renewal, $payment_gateway)
    {
        if (! \Schema::hasTable('razorpay')) {
            throw new \Exception(__('message.razorpay_not_configured'));
        }
        $stripe = $this->razorpay->where('id', 1)->first();
        if (! $stripe) {
            throw new \Exception(__('message.razorpay_fields_not_given'));
        }
        $regularPayment = \Cart::getTotal() ? true : false;

        \Session::put('invoice', $invoice);

        if ($auto_renewal) {
            \Session::put('auto-renewal', true);
            $razorpayController = new Razorpay\Controllers\OnetimeController();
            $json = $razorpayController->processRazorpayOrder($invoice);
        } else {
            $razorpayController = new Razorpay\Controllers\RecurringController();
            $json = $razorpayController->processRazorpayOrder($invoice, $regularPayment, $auto_renewal);
        }
        \Session::save();
        $this->middlePage($payment_gateway, ['json' => $json, 'auto_renewal' => 0, 'url' => '']);
    }

    public function middlePage($gateway, $data = [])
    {
        try {
            $rzp_key = ApiKey::where('id', 1)->value('rzp_key');
            $rzp_secret = ApiKey::where('id', 1)->value('rzp_secret');
            $stripe_key = ApiKey::where('id', 1)->value('stripe_key');
            $apilayer_key = ApiKey::where('id', 1)->value('apilayer_key');
            $path = app_path().'/Plugins/Stripe/views';
            $total = intval(\Cart::getTotal());
            $payment_method = \Session::get('payment_method');
            $regularPayment = true;
            $invoice = \Session::get('invoice');
            if (! $total) {
                $paid = 0;
                // $total = \Session::get('totalToBePaid');
                $regularPayment = false;
                $items = $invoice->invoiceItem()->get();
                $product = $this->product($invoice->id);
                $processingFee = $this->getProcessingFee($payment_method, $invoice->currency);
                $invoice->processing_fee = $processingFee;
                $invoice->grand_total = intval($invoice->grand_total * (1 + $processingFee / 100));
                $amount = rounding($invoice->grand_total);
                $creditBalance = $invoice->billing_pay;
                if (empty($creditBalance)) {
                    $creditBalance = 0;
                }
                if (count($invoice->payment()->get())) {//If partial payment is made
                    $paid = array_sum($invoice->payment()->pluck('amount')->toArray());
                    $amount = rounding($invoice->grand_total - $paid);
                }
                \Session::put('totalToBePaid', $amount);
                $path = app_path().'/Plugins/Payment_module';
                \View::addNamespace('Plugins', $path);
                echo view('plugins::middle-page', compact('total', 'invoice', 'regularPayment', 'items', 'product', 'amount', 'paid', 'creditBalance', 'gateway', 'rzp_key', 'rzp_secret', 'apilayer_key', 'stripe_key', 'data', 'processingFee'));
            } else {
                $pay = $this->payment($payment_method, $status = 'pending');
                $payment_method = $pay['payment'];
                $invoice_no = $invoice->number;
                $status = $pay['status'];
                $processingFee = $this->getProcessingFee($payment_method, $invoice->currency);
                //                $this->updateFinalPrice(new Request(['processing_fee' => $processingFee]));
                $amount = rounding(\Cart::getTotal());
                $path = app_path().'/Plugins/Payment_module';
                \View::addNamespace('plugins', $path);
                echo view('plugins::middle-page', compact('invoice', 'amount', 'invoice_no', 'payment_method', 'invoice', 'regularPayment', 'gateway', 'rzp_key', 'rzp_secret', 'apilayer_key', 'stripe_key', 'data', 'processingFee'))->render();
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

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

    /**
     *  Delete Auto renewal and update auto-renewal status.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function disableAutorenewalStatus(Request $request)
    {
        try {
            $orderid = $request->get('order_id');
            $userid = Subscription::where('order_id', $orderid)->value('user_id');
            $user = User::find($userid);
            $subscription = Subscription::where('order_id', $orderid)->first();
            $this->autoRenewalSubOps($subscription, $orderid);
            $response = ['type' => 'success', 'message' => __('message.auto_subscription_disabled')];

            return response()->json($response);
        } catch(\Exception $ex) {
            $result = $ex->getMessage();

            return response()->json(compact('result'), 500);
        }
    }

    private function autoRenewalSubOps($subscription, $orderid)
    {
        $days = Plan::where('id', $subscription->plan_id)->value('days');
        $RemainingDays = Carbon::now()->diffInDays($subscription->update_ends_at, false);
        $order = Order::where('id', $orderid)->first();
        $invoice = $order->invoice()->first();
        $perDay = $invoice->grand_total / $days;
        $creditBalance = $perDay * $RemainingDays;
        if ($subscription->rzp_subscription && $subscription->is_subscribed && $subscription->subscribe_id) {
            $rzp_key = ApiKey::where('id', 1)->value('rzp_key');
            $rzp_secret = ApiKey::where('id', 1)->value('rzp_secret');
            $days = Plan::where('id', $subscription->plan_id)->value('days');
            $api = new Api($rzp_key, $rzp_secret);
            $pause = $api->subscription->fetch($subscription->subscribe_id)->cancel();
            if ($RemainingDays > 0 && $subscription->credit_refund == 0) {
                $this->updateCredit($creditBalance);
            }
            Subscription::where('order_id', $orderid)->update(['is_subscribed' => '0', 'rzp_subscription' => '0']);
        } elseif ($subscription->autoRenew_status && $subscription->is_subscribed && $subscription->subscribe_id) {
            $stripeSecretKey = ApiKey::pluck('stripe_secret')->first();
            $stripe = new \Stripe\StripeClient($stripeSecretKey);
            \Stripe\Stripe::setApiKey($stripeSecretKey);
            $pause = $stripe->subscriptions->cancel($subscription->subscribe_id, []);
            if ($RemainingDays > 0 && $subscription->credit_refund == 0) {
                $this->updateCredit($creditBalance);
            }
            Subscription::where('order_id', $orderid)->update(['is_subscribed' => '0', 'autoRenew_status' => '0', 'credit_refund' => '0']);
        } else {
            Subscription::where('order_id', $orderid)->update(['is_subscribed' => '0', 'autoRenew_status' => '0', 'rzp_subscription' => '0', 'credit_refund' => '0',
            ]);
        }
    }

    public function updateCredit($discount)
    {
        $payUpdate = Payment::where('user_id', \Auth::user()->id)->where('payment_status', 'success')->where('payment_method', 'Credit Balance')->get();

        $pay = Payment::where('user_id', \Auth::user()->id)->where('payment_status', 'success')->where('payment_method', 'Credit Balance')->value('amt_to_credit');
        $formattedValue = currencyFormat(round($discount), getCurrencyForClient(\Auth::user()->country), true);
        $payment_id = Payment::where('user_id', \Auth::user()->id)->where('payment_status', 'success')->where('payment_method', 'Credit Balance')->value('id');
        $formattedPay = currencyFormat($pay, getCurrencyForClient(\Auth::user()->country), true);
        $orderId = \Session::get('creditOrderId');
        $orderNumber = Order::where('id', $orderId)->value('number');

        if (! $payUpdate->isEmpty()) {
            $pay = $pay + round($discount);
            Payment::where('user_id', \Auth::user()->id)->where('payment_status', 'success')->update(['amt_to_credit' => $pay]);

            $messageAdmin = 'An amount of '.$formattedValue.' has been added to your existing balance due to a subscription cancellation. You can view the details of the subscription cancelled order here: '.
                '<a href="'.config('app.url').'/orders/'.$orderId.'">'.$orderNumber.'</a>.';

            $messageClient = 'An amount of '.$formattedValue.' has been added to your existing balance due to a subscription cancellation. You can view the details of the subscription cancelled order here: '.
                '<a href="'.config('app.url').'/my-order/'.$orderId.'">'.$orderNumber.'</a>.';
            CreditActivity::insert(['payment_id' => $payment_id, 'text' => $messageAdmin, 'role' => 'admin', 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()]);
            CreditActivity::insert(['payment_id' => $payment_id, 'text' => $messageClient, 'role' => 'user', 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()]);
        } else {
            $price = 0;
            \Session::put('discount', round($discount));
            (new ExtendedBaseInvoiceController())->multiplePayment(\Auth::user()->id, [0 => 'Credit Balance'], 'Credit Balance', Carbon::now(), $price, null, round($discount), 'pending');
        }
    }
}
