<?php

namespace App\Services\Cloud;

use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Tenancy\TenantController;
use App\Model\Common\FaveoCloud;
use App\Model\Common\Setting;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\Mailjob\Condition;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Logger;
use Throwable;

/**
 * The "cloud" cron task: deletes cloud instances expired for longer than its days setting and
 * emails each owner "Order has been deleted". Does nothing while the task is switched off.
 * Queued daily (or as configured) through the DeleteExpiredCloudInstances job.
 */
class ExpiredCloudInstanceCleaner
{
    public function __invoke(): void
    {
        try {
            if (! Condition::isActive('cloud')) {
                return;
            }

            $contact = getContactData();
            $day = Condition::dayCount('cloud');
            $today = Date::today();

            // Equivalent to DATE(DATE_ADD(ends_at, INTERVAL $day DAY)) <= $today,
            // rewritten as a sargable range on ends_at itself (verified boundary shift).
            $expired = Subscription::whereNotNull('ends_at')
                ->where('is_deleted', 0)
                ->whereIn('product_id', cloudPopupProducts())
                ->where('ends_at', '<', $today->copy()->subDays(((int) $day) - 1))
                ->get();
        } catch (Exception $exception) {
            Logger::exception($exception);

            return;
        }

        foreach ($expired as $subscription) {
            // One broken subscription (e.g. its user was deleted) must not stop the rest.
            try {
                $this->deleteInstance($subscription, $contact);
            } catch (Throwable $exception) {
                Logger::exception($exception);
            }
        }
    }

    /**
     * Deletes one expired cloud instance and emails its owner.
     *
     * @param  array<mixed>  $contact
     */
    private function deleteInstance(Subscription $subscription, array $contact): void
    {
        $order = Order::find($subscription->order_id);
        if (! $order instanceof Order) {
            return;
        }

        $domain = $order->installedDomains()->first();
        if (is_null($domain)) {
            return;
        }

        $destroy = new TenantController(new Client, new FaveoCloud)->destroyTenant(new Request(['id' => $domain, 'orderId' => $subscription->order_id]));
        if ($destroy->status() != 200) {
            return;
        }

        $subscription->is_deleted = 1;
        $subscription->save();

        $this->emailOwner($subscription, $order, $contact);
    }

    /**
     * @param  array<mixed>  $contact
     */
    private function emailOwner(Subscription $subscription, Order $order, array $contact): void
    {
        $user = DB::table('users')->find($subscription->user_id);
        /** @var Product $product */
        $product = Product::find($subscription->product_id);
        /** @var Setting $setting */
        $setting = Setting::find(1);
        /** @var Template $template */
        $template = TemplateType::getSelectedTemplate('cloud_deleted');

        $type = $template->type()->value('name') ?? '';
        $replace = ['name' => $user->first_name.' '.$user->last_name, // @phpstan-ignore property.nonObject, property.nonObject
            'product' => $product->name,
            'number' => $order->number,
            'expiry' => date('j M Y', (int) strtotime((string) $subscription->update_ends_at)),
            'contact' => $contact['contact'],
            'logo' => $contact['logo'],
            'reply_email' => $setting->company_email,
        ];
        // @phpstan-ignore property.nonObject
        new PhpMailController()->SendEmail($setting->email, $user->email, $template->data, $template->name, $type, $replace, $type);
    }
}
