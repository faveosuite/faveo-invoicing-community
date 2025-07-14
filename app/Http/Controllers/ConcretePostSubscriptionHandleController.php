<?php

namespace App\Http\Controllers;

use App\ApiKey;
use App\Http\Controllers\License\LicensePermissionsController;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Common\Template;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Payment\Plan;
use App\Model\Product\Subscription;
use Carbon\Carbon;
use Razorpay\Api\Api;

abstract class PostSubscriptionHandleController
{
    protected $invoiceModel;
    protected $orderModel;
    protected $statusSettingModel;
    protected $plan;
    protected $sub;
    protected $payment;

    public function __construct(Invoice $invoiceModel, Order $orderModel, StatusSetting $statusSettingModel, Plan $plan, Subscription $sub, Payment $payment)
    {
        $this->invoiceModel = $invoiceModel;
        $this->orderModel = $orderModel;
        $this->statusSettingModel = $statusSettingModel;
        $this->plan = $plan;
        $this->sub = $sub;
        $this->payment = $payment;
    }

    abstract public function successRenew($invoice, $subscription, $payment_method, $currency);

    abstract public function getExpiryDate($permissions, $sub, $days);

    abstract public function getUpdatesExpiryDate($permissions, $sub, $days);

    abstract public function getSupportExpiryDate($permissions, $sub, $days);

    abstract public function editDateInAPL($sub, $updatesExpiry, $licenseExpiry, $supportExpiry);

    abstract public function postRazorpayPayment($invoice, $payment_method);

    abstract public function getProcessingFee($paymentMethod, $currency);

    abstract public function PaymentSuccessMailtoAdmin($invoice, $total, $user, $productName, $template, $order, $payment);

    abstract public function FailedPaymenttoAdmin($invoice, $total, $productName, $exceptionMessage, $user, $template, $order, $payment);

    abstract public function calculateUnitCost($currency, $cost);

    abstract public function sendPaymentSuccessMail($sub, $currency, $total, $user, $product, $number);

    abstract public function sendFailedPayment($total, $exceptionMessage, $user, $number, $end, $currency, $order, $product_details, $invoice, $payment);
}

class ConcretePostSubscriptionHandleController extends PostSubscriptionHandleController
{
    public function __construct(Invoice $invoiceModel, Order $orderModel, StatusSetting $statusSettingModel, Plan $plan, Subscription $sub, Payment $payment)
    {
        parent::__construct($invoiceModel, $orderModel, $statusSettingModel, $plan, $sub, $payment);
    }

    public function successRenew($invoice, $subscription, $payment_method, $currency)
    {
        try {
            $processingFee = $this->getProcessingFee($payment_method, $currency);
            Invoice::where('id', $invoice->invoice_id)->update(['processing_fee' => $processingFee, 'status' => 'success']);
            $id = $subscription->id;
            $planid = $subscription->plan_id;
            $plan = $this->plan->find($planid);
            $days = $plan->days;
            $sub = $this->sub->find($id);
            $permissions = LicensePermissionsController::getPermissionsForProduct($sub->product_id);
            $licenseExpiry = $this->getExpiryDate($permissions['generateLicenseExpiryDate'], $sub, $days);
            $updatesExpiry = $this->getUpdatesExpiryDate($permissions['generateUpdatesxpiryDate'], $sub, $days);
            $supportExpiry = $this->getSupportExpiryDate($permissions['generateSupportExpiryDate'], $sub, $days);
            $sub->ends_at = $licenseExpiry;
            $sub->update_ends_at = $updatesExpiry;
            $sub->support_ends_at = $supportExpiry;
            $sub->save();
            if (Order::where('id', $sub->order_id)->value('license_mode') == 'File') {
                Order::where('id', $sub->order_id)->update(['is_downloadable' => 0]);
            } else {
                $licenseStatus = StatusSetting::pluck('license_status')->first();
                if ($licenseStatus == 1) {
                    $this->editDateInAPL($sub, $updatesExpiry, $licenseExpiry, $supportExpiry);
                }
            }

            return $id;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getExpiryDate($permissions, $sub, $days)
    {
        $expiry_date = '';
        if ($days > 0 && $permissions == 1) {
            $date = \Carbon\Carbon::parse($sub->ends_at);
            if ($date->isPast()) {
                $date = now();
            }
            $expiry_date = $date->addDays($days);
        }

        return $expiry_date;
    }

    public function getUpdatesExpiryDate($permissions, $sub, $days)
    {
        $expiry_date = '';
        if ($days > 0 && $permissions == 1) {
            $date = \Carbon\Carbon::parse($sub->update_ends_at);
            if ($date->isPast()) {
                $date = now();
            }
            $expiry_date = $date->addDays($days);
        }

        return $expiry_date;
    }

    public function getSupportExpiryDate($permissions, $sub, $days)
    {
        $expiry_date = '';
        if ($days > 0 && $permissions == 1) {
            $date = \Carbon\Carbon::parse($sub->support_ends_at);
            if ($date->isPast()) {
                $date = now();
            }
            $expiry_date = $date->addDays($days);
        }

        return $expiry_date;
    }

    public function editDateInAPL($sub, $updatesExpiry, $licenseExpiry, $supportExpiry)
    {
        $productId = $sub->product_id;
        $domain = $sub->order->domain;
        $orderNo = $sub->order->number;
        $licenseCode = $sub->order->serial_key;
        $expiryDate = $updatesExpiry ? Carbon::parse($updatesExpiry)->format('Y-m-d') : '';
        $licenseExpiry = $licenseExpiry ? Carbon::parse($licenseExpiry)->format('Y-m-d') : '';
        $supportExpiry = $supportExpiry ? Carbon::parse($supportExpiry)->format('Y-m-d') : '';
        $noOfAllowedInstallation = '';
        $getInstallPreference = '';
        $cont = new \App\Http\Controllers\License\LicenseController();
        $noOfAllowedInstallation = $cont->getNoOfAllowedInstallation($licenseCode, $productId);
        $getInstallPreference = $cont->getInstallPreference($licenseCode, $productId);
        $updateLicensedDomain = $cont->updateExpirationDate($licenseCode, $expiryDate, $productId, $domain, $orderNo, $licenseExpiry, $supportExpiry, $noOfAllowedInstallation, $getInstallPreference);
    }

    public function postRazorpayPayment($invoice, $payment_method)
    {
        try {
            $invoice = Invoice::where('id', $invoice->invoice_id)->first();

            $payment_status = 'success';
            $payment_date = \Carbon\Carbon::now()->toDateTimeString();

            $invoice = Invoice::find($invoice->id);

            $invoice->status = 'success';
            $invoice->save();

            $payment = $this->payment->create([
                'invoice_id' => $invoice->id,
                'user_id' => $invoice->user_id,
                'amount' => $invoice->grand_total,
                'payment_method' => $payment_method,
                'payment_status' => $payment_status,
                'created_at' => $payment_date,
            ]);
            $all_payments = $this->payment
            ->where('invoice_id', $invoice->id)
            ->where('payment_status', 'success')
            ->pluck('amount')->toArray();
            $total_paid = array_sum($all_payments);
            if ($total_paid >= $invoice->grand_total) {
                $invoice_status = 'success';
            }

            return $payment;
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function getProcessingFee($paymentMethod, $currency)
    {
        if ($paymentMethod) {
            $de = $paymentMethod == 'razorpay' ? 0 : \DB::table(strtolower($paymentMethod))->where('currencies', $currency)->value('processing_fee');
            \DB::table(strtolower($paymentMethod))->where('currencies', $currency)->value('processing_fee');
        }
    }

    public function PaymentSuccessMailtoAdmin($invoice, $total, $user, $productName, $template, $order, $payment)
    {
        $amount = currencyFormat($total, getCurrencyForClient($user->country));
        $setting = Setting::find(1);
        $currency = getCurrencyForClient($user->country);
        $paymentSuccessdata = 'Payment for '.$productName.' of '.$currency.' '.$total.' successful by '.$user->first_name.' '.$user->last_name.' Email: '.$user->email;

        $mail = new \App\Http\Controllers\Common\PhpMailController();
        $mail->SendEmail($setting->email, $setting->company_email, $paymentSuccessdata, 'Payment Successful ');
        $mail->payment_log($user->email, $payment, 'success', $order->number, null, $amount, 'Product renew');
    }

    public function FailedPaymenttoAdmin($invoice, $total, $productName, $exceptionMessage, $user, $template, $order, $payment)
    {
        $amount = currencyFormat($total, getCurrencyForClient($user->country));
        $setting = Setting::find(1);
        $currency = getCurrencyForClient($user->country);
        $paymentFailData = 'Payment for'.' '.'of'.' '.$currency.' '.$total.' '.'failed by'.' '.$user->first_name.' '.$user->last_name.' '.'. User Email:'.' '.$user->email.'<br>'.'Reason:'.$exceptionMessage;
        $mail = new \App\Http\Controllers\Common\PhpMailController();
        $mail->SendEmail($setting->email, $setting->company_email, $paymentFailData, 'Payment failed ');
        $mail->payment_log($user->email, $payment, 'failed', $order->number, $exceptionMessage, $amount, 'Product renew');
    }

    public function sendPaymentSuccessMail($sub, $currency, $total, $user, $product, $number)
    {
        $future_expiry = Subscription::find($sub);
        $contact = getContactData();
        //check in the settings
        $settings = new \App\Model\Common\Setting();
        $setting = $settings::find(1);

        $mail = new \App\Http\Controllers\Common\PhpMailController();
        //template
        $templates = new \App\Model\Common\Template();
        $temp_id = $setting->payment_successfull;

        $template = $templates->where('id', $temp_id)->first();
        $date = date_create($future_expiry->update_ends_at);
        $end = date_format($date, 'l, F j, Y ');

        $replace = [
            'name' => ucfirst($user->first_name).' '.ucfirst($user->last_name),
            'product' => $product,
            'total' => currencyFormat($total, $code = $currency),
            'number' => $number,
            'contact' => $contact['contact'],
            'logo' => $contact['logo'],
            'future_expiry' => $end,
            'reply_email' => $setting->company_email,
        ];

        $type = '';
        if ($template) {
            $type_id = $template->type;
            $temp_type = new \App\Model\Common\TemplateType();
            $type = $temp_type->where('id', $type_id)->first()->name;
        }
        $mail->SendEmail($setting->email, $user->email, $template->data, $template->name, $replace, $type);
    }

    public function sendFailedPayment($total, $exceptionMessage, $user, $number, $end, $currency, $order, $product_details, $invoice, $payment)
    {
        $contact = getContactData();
        //check in the settings
        $settings = new \App\Model\Common\Setting();
        $setting = $settings::find(1);

        $this->disableAutorenewalStatusByOrderId($order->id);

        $mail = new \App\Http\Controllers\Common\PhpMailController();
        $mailer = $mail->setMailConfig($setting);
        //template
        $templates = new \App\Model\Common\Template();
        $temp_id = $setting->payment_failed;

        $template = $templates->where('id', $temp_id)->first();
        $url = url("autopaynow/$invoice->invoice_id");
        $type = '';
        $replace = ['name' => ucfirst($user->first_name).' '.ucfirst($user->last_name),
            'product' => $product_details->name,
            'total' => $total ? currencyFormat($total, $code = $currency) : 'N/A',
            'number' => $number,
            'expiry' => date('d-m-Y', strtotime($end)),
            'exception' => $exceptionMessage,
            'url' => $url,
            'contact' => $contact['contact'],
            'logo' => $contact['logo'],
            'reply_email' => $setting->company_email, ];
        $type = '';

        if ($template) {
            $type_id = $template->type;
            $temp_type = new \App\Model\Common\TemplateType();
            $type = $temp_type->where('id', $type_id)->first()->name;
        }

        $mail->SendEmail($setting->email, $user->email, $template->data, $template->name, $replace, $type);
        $this->FailedPaymenttoAdmin($invoice, $total, $product_details->name, $exceptionMessage, $user, $template->name, $order, $payment);
    }

    public function calculateUnitCost($currency, $cost)
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

    public function disableAutorenewalStatusByOrderId($orderId)
    {
        try {
            $subscription = Subscription::where('order_id', $orderId)->first();

            $cancellationHandlers = collect([
                'rzp_subscription' => function($subscribeId) {
                    $apiKeys = ApiKey::select('rzp_key', 'rzp_secret')->first();
                    $api = new Api($apiKeys->rzp_key, $apiKeys->rzp_secret);
                    $api->subscription->fetch($subscribeId)->cancel();
                },
                'autoRenew_status' => function($subscribeId) {
                    $stripeSecretKey = ApiKey::value('stripe_secret');
                    $stripe = new \Stripe\StripeClient($stripeSecretKey);
                    $stripe->subscriptions->cancel($subscribeId, []);
                }
            ]);

            if ($subscription->is_subscribed && $subscription->subscribe_id) {
                $cancellationHandlers
                    ->filter(fn($handler, $field) => $subscription->$field)
                    ->first()($subscription->subscribe_id);
            }

            $subscription->update([
                'is_subscribed' => 0,
                'autoRenew_status' => 0,
                'rzp_subscription' => 0
            ]);


        } catch (\Exception $ex) {
            \Log::error('Subscription cancellation failed: ' . $ex->getMessage());
            return;
        }
    }
}
