<?php

namespace App\Plugins\Stripe\Controllers;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Model\Order\InvoiceItem;
use App\Model\Product\Product;
use App\Plugins\Razorpay\Model\RazorpayPayment;
use App\Plugins\Stripe\Model\StripePayment;
use Darryldecode\Cart\CartCondition;
use Illuminate\Http\Request;
use Razorpay\Api\Api;

class ProcessController extends Controller
{
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
            $cart = \Cart::getContent();
            if (! $cart->count()) {
                \Cart::clear();
            } else {
                $invoice->grand_total = \Cart::getTotal();
            }
            if ($request->input('payment_gateway') == 'Stripe') {
                if (! \Schema::hasTable('stripe')) {
                    throw new \Exception(__('message.stripe_not_configured'));
                }
                $stripe = $this->stripe->where('id', 1)->first();
                if (! $stripe) {
                    throw new \Exception(__('message.stripe_fields_not_given'));
                }
                \Session::put('invoice', $invoice);
                \Session::save();
                $this->middlePage($request->input('payment_gateway'));
            } elseif ($request->input('payment_gateway') == 'Razorpay') {
                if (! \Schema::hasTable('razorpay')) {
                    throw new \Exception(__('message.razorpay_not_configured'));
                }
                $stripe = $this->razorpay->where('id', 1)->first();
                if (! $stripe) {
                    throw new \Exception(__('message.razorpay_fields_not_given'));
                }
                \Session::put('invoice', $invoice);
                \Session::save();
                $regularPayment = \Cart::getTotal() ? true : false;
                $json = $this->processRazorpayOrder($invoice, $regularPayment);
                $this->middlePage($request->input('payment_gateway'), ['json' => $json]);
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode(), $ex->getPrevious());
        }
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
                \View::addNamespace('plugins', $path);
                echo view('plugins::middle-page', compact('total', 'invoice', 'regularPayment', 'items', 'product', 'amount', 'paid', 'creditBalance', 'gateway', 'rzp_key', 'rzp_secret', 'apilayer_key', 'stripe_key', 'data'));
            } else {
                $pay = $this->payment($payment_method, $status = 'pending');
                $payment_method = $pay['payment'];
                $invoice_no = $invoice->number;
                $status = $pay['status'];
                $processingFee = $this->getProcessingFee($payment_method, $invoice->currency);
                $this->updateFinalPrice(new Request(['processing_fee' => $processingFee]));
                $amount = rounding(\Cart::getTotal());
                \View::addNamespace('plugins', $path);

                echo view('plugins::middle-page', compact('invoice', 'amount', 'invoice_no', 'payment_method', 'invoice', 'regularPayment', 'gateway', 'rzp_key', 'rzp_secret', 'apilayer_key', 'stripe_key', 'data'))->render();
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
                return $paymentMethod == 'razorpay' ? 0 : \DB::table(strtolower($paymentMethod))->where('currencies', $currency)->value('processing_fee');
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

    protected function processRazorpayOrder($invoice, $regularPayment)
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
}
