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
        $permissionid = [
            0 => '1',
            1 => '2',
            2 => '3',
            3 => '4',
            6 => '6',
        ];
        $licensetype->permissions()->attach($permissionid);

        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct', 'type' => $licensetype->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        PlanPrice::factory()->create(['plan_id' => $plan->id]);
        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '']);
        $response = $this->call('get', 'get-my-orders', ['updated_ends_at' => '']);
        $response->json();

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['data' => [
                    '*' => [
                        'id',
                        'date',
                        'number',
                        'agents',
                        'expiry',
                        'action',
                    ],
                ],
                ],
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
            'tax_name' => 'CGST+SGST',
            'tax_percentage' => 18,
            'subtotal' => 11800,
            'domain' => 'faveo.com',
            'plan_id' => 1,
        ]);
        $plan = Plan::factory()->create(['product' => $product->id]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'USD']);
        $order = Order::factory()->create(['invoice_id' => $invoice->id,
            'invoice_item_id' => $invoiceItem->id, 'client' => $user->id, 'product' => $product->id]);
        Subscription::create(['user_id' => $user->id, 'order_id' => $order->id, 'product_id' => $product->id, 'version' => 'v3.0.0', 'is_subscribed' => '1', 'autoRenew_status' => '1']);
        ApiKey::create(['rzp_key' => 'test_key', 'rzp_secret' => 'test_secret', 'stripe_key' => 'test_stripe']);
        Setting::create(['id' => 1, 'autorenewal_status' => 1]);
        $mock = Mockery::mock(InstallationService::class);
        $mock->shouldReceive('getInstallationsByProduct')
            ->withAnyArgs()
            ->once()
            ->andReturn(['installed_path' => ['/mocked'], 'installed_ip' => [], 'installation_date' => [], 'installation_status' => []]);

        $this->app->instance(InstallationService::class, $mock);
        $response = $this->call('get', 'my-order/'.$order->id);
        $response->assertStatus(200);
    }

    #[Group('order')]
    public function test_auto_renewal(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $date = '2025-03-02 18:15:02';
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name, 'product_id' => $product->id]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => 'Helpdesk Advance', 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'USD']);
        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id, 'version' => 'v6.0.0', 'update_ends_at' => $date]);

        $response = $this->call('post', 'client/renew/'.$subscription->id, ['plan' => $plan->id, 'user' => $user->id]);
        $response->assertRedirect();
        $response->assertSessionHas('plan_id');
    }

    #[Group('order')]
    public function test_to_pay_now_exception(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $user1 = User::factory()->create();
        $date = '2025-03-02 18:15:02';
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $invoice = Invoice::factory()->create(['user_id' => $user1->id]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name, 'product_id' => $product->id]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        PlanPrice::factory()->create(['plan_id' => $plan->id]);
        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => $date]);
        $response = $this->call('get', 'paynow/'.$invoice->id);
        $content = $response->json();
        $this->assertEquals('Cannot initiate payment. Invalid modification of data', $content['message']);
    }

    #[Group('order')]
    public function test_to_pay_now_redirection(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $date = '2025-03-02 18:15:02';
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name, 'product_id' => $product->id, 'quantity' => 1, 'regular_price' => 1000,
            'tax_name' => 'GST', 'tax_percentage' => '10', 'subtotal' => 300, ]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        PlanPrice::factory()->create(['plan_id' => $plan->id]);
        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => $date]);
        $response = $this->call('get', 'paynow/'.$invoice->id);
        $response->assertStatus(200);

        $content = $response->json()['data'];
        $this->assertArrayHasKey('paid', $content);
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
        $permissionid = [
            0 => '1',
            1 => '2',
            2 => '3',
            3 => '4',
            6 => '6',
        ];
        $licensetype->permissions()->attach($permissionid);

        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct', 'type' => $licensetype->id]);
        Product::find($product->id)->licenseType->permissions->pluck('permissions'); // Get All the permissions related to patrticular Product
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        OrderInvoiceRelation::create(['order_id' => $order->id, 'invoice_id' => $invoice->id]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        PlanPrice::factory()->create(['plan_id' => $plan->id]);
        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => $date]);
        ProductUpload::create(['product_id' => $product->id, 'version' => 'v6.0.0', 'title' => $product->name, 'description' => $product->description, 'release_type' => 'official', 'is_private' => 0]);
        $response = $this->call('get', 'get-versions/'.$product->id.'/'.$order->client.'/'.$invoice->number.'/');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'version',
                            'title',
                            'description',
                            'file',
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
        $permissionid = [
            0 => '1',
            1 => '2',
            2 => '3',
            3 => '4',
            6 => '6',
        ];
        $licensetype->permissions()->attach($permissionid);

        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct', 'type' => $licensetype->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'USD']);
        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '']);
        $response = $this->call('get', 'get-my-orders', ['updated_ends_at' => '']);
        $content = $response->json()['data'];

        $response->assertStatus(200);
        $this->assertEquals($content['data'][0]['id'], $order->id);
        $this->assertEquals($content['data'][0]['product_name'], $product->name);
        $this->assertEquals($content['data'][0]['number'], $order->number);
    }
}
