<?php

namespace App\Services\Payment;

use App\Plugins\Payment\Contracts\SubscriptionGateway;
use App\Plugins\Payment\Dto\SubscriptionRequest;
use App\Plugins\Payment\Dto\SubscriptionResult;
use App\Plugins\Payment\Exceptions\PaymentException;

/**
 * Generalized recurring-subscription processing.
 *
 * The subscription counterpart to {@see PaymentService}: domain-agnostic, it
 * reuses the same configured gateway manager and drives the package's
 * {@see SubscriptionGateway} drivers with plain value objects. It knows nothing
 * about plans, orders, invoices or the Subscription model — callers (the autopay
 * adapters) build the {@see SubscriptionRequest} and interpret the result.
 */
class SubscriptionService
{
    public function __construct(private readonly PaymentService $payments)
    {
    }

    /**
     * Open a recurring subscription on a gateway.
     *
     * @throws \App\Plugins\Payment\Exceptions\PaymentException
     */
    public function createSubscription(string $gateway, SubscriptionRequest $request): SubscriptionResult
    {
        return $this->gateway($gateway)->createSubscription($request);
    }

    /**
     * Read a subscription's current status (the gateway's own status string).
     *
     * @throws \App\Plugins\Payment\Exceptions\PaymentException
     */
    public function getStatus(string $gateway, string $subscriptionId): string
    {
        return $this->gateway($gateway)->getSubscriptionStatus($subscriptionId);
    }

    /**
     * Cancel a subscription at the gateway.
     *
     * @throws \App\Plugins\Payment\Exceptions\PaymentException
     */
    public function cancelSubscription(string $gateway, string $subscriptionId): SubscriptionResult
    {
        return $this->gateway($gateway)->cancelSubscription($subscriptionId);
    }

    /**
     * Ensure a subscription bills the requested amount/interval going forward.
     *
     * @throws \App\Plugins\Payment\Exceptions\PaymentException
     */
    public function updateSubscriptionPrice(string $gateway, string $subscriptionId, SubscriptionRequest $request): SubscriptionResult
    {
        return $this->gateway($gateway)->updateSubscriptionPrice($subscriptionId, $request);
    }

    /**
     * Resolve a gateway and assert it supports subscriptions.
     *
     * @throws \App\Plugins\Payment\Exceptions\PaymentException
     */
    private function gateway(string $gateway): SubscriptionGateway
    {
        $driver = $this->payments->manager()->gateway($gateway);

        if (! $driver instanceof SubscriptionGateway) {
            throw new PaymentException("Payment gateway [{$gateway}] does not support subscriptions.");
        }

        return $driver;
    }
}
