<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Model\Common\StatusSetting;
use App\Model\Order\InvoiceItem;
use App\Model\Product\Product;
use App\Plugins\Mailchimp\Listeners\UpdateSubscriberOnPurchase;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\ZohoCampaignsController;
use App\User;
use Logger;
use Throwable;

class ExternalServiceController extends Controller
{
    public function subscribeForProductsUpdates(int $productId, int $userId, InvoiceItem $item): void
    {
        $status = StatusSetting::select('zoho_status', 'mailchimp_status')->first();

        if (! $status) {
            return;
        }

        if ($status->zoho_status) {
            $this->updateSubscriberForZohoProduct($productId, $userId, $item);
        }

        if ($status->mailchimp_status) {
            $this->updateSubscriberForMailchimpProduct($productId, $userId, $item);
        }
    }

    public function updateSubscriberForMailchimpProduct(int $productId, int $userId, InvoiceItem $item): void
    {
        resolve(UpdateSubscriberOnPurchase::class)
            ->handle($productId, $userId, $item);
    }

    public function updateSubscriberForZohoProduct(int $productId, int $userId, InvoiceItem $item): void
    {
        try {
            $email = User::whereKey($userId)->value('email');

            if (! $email) {
                return;
            }

            $productName = Product::whereKey($productId)->value('name');

            $type = $item->subtotal > 0
                ? 'paid_products'
                : 'free_products';

            resolve(ZohoCampaignsController::class)->subscribeWithTag($email, $type, $productName ?? $type);
        } catch (Throwable $e) {
            Logger::exception($e);
        }
    }
}
