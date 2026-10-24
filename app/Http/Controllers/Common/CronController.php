<?php

namespace App\Http\Controllers\Common;

use App\EmailValidationResults;
use App\FailedWhatsappMessage;
use App\Jobs\SendWhatsappMessage;
use App\Model\Common\MsgDeliveryReports;
use App\Model\Common\Template;
use App\Model\Mailjob\Condition;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
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
    protected ?Subscription $subscription = null;

    protected Order $order;

    protected User $user;

    protected Template $template;

    protected Invoice $invoice;

    protected Client $client;

    protected mixed $PostSubscriptionHandle = null;

    public function __construct()
    {
        $subscription = new Subscription;
        $this->sub = $subscription; // @phpstan-ignore property.notFound

        $plan = new Plan;
        $this->plan = $plan; // @phpstan-ignore property.notFound

        $order = new Order;
        $this->order = $order;

        $user = new User;
        $this->user = $user;

        $template = new Template;
        $this->template = $template;

        $invoice = new Invoice;
        $this->invoice = $invoice;

        $payment = new Payment;
        $this->payment = $payment; // @phpstan-ignore property.notFound

        $stripeController = new SettingsController;
        $this->stripeController = $stripeController; // @phpstan-ignore property.notFound

        $this->client = new Client;
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
     * @param  list<int>  $days
     * @return mixed[]
     */
    #[Override]
    public function getSubscriptions(array $days): array
    {
        $subscriptions = [];
        foreach ($days as $day) {
            // Calculate the start and end dates

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

        // Values just came from serialize() two lines up (DB-row arrays), never from
        // untrusted input — this is a dedupe trick, not real deserialization of external data.
        return array_map(unserialize(...), array_unique(array_map(serialize(...), $subscriptions))); // nosemgrep: php.lang.security.unserialize-use.unserialize-use
    }

    /**
     * @param  list<int>  $days
     * @return mixed[]
     */
    public function getautoSubscriptions(array $days): array
    {
        $subscriptions = [];
        foreach ($days as $day) {
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
                ->where('subscriptions.is_deleted', 0) // deleted cloud instance — nothing to remind about
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

        // Values just came from serialize() two lines up (DB-row arrays), never from
        // untrusted input — this is a dedupe trick, not real deserialization of external data.
        return array_map(unserialize(...), array_unique(array_map(serialize(...), $subscriptions))); // nosemgrep: php.lang.security.unserialize-use.unserialize-use
    }

    /**
     * @param  list<int>  $days
     * @return mixed[]
     */
    public function getPostSubscriptions(array $days): array
    {
        $subscriptions = [];

        // Calculate the end date as today

        foreach ($days as $day) {
            // Calculate the start date based on the specific day value from $days
            $endDate = Date::now()->subDays($day)->toDateString(); // Use $day here

            // Dates grouped: ungrouped orWhere()s let the conditions below apply only to the last date.
            $subscriptionsForDay = Subscription::where(function (Builder $query) use ($endDate): void {
                $query->where('update_ends_at', 'LIKE', $endDate.'%')
                    ->orWhere('support_ends_at', 'LIKE', $endDate.'%')
                    ->orWhere('ends_at', 'LIKE', $endDate.'%');
            })
                ->join('orders', 'subscriptions.order_id', '=', 'orders.id')
                ->where('orders.order_status', 'executed')
                ->where('subscriptions.is_deleted', 0) // deleted cloud instance — no post-expiry reminders
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

        // Values just came from serialize() two lines up (DB-row arrays), never from
        // untrusted input — this is a dedupe trick, not real deserialization of external data.
        return array_map(unserialize(...), array_unique(array_map(serialize(...), $subscriptions))); // nosemgrep: php.lang.security.unserialize-use.unserialize-use
    }

    public function eachSubscription(): void
    {
        if (Condition::isActive('expiryMail')) {
            $sub = $this->getSubscriptions(Condition::dayList('expiryMail'));
            foreach ($sub as $value) {
                $value = (object) $value;
                $userid = $value->user_id;
                /** @var User $user */
                $user = $this->getUserById($userid);
                $end = $value->update_ends_at;
                /** @var Order $order */
                $order = $this->getOrderById($value->order_id);
                /** @var Invoice $invoice */
                $invoice = $this->getInvoiceByOrderId($value->order_id);
                /** @var InvoiceItem $item */
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
        if (Condition::isActive('subsExpirymail')) {
            $Autosub = $this->getautoSubscriptions(Condition::dayList('subsExpirymail'));
            foreach ($Autosub as $value) {
                $value = (object) $value;
                $userid = $value->user_id;
                /** @var User $user */
                $user = $this->getUserById($userid);
                $end = $value->update_ends_at;
                /** @var Order $order */
                $order = $this->getOrderById($value->order_id);
                /** @var Invoice $invoice */
                $invoice = $this->getInvoiceByOrderId($value->order_id);
                /** @var InvoiceItem $item */
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
        if (Condition::isActive('postExpirymail')) {
            $postSub = $this->getPostSubscriptions(Condition::dayList('postExpirymail'));
            foreach ($postSub as $value) {
                $value = (object) $value;
                $userid = $value->user_id;
                $user = $this->getUserById($userid);
                $end = $value->update_ends_at;
                $order = Order::find($value->order_id);
                if ($order) {
                    /** @var User $user */
                    $user = $this->getUserById($userid);
                    /** @var Order $postOrder */
                    $postOrder = $this->getOrderById($value->order_id);
                    /** @var Invoice $invoice */
                    $invoice = $this->getInvoiceByOrderId($value->order_id);
                    /** @var InvoiceItem $item */
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

        $days = Condition::dayCount('invoice');
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

        $days = Condition::dayCount('reoon');
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
        return Condition::isActive('invoice');
    }

    private function shouldDeleteReooLogs(): bool
    {
        return Condition::isActive('reoon');
    }

    private function getOldInvoices(mixed $days): mixed
    {
        $date = Date::now()->subDays($days)->toDateString();

        return Invoice::where('status', 'pending')
            ->where('date', '<=', Date::parse($date)->endOfDay())
            ->with(['invoiceItem', 'orders'])
            ->get();
    }

    private function getOldReoonLogs(mixed $days): mixed
    {
        $date = Date::now()->subDays($days)->toDateString();

        return EmailValidationResults::where('created_at', '<=', Date::parse($date)->endOfDay())
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

    private function deleteInvoice(mixed $invoice): void
    {
        DB::transaction(function () use ($invoice): void {
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
        if (! Condition::isActive('msg91Reports')) {
            return;
        }

        $days = (int) Condition::dayCount('msg91Reports');

        CarbonImmutable::startOfTime();

        $to = Date::now()->subDays($days)->endOfDay();

        MsgDeliveryReports::where('created_at', '<=', $to)->delete();
    }
}
