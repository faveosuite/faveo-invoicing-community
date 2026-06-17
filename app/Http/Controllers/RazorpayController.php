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
    public function payment($invoice, Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $model = Invoice::find($invoice);
        abort_if(! $model, 404, 'Invoice not found.');
        if (Auth::user()->role != 'admin' && (int) $model->user_id !== (int) Auth::id()) {
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
                $this->sendFailedPaymenttoAdmin($model, $model->grand_total, $model->invoiceItem()->first()?->product_name, $e->getMessage(), Auth::user());
            }

            return errorResponse(__('message.payment_declined_try_other_gateway'));
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function getCurrency()
    {
        return Auth::user()->currency_symbol;
    }

    public function getState($country, $stateCode)
    {
        if (Auth::user()->country != 'IN') {
            return State::where('country_code', $country)->where('iso2', $stateCode)->value('state_subdivision_name');
        }

        return TaxByState::where('state_code', Auth::user()->state)->value('state');
    }

    public function afterPayment(Request $request)
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
                $result = $controller->processPaymentSuccess($invoice, $currency);
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
    public function handleRzpAutoPay($cost, $days, $product_name, $invoice, $currency, $subscription, $user, $order, $endDate, $productDetails): \App\Plugins\Payment\Dto\SubscriptionResult
    {
        return resolve(SubscriptionService::class)->createSubscription('Razorpay', new SubscriptionRequest(
            amountMinor: (int) $cost,
            currency: $currency,
            intervalDays: (int) $days,
            planName: $product_name,
            startAt: Date::parse($subscription->update_ends_at)->addDays(round((int) $days))->timestamp,
            expireBy: Date::parse($subscription->update_ends_at)->addDays(1)->timestamp,
        ));
    }
}
