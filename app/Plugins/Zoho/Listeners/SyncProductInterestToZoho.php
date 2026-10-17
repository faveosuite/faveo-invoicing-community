<?php

namespace App\Plugins\Zoho\Listeners;

use App\Events\OrderPlacedEvent;
use App\Model\Common\StatusSetting;
use App\Model\Product\Product;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\ZohoCampaignsController;
use App\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Logger;
use Throwable;

class SyncProductInterestToZoho implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public int $tries = 3;

    public int $backoff = 60;

    public function handle(OrderPlacedEvent $event): void
    {
        if (! (bool) StatusSetting::value('zoho_status')) {
            return;
        }

        $invoice = $event->invoice;
        $email = User::whereKey($invoice->user_id)->value('email');

        if (! $email) {
            return;
        }

        foreach ($invoice->invoiceItem()->get() as $item) {
            try {
                $type = $item->subtotal > 0 ? 'paid_products' : 'free_products';
                $productName = Product::whereKey($item->product_id)->value('name');
                resolve(ZohoCampaignsController::class)->subscribeWithTag($email, $type, $productName ?? $type);
            } catch (Throwable $e) {
                Logger::exception($e);
            }
        }
    }

    public function failed(OrderPlacedEvent $event, Throwable $exception): void
    {
        Logger::exception($exception);
    }
}
