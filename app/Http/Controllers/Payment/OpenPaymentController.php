<?php

namespace App\Http\Controllers\Payment;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\OpenPaymentRequest;
use App\Model\Payment\OpenPaymentOrder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Razorpay\Api\Api;

class OpenPaymentController extends Controller
{
    /**
     * Create order.
     */
    public function createOrder(OpenPaymentRequest $request)
    {
        try {
            $order = OpenPaymentOrder::create([
                'name' => $request->name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
                'country' => $request->country,
                'company' => $request->company,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'gateway' => $request->gateway,
                'description' => $request->description,
                'payment_status' => 'pending',
            ]);

            return successResponse('Order created successfully', ['order' => $order]);
        } catch (\Exception $e) {
            return errorResponse('Failed to create order: '.$e->getMessage());
        }
    }

    /**
     * Get Order Details.
     */
    public function getOrderDetails($id)
    {
        try {
            $order = OpenPaymentOrder::findOrFail($id);
            $apiKeys = ApiKey::first();

            if (! $apiKeys) {
                return errorResponse('Payment gateway configuration not found', 500);
            }

            return successResponse('', [
                'order' => $order,
                'rzp_key' => $apiKeys->rzp_key,
                'stripe_key' => $apiKeys->stripe_key,
            ]);
        } catch (ModelNotFoundException $e) {
            return errorResponse('Order not found', 404);
        } catch (\Exception $e) {
            return errorResponse('Failed to get order details: '.$e->getMessage());
        }
    }

    /**
     * Prepare gateway (Generate Intent/Order) called via AJAX.
     */
    public function preparePayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
        ]);

        try {
            $order = OpenPaymentOrder::findOrFail($request->order_id);

            // Check if already paid
            if ($order->isPaid()) {
                return errorResponse('This order has already been paid');
            }

            return match (strtolower($order->gateway)) {
                'razorpay' => $this->initializeRazorpay($order),
                'stripe' => $this->initializeStripe($request, $order),
                default => errorResponse('Invalid payment gateway')
            };
        } catch (ModelNotFoundException $e) {
            return errorResponse('Order not found', 404);
        } catch (\Exception $e) {
            return errorResponse('Failed to prepare payment: '.$e->getMessage());
        }
    }

    /**
     * Initialize Razorpay payment.
     */
    private function initializeRazorpay(OpenPaymentOrder $order)
    {
        try {
            $apiKeys = ApiKey::first();

            if (! $apiKeys || ! $apiKeys->rzp_key || ! $apiKeys->rzp_secret) {
                return errorResponse('Razorpay API keys not configured', 500);
            }

            $api = new Api($apiKeys->rzp_key, $apiKeys->rzp_secret);
            $amountInSmallestUnit = calculateUnitCost($order->currency, $order->amount);

            // Create Razorpay order
            $razorpayOrder = $api->order->create([
                'amount' => $amountInSmallestUnit,
                'currency' => $order->currency,
                'receipt' => 'OPEN_PAY_'.$order->id,
                'notes' => [
                    'order_id' => $order->id,
                    'customer' => $order->name,
                    'email' => $order->email,
                    'description' => $order->description ?? 'Open Payment',
                ],
            ]);

            // Update order with Razorpay order ID
            $order->update([
                'gateway_transaction_id' => $razorpayOrder['id'],  // Store Razorpay order_id
            ]);

            return successResponse('', [
                'gateway' => 'Razorpay',
                'order_id' => $order->id,
                'transaction_id' => $order->transaction_id,
                'razorpay_key' => $apiKeys->rzp_key,
                'razorpay_order' => $razorpayOrder['id'],
                'amount' => $amountInSmallestUnit,
                'currency' => $order->currency,
                'name' => $order->name,
                'email' => $order->email,
                'mobile' => $order->mobile,
                'description' => $order->description ?? 'Open Payment',
            ]);
        } catch (\Exception $e) {
            return errorResponse('Failed to initialize Razorpay: '.$e->getMessage());
        }
    }

    /**
     * Initialize Stripe payment using handlePayment from SettingsController.
     */
    private function initializeStripe(Request $request, OpenPaymentOrder $order)
    {
        try {
            $apiKeys = ApiKey::first();

            if (! $apiKeys || ! $apiKeys->stripe_key || ! $apiKeys->stripe_secret) {
                return errorResponse('Stripe API keys not configured', 500);
            }

            // Use handlePayment from Stripe SettingsController
            $stripeController = app(\App\Plugins\Stripe\Controllers\SettingsController::class);
            $paymentIntent = $stripeController->handlePayment(
                $request,
                $order->amount,
                $order->currency,
                url('/open-payment/stripe/callback?order_id='.$order->id),
                $order->toArray()
            );

            // Update order with payment details
            $order->update([
                'gateway_transaction_id' => $paymentIntent->id,  // Store Stripe payment_intent_id
                'payment_status' => $paymentIntent->status === 'succeeded' ? 'completed' : 'pending',
                'paid_at' => $paymentIntent->status === 'succeeded' ? now() : null,
            ]);

            // Check if payment succeeded or needs action
            if ($paymentIntent->status === 'succeeded') {
                return successResponse('Payment successful!', [
                    'gateway' => 'Stripe',
                    'order_id' => $order->id,
                    'transaction_id' => $order->transaction_id,
                    'status' => 'succeeded',
                    'gateway_transaction_id' => $paymentIntent->id,
                ]);
            } elseif ($paymentIntent->status === 'requires_action') {
                return successResponse('', [
                    'gateway' => 'Stripe',
                    'order_id' => $order->id,
                    'transaction_id' => $order->transaction_id,
                    'status' => 'requires_action',
                    'redirect_url' => $paymentIntent->next_action->redirect_to_url->url ?? null,
                ]);
            } else {
                return successResponse('', [
                    'gateway' => 'Stripe',
                    'order_id' => $order->id,
                    'transaction_id' => $order->transaction_id,
                    'status' => $paymentIntent->status,
                    'gateway_transaction_id' => $paymentIntent->id,
                ]);
            }
        } catch (\Exception $e) {
            return errorResponse('Failed to process Stripe payment: '.$e->getMessage());
        }
    }

    /**
     * Verify Razorpay payment (Client-side verification - backup for webhook).
     */
    public function verifyRazorpayPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:open_payment_orders,id',
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        try {
            $order = OpenPaymentOrder::findOrFail($request->order_id);

            // Skip if already completed (webhook may have processed it)
            if ($order->isPaid()) {
                return successResponse('Payment already processed!', ['order' => $order]);
            }

            $apiKeys = ApiKey::first();

            if (! $apiKeys || ! $apiKeys->rzp_key || ! $apiKeys->rzp_secret) {
                return errorResponse('Razorpay API keys not configured', 500);
            }

            $api = new Api($apiKeys->rzp_key, $apiKeys->rzp_secret);

            // Verify signature
            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ];

            $api->utility->verifyPaymentSignature($attributes);

            // Fetch payment details
            $payment = $api->payment->fetch($request->razorpay_payment_id);

            // Update order status
            $order->update([
                'payment_status' => 'completed',
                'gateway_transaction_id' => $request->razorpay_payment_id,  // Store Razorpay payment_id
                'paid_at' => now(),
            ]);

            return successResponse('Payment successful!', ['order' => $order]);
        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            $order = OpenPaymentOrder::find($request->order_id);
            if ($order && ! $order->isPaid()) {
                $order->update([
                    'payment_status' => 'failed',
                ]);
            }

            return errorResponse('Payment verification failed: Invalid signature.', 400);
        } catch (\Exception $e) {
            return errorResponse('Payment verification failed: '.$e->getMessage(), 500);
        }
    }

    /**
     * Verify Stripe payment (Client-side verification - backup for webhook).
     */
    public function verifyStripePayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:open_payment_orders,id',
            'payment_intent_id' => 'required|string',
        ]);

        try {
            $order = OpenPaymentOrder::findOrFail($request->order_id);

            // Skip if already completed (webhook may have processed it)
            if ($order->isPaid()) {
                return successResponse('Payment already processed!', ['order' => $order]);
            }

            $apiKeys = ApiKey::first();

            if (! $apiKeys || ! $apiKeys->stripe_secret) {
                return errorResponse('Stripe API keys not configured', 500);
            }

            \Stripe\Stripe::setApiKey($apiKeys->stripe_secret);

            // Retrieve payment intent
            $paymentIntent = \Stripe\PaymentIntent::retrieve($request->payment_intent_id);

            if ($paymentIntent->status === 'succeeded') {
                // Update order status
                $order->update([
                    'payment_status' => 'completed',
                    'paid_at' => now(),
                ]);

                return successResponse('Payment successful!', ['order' => $order]);
            } else {
                $order->update([
                    'payment_status' => 'failed',
                ]);

                return errorResponse('Payment not completed. Status: '.$paymentIntent->status, 400);
            }
        } catch (\Exception $e) {
            return errorResponse('Payment verification failed: '.$e->getMessage(), 500);
        }
    }

    /**
     * Handle Razorpay webhook for payment confirmation
     * Webhook URL: /open-payment/webhook/razorpay.
     */
    public function handleRazorpayWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');

        try {
            $apiKeys = ApiKey::first();
            $webhookSecret = config('open_payment.razorpay_webhook_secret');

            if (! $apiKeys || ! $apiKeys->rzp_key || ! $apiKeys->rzp_secret) {
                return errorResponse('Configuration error', 500);
            }

            // Verify webhook signature if secret is configured
            if ($webhookSecret && $signature) {
                $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
                if (! hash_equals($expectedSignature, $signature)) {
                    return errorResponse('Invalid signature', 400);
                }
            }

            $event = json_decode($payload, true);

            if (! $event || ! isset($event['event'])) {
                return errorResponse('Invalid payload', 400);
            }

            // Handle payment.captured event (successful payment)
            if ($event['event'] === 'payment.captured') {
                $paymentData = $event['payload']['payment']['entity'] ?? null;

                if (! $paymentData) {
                    return errorResponse('Payment data not found in webhook', 400);
                }

                $orderId = $paymentData['notes']['order_id'] ?? null;

                if ($orderId) {
                    $order = OpenPaymentOrder::find($orderId);

                    if ($order && ! $order->isPaid()) {
                        $order->update([
                            'payment_status' => 'completed',
                            'gateway_transaction_id' => $paymentData['id'],  // Store Razorpay payment_id
                            'paid_at' => now(),
                        ]);
                    }
                }
            }

            // Handle payment.failed event
            if ($event['event'] === 'payment.failed') {
                $paymentData = $event['payload']['payment']['entity'] ?? null;

                if ($paymentData) {
                    $orderId = $paymentData['notes']['order_id'] ?? null;

                    if ($orderId) {
                        $order = OpenPaymentOrder::find($orderId);

                        if ($order && ! $order->isPaid()) {
                            $order->update([
                                'payment_status' => 'failed',
                            ]);
                        }
                    }
                }
            }

            return successResponse('Webhook processed successfully');
        } catch (\Exception $e) {
            return errorResponse('Webhook processing failed: '.$e->getMessage(), 500);
        }
    }

    /**
     * Handle Stripe webhook for async payment confirmation
     * Webhook URL: /open-payment/webhook/stripe.
     */
    public function handleStripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $apiKeys = ApiKey::first();
            $webhookSecret = 'whsec_75053c0f9cf8fde3718f5ddb032578abd57e778109b12f9c2d4182d7b0b2ffb3';

            if (! $apiKeys || ! $apiKeys->stripe_secret) {
                return errorResponse('Configuration error', 500);
            }

            \Stripe\Stripe::setApiKey($apiKeys->stripe_secret);

            // Verify webhook signature if secret is configured
            if ($webhookSecret && $sigHeader) {
                try {
                    $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
                } catch (\Stripe\Exception\SignatureVerificationException $e) {
                    return errorResponse('Invalid signature', 400);
                }
            } else {
                $event = json_decode($payload, true);
            }

            $eventType = is_array($event) ? ($event['type'] ?? null) : $event->type;
            $eventData = is_array($event) ? ($event['data']['object'] ?? null) : $event->data->object;

            if (! $eventType || ! $eventData) {
                return errorResponse('Invalid payload', 400);
            }

            // Handle the payment_intent.succeeded event
            if ($eventType === 'payment_intent.succeeded') {
                $paymentIntent = is_array($eventData) ? $eventData : $eventData->toArray();
                $orderId = $paymentIntent['metadata']['order_id'] ?? null;

                if ($orderId) {
                    $order = OpenPaymentOrder::find($orderId);

                    if ($order && ! $order->isPaid()) {
                        $order->update([
                            'payment_status' => 'completed',
                            'paid_at' => now(),
                        ]);
                    }
                }
            }

            // Handle payment_intent.payment_failed event
            if ($eventType === 'payment_intent.payment_failed') {
                $paymentIntent = is_array($eventData) ? $eventData : $eventData->toArray();
                $orderId = $paymentIntent['metadata']['order_id'] ?? null;

                if ($orderId) {
                    $order = OpenPaymentOrder::find($orderId);

                    if ($order && ! $order->isPaid()) {
                        $order->update([
                            'payment_status' => 'failed',
                        ]);
                    }
                }
            }

            return successResponse('Webhook processed successfully');
        } catch (\Exception $e) {
            return errorResponse('Webhook processing failed: '.$e->getMessage(), 500);
        }
    }

    /**
     * List all open payment orders (Admin).
     */
    public function listOrders(Request $request)
    {
        try {
            $query = OpenPaymentOrder::query();

            // Filter by payment status
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('payment_status', $request->status);
            }

            // Filter by gateway
            if ($request->has('gateway') && $request->gateway !== 'all') {
                $query->where('gateway', $request->gateway);
            }

            // Search functionality
            if ($request->has('search') && ! empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('transaction_id', 'like', "%{$search}%");
                });
            }

            // Date range filter
            if ($request->has('from_date') && $request->from_date) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }

            if ($request->has('to_date') && $request->to_date) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            $orders = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 20));

            return successResponse('', ['orders' => $orders]);
        } catch (\Exception $e) {
            return errorResponse('Failed to fetch orders: '.$e->getMessage());
        }
    }

    /**
     * Get order by ID (Admin).
     */
    public function getOrder($id)
    {
        try {
            $order = OpenPaymentOrder::findOrFail($id);

            return successResponse('', ['order' => $order]);
        } catch (ModelNotFoundException $e) {
            return errorResponse('Order not found', 404);
        } catch (\Exception $e) {
            return errorResponse('Failed to get order: '.$e->getMessage());
        }
    }

    /**
     * Handle Stripe 3D Secure callback redirect.
     */
    public function handleStripeCallback(Request $request)
    {
        $orderId = $request->query('order_id');
        $paymentIntentId = $request->query('payment_intent');

        if (! $orderId) {
            return redirect('/open-payment')->with('error', 'Order ID not found');
        }

        try {
            $order = OpenPaymentOrder::findOrFail($orderId);

            // If already paid, redirect to success
            if ($order->isPaid()) {
                return redirect('/open-payment?order_id='.$orderId.'&status=success');
            }

            $apiKeys = ApiKey::first();

            if (! $apiKeys || ! $apiKeys->stripe_secret) {
                return redirect('/open-payment?order_id='.$orderId.'&status=error&message=Configuration error');
            }

            \Stripe\Stripe::setApiKey($apiKeys->stripe_secret);

            // Get payment intent ID from order if not in query
            $paymentIntentId = $paymentIntentId ?: $order->transaction_id;

            if (! $paymentIntentId) {
                return redirect('/open-payment?order_id='.$orderId.'&status=error&message=Payment not found');
            }

            // Retrieve payment intent to check status
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

            if ($paymentIntent->status === 'succeeded') {
                // Update order as paid
                $order->update([
                    'payment_status' => 'completed',
                    'paid_at' => now(),
                ]);

                return redirect('/open-payment?order_id='.$orderId.'&status=success');
            } elseif ($paymentIntent->status === 'requires_payment_method') {
                // Payment failed - card was declined after 3DS
                $order->update([
                    'payment_status' => 'failed',
                ]);

                return redirect('/open-payment?order_id='.$orderId.'&status=failed&message=Payment was declined');
            } else {
                // Other status - still processing or requires more action
                return redirect('/open-payment?order_id='.$orderId.'&status=pending&message=Payment is processing');
            }
        } catch (ModelNotFoundException $e) {
            return redirect('/open-payment?status=error&message=Order not found');
        } catch (\Exception $e) {
            return redirect('/open-payment?order_id='.$orderId.'&status=error&message='.urlencode($e->getMessage()));
        }
    }
}
