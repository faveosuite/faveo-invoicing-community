<?php

namespace App\Plugins\Razorpay\Controllers;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Model\Order\InvoiceItem;
use App\Model\Product\Product;
use App\Plugins\Razorpay\Model\RazorpayPayment;
use Darryldecode\Cart\CartCondition;
use Illuminate\Http\Request;

class ProcessController extends Controller
{
    protected $razorpay;

    public function __construct()
    {
        $razorpay = new RazorpayPayment();
        $this->razorpay = $razorpay;

        $product = new Product();
        $this->product = $product;

        $invoiceItem = new InvoiceItem();
        $this->invoiceItem = $invoiceItem;
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
            // if ($request->input('payment_gateway') == 'Razorpay') {
            //     if (! \Schema::hasTable('razorpay')) {
            //         throw new \Exception('Razorpay is not configured');
            //     }
            //     $stripe = $this->razorpay->where('id', 1)->first();
            //     if (! $stripe) {
            //         throw new \Exception('Razorpay Fields not given');
            //     }
            //     \Session::put('invoice', $invoice);
            //     \Session::save();
            //     $this->middlePage();
            // }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode(), $ex->getPrevious());
        }
    }

    public function middlePage()
    {
        try {
            $rzp_key = ApiKey::where('id', 1)->value('rzp_key');
            $rzp_secret = ApiKey::where('id', 1)->value('rzp_secret');
            $apilayer_key = ApiKey::where('id', 1)->value('apilayer_key');
            $payment_method = \Session::get('payment_method');
            $path = app_path().'/Plugins/Razorpay/views';
            $total = intval(\Cart::getTotal());
            $invoice = \Session::get('invoice');
            $regularPayment = true;
            if (! $total) {//When renewal
                $paid = 0;
                // $total = \Session::get('totalToBePaid');
                $regularPayment = false;
                $items = $invoice->invoiceItem()->get();
                $product = $this->product($invoice->id);
                $amount = $invoice->grand_total;

                $processingFee = $this->getProcessingFee($payment_method, $invoice->currency);
                $invoice->processing_fee = $processingFee;
                $invoice->grand_total = intval($invoice->grand_total * (1 + $processingFee / 100));
                $totalPaid = $invoice->grand_total;
                $creditBalance = $invoice->billing_pay;
                if (empty($creditBalance)) {
                    $creditBalance = 0;
                }
                if (count($invoice->payment()->get())) {//If partial payment is made
                    $paid = array_sum($invoice->payment()->pluck('amount')->toArray());
                    $totalPaid = $invoice->grand_total - $paid;
                }
                \Session::put('totalToBePaid', $totalPaid);
                \View::addNamespace('plugins', $path);

                echo view('plugins::middle-page', compact('total', 'rzp_key', 'rzp_secret', 'apilayer_key', 'invoice', 'regularPayment', 'items', 'product', 'amount', 'paid', 'totalPaid', 'creditBalance'));
            } else {//When regular payment
                $pay = $this->payment($payment_method, $status = 'pending');
                $payment_method = $pay['payment'];
                $invoice_no = $invoice->number;
                $status = $pay['status'];
                $processingFee = $this->getProcessingFee($payment_method, $invoice->currency);
                $this->updateFinalPrice(new Request(['processing_fee' => $processingFee]));
                $amount = \Cart::getTotal();
                \View::addNamespace('plugins', $path);

                echo view('plugins::middle-page', compact('invoice', 'amount', 'invoice_no', 'payment_method', 'invoice', 'regularPayment', 'rzp_key', 'rzp_secret', 'apilayer_key'))->render();
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
}
