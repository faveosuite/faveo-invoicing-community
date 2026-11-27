<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Product;

use App\Http\Controllers\Order\OrderController;
use App\License\Models\License;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Entitlement;
use App\Model\Product\Grant;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Services\EntitlementService;
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
        return ['code' => 'sla', 'label' => 'SLA', 'type' => 'boolean', 'default_value' => '0', 'is_active' => true, ...$overrides];
    }

    public function test_sync_creates_items_and_rejects_duplicates(): void
    {
        $this->postJson('/entitlements', ['entitlements' => [$this->row(['category' => ' Support '])]])
            ->assertOk();
        $this->assertDatabaseHas('entitlements', ['code' => 'sla', 'category' => 'Support']);

        $this->postJson('/entitlements', ['entitlements' => [$this->row(), $this->row()]])
            ->assertStatus(412);
    }

    public function test_a_choice_or_text_needs_a_default_and_an_empty_number_default_is_unlimited(): void
    {
        $text = $this->row(['code' => 'region', 'type' => 'text', 'default_value' => null]);
        $this->postJson('/entitlements', ['entitlements' => [$text]])
            ->assertStatus(412)->assertJsonFragment([__('message.entitlement_default_required')]);

        $this->postJson('/entitlements', ['entitlements' => [
            [...$text, 'default_value' => 'US'],
            $this->row(['code' => 'storage', 'type' => 'number', 'default_value' => null]),
        ]])->assertOk();
        $this->assertNull(Entitlement::where('code', 'storage')->value('default_value'));
    }

    public function test_index_pages_and_searches_when_a_limit_is_given(): void
    {
        $this->postJson('/entitlements', ['entitlements' => [
            $this->row(), $this->row(['code' => 'chat', 'label' => 'Chat']), $this->row(['code' => 'kb', 'label' => 'Knowledge base']),
        ]])->assertOk();
        $total = count($this->getJson('/entitlements')->json('data.entitlements'));

        $this->getJson('/entitlements?limit=2&page=1')->assertOk()
            ->assertJsonPath('data.total', $total)->assertJsonPath('data.per_page', 2)->assertJsonCount(2, 'data.data');
        $this->getJson('/entitlements?limit=10&search-query=knowledge')
            ->assertJsonPath('data.total', 1)->assertJsonPath('data.data.0.code', 'kb');
    }

    public function test_used_item_cannot_change_type_and_is_archived_when_removed(): void
    {
        $item = Entitlement::create([...$this->row()]);
        $this->planGrants(License::create([
            'product_id' => $this->product->id, 'license_code' => 'ENT'.uniqid(), 'license_order_number' => '444444',
            'license_limit' => 1, 'license_status' => 1,
        ]), ['sla' => '0']);

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

        $used = Entitlement::create([...$this->row(['code' => 'chat'])]);
        $plan = Plan::factory()->create(['product' => $this->product->id]);
        $plan->entitlements()->attach($used->id, ['value' => '1']);
        $this->deleteJson("/entitlements/{$used->id}")->assertOk();
        $this->assertFalse($used->fresh()->is_active);
    }

    public function test_order_override_can_be_set_and_reset(): void
    {
        $chat = Entitlement::create([...$this->row(['code' => 'chat'])]);
        $agents = EntitlementService::agents();
        $order = \App\Model\Order\Order::factory()->create(['product' => $this->product->id, 'number' => 88800001]);
        $license = License::create([
            'product_id' => $this->product->id, 'license_code' => 'ENT'.uniqid().'X', 'license_order_number' => '88800001',
            'license_limit' => 1, 'license_status' => 1,
        ]);

        $this->putJson("/order/{$order->id}/entitlements/{$chat->id}", ['value' => '1', 'until' => now()->addMonth()->toDateString()])->assertOk();
        $this->putJson("/order/{$order->id}/entitlements/{$chat->id}", ['value' => 'maybe'])->assertStatus(412);

        $page = $this->getJson("/order/{$order->id}/entitlements")
            ->assertOk()
            ->assertJsonPath('data.entitlements.0.value', true)
            ->assertJsonPath('data.entitlements.0.override', '1')
            ->assertJsonCount(1, 'data.entitlements'); // the code has no agent count
        // Agents can be given like any other feature.
        $this->assertContains($agents->id, array_column($page->json('data.available'), 'id'));

        $this->deleteJson("/order/{$order->id}/entitlements/{$chat->id}")->assertOk();
        $this->assertSame([], $this->grantsOf($license, 'admin'));
    }

    public function test_any_number_can_be_overridden_to_unlimited_but_not_left_blank(): void
    {
        $storage = Entitlement::create([...$this->row(['code' => 'storage', 'type' => 'number', 'default_value' => '5'])]);
        $chat = Entitlement::create([...$this->row(['code' => 'chat'])]);
        $order = $this->agentsOrder('0005');
        $license = License::where('license_order_number', $order->number)->firstOrFail();

        $this->putJson("/order/{$order->id}/entitlements/{$storage->id}", ['value' => ''])->assertStatus(412);
        $this->putJson("/order/{$order->id}/entitlements/{$chat->id}", ['unlimited' => true])->assertStatus(412); // only numbers

        $this->putJson("/order/{$order->id}/entitlements/{$storage->id}", ['unlimited' => true])->assertOk();
        $row = collect($this->getJson("/order/{$order->id}/entitlements")->json('data.entitlements'))->firstWhere('code', 'storage');
        $this->assertSame([true, null, false], [$row['overridden'], $row['value'], $row['in_plan']]);
        $this->assertArrayHasKey('storage', resolve(EntitlementService::class)->payload($license)['limits']);
        $this->assertNull(resolve(EntitlementService::class)->payload($license)['limits']['storage']);
    }

    /** A licensed order on a plan including 5 agents, its code ending in $digits. */
    private function agentsOrder(string $digits, bool $cloud = false): Order
    {
        $plan = Plan::factory()->create(['product' => $this->product->id, 'days' => 30]);
        $plan->entitlements()->attach(EntitlementService::agents()->id, ['value' => '5']);
        if ($cloud) {
            \App\Model\Product\CloudProducts::create(['cloud_product' => $this->product->id, 'cloud_free_plan' => Plan::factory()->create(['product' => $this->product->id])->id]);
        }
        $code = 'AGNT'.random_int(10000000, 99999999).$digits;
        $order = Order::factory()->create(['product' => $this->product->id, 'number' => random_int(10000000, 99999999), 'order_status' => 'executed', 'serial_key' => \Illuminate\Support\Facades\Crypt::encrypt($code)]);
        Subscription::factory()->create(['order_id' => $order->id, 'plan_id' => $plan->id, 'product_id' => $this->product->id, 'ends_at' => now()->addDays(20)]);
        License::create(['product_id' => $this->product->id, 'license_code' => $code, 'license_order_number' => (string) $order->number, 'license_limit' => 1, 'license_status' => 1]);

        return $order;
    }

    public function test_an_agents_override_writes_the_license_digits_and_a_reset_puts_them_back(): void
    {
        $order = $this->agentsOrder('0005');
        $agents = EntitlementService::agents();
        $digits = fn (): string => substr((string) $order->fresh()->serial_key, -4);
        $licenseDigits = fn (): string => substr((string) License::where('license_order_number', $order->number)->value('license_code'), -4);
        $row = fn () => collect($this->getJson("/order/{$order->id}/entitlements")->json('data.entitlements'))->firstWhere('code', 'agents');

        $this->putJson("/order/{$order->id}/entitlements/{$agents->id}", ['value' => '12'])->assertOk();
        $this->assertSame(['0012', '0012'], [$digits(), $licenseDigits()]);
        $this->assertSame(12, $row()['value']);
        $this->assertSame('12', $row()['override']);

        // Unlimited is asked for on its own (0 would read as 0000), and stored empty like any number.
        $this->putJson("/order/{$order->id}/entitlements/{$agents->id}", ['value' => '0'])->assertStatus(412);
        $this->putJson("/order/{$order->id}/entitlements/{$agents->id}", ['unlimited' => true])->assertOk();
        $this->assertSame(['0000', '0000'], [$digits(), $licenseDigits()]);
        $this->assertTrue(resolve(\App\Services\AgentCountService::class)->unlimited($order));
        $this->assertSame([true, null, null], [$row()['overridden'], $row()['override'], $row()['value']]);

        $this->deleteJson("/order/{$order->id}/entitlements/{$agents->id}")->assertOk();
        $this->assertSame(['0005', '0005'], [$digits(), $licenseDigits()]);
        $this->assertFalse($row()['overridden']);

        // Four digits only, and no end date: the license code can't change back on its own.
        $this->putJson("/order/{$order->id}/entitlements/{$agents->id}", ['value' => '10000'])->assertStatus(412);
        $this->putJson("/order/{$order->id}/entitlements/{$agents->id}", ['value' => '7', 'until' => now()->addMonth()->toDateString()])->assertStatus(412);
        $this->assertSame('0005', $digits());
    }

    public function test_a_cloud_agents_override_below_the_agents_in_use_is_refused(): void
    {
        $order = $this->agentsOrder('0008', cloud: true);
        $code = (string) License::where('license_order_number', $order->number)->value('license_code');
        \App\License\Models\Installation::create(['product_id' => $this->product->id, 'license_code' => $code, 'installation_domain' => 'tenant.example.test']);
        // The tenant answers "true": more agents in use than the 3 asked for.
        $history = [];
        $stack = \GuzzleHttp\HandlerStack::create(new \GuzzleHttp\Handler\MockHandler([new \GuzzleHttp\Psr7\Response(200, [], 'true{}')]));
        $stack->push(\GuzzleHttp\Middleware::history($history));
        $this->app->bind(\GuzzleHttp\Client::class, fn (): \GuzzleHttp\Client => new \GuzzleHttp\Client(['handler' => $stack]));

        $this->putJson("/order/{$order->id}/entitlements/".EntitlementService::agents()->id, ['value' => '3'])
            ->assertStatus(400)->assertJsonPath('message', __('message.agent_reduce'));

        $this->assertStringContainsString('number_of_agents=3', (string) $history[0]['request']->getBody());
        $this->assertSame('0008', substr((string) $order->fresh()->serial_key, -4));
        $this->assertNull(resolve(\App\Services\AgentCountService::class)->override($order));
    }

    public function test_agents_cannot_be_overridden_while_an_agents_purchase_is_unpaid(): void
    {
        // Paying it later would add renewing agents the override hides.
        $order = $this->agentsOrder('0005');
        $agents = EntitlementService::agents();
        $invoice = \App\Model\Order\Invoice::factory()->create(['status' => 'pending',
            'metadata' => ['type' => \App\Services\EntitlementSalesService::INVOICE_TYPE, 'order_id' => $order->id, 'entitlement_id' => $agents->id, 'quantity' => 2]]);

        $this->putJson("/order/{$order->id}/entitlements/{$agents->id}", ['value' => '12'])
            ->assertStatus(400)->assertJsonPath('message', __('message.order_change_invoice_pending', ['number' => $invoice->number]));
        $this->deleteJson("/order/{$order->id}/entitlements/{$agents->id}")->assertStatus(400);

        $this->assertSame('0005', substr((string) $order->fresh()->serial_key, -4));
        $this->assertNull(resolve(\App\Services\AgentCountService::class)->override($order));
    }

    public function test_a_lower_cloud_agents_override_needs_the_tenant(): void
    {
        $order = $this->agentsOrder('0008', cloud: true); // no installation: the agents in use can't be checked

        $this->putJson("/order/{$order->id}/entitlements/".EntitlementService::agents()->id, ['value' => '3'])
            ->assertStatus(400)->assertJsonPath('message', __('message.installation_path_not_found'));
        $this->assertNull(resolve(\App\Services\AgentCountService::class)->override($order));
    }

    public function test_plan_features_list_the_products_other_plans_to_copy_from(): void
    {
        $plan = Plan::factory()->create(['product' => $this->product->id, 'name' => 'B plan']);
        $other = Plan::factory()->create(['product' => $this->product->id, 'name' => 'A plan']);
        Plan::factory()->create(['product' => Product::factory()->create()->id]); // another product's

        $this->getJson("/plan/{$plan->id}/entitlements")->assertOk()
            ->assertJsonPath('data.other_plans', [['id' => $other->id, 'name' => 'A plan']]);
    }

    public function test_plan_save_includes_features_with_values_and_prices(): void
    {
        $storage = Entitlement::create([...$this->row(['code' => 'storage', 'type' => 'number', 'default_value' => '5'])]);
        $region = Entitlement::create([...$this->row(['code' => 'region', 'type' => 'text', 'default_value' => 'US'])]);
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

    public function test_plan_save_treats_agents_as_any_number_feature(): void
    {
        $plan = Plan::factory()->create(['product' => $this->product->id]);
        \App\Model\Payment\PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'INR']);
        $agents = EntitlementService::agents();
        $save = fn (array $row) => $this->postJson("/plan/{$plan->id}/entitlements", ['entitlements' => [['id' => $agents->id, ...$row]]]);
        $value = fn () => \Illuminate\Support\Facades\DB::table('plan_entitlements')->where('plan_id', $plan->id)->where('entitlement_id', $agents->id)->value('value');
        $row = fn () => collect($this->getJson("/plan/{$plan->id}/entitlements")->json('data.entitlements'))->firstWhere('id', $agents->id);

        $save(['mode' => 'included', 'value' => '-3'])->assertStatus(412);
        $save(['mode' => 'included', 'value' => '10000', 'prices' => ['INR' => ['unit_price' => 20]]])->assertStatus(412); // four license digits

        $save(['mode' => 'included', 'value' => '7', 'prices' => ['INR' => ['unit_price' => 20]]])->assertOk();
        $this->assertSame('7', $value());
        $this->assertSame('7', $row()['value']);
        $this->assertArrayNotHasKey('unlimited', $row());
        $this->assertArrayNotHasKey('agents', $row());

        // Empty = unlimited, like any number feature: nothing more to sell.
        $save(['mode' => 'included', 'value' => null, 'prices' => ['INR' => ['unit_price' => 20]]])->assertOk();
        $this->assertDatabaseMissing('entitlement_prices', ['plan_id' => $plan->id, 'entitlement_id' => $agents->id]);
        $this->assertDatabaseHas('plan_entitlements', ['plan_id' => $plan->id, 'entitlement_id' => $agents->id, 'value' => null]);
        $this->assertNull($row()['value']);
        $this->assertTrue(EntitlementService::unlimitedAgents($plan));

        // Agents can't be optional: a per-agent plan needs a count on the license.
        $save(['mode' => 'optional', 'value' => '3', 'prices' => ['INR' => ['unit_price' => 20]]])->assertOk();
        $this->assertSame('3', $value());
        $this->patchJson("/plan/{$plan->id}", [
            'name' => 'Renamed', 'product' => $this->product->id, 'status' => 0,
            'currency' => ['INR'], 'add_price' => [500], 'renew_price' => [400], 'offer_price' => [null],
        ])->assertOk();
        $this->assertSame('3', $row()['value']); // a price edit keeps the agent count

        $save(['mode' => 'none', 'prices' => ['INR' => ['unit_price' => 20]]])->assertOk();
        $this->assertDatabaseMissing('plan_entitlements', ['plan_id' => $plan->id, 'entitlement_id' => $agents->id]);
        $this->assertDatabaseMissing('entitlement_prices', ['plan_id' => $plan->id, 'entitlement_id' => $agents->id]);
        $this->assertSame('none', $row()['mode']);
    }

    public function test_an_empty_number_feature_is_unlimited(): void
    {
        $storage = Entitlement::create([...$this->row(['code' => 'storage', 'type' => 'number', 'default_value' => '5'])]);
        $plan = Plan::factory()->create(['product' => $this->product->id]);

        $this->postJson("/plan/{$plan->id}/entitlements", ['entitlements' => [['id' => $storage->id, 'mode' => 'included', 'value' => '']]])->assertOk();

        $this->assertDatabaseHas('plan_entitlements', ['plan_id' => $plan->id, 'entitlement_id' => $storage->id, 'value' => null]);
        $this->assertNull(collect($this->getJson("/plan/{$plan->id}/entitlements")->json('data.entitlements'))->firstWhere('id', $storage->id)['value']);
        $this->assertNull((new EntitlementService)->forPlan($this->product->id, $plan->id)['storage']); // the license gets no limit
    }

    public function test_optional_feature_needs_a_price_and_is_sold_but_not_included(): void
    {
        $chat = Entitlement::create([...$this->row(['code' => 'chat'])]);
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
        $plan = Plan::factory()->create(['product' => $this->product->id]);
        $price = \App\Model\Payment\PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => 1000, 'offer_price' => 0]);
        $agents = \App\Services\EntitlementService::agents();

        // 0 included needs an agent price in every currency.
        $this->postJson("/plan/{$plan->id}/entitlements", ['entitlements' => [['id' => $agents->id, 'mode' => 'included', 'value' => '0']]])->assertStatus(412);
        $this->postJson("/plan/{$plan->id}/entitlements", ['entitlements' => [
            ['id' => $agents->id, 'mode' => 'included', 'value' => '0', 'prices' => ['INR' => ['unit_price' => 20]]],
        ]])->assertOk();

        $this->assertSame(0, EntitlementService::includedAgents($plan));
        $this->assertTrue($agents->fresh()->canBeSoldOn($plan)); // every agent is an Agents extra
        $row = collect($this->getJson("/plan/{$plan->id}/entitlements")->json('data.entitlements'))->firstWhere('id', $agents->id);
        $this->assertSame('0', $row['value']);
    }

    public function test_a_service_order_keeps_its_features_and_dates_on_the_subscription(): void
    {
        \Illuminate\Support\Carbon::setTestNow('2026-11-20 10:00:00');
        $service = Product::factory()->create(['issues_license' => false]);
        $support = Entitlement::create([...$this->row(['code' => 'support', 'type' => 'date', 'default_value' => null])]);
        $chat = Entitlement::create([...$this->row(['code' => 'chat'])]);
        $plan = Plan::factory()->create(['product' => $service->id, 'days' => 365]);
        $plan->entitlements()->attach($support->id, ['value' => '{"ends":"custom","every":30,"unit":"day"}']);
        $order = Order::factory()->create(['product' => $service->id, 'number' => random_int(10000000, 99999999)]);

        resolve(OrderController::class)->addSubscription($order->id, $plan->id, 'v1', $service->id, 'SERVICE'.uniqid());

        $this->assertFalse(License::where('license_order_number', $order->number)->exists()); // no license for a service
        $subscription = Subscription::where('order_id', $order->id)->firstOrFail();
        $this->assertSame('2026-12-20', Grant::where('subscription_id', $subscription->id)->whereNull('license_id')->where('code', 'support')->first()?->ends_at?->toDateString());

        $page = $this->getJson("/order/{$order->id}/entitlements")->assertOk();
        $this->assertFalse($page->json('data.has_license'));
        $this->assertSame('2026-12-20', collect($page->json('data.dates'))->firstWhere('code', 'support')['ends_at']);
        $this->assertContains($chat->id, array_column($page->json('data.available'), 'id'));

        // Give a feature and move a date: both stay on the subscription.
        $this->putJson("/order/{$order->id}/entitlements/{$chat->id}", ['value' => '1'])->assertOk();
        $this->putJson("/order/{$order->id}/dates/support", ['date' => '2027-01-31'])->assertOk();
        $this->assertSame('2027-01-31', $subscription->fresh()->support_ends_at ? substr((string) $subscription->fresh()->support_ends_at, 0, 10) : null);
        $this->assertSame('2027-01-31', Grant::where('subscription_id', $subscription->id)->where('code', 'support')->first()?->ends_at?->toDateString());
        $this->getJson("/order/{$order->id}/entitlements")->assertJsonPath('data.entitlements.0.code', 'chat')->assertJsonPath('data.entitlements.0.value', true);
        \Illuminate\Support\Carbon::setTestNow();
    }

    public function test_order_dates_edit_the_license_grant_and_the_old_columns(): void
    {
        $order = Order::factory()->create(['product' => $this->product->id, 'number' => random_int(10000000, 99999999)]);
        $subscription = Subscription::factory()->create(['order_id' => $order->id, 'product_id' => $this->product->id, 'ends_at' => '2027-05-01 00:00:00']);
        $license = License::create([
            'product_id' => $this->product->id, 'license_code' => 'DATE'.uniqid().'X', 'license_order_number' => (string) $order->number,
            'license_limit' => 1, 'license_status' => 1,
        ]);
        Entitlement::create([...$this->row(['code' => 'support', 'type' => 'date', 'default_value' => null])]);

        $this->putJson("/order/{$order->id}/dates/support", ['date' => '2027-08-15'])->assertOk();
        $this->assertSame('2027-08-15', substr((string) $subscription->fresh()->support_ends_at, 0, 10));
        $this->assertSame('2027-08-15', resolve(EntitlementService::class)->currentDates($license->fresh())['support']);

        $this->putJson("/order/{$order->id}/dates/license", ['date' => null])->assertOk(); // never
        $this->assertNull($subscription->fresh()->ends_at);

        $this->putJson("/order/{$order->id}/dates/nonsense", ['date' => '2027-01-01'])->assertStatus(412);
        $this->assertTrue($this->getJson("/order/{$order->id}/entitlements")->json('data.has_license'));
    }
}

