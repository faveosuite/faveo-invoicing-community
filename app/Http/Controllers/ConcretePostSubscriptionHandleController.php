<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Common\PhpMailController;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Services\Payment\ProcessingFee;
use App\Services\Payment\SubscriptionService;
use App\Services\SubscriptionRenewalService;
use App\User;
use Exception;
use Illuminate\Support\Facades\Date;
use Log;

abstract class PostSubscriptionHandleController
{
    protected \App\Model\Order\Invoice $invoiceModel;

    protected \App\Model\Order\Order $orderModel;

    protected \App\Model\Common\StatusSetting $statusSettingModel;

    protected \App\Model\Payment\Plan $plan;

    protected \App\Model\Product\Subscription $sub;

    protected \App\Model\Order\Payment $payment;

    public function __construct(Invoice $invoiceModel, Order $orderModel, StatusSetting $statusSettingModel, Plan $plan, Subscription $sub, Payment $payment)
    {
        $this->invoiceModel = $invoiceModel;
        $this->orderModel = $orderModel;
        $this->statusSettingModel = $statusSettingModel;
        $this->plan = $plan;
        $this->sub = $sub;
        $this->payment = $payment;
    }

    abstract public function successRenew(Invoice $invoice, Subscription $subscription, string $payment_method, string $currency): int;

    abstract public function recordPayment(Invoice $invoice, string $payment_method): Payment;

    abstract public function getProcessingFee(string $paymentMethod, string $currency): ?string;

    abstract public function PaymentSuccessMailtoAdmin(Invoice $invoice, float|int $total, User $user, string $productName, ?Template $template, Order $order, Payment|string $payment): void;

    abstract public function FailedPaymenttoAdmin(Invoice $invoice, float|int $total, string $productName, string $exceptionMessage, User $user, string $template, Order $order, Payment $payment): void;

    abstract public function calculateUnitCost(string $currency, float|int $cost): float;

    abstract public function sendPaymentSuccessMail(int $sub, string $currency, float|int $total, User $user, string $product, string $number): void;

    abstract public function sendFailedPayment(float|int|null $total, string $exceptionMessage, ?User $user, ?string $number, string $end, ?string $currency, ?Order $order, ?Product $product_details, ?Invoice $invoice, Payment|string|null $payment): void;
}

class ConcretePostSubscriptionHandleController extends PostSubscriptionHandleController
{
    public function successRenew(Invoice $invoice, Subscription $subscription, string $payment_method, string $currency): int
    {
        $sub = $this->sub->find($subscription->id);
        $plan = $this->plan->find($subscription->plan_id);

        // Extend dates first — if this fails, invoice remains pending (safe to retry)
        resolve(SubscriptionRenewalService::class)->extendDates($sub, (int) $plan->days, fromNowIfExpired: true);

        $processingFee = $this->getProcessingFee($payment_method, $currency);
        $invoice->update(['processing_fee' => $processingFee, 'status' => 'success']);

        return $sub->id;
    }

    public function recordPayment(Invoice $invoice, string $payment_method): Payment
    {
        $invoice->update(['status' => 'success']);

        return $this->payment->create([
            'invoice_id' => $invoice->id,
            'user_id' => $invoice->user_id,
            'amount' => $invoice->grand_total,
            'payment_method' => $payment_method,
            'payment_status' => 'success',
            'created_at' => Date::now()->toDateTimeString(),
        ]);
    }

    public function getProcessingFee(string $paymentMethod, string $currency): ?string
    {
        $percent = ProcessingFee::percent($paymentMethod);

        // Stored as the same "2.5%" label the checkout/pay flow uses, so the
        // invoice's grand_total (already fee-inclusive) reconciles on display.
        return $percent > 0 ? ProcessingFee::label($percent) : null;
    }

    public function PaymentSuccessMailtoAdmin(Invoice $invoice, float|int $total, User $user, string $productName, ?Template $template, Order $order, Payment|string $payment): void
    {
        $amount = currencyFormat($total, getCurrencyForClient($user->country));
        $setting = Setting::find(1);
        $currency = getCurrencyForClient($user->country);
        $paymentSuccessdata = 'Payment for '.$productName.' of '.$currency.' '.$total.' successful by '.$user->first_name.' '.$user->last_name.' Email: '.$user->email;

        $mail = new PhpMailController();
        $mail->SendEmail($setting->email, $setting->company_email, $paymentSuccessdata, 'payment-success', $template->type()->value('name'));
        $mail->payment_log($user->email, $payment, 'success', $order->number, amount: $amount, payment_type: 'Product renew');
    }

    public function FailedPaymenttoAdmin(Invoice $invoice, float|int $total, string $productName, string $exceptionMessage, User $user, string $template, Order $order, Payment $payment): void
    {
        $amount = currencyFormat($total, getCurrencyForClient($user->country));
        $setting = Setting::find(1);
        $currency = getCurrencyForClient($user->country);
        $paymentFailData = 'Payment for of '.$currency.' '.$total.' '.'failed by'.' '.$user->first_name.' '.$user->last_name.' '.'. User Email:'.' '.$user->email.'<br>'.'Reason:'.$exceptionMessage;
        $mail = new PhpMailController();
        $mail->SendEmail($setting->email, $setting->company_email, $paymentFailData, 'payment-failed', Template::where('name', $template)->type()->value('name')); // @phpstan-ignore method.notFound
        $mail->payment_log($user->email, $payment, 'failed', $order->number, $exceptionMessage, $amount, 'Product renew');
    }

    public function sendPaymentSuccessMail(int $sub, string $currency, float|int $total, User $user, string $product, string $number): void
    {
        $future_expiry = Subscription::find($sub);
        $contact = getContactData();
        //check in the settings
        $settings = new Setting();
        $setting = $settings::find(1);

        $mail = new PhpMailController();
        //template
        $template = TemplateType::getSelectedTemplate('payment_successfull');
        $date = date_create($future_expiry->update_ends_at);
        $end = date_format($date, 'l, F j, Y ');

        $replace = [
            'name' => ucfirst((string) $user->first_name).' '.ucfirst((string) $user->last_name),
            'product' => $product,
            'total' => currencyFormat($total, $code = $currency),
            'number' => $number,
            'contact' => $contact['contact'],
            'logo' => $contact['logo'],
            'future_expiry' => $end,
            'reply_email' => $setting->company_email,
        ];

        $type = $template?->type()->value('name') ?? '';
        $mail->SendEmail($setting->email, $user->email, $template->data, $template->name, $template->type()->value('name'), $replace, $type);
    }

    public function sendFailedPayment(float|int|null $total, string $exceptionMessage, ?User $user, ?string $number, string $end, ?string $currency, ?Order $order, ?Product $product_details, ?Invoice $invoice, Payment|string|null $payment): void
    {
        $contact = getContactData();
        //check in the settings
        $settings = new Setting();
        $setting = $settings::find(1);

        $this->disableAutorenewalStatusByOrderId($order->id);

        $mail = new PhpMailController();
        $mail->setMailConfig($setting);
        //template
        $template = TemplateType::getSelectedTemplate('payment_failed');
        $url = url('autopaynow/'.$invoice->invoice_id); // @phpstan-ignore property.notFound
        $type = '';
        $replace = ['name' => ucfirst((string) $user->first_name).' '.ucfirst((string) $user->last_name),
            'product' => $product_details->name,
            'total' => $total ? currencyFormat($total, $code = $currency) : 'N/A',
            'number' => $number,
            'expiry' => date('d-m-Y', strtotime($end)),
            'exception' => $exceptionMessage,
            'url' => $url,
            'contact' => $contact['contact'],
            'logo' => $contact['logo'],
            'reply_email' => $setting->company_email, ];
        $type = $template?->type()->value('name') ?? '';

        $mail->SendEmail($setting->email, $user->email, $template->data, $template->name, $template->type()->value('name'), $replace, $type);
        $this->FailedPaymenttoAdmin($invoice, $total, $product_details->name, $exceptionMessage, $user, $template->name, $order, $payment);
    }

    public function calculateUnitCost(string $currency, float|int $cost): float
    {
        $decimalPlaces = [
            'BIF' => 0, 'CLP' => 0, 'DJF' => 0, 'GNF' => 0, 'JPY' => 0,
            'KMF' => 0, 'KRW' => 0, 'MGA' => 0, 'PYG' => 0, 'RWF' => 0,
            'UGX' => 0, 'VND' => 0, 'VUV' => 0, 'XAF' => 0, 'XOF' => 0,
            'XPF' => 0, 'BHD' => 3, 'JOD' => 3, 'KWD' => 3, 'OMR' => 3,
            'TND' => 3,
        ];

        $decimalPlacesForCurrency = $decimalPlaces[$currency] ?? 2;

        if ($decimalPlacesForCurrency === 0) {
            $unit_cost = round((int) $cost);
        } elseif ($decimalPlacesForCurrency === 3) {
            $unit_cost = round((int) $cost) * 1000;
        } else {
            $unit_cost = round((int) $cost) * 100;
        }

        return $unit_cost;
    }

    public function disableAutorenewalStatusByOrderId(int $orderId): void
    {
        try {
            $subscription = Subscription::where('order_id', $orderId)->first();

            $cancellationHandlers = collect([
                'rzp_subscription' => fn (string $subscribeId) => resolve(SubscriptionService::class)->cancelSubscription('Razorpay', $subscribeId),
                'autoRenew_status' => fn (string $subscribeId) => resolve(SubscriptionService::class)->cancelSubscription('Stripe', $subscribeId),
            ]);

            if ($subscription->is_subscribed && $subscription->subscribe_id) {
                $cancellationHandlers
                    ->filter(fn ($handler, $field) => $subscription->$field)
                    ->first()($subscription->subscribe_id);
            }

            $subscription->update([
                'is_subscribed' => 0,
                'autoRenew_status' => 0,
                'rzp_subscription' => 0,
            ]);
        } catch (Exception $exception) {
            Log::error('Subscription cancellation failed: '.$exception->getMessage());

            return;
        }
    }
}
