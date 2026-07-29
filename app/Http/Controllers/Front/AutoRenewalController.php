<?php

namespace App\Http\Controllers\Front;

use App\Auto_renewal;
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Controller;
use App\Model\Order\Order;
use App\Model\Payment\Currency;
use App\Model\Product\Subscription;
use App\Plugins\Payment\Dto\Customer as PaymentCustomer;
use App\Plugins\Payment\Dto\PaymentRequest as GatewayPaymentRequest;
use App\Services\Payment\AutoRenewalActivationService;
use App\Services\Payment\PaymentService;
use App\Services\Payment\SubscriptionService;
use App\User;
use Auth;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Logger;

class AutoRenewalController extends Controller
{
    public function __construct(
        private readonly PaymentService $payments,
        private readonly AutoRenewalActivationService $activation,
    ) {
    }

    /**
     * Create a Stripe PaymentIntent for card verification.
     * Returns client_secret + payment_intent_id + publishable_key.
     * POST auto-renewal/{order}/stripe/session.
     */
    public function stripeSession(Request $request, int $order): JsonResponse
    {
        $order = $this->authorizedOrder($order);
        /** @var User $authUser */
        $authUser = Auth::user();
        $currency = getCurrencyForClient($authUser->country);
        $amount = getMinimumAmountForPayments($currency, 'stripe');
        $symbol = Currency::where('code', $currency)->value('symbol') ?? $currency;

        try {
            // Use a unique nonce in the reference so each modal open creates a
            // fresh PaymentIntent — prevents idempotency returning an already-
            // confirmed PI when the user retries after a partial failure.
            $paymentRequest = $this->buildRequest($order, 'stripe', uniqid('', more_entropy: true));
            $session = $this->payments->startCardPayment('Stripe', $paymentRequest);

            return successResponse('', array_merge($session->clientConfig, [
                'display_amount' => currencyFormat($amount, $currency, includeSymbol: false),
                'currency_symbol' => $symbol,
            ]));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Verify the confirmed PaymentIntent, refund the verification charge,
     * and save the payment method for future auto-renewal.
     * POST auto-renewal/{order}/stripe/confirm.
     */
    public function stripeConfirm(Request $request, int $order): JsonResponse
    {
        $order = $this->authorizedOrder($order);
        try {
            $paymentIntentId = $request->input('payment_intent');
            if (! $paymentIntentId) {
                return errorResponse(__('message.something_went_wrong'));
            }

            $result = $this->payments->capture('Stripe', ['payment_intent' => $paymentIntentId]);
            if (! $result->paid) {
                return errorResponse(__('message.payment_declined_try_other_gateway'));
            }

            try {
                $this->payments->manager()->gateway('Stripe')->refundPayment($paymentIntentId);
            } catch (Exception $e) {
                // Already refunded on a prior attempt — log and continue.
                $this->logPayment($order, 'stripe', 'refund_skipped', $e->getMessage());
            }

            $this->saveRenewal($order, 'stripe', $paymentIntentId);

            return successResponse(__('message.card_details_updated_successfully'));
        } catch (Exception $exception) {
            $this->logPayment($order, 'stripe', 'failed', $exception->getMessage());

            return errorResponse(__('message.something_different_payment'));
        }
    }

    /**
     * Create the real Razorpay Subscription up front and return the Checkout
     * config for the browser to authorize it directly — one payment, not a
     * throwaway verification charge followed by a second emailed step.
     * POST auto-renewal/{order}/razorpay/order.
     */
    public function razorpayOrder(Request $request, int $order): JsonResponse
    {
        $order = $this->authorizedOrder($order);
        try {
            /** @var User $user */
            $user = Auth::user();
            $config = $this->activation->prepareRazorpaySubscriptionForAuthorization($order, $user);

            return successResponse('', $config);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Verify the Razorpay subscription-authorization signature and activate
     * auto-renewal immediately — no separate refund step, since this payment
     * *is* the mandate authorization, not a throwaway verification charge.
     * POST auto-renewal/{order}/razorpay/confirm.
     */
    public function razorpayConfirm(Request $request, int $order): JsonResponse
    {
        $order = $this->authorizedOrder($order);
        try {
            // The signature only proves this (payment_id, subscription_id)
            // pair is a real, Razorpay-issued authorization — not that it's
            // for *this* order's subscription. Without this check, a valid
            // authorization from any order the user owns could be replayed
            // here to mark a different order "active" while its real
            // subscription (the correct plan/price, created by razorpayOrder())
            // sits forever un-authorized.
            $subscriptionId = $request->input('razorpay_subscription_id');
            $expectedSubscriptionId = Subscription::where('order_id', $order->id)->value('subscribe_id');
            if (! $subscriptionId || $subscriptionId !== $expectedSubscriptionId) {
                return errorResponse(__('message.something_went_wrong'));
            }

            $this->payments->capture('Razorpay', $request->only([
                'razorpay_subscription_id',
                'razorpay_payment_id',
                'razorpay_signature',
            ]));

            /** @var User $user */
            $user = Auth::user();
            $this->activation->activateConfirmedRazorpaySubscription($order, $user, $request->input('razorpay_payment_id'));

            return successResponse(__('message.card_details_updated_successfully'));
        } catch (Exception $exception) {
            $this->logPayment($order, 'razorpay', 'failed', $exception->getMessage());

            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Disable auto-renewal for an order, cancelling the active subscription
     * on the gateway if one exists.
     * POST auto-renewal/{order}/disable.
     */
    public function disable(Request $request, int $order): JsonResponse
    {
        $order = $this->authorizedOrder($order, allowAdmin: true);
        try {
            $subscription = Subscription::where('order_id', $order->id)->firstOrFail();
            $this->cancelSubscription($subscription);

            return successResponse(__('message.auto_subscription_disabled'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function authorizedOrder(int $orderId, bool $allowAdmin = false): Order
    {
        $order = Order::findOrFail($orderId);
        abort_if(! authorizeOwnership($order->client, $allowAdmin), 403, __('message.unauthorized_action'));

        return $order;
    }

    private function buildRequest(Order $order, string $gateway, string $nonce = ''): GatewayPaymentRequest
    {
        /** @var User $user */
        $user = Auth::user();
        $currency = getCurrencyForClient($user->country);

        $amount = getMinimumAmountForPayments($currency, $gateway);

        $reference = $nonce !== '' && $nonce !== '0' ? 'renewal_'.$order->id.'_'.$nonce : 'renewal_'.$order->id;

        return new GatewayPaymentRequest(
            amount: $amount,
            currency: $currency,
            reference: $reference,
            customer: new PaymentCustomer(
                name: trim(($user->first_name ?? '').' '.($user->last_name ?? '')),
                email: $user->email,
                phone: ($user->mobile_code ? '+'.$user->mobile_code : '').$user->mobile,
                line1: $user->address ?? '',
                city: $user->town ?? '',
                state: $user->state ?? '',
                postalCode: $user->zip ?? '',
                country: $user->country ?? '',
            ),
            description: 'Card verification for auto-renewal — Order '.$order->number,
            metadata: ['order_id' => (int) $order->id, 'user_id' => (int) $user->id],
            saveForFutureUse: true,
        );
    }

    private function saveRenewal(Order $order, string $gateway, string $paymentRef): void
    {
        /** @var User $authUser2 */
        $authUser2 = Auth::user();
        $this->activation->activate($order, $authUser2, $gateway, $paymentRef);
    }

    private function cancelSubscription(Subscription $subscription): void
    {
        $service = resolve(SubscriptionService::class);

        if ($subscription->subscribe_id) {
            $gateway = $subscription->rzp_subscription ? 'Razorpay' : 'Stripe';
            try {
                $service->cancelSubscription($gateway, $subscription->subscribe_id);
            } catch (Exception $e) {
                // Already cancelled at gateway — continue to reset local state
                Logger::exception(new Exception(sprintf('Subscription cancel skipped [%s]: ', $gateway).$e->getMessage(), previous: $e));
            }
        }

        // Without this, AutoRenewalActivationService::activate()'s idempotency
        // check (keyed on an Auto_renewal row existing for this order+gateway)
        // would silently no-op forever on any future re-enable — no flags
        // flipped, no mail sent — since it can't distinguish "already active"
        // from "was active once, since disabled".
        Auto_renewal::where('order_id', $subscription->order_id)->delete();

        // Query-builder update, not $subscription->update() — subscribe_id and
        // rzp_subscription aren't in Subscription::$fillable, so a model-instance
        // update() silently drops them and this reset would never actually happen.
        Subscription::where('id', $subscription->id)->update([
            'is_subscribed' => 0,
            'autoRenew_status' => 0,
            'rzp_subscription' => 0,
            'subscribe_id' => '',
        ]);
    }

    private function logPayment(Order $order, string $gateway, string $status, string $note = ''): void
    {
        /** @var User $authUser3 */
        $authUser3 = Auth::user();
        new PhpMailController()->payment_log(
            $authUser3->email, $gateway, $status, $order->number, $note ?: null, 0, 'Payment method updated'
        );
    }
}
