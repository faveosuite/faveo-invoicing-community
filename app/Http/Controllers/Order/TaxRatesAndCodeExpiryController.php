<?php

namespace App\Http\Controllers\Order;

use App\Model\Common\TemplateType;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Payment\Currency;
use App\User;

class TaxRatesAndCodeExpiryController extends BaseInvoiceController
{
    /**
     * Get Grandtotal.
     **/
    public function getGrandTotal($code, $total, $cost, $productid, $currency, $user_id = '')
    {
        if (! $total) {
            return ['total' => $total, 'code' => '', 'value' => '', 'mode' => ''];
        }
        if ($code) {
            $cont = new \App\Http\Controllers\Payment\PromotionController();
            $promo = $cont->getPromotionDetails($code);
            $total = $cont->findCostAfterDiscount($promo->id, $productid, $user_id);

            return ['total' => $total, 'code' => $promo->code, 'value' => $promo->value, 'mode' => 'coupon'];
        } else {
            return ['total' => $total, 'code' => '', 'value' => '', 'mode' => ''];
        }
    }

    /**
     * Get Message on Invoice Generation.
     **/
    public function getMessage($items, $user_id)
    {
        if ($items) {
            // $this->sendmailClientAgent($user_id, $items->invoice_id);
            $result = ['success' => \Lang::get('message.invoice-generated-successfully')];
        } else {
            $result = ['fails' => \Lang::get('message.can-not-generate-invoice')];
        }

        return $result;
    }

    public function checkExecution($invoiceid)
    {
        try {
            $response = false;
            $invoice = Invoice::find($invoiceid);

            $order = Order::where('invoice_id', $invoiceid);
            $order_invoice_relation = $invoice->orderRelation()->first();

            if ($order_invoice_relation) {
                $response = true;
            } elseif ($order->get()->count() > 0) {
                $response = true;
            }

            return $response;
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function invoiceContent($invoiceid)
    {
        $invoice = $this->invoice->find($invoiceid);
        $items = $invoice->invoiceItem()->get();
        $content = '';
        if ($items->count() > 0) {
            foreach ($items as $item) {
                $content .= '<tr>'.
                        '<td style="border-bottom: 1px solid#ccc; color: #333; 
                        font-family: Arial,sans-serif; font-size: 14px; line-height: 20px;
                         padding: 15px 8px;" valign="top">'.$invoice->number.'</td>'.
                        '<td style="border-bottom: 1px solid#ccc; color: #333; 
                        font-family: Arial,sans-serif; font-size: 14px; line-height: 20px;
                         padding: 15px 8px;" valign="top">'.$item->product_name.'</td>'.
                        '<td style="border-bottom: 1px solid#ccc; color: #333; 
                        font-family: Arial,sans-serif; font-size: 14px; line-height: 20px;
                         padding: 15px 8px;" valign="top">'.$this->currency($invoiceid)
                         .number_format($item->subtotal).'</td>'.
                        '</tr>';
            }
        }

        return $content;
    }

    public function currency($invoiceid)
    {
        $invoice = Invoice::find($invoiceid);
        $currency_code = $invoice->currency;

        $cur = ' ';
        if ($invoice->grand_total == 0) {
            return $cur;
        }
        $currency = Currency::where('code', $currency_code)->first();
        if ($currency) {
            $cur = $currency->symbol;
            if (! $cur) {
                $cur = $currency->code;
            }
        }

        return $cur;
    }

    public function sendInvoiceMail($userid, $number, $total, $invoiceid)
    {
        $contact = getContactData();
        //user
        $users = new User();
        $user = $users->find($userid);
        //check in the settings
        $settings = new \App\Model\Common\Setting();
        $setting = $settings::find(1);
        $invoiceurl = $this->invoiceUrl($invoiceid);
        //template
        $templates = new \App\Model\Common\Template();
        $temp_id = TemplateType::where('name', 'invoice_mail')->value('id');
        $template = $templates->where('type', $temp_id)->first();
        $type = '';
        $replace = [
            'name' => $user->first_name.' '.$user->last_name,
            'number' => $number,
            'address' => $user->address,
            'invoiceurl' => $invoiceurl,
            'content' => $this->invoiceContent($invoiceid),
            'currency' => $this->currency($invoiceid),
            'contact' => $contact['contact'],
            'logo' => $contact['logo'],
            'reply_email' => $setting->company_email,
        ];
        if ($template) {
            $type_id = $template->type;
            $temp_type = new \App\Model\Common\TemplateType();
            $type = $temp_type->where('id', $type_id)->first()->name;
        }
        $mail = new \App\Http\Controllers\Common\PhpMailController();
        $mail->SendEmail($setting->email, $user->email, $template->data, $template->name, $replace, $type);
    }

    public function invoiceUrl($invoiceid)
    {
        $url = url('my-invoice/'.$invoiceid);

        return $url;
    }

    public function paymentDeleleById($id)
    {
        try {
            $invoice_no = '';
            $payment = Payment::find($id);
            if ($payment) {
                $invoice_id = $payment->invoice_id;
                $invoice = Invoice::find($invoice_id);
                if ($invoice) {
                    $invoice_no = $invoice->number;
                }
                $payment->delete();
            } else {
                return redirect()->back()->with('fails', __('message.cannot_delete'));
            }

            return redirect()->back()->with('success', __('messages.payment_deleted_successfully', ['invoice_no' => $invoice_no]));
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function paymentEditById($id)
    {
        try {
            $cltCont = new \App\Http\Controllers\User\ClientController();
            $payment = Payment::find($id);
            $clientid = $payment->user_id;
            $invoice = new Invoice();
            $order = new Order();
            $invoices = $invoice->where('user_id', $clientid)->where('status', '=', 'pending')
            ->orderBy('created_at', 'desc')->get();
            $cltCont = new \App\Http\Controllers\User\ClientController();
            $invoiceSum = $cltCont->getTotalInvoice($invoices);
            $amountReceived = $cltCont->getExtraAmt($clientid);
            $pendingAmount = $invoiceSum - $amountReceived;
            $client = $this->user->where('id', $clientid)->first();
            $currency = $client->currency;
            $symbol = Currency::where('code', $currency)->pluck('symbol')->first();
            $orders = $order->where('client', $clientid)->get();

            return view('themes.default1.invoice.editPayment',
                compact('amountReceived', 'clientid', 'client', 'invoices', 'orders',
                    'invoiceSum', 'amountReceived', 'pendingAmount', 'currency', 'symbol'));
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }
}
