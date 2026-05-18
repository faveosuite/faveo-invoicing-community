<?php

namespace App\Plugins\Stripe\Controllers;

use App\ApiKey;
use App\Facades\Cart;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SyncBillingToLatestVersion;
use App\Plugins\Stripe\Model\StripePayment;
use App\Traits\Payment\PostPaymentHandle;
use App\User;
use Cartalyst\Stripe\Laravel\Facades\Stripe;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class SettingsController extends Controller
{
    use PostPaymentHandle;
    public $cart;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['postPaymentWithStripe']]);
        $this->cart = new Cart();
    }

    public function getSettings()
    {
        try {
            $stripeKeys = ApiKey::select('stripe_key', 'stripe_secret')->first();

            return successResponse('', [
                'stripe_key'    => $stripeKeys->stripe_key ?? '',
                'stripe_secret' => $stripeKeys->stripe_secret ?? '',
            ]);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
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

            return successResponse(__('message.stripe_settings_updated_successfully'));
        } catch (\Cartalyst\Stripe\Exception\UnauthorizedException  $e) {
            return errorResponse($e->getMessage());
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    /**
     * success response method.
     *
     * @return
     */
    public function postPaymentWithStripe(Request $request)
    {
        try {
            $invoice = \Session::get('invoice');
            $amount = rounding($this->cart->getTotal()) ?: rounding(\Session::get('totalToBePaid'));
            $currency = strtolower($invoice->currency);
            $url = url('/confirm/payment');
            $confirm = $this->handlePayment($request, $amount, $currency, $url);

            // Check if payment was successful
            if (isset($confirm->status) && $confirm->status === 'succeeded') {
                $result = $this->processPaymentSuccess($invoice, $currency);
                \Session::forget(['items', 'code', 'codevalue', 'totalToBePaid', 'invoice', 'cart_currency']);
                \Cart::removeCartCondition('Processing fee');
                $data = ['status' => $result['status'], 'message' => $result['message']];

                return successResponse('success', []);
//                return redirect('checkout')->with($result['status'], $result['message']);
            } else {
                $paymentIntent = \Stripe\PaymentIntent::retrieve($confirm['id']);
                $redirectUrl = $paymentIntent->next_action->redirect_to_url->url;

                return errorResponse('fail', ['redirectUrl' => $redirectUrl]);
//                return redirect()->away($redirectUrl);
            }
        } catch (\Cartalyst\Stripe\Exception\ApiLimitExceededException|\Cartalyst\Stripe\Exception\BadRequestException|\Cartalyst\Stripe\Exception\MissingParameterException|\Cartalyst\Stripe\Exception\NotFoundException|\Cartalyst\Stripe\Exception\ServerErrorException|\Cartalyst\Stripe\Exception\StripeException|\Cartalyst\Stripe\Exception\UnauthorizedException $e) {
            $control = new \App\Http\Controllers\Order\RenewController();
            if ($control->checkRenew($invoice->is_renewed) != true) {
                return errorResponse($e->getMessage(), ['redirectTo' => 'checkout']);
//                return redirect('checkout')->with('fails', __('message.stripe_payment_declined', ['error' => $e->getMessage()]));
            } else {
                return errorResponse($e->getMessage(), ['redirectTo' => 'paynow']);
//                return redirect('paynow/'.$invoice->id)->with('fails', __('message.stripe_payment_declined', ['error' => $e->getMessage()]));
            }
        } catch (\Cartalyst\Stripe\Exception\CardErrorException $e) {
            if (emailSendingStatus()) {
                $user = auth()->user();
                $this->sendFailedPaymenttoAdmin($invoice, $invoice->grand_total, $invoice->invoiceItem()->first()->product_name, $e->getMessage(), $user);
            }
            \Session::put('amount', $amount);
            \Session::put('error', $e->getMessage());

//            return redirect()->route('checkout');
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), ['redirectTo' => 'checkout']);
//            return redirect('checkout')->with('fails', __('message.stripe_payment_declined', ['error' => $e->getMessage()]));
        }
    }

    public function handlePayment(Request $request, $amount, $currency, $url, $user = null)
    {
        $request->validate([
            'stripeToken' => 'required|string',
        ], [
            'stripeToken.required' => __('message.stripe_token_required'),
        ]);

        $user = $user ?? auth()->user();

        $stripeSecretKey = ApiKey::value('stripe_secret');

        $stripe = new StripeClient($stripeSecretKey);

        $cost = calculateUnitCost($currency, $amount);

        // Extract customer data
        $customerData = $this->extractCustomerData($user);

        // Create customer
        $customer = $stripe->customers->create($customerData);

        // Build shipping details for Indian export compliance
        $addressData = $customerData['address'] ?? [];
        $shippingDetails = [
            'name' => $customerData['name'] ?: 'Customer',
            'address' => [
                'line1' => $addressData['line1'] ?: 'Not Provided',
                'city' => $addressData['city'] ?: 'Not Provided',
                'state' => $addressData['state'] ?? '',
                'postal_code' => $addressData['postal_code'] ?? '',
                'country' => $addressData['country'] ?: 'IN',
            ],
        ];

        // Create and confirm PaymentIntent in ONE call
        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => $cost,
            'currency' => $currency,
            'customer' => $customer->id,
            'payment_method_data' => [
                'type' => 'card',
                'card' => [
                    'token' => $request->stripeToken,
                ],
            ],
            'confirmation_method' => 'automatic',
            'confirm' => true,
            'return_url' => $url,
            'setup_future_usage' => 'off_session',
            'description' => 'Payment for purchased product',
            'shipping' => $shippingDetails,
        ], [
            'idempotency_key' => uniqid('payment_', true),
        ]);

        return $paymentIntent;
    }

    /**
     * Extract customer data from User model, array, or object.
     */
    public function extractCustomerData($user): array
    {
        $data = $user instanceof User ? $user->toArray() : (array) $user;

        $firstName = \Arr::get($data, 'first_name');
        $lastName = \Arr::get($data, 'last_name');

        return [
            'name' => ($firstName || $lastName)
                ? trim($firstName.' '.$lastName)
                : \Arr::get($data, 'name'),

            'email' => \Arr::get($data, 'email'),

            'address' => [
                'line1' => \Arr::get($data, 'address'),
                'postal_code' => \Arr::get($data, 'zip'),
                'city' => \Arr::get($data, 'town') ?? \Arr::get($data, 'city'),
                'state' => \Arr::get($data, 'state'),
                'country' => \Arr::get($data, 'country'),
            ],
        ];
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
