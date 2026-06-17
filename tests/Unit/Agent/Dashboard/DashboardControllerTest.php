<?php

namespace Tests\Unit\Agent\Dashboard;

use App\Model\Order\InstallationDetail;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class DashboardControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getLoggedInUser('admin');
    }

    public function test_it_returns_zero_values_when_database_is_empty(): void
    {
        $response = $this->getJson('dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'totalSales' => [],
                'yearlySales' => [],
                'monthlySales' => [],
                'pendingPayments' => [],
                'productInstalledRate' => [
                    'total_subscription' => 0,
                    'inactive_subscription' => 0,
                    'rate' => 0,
                ],
                'paidOrderRate' => [
                    'all_orders' => 0,
                    'paid_orders' => 0,
                    'rate' => 0,
                ],
                'expiringOrders' => [],
                'expiredOrders' => [],
            ]);
    }

    public function test_it_calculates_total_yearly_and_monthly_sales_by_currency(): void
    {
        // 1. Current Month USD
        $invoice1 = Invoice::factory()->create(['currency' => 'USD', 'status' => 'paid', 'created_at' => now()]);
        Payment::factory()->create(['invoice_id' => $invoice1->id, 'amount' => 100]);

        // 2. Current Month EUR
        $invoice2 = Invoice::factory()->create(['currency' => 'EUR', 'status' => 'paid', 'created_at' => now()]);
        Payment::factory()->create(['invoice_id' => $invoice2->id, 'amount' => 50]);

        // 3. Last Year USD
        $invoice3 = Invoice::factory()->create(['currency' => 'USD', 'status' => 'paid', 'created_at' => now()->subYear()]);
        Payment::factory()->create(['invoice_id' => $invoice3->id, 'amount' => 200]);

        // 4. Pending USD
        $invoice4 = Invoice::factory()->create(['currency' => 'USD', 'status' => 'pending', 'created_at' => now()]);
        Payment::factory()->create(['invoice_id' => $invoice4->id, 'amount' => 0]);

        $response = $this->getJson('dashboard');

        $response->assertStatus(200);

        // Check Total Sales (100 + 200 = 300 USD, 50 EUR)
        $response->assertJsonPath('totalSales.USD', 300);
        $response->assertJsonPath('totalSales.EUR', 50);

        // Check Yearly Sales (Only current year: 100 USD, 50 EUR)
        $response->assertJsonPath('yearlySales.USD', 100);
        $response->assertJsonPath('yearlySales.EUR', 50);

        // Check Monthly Sales (Only current month: 100 USD, 50 EUR)
        $response->assertJsonPath('monthlySales.USD', 100);
    }

    public function test_it_calculates_pending_payments_correctly(): void
    {
        // Scenario: Invoice for 500 USD, 200 paid. Pending should be 300.
        $invoice = Invoice::factory()->create([
            'currency' => 'USD',
            'grand_total' => 500,
            'status' => 'partial',
        ]);
        Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 200]);

        $response = $this->getJson('dashboard');

        $response->assertJsonPath('pendingPayments.USD', 300);
    }

    public function test_it_calculates_product_installation_rate(): void
    {
        // Active
        $sub1 = Subscription::factory()->create(['created_at' => now()->subDays(5)]);
        $order1 = Order::find($sub1->order_id);
        // Assuming you have an InstallationDetail model linked via order_id
        InstallationDetail::insert(['order_id' => $order1->id]);

        // Inactive (No installation detail)
        $sub2 = Subscription::factory()->create(['created_at' => now()->subDays(5)]);
        Order::find($sub2->order_id);

        $response = $this->getJson('dashboard');

        $response->assertJsonPath('productInstalledRate.total_subscription', 2);
        $response->assertJsonPath('productInstalledRate.inactive_subscription', 1);
        $response->assertJsonPath('productInstalledRate.rate', 50);
    }

    public function test_it_calculates_paid_order_conversion_rate_last_30_days(): void
    {
        // 1. Paid recent
        Order::factory()->create([
            'price_override' => 100,
            'created_at' => now()->subDays(5),
        ]);

        // 2. Free recent
        Order::factory()->create([
            'price_override' => 0,
            'created_at' => now()->subDays(5),
        ]);

        // 3. Paid old
        Order::factory()->create([
            'price_override' => 100,
            'created_at' => now()->subDays(40),
        ]);

        $response = $this->getJson('dashboard');

        $response->assertJsonPath('paidOrderRate.all_orders', 2); // Only recent ones
        $response->assertJsonPath('paidOrderRate.paid_orders', 1);
        $response->assertJsonPath('paidOrderRate.rate', 50);
    }

    public function test_it_fetches_users_with_verified_mobile_and_email(): void
    {
        // Valid User
        User::factory()->create([
            'mobile_verified' => 1,
            'email_verified' => 1,
            'created_at' => now()->subDays(2),
        ]);

        // Invalid: Only email
        User::factory()->create([
            'mobile_verified' => 0,
            'email_verified' => 1,
            'created_at' => now()->subDays(2),
        ]);

        $response = $this->getJson('dashboard');

        $this->assertCount(1, $response->json('clientWithMobileAndEmailActivation'));
    }

    public function test_it_identifies_expiring_and_expired_orders(): void
    {
        // 1. Expiring in 5 days (Should appear in expiringOrders)
        $expiring = Subscription::factory()->create([
            'update_ends_at' => now()->addDays(5),
        ]);

        // 2. Expired 5 days ago (Should appear in expiredOrders)
        $expired = Subscription::factory()->create([
            'update_ends_at' => now()->subDays(5),
        ]);

        // 3. Safe (Expiring in 60 days) - Ignored
        Subscription::factory()->create([
            'update_ends_at' => now()->addDays(60),
        ]);

        $response = $this->actingAs($this->user)->getJson('dashboard');

        // Check Expiring
        $expiringList = $response->json('expiringOrders');
        $this->assertCount(1, $expiringList);
        $this->assertEquals($expiring->id, $expiringList[0]['id']);

        // Check Expired
        $expiredList = $response->json('expiredOrders');
        $this->assertCount(1, $expiredList);
        $this->assertEquals($expired->id, $expiredList[0]['id']);
    }

    public function test_it_identifies_clients_using_old_versions(): void
    {
        $product = Product::factory()->create();

        // 1. Create a previous version 1.0
        ProductUpload::insert([
            'product_id' => $product->id,
            'version' => '1.0',
        ]);

        // 2. Create a Product Upload entry for version 2.0 (Latest)
        ProductUpload::insert([
            'product_id' => $product->id,
            'version' => '2.0',
        ]);

        // 3. Create a User Subscription on Version 1.0 (Outdated)
        $outdatedSub = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'version' => '1.0',
        ]);
        // Ensure order exists and is paid for the query condition
        Order::find($outdatedSub->order_id)->update([
            'product' => $product->id,
            'client' => $this->user->id,
            'price_override' => 10,
        ]);

        // 4. Create a User Subscription on Version 2.0 (Current)
        $currentSub = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'version' => '2.0',
        ]);
        Order::find($currentSub->order_id)->update([
            'product' => $product->id,
            'client' => $this->user->id,
            'price_override' => 10,
        ]);

        $response = $this->getJson('dashboard');

        $outdatedClients = $response->json('clientWithOutdatedProducts');

        $this->assertCount(1, $outdatedClients);
        $this->assertEquals($outdatedSub->id, $outdatedClients[0]['id']);
    }

    public function test_it_lists_products_sold_in_last_30_days(): void
    {
        $product1 = Product::factory()->create(['name' => 'Popular Product']);
        $product2 = Product::factory()->create(['name' => 'Old Product']);

        // Create 2 orders for Product 1 recently
        Order::factory(2)->create([
            'product' => $product1->id,
            'order_status' => 'executed',
            'created_at' => now()->subDays(2),
        ]);

        // Create 1 order for Product 2 long ago
        Order::factory(1)->create([
            'product' => $product2->id,
            'order_status' => 'executed',
            'created_at' => now()->subDays(40),
        ]);

        $response = $this->actingAs($this->user)->getJson('dashboard');

        $soldProducts = $response->json('productSoldInLast30Days');

        // Should only contain Product 1
        $this->assertCount(1, $soldProducts);
        $this->assertEquals($product1->id, $soldProducts[0]['id']);
        $this->assertEquals(2, $soldProducts[0]['order_count']);
    }

    public function test_it_lists_recent_invoices_with_calculated_balances(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'grand_total' => 1000,
            'currency' => 'USD',
            'date' => now()->subDays(1),
            'status' => 'partial',
        ]);

        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 400,
        ]);

        $response = $this->getJson('dashboard');

        $response->assertJsonStructure([
            'recentInvoices' => [
                '*' => ['id', 'grand_total', 'paid_amount', 'balance', 'status'],
            ],
        ]);

        $recentInvoice = $response->json('recentInvoices')[0];
        $this->assertStringContainsString('400', (string) $recentInvoice['paid_amount']);
        // 1000 - 400 = 600 balance
        $this->assertStringContainsString('600', (string) $recentInvoice['balance']);
    }
}
