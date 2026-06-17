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
use App\Services\Payment\PaymentService;
use App\Services\Payment\SubscriptionService;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Logger;

class AutoRenewalController extends Controller
{
    public function __construct(private readonly PaymentService $payments)
    {
    }

    /**
     * Create a Stripe PaymentIntent for card verification.
     * Returns client_secret + payment_intent_id + publishable_key.
     * POST auto-renewal/{order}/stripe/session.
     */
    public function stripeSession(Request $request, int $order)
    {
        $order = $this->authorizedOrder($order);
        $currency = getCurrencyForClient(Auth::user()->country);
        $amount = getMinimumAmountForPayments($currency, 'stripe');
        $symbol = Currency::where('code', $currency)->value('symbol') ?? $currency;

        try {
            // Use a unique nonce in the reference so each modal open creates a
            // fresh PaymentIntent — prevents idempotency returning an already-
            // confirmed PI when the user retries after a partial failure.
            $paymentRequest = $this->buildRequest($order, 'stripe', uniqid('', more_entropy: true));
            $session = $this->payments->startCardPayment('Stripe', $paymentRequest);

            return successResponse('', array_merge($session->clientConfig, [
                'display_amount' => $amount,
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
    public function stripeConfirm(Request $request, int $order)
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
     * Create a Razorpay Order and return the Checkout config for the browser.
     * POST auto-renewal/{order}/razorpay/order.
     */
    public function razorpayOrder(Request $request, int $order)
    {
        $order = $this->authorizedOrder($order);
        try {
            $session = $this->payments->start('Razorpay', $this->buildRequest($order, 'razorpay'));

            return successResponse('', $session->clientConfig);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Verify the Razorpay signature, refund the verification charge,
     * and save the payment method for future auto-renewal.
     * POST auto-renewal/{order}/razorpay/confirm.
     */
    public function razorpayConfirm(Request $request, int $order)
    {
        $order = $this->authorizedOrder($order);
        try {
            $result = $this->payments->capture('Razorpay', $request->only([
                'razorpay_order_id',
                'razorpay_payment_id',
                'razorpay_signature',
            ]));

            $this->payments->manager()->gateway('Razorpay')->refundPayment(
                $request->input('razorpay_payment_id')
            );

            $this->saveRenewal($order, 'razorpay', $request->input('razorpay_payment_id'));

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
    public function disable(Request $request, int $order)
    {
        $order = $this->authorizedOrder($order);
        try {
            $subscription = Subscription::where('order_id', $order->id)->firstOrFail();
            $this->cancelSubscription($subscription);

            return successResponse(__('message.auto_subscription_disabled'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function authorizedOrder(int $orderId): Order
    {
        $order = Order::findOrFail($orderId);
        abort_if(! authorizeOwnership($order->client), 403, __('message.unauthorized_action'));

        return $order;
    }

    private function buildRequest(Order $order, string $gateway, string $nonce = ''): GatewayPaymentRequest
    {
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
        );
    }

    private function saveRenewal(Order $order, string $gateway, string $paymentRef): void
    {
        Auto_renewal::create([
            'user_id' => Auth::user()->id,
            'customer_id' => $paymentRef,
            'payment_method' => $gateway,
            'order_id' => $order->id,
            'payment_intent_id' => $paymentRef,
        ]);

        $gatewayColumn = $gateway === 'razorpay' ? 'rzp_subscription' : 'autoRenew_status';
        Subscription::where('order_id', $order->id)->update([
            'is_subscribed' => '1',
            $gatewayColumn => '1',
        ]);

        $this->logPayment($order, $gateway, 'success');
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
                Logger::warning(sprintf('Subscription cancel skipped [%s]: ', $gateway).$e->getMessage());
            }
        }

        $subscription->update([
            'is_subscribed' => 0,
            'autoRenew_status' => 0,
            'rzp_subscription' => 0,
            'subscribe_id' => '',
        ]);
    }

    private function logPayment(Order $order, string $gateway, string $status, string $note = ''): void
    {
        new PhpMailController()->payment_log(
            Auth::user()->email, $gateway, $status, $order->number, $note ?: null, 0, 'Payment method updated'
        );
    }
}
