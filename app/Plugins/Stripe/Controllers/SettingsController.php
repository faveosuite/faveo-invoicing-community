<?php

namespace App\Plugins\Stripe\Controllers;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Services\Payment\ProcessingFee;
use App\Traits\Payment\PostPaymentHandle;
use App\User;
use Cartalyst\Stripe\Laravel\Facades\Stripe;
use Illuminate\Http\Request;
use Stripe\StripeClient;

/**
 * Stripe admin settings + shared Stripe helpers.
 *
 * The SPA invoice-payment flow lives in the standalone payment package
 * (App\Plugins\Payment, via App\Services\Payment\InvoicePaymentService). This
 * controller keeps:
 *  - admin settings: read (getSettings) and update (updateApiKey) the API keys;
 *  - handlePayment(): a server-confirmed PaymentIntent used by the auto-renew
 *    (Front\ClientController) and open-payment (OpenPaymentController) flows;
 *  - handleStripeAutoPay(): subscription creation used by SubscriptionController.
 */
class SettingsController extends Controller
{
    use PostPaymentHandle;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => []]);
    }

    public function getSettings()
    {
        try {
            $keys   = ApiKey::select('stripe_key', 'stripe_secret', 'stripe_webhook_secret')->first();
            $status = \App\Model\Common\StatusSetting::select('stripe_auto_renewal')->first();

            return successResponse('', [
                'stripe_key'      => $keys->stripe_key ?? '',
                'stripe_secret'   => $keys->stripe_secret ?? '',
                'webhook_secret'  => $keys->stripe_webhook_secret ?? '',
                'processing_fee'  => (string) ProcessingFee::percent('stripe'),
                'auto_renewal'    => (bool) ($status->stripe_auto_renewal ?? false),
                'webhook_url'     => url('webhook/stripe'),
            ]);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function updateApiKey(Request $request)
    {
        $request->validate([
            'stripe_secret'  => 'required|string',
            'stripe_key'     => 'required|string',
            'webhook_secret' => 'nullable|string',
            'processing_fee' => 'nullable|numeric|min:0|max:100',
            'auto_renewal'   => 'nullable|boolean',
        ], [
            'stripe_secret.required' => __('message.stripe_secret_required'),
            'stripe_key.required'    => __('message.stripe_key_required'),
        ]);

        try {
            $stripe = Stripe::make($request->input('stripe_secret'));
            $stripe->customers()->create(['description' => 'Test Customer to Validate Secret Key']);

            ApiKey::find(1)->update([
                'stripe_secret'          => $request->input('stripe_secret'),
                'stripe_key'             => $request->input('stripe_key'),
                'stripe_webhook_secret'  => $request->input('webhook_secret'),
            ]);

            ProcessingFee::store('stripe', (float) $request->input('processing_fee', 0));

            if ($request->has('auto_renewal')) {
                $enabling = $request->boolean('auto_renewal');
                \App\Model\Common\StatusSetting::find(1)->update(['stripe_auto_renewal' => $enabling ? 1 : 0]);

                if (! $enabling) {
                    \App\Jobs\CancelGatewaySubscriptionsJob::dispatch('stripe');
                }
            }

            return successResponse(__('message.stripe_settings_updated_successfully'));
        } catch (\Cartalyst\Stripe\Exception\UnauthorizedException $e) {
            return errorResponse($e->getMessage());
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    /**
     * Create and confirm a PaymentIntent from a card token in a single call.
     *
     * Shared by the auto-renew (Front\ClientController) and open-payment
     * (OpenPaymentController) flows, which collect a card token client-side and
     * need a server-confirmed charge with 3-D Secure support via $url.
     */
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

        $customerData = $this->extractCustomerData($user);
        $customer = $stripe->customers->create($customerData);

        // Shipping details for Indian export compliance.
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
     * Extract Stripe customer data from a User model, array, or object.
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

    /**
     * Create a recurring Stripe subscription for autopay.
     *
     * Thin adapter over the centralized {@see \App\Services\Payment\SubscriptionService}
     * (which drives the payment package's StripeGateway). Returns a
     * {@see \App\Plugins\Payment\Dto\SubscriptionResult} — callers read ->status,
     * ->id and ->raw['latest_invoice']. $unit_cost is already in minor units.
     */
    public function handleStripeAutoPay($stripe_payment_details, $product_details, $unit_cost, $currency, $plan)
    {
        try {
            return app(\App\Services\Payment\SubscriptionService::class)->createSubscription('Stripe', new \App\Plugins\Payment\Dto\SubscriptionRequest(
                amountMinor: (int) $unit_cost,
                currency: $currency,
                intervalDays: (int) $plan->days,
                planName: $product_details->name,
                paymentMethodReference: $stripe_payment_details->payment_intent_id,
            ));
        } catch (\Exception $e) {
            \Logger::exception($e);
        }
    }
}
