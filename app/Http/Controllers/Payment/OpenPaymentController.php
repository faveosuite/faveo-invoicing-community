<?php

namespace App\Http\Controllers\Payment;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\OpenPaymentRequest;
use App\Model\Common\Country;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Payment\Currency;
use App\Model\Payment\OpenPaymentOrder;
use App\Model\Plugin;
use App\Plugins\Payment\Exceptions\SignatureVerificationException;
use App\Services\Payment\OpenPaymentService;
use App\Services\Payment\PaymentService;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
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
        if (! StatusSetting::value('open_payment_status')) {
            return errorResponse(__('message.open_payment_disabled'));
        }

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

            return successResponse('Order created successfully', [
                'order' => $this->formatOrder($order),
            ]);
        } catch (Exception $exception) {
            return errorResponse('Failed to create order: '.$exception->getMessage());
        }
    }

    /**
     * Format an order for the client — raw model attributes are unformatted
     * DB decimals (no thousand separators, precision from the column, not the
     * currency). Every endpoint that hands an order's amount to the frontend
     * must go through here, or the same order shows a different-looking
     * amount depending which step rendered it (e.g. the review step read
     * this formatting, but a raw model on the success page didn't).
     */
    private function formatOrder(OpenPaymentOrder $order): array
    {
        $currency = $order->currency;

        return array_merge($order->toArray(), [
            'amount' => currencyFormat($order->amount, $currency, includeSymbol: false),
            'base_amount' => currencyFormat($order->base_amount, $currency, includeSymbol: false),
            'processing_fee' => currencyFormat($order->processing_fee, $currency, includeSymbol: false),
            'processing_fee_rate' => $order->processing_fee_rate,
        ]);
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
                'order' => $this->formatOrder($order),
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
                ? successResponse('Payment successful!', ['order' => $this->formatOrder($order->fresh())])
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
                ? successResponse('Payment successful!', ['order' => $this->formatOrder($order->fresh())])
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
        $currency = $request->input('currency');

        return successResponse('', [
            'base_amount' => currencyFormat($baseAmount, $currency, includeSymbol: false),
            'processing_fee' => currencyFormat($fee, $currency, includeSymbol: false),
            'processing_fee_rate' => $feeRate,
            'total' => currencyFormat($total, $currency, includeSymbol: false),
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

        return successResponse('', [
            'gateways' => $gateways,
            'currencies' => $currencies,
            'app_title' => $appTitle,
            'enabled' => (bool) StatusSetting::value('open_payment_status'),
        ]);
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
            $search = $request->input('search-query') ?: $request->input('search');
            $status = $request->input('status');
            $gateway = $request->input('gateway');
            $currency = $request->input('currency');
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');

            $allowedSortFields = ['name', 'email', 'company', 'transaction_id', 'amount', 'currency', 'gateway', 'payment_status', 'created_at'];
            $sortField = $request->input('sort-field', 'created_at');
            $sortField = in_array($sortField, $allowedSortFields, true) ? $sortField : 'created_at';
            $sortOrder = $request->input('sort-order') === 'asc' ? 'asc' : 'desc';

            $perPage = (int) ($request->input('limit') ?: $request->input('per_page', 10));

            $orders = OpenPaymentOrder::query()
                ->with('currencyInfo')
                ->when($search, fn (Builder $query) => $query->whereAny([
                    'name',
                    'email',
                    'company',
                    'mobile',
                    'amount',
                    'gateway',
                    'payment_status',
                    'transaction_id',
                ], 'like', "%{$search}%"))
                ->when($status && $status !== 'all', fn (Builder $query) => $query->where('payment_status', $status))
                ->when($gateway && $gateway !== 'all', fn (Builder $query) => $query->where('gateway', $gateway))
                ->when($currency && $currency !== 'all', fn (Builder $query) => $query->where('currency', $currency))
                ->when($fromDate, fn (Builder $query) => $query->where('created_at', '>=', Date::parse($fromDate)->startOfDay()))
                ->when($toDate, fn (Builder $query) => $query->where('created_at', '<=', Date::parse($toDate)->endOfDay()))
                ->orderBy($sortField, $sortOrder)
                ->paginate($perPage);

            // currency_symbol is flattened onto each row for the frontend table;
            // currencyInfo is eager-loaded above so this doesn't N+1.
            $orders->getCollection()->transform(fn (OpenPaymentOrder $order) => [
                ...$order->makeHidden('currencyInfo')->toArray(),
                'currency_symbol' => $order->currencyInfo?->symbol,
            ]);

            return successResponse('', $orders);
        } catch (Exception $exception) {
            return errorResponse('Failed to fetch orders: '.$exception->getMessage());
        }
    }
}
