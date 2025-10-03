<?php

namespace App\Plugins\Stripe\Controllers;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SyncBillingToLatestVersion;
use App\Plugins\Stripe\Model\StripePayment;
use App\Traits\Payment\PostPaymentHandle;
use Cartalyst\Stripe\Laravel\Facades\Stripe;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    use PostPaymentHandle;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['postPaymentWithStripe']]);
    }

    public function Settings()
    {
        try {
            $stripe1 = new StripePayment();
            // //dd($ccavanue);
            $stripe = $stripe1->where('id', '1')->first();

            if (! $stripe) {
                (new SyncBillingToLatestVersion)->sync();
            }
            $allCurrencies = StripePayment::pluck('currencies', 'id')->toArray();
            $apikey = new ApiKey();
            $stripeKeys = $apikey->select('stripe_key', 'stripe_secret')->first();
            $baseCurrency = StripePayment::pluck('base_currency')->toArray();
            $path = app_path().'/Plugins/Stripe/views';
            \View::addNamespace('plugins', $path);

            return view('plugins::settings', compact('stripe', 'baseCurrency', 'allCurrencies', 'stripeKeys'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function postSettings(Request $request)
    {
        $this->validate($request, [
            'business' => 'required',
            'cmd' => 'required',
            'paypal_url' => 'required|url',
            'success_url' => 'url',
            'cancel_url' => 'url',
            'notify_url' => 'url',
            'currencies' => 'required',
        ], [
            'business.required' => __('validation.razorpay_val.business_required'),
            'cmd.required' => __('validation.razorpay_val.cmd_required'),
            'paypal_url.required' => __('validation.razorpay_val.paypal_url_required'),
            'paypal_url.url' => __('validation.razorpay_val.paypal_url_invalid'),
            'success_url.url' => __('validation.razorpay_val.success_url_invalid'),
            'cancel_url.url' => __('validation.razorpay_val.cancel_url_invalid'),
            'notify_url.url' => __('validation.razorpay_val.notify_url_invalid'),
            'currencies.required' => __('validation.razorpay_val.currencies_required'),
        ]);

        try {
            $ccavanue1 = new Paypal();
            $ccavanue = $ccavanue1->where('id', '1')->first();
            $ccavanue->fill($request->input())->save();

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function changeBaseCurrency(Request $request)
    {
        $baseCurrency = Stripe::where('id', $request->input('b_currency'))->pluck('currencies')->first();
        $allCurrencies = Stripe::select('base_currency', 'id')->get();
        foreach ($allCurrencies as $currencies) {
            Stripe::where('id', $currencies->id)->update(['base_currency' => $baseCurrency]);
        }

        return ['message' => 'success', 'update' => 'Base Currency Updated'];
    }

    public function updateApiKey(Request $request)
    {
        $request->validate([
            'stripe_secret' => 'required|string',
            'stripe_key' => 'required|string',
        ], [
            'stripe_secret.required' => __('message.stripe_secret_required'),
            'stripe_key.required' => __('message.stripe_key_required'),
        ]);

        try {
            $stripe = Stripe::make($request->input('stripe_secret'));
            $response = $stripe->customers()->create(['description' => 'Test Customer to Validate Secret Key']);
            $stripe_secret = $request->input('stripe_secret');
            ApiKey::find(1)->update([
                'stripe_secret' => $request->input('stripe_secret'),
                'stripe_key' => $request->input('stripe_key'),
            ]);

            return successResponse(['success' => 'true', 'message' => __('message.stripe_settings_updated_successfully')]);
        } catch (\Cartalyst\Stripe\Exception\UnauthorizedException  $e) {
            return errorResponse($e->getMessage());
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function postPaymentWithStripe(Request $request)
    {
        try {
            $invoice = \Session::get('invoice');
            $amount = rounding(\Cart::getTotal()) ?: rounding(\Session::get('totalToBePaid'));
            $currency = strtolower($invoice->currency);
            $url = url('/confirm/payment');
            $confirm = $this->handlePayment($request, $amount, $currency, $url, $invoice);

            // Check if payment was successful
            if (isset($confirm->status) && $confirm->status === 'succeeded') {
                $result = $this->processPaymentSuccess($invoice, $currency);
                \Session::forget(['items', 'code', 'codevalue', 'totalToBePaid', 'invoice', 'cart_currency']);
                \Cart::removeCartCondition('Processing fee');

                return redirect('checkout')->with($result['status'], $result['message']);
            } else {
                $paymentIntent = \Stripe\PaymentIntent::retrieve($confirm['id']);
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

        \Stripe\Stripe::setApiKey($stripeSecretKey);

        $cost = $this->calculateUnitCost($currency, $amount);

        $user = \Auth::user();

        // Create a Stripe customer with user's information
        $customer = \Stripe\Customer::create([
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

        // Create a payment method using the provided token
        $paymentMethod = \Stripe\PaymentMethod::create([
            'type' => 'card',
            'card' => [
                'token' => $request->stripeToken,
            ],
        ]);

        // Create a payment intent for the transaction
        $intent = \Stripe\PaymentIntent::create([
            'amount' => intval($cost),
            'currency' => $currency,
            'payment_method' => $paymentMethod['id'],
            'customer' => $customer['id'],
            'confirmation_method' => 'automatic',
            'setup_future_usage' => 'off_session',
            'description' => 'payments for the purchased product',
        ]);

        // Confirm the payment intent
        $stripe = new \Stripe\StripeClient($stripeSecretKey);
        $confirm = $stripe->paymentIntents->confirm(
            $intent['id'],
            [
                'payment_method' => $paymentMethod['id'],
                'return_url' => $url,
            ]
        );

        return $confirm;
    }

    public function handleStripeAutoPay($stripe_payment_details, $product_details, $unit_cost, $currency, $plan)
    {
        try {
            $stripeSecretKey = ApiKey::pluck('stripe_secret')->first();
            $stripe = new \Stripe\StripeClient($stripeSecretKey);
            \Stripe\Stripe::setApiKey($stripeSecretKey);

            $paymentMethod = \Stripe\PaymentMethod::retrieve($stripe_payment_details->payment_intent_id);

            //create product
            $product = $stripe->products->create([
                'name' => $product_details->name,
            ]);
            $product_id = $product['id'];

            //define product price and recurring interval

            $price = $stripe->prices->create([
                'unit_amount' => $unit_cost,
                'currency' => $currency,
                'recurring' => ['interval' => 'day', 'interval_count' => $plan->days],
                'product' => $product_id,
            ]);
            $price_id = $price['id'];

            //CREATE SUBSCRIPTION

            $stripe_subscription = $stripe->subscriptions->create([
                'customer' => $paymentMethod->customer,
                'items' => [
                    ['price' => $price_id],
                ],
                'default_payment_method' => $paymentMethod->id,
            ]);

            return $stripe_subscription;
        } catch (ApiErrorException $e) {
            $errorCode = $e->getStripeCode();
            $errorMessage = $e->getMessage();
            $exception = new \Exception("Stripe Error ({$errorCode}): {$errorMessage}");
            \Logger::exception($exception);
        }
    }
}
