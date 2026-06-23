<?php

namespace App\Http\Controllers\Payment;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\OpenPaymentRequest;
use App\Model\Common\Country;
use App\Model\Common\Setting;
use App\Model\Payment\Currency;
use App\Model\Payment\OpenPaymentOrder;
use App\Model\Plugin;
use App\Plugins\Payment\Exceptions\SignatureVerificationException;
use App\Services\Payment\OpenPaymentService;
use App\Services\Payment\PaymentService;
use DB;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Open payments — standalone, ad-hoc charges not tied to an invoice.
 *
 * HTTP + CRUD layer. All gateway work (open a payment, verify a callback,
 * authenticate a webhook) is delegated to {@see OpenPaymentService}, which drives
 * the same {@see PaymentService} / payment package the rest
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
    public function createOrder(OpenPaymentRequest $request): JsonResponse
    {
        try {
            // Lock fee server-side — client cannot manipulate it
            $feeRate = (float) (DB::table(strtolower($request->gateway))->value('processing_fee') ?? 0);
            $baseAmount = round((float) $request->amount, 2);
            $fee = round($baseAmount * $feeRate / 100, 2);
            $total = round($baseAmount + $fee, 2);  // gateway always reads this directly

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
                'amount' => $total,       // pre-calculated total — gateway charges this directly
                'base_amount' => $baseAmount,  // user-entered amount — for display/audit only
                'processing_fee' => $fee,
                'processing_fee_rate' => $feeRate,
                'currency' => $request->currency,
                'gateway' => $request->gateway,
                'description' => $request->description,
                'payment_status' => 'pending',
            ]);

            return successResponse('Order created successfully', ['order' => $order]);
        } catch (Exception $exception) {
            return errorResponse('Failed to create order: '.$exception->getMessage());
        }
    }

    /**
     * Get Order Details (with gateway publishable keys for the client).
     */
    public function getOrderDetails(mixed $id): JsonResponse
    {
        try {
            /** @var OpenPaymentOrder $order */
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
        } catch (ModelNotFoundException) {
            return errorResponse('Order not found', 404);
        } catch (Exception $e) {
            return errorResponse('Failed to get order details: '.$e->getMessage());
        }
    }

    /**
     * Open the payment on the order's gateway and return the client SDK config.
     *
     * Stripe → embedded Checkout Session {client_secret, session_id, publishable_key};
     * Razorpay → Checkout options {key, order_id, amount, ...} for `new Razorpay()`.
     */
    public function preparePayment(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => ['required', 'integer'],
        ]);

        try {
            /** @var OpenPaymentOrder $order */
            $order = OpenPaymentOrder::findOrFail($request->order_id);

            if ($order->isPaid()) {
                return errorResponse('This order has already been paid');
            }

            return successResponse('', $this->payments->start($order)->clientConfig);
        } catch (ModelNotFoundException) {
            return errorResponse('Order not found', 404);
        } catch (Throwable $e) {
            return errorResponse('Failed to prepare payment: '.$e->getMessage());
        }
    }

    /**
     * Create a Stripe PaymentIntent for the custom card UI and return its client
     * secret + publishable key. The browser confirms the card directly against
     * the PaymentIntent; the server verifies the result via verifyStripePayment.
     */
    public function stripeCardSession(Request $request): JsonResponse
    {
        $request->validate(['order_id' => ['required', 'exists:open_payment_orders,id']]);

        try {
            /** @var OpenPaymentOrder $order */
            $order = OpenPaymentOrder::findOrFail($request->order_id);

            if ($order->isPaid()) {
                return errorResponse('This order has already been paid');
            }

            $session = $this->payments->startCardPayment($order);

            $order->update(['gateway_transaction_id' => $session->id]);

            return successResponse('', $session->clientConfig);
        } catch (Throwable $throwable) {
            return errorResponse('Failed to create card session: '.$throwable->getMessage());
        }
    }

    /**
     * Verify a Razorpay Checkout handler response (client-side verification).
     */
    public function verifyRazorpayPayment(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => ['required', 'exists:open_payment_orders,id'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        try {
            /** @var OpenPaymentOrder $order */
            $order = OpenPaymentOrder::findOrFail($request->order_id);

            $paid = $this->payments->confirm($order, $request->only([
                'razorpay_payment_id', 'razorpay_order_id', 'razorpay_signature',
            ]));

            return $paid
                ? successResponse('Payment successful!', ['order' => $order->fresh()])
                : errorResponse('Payment verification failed.', 400);
        } catch (SignatureVerificationException) {
            return errorResponse('Payment verification failed: Invalid signature.', 400);
        } catch (Exception $e) {
            return errorResponse('Payment verification failed: '.$e->getMessage(), 500);
        }
    }

    /**
     * Verify a completed Stripe PaymentIntent (custom card UI).
     */
    public function verifyStripePayment(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => ['required', 'exists:open_payment_orders,id'],
            'payment_intent_id' => ['required', 'string'],
        ]);

        try {
            /** @var OpenPaymentOrder $order */
            $order = OpenPaymentOrder::findOrFail($request->order_id);

            $paid = $this->payments->confirm($order, ['payment_intent' => $request->payment_intent_id]);

            return $paid
                ? successResponse('Payment successful!', ['order' => $order->fresh()])
                : errorResponse('Payment not completed.', 400);
        } catch (Exception $exception) {
            return errorResponse('Payment verification failed: '.$exception->getMessage(), 500);
        }
    }

    /**
     * Detect country from the user's IP using the installed GeoIP package.
     */
    public function detectCountry(Request $request): JsonResponse
    {
        $location = getLocation($request->ip());
        $countryCode = $location->iso_code ?? null;

        $row = $countryCode
            ? Country::where('country_code_char2', $countryCode)
                ->first(['country_id', 'country_name', 'country_code_char2'])
            : null;

        $country = $row ? [
            'id' => $row->country_id,
            'name' => $row->country_name,
            'code' => $row->country_code_char2,
        ] : null;

        return successResponse('', ['country' => $country]);
    }

    /**
     * Calculate totals server-side — called by the review page before order creation.
     * Returns base_amount, processing_fee, processing_fee_rate, total.
     */
    public function calculate(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'gateway' => ['required', 'string'],
        ]);

        $feeRate = (float) (DB::table(strtolower($request->gateway))->value('processing_fee') ?? 0);
        $baseAmount = round((float) $request->amount, 2);
        $fee = round($baseAmount * $feeRate / 100, 2);
        $total = round($baseAmount + $fee, 2);

        return successResponse('', [
            'base_amount' => $baseAmount,
            'processing_fee' => $fee,
            'processing_fee_rate' => $feeRate,
            'total' => $total,
        ]);
    }

    /**
     * Return enabled gateways (with processing fee) and active currencies (with symbol).
     */
    public function getConfig(): JsonResponse
    {
        $gatewayNames = Plugin::where('status', 1)
            ->whereIn('name', ['Stripe', 'Razorpay'])
            ->pluck('name');

        $gateways = $gatewayNames->map(function ($name): array {
            $table = strtolower($name);
            $fee = DB::table($table)->value('processing_fee');

            return ['name' => $name, 'processing_fee' => (float) ($fee ?? 0)];
        })->values();

        $currencies = Currency::where('status', 1)
            ->orderBy('code')
            ->get(['code', 'symbol', 'name']);

        $appTitle = Setting::find(1)->title ?? config('app.name');

        return successResponse('', ['gateways' => $gateways, 'currencies' => $currencies, 'app_title' => $appTitle]);
    }

    /**
     * List all open payment orders (Admin).
     * Accepts both DataTable params (search-query, sort-field, sort-order, limit)
     * and legacy params (search, status, gateway, from_date, to_date, per_page).
     * Returns the paginator directly so the frontend DataTable default adapter works.
     */
    public function listOrders(Request $request): JsonResponse
    {
        try {
            $query = OpenPaymentOrder::query();

            $search = $request->input('search-query') ?: $request->input('search');
            if ($search) {
                $query->where(function (Builder $q) use ($search): void {
                    $q->where('name', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('email', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('company', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('transaction_id', 'like', sprintf('%%%s%%', $search));
                });
            }

            if (($status = $request->input('status')) && $status !== 'all') {
                $query->where('payment_status', $status);
            }

            if (($gateway = $request->input('gateway')) && $gateway !== 'all') {
                $query->where('gateway', $gateway);
            }

            if ($from = $request->input('from_date')) {
                $query->whereDate('created_at', '>=', $from);
            }

            if ($to = $request->input('to_date')) {
                $query->whereDate('created_at', '<=', $to);
            }

            $allowed = ['name', 'email', 'amount', 'currency', 'gateway', 'payment_status', 'created_at'];
            $sortField = $request->input('sort-field', 'created_at');
            $sortOrder = $request->input('sort-order', 'desc');
            if (! in_array($sortField, $allowed)) {
                $sortField = 'created_at';
            }

            $perPage = (int) ($request->input('limit') ?: $request->input('per_page', 10));

            $orders = $query
                ->select('open_payment_orders.*')
                ->leftJoin('currencies', 'open_payment_orders.currency', '=', 'currencies.code')
                ->addSelect('currencies.symbol as currency_symbol')
                ->orderBy('open_payment_orders.'.$sortField, $sortOrder === 'asc' ? 'asc' : 'desc')
                ->paginate($perPage);

            return successResponse('', $orders);
        } catch (Exception $exception) {
            return errorResponse('Failed to fetch orders: '.$exception->getMessage());
        }
    }
}
