<?php

namespace Tests\Unit\Backend\Policies;

use App\Enums\OrderAbility;
use App\Model\Common\Setting;
use App\Model\License\LicensePermission;
use App\Model\License\LicenseType;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Policies\OrderPolicy;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class OrderPolicyTest extends DBTestCase
{
    use DatabaseTransactions;

    private OrderPolicy $policy;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new OrderPolicy;
        $this->owner = User::factory()->create(['role' => 'user']);
    }

    /** @param  list<string>  $permissions  stored permission names */
    private function order(array $permissions = ['Can be Downloaded'], array $subscription = [], array $order = []): Order
    {
        $type = LicenseType::factory()->create();
        foreach ($permissions as $name) {
            $type->permissions()->attach(LicensePermission::firstOrCreate(['permissions' => $name])->id);
        }
        $product = Product::factory()->create(['type' => $type->id]);

        $model = Order::factory()->create($order + ['product' => $product->id, 'client' => $this->owner->id, 'order_status' => 'executed']);
        if ($subscription !== []) {
            Subscription::factory()->create($subscription + ['order_id' => $model->id, 'product_id' => $product->id, 'user_id' => $this->owner->id, 'is_deleted' => 0]);
        }

        return $model->fresh();
    }

    private function stranger(): User
    {
        return User::factory()->create(['role' => 'user']);
    }

    public function test_every_order_ability_has_a_policy_method(): void
    {
        // Gate silently denies an ability with no matching method, so a rename would hide buttons without an error.
        foreach (OrderAbility::cases() as $ability) {
            $this->assertTrue(method_exists(OrderPolicy::class, $ability->value), "OrderPolicy has no {$ability->value}() for OrderAbility::{$ability->name}");
        }
    }

    public function test_gate_resolves_order_abilities_to_this_policy(): void
    {
        $this->be($this->owner);

        $this->assertTrue(\Gate::allows(OrderAbility::Download, $this->order(subscription: ['update_ends_at' => now()->addMonth()])));
    }

    // renew

    public function test_owner_can_renew_an_order_that_expires(): void
    {
        $this->assertTrue($this->policy->renew($this->owner, $this->order(subscription: ['ends_at' => now()->addMonth()]))->allowed());
    }

    public function test_renew_is_refused_with_403_for_someone_else(): void
    {
        $response = $this->policy->renew($this->stranger(), $this->order(subscription: ['ends_at' => now()->addMonth()]));

        $this->assertTrue($response->denied());
        $this->assertSame(403, $response->status());
    }

    public function test_renew_is_refused_for_terminated_deleted_or_never_expiring_orders(): void
    {
        $terminated = $this->order(subscription: ['ends_at' => now()->addMonth()], order: ['order_status' => 'Terminated']);
        $deleted = $this->order(subscription: ['ends_at' => now()->addMonth(), 'is_deleted' => 1]);
        $neverExpires = $this->order(subscription: ['ends_at' => null, 'update_ends_at' => null, 'support_ends_at' => null]);

        $this->assertTrue($this->policy->renew($this->owner, $terminated)->denied());
        $this->assertTrue($this->policy->renew($this->owner, $deleted)->denied());
        $this->assertTrue($this->policy->renew($this->owner, $neverExpires)->denied());
    }

    // download

    public function test_download_needs_the_allow_downloads_permission(): void
    {
        $allowed = $this->order(['Can be Downloaded'], ['update_ends_at' => now()->addMonth()]);
        $notAllowed = $this->order([], ['update_ends_at' => now()->addMonth()]);

        $this->assertTrue($this->policy->download($this->owner, $allowed)->allowed());
        $this->assertTrue($this->policy->download($this->owner, $notAllowed)->denied());
    }

    public function test_cloud_orders_can_never_download_even_with_the_permission_on(): void
    {
        $order = $this->order(['Can be Downloaded', 'Allow Downloads Before Updates Expire'], ['update_ends_at' => now()->addMonth()]);
        $plan = Plan::factory()->create(['product' => $order->getRawOriginal('product')]);
        CloudProducts::create(['cloud_product' => $order->getRawOriginal('product'), 'cloud_free_plan' => $plan->id]);

        $this->assertTrue($this->policy->download($this->owner, $order)->denied());
        $this->assertTrue($this->policy->downloadVersion($this->owner, $order, now()->subDay()->toDateTimeString())->denied());
    }

    public function test_download_is_refused_for_terminated_orders_and_orders_without_subscription(): void
    {
        $terminated = $this->order(subscription: ['update_ends_at' => now()->addMonth()], order: ['order_status' => 'Terminated']);

        $this->assertTrue($this->policy->download($this->owner, $terminated)->denied());
        $this->assertSame(__('message.no_order_exists_invoice'), $this->policy->download($this->owner, $this->order())->message());
    }

    public function test_after_updates_end_downloads_stop_unless_old_downloads_are_kept(): void
    {
        $ended = ['update_ends_at' => now()->subDay()];

        $stop = $this->order(['Can be Downloaded'], $ended);
        $keep = $this->order(['Can be Downloaded', 'Allow Downloads Before Updates Expire'], $ended);

        $this->assertSame(__('message.renew_subscription_download'), $this->policy->download($this->owner, $stop)->message());
        $this->assertTrue($this->policy->download($this->owner, $keep)->allowed());
    }

    public function test_admin_can_download_any_order_but_another_customer_cannot(): void
    {
        $order = $this->order(subscription: ['update_ends_at' => now()->addMonth()]);

        $this->assertTrue($this->policy->download(User::factory()->create(['role' => 'admin']), $order)->allowed());
        $this->assertSame(403, $this->policy->download($this->stranger(), $order)->status());
    }

    // downloadVersion

    public function test_only_releases_from_before_the_updates_end_can_be_downloaded(): void
    {
        $order = $this->order(['Can be Downloaded', 'Allow Downloads Before Updates Expire'], ['update_ends_at' => now()->subDays(10)]);

        $this->assertTrue($this->policy->downloadVersion($this->owner, $order, now()->subDays(20)->toDateTimeString())->allowed());
        $this->assertTrue($this->policy->downloadVersion($this->owner, $order, now()->subDays(5)->toDateTimeString())->denied());
        $this->assertTrue($this->policy->downloadVersion($this->owner, $order, null)->denied());
    }

    public function test_every_release_can_be_downloaded_when_updates_never_end(): void
    {
        $order = $this->order(subscription: ['update_ends_at' => null]);

        $this->assertTrue($this->policy->downloadVersion($this->owner, $order, now()->toDateTimeString())->allowed());
    }

    // deploy

    public function test_deploy_needs_deployment_switched_on_and_the_download_rules(): void
    {
        $order = $this->order(subscription: ['update_ends_at' => now()->addMonth()]);

        Setting::where('id', 1)->update(['deployment_enabled' => 0]);
        $this->assertTrue($this->policy->deploy($this->owner, $order)->denied());

        Setting::where('id', 1)->update(['deployment_enabled' => 1]);
        $this->assertTrue($this->policy->deploy($this->owner, $order)->allowed());
        $this->assertTrue($this->policy->deploy($this->owner, $this->order([], ['update_ends_at' => now()->addMonth()]))->denied());
    }

    public function test_plugins_can_be_downloaded_but_never_deployed(): void
    {
        Setting::where('id', 1)->update(['deployment_enabled' => 1]);
        $order = $this->order(subscription: ['update_ends_at' => now()->addMonth()]);
        $order->productRelation->licenseType->update(['name' => 'plugin']);
        $order = $order->fresh();

        $this->assertTrue($this->policy->download($this->owner, $order)->allowed());
        $this->assertTrue($this->policy->deploy($this->owner, $order)->denied());
    }

    // deleteCloud

    public function test_only_a_live_cloud_order_can_have_its_instance_deleted(): void
    {
        $selfHosted = $this->order([], ['update_ends_at' => now()->addMonth()]);
        $cloud = $this->order([], ['update_ends_at' => now()->addMonth()]);
        $deleted = $this->order([], ['update_ends_at' => now()->addMonth(), 'is_deleted' => 1]);
        foreach ([$cloud, $deleted] as $order) {
            $plan = Plan::factory()->create(['product' => $order->getRawOriginal('product')]);
            CloudProducts::create(['cloud_product' => $order->getRawOriginal('product'), 'cloud_free_plan' => $plan->id]);
        }

        $this->assertTrue($this->policy->deleteCloud($this->owner, $selfHosted)->denied());
        $this->assertTrue($this->policy->deleteCloud($this->owner, $cloud)->allowed());
        $this->assertTrue($this->policy->deleteCloud($this->owner, $deleted)->denied());
        $this->assertSame(403, $this->policy->deleteCloud($this->stranger(), $cloud)->status());
    }

    // cloud settings

    private function cloudOrder(array $subscription = [], array $order = []): Order
    {
        $model = $this->order([], $subscription + ['update_ends_at' => now()->addMonth()], $order);
        $plan = Plan::factory()->create(['product' => $model->getRawOriginal('product')]);
        CloudProducts::create(['cloud_product' => $model->getRawOriginal('product'), 'cloud_free_plan' => $plan->id]);

        return $model->fresh();
    }

    public function test_cloud_settings_are_only_for_the_owners_live_cloud_instance(): void
    {
        $this->assertTrue($this->policy->manageCloud($this->owner, $this->cloudOrder())->allowed());

        $this->assertSame(403, $this->policy->manageCloud($this->stranger(), $this->cloudOrder())->status());
        $this->assertTrue($this->policy->manageCloud($this->owner, $this->order([], ['update_ends_at' => now()->addMonth()]))->denied());
        $this->assertTrue($this->policy->manageCloud($this->owner, $this->cloudOrder(['is_deleted' => 1]))->denied());
        $this->assertTrue($this->policy->manageCloud($this->owner, $this->cloudOrder(order: ['order_status' => 'Terminated']))->denied());
    }

    public function test_no_second_cloud_change_while_one_is_unpaid(): void
    {
        $order = $this->cloudOrder();
        $this->assertTrue($this->policy->changeCloudAgents($this->owner, $order)->allowed());

        $invoice = \App\Model\Order\Invoice::factory()->create(['user_id' => $this->owner->id, 'status' => 'pending',
            'metadata' => ['type' => 'agent_alteration', 'order_id' => $order->id]]);

        $this->assertSame(
            __('message.order_change_invoice_pending', ['number' => $invoice->number]),
            $this->policy->changeCloudAgents($this->owner, $order)->message()
        );
    }

    public function test_plan_changes_only_go_to_plans_the_plan_list_offers(): void
    {
        $order = $this->cloudOrder();
        $samePlanProduct = Plan::factory()->create(['product' => $order->getRawOriginal('product'), 'name' => 'Yearly']);
        $offered = Plan::factory()->create(['product' => Product::factory()->create(['type' => 4, 'can_modify_agent' => 1])->id, 'name' => 'Cloud Pro']);
        \App\Model\Payment\PlanPrice::factory()->create(['plan_id' => $offered->id, 'renew_price' => 100]);

        $this->assertTrue($this->policy->changeCloudPlan($this->owner, $order, $offered->id)->allowed());
        $this->assertTrue($this->policy->changeCloudPlan($this->owner, $order, $samePlanProduct->id)->denied());
    }

    // license actions

    public function test_customers_reissue_only_their_own_live_license_admins_any_never_cloud(): void
    {
        $selfHosted = $this->order(subscription: ['update_ends_at' => now()->addMonth()]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($this->policy->reissue($this->owner, $selfHosted)->allowed());
        $this->assertSame(403, $this->policy->reissue($this->stranger(), $selfHosted)->status());
        $this->assertTrue($this->policy->reissue($this->owner, $this->cloudOrder())->denied());
        $this->assertTrue($this->policy->reissue($this->owner, $this->order(order: ['order_status' => 'Terminated']))->denied());
        $this->assertTrue($this->policy->reissue($admin, $selfHosted)->allowed());
        $this->assertTrue($this->policy->reissue($admin, $this->cloudOrder())->denied());
    }

    public function test_license_file_only_for_file_mode_orders(): void
    {
        $file = $this->order(order: ['license_mode' => 'File']);

        $this->assertTrue($this->policy->useLicenseFile($this->owner, $file)->allowed());
        $this->assertTrue($this->policy->useLicenseFile($this->owner, $this->order(order: ['license_mode' => 'Database']))->denied());
        $this->assertTrue($this->policy->useLicenseFile($this->owner, $this->order(order: ['license_mode' => 'File', 'order_status' => 'Terminated']))->denied());
        $this->assertTrue($this->policy->useLicenseFile(User::factory()->create(['role' => 'admin']), $file)->allowed());
        $this->assertSame(403, $this->policy->useLicenseFile($this->stranger(), $file)->status());
        $this->assertTrue($this->policy->useLicenseFile($this->owner, $this->cloudOrder(order: ['license_mode' => 'File']))->denied());
    }

    public function test_auto_renewal_can_only_be_turned_on_with_an_offered_gateway(): void
    {
        $order = $this->order(subscription: ['ends_at' => now()->addMonth()]);
        Setting::where('id', 1)->update(['autorenewal_status' => 1]);
        \App\Model\Common\StatusSetting::query()->update(['stripe_auto_renewal' => 1, 'razorpay_auto_renewal' => 0]);
        \App\Model\Plugin::whereIn('name', ['Stripe', 'Razorpay'])->update(['status' => 1]);
        $this->owner->update(['country' => 'IN']);

        $this->assertTrue($this->policy->enableAutoRenew($this->owner, $order, 'Stripe')->allowed());
        $this->assertTrue($this->policy->enableAutoRenew($this->owner, $order, 'Razorpay')->denied());
    }
}
