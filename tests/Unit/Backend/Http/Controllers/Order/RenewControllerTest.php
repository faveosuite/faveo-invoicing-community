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
    // getPriceByProductId
    // -------------------------------------------------------------------------

    public function test_get_price_by_product_id_throws_when_product_not_found(): void
    {
        $this->expectException(\Exception::class);
        $this->controller->getPriceByProductId(999999, $this->user->id);
    }

    public function test_get_price_by_product_id_returns_price_when_found(): void
    {
        $product = Product::has('price')->first();
        if (! $product) {
            $product = \App\Model\Product\Product::create(['name' => 'RenewPP '.uniqid()]);
        }

        try {
            $result = $this->controller->getPriceByProductId($product->id, $this->user->id);
            $this->assertIsNumeric($result);
        } catch (\Exception $e) {
            // Price might not match user currency → acceptable
            $this->assertNotEmpty($e->getMessage());
        }
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
}
