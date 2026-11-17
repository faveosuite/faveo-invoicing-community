<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Product;

use App\License\Models\License;
use App\Model\Payment\Plan;
use App\Model\Product\Entitlement;
use App\Model\Product\Product;
use Tests\DBTestCase;

class EntitlementControllerTest extends DBTestCase
{
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
        $this->product = Product::factory()->create();
    }

    private function row(array $overrides = []): array
    {
        return ['feature' => 'sla', 'key' => 'enabled', 'label' => 'SLA', 'type' => 'boolean', 'default_value' => '0', 'is_active' => true, ...$overrides];
    }

    public function test_sync_creates_items_and_rejects_duplicates(): void
    {
        $this->postJson('/entitlements', ['entitlements' => [$this->row()]])
            ->assertOk();
        $this->assertDatabaseHas('entitlements', ['feature' => 'sla', 'key' => 'enabled']);

        $this->postJson('/entitlements', ['entitlements' => [$this->row(), $this->row()]])
            ->assertStatus(412);
    }

    public function test_used_item_cannot_change_type_and_is_archived_when_removed(): void
    {
        $item = Entitlement::create([...$this->row()]);
        License::create([
            'product_id' => $this->product->id, 'license_code' => 'ENT'.uniqid(), 'license_order_number' => '444444',
            'license_limit' => 1, 'license_status' => 1, 'entitlement_base' => ['sla' => ['enabled' => false]],
        ]);

        $this->postJson('/entitlements', ['entitlements' => [$this->row(['id' => $item->id, 'type' => 'number'])]])
            ->assertStatus(412);

        $this->postJson('/entitlements', ['entitlements' => []])->assertOk();
        $this->assertFalse($item->fresh()->is_active);
    }

    public function test_unused_item_is_deleted_when_removed(): void
    {
        $item = Entitlement::create([...$this->row()]);

        $this->postJson('/entitlements', ['entitlements' => []])->assertOk();
        $this->assertNull($item->fresh());
    }

    public function test_destroy_deletes_unused_and_archives_used_items(): void
    {
        $unused = Entitlement::create([...$this->row()]);
        $this->deleteJson("/entitlements/{$unused->id}")->assertOk();
        $this->assertNull($unused->fresh());

        $used = Entitlement::create([...$this->row(['feature' => 'chat'])]);
        $plan = Plan::factory()->create(['product' => $this->product->id]);
        $plan->entitlements()->attach($used->id, ['value' => '1']);
        $this->deleteJson("/entitlements/{$used->id}")->assertOk();
        $this->assertFalse($used->fresh()->is_active);
    }

    public function test_order_override_can_be_set_and_reset_but_not_for_agents(): void
    {
        $chat = Entitlement::create([...$this->row(['feature' => 'chat'])]);
        $agents = Entitlement::create([...$this->row(['feature' => 'agents', 'key' => 'max', 'type' => 'number', 'default_value' => '5'])]);
        $order = \App\Model\Order\Order::factory()->create(['product' => $this->product->id, 'number' => 88800001]);
        $license = License::create([
            'product_id' => $this->product->id, 'license_code' => 'ENT'.uniqid().'X', 'license_order_number' => '88800001',
            'license_limit' => 1, 'license_status' => 1,
        ]);

        $this->putJson("/order/{$order->id}/entitlements/{$chat->id}", ['value' => '1', 'until' => now()->addMonth()->toDateString()])->assertOk();
        $this->putJson("/order/{$order->id}/entitlements/{$agents->id}", ['value' => '99'])->assertStatus(412);
        $this->putJson("/order/{$order->id}/entitlements/{$chat->id}", ['value' => 'maybe'])->assertStatus(412);

        $this->getJson("/order/{$order->id}/entitlements")
            ->assertOk()
            ->assertJsonPath('data.entitlements.0.value', true)
            ->assertJsonPath('data.entitlements.0.override', '1')
            ->assertJsonCount(1, 'data.entitlements'); // the code has no agent count; agents aren't offered

        $this->deleteJson("/order/{$order->id}/entitlements/{$chat->id}")->assertOk();
        $this->assertNull($license->fresh()->entitlement_overrides);
    }

    public function test_plan_save_includes_features_with_values_and_prices(): void
    {
        $storage = Entitlement::create([...$this->row(['feature' => 'storage', 'key' => 'gb', 'type' => 'number', 'default_value' => '5'])]);
        $region = Entitlement::create([...$this->row(['feature' => 'region', 'key' => 'value', 'type' => 'text', 'default_value' => 'US'])]);
        $plan = Plan::factory()->create(['product' => $this->product->id]);
        \App\Model\Payment\PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'INR']);

        $this->postJson("/plan/{$plan->id}/entitlements", ['entitlements' => [['id' => $storage->id, 'mode' => 'included', 'value' => 'lots']]])->assertStatus(412);

        $this->postJson("/plan/{$plan->id}/entitlements", ['entitlements' => [
            ['id' => $storage->id, 'mode' => 'included', 'value' => 25, 'prices' => ['INR' => ['unit_price' => 10, 'renew_price' => 8, 'offer_percent' => '']]],
            ['id' => $region->id, 'mode' => 'none', 'value' => null, 'prices' => ['INR' => ['unit_price' => 99]]], // not available: no price kept
        ]])->assertOk();

        $this->assertDatabaseHas('plan_entitlements', ['plan_id' => $plan->id, 'entitlement_id' => $storage->id, 'value' => '25']);
        $this->assertDatabaseMissing('plan_entitlements', ['plan_id' => $plan->id, 'entitlement_id' => $region->id]);
        $this->assertDatabaseHas('entitlement_prices', ['plan_id' => $plan->id, 'entitlement_id' => $storage->id, 'currency' => 'INR', 'unit_price' => 10, 'renew_price' => 8]);
        $this->assertDatabaseMissing('entitlement_prices', ['entitlement_id' => $region->id]);

        $res = $this->getJson("/plan/{$plan->id}/entitlements")->assertOk()->assertJsonPath('data.currencies', ['INR']);
        $row = collect($res->json('data.entitlements'))->firstWhere('id', $storage->id);
        $this->assertSame('included', $row['mode']);
        $this->assertSame('25', $row['value']);
        $this->assertEquals(10, $row['prices']['INR']['unit_price']);
    }

    public function test_plan_sets_its_included_agents_in_the_features_table_and_keeps_them_on_a_price_edit(): void
    {
        $product = Product::factory()->create(['show_agent' => 1]);
        $plan = Plan::factory()->create(['product' => $product->id]);
        \App\Model\Payment\PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'INR', 'no_of_agents' => 5]);
        $agents = \App\Services\EntitlementService::agents();

        $row = fn () => collect($this->getJson("/plan/{$plan->id}/entitlements")->json('data.entitlements'))->firstWhere('id', $agents->id);
        $this->assertSame('5', $row()['value']);

        $this->postJson("/plan/{$plan->id}/entitlements", ['entitlements' => [['id' => $agents->id, 'mode' => 'included', 'value' => '-3']]])->assertStatus(412);
        $this->postJson("/plan/{$plan->id}/entitlements", ['entitlements' => [['id' => $agents->id, 'mode' => 'included', 'value' => '10']]])->assertOk();
        $this->assertSame('10', $row()['value']);

        $this->postJson("/plan/{$plan->id}/entitlements", ['entitlements' => [['id' => $agents->id, 'mode' => 'included', 'value' => null]]])->assertStatus(412); // a count or Unlimited
        $this->postJson("/plan/{$plan->id}/entitlements", ['entitlements' => [['id' => $agents->id, 'mode' => 'included', 'unlimited' => true]]])->assertOk();
        $this->assertSame('', $row()['value']); // unlimited

        $this->postJson("/plan/{$plan->id}/entitlements", ['entitlements' => [['id' => $agents->id, 'mode' => 'included', 'value' => '7']]])->assertOk();
        $this->patchJson("/plan/{$plan->id}", [
            'name' => 'Renamed', 'product' => $product->id, 'status' => 0,
            'currency' => ['INR'], 'add_price' => [500], 'renew_price' => [400], 'offer_price' => [null],
        ])->assertOk();
        $this->assertSame('7', $row()['value']); // a price edit keeps the agent count
    }

    public function test_optional_feature_needs_a_price_and_is_sold_but_not_included(): void
    {
        $chat = Entitlement::create([...$this->row(['feature' => 'chat'])]);
        $plan = Plan::factory()->create(['product' => $this->product->id]);
        \App\Model\Payment\PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'INR']);

        $this->postJson("/plan/{$plan->id}/entitlements", ['entitlements' => [['id' => $chat->id, 'mode' => 'optional']]])->assertStatus(412);
        $this->postJson("/plan/{$plan->id}/entitlements", ['entitlements' => [
            ['id' => $chat->id, 'mode' => 'optional', 'prices' => ['INR' => ['unit_price' => 500]]],
        ]])->assertOk();

        $this->assertDatabaseMissing('plan_entitlements', ['plan_id' => $plan->id, 'entitlement_id' => $chat->id]);
        $this->assertTrue($chat->fresh()->canBeSoldOn($plan));
        $row = collect($this->getJson("/plan/{$plan->id}/entitlements")->json('data.entitlements'))->firstWhere('id', $chat->id);
        $this->assertSame('optional', $row['mode']);
    }

    public function test_plan_can_charge_for_every_agent(): void
    {
        $product = Product::factory()->create(['show_agent' => 1]);
        $plan = Plan::factory()->create(['product' => $product->id]);
        $price = \App\Model\Payment\PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => 1000, 'offer_price' => 0, 'no_of_agents' => 5]);
        $agents = \App\Services\EntitlementService::agents();

        // 0 included needs an agent price in every currency.
        $this->postJson("/plan/{$plan->id}/entitlements", ['entitlements' => [['id' => $agents->id, 'mode' => 'included', 'value' => '0']]])->assertStatus(412);
        $this->postJson("/plan/{$plan->id}/entitlements", ['entitlements' => [
            ['id' => $agents->id, 'mode' => 'included', 'value' => '0', 'prices' => ['INR' => ['unit_price' => 20]]],
        ]])->assertOk();

        $price->refresh();
        $this->assertSame('1', (string) $price->no_of_agents); // customers start at one agent, never 0000 (unlimited)
        $sales = new \App\Services\EntitlementSalesService;
        $this->assertSame(1020.0, $sales->planCost($price, 1, 'purchase'));
        $this->assertSame(1100.0, $sales->planCost($price, 5, 'purchase'));
        $row = collect($this->getJson("/plan/{$plan->id}/entitlements")->json('data.entitlements'))->firstWhere('id', $agents->id);
        $this->assertSame('0', $row['value']);
    }
}
