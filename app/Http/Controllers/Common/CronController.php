<?php

namespace App\Http\Controllers\Common;

use Override;
use Illuminate\Support\Facades\Date;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Session;
use DB;
use App\EmailValidationResults;
use App\FailedWhatsappMessage;
use App\Jobs\SendWhatsappMessage;
use App\Model\Common\MsgDeliveryReports;
use App\Model\Common\StatusSetting;
use App\Model\Common\Template;
use App\Model\Mailjob\ExpiryMailDay;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Payment\Plan;
use App\Model\Product\Subscription;
use App\Plugins\Stripe\Controllers\SettingsController;
use App\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use GuzzleHttp\Client;

class CronController extends BaseCronController
{
    protected $subscription;

    protected $order;

    protected $user;

    protected $template;

    protected $invoice;

    protected $client;

    protected $PostSubscriptionHandle;

    public function __construct()
    {
        $subscription = new Subscription();
        $this->sub = $subscription;

        $plan = new Plan();
        $this->plan = $plan;

        $order = new Order();
        $this->order = $order;

        $user = new User();
        $this->user = $user;

        $template = new Template();
        $this->template = $template;

        $invoice = new Invoice();
        $this->invoice = $invoice;

        $payment = new Payment();
        $this->payment = $payment;

        $stripeController = new SettingsController();
        $this->stripeController = $stripeController;

        $this->client = new Client();
    }

    public function getExpiredInfoByOrderId($orderid)
    {
        $yesterday = new Carbon('today');
        $sub = $this->sub
            ->where('order_id', $orderid)
            ->where('update_ends_at', '!=', '0000-00-00 00:00:00')
            ->whereNotNull('update_ends_at')
            ->where('update_ends_at', '<', $yesterday)
            ->first();

        return $sub;
    }

    public function getAllDaysExpiryUsers($day)
    {
        $sub = $this->getAllDaysExpiryInfo($day);
        //dd($sub->get());
        $users = [];
        if ($sub->get()->count() > 0) {
            foreach ($sub->get() as $key => $value) {
                $users[$key]['users'] = $this->sub->find($value->id)->user()->get();
                $users[$key]['orders'] = $this->sub->find($value->id)->order()->get();
                $users[$key]['subscription'] = $value;
            }
        }

        return $users;
    }

    public function get15DaysExpiryUsers()
    {
        $sub = $this->get15DaysExpiryInfo();
        $users = [];
        if ($sub->get()->count() > 0) {
            foreach ($sub->get() as $key => $value) {
                $users[$key]['users'] = $this->sub->find($value->id)->user()->get();
                $users[$key]['orders'] = $this->sub->find($value->id)->order()->get();
                $users[$key]['subscription'] = $value;
            }
        }

        return $users;
    }

    public function getOneDayExpiryUsers()
    {
        $sub = $this->getOneDayExpiryInfo();
        $users = [];
        if ($sub->get()->count() > 0) {
            foreach ($sub->get() as $key => $value) {
                $users[$key]['users'] = $this->sub->find($value->id)->user()->get();
                $users[$key]['orders'] = $this->sub->find($value->id)->order()->get();
                $users[$key]['subscription'] = $value;
            }
        }

        return $users;
    }

    public function getOnDayExpiryUsers()
    {
        $sub = $this->getOnDayExpiryInfo();
        $users = [];
        if ($sub->get()->count() > 0) {
            foreach ($sub->get() as $key => $value) {
                $users[$key]['users'] = $this->sub->find($value->id)->user()->get();
                $users[$key]['orders'] = $this->sub->find($value->id)->order()->get();
                $users[$key]['subscription'] = $value;
            }
        }

        return $users;
    }

    public function getExpiredUsers()
    {
        $sub = $this->getExpiredInfo();
        $users = [];
        if ($sub->get()->count() > 0) {
            foreach ($sub->get() as $key => $value) {
                $users[$key]['users'] = $this->sub->find($value->id)->user()->get();
                $users[$key]['orders'] = $this->sub->find($value->id)->order()->get();
                $users[$key]['subscription'] = $value;
            }
        }

        return $users;
    }

    public function get30DaysOrders()
    {
        $users = [];
        $users = $this->get30DaysExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['orders'];
        }

        return $users;
    }

    public function get15DaysOrders()
    {
        $users = [];
        $users = $this->get15DaysExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['orders'];
        }

        return $users;
    }

    public function get1DaysOrders()
    {
        $users = [];
        $users = $this->getOneDayExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['orders'];
        }

        return $users;
    }

    public function get0DaysOrders()
    {
        $users = [];
        $users = $this->getOnDayExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['orders'];
        }

        return $users;
    }

    public function getPlus1Orders()
    {
        $users = [];
        $users = $this->getExpiredUsers();
        if (count($users) > 0) {
            return $users[0]['orders'];
        }

        return $users;
    }

    public function get15DaysSubscription()
    {
        $users = [];
        $users = $this->get15DaysExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['subscription'];
        }

        return $users;
    }

    public function get1DaysSubscription()
    {
        $users = [];
        $users = $this->getOneDayExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['subscription'];
        }

        return $users;
    }

    public function get0DaysSubscription()
    {
        $users = [];
        $users = $this->getOnDayExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['subscription'];
        }

        return $users;
    }

    public function getPlus1Subscription()
    {
        $users = [];
        $users = $this->getExpiredUsers();
        if (count($users) > 0) {
            return $users[0]['subscription'];
        }

        return $users;
    }

    public function getUsers()
    {
        $users = [];
        if (count($this->get30DaysUsers())) {
            array_push($users, $this->get30DaysUsers());
        }

        if (count($this->get15DaysUsers())) {
            array_push($users, $this->get15DaysUsers());
        }

        if (count($this->get1DaysUsers())) {
            array_push($users, $this->get1DaysUsers());
        }

        if (count($this->get0DaysUsers())) {
            array_push($users, $this->get0DaysUsers());
        }

        if (count($this->getPlus1Users())) {
            array_push($users, $this->getPlus1Users());
        }

        return $users;
    }

    #[Override]
    public function getSubscriptions($days)
    {
        $decodedData = json_decode((string) $days[0]);

        if ($decodedData === null && json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        $subscriptions = [];
        foreach ($decodedData as $day) {
            $day = (int) $day;

            // Calculate the start and end dates
            $startDate = Date::now()->toDateString();
            $endDate = Date::now()->addDays($day)->toDateString();

            $subscriptionsForDay = Subscription::where(function (Builder $query) use ($endDate): void {
                $query->where('update_ends_at', 'LIKE', $endDate.'%')
                    ->orWhere('support_ends_at', 'LIKE', $endDate.'%')
                    ->orWhere('ends_at', 'LIKE', $endDate.'%');
            })
                ->join('orders', 'subscriptions.order_id', '=', 'orders.id')
                ->where('orders.order_status', 'executed')
                ->where('subscriptions.is_subscribed', '0') // Apply this condition correctly
                ->select([
                    'subscriptions.*',
                    'orders.id as order_id',
                    'orders.*',
                    'subscriptions.id as id',
                ])
                ->get()
                ->toArray(); // Convert the collection to an array

            $subscriptions = array_merge($subscriptions, $subscriptionsForDay);
        }

        $uniqueSubscriptions = array_map(unserialize(...), array_unique(array_map(serialize(...), $subscriptions)));

        return $uniqueSubscriptions;
    }

    public function getautoSubscriptions($days)
    {
        $decodedData = json_decode((string) $days[0]);

        if ($decodedData === null && json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        $subscriptions = [];
        foreach ($decodedData as $day) {
            $day = (int) $day;

            // Calculate the start and end dates
            $endDate = Date::now()->addDays($day)->toDateString();

            $subscriptionsForDay = Subscription::where(function (Builder $query) use ($endDate): void {
                $query->where('update_ends_at', 'LIKE', $endDate.'%')
                    ->orWhere('support_ends_at', 'LIKE', $endDate.'%')
                    ->orWhere('ends_at', 'LIKE', $endDate.'%');
            })
                ->join('orders', 'subscriptions.order_id', '=', 'orders.id')
                ->where('orders.order_status', 'executed')
                ->where('subscriptions.is_subscribed', '1') // Apply this condition correctly
                ->select([
                    'subscriptions.*',
                    'orders.id as order_id',
                    'orders.*',
                    'subscriptions.id as id',
                ])
                ->get()
                ->toArray(); // Convert the collection to an array

            $subscriptions = array_merge($subscriptions, $subscriptionsForDay);
        }

        $uniqueSubscriptions = array_map(unserialize(...), array_unique(array_map(serialize(...), $subscriptions)));

        return $uniqueSubscriptions;
    }

    public function getPostSubscriptions($days)
    {
        $decodedData = json_decode((string) $days[0]);

        if ($decodedData === null && json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        $subscriptions = [];

        // Calculate the end date as today

        foreach ($decodedData as $day) {
            $day = (int) $day;

            // Calculate the start date based on the specific day value from $decodedData
            $endDate = Date::now()->subDays($day)->toDateString(); // Use $day here

            $subscriptionsForDay = Subscription::where('update_ends_at', 'LIKE', $endDate.'%')
                ->orWhere('support_ends_at', 'LIKE', $endDate.'%')
                ->orWhere('ends_at', 'LIKE', $endDate.'%')
                ->join('orders', 'subscriptions.order_id', '=', 'orders.id')
                ->where('orders.order_status', 'executed')
                ->select([
                    'subscriptions.*',
                    'orders.id as order_id',
                    'orders.*',
                    'subscriptions.id as id',
                ])
                ->get()
                ->toArray();

            $subscriptions = array_merge($subscriptions, $subscriptionsForDay);
        }

        $uniqueSubscriptions = array_map(unserialize(...), array_unique(array_map(serialize(...), $subscriptions)));

        return $uniqueSubscriptions;
    }

    public function eachSubscription()
    {
        $status = StatusSetting::value('expiry_mail');
        if ($status == 1) {
            $allDays = ExpiryMailDay::pluck('days')->toArray();
            $sub = $this->getSubscriptions($allDays);
            foreach ($sub as $value) {
                $value = (object) $value;
                $userid = $value->user_id;
                $user = $this->getUserById($userid);
                $end = $value->update_ends_at;
                $order = $this->getOrderById($value->order_id);
                $invoice = $this->getInvoiceByOrderId($value->order_id);
                $item = $this->getInvoiceItemByInvoiceId($invoice->id);
                $product = $item->product_id;
                if (emailSendingStatus()) {
                    $this->mail($user, $end, $product, $order, $value->id);
                }
            }
        }
    }

    public function autoRenewalExpiryNotify()
    {
        $status = StatusSetting::value('subs_expirymail');
        if ($status == 1) {
            $Days = ExpiryMailDay::pluck('autorenewal_days')->toArray();
            $cron = new AutorenewalCronController();
            $Autosub = $this->getautoSubscriptions($Days);
            foreach ($Autosub as $value) {
                $value = (object) $value;
                $userid = $value->user_id;
                $user = $this->getUserById($userid);
                $end = $value->update_ends_at;
                $order = $this->getOrderById($value->order_id);
                $invoice = $this->getInvoiceByOrderId($value->order_id);
                $item = $this->getInvoiceItemByInvoiceId($invoice->id);
                $product = $item->product_id;
                if (emailSendingStatus()) {
                    $this->Auto_renewalMail($user, $end, $product, $order, $value->id);
                }
            }
        }
    }

    public function postRenewalNotify()
    {
        $status = StatusSetting::value('post_expirymail');
        if ($status == 1) {
            $periods = ExpiryMailDay::pluck('postexpiry_days')->toArray();
            $cron = new AutorenewalCronController();
            $postSub = $this->getPostSubscriptions($periods);
            foreach ($postSub as $value) {
                $value = (object) $value;
                $userid = $value->user_id;
                $user = $this->getUserById($userid);
                $end = $value->update_ends_at;
                $order = Order::find($value->order_id);
                if ($order) {
                    $order = $this->getOrderById($value->order_id);
                    $invoice = $this->getInvoiceByOrderId($value->order_id);
                    $item = $this->getInvoiceItemByInvoiceId($invoice->id);
                    $product = $item->product_id;
                    if (emailSendingStatus()) {
                        $this->Expiredsub_Mail($user, $end, $product, $order, $value->id);
                    }
                }
            }
        }
    }

    /**
     * Deletes old invoices based on specified criteria.
     *
     * This function checks if invoices should be deleted, retrieves old invoices,
     * and deletes invoices that meet specific conditions.
     *
     * @return void
     */
    public function invoicesDeletion()
    {
        if (! $this->shouldDeleteInvoices()) {
            return;
        }

        $days = ExpiryMailDay::value('invoice_days');
        $dueInvoices = $this->getOldInvoices($days);

        foreach ($dueInvoices as $invoice) {
            if ($this->canDeleteInvoice($invoice)) {
                $this->deleteInvoice($invoice);
            }
        }
    }

    public function reoonLogsDeletion()
    {
        if (! $this->shouldDeleteReooLogs()) {
            return;
        }

        $days = ExpiryMailDay::value('reoon_logs_days');
        $logs = $this->getOldReoonLogs($days);
        foreach ($logs as $log) {
            $log->delete();
        }
    }

    public function failedMessageDelivery()
    {
        Session::forget('NonReachableUrls');
        $messages = FailedWhatsappMessage::get();
        foreach ($messages as $message) {
            $rawBody = $message->message;
            if ($rawBody != '') {
                dispatch(new SendWhatsappMessage($rawBody))->onQueue('whatsapp');
                $message->delete();
//                $data = json_decode($rawBody, true);
//                try {
//                    if (isset($data['entry']) && $data['entry'][0]['id'] !== '') {
//                        $wabaId = $data['entry'][0]['id'];
//                        $url = WhatsappIntegrationUser::where('waba_id', $wabaId)->value('user_callback_url');
//                        if($url && !in_array($url, $urls)) {
//                            $response = $this->client->post($url, [
//                                'body' => $rawBody,
//                                'headers' => [
//                                    'Content-Type' => 'application/json',
//                                    'Accept' => 'application/json',
//                                ],
//                            ]);
//
//                            if ($response->getStatusCode() == 200) {
//                                $message->delete();
//                            }
//                        }
//                    }
//                } catch (\Exception $exception) {
//                    $urls[]=$url;
//                    \Log::error($exception->getMessage());
//                }
            }
        }
    }

    private function shouldDeleteInvoices()
    {
        return StatusSetting::value('invoice_deletion_status') == 1;
    }

    private function shouldDeleteReooLogs()
    {
        return StatusSetting::value('reoon_deletion_status') == 1;
    }

    private function getOldInvoices($days)
    {
        $date = Date::now()->subDays($days)->toDateString();

        $oldInvoices = Invoice::where('status', 'pending')
            ->whereDate('date', '<=', $date)
            ->with(['invoiceItem', 'orderRelation'])
            ->get();

        return $oldInvoices;
    }

    private function getOldReoonLogs($days)
    {
        $date = Date::now()->subDays($days)->toDateString();
        $oldLogs = EmailValidationResults::whereDate('created_at', '<=', $date)
            ->get();

        return $oldLogs;
    }

    private function canDeleteInvoice($invoice)
    {
        $condition1 = $invoice->is_renewed == 0 &&
            ! $invoice->orderRelation()->exists() &&
            $invoice->invoiceItem()->exists();

        $condition2 = $invoice->is_renewed != 0 &&
            $invoice->orderRelation()->exists() &&
            $invoice->invoiceItem()->exists();

        return $condition1 || $condition2;
    }

    private function deleteInvoice($invoice)
    {
        return DB::transaction(function () use ($invoice): void {
            // Delete related InvoiceItem records
            $invoice->invoiceItem()->delete();

            if ($invoice->is_renewed != 0 && $invoice->orderRelation()->exists()) {
                // Delete related OrderRelation records
                $invoice->orderRelation()->delete();
            }

            // Delete the Invoice record
            $invoice->delete();
        });
    }

    public function msgDeletions()
    {
        if (StatusSetting::value('msg91_report_delete_status') != 1) {
            return;
        }

        $days = ExpiryMailDay::value('msg91_days');

        $from = CarbonImmutable::startOfTime();

        $to = Date::now()->subDays($days)->endOfDay();

        MsgDeliveryReports::where('created_at', '<=', $to)->delete();
    }
}
