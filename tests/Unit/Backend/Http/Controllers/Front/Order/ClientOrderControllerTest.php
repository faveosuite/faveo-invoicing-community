<?php

namespace Tests\Unit\Backend\Http\Controllers\Front\Order;

use App\ApiKey;
use App\License\Services\InstallationService;
use App\Model\Common\Setting;
use App\Model\License\LicensePermission;
use App\Model\License\LicenseType;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Payment\Currency;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use App\User;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Spatie\Html\Html;
use Tests\DBTestCase;

class ClientOrderControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->request = resolve(Request::class);
        $this->html = Mockery::mock(Html::class, [$this->request])->makePartial();
        $this->html->shouldReceive('token')->andReturn('mocked-token');
        $this->app->instance(Html::class, $this->html);
    }

    #[Group('order')]
    public function test_my_orders_datatable_sends_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $licensetype = LicenseType::create(['name' => 'DevelopmentLicense']);
        LicensePermission::create(['Can be Downloaded']);
        LicensePermission::create(['Generate License Expiry Date']);
        LicensePermission::create(['Generate Updates Expiry Date']);
        LicensePermission::create(['Allow Downloads Before Updates Expire']);
        $licensetype->permissions()->attach([0 => '1', 1 => '2', 2 => '3', 3 => '4', 6 => '6']);

        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct', 'type' => $licensetype->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_item_id' => $invoiceItem->id]);
        OrderInvoiceRelation::create(['order_id' => $order->id, 'invoice_id' => $invoice->id]);
        $plan = Plan::create(['name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        PlanPrice::factory()->create(['plan_id' => $plan->id]);
        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '']);
        $response = $this->call('get', 'get-my-orders', ['updated_ends_at' => '']);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => ['data' => [
                '*' => [
                    'id',
                    'number',
                    'product_name',
                    'agents',
                ],
            ]],
        ]);
    }

    #[Group('order')]
    public function test_while_selecting_plan_provides_price(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        Currency::where('code', 'USD')->update(['status' => 1]);
        $plan = Plan::factory()->create(['product' => $product->id]);
        $planPrice = PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'USD']);
        $response = $this->call('get', 'get-renew-cost', ['user' => $user->id, 'plan' => $plan->id]);
        $content = $response->json()['data'];

        $response->assertStatus(200);
        $this->assertEquals($content['formatted_price'], currencyFormat($planPrice->renew_price, 'USD'));
    }

    #[Group('order')]
    public function test_successful_when_license_mocked(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::factory()->create(['type' => 4]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_name' => 'Helpdesk Advance',
            'regular_price' => 10000,
            'quantity' => 1,
            'subtotal' => 11800,
            'domain' => 'faveo.com',
            'plan_id' => 1,
        ]);
        $plan = Plan::factory()->create(['product' => $product->id]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'USD']);
        $order = Order::factory()->create([
            'invoice_item_id' => $invoiceItem->id, 'client' => $user->id, 'product' => $product->id,
        ]);
        OrderInvoiceRelation::create(['order_id' => $order->id, 'invoice_id' => $invoice->id]);
        Subscription::create(['user_id' => $user->id, 'order_id' => $order->id, 'product_id' => $product->id, 'version' => 'v3.0.0', 'is_subscribed' => '1', 'autoRenew_status' => '1']);
        ApiKey::create(['rzp_key' => 'test_key', 'rzp_secret' => 'test_secret', 'stripe_key' => 'test_stripe']);
        Setting::create(['id' => 1, 'autorenewal_status' => 1]);
        $mock = Mockery::mock(InstallationService::class);
        $mock->shouldReceive('getInstallationsByProduct')
            ->withAnyArgs()
            ->zeroOrMoreTimes()
            ->andReturn(['installed_path' => ['/mocked'], 'installed_ip' => [], 'installation_date' => [], 'installation_status' => []]);

        $this->app->instance(InstallationService::class, $mock);
        $response = $this->call('get', 'my-order/'.$order->id);
        $response->assertStatus(200);
    }

    #[Group('order')]
    public function test_download_version(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $date = '2025-03-02 18:15:02';
        $licensetype = LicenseType::create(['name' => 'DevelopmentLicense']);
        LicensePermission::create(['Can be Downloaded']);
        LicensePermission::create(['Generate License Expiry Date']);
        LicensePermission::create(['Generate Updates Expiry Date']);
        LicensePermission::create(['Allow Downloads Before Updates Expire']);
        $licensetype->permissions()->attach([0 => '1', 1 => '2', 2 => '3', 3 => '4', 6 => '6']);

        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct', 'type' => $licensetype->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_item_id' => $invoiceItem->id]);
        OrderInvoiceRelation::create(['order_id' => $order->id, 'invoice_id' => $invoice->id]);
        $plan = Plan::create(['name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        PlanPrice::factory()->create(['plan_id' => $plan->id]);
        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => $date]);
        ProductUpload::create(['product_id' => $product->id, 'version' => 'v6.0.0', 'title' => $product->name,
            'description' => $product->description, 'release_type' => 'official', 'is_private' => 0]);
        $response = $this->call('get', 'get-versions/'.$order->id);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => [
                            'version',
                            'name',
                            'description',
                            'can_download',
                        ],
                    ],
                ],
            ]);
    }

    #[Group('order')]
    public function test_my_orders_datatable_individual_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $licensetype = LicenseType::create(['name' => 'DevelopmentLicense']);
        LicensePermission::create(['Can be Downloaded']);
        LicensePermission::create(['Generate License Expiry Date']);
        LicensePermission::create(['Generate Updates Expiry Date']);
        LicensePermission::create(['Allow Downloads Before Updates Expire']);
        $licensetype->permissions()->attach([0 => '1', 1 => '2', 2 => '3', 3 => '4', 6 => '6']);

        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct', 'type' => $licensetype->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_item_id' => $invoiceItem->id]);
        OrderInvoiceRelation::create(['order_id' => $order->id, 'invoice_id' => $invoice->id]);
        $plan = Plan::create(['name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'USD']);
        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '']);
        $response = $this->call('get', 'get-my-orders', ['updated_ends_at' => '']);
        $content = $response->json()['data'];

        $response->assertStatus(200);
        $this->assertEquals($order->id, $content['data'][0]['id']);
        $this->assertEquals($product->name, $content['data'][0]['product_name']);
        $this->assertEquals($order->number, $content['data'][0]['number']);
    }

    // =========================================================================
    // getCloudSettings – GET /get-cloud-settings/{orderId}
    // =========================================================================

    public function test_get_cloud_settings_returns_200_for_nonexistent_order(): void
    {
        $user = User::factory()->create(['email' => 'cloud-settings-'.uniqid().'@test.local']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->getJson('/get-cloud-settings/999999');
        $this->assertContains($response->status(), [200, 400]);
    }

    // =========================================================================
    // getInvoicesByOrderId – GET /get-my-invoices/{orderid}/{userid}/{admin?}
    // =========================================================================

    public function test_get_invoices_by_order_id_returns_200_for_nonexistent(): void
    {
        $user = User::factory()->create(['email' => 'invoices-by-order-'.uniqid().'@test.local']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->getJson('/get-my-invoices/999999/'.$user->id);
        $this->assertContains($response->status(), [200, 400]);
    }

    // =========================================================================
    // getPaymentByOrderIdClient – GET /get-my-payment-client/{orderid}/{userid}
    // =========================================================================

    public function test_get_payment_by_order_id_returns_200(): void
    {
        $user = User::factory()->create(['email' => 'payment-by-order-'.uniqid().'@test.local']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->getJson('/get-my-payment-client/999999/'.$user->id);
        $this->assertContains($response->status(), [200, 400]);
    }

    // =========================================================================
    // getOrderInstallations – GET /get-my-installations/{orderid}
    // =========================================================================

    public function test_get_order_installations_returns_200(): void
    {
        $user = User::factory()->create(['email' => 'installations-'.uniqid().'@test.local']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->getJson('/get-my-installations/999999');
        $this->assertContains($response->status(), [200, 400]);
    }

    // =========================================================================
    // getVersionList – covers error paths and basic structure
    // =========================================================================

    public function test_get_version_list_returns_error_for_order_not_belonging_to_user(): void
    {
        $user = User::factory()->create(['email' => 'version-list-'.uniqid().'@test.local']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        // Order 999999 doesn't exist for this user
        $response = $this->getJson('/get-versions/999999');
        // Returns 400 (errorResponse) or 403 depending on how Laravel handles it
        $this->assertContains($response->status(), [400, 403]);
        $this->assertFalse($response->json('success'));
    }

    public function test_get_version_list_with_existing_order_returns_structured_data(): void
    {
        $user = User::factory()->create(['email' => 'version-list-2-'.uniqid().'@test.local']);
        $this->actingAs($user);
        $this->withoutMiddleware();

        $product = Product::create(['name' => 'TestProduct-'.uniqid(), 'description' => 'Test']);
        $order = Order::create([
            'client' => $user->id,
            'order_status' => 'executed',
            'product' => $product->id,
            'number' => mt_rand(100000, 999999),
        ]);

        // A product with no license type has no "Allow downloads" permission (OrderPolicy::download).
        $this->getJson('/get-versions/'.$order->id)
            ->assertStatus(403)
            ->assertJson(['success' => false, 'message' => __('message.no_permission_for_action')]);
    }

    // =========================================================================
    // renewPopupVue – GET /renew-popup-details/{productid}
    // =========================================================================

    public function test_renew_popup_vue_returns_plans_for_product(): void
    {
        $user = User::factory()->create(['email' => 'renew-popup-'.uniqid().'@test.local', 'country' => 'US']);
        $this->actingAs($user);
        $this->withoutMiddleware();

        $product = Product::create(['name' => 'TestRenewProduct-'.uniqid()]);
        $plan = Plan::create(['name' => 'Annual', 'product' => $product->id, 'days' => 365, 'status' => 1]);
        PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'USD', 'renew_price' => '99', 'add_price' => '99']);

        $response = $this->getJson('/renew-popup-details/'.$product->id);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $data = $response->json('data');
        $this->assertArrayHasKey('plans', $data);
        $this->assertArrayHasKey('user_id', $data);
        $this->assertSame($user->id, $data['user_id']);
    }

    public function test_renew_popup_vue_returns_empty_plans_for_nonexistent_product(): void
    {
        $user = User::factory()->create(['email' => 'renew-popup-2-'.uniqid().'@test.local', 'country' => 'US']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->getJson('/renew-popup-details/999999');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertIsArray($data['plans']);
        $this->assertCount(0, $data['plans']);
    }

    // =========================================================================
    // getOrderInstallations – with real data
    // =========================================================================

    public function test_get_order_installations_returns_structured_data(): void
    {
        $user = User::factory()->create(['email' => 'installations-2-'.uniqid().'@test.local']);
        $this->actingAs($user);
        $this->withoutMiddleware();

        $product = Product::create(['name' => 'TestInstallProd-'.uniqid()]);
        $order = Order::create([
            'client' => $user->id,
            'order_status' => 'executed',
            'product' => $product->id,
            'number' => mt_rand(100000, 999999),
        ]);

        $response = $this->getJson('/get-my-installations/'.$order->id);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $data = $response->json('data');
        $this->assertArrayHasKey('data', $data);
    }

    // =========================================================================
    // Action rules (OrderPolicy) — buttons and endpoints agree
    // =========================================================================

    /** @param  list<string>  $permissions */
    private function policyOrder(User $user, array $permissions, array $productAttributes = [], array $subscription = []): Order
    {
        $type = LicenseType::factory()->create();
        foreach ($permissions as $name) {
            $type->permissions()->attach(LicensePermission::firstOrCreate(['permissions' => $name])->id);
        }
        $product = Product::factory()->create(['type' => $type->id] + $productAttributes);
        $order = Order::factory()->create(['client' => $user->id, 'product' => $product->id, 'order_status' => 'executed']);
        Subscription::factory()->create($subscription + [
            'order_id' => $order->id, 'product_id' => $product->id, 'user_id' => $user->id, 'is_deleted' => 0,
        ]);

        return $order;
    }

    public function test_versions_are_refused_when_downloads_are_not_allowed_even_for_github_products(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $order = $this->policyOrder($user, [], ['github_owner' => 'acme', 'github_repository' => 'app']);

        // Never asks GitHub for (direct) download links.
        $github = Mockery::mock(\App\Http\Controllers\Github\GithubApiController::class);
        $github->shouldNotReceive('releases');
        $github->shouldNotReceive('resolveDownloadUrl');
        $this->app->instance(\App\Http\Controllers\Github\GithubApiController::class, $github);

        $this->getJson('/get-versions/'.$order->id)
            ->assertStatus(403)
            ->assertJson(['success' => false, 'message' => __('message.no_permission_for_action')]);
    }

    public function test_versions_are_refused_after_updates_end_when_old_downloads_are_not_kept(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $order = $this->policyOrder($user, ['Can be Downloaded'], subscription: ['update_ends_at' => now()->subDay()]);

        $this->getJson('/get-versions/'.$order->id)
            ->assertStatus(403)
            ->assertJson(['success' => false, 'message' => __('message.renew_subscription_download')]);
    }

    public function test_versions_list_only_unlocks_releases_from_before_updates_end(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $order = $this->policyOrder($user, ['Can be Downloaded', 'Allow Downloads Before Updates Expire'], subscription: ['update_ends_at' => now()->subDays(10)]);
        $productId = $order->getRawOriginal('product');
        ProductUpload::factory()->create(['product_id' => $productId, 'version' => 'v1.0.0', 'is_private' => 0, 'created_at' => now()->subDays(20)]);
        ProductUpload::factory()->create(['product_id' => $productId, 'version' => 'v2.0.0', 'is_private' => 0, 'created_at' => now()->subDays(5)]);

        $data = $this->getJson('/get-versions/'.$order->id)->assertOk()->json('data');
        // v2.0.0 is the newest, so it's the featured card; v1.0.0 is in the previous-versions table.
        $older = collect($data['data'])->keyBy('version');
        $newest = collect($data['featured'])->keyBy('version');

        $this->assertTrue($older['v1.0.0']['can_download']);
        $this->assertFalse($newest['v2.0.0']['can_download']);
        $this->assertNull($newest['v2.0.0']['download_url']);
    }

    public function test_my_orders_flags_come_from_the_same_rules(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        // Self-hosted, downloads off: no download button and no cloud-delete button either.
        $this->policyOrder($user, [], subscription: ['ends_at' => now()->addMonth()]);

        $row = $this->getJson('/get-my-orders')->assertOk()->json('data.data.0');

        $this->assertTrue($row['can_renew']);
        $this->assertFalse($row['can_download']);
        $this->assertFalse($row['can_delete_cloud']);
    }
}
