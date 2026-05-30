<?php

namespace App\Http\Controllers;

use App\ApiKey;
use App\Facades\Cart;
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
    public $cart;

    public function __construct()
    {
        $invoice = new Invoice();
        $this->invoice = $invoice;

        $invoiceItem = new InvoiceItem();
        $this->invoiceItem = $invoiceItem;
        $this->cart = new Cart();
        // $mailchimp = new MailChimpController();
        // $this->mailchimp = $mailchimp;
    }

    /*
     * Verify a Razorpay Checkout handler response for an invoice and fulfil it.
     * The signature is verified server-side; nothing is recorded unless authentic.
     */
    public function payment($invoice, Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id'   => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        $model = Invoice::find($invoice);
        abort_if(! $model, 404, 'Invoice not found.');
        if (\Auth::user()->role != 'admin' && (int) $model->user_id !== (int) \Auth::id()) {
            return errorResponse(__('message.invalid_modification'));
        }

        try {
            $paid = app(\App\Services\Payment\InvoicePaymentService::class)
                ->confirm($model, 'Razorpay', $request->only([
                    'razorpay_payment_id', 'razorpay_order_id', 'razorpay_signature',
                ]));

            return $paid
                ? successResponse('success', [])
                : errorResponse(__('message.payment_declined_try_other_gateway'));
        } catch (\App\Plugins\Payment\Exceptions\SignatureVerificationException $e) {
            if (emailSendingStatus()) {
                $this->sendFailedPaymenttoAdmin($model, $model->grand_total, optional($model->invoiceItem()->first())->product_name, $e->getMessage(), \Auth::user());
            }

            return errorResponse(__('message.payment_declined_try_other_gateway'));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function getCurrency()
    {
        $symbol = \Auth::user()->currency_symbol;

        return $symbol;
    }

    public function getState($country, $stateCode)
    {
        if (\Auth::user()->country != 'IN') {
            $state = State::where('country_code', $country)->where('iso2', $stateCode)->pluck('state_subdivision_name')->first();
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
            // SPA flow carries the invoice id on the return URL (stateless);
            // legacy flow still falls back to the session-stored invoice.
            $invoice = $request->query('invoice')
                ? Invoice::find($request->query('invoice'))
                : \Session::get('invoice');
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
