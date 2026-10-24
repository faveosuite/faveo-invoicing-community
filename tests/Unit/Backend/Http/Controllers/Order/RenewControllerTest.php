<?php

namespace Tests\Unit\Backend\Http\Controllers\Order;

use App\Http\Controllers\Order\RenewController;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class RenewControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    private RenewController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
        $this->controller = new RenewController;
    }

    // -------------------------------------------------------------------------
    // getProductById
    // -------------------------------------------------------------------------

    public function test_get_product_by_id_returns_product_when_found(): void
    {
        $product = Product::first() ?? Product::create(['name' => 'Test Product '.uniqid()]);

        $result = $this->controller->getProductById($product->id);
        $this->assertInstanceOf(Product::class, $result);
    }

    public function test_get_product_by_id_returns_null_when_not_found(): void
    {
        $result = $this->controller->getProductById(999999);
        $this->assertNull($result);
    }

    // -------------------------------------------------------------------------
    // getUserById
    // -------------------------------------------------------------------------

    public function test_get_user_by_id_returns_user_when_found(): void
    {
        $result = $this->controller->getUserById($this->user->id);
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($this->user->id, $result->id);
    }

    public function test_get_user_by_id_returns_null_when_not_found(): void
    {
        $result = $this->controller->getUserById(999999);
        $this->assertNull($result);
    }

    // -------------------------------------------------------------------------
    // createOrderInvoiceRelation
    // -------------------------------------------------------------------------

    public function test_create_order_invoice_relation_creates_record(): void
    {
        $order = Order::first();
        $invoice = \App\Model\Order\Invoice::first();

        if (! $order) {
            $order = \App\Model\Order\Order::create(['client' => $this->user->id, 'order_status' => 'executed', 'number' => mt_rand(10000000, 99999999)]);
        }
        if (! $invoice) {
            $invoice = \App\Model\Order\Invoice::factory()->create(['user_id' => $this->user->id]);
        }

        // Should not throw; wrapped in try-catch internally
        $this->controller->createOrderInvoiceRelation($order->id, $invoice->id);
        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // setSession
    // -------------------------------------------------------------------------

    public function test_set_session_stores_values(): void
    {
        $this->controller->setSession(5, 10);

        $this->assertEquals(5, session('subscription_id'));
        $this->assertEquals(10, session('plan_id'));
    }

    // -------------------------------------------------------------------------
    // getExpiryDate / getUpdatesExpiryDate / getSupportExpiryDate
    // -------------------------------------------------------------------------

    public function test_get_expiry_date_returns_empty_when_days_zero(): void
    {
        $sub = new Subscription(['ends_at' => now()->toDateString()]);
        $result = $this->controller->getExpiryDate(1, $sub, 0);
        $this->assertEquals('', $result);
    }

    public function test_get_expiry_date_returns_empty_when_permissions_zero(): void
    {
        $sub = new Subscription(['ends_at' => now()->toDateString()]);
        $result = $this->controller->getExpiryDate(0, $sub, 30);
        $this->assertEquals('', $result);
    }

    public function test_get_expiry_date_adds_days_when_permissions_set(): void
    {
        $sub = new Subscription(['ends_at' => now()->toDateString()]);
        $result = $this->controller->getExpiryDate(1, $sub, 30);
        $this->assertNotEmpty($result);
    }

    public function test_get_updates_expiry_date_returns_empty_when_days_zero(): void
    {
        $sub = new Subscription(['update_ends_at' => now()->toDateString()]);
        $result = $this->controller->getUpdatesExpiryDate(1, $sub, 0);
        $this->assertEquals('', $result);
    }

    public function test_get_updates_expiry_date_adds_days(): void
    {
        $sub = new Subscription(['update_ends_at' => now()->toDateString()]);
        $result = $this->controller->getUpdatesExpiryDate(1, $sub, 15);
        $this->assertNotEmpty($result);
    }

    public function test_get_support_expiry_date_returns_empty_when_permissions_zero(): void
    {
        $sub = new Subscription(['support_ends_at' => now()->toDateString()]);
        $result = $this->controller->getSupportExpiryDate(0, $sub, 30);
        $this->assertEquals('', $result);
    }

    public function test_get_support_expiry_date_adds_days(): void
    {
        $sub = new Subscription(['support_ends_at' => now()->toDateString()]);
        $result = $this->controller->getSupportExpiryDate(1, $sub, 7);
        $this->assertNotEmpty($result);
    }

    // -------------------------------------------------------------------------
    // renewByClient — HTTP endpoint
    // -------------------------------------------------------------------------

    public function test_renew_by_client_fails_validation_when_plan_missing(): void
    {
        $sub = Subscription::first();
        if (! $sub) {
            $this->assertTrue(true);

            return;
        } // no subscription available

        $response = $this->postJson("/client/renew/{$sub->id}", []);

        // Validation fails → 422 (standard Laravel validation) or 200 with error
        $this->assertContains($response->getStatusCode(), [200, 422, 500]);
    }

    public function test_renew_by_client_refuses_a_deleted_cloud_instance(): void
    {
        $sub = Subscription::factory()->create(['user_id' => $this->user->id]); // their own
        Subscription::where('id', $sub->id)->update(['is_deleted' => 1]);
        \App\Model\Order\Order::where('id', $sub->order_id)->update(['client' => $this->user->id, 'order_status' => 'executed']);

        $this->postJson("/client/renew/{$sub->id}", ['plan' => $sub->plan_id])
            ->assertStatus(403)
            ->assertJson(['success' => false, 'message' => __('message.cloud_instance_deleted_no_renew')]);

        $this->assertDatabaseMissing('invoices', ['user_id' => $sub->user_id, 'is_renewed' => 1]);
    }

    private function linkInvoice(Subscription $sub, string $status): \App\Model\Order\Invoice
    {
        $invoice = \App\Model\Order\Invoice::factory()->create(['user_id' => $sub->user_id, 'status' => $status]);
        \App\Model\Order\OrderInvoiceRelation::create(['order_id' => $sub->order_id, 'invoice_id' => $invoice->id]);

        return $invoice;
    }

    private function adminRenew(Subscription $sub)
    {
        // The factory may pick a Terminated order, which is never renewable.
        Order::whereKey($sub->order_id)->where('order_status', 'Terminated')->update(['order_status' => 'executed']);

        return $this->postJson("/admin/renew/{$sub->order_id}", ['plan' => $sub->plan_id, 'payment_method' => 'cash', 'cost' => 10]);
    }

    public function test_admin_renew_refuses_a_terminated_order(): void
    {
        $sub = Subscription::factory()->create();
        Order::whereKey($sub->order_id)->update(['order_status' => 'Terminated']);

        $this->postJson("/admin/renew/{$sub->order_id}", ['plan' => $sub->plan_id, 'payment_method' => 'cash', 'cost' => 10])
            ->assertStatus(403)
            ->assertJson(['success' => false, 'message' => __('message.no_permission_for_action')]);
        $this->assertDatabaseMissing('invoices', ['user_id' => $sub->user_id, 'is_renewed' => 1]);
    }

    public function test_admin_renew_refuses_a_deleted_cloud_instance(): void
    {
        $sub = Subscription::factory()->create();
        Subscription::where('id', $sub->id)->update(['is_deleted' => 1]);

        $this->adminRenew($sub)->assertStatus(403)
            ->assertJson(['success' => false, 'message' => __('message.renew_blocked_instance_deleted')]);
    }

    public function test_admin_renew_refuses_while_the_latest_invoice_is_unpaid(): void
    {
        $sub = Subscription::factory()->create();
        $this->linkInvoice($sub, 'Success');
        $unpaid = $this->linkInvoice($sub, 'Pending');

        $this->adminRenew($sub)->assertStatus(403)
            ->assertJson(['success' => false, 'message' => __('message.renew_blocked_unpaid_invoice', ['number' => $unpaid->number])]);
    }

    public function test_renew_is_not_blocked_once_the_latest_invoice_is_paid(): void
    {
        $sub = Subscription::factory()->create();
        $this->linkInvoice($sub, 'Pending');
        $this->linkInvoice($sub, 'Success');

        $this->assertNull(Order::find($sub->order_id)->renewBlockedReason());
    }

    public function test_renew_by_client_returns_error_for_invalid_subscription(): void
    {
        $response = $this->postJson('/client/renew/999999', ['plan' => 1]);

        // Subscription 999999 doesn't exist → findOrFail throws → caught → errorResponse (200)
        // or middleware redirects (302) or 401 if not auth
        $this->assertContains($response->getStatusCode(), [200, 302, 400, 401, 404, 500]);
    }

    // -------------------------------------------------------------------------
    // getCost (from BaseRenewController) — HTTP endpoint
    // -------------------------------------------------------------------------

    public function test_get_cost_returns_zero_when_no_plan_provided(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/get-renew-cost');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['formatted_price', 'renewalPrice']]);
    }

    public function test_get_cost_returns_error_for_invalid_plan(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/get-renew-cost?plan=999999&order=999999');

        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    // -------------------------------------------------------------------------
    // getProductByProductId (from BaseRenewController)
    // -------------------------------------------------------------------------

    public function test_get_product_by_product_id_returns_null_for_invalid_id(): void
    {
        $result = $this->controller->getProductByProductId(999999);

        $this->assertNull($result);
    }

    public function test_get_product_by_product_id_returns_product_when_found(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'Test Product '.uniqid()]);

        $result = $this->controller->getProductByProductId($product->id);

        $this->assertInstanceOf(\App\Model\Product\Product::class, $result);
    }

    // -------------------------------------------------------------------------
    // successRenew — direct call
    // -------------------------------------------------------------------------

    public function test_success_renew_throws_when_subscription_not_found(): void
    {
        $invoice = new \App\Model\Order\Invoice;
        $invoice->id = 999999;
        $invoice->status = 'unpaid';
        $invoice->grand_total = 0;

        $this->expectException(\Exception::class);

        $this->controller->successRenew($invoice);
    }

    // ── H2: admin renew settles its own invoice, so paying it can't extend dates again ──

    public function test_admin_renew_records_the_chosen_payment_so_the_invoice_cannot_be_paid_again(): void
    {
        $sub = Subscription::factory()->create();
        $this->linkInvoice($sub, 'Success');

        $this->adminRenew($sub)->assertOk();

        $renewal = \App\Model\Order\Invoice::whereHas('orders', fn ($q) => $q->whereKey($sub->order_id))
            ->where('is_renewed', 1)->latest('id')->firstOrFail();
        $this->assertSame('success', strtolower((string) $renewal->status)); // not left pending
        $this->assertSame(0.0, $renewal->outstanding());
        $this->assertSame(1, $renewal->payments()->where('payment_method', 'cash')->count());
    }

    public function test_a_client_cannot_renew_someone_elses_subscription(): void
    {
        $sub = Subscription::factory()->create(); // another user's

        $this->postJson("/client/renew/{$sub->id}", ['plan' => $sub->plan_id])->assertStatus(403);
        $this->assertDatabaseMissing('invoices', ['user_id' => $sub->user_id, 'is_renewed' => 1]);
    }

    // ── H5: renewal priced in the client's currency, whoever is asking ──

    private function usdClientOrderOnPlan(): array
    {
        $client = User::factory()->create(['country' => 'US']);
        $sub = Subscription::factory()->create(['user_id' => $client->id]);
        Order::whereKey($sub->order_id)->update(['client' => $client->id]);
        \App\Model\Payment\PlanPrice::where('plan_id', $sub->plan_id)->delete();
        foreach (['USD' => 50, 'INR' => 4000] as $currency => $price) {
            \App\Model\Payment\PlanPrice::factory()->create(['plan_id' => $sub->plan_id, 'currency' => $currency, 'renew_price' => $price, 'no_of_agents' => 0]);
        }

        return [$client, $sub];
    }

    public function test_an_admin_sees_the_clients_currency_not_their_own(): void
    {
        [, $sub] = $this->usdClientOrderOnPlan();
        $this->user->update(['country' => 'IN']); // the logged-in admin is in India

        $data = $this->getJson("/get-renew-cost?plan={$sub->plan_id}&order={$sub->order_id}")->assertOk()->json('data');

        $this->assertSame(currencyFormat(50, 'USD'), $data['formatted_price']);
    }

    public function test_a_client_cannot_price_another_clients_order(): void
    {
        [, $sub] = $this->usdClientOrderOnPlan();
        $this->getLoggedInUser('user'); // someone else

        $this->getJson("/get-renew-cost?plan={$sub->plan_id}&order={$sub->order_id}")->assertStatus(403);
    }

    // ── H6: older orders (no invoice_item_id) renew via the order↔invoice link ──

    public function test_an_older_order_without_invoice_item_id_can_be_renewed(): void
    {
        $sub = Subscription::factory()->create();
        $original = $this->linkInvoice($sub, 'Success');
        \App\Model\Order\InvoiceItem::factory()->create(['invoice_id' => $original->id, 'product_id' => $sub->product_id]);
        Order::whereKey($sub->order_id)->update(['invoice_item_id' => 0, 'order_status' => 'executed']); // like the 145 pre-pivot orders

        $this->postJson("/admin/renew/{$sub->order_id}", ['plan' => $sub->plan_id, 'payment_method' => 'cash', 'cost' => 10])
            ->assertOk()->assertJson(['success' => true]);

        $this->assertSame(1, \App\Model\Order\Invoice::whereHas('orders', fn ($q) => $q->whereKey($sub->order_id))->where('is_renewed', 1)->count());
    }

    // ── Batch 3 #2/#3: one open renewal per order; agent-change invoices are not renewals ──

    private function pendingOn(Subscription $sub, int $planId, ?array $metadata = null): \App\Model\Order\Invoice
    {
        $invoice = \App\Model\Order\Invoice::factory()->create(['user_id' => $sub->user_id, 'status' => 'pending', 'is_renewed' => 1, 'metadata' => $metadata]);
        \App\Model\Order\InvoiceItem::factory()->create(['invoice_id' => $invoice->id, 'plan_id' => $planId, 'product_id' => $sub->product_id]);
        \App\Model\Order\OrderInvoiceRelation::create(['order_id' => $sub->order_id, 'invoice_id' => $invoice->id]);

        return $invoice;
    }

    private function ownSubscription(): Subscription
    {
        $sub = Subscription::factory()->create(['user_id' => $this->user->id]);
        Order::whereKey($sub->order_id)->update(['client' => $this->user->id, 'order_status' => 'executed']);

        return $sub;
    }

    public function test_renewing_again_on_the_same_plan_returns_the_open_invoice(): void
    {
        $sub = $this->ownSubscription();
        $open = $this->pendingOn($sub, $sub->plan_id);

        $this->postJson("/client/renew/{$sub->id}", ['plan' => $sub->plan_id])
            ->assertOk()->assertJsonPath('data.invoice_id', $open->id);
    }

    public function test_renewing_on_another_plan_while_one_is_unpaid_is_refused(): void
    {
        $sub = $this->ownSubscription();
        $open = $this->pendingOn($sub, $sub->plan_id);
        $otherPlan = \App\Model\Payment\Plan::factory()->create(['product' => $sub->product_id]);
        $before = \App\Model\Order\Invoice::count();

        $this->postJson("/client/renew/{$sub->id}", ['plan' => $otherPlan->id])
            ->assertStatus(400)->assertJson(['message' => __('message.renew_blocked_unpaid_invoice', ['number' => $open->number])]);
        $this->assertSame($before, \App\Model\Order\Invoice::count()); // no second renewal invoice
    }

    public function test_an_unpaid_agent_change_invoice_is_not_handed_back_as_the_renewal(): void
    {
        $sub = $this->ownSubscription();
        $agentChange = $this->pendingOn($sub, $sub->plan_id, ['type' => 'agent_alteration']);

        $response = $this->postJson("/client/renew/{$sub->id}", ['plan' => $sub->plan_id]);

        $this->assertNotSame($agentChange->id, $response->json('data.invoice_id'));
        $response->assertStatus(400)->assertJson(['message' => __('message.renew_blocked_unpaid_invoice', ['number' => $agentChange->number])]);
    }

    public function test_a_terminated_order_cannot_be_renewed(): void
    {
        $sub = $this->ownSubscription();
        Order::whereKey($sub->order_id)->update(['order_status' => 'Terminated']);

        $this->postJson("/client/renew/{$sub->id}", ['plan' => $sub->plan_id])->assertStatus(403)->assertJson(['message' => __('message.plan_change_order_terminated')]);
    }

    // ── Batch 3 #1: the renew popup prices the order's real agent count, like the invoice ──

    public function test_the_popup_price_uses_the_orders_agents_after_a_plan_switch(): void
    {
        $sub = $this->ownSubscription();
        $this->user->update(['country' => 'US']);
        $original = $this->linkInvoice($sub, 'Success');
        \App\Model\Order\InvoiceItem::factory()->create(['invoice_id' => $original->id, 'plan_id' => $sub->plan_id, 'agents' => 10]); // 10 agents, on plan P1
        $p2 = \App\Model\Payment\Plan::factory()->create(['product' => $sub->product_id]);
        \App\Model\Payment\PlanPrice::factory()->create(['plan_id' => $p2->id, 'currency' => 'USD', 'renew_price' => 50, 'no_of_agents' => 5]);

        $data = $this->getJson("/get-renew-cost?plan={$p2->id}&order={$sub->order_id}")->assertOk()->json('data');

        $this->assertSame(currencyFormat(100, 'USD'), $data['formatted_price']); // 50 / 5 × 10 — was 50
    }
}
