<?php

namespace App\Http\Controllers;

use App\ApiKey;
use App\Http\Controllers\Order\RenewController;
use App\Model\Common\State;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Payment\TaxByState;
use App\Plugins\Payment\Dto\SubscriptionRequest;
use App\Plugins\Payment\Exceptions\SignatureVerificationException;
use App\Plugins\Stripe\Controllers\SettingsController;
use App\Services\Payment\InvoicePaymentService;
use App\Services\Payment\SubscriptionService;
use App\Traits\Payment\PostPaymentHandle;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Session;
use Stripe\StripeClient;

class RazorpayController extends Controller
{
    use PostPaymentHandle;

    /**
     * @var \App\Model\Order\Invoice
     */
    public $invoice;

    /**
     * @var \App\Model\Order\InvoiceItem
     */
    public $invoiceItem;

    public function __construct()
    {
        $invoice = new Invoice();
        $this->invoice = $invoice;

        $invoiceItem = new InvoiceItem();
        $this->invoiceItem = $invoiceItem;
    }

    /*
     * Verify a Razorpay Checkout handler response for an invoice and fulfil it.
     * The signature is verified server-side; nothing is recorded unless authentic.
     */
    public function payment(mixed $invoice, Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        /** @var \App\Model\Order\Invoice|null $model */
        $model = Invoice::find($invoice);
        abort_if(! $model, 404, 'Invoice not found.');
        /** @var \App\User $authUser */
        $authUser = Auth::user();
        if ($authUser->role != 'admin' && (int) $model->user_id !== (int) Auth::id()) {
            return errorResponse(__('message.invalid_modification'));
        }

        try {
            $paid = resolve(InvoicePaymentService::class)
                ->confirm($model, 'Razorpay', $request->only([
                    'razorpay_payment_id', 'razorpay_order_id', 'razorpay_signature',
                ]));

            return $paid
                ? successResponse('success', [])
                : errorResponse(__('message.payment_declined_try_other_gateway'));
        } catch (SignatureVerificationException $e) {
            if (emailSendingStatus()) {
                /** @var \App\User $authUser4 */
                $authUser4 = Auth::user();
                $this->sendFailedPaymenttoAdmin($model, $model->grand_total, (string) $model->invoiceItem()->first()?->product_name, $e->getMessage(), $authUser4); // @phpstan-ignore argument.type
            }

            return errorResponse(__('message.payment_declined_try_other_gateway'));
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function getCurrency(): mixed
    {
        /** @var \App\User $authUser2 */
        $authUser2 = Auth::user();

        return $authUser2->currency_symbol;
    }

    public function getState(mixed $country, mixed $stateCode): mixed
    {
        /** @var \App\User $authUser3 */
        $authUser3 = Auth::user();
        if ($authUser3->country != 'IN') {
            return State::where('country_code', $country)->where('iso2', $stateCode)->value('state_subdivision_name');
        }

        return TaxByState::where('state_code', $authUser3->state)->value('state');
    }

    public function afterPayment(Request $request): mixed
    {
        try {
            $stripeSecretKey = ApiKey::value('stripe_secret');
            $stripe = new StripeClient($stripeSecretKey);
            // SPA flow carries the invoice id on the return URL (stateless);
            // legacy flow still falls back to the session-stored invoice.
            $invoice = $request->query('invoice')
                ? Invoice::find($request->query('invoice'))
                : Session::get('invoice');
            $paymentIntent = $stripe->paymentIntents->retrieve($request->input('payment_intent'));
            if ($paymentIntent->status === 'succeeded') {
                $currency = strtolower((string) $invoice->currency);
                $controller = new SettingsController();
                $result = $controller->processPaymentSuccess($invoice, $currency); // @phpstan-ignore method.notFound
                Session::forget(['items', 'code', 'codevalue', 'totalToBePaid', 'invoice', 'cart_currency']);

                return redirect('checkout')->with($result['status'], $result['message']);
            }

            $control = new RenewController();
            if (! $control->checkRenew($invoice->is_renewed)) {
                return redirect('checkout')->with('fails', 'Your Payment was declined. Please try with another card or gateway');
            }

            return redirect('paynow/'.$invoice->id)->with('fails', 'Your Payment was declined. Please try with another card or gateway');
        } catch (Exception) {
            return redirect('checkout')->with('fails', 'Your Payment was declined. Please try with another card or gateway');
        }
    }

    /**
     * Create a recurring Razorpay subscription for autopay.
     *
     * Thin adapter over the centralized {@see \App\Services\Payment\SubscriptionService}
     * (which drives the payment package's RazorpayGateway). Returns a
     * {@see \App\Plugins\Payment\Dto\SubscriptionResult} — callers read ->status,
     * ->id and ->raw['short_url']. $cost is already in minor units; start_at /
     * expire_by are derived here from the subscription's current period.
     */
    public function handleRzpAutoPay(mixed $cost, mixed $days, mixed $product_name, mixed $invoice, mixed $currency, mixed $subscription, mixed $user, mixed $order, mixed $endDate, mixed $productDetails): \App\Plugins\Payment\Dto\SubscriptionResult
    {
        return resolve(SubscriptionService::class)->createSubscription('Razorpay', new SubscriptionRequest(
            amountMinor: (int) $cost,
            currency: $currency,
            intervalDays: (int) $days,
            planName: $product_name,
            startAt: (int) Date::parse($subscription->update_ends_at)->addDays(round((int) $days))->timestamp,
            expireBy: (int) Date::parse($subscription->update_ends_at)->addDays(1)->timestamp,
        ));
    }
}
