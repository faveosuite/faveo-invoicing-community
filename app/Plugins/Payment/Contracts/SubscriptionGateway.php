<?php

declare(strict_types=1);

namespace App\Plugins\Payment\Contracts;

use App\Plugins\Payment\Dto\SubscriptionRequest;
use App\Plugins\Payment\Dto\SubscriptionResult;
use App\Plugins\Payment\Exceptions\PaymentException;

/**
 * Contract for gateways that support recurring subscriptions.
 *
 * Separate from {@see PaymentGateway} (one-time payments) because not every
 * gateway does recurring billing, and the lifecycle differs. A driver may
 * implement both. Like the rest of the package, implementations are
 * application-agnostic: they take a plain {@see SubscriptionRequest} and return a
 * plain {@see SubscriptionResult}, depending only on their vendor SDK.
 *
 * Gateway calls throw {@see \App\Plugins\Payment\Exceptions\PaymentException} on
 * failure.
 */
interface SubscriptionGateway
{
    /**
     * Open a recurring subscription and return the gateway's subscription record.
     *
     * @throws PaymentException
     */
    public function createSubscription(SubscriptionRequest $request): SubscriptionResult;

    /**
     * Read a subscription's current status from the gateway (its own status
     * string, e.g. Stripe "active", Razorpay "authenticated").
     *
     * @throws PaymentException
     */
    public function getSubscriptionStatus(string $subscriptionId): string;

    /**
     * Cancel a subscription at the gateway. Returns the cancelled record.
     *
     * @throws PaymentException
     */
    public function cancelSubscription(string $subscriptionId): SubscriptionResult;

    /**
     * Ensure a subscription bills the requested amount/interval going forward.
     *
     * Idempotent: when the subscription already bills the requested price it is
     * left untouched, and an inactive subscription is not modified. When a price
     * change leaves the subscription inactive the driver cancels it and flags
     * SubscriptionResult::$raw['cancelled'] = true so the caller can mirror that.
     *
     * @throws PaymentException
     */
    public function updateSubscriptionPrice(string $subscriptionId, SubscriptionRequest $request): SubscriptionResult;
}
