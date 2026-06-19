<?php

namespace App\Http\Controllers\Common;

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
use Carbon\CarbonImmutable;
use DB;
use GuzzleHttp\Client;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;
use Override;
use Session;

class CronController extends BaseCronController
{
    protected ?\App\Model\Product\Subscription $subscription = null;

    protected \App\Model\Order\Order $order;

    protected \App\User $user;

    protected \App\Model\Common\Template $template;

    protected \App\Model\Order\Invoice $invoice;

    protected \GuzzleHttp\Client $client;

    protected mixed $PostSubscriptionHandle = null;

    public function __construct()
    {
        $subscription = new Subscription();
        $this->sub = $subscription; // @phpstan-ignore property.notFound

        $plan = new Plan();
        $this->plan = $plan; // @phpstan-ignore property.notFound

        $order = new Order();
        $this->order = $order;

        $user = new User();
        $this->user = $user;

        $template = new Template();
        $this->template = $template;

        $invoice = new Invoice();
        $this->invoice = $invoice;

        $payment = new Payment();
        $this->payment = $payment; // @phpstan-ignore property.notFound

        $stripeController = new SettingsController();
        $this->stripeController = $stripeController; // @phpstan-ignore property.notFound

        $this->client = new Client();
    }

    /**
     * @return array{users: mixed, orders: mixed, subscription: mixed}[]
     */
    public function getAllDaysExpiryUsers(int $day): array
    {
        $sub = $this->getAllDaysExpiryInfo($day);
        $users = [];
        if ($sub->count() > 0) {
            foreach ($sub->get() as $key => $value) {
                $users[$key]['users'] = $this->sub->find($value->id)->user()->get(); // @phpstan-ignore property.notFound
                $users[$key]['orders'] = $this->sub->find($value->id)->order()->get(); // @phpstan-ignore property.notFound
                $users[$key]['subscription'] = $value;
            }
        }

        return $users;
    }

    /**
     * @return array{users: mixed, orders: mixed, subscription: mixed}[]
     */
    public function get15DaysExpiryUsers(): array
    {
        $sub = $this->get15DaysExpiryInfo();
        $users = [];
        if ($sub->count() > 0) {
            foreach ($sub->get() as $key => $value) {
                $users[$key]['users'] = $this->sub->find($value->id)->user()->get(); // @phpstan-ignore property.notFound
                $users[$key]['orders'] = $this->sub->find($value->id)->order()->get(); // @phpstan-ignore property.notFound
                $users[$key]['subscription'] = $value;
            }
        }

        return $users;
    }

    /**
     * @return array{users: mixed, orders: mixed, subscription: mixed}[]
     */
    public function getOneDayExpiryUsers(): array
    {
        $sub = $this->getOneDayExpiryInfo();
        $users = [];
        if ($sub->count() > 0) {
            foreach ($sub->get() as $key => $value) {
                $users[$key]['users'] = $this->sub->find($value->id)->user()->get(); // @phpstan-ignore property.notFound
                $users[$key]['orders'] = $this->sub->find($value->id)->order()->get(); // @phpstan-ignore property.notFound
                $users[$key]['subscription'] = $value;
            }
        }

        return $users;
    }

    /**
     * @return array{users: mixed, orders: mixed, subscription: mixed}[]
     */
    public function getOnDayExpiryUsers(): array
    {
        $sub = $this->getOnDayExpiryInfo();
        $users = [];
        if ($sub->count() > 0) {
            foreach ($sub->get() as $key => $value) {
                $users[$key]['users'] = $this->sub->find($value->id)->user()->get(); // @phpstan-ignore property.notFound
                $users[$key]['orders'] = $this->sub->find($value->id)->order()->get(); // @phpstan-ignore property.notFound
                $users[$key]['subscription'] = $value;
            }
        }

        return $users;
    }

    /**
     * @return array{users: mixed, orders: mixed, subscription: mixed}[]
     */
    public function getExpiredUsers(): array
    {
        $sub = $this->getExpiredInfo();
        $users = [];
        if ($sub->count() > 0) {
            foreach ($sub->get() as $key => $value) {
                $users[$key]['users'] = $this->sub->find($value->id)->user()->get(); // @phpstan-ignore property.notFound
                $users[$key]['orders'] = $this->sub->find($value->id)->order()->get(); // @phpstan-ignore property.notFound
                $users[$key]['subscription'] = $value;
            }
        }

        return $users;
    }

    public function get1DaysSubscription(): mixed
    {
        $users = $this->getOneDayExpiryUsers();
        if ($users !== []) {
            return $users[0]['subscription'];
        }

        return $users;
    }

    public function get0DaysSubscription(): mixed
    {
        $users = $this->getOnDayExpiryUsers();
        if ($users !== []) {
            return $users[0]['subscription'];
        }

        return $users;
    }

    public function getPlus1Subscription(): mixed
    {
        $users = $this->getExpiredUsers();
        if ($users !== []) {
            return $users[0]['subscription'];
        }

        return $users;
    }

    /**
     * @return list<mixed>
     */
    public function getUsers(): array
    {
        $users = [];
        if (count($this->get30DaysUsers()) > 0) {
            $users[] = $this->get30DaysUsers();
        }

        if (count($this->get15DaysUsers()) > 0) {
            $users[] = $this->get15DaysUsers();
        }

        if (count($this->get1DaysUsers()) > 0) {
            $users[] = $this->get1DaysUsers();
        }

        if (count($this->get0DaysUsers()) > 0) {
            $users[] = $this->get0DaysUsers();
        }

        if (count($this->getPlus1Users()) > 0) {
            $users[] = $this->getPlus1Users();
        }

        return $users;
    }

    /**
     * @return mixed[]
     */
    #[Override]
    public function getSubscriptions(mixed $days): array
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
        } // nosemgrep: php.lang.security.unserialize-use.unserialize-use

        return array_map(unserialize(...), array_unique(array_map(serialize(...), $subscriptions)));
    }

    /**
     * @return mixed[]
     */
    public function getautoSubscriptions(mixed $days): array
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
        } // nosemgrep: php.lang.security.unserialize-use.unserialize-use

        return array_map(unserialize(...), array_unique(array_map(serialize(...), $subscriptions)));
    }

    /**
     * @return mixed[]
     */
    public function getPostSubscriptions(mixed $days): array
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
        } // nosemgrep: php.lang.security.unserialize-use.unserialize-use

        return array_map(unserialize(...), array_unique(array_map(serialize(...), $subscriptions)));
    }

    public function eachSubscription(): void
    {
        $status = StatusSetting::value('expiry_mail');
        if ($status == 1) {
            $allDays = ExpiryMailDay::pluck('days')->toArray();
            $sub = $this->getSubscriptions($allDays);
            foreach ($sub as $value) {
                $value = (object) $value;
                $userid = $value->user_id;
                /** @var \App\User $user */
                $user = $this->getUserById($userid);
                $end = $value->update_ends_at;
                /** @var \App\Model\Order\Order $order */
                $order = $this->getOrderById($value->order_id);
                /** @var \App\Model\Order\Invoice $invoice */
                $invoice = $this->getInvoiceByOrderId($value->order_id);
                /** @var \App\Model\Order\InvoiceItem $item */
                $item = $this->getInvoiceItemByInvoiceId($invoice->id);
                $product = (int) $item->product_id;
                if (emailSendingStatus()) {
                    $this->mail($user, $end, $product, $order, $value->id);
                }
            }
        }
    }

    public function autoRenewalExpiryNotify(): void
    {
        $status = StatusSetting::value('subs_expirymail');
        if ($status == 1) {
            $Days = ExpiryMailDay::pluck('autorenewal_days')->toArray();
            $Autosub = $this->getautoSubscriptions($Days);
            foreach ($Autosub as $value) {
                $value = (object) $value;
                $userid = $value->user_id;
                /** @var \App\User $user */
                $user = $this->getUserById($userid);
                $end = $value->update_ends_at;
                /** @var \App\Model\Order\Order $order */
                $order = $this->getOrderById($value->order_id);
                /** @var \App\Model\Order\Invoice $invoice */
                $invoice = $this->getInvoiceByOrderId($value->order_id);
                /** @var \App\Model\Order\InvoiceItem $item */
                $item = $this->getInvoiceItemByInvoiceId($invoice->id);
                $product = (int) $item->product_id;
                if (emailSendingStatus()) {
                    $this->Auto_renewalMail($user, $end, $product, $order, $value->id);
                }
            }
        }
    }

    public function postRenewalNotify(): void
    {
        $status = StatusSetting::value('post_expirymail');
        if ($status == 1) {
            $periods = ExpiryMailDay::pluck('postexpiry_days')->toArray();
            $postSub = $this->getPostSubscriptions($periods);
            foreach ($postSub as $value) {
                $value = (object) $value;
                $userid = $value->user_id;
                $user = $this->getUserById($userid);
                $end = $value->update_ends_at;
                $order = Order::find($value->order_id);
                if ($order) {
                    /** @var \App\User $user */
                    $user = $this->getUserById($userid);
                    /** @var \App\Model\Order\Order $postOrder */
                    $postOrder = $this->getOrderById($value->order_id);
                    /** @var \App\Model\Order\Invoice $invoice */
                    $invoice = $this->getInvoiceByOrderId($value->order_id);
                    /** @var \App\Model\Order\InvoiceItem $item */
                    $item = $this->getInvoiceItemByInvoiceId($invoice->id);
                    $product = (int) $item->product_id;
                    if (emailSendingStatus()) {
                        $this->Expiredsub_Mail($user, $end, $product, $postOrder, $value->id);
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
     */
    public function invoicesDeletion(): void
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

    public function reoonLogsDeletion(): void
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

    public function failedMessageDelivery(): void
    {
        Session::forget('NonReachableUrls');
        $messages = FailedWhatsappMessage::get();
        foreach ($messages as $message) {
            $rawBody = $message->message;
            if ($rawBody != '') {
                dispatch(new SendWhatsappMessage($rawBody))->onQueue('whatsapp');
                $message->delete();
            }
        }
    }

    private function shouldDeleteInvoices(): bool
    {
        return StatusSetting::value('invoice_deletion_status') == 1;
    }

    private function shouldDeleteReooLogs(): bool
    {
        return StatusSetting::value('reoon_deletion_status') == 1;
    }

    private function getOldInvoices(mixed $days): mixed
    {
        $date = Date::now()->subDays($days)->toDateString();

        return Invoice::where('status', 'pending')
            ->whereDate('date', '<=', $date)
            ->with(['invoiceItem', 'orders'])
            ->get();
    }

    private function getOldReoonLogs(mixed $days): mixed
    {
        $date = Date::now()->subDays($days)->toDateString();

        return EmailValidationResults::whereDate('created_at', '<=', $date)
            ->get();
    }

    private function canDeleteInvoice(mixed $invoice): bool
    {
        $condition1 = $invoice->is_renewed == 0 &&
            ! $invoice->orderRelation()->exists() &&
            $invoice->invoiceItem()->exists();

        $condition2 = $invoice->is_renewed != 0 &&
            $invoice->orderRelation()->exists() &&
            $invoice->invoiceItem()->exists();

        return $condition1 || $condition2;
    }

    private function deleteInvoice(mixed $invoice): mixed
    {
        return DB::transaction(function () use ($invoice): void { // @phpstan-ignore staticMethod.void
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

    public function msgDeletions(): void
    {
        if (StatusSetting::value('msg91_report_delete_status') != 1) {
            return;
        }

        $days = ExpiryMailDay::value('msg91_days');

        CarbonImmutable::startOfTime();

        $to = Date::now()->subDays($days)->endOfDay();

        MsgDeliveryReports::where('created_at', '<=', $to)->delete();
    }
}
