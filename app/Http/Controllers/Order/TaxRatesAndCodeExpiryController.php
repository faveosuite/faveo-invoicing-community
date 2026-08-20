<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Payment\PromotionController;
use App\Http\Controllers\User\AdvanceSearchController;
use App\Model\Common\Setting;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\Order\Invoice;
use App\Model\Order\Payment;
use App\Model\Payment\Currency;
use App\User;
use Exception;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\JsonResponse;
use Lang;

class TaxRatesAndCodeExpiryController extends BaseInvoiceController
{
    /**
     * Get Grandtotal.
     *
     * @return array<mixed>
     **/
    public function getGrandTotal(?string $code, float|int $total, float|int|null $cost, int $productid, string $currency, string $user_id = ''): array
    {
        if (! $total) {
            return ['total' => $total, 'code' => '', 'value' => '', 'mode' => ''];
        }

        if ($code) {
            $cont = new PromotionController;
            $promo = $cont->getPromotionDetails($code);
            $total = $cont->findCostAfterDiscount($promo->id, $productid, $user_id);

            return ['total' => $total, 'code' => $promo->code, 'value' => $promo->value, 'mode' => 'coupon'];
        }

        return ['total' => $total, 'code' => '', 'value' => '', 'mode' => ''];
    }

    /**
     * Get Message on Invoice Generation.
     *
     * @return array<mixed>
     **/
    public function getMessage(mixed $items, int $user_id): array
    {
        if ($items) {
            return ['success' => Lang::get('message.invoice-generated-successfully')];
        }

        return ['fails' => Lang::get('message.can-not-generate-invoice')];
    }

    public function invoiceContent(int $invoiceid): string
    {
        $invoice = $this->invoice->find($invoiceid); // @phpstan-ignore property.notFound
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

    public function currency(int $invoiceid): string
    {
        $invoice = Invoice::find($invoiceid);
        $currency_code = $invoice->currency ?? '';

        $cur = ' ';
        if (($invoice->grand_total ?? 0) == 0) {
            return $cur;
        }

        $currency = Currency::where('code', $currency_code)->first();
        if ($currency) {
            $cur = $currency->symbol ?? '';
            if (! $cur) {
                $cur = $currency->code ?? '';
            }
        }

        return (string) $cur;
    }

    public function sendInvoiceMail(int $userid, string $number, float|int $total, int $invoiceid): void
    {
        $contact = getContactData();
        // user
        $users = new User;
        $user = $users->find($userid);
        // check in the settings
        $settings = new Setting;
        /** @var Setting $setting */
        $setting = $settings::find(1);
        $invoiceurl = $this->invoiceUrl($invoiceid);
        // template
        /** @var Template $template */
        $template = TemplateType::getSelectedTemplate('invoice_mail');
        $type = '';
        $replace = [
            'name' => ($user->first_name ?? '').' '.($user->last_name ?? ''),
            'number' => $number,
            'address' => $user->address ?? '',
            'invoiceurl' => $invoiceurl,
            'content' => $this->invoiceContent($invoiceid),
            'currency' => $this->currency($invoiceid),
            'contact' => $contact['contact'],
            'logo' => $contact['logo'],
            'reply_email' => $setting->company_email,
        ];
        $type = $template->type()->value('name') ?? '';
        $mail = new PhpMailController;
        $mail->SendEmail($setting->email, $user->email ?? '', $template->data, $template->name, $template->type()->value('name'), $replace, $type);
    }

    public function invoiceUrl(int $invoiceid): UrlGenerator|string
    {
        return url('my-invoice/'.$invoiceid);
    }

    public function paymentEditById(int $id): JsonResponse
    {
        try {
            $payment = Payment::findOrFail($id);
            $clientid = $payment->user_id;
            $client = $this->user->where('id', $clientid)->firstOrFail(); // @phpstan-ignore property.notFound
            $symbol = Currency::where('code', $client->currency)->value('symbol');

            // Client's available credit balance = sum of their invoice_id = 0 rows.
            $availableCredit = new AdvanceSearchController()->getExtraAmt($clientid);

            // Invoices that still carry a balance and can absorb credit.
            $invoices = Invoice::where('user_id', $clientid)
                ->whereNotIn('status', ['success', 'Success'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($inv): array {
                    $paid = Payment::where('invoice_id', $inv->id)
                        ->where('payment_status', 'success')
                        ->sum('amount');

                    return [
                        'id' => $inv->id,
                        'number' => $inv->number,
                        'date' => $inv->date,
                        'grand_total' => $inv->grand_total,
                        'pending' => max(0, (float) $inv->grand_total - $paid),
                        'status' => $inv->status,
                    ];
                })
                ->filter(fn ($inv): bool => $inv['pending'] > 0)
                ->values();

            return successResponse('', [
                'payment' => [
                    'id' => $payment->id,
                    'invoice_id' => $payment->invoice_id,
                    'payment_method' => $payment->payment_method,
                ],
                'clientid' => $clientid,
                'available_credit' => $availableCredit,
                'invoices' => $invoices,
                'symbol' => $symbol,
                'currency' => $client->currency,
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }
}
