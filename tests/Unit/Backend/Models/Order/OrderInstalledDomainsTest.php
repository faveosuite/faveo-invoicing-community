<?php

namespace Tests\Unit\Backend\Models\Order;

use App\License\Models\Installation;
use App\License\Models\InstallationLog;
use App\License\Models\License;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
use App\User;
use Tests\DBTestCase;

class OrderInstalledDomainsTest extends DBTestCase
{
    public function test_domain_maps_to_cloud_order_through_installations_or_logs(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'Trial', 'product' => $product->id, 'days' => 7]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id, 'cloud_product_key' => 'k']);
        $order = Order::factory()->create(['client' => $user->id, 'product' => $product->id]);

        // Original install, then a reissue: the old code keeps only an installations row,
        // the new code (tenant still running) only a check-in log row.
        License::create(['product_id' => $product->id, 'license_code' => 'TESTCODE0002', 'license_order_number' => $order->number]);
        License::create(['product_id' => $product->id, 'license_code' => 'TESTCODE0003', 'license_order_number' => $order->number]);
        Installation::create(['product_id' => $product->id, 'license_code' => 'TESTCODE0002', 'installation_domain' => 'old.example.test']);
        InstallationLog::create(['license_code' => 'TESTCODE0003', 'installation_domain' => 'acme.example.test', 'installation_last_active_date' => now()]);

        $this->assertSame($order->id, Order::idForDomain('acme.example.test'));
        $this->assertSame($order->id, Order::idForDomain('old.example.test'));
        $this->assertNull(Order::idForDomain('nobody.example.test'));
        $this->assertSame(['acme.example.test', 'old.example.test'], $order->installedDomains()->all());
        $this->assertCount(1, $order->installationLogs);
    }

    public function test_non_cloud_order_is_not_returned_for_a_domain(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['client' => $user->id, 'product' => $product->id]);
        License::create(['product_id' => $product->id, 'license_code' => 'SELFHOST0001', 'license_order_number' => $order->number]);
        InstallationLog::create(['license_code' => 'SELFHOST0001', 'installation_domain' => 'self.example.test', 'installation_last_active_date' => now()]);

        $this->assertNull(Order::idForDomain('self.example.test'));
        $this->assertSame(['self.example.test'], $order->installedDomains()->all());
    }
}
