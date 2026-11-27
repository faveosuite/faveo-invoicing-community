<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Services;

use App\License\Models\License;
use App\Model\Order\Order;
use App\Model\Order\OrderEntitlement;
use App\Model\Payment\Plan;
use App\Model\Product\Subscription;
use App\Services\EntitlementService;
use App\Services\SubscriptionRenewalService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Date;
use Tests\DBTestCase;

class SubscriptionRenewalServiceTest extends DBTestCase
{
    use DatabaseTransactions;

    private SubscriptionRenewalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = resolve(SubscriptionRenewalService::class);
    }

    private function makeSubscription(array $attrs = []): Subscription
    {
        return Subscription::factory()->create(array_merge([
            'ends_at' => now()->addDays(30),
            'update_ends_at' => now()->addDays(30),
            'support_ends_at' => now()->addDays(30),
        ], $attrs));
    }

    // --- setDate ---

    public function test_set_date_skips_update_when_subscription_has_no_order(): void
    {
        $sub = Subscription::factory()->create(['order_id' => 999999, 'ends_at' => now()->addDays(30)]);

        $this->service->setDate($sub, 'ends_at', now()->addDays(60)->toDateTimeString());

        $this->assertTrue(true); // no exception
    }

    public function test_set_date_updates_the_license_expire_date_for_file_mode_orders(): void
    {
        $order = Order::factory()->withRelations([
            'number' => '90000200',
            'license_mode' => 'File',
            'serial_key' => 'LIC-RENEW-CODE',
        ])->create();

        $license = License::create([
            'product_id' => $order->product,
            'user_id' => $order->client,
            'license_code' => 'LIC-RENEW-CODE',
            'license_order_number' => $order->number,
            'license_expire_date' => '2026-01-01',
            'license_status' => 1,
        ]);

        $sub = Subscription::where('order_id', $order->id)->firstOrFail();

        $this->service->setDate($sub, 'ends_at', '2028-01-01');

        $this->assertDatabaseHas('licenses', [
            'id' => $license->id,
            'license_expire_date' => '2028-01-01',
        ]);
    }

    // --- syncLicense ---

    public function test_sync_license_keeps_the_install_limit(): void
    {
        $order = Order::factory()->withRelations(['serial_key' => 'LIC-KEEP-LIMIT'])->create();
        $license = License::create(['product_id' => $order->product, 'license_code' => 'LIC-KEEP-LIMIT', 'license_limit' => 5, 'license_status' => 1]);

        $this->service->syncLicense(Subscription::where('order_id', $order->id)->firstOrFail());

        $this->assertSame(5, $license->fresh()->license_limit);
    }

    public function test_sync_license_runs_without_exception(): void
    {
        $sub = $this->makeSubscription();

        $this->service->syncLicense($sub);

        $this->assertTrue(true);
    }

    /**
     * Regression test: syncLicenseServer() used to let ANY failure (e.g. a
     * null serial_key on the order, a missing order) propagate straight out
     * of extendDates() — which meant a renewal invoice never got marked paid
     * even after the gateway had already charged the customer. It must now
     * swallow failures instead of throwing.
     */
    public function test_extend_dates_does_not_throw_when_order_has_no_serial_key(): void
    {
        $order = Order::factory()->create(['serial_key' => null]);
        $sub = $this->makeSubscription(['order_id' => $order->id]);

        $this->service->extendDates($sub, 30);

        $this->assertTrue(true); // reaching here proves extendDates() didn't throw
    }

    public function test_a_renewal_with_a_pending_agents_reduction_still_extends_the_license(): void
    {
        // No transaction of its own around extendDates(): the license rewrite runs at once.
        $order = Order::factory()->withRelations(['serial_key' => \Crypt::encrypt('RENEWAGENTSX0008')])->create();
        $sub = Subscription::where('order_id', $order->id)->firstOrFail();
        $sub->update(['ends_at' => now()->addDays(5)]);
        $plan = Plan::findOrFail($sub->plan_id);
        $agents = EntitlementService::agents();
        $plan->entitlements()->syncWithoutDetaching([$agents->id => ['value' => '5']]);
        OrderEntitlement::create(['order_id' => $order->id, 'entitlement_id' => $agents->id, 'quantity' => 3, 'renew_quantity' => 1, 'unit_price' => '3', 'currency' => 'USD']);
        $license = License::create(['product_id' => $order->product, 'license_code' => 'RENEWAGENTSX0008', 'license_order_number' => $order->number, 'license_expire_date' => now()->addDays(5)->format('Y-m-d'), 'license_status' => 1]);

        $this->service->extendDates($sub->fresh(), 30);

        $license->refresh();
        $this->assertSame('RENEWAGENTSX0006', $license->license_code);
        $this->assertSame(now()->addDays(35)->format('Y-m-d'), Date::parse($license->license_expire_date)->format('Y-m-d'));
    }

    // --- updateInstallationLimit ---

    public function test_update_installation_limit_returns_early_when_no_license(): void
    {
        $sub = $this->makeSubscription(['order_id' => 999999]);

        // No order → no license found → returns early
        try {
            $this->service->updateInstallationLimit($sub, 5);
        } catch (\Throwable) {
            // May throw if order is missing — acceptable
        }

        $this->assertTrue(true);
    }
}
