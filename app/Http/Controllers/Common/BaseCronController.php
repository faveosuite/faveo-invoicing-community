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
    public function getUserById(int $id): ?\App\User
    {
        return User::find($id);
    }

    public function getOrderById(int $id): ?\App\Model\Order\Order
    {
        return Order::find($id);
    }

    public function getInvoiceByOrderId(int $orderid): ?\App\Model\Order\Invoice
    {
        $order = Order::find($orderid);
        if (!$order) {
            return null;
        }
        /** @var mixed $invoice */
        $invoice = $order->invoice()->first();

        return $invoice instanceof Invoice ? $invoice : null;
    }

    public function getInvoiceItemByInvoiceId(int $invoiceid): ?\App\Model\Order\InvoiceItem
    {
        $invoice = Invoice::find($invoiceid);

        return $invoice ? $invoice->invoiceItem()->first() : null;
    }

    /**
     * @return mixed[]
     * @param array<mixed> $allDays
     */
    public function getSubscriptions(array $allDays): array
    {
        $sub = [];
        foreach ($allDays as $allDay) {
            if ($allDay >= 2) {
                if ($this->getAllDaysSubscription($allDay) != []) {
                    $sub[] = $this->getAllDaysSubscription($allDay);
                }
            } elseif ($allDay == 1) {
                if (count($this->get1DaysUsers()) > 0) {
                    $sub[] = $this->get1DaysSubscription(); // @phpstan-ignore method.notFound
                }
            } elseif ($allDay == 0) {
                if (count($this->get0DaysUsers()) > 0) {
                    $sub[] = $this->get0DaysSubscription(); // @phpstan-ignore method.notFound
                }

                if (count($this->getPlus1Users()) > 0) {
                    $sub[] = $this->getPlus1Subscription(); // @phpstan-ignore method.notFound
                }
            }
        }

        return $sub;
    }

    // if (count($this->get15DaysUsers())) {
    //     array_push($sub, $this->get15DaysSubscription());
    // }

    public function getAllDaysSubscription(int $day): mixed
    {
        $users = $this->getAllDaysExpiryUsers($day); // @phpstan-ignore method.notFound
        if (count($users) > 0) {
            return $users[0]['subscription'];
        }

        return $users;
    }

    public function get15DaysUsers(): mixed
    {
        $users = $this->get15DaysExpiryUsers(); // @phpstan-ignore method.notFound
        if (count($users) > 0) {
            return $users[0]['users'];
        }

        return $users;
    }

    public function get1DaysUsers(): mixed
    {
        $users = $this->getOneDayExpiryUsers(); // @phpstan-ignore method.notFound
        if (count($users) > 0) {
            return $users[0]['users'];
        }

        return $users;
    }

    public function get0DaysUsers(): mixed
    {
        $users = $this->getOnDayExpiryUsers(); // @phpstan-ignore method.notFound
        if (count($users) > 0) {
            return $users[0]['users'];
        }

        return $users;
    }

    public function getPlus1Users(): mixed
    {
        $users = $this->getExpiredUsers(); // @phpstan-ignore method.notFound
        if (count($users) > 0) {
            return $users[0]['users'];
        }

        return $users;
    }

    public function get30DaysUsers(): mixed
    {
        $users = $this->get30DaysExpiryUsers(); // @phpstan-ignore method.notFound
        //dd($users);
        if (count($users) > 0) {
            return $users[0]['users'];
        }

        return $users;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Model\Product\Subscription>
     */
    public function getExpiredInfo(): \Illuminate\Database\Eloquent\Builder
    {
        $yesterday = new Carbon('today');
        $tomorrow = new Carbon('+2 days');

        return Subscription::whereNotNull('update_ends_at')
                ->where('is_subscribed', 0)
                ->whereBetween('update_ends_at', [$yesterday, $tomorrow]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Model\Product\Subscription>
     */
    public function getOnDayExpiryInfo(): \Illuminate\Database\Eloquent\Builder
    {
        $yesterday = new Carbon('yesterday');
        $tomorrow = new Carbon('tomorrow');

        return Subscription::whereNotNull('update_ends_at')
            ->where('is_subscribed', 0)
            ->whereBetween('update_ends_at', [$yesterday, $tomorrow]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Model\Product\Subscription>
     */
    public function getOneDayExpiryInfo(): \Illuminate\Database\Eloquent\Builder
    {
        $yesterday = new Carbon('-2 days');
        $today = new Carbon('today');

        return Subscription::whereNotNull('update_ends_at')
                ->where('is_subscribed', 0)
                ->whereBetween('update_ends_at', [$yesterday, $today]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Model\Product\Subscription>
     */
    public function get15DaysExpiryInfo(): \Illuminate\Database\Eloquent\Builder
    {
        $plus14days = new Carbon('+14 days');
        $plus16days = new Carbon('+16 days');

        return Subscription::whereNotNull('update_ends_at')
            ->where('is_subscribed', 0)
            ->whereBetween('update_ends_at', [$plus14days, $plus16days]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Model\Product\Subscription>
     */
    public function getAllDaysExpiryInfo(int $day): \Illuminate\Database\Eloquent\Builder
    {
        $minus1day = new Carbon('+'.($day - 1).' days');
        $plus1day = new Carbon('+'.($day + 1).' days');

        return Subscription::whereNotNull('update_ends_at')
            ->where('is_subscribed', 0)
            ->whereBetween('update_ends_at', [$minus1day, $plus1day]);
    }

    public function mail(\App\User $user, string $end, int $productId, \App\Model\Order\Order $order, \App\Model\Product\Subscription $sub): void
    {
        $contact = getContactData();
        $product = Product::where('id', $productId)->first();
        if (!$product) {
            return;
        }
        $product_type = $product->type;
        $expiryMailDay = ExpiryMailDay::first();
        $expiryDays = $expiryMailDay ? $expiryMailDay->cloud_days : 0;
        
        $setting = Setting::find(1);
        if (!$setting) {
            return;
        }
        
        $template = TemplateType::getSelectedTemplate('subscription_going_to_end_mail');
        if (!$template) {
            return;
        }
        
        $date = date_create($end);
        if ($date === false) {
            return;
        }
        $formattedEnd = date_format($date, 'l, F j, Y');

        $delDate = strtotime($formattedEnd.' +'.$expiryDays.' days');
        if ($delDate === false) {
            return;
        }
        $deletionDate = date('l, F j, Y', $delDate);

        $replace = [
            'name' => ucfirst((string) $user->first_name).' '.ucfirst((string) $user->last_name),
            'deletionDate' => ($product_type == '4') ? $deletionDate : '',
            'product_type' => ($product_type == '4') ? 'Deletion Date' : '',
            'expiry' => $formattedEnd,
            'product' => (string) $product->name,
            'number' => (string) $order->number,
            'url' => url('my-orders'),
            'contact' => $contact['contact'],
            'logo' => $contact['logo'],
            'reply_email' => (string) $setting->company_email,
        ];

        $type = (string) ($template->type()->value('name') ?? '');
        $mail = new PhpMailController();
        $mail->SendEmail((string) $setting->email, (string) $user->email, (string) $template->data, (string) $template->name, $type, $replace, $type);
    }

    public function Auto_renewalMail(\App\User $user, string $end, int $productId, \App\Model\Order\Order $order, int $sub): void
    {
        $contact = getContactData();
        $product = Product::where('id', $productId)->first();
        if (!$product) {
            return;
        }
        $product_type = $product->type;
        $plan_id = Subscription::find($sub);
        if (!$plan_id) {
            return;
        }
        $currency = getCurrencyForClient((string) $user->country);

        $renewPrice = PlanPrice::where('plan_id', $plan_id->plan_id)->where('currency', $currency)->value('renew_price');
        $expiryMailDay = ExpiryMailDay::first();
        $expiryDays = $expiryMailDay ? $expiryMailDay->cloud_days : 0;
        
        $setting = Setting::where('id', 1)->first();
        if (!$setting) {
            return;
        }

        $mail = new PhpMailController();

        //template
        $template = TemplateType::getSelectedTemplate('auto_subscription_going_to_end');
        if (!$template) {
            return;
        }

        $date = date_create($end);
        if ($date === false) {
            return;
        }
        $formattedEnd = date_format($date, 'l, F j, Y ');
        $delDate = strtotime($formattedEnd.' +'.$expiryDays.' days');
        if ($delDate === false) {
            return;
        }
        $deletionDate = date('l, F j, Y', $delDate);

        $replace = [
            'name' => ucfirst((string) $user->first_name).' '.ucfirst((string) $user->last_name),
            'renewPrice' => currencyFormat($renewPrice, $currency),
            'deletionDate' => ($product_type == '4') ? $deletionDate : '',
            'product_type' => ($product_type == '4') ? 'Deletion Date' : '',
            'expiry' => $formattedEnd,
            'product' => (string) $product->name,
            'number' => (string) $order->number,
            'contact' => $contact['contact'],
            'logo' => $contact['logo'],
            'reply_email' => (string) $setting->company_email,
        ];

        $type = (string) ($template->type()->value('name') ?? '');
        $from = (string) $setting->email;
        $to = (string) $user->email;
        $subject = (string) $template->name;
        $data = (string) $template->data;
        $mail->SendEmail($from, $to, $data, $subject, $type, $replace, $type);
    }

    public function Expiredsub_Mail(\App\User $user, string $end, int $productId, \App\Model\Order\Order $order, mixed $sub): void
    {
        $contact = getContactData();
        $product = Product::where('id', $productId)->first();
        if (!$product) {
            return;
        }
        $product_type = $product->type;
        $expiryMailDay = ExpiryMailDay::first();
        $expiryDays = $expiryMailDay ? $expiryMailDay->cloud_days : 0;

        //check in the settings
        $setting = Setting::where('id', 1)->first();
        if (!$setting) {
            return;
        }

        $mail = new PhpMailController();

        //template
        $template = TemplateType::getSelectedTemplate('subscription_over_mail');
        if (!$template) {
            return;
        }

        $date = date_create($end);
        if ($date === false) {
            return;
        }
        $formattedEnd = date_format($date, 'l, F j, Y ');
        $delDate = strtotime($formattedEnd.' +'.$expiryDays.' days');
        if ($delDate === false) {
            return;
        }
        $deletionDate = date('l, F j, Y', $delDate);

        $replace = [
            'name' => ucfirst((string) $user->first_name).' '.ucfirst((string) $user->last_name),
            'deletionDate' => ($product_type == '4') ? $deletionDate : '',
            'product_type' => ($product_type == '4') ? 'Deletion Date' : '',
            'expiry' => $formattedEnd,
            'product' => (string) $product->name,
            'number' => (string) $order->number,
            'contact' => $contact['contact'],
            'logo' => $contact['logo'],
            'url' => url('my-orders'),
            'reply_email' => (string) $setting->company_email,
        ];
        
        $type = (string) ($template->type()->value('name') ?? '');
        $from = (string) $setting->email;
        $to = (string) $user->email;
        $subject = (string) $template->name;
        $data = (string) $template->data;
        $mail->SendEmail($from, $to, $data, $subject, $type, $replace, $type);
    }
}
