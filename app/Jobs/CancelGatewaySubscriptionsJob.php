<?php

namespace App\Jobs;

use App\Model\Product\Subscription;
use App\Services\Payment\SubscriptionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Cancels all active gateway subscriptions for a given gateway.
 * Dispatched when admin disables auto-renewal for Stripe or Razorpay.
 */
class CancelGatewaySubscriptionsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $gateway)
    {
    }

    public function handle(SubscriptionService $service): void
    {
        $statusField = $this->gateway === 'stripe' ? 'autoRenew_status' : 'rzp_subscription';

        Subscription::where($statusField, '!=', 0)
            ->where('is_subscribed', 1)
            ->whereNotNull('subscribe_id')
            ->where('subscribe_id', '!=', '')
            ->chunkById(50, function ($subscriptions) use ($service, $statusField) {
                foreach ($subscriptions as $subscription) {
                    try {
                        $service->cancelSubscription(ucfirst($this->gateway), $subscription->subscribe_id);
                    } catch (\Exception $e) {
                        \Logger::warning("Failed to cancel {$this->gateway} subscription {$subscription->subscribe_id}: ".$e->getMessage());
                    }

                    $subscription->update([
                        'is_subscribed' => 0,
                        $statusField    => 0,
                        'subscribe_id'  => '',
                    ]);
                }
            });
    }
}
