<?php

namespace App\Http\Controllers;

use App\ApiKey;
use App\Model\Common\State;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Payment\TaxByState;
use App\Plugins\Stripe\Controllers\SettingsController;
use App\Traits\Payment\PostPaymentHandle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Razorpay\Api\Api;

class RazorpayController extends Controller
{
    use PostPaymentHandle;
    public $invoice;

    public $invoiceItem;

    public function __construct()
    {
        $invoice = new Invoice();
        $this->invoice = $invoice;

        $invoiceItem = new InvoiceItem();
        $this->invoiceItem = $invoiceItem;

        // $mailchimp = new MailChimpController();
        // $this->mailchimp = $mailchimp;
    }

    /*
    * Create Order And Payment for invoice paid with Razorpay
     */
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
                $response = $api->payment->fetch($input['razorpay_payment_id']);

                $stateCode = \Auth::user()->state;
                $state = $this->getState($stateCode);
                $currency = $this->getCurrency();

                $result = $this->processPaymentSuccess($invoice, $currency);
                \Cart::removeCartCondition('Processing fee');
                \Session::forget(['items', 'code', 'codevalue', 'totalToBePaid', 'invoice', 'cart_currency']);

                return redirect('checkout')->with($result['status'], $result['message']);
            } catch (\Razorpay\Api\Errors\SignatureVerificationError|\Razorpay\Api\Errors\BadRequestError|\Razorpay\Api\Errors\GatewayError|\Razorpay\Api\Errors\ServerError $e) {
                SettingsController::sendFailedPaymenttoAdmin($invoice, $invoice->grand_total, $invoice->invoiceItem()->first()->product_name, $e->getMessage(), \Auth::user());

                return redirect('checkout')->with('fails', 'Your Payment was declined. '.$e->getMessage().'. Please try with another card or gateway');
            } catch (\Exception $e) {
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

    public function afterPayment(Request $request)
    {
        try {
            $stripeSecretKey = ApiKey::pluck('stripe_secret')->first();
            $stripe = new \Stripe\StripeClient($stripeSecretKey);
            $invoice = \Session::get('invoice');
            $paymentIntent = $stripe->paymentIntents->retrieve($request->input('payment_intent'));
            if ($paymentIntent->status === 'succeeded') {
                $currency = strtolower($invoice->currency);
                $controller = new SettingsController();
                $result = $controller->processPaymentSuccess($invoice, $currency);
                \Session::forget(['items', 'code', 'codevalue', 'totalToBePaid', 'invoice', 'cart_currency']);
                \Cart::removeCartCondition('Processing fee');

                return redirect('checkout')->with($result['status'], $result['message']);
            } else {
                $control = new \App\Http\Controllers\Order\RenewController();
                if ($control->checkRenew($invoice->is_renewed) != true) {
                    return redirect('checkout')->with('fails', 'Your Payment was declined. Please try with another card or gateway');
                } else {
                    return redirect('paynow/'.$invoice->id)->with('fails', 'Your Payment was declined. Please try with another card or gateway');
                }
            }
        } catch (\Exception $e) {
            return redirect('checkout')->with('fails', 'Your Payment was declined. Please try with another card or gateway');
        }
    }

    public function handleRzpAutoPay($cost, $days, $product_name, $invoice, $currency, $subscription, $user, $order, $endDate, $productDetails)
    {
        $key_id = ApiKey::pluck('rzp_key')->first();
        $secret = ApiKey::pluck('rzp_secret')->first();
        $api = new Api($key_id, $secret);
        $rzp_plan = $api->plan->create(['period' => 'monthly',
            'interval' => round((int) $days / 30),
            'item' => [
                'name' => $product_name,
                'amount' => $cost,
                'currency' => $currency, ],

        ]
        );

        $rzp_subscriptionLink = $api->subscription->create([
            'plan_id' => $rzp_plan['id'],
            'total_count' => 100,
            'quantity' => 1,
            'expire_by' => Carbon::parse($subscription->update_ends_at)->addDays(1)->timestamp,
            'start_at' => Carbon::parse($subscription->update_ends_at)->addDays(round((int) $days))->timestamp,

            'customer_notify' => 1,
            'addons' => [['item' => [
                'name' => $product_name,
                'amount' => $cost,
                'currency' => $currency]]],

        ]);

        return $rzp_subscriptionLink;
    }
}
