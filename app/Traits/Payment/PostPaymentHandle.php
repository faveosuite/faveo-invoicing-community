<?php

namespace App\Traits\Payment;

use App\Http\Controllers\Common\PhpMailController;
use App\Model\Common\Setting;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Order\Payment;
use Auth;

trait PostPaymentHandle
{
    public static function sendFailedPaymenttoAdmin($invoice, $total, $productName, $exceptionMessage, $user)
    {
        $amount = currencyFormat($total, Auth::user()->currency);
        $payment = Payment::where('invoice_id', $invoice->id)->first();
        $orderid = OrderInvoiceRelation::where('invoice_id', $invoice->id)->value('order_id');
        $order = Order::find($orderid);
        $setting = Setting::find(1);
        $paymentFailData = 'Payment for'.' '.'of'.' '.$invoice->currency.' '.round($total).' '.'failed by'.' '.Auth::user()->first_name.' '.Auth::user()->last_name.' '.'. User Email:'.' '.Auth::user()->email.'<br>'.'Reason:'.$exceptionMessage;
        $mail = new PhpMailController();
        $mail->SendEmail($setting->email, $setting->company_email, $paymentFailData, 'Payment failed ', 'payment-failed');
        if ($payment) {
            $message = $invoice->is_renewed == 1 ? 'Product renew' : 'Product purchase';
            $mail->payment_log($user->email, $payment->payment_method, $payment->payment_status, $order->number, $exceptionMessage, $amount, $message);
        }
    }

    public static function sendPaymentSuccessMailtoAdmin($invoice, $total, $user, $productName)
    {
        $amount = currencyFormat($total, Auth::user()->currency);
        $payment = Payment::where('invoice_id', $invoice->id)->first();
        $orderid = OrderInvoiceRelation::where('invoice_id', $invoice->id)->value('order_id');
        $order = Order::find($orderid);
        $setting = Setting::find(1);
        $paymentSuccessdata = 'Payment for '.$productName.' of '.$invoice->currency.' '.round($total).' successful by '.$user->first_name.' '.$user->last_name.' Email: '.$user->email;

        $mail = new PhpMailController();
        $mail->SendEmail($setting->email, $setting->company_email, $paymentSuccessdata, 'Payment Successful', 'payment-success');
        if ($payment) {
            $message = $invoice->is_renewed == 1 ? 'Product renew' : 'Product purchase';
            $mail->payment_log($user->email, $payment->payment_method, $payment->payment_status, $order->number, null, $amount, $message);
        }
    }
}
