<?php

namespace Tests\Unit\Client\Order;

use App\Model\License\LicensePermission;
use App\Model\License\LicenseType;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use App\User;
use Illuminate\Http\Request;
use Mockery;
use Spatie\Html\Html;
use Tests\DBTestCase;

class ClientOrderControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->request = app(Request::class);
        $this->html = Mockery::mock(Html::class, [$this->request])->makePartial();
        $this->html->shouldReceive('token')->andReturn('mocked-token');
        $this->app->instance(Html::class, $this->html);
    }

    #[\PHPUnit\Framework\Attributes\Group('order')]
    public function test_my_orders_datatable_sends_data()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $licensetype = LicenseType::create(['name' => 'DevelopmentLicense']);
        $licensepermissiontype = LicensePermission::create(['Can be Downloaded']);
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
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $planPrice = PlanPrice::factory()->create(['plan_id' => $plan->id]);
        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '']);
        $response = $this->call('get', 'get-my-orders', ['updated_ends_at' => '']);
        $content = $response->json();
        $response->assertStatus(200)
            ->assertJsonStructure([
                'draw',
                'recordsTotal',
                'recordsFiltered',
                'data' => [
                    '*' => [
                        'product_name',
                        'date',
                        'number',
                        'agents',
                        'expiry',
                        'Action',
                    ],
                ],
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Group('order')]
    public function test_while_selecting_plan_provides_price()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $plan = Plan::factory()->create(['product' => $product->id]);
        $planPrice = PlanPrice::factory()->create(['plan_id' => $plan->id]);
        $response = $this->call('get', 'get-renew-cost', ['user' => $user->id, 'plan' => $plan->id]);
        $content = json_decode($response->getContent());
        $response->assertStatus(200);
        $this->assertEquals($content[0], $planPrice->renew_price);
        $this->assertEquals($content[1], $product->can_modify_agent);
    }

    #[\PHPUnit\Framework\Attributes\Group('order')]
    public function test_fails_due_to_invalid_license_key()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::factory()->create();
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
        $order = Order::factory()->create(['invoice_id' => $invoice->id,
            'invoice_item_id' => $invoiceItem->id, 'client' => $user->id, 'product' => $product->id]);
        $subscription = Subscription::create(['user_id' => $user->id, 'order_id' => $order->id, 'product_id' => $product->id, 'version' => 'v3.0.0', 'is_subscribed' => '1', 'autoRenew_status' => '1']);

        $response = $this->call('get', 'my-order/'.$order->id);

        $response->assertSessionHas('fails', 'Please configure the valid license details in Apikey settings.');
    }

    #[\PHPUnit\Framework\Attributes\Group('order')]
    public function test_successful_when_license_mocked()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::factory()->create();
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
        $order = Order::factory()->create(['invoice_id' => $invoice->id,
            'invoice_item_id' => $invoiceItem->id, 'client' => $user->id, 'product' => $product->id]);
        $subscription = Subscription::create(['user_id' => $user->id, 'order_id' => $order->id, 'product_id' => $product->id, 'version' => 'v3.0.0', 'is_subscribed' => '1', 'autoRenew_status' => '1']);
        $serialKey = 'eertrertyuhgbvfdrgtyujhnbvfdrethgbf';
        $productId = 1;
        $mock = Mockery::mock(\App\Http\Controllers\License\LicenseController::class);
        $mock->shouldReceive('searchInstallationPath')
            ->withAnyArgs()
            ->once()
            ->andReturn(['path' => '/mocked']);

        $this->app->instance(\App\Http\Controllers\License\LicenseController::class, $mock);
        $response = $this->call('get', 'my-order/'.$order->id);
        $response->assertStatus(200);
        $response->assertViewIs('themes.default1.front.clients.show-order');
    }

    #[\PHPUnit\Framework\Attributes\Group('order')]
    public function test_auto_renewal()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $date = '2025-03-02 18:15:02';
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name, 'product_id' => $product->id]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => 'Helpdesk Advance', 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $planPrice = PlanPrice::factory()->create(['plan_id' => $plan->id]);
        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id, 'version' => 'v6.0.0', 'update_ends_at' => $date]);

        $response = $this->call('post', 'client/renew/'.$subscription->id, ['plan' => $plan->id, 'user' => $user->id]);
        $response->assertRedirect();
        $response->assertSessionHas('plan_id');
    }

    #[\PHPUnit\Framework\Attributes\Group('order')]
    public function test_to_payNow_exception()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $user1 = User::factory()->create();
        $date = '2025-03-02 18:15:02';
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $invoice = Invoice::factory()->create(['user_id' => $user1->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => 'Helpdesk Advance', 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $planPrice = PlanPrice::factory()->create(['plan_id' => $plan->id]);
        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => $date]);
        $response = $this->call('get', 'paynow/'.$invoice->id);
        $response->assertRedirect();
        $response->assertSessionHas('fails', 'Cannot initiate payment. Invalid modification of data');
    }

    #[\PHPUnit\Framework\Attributes\Group('order')]
    public function test_to_payNow_redirection()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $date = '2025-03-02 18:15:02';
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name, 'regular_price' => 1000,
            'tax_name' => 'GST', 'tax_percentage' => '10', 'subtotal' => 300, ]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => 'Helpdesk Advance', 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $planPrice = PlanPrice::factory()->create(['plan_id' => $plan->id]);
        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => $date]);
        $response = $this->call('get', 'paynow/'.$invoice->id);
        $response->assertStatus(200);
        $response->assertViewIs('themes.default1.front.paynow');
    }

    #[\PHPUnit\Framework\Attributes\Group('order')]
    public function test_download_version()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $date = '2025-03-02 18:15:02';
        $licensetype = LicenseType::create(['name' => 'DevelopmentLicense']);
        $licensepermissiontype = LicensePermission::create(['Can be Downloaded']);
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
        $permissions = Product::find($product->id)->licenseType->permissions->pluck('permissions'); //Get All the permissions related to patrticular Product
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => 'Helpdesk Advance', 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $planPrice = PlanPrice::factory()->create(['plan_id' => $plan->id]);
        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => $date]);
        $productupload = ProductUpload::create(['product_id' => $product->id, 'version' => 'v6.0.0', 'title' => $product->name, 'description' => $product->description, 'release_type' => 'official', 'is_private' => 0]);
        $response = $this->call('get', 'get-versions/'.$product->id.'/'.$order->client.'/'.$invoice->number.'/');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'version',
                        'title',
                        'description',
                        'file',
                    ],
                ],
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Group('order')]
    public function test_my_orders_datatable_individual_data()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $licensetype = LicenseType::create(['name' => 'DevelopmentLicense']);
        $licensepermissiontype = LicensePermission::create(['Can be Downloaded']);
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
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $planPrice = PlanPrice::factory()->create(['plan_id' => $plan->id]);
        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '']);
        $response = $this->call('get', 'get-my-orders', ['updated_ends_at' => '']);
        $content = $response->json();
        $response->assertStatus(200);
        $this->assertEquals($content['data'][0]['product_id'], $product->id);
        $this->assertEquals($content['data'][0]['product_name'], $product->name);
        $this->assertEquals($content['data'][0]['invoice_id'], $invoice->id);
        $this->assertEquals($content['data'][0]['invoice_number'], $invoice->number);
        $this->assertEquals($content['data'][0]['version'], $subscription->version);
        $this->assertEquals($content['data'][0]['client'], $user->id);
    }
}
