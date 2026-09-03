<?php

namespace App\Plugins\Mailchimp\Listeners;

use App\Events\OrderPlacedEvent;
use App\Model\Common\StatusSetting;
use App\Plugins\Mailchimp\Exceptions\MailchimpApiException;
use App\Plugins\Mailchimp\Services\MailchimpService;
use App\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Logger;
use Throwable;

class UpdateSubscriberOnPurchase implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(private readonly MailchimpService $service)
    {
    }

    public function handle(OrderPlacedEvent $event): void
    {
        if (! (bool) StatusSetting::value('mailchimp_status')) {
            return;
        }

        $invoice = $event->invoice;
        $user = User::find($invoice->user_id);

        if (! $user) {
            return;
        }

        foreach ($invoice->invoiceItem()->get() as $item) {
            try {
                $this->service->updatePurchaseInterests($user, (int) $item->product_id, $item->subtotal > 0);
            } catch (MailchimpApiException $e) {
                if ($e->getHttpStatus() !== 404) {
                    Logger::exception($e);
                }
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
