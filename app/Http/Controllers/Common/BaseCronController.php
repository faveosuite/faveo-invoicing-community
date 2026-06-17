<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Model\Common\Setting;
use App\Model\Common\TemplateType;
use App\Model\Mailjob\ExpiryMailDay;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
use Carbon\Carbon;

class BaseCronController extends Controller
{
    public function getUserById($id)
    {
        return User::find($id);
    }

    public function getOrderById($id)
    {
        return Order::find($id);
    }

    public function getInvoiceByOrderId($orderid)
    {
        $order = Order::find($orderid);

        return $order->invoice()->first();
    }

    public function getInvoiceItemByInvoiceId($invoiceid)
    {
        $invoice = Invoice::find($invoiceid);

        return $invoice->invoiceItem()->first();
    }

    /**
     * @return mixed[]
     */
    public function getSubscriptions($allDays): array
    {
        $sub = [];
        foreach ($allDays as $allDay) {
            if ($allDay >= 2) {
                if ($this->getAllDaysSubscription($allDay) != []) {
                    $sub[] = $this->getAllDaysSubscription($allDay);
                }
            } elseif ($allDay == 1) {
                if (count($this->get1DaysUsers()) > 0) {
                    $sub[] = $this->get1DaysSubscription();
                }
            } elseif ($allDay == 0) {
                if (count($this->get0DaysUsers()) > 0) {
                    $sub[] = $this->get0DaysSubscription();
                }

                if (count($this->getPlus1Users()) > 0) {
                    $sub[] = $this->getPlus1Subscription();
                }
            }
        }

        return $sub;
    }

    // if (count($this->get15DaysUsers())) {
    //     array_push($sub, $this->get15DaysSubscription());
    // }

    public function getAllDaysSubscription($day)
    {
        $users = $this->getAllDaysExpiryUsers($day);
        if (count($users) > 0) {
            return $users[0]['subscription'];
        }

        return $users;
    }

    public function get15DaysUsers()
    {
        $users = $this->get15DaysExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['users'];
        }

        return $users;
    }

    public function get1DaysUsers()
    {
        $users = $this->getOneDayExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['users'];
        }

        return $users;
    }

    public function get0DaysUsers()
    {
        $users = $this->getOnDayExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['users'];
        }

        return $users;
    }

    public function getPlus1Users()
    {
        $users = $this->getExpiredUsers();
        if (count($users) > 0) {
            return $users[0]['users'];
        }

        return $users;
    }

    public function get30DaysUsers()
    {
        $users = $this->get30DaysExpiryUsers();
        //dd($users);
        if (count($users) > 0) {
            return $users[0]['users'];
        }

        return $users;
    }

    public function getExpiredInfo()
    {
        $yesterday = new Carbon('today');
        $tomorrow = new Carbon('+2 days');

        return Subscription::whereNotNull('update_ends_at')
                ->where('is_subscribed', 0)
                ->whereBetween('update_ends_at', [$yesterday, $tomorrow]);
    }

    public function getOnDayExpiryInfo()
    {
        $yesterday = new Carbon('yesterday');
        $tomorrow = new Carbon('tomorrow');

        return Subscription::whereNotNull('update_ends_at')
            ->where('is_subscribed', 0)
            ->whereBetween('update_ends_at', [$yesterday, $tomorrow]);
    }

    public function getOneDayExpiryInfo()
    {
        $yesterday = new Carbon('-2 days');
        $today = new Carbon('today');

        return Subscription::whereNotNull('update_ends_at')
                ->where('is_subscribed', 0)
                ->whereBetween('update_ends_at', [$yesterday, $today]);
    }

    public function get15DaysExpiryInfo()
    {
        $plus14days = new Carbon('+14 days');
        $plus16days = new Carbon('+16 days');

        return Subscription::whereNotNull('update_ends_at')
            ->where('is_subscribed', 0)
            ->whereBetween('update_ends_at', [$plus14days, $plus16days]);
    }

    public function getAllDaysExpiryInfo($day)
    {
        $minus1day = new Carbon('+'.($day - 1).' days');
        $plus1day = new Carbon('+'.($day + 1).' days');

        return Subscription::whereNotNull('update_ends_at')
            ->where('is_subscribed', 0)
            ->whereBetween('update_ends_at', [$minus1day, $plus1day]);
    }

    public function mail($user, $end, $productId, $order, $sub): void
    {
        $contact = getContactData();
        $product = Product::where('id', $productId)->first();
        $product_type = $product->type;
        $expiryDays = ExpiryMailDay::first()->cloud_days;
        //check in the settings
        $setting = Setting::find(1);
        //template
        $template = TemplateType::getSelectedTemplate('subscription_going_to_end_mail');
        $date = date_create($end);
        $end = date_format($date, 'l, F j, Y');

        $delDate = strtotime($end.' +'.$expiryDays.' days');
        $deletionDate = date('l, F j, Y', $delDate);

        $replace = ['name' => ucfirst((string) $user->first_name).' '.ucfirst((string) $user->last_name),
            'deletionDate' => ($product_type == '4') ? $deletionDate : '',
            'product_type' => ($product_type == '4') ? 'Deletion Date' : '',
            'expiry' => $end,
            'product' => $product->name,
            'number' => $order->number,
            'url' => url('my-orders'),
            'contact' => $contact['contact'],
            'logo' => $contact['logo'],
            'reply_email' => $setting->company_email,

        ];
        $type = $template?->type()->value('name') ?? '';
        $mail = new PhpMailController();
        $mail->SendEmail($setting->email, $user->email, $template->data, $template->name, $template->type()->value('name'), $replace, $type);
    }

    public function Auto_renewalMail($user, $end, $productId, $order, $sub): void
    {
        $contact = getContactData();
        $product = Product::where('id', $productId)->first();
        $product_type = $product->type;
        $plan_id = Subscription::find($sub);
        $currency = getCurrencyForClient($user->country);

        $renewPrice = PlanPrice::where('plan_id', $plan_id->plan_id)->where('currency', $currency)->value('renew_price');
        $expiryDays = ExpiryMailDay::first()->cloud_days;
        //check in the settings
        $settings = new Setting();
        $setting = $settings->where('id', 1)->first();

        $mail = new PhpMailController();

        //template
        $template = TemplateType::getSelectedTemplate('auto_subscription_going_to_end');
        $data = $template->data;

        $date = date_create($end);
        $end = date_format($date, 'l, F j, Y ');
        $delDate = strtotime($end.' +'.$expiryDays.' days');
        $deletionDate = date('l, F j, Y', $delDate);

        $replace = ['name' => ucfirst((string) $user->first_name).' '.ucfirst((string) $user->last_name),
            'renewPrice' => currencyFormat($renewPrice, $code = $currency),
            'deletionDate' => ($product_type == '4') ? $deletionDate : '',
            'product_type' => ($product_type == '4') ? 'Deletion Date' : '',
            'expiry' => $end,
            'product' => $product->name,
            'number' => $order->number, 'contact' => $contact['contact'],
            'logo' => $contact['logo'],
            'reply_email' => $setting->company_email,
        ];

        $type = $template?->type()->value('name') ?? '';
        $from = $setting->email;
        $to = $user->email;
        $subject = $template->name;
        $data = $template->data;
        $mail->SendEmail($from, $to, $data, $subject, $template->type()->value('name'), $replace, $type);
    }

    public function Expiredsub_Mail($user, $end, $productId, $order, $sub): void
    {
        $contact = getContactData();
        $product = Product::where('id', $productId)->first();
        $product_type = $product->type;
        $expiryDays = ExpiryMailDay::first()->cloud_days;

        //check in the settings
        $settings = new Setting();
        $setting = $settings->where('id', 1)->first();

        $mail = new PhpMailController();

        //template
        $template = TemplateType::getSelectedTemplate('subscription_over_mail');
        $data = $template->data;

        $date = date_create($end);
        $end = date_format($date, 'l, F j, Y ');
        $delDate = strtotime($end.' +'.$expiryDays.' days');
        $deletionDate = date('l, F j, Y', $delDate);

        $replace = ['name' => ucfirst((string) $user->first_name).' '.ucfirst((string) $user->last_name),
            'deletionDate' => ($product_type == '4') ? $deletionDate : '',
            'product_type' => ($product_type == '4') ? 'Deletion Date' : '',
            'expiry' => $end,
            'product' => $product->name,
            'number' => $order->number,
            'contact' => $contact['contact'],
            'logo' => $contact['logo'],
            'url' => url('my-orders'),
            'reply_email' => $setting->company_email,
        ];
        $type = $template?->type()->value('name') ?? '';
        $from = $setting->email;
        $to = $user->email;
        $subject = $template->name;
        $data = $template->data;
        $mail->SendEmail($from, $to, $data, $subject, $template->type()->value('name'), $replace, $type);
    }
}
