<?php

namespace App\Plugins\Payment_module\Stripe\Controllers;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Plugins\Stripe\Controllers\SettingsController;
use App\Traits\Payment\PostPaymentHandle;
use Illuminate\Http\Request;

class OnetimeController extends Controller
{
    use PostPaymentHandle;

    public function postPaymentWithStripe(Request $request)
    {
        try {
            $invoice = \Session::get('invoice');
            $amount = rounding(\Cart::getTotal()) ?: rounding(\Session::get('totalToBePaid'));
            $currency = strtolower($invoice->currency);
            $url = url('/confirm/payment');
            $confirm = $this->handlePayment($request, $amount, $currency, $url, $invoice);
            // Check if payment was successful
            if (isset($confirm['confirm']->status) && $confirm['confirm']->status === 'succeeded') {
                $result = $this->processPaymentSuccess($invoice, $currency);
                \Session::forget(['items', 'code', 'codevalue', 'totalToBePaid', 'invoice', 'cart_currency', 'auto-renewal']);
                \Cart::removeCartCondition('Processing fee');

                return redirect('checkout')->with($result['status'], $result['message']);
            } else {
                $paymentIntent = \Stripe\PaymentIntent::retrieve($confirm['confirm']->id);
                $redirectUrl = $paymentIntent->next_action->redirect_to_url->url;

                return redirect()->away($redirectUrl);
            }
        } catch (\Cartalyst\Stripe\Exception\ApiLimitExceededException|\Cartalyst\Stripe\Exception\BadRequestException|\Cartalyst\Stripe\Exception\MissingParameterException|\Cartalyst\Stripe\Exception\NotFoundException|\Cartalyst\Stripe\Exception\ServerErrorException|\Cartalyst\Stripe\Exception\StripeException|\Cartalyst\Stripe\Exception\UnauthorizedException $e) {
            $control = new \App\Http\Controllers\Order\RenewController();
            if ($control->checkRenew($invoice->is_renewed) != true) {
                return redirect('checkout')->with('fails', __('message.stripe_payment_declined', ['error' => $e->getMessage()]));
            } else {
                return redirect('paynow/'.$invoice->id)->with('fails', __('message.stripe_payment_declined', ['error' => $e->getMessage()]));
            }
        } catch (\Cartalyst\Stripe\Exception\CardErrorException $e) {
            if (emailSendingStatus()) {
                $user = auth()->user();
                $this->sendFailedPaymenttoAdmin($invoice, $invoice->grand_total, $invoice->invoiceItem()->first()->product_name, $e->getMessage(), $user);
            }
            \Session::put('amount', $amount);
            \Session::put('error', $e->getMessage());

            return redirect()->route('checkout');
        } catch (\Exception $e) {
            return redirect('checkout')->with('fails', __('message.stripe_payment_declined', ['error' => $e->getMessage()]));
        }
    }

    public function handlePayment(Request $request, $amount, $currency, $url, $invoice = null)
    {
        $request->validate([
            'stripeToken' => 'required|string',
        ], [
            'stripeToken.required' => __('message.stripe_token_required'),
        ]);
        $stripeSecretKey = ApiKey::pluck('stripe_secret')->first();
        $stripe = \Stripe\Stripe::setApiKey($stripeSecretKey);
        $cost = $this->calculateUnitCost($currency, $amount);
        $user = \Auth::user();
        $payment = $this->paymentIntentCreation($user, $cost, $stripe, $request->stripeToken, $currency);

        // Confirm the payment intent
        $stripe = new \Stripe\StripeClient($stripeSecretKey);
        $confirm = $stripe->paymentIntents->confirm(
            $payment['paymentIntent']['id'],
            [
                'payment_method' => $payment['paymentMethod']['id'],
                'return_url' => $url,
            ]
        );

        return['confirm' => $confirm];
    }

    public function paymentIntentCreation($user, $cost, $stripe, $token, $currency)
    {
        $customer = $this->customerCreation($user, $stripe);
        $paymentMethod = \Stripe\PaymentMethod::create([
            'type' => 'card',
            'card' => [
                'token' => $token,
            ],
        ]);

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => intval($cost),
            'currency' => $currency,
            'payment_method' => $paymentMethod['id'],
            'customer' => $customer['id'],
            'confirmation_method' => 'automatic',
            'setup_future_usage' => 'off_session',
            'description' => 'payments for the purchased product',
        ]);

        return ['paymentIntent' => $paymentIntent, 'paymentMethod' => $paymentMethod];
    }

    public function customerCreation($user)
    {
        try {
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
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
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

                \Session::forget(['items', 'code', 'codevalue', 'totalToBePaid', 'invoice', 'cart_currency', 'customer_id']);
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
}
