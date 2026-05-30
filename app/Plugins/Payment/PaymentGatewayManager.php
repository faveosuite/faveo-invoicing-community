<?php

namespace App\Plugins\Payment;

use App\Plugins\Payment\Contracts\PaymentGateway;
use App\Plugins\Payment\Exceptions\PaymentException;

/**
 * A tiny registry that resolves gateway drivers by name.
 *
 * It is intentionally credential-free: the consumer registers a factory closure
 * for each gateway it has configured, and the factory supplies the credentials.
 * This keeps the manager — and the whole package — independent of where
 * credentials come from (env, a database, a vault, hard-coded in a test).
 *
 * Usage:
 *   $gateways = (new PaymentGatewayManager)
 *       ->register('Stripe',   fn () => new StripeGateway($secret, $publishable))
 *       ->register('Razorpay', fn () => new RazorpayGateway($key, $keySecret));
 *
 *   $gateways->gateway('Stripe')->createPayment($request);
 *
 * Lookups are case-insensitive ("stripe" === "Stripe"); driver instances are
 * memoised so repeated gateway() calls reuse the same object.
 */
final class PaymentGatewayManager
{
    /** @var array<string, callable(): PaymentGateway> */
    private array $factories = [];

    /** @var array<string, PaymentGateway> */
    private array $resolved = [];

    /**
     * @param  callable(): PaymentGateway  $factory
     */
    public function register(string $name, callable $factory): self
    {
        $this->factories[strtolower($name)] = $factory;

        return $this;
    }

    public function has(string $name): bool
    {
        return isset($this->factories[strtolower($name)]);
    }

    /**
     * Resolve (and memoise) a registered gateway driver.
     *
     * @throws PaymentException when the gateway has not been registered.
     */
    public function gateway(string $name): PaymentGateway
    {
        $key = strtolower($name);

        if (! isset($this->factories[$key])) {
            throw new PaymentException("Payment gateway [{$name}] is not registered.");
        }

        return $this->resolved[$key] ??= ($this->factories[$key])();
    }

    /**
     * Names of every registered gateway, in registration order.
     *
     * @return array<int, string>
     */
    public function names(): array
    {
        return array_keys($this->factories);
    }
}
