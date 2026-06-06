<?php

namespace App\Plugins\Mailchimp\Listeners;

use App\Model\Order\InvoiceItem;
use App\Plugins\Mailchimp\Exceptions\MailchimpApiException;
use App\Plugins\Mailchimp\Services\MailchimpService;
use App\User;

class UpdateSubscriberOnPurchase
{
    public function __construct(private readonly MailchimpService $service) {}

    public function handle(int $productId, int $userId, InvoiceItem $item): void
    {
        try {
            $user = User::find($userId);
            if (! $user) return;

            $isPaid = $item->subtotal > 0;
            $this->service->updatePurchaseInterests($user, $productId, $isPaid);
        } catch (MailchimpApiException $e) {
            // 404 = member not subscribed yet; ignore
            if ($e->getHttpStatus() !== 404) {
                \Logger::exception($e);
            }
        } catch (\Throwable $e) {
            \Logger::exception($e);
        }
    }
}
