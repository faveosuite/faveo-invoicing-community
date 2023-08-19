<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Model\Common\TemplateType;
use App\Model\Mailjob\ExpiryMailDay;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Subscription;
use App\User;
use Carbon\Carbon;
use Symfony\Component\Mime\Email;

class BaseCronController extends Controller
{
    public function getUserById($id)
    {
        $user = User::find($id);

        return $user;
    }

    public function getOrderById($id)
    {
        $order = Order::find($id);

        return $order;
    }

    public function getInvoiceByOrderId($orderid)
    {
        $order = Order::find($orderid);
        $invoice = $order->invoice()->first();

        return $invoice;
    }

    public function getInvoiceItemByInvoiceId($invoiceid)
    {
        $invoice = Invoice::find($invoiceid);
        $item_id = $invoice->invoiceItem()->first();

        return $item_id;
    }

    public function getSubscriptions($allDays)
    {
        $sub = [];
        foreach ($allDays as $allDay) {
            if ($allDay >= 2) {
                if ($this->getAllDaysSubscription($allDay) != []) {
                    array_push($sub, $this->getAllDaysSubscription($allDay));
                }
            } elseif ($allDay == 1) {
                if (count($this->get1DaysUsers()) > 0) {
                    array_push($sub, $this->get1DaysSubscription());
                }
            } elseif ($allDay == 0) {
                if (count($this->get0DaysUsers()) > 0) {
                    array_push($sub, $this->get0DaysSubscription());
                }
                if (count($this->getPlus1Users()) > 0) {
                    array_push($sub, $this->getPlus1Subscription());
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
        $users = [];
        $users = $this->getAllDaysExpiryUsers($day);
        if (count($users) > 0) {
            return $users[0]['subscription'];
        }

        return $users;
    }

    public function get15DaysUsers()
    {
        $users = [];
        $users = $this->get15DaysExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['users'];
        }

        return $users;
    }

    public function get1DaysUsers()
    {
        $users = [];
        $users = $this->getOneDayExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['users'];
        }

        return $users;
    }

    public function get0DaysUsers()
    {
        $users = [];
        $users = $this->getOnDayExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['users'];
        }

        return $users;
    }

    public function getPlus1Users()
    {
        $users = [];
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
        $sub = Subscription::whereNotNull('update_ends_at')
                ->where('is_subscribed', 0)
                ->whereBetween('update_ends_at', [$yesterday, $tomorrow]);

        return $sub;
    }

    public function getOnDayExpiryInfo()
    {
        $yesterday = new Carbon('yesterday');
        $tomorrow = new Carbon('tomorrow');
        $sub = Subscription::whereNotNull('update_ends_at')
            ->where('is_subscribed', 0)
            ->whereBetween('update_ends_at', [$yesterday, $tomorrow]);

        return $sub;
    }

    public function getOneDayExpiryInfo()
    {
        $yesterday = new Carbon('-2 days');
        $today = new Carbon('today');
        $sub = Subscription::whereNotNull('update_ends_at')
                ->where('is_subscribed', 0)
                ->whereBetween('update_ends_at', [$yesterday, $today]);

        return $sub;
    }

    public function get15DaysExpiryInfo()
    {
        $plus14days = new Carbon('+14 days');
        $plus16days = new Carbon('+16 days');
        $sub = Subscription::whereNotNull('update_ends_at')
            ->where('is_subscribed', 0)
            ->whereBetween('update_ends_at', [$plus14days, $plus16days]);

        return $sub;
    }

    public function getAllDaysExpiryInfo($day)
    {
        $minus1day = new Carbon('+'.($day - 1).' days');
        $plus1day = new Carbon('+'.($day + 1).' days');
        $sub = Subscription::whereNotNull('update_ends_at')
            ->where('is_subscribed', 0)
            ->whereBetween('update_ends_at', [$minus1day, $plus1day]);

        return $sub;
    }

    public function mail($user, $end, $product, $order, $sub)
    {
        try {
            $contact = getContactData();
            $product_type = Product::where('name', $product)->value('type');
            $expiryDays = ExpiryMailDay::first()->cloud_days;
            //check in the settings
            $settings = new \App\Model\Common\Setting();
            $setting = $settings->where('id', 1)->first();

            $mail = new \App\Http\Controllers\Common\PhpMailController();
            $mailer = $mail->setMailConfig($setting);
            $url = url('my-orders');
            //template
            $templates = new \App\Model\Common\Template();
            $temp_id = $setting->subscription_going_to_end;

            $template = $templates->where('id', $temp_id)->first();
            $data = $template->data;
            $date = date_create($end);
            $end = date_format($date, 'l, F j, Y');

            $delDate = strtotime($end.' +'.$expiryDays.' days');
            $deletionDate = date('l, F j, Y', $delDate);

            $email = (new Email())
            ->from($setting->email)
            ->to($user->email)
             ->subject($template->name)
             ->html($mail->mailTemplate($template->data, $templatevariables = ['name' => ucfirst($user->first_name).' '.ucfirst($user->last_name),
                 'deletionDate' => ($product_type == '4') ? $deletionDate : '',
                 'product_type' => ($product_type == '4') ? 'Deletion Date' : '',
                 'expiry' => $end,
                 'product' => $product,
                 'number' => $order->number,
                 'contact' => $contact['contact'],
                 'logo' => $contact['logo'],
                 'url' => $url, ]));
            $mailer->send($email);
            $mail->email_log_success($setting->email, $user->email, $template->name, $data);
        } catch (\Exception $ex) {
            dd($ex);
            $mail->email_log_fail($setting->email, $user->email, $template->name, $data);
        }
    }

    public function Auto_renewalMail($user, $end, $product, $order, $sub)
    {
        $contact = getContactData();
        $product_type = Product::where('name', $product)->value('type');
        $plan_id = Subscription::find($sub);
        $renewPrice = PlanPrice::where('plan_id',$plan_id->plan_id)->value('renew_price');
        $expiryDays = ExpiryMailDay::first()->cloud_days;
        //check in the settings
        $settings = new \App\Model\Common\Setting();
        $setting = $settings->where('id', 1)->first();

        $mail = new \App\Http\Controllers\Common\PhpMailController();
        $mailer = $mail->setMailConfig($setting);

        //template
        $templates = new \App\Model\Common\Template();
        $temp_id = TemplateType::where('name', 'auto_subscription_going_to_end')->value('id');

        $template = $templates->where('type', $temp_id)->first();
        $data = $template->data;
        $date = date_create($end);
        $end = date_format($date, 'l, F j, Y ');
        $delDate = strtotime($end.' +'.$expiryDays.' days');
        $deletionDate = date('l, F j, Y', $delDate);

        try {
            $email = (new Email())
        ->from($setting->email)
        ->to($user->email)
         ->subject($template->name)
         ->html($mail->mailTemplate($template->data, $templatevariables = ['name' => ucfirst($user->first_name).' '.ucfirst($user->last_name),
             'renewPrice' => currencyFormat($renewPrice, $code = $user->currency),
             'deletionDate' => ($product_type == '4') ? $deletionDate : '',
             'product_type' => ($product_type == '4') ? 'Deletion Date' : '',
             'expiry' => $end,
             'product' => $product,
             'contact' => $contact['contact'],
             'logo' => $contact['logo'],
             'number' => $order->number, ]));
            $mailer->send($email);
            $mail->email_log_success($setting->email, $user->email, $template->name, $data);
        } catch (\Exception $ex) {
            $mail->email_log_fail($setting->email, $user->email, $template->name, $data);
        }
    }

    public function Expiredsub_Mail($user, $end, $product, $order, $sub)
    {
        $contact = getContactData();
        $product_type = Product::where('name', $product)->value('type');
        $expiryDays = ExpiryMailDay::first()->cloud_days;
        //check in the settings
        $settings = new \App\Model\Common\Setting();
        $setting = $settings->where('id', 1)->first();

        $mail = new \App\Http\Controllers\Common\PhpMailController();
        $mailer = $mail->setMailConfig($setting);

        //template
        $templates = new \App\Model\Common\Template();
        $temp_id = $setting->subscription_over;

        $template = $templates->where('id', $temp_id)->first();
        $data = $template->data;
        $url = url('my-orders');
        $date = date_create($end);
        $end = date_format($date, 'l, F j, Y ');
        $delDate = strtotime($end.' +'.$expiryDays.' days');
        $deletionDate = date('l, F j, Y', $delDate);

        try {
            $email = (new Email())
           ->from($setting->email)
           ->to($user->email)
           ->subject($template->name)
           ->html($mail->mailTemplate($template->data, $templatevariables = ['name' => ucfirst($user->first_name).' '.ucfirst($user->last_name),
               'deletionDate' => ($product_type == '4') ? $deletionDate : '',
               'product_type' => ($product_type == '4') ? 'Deletion Date' : '',
               'expiry' => $end,
               'product' => $product,
               'number' => $order->number,
               'contact' => $contact['contact'],
               'logo' => $contact['logo'],
               'url' => $url, ]));
            $mailer->send($email);
            $mail->email_log_success($setting->email, $user->email, $template->name, $data);
        } catch (\Exception $ex) {
            $mail->email_log_fail($setting->email, $user->email, $template->name, $data);
        }
    }
}
