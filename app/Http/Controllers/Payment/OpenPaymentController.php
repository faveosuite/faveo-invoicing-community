<?php

namespace App\Http\Controllers\Payment;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\OpenPaymentRequest;
use App\Model\Payment\OpenPaymentOrder;
use App\Plugins\Payment\Exceptions\SignatureVerificationException;
use App\Services\Payment\OpenPaymentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

/**
 * Open payments — standalone, ad-hoc charges not tied to an invoice.
 *
 * HTTP + CRUD layer. All gateway work (open a payment, verify a callback,
 * authenticate a webhook) is delegated to {@see OpenPaymentService}, which drives
 * the same {@see \App\Services\Payment\PaymentService} / payment package the rest
 * of the application uses — no raw gateway SDK calls live here anymore.
 */
class OpenPaymentController extends Controller
{
    public function __construct(private readonly OpenPaymentService $payments)
    {
    }

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
     * Get Order Details (with gateway publishable keys for the client).
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
                'stripe_key' => $this->payments->publishableKey(),
            ]);
        } catch (ModelNotFoundException $e) {
            return errorResponse('Order not found', 404);
        } catch (\Exception $e) {
            return errorResponse('Failed to get order details: '.$e->getMessage());
        }
    }

    /**
     * Open the payment on the order's gateway and return the client SDK config.
     *
     * Stripe → embedded Checkout Session {client_secret, session_id, publishable_key};
     * Razorpay → Checkout options {key, order_id, amount, ...} for `new Razorpay()`.
     */
    public function preparePayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
        ]);

        try {
            $order = OpenPaymentOrder::findOrFail($request->order_id);

            if ($order->isPaid()) {
                return errorResponse('This order has already been paid');
            }

            return successResponse('', $this->payments->start($order)->clientConfig);
        } catch (ModelNotFoundException $e) {
            return errorResponse('Order not found', 404);
        } catch (\Throwable $e) {
            return errorResponse('Failed to prepare payment: '.$e->getMessage());
        }
    }

    /**
     * Verify a Razorpay Checkout handler response (client-side verification).
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

            $paid = $this->payments->confirm($order, $request->only([
                'razorpay_payment_id', 'razorpay_order_id', 'razorpay_signature',
            ]));

            return $paid
                ? successResponse('Payment successful!', ['order' => $order->fresh()])
                : errorResponse('Payment verification failed.', 400);
        } catch (SignatureVerificationException $e) {
            return errorResponse('Payment verification failed: Invalid signature.', 400);
        } catch (\Exception $e) {
            return errorResponse('Payment verification failed: '.$e->getMessage(), 500);
        }
    }

    /**
     * Verify a completed Stripe Checkout Session (client-side verification).
     */
    public function verifyStripePayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:open_payment_orders,id',
            'session_id' => 'required|string',
        ]);

        try {
            $order = OpenPaymentOrder::findOrFail($request->order_id);

            $paid = $this->payments->confirm($order, ['session_id' => $request->session_id]);

            return $paid
                ? successResponse('Payment successful!', ['order' => $order->fresh()])
                : errorResponse('Payment not completed.', 400);
        } catch (\Exception $e) {
            return errorResponse('Payment verification failed: '.$e->getMessage(), 500);
        }
    }

    /**
     * List all open payment orders (Admin).
     */
    public function listOrders(Request $request)
    {
        try {
            $query = OpenPaymentOrder::query();

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('payment_status', $request->status);
            }

            if ($request->has('gateway') && $request->gateway !== 'all') {
                $query->where('gateway', $request->gateway);
            }

            if ($request->has('search') && ! empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('transaction_id', 'like', "%{$search}%");
                });
            }

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
     * Stripe return URL.
     *
     * Embedded Checkout completes in-page (redirect_on_completion = never), so the
     * gateway no longer redirects here; this remains only as a safety net that
     * reflects the order's current status back to the open-payment page.
     */
    public function handleStripeCallback(Request $request)
    {
        $orderId = $request->query('order_id');

        if (! $orderId || ! $order = OpenPaymentOrder::find($orderId)) {
            return redirect('/open-payment?status=error&message=Order not found');
        }

        $status = $order->isPaid() ? 'success' : ($order->isFailed() ? 'failed' : 'pending');

        return redirect('/open-payment?order_id='.$orderId.'&status='.$status);
    }
}
