<?php

namespace Tests\Unit\Client\Product;

use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\DBTestCase;

class DownloadApiTest extends DBTestCase
{
    use DatabaseTransactions;

    #[Group('product-download')]
    public function test_downloadValidation_whenValidParamasPassed_returnstrue()
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user_id = $this->user->id;
        $product = Product::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $user_id]);
        $order = Order::factory()->create(['client' => $user_id, 'invoice_id' => $invoice->id, 'product' => $product->id]);
        $subscription = Subscription::factory()->create(['user_id' => $user_id, 'product_id' => $product->id, 'order_id' => $order->id]);
        $cont = new \App\Http\Controllers\Product\ExtendedBaseProductController();
        $response = $this->getPrivateMethod($cont, 'downloadValidation', ['true', $product->id, $invoice->number, false]);
        $this->assertEquals($response, true);
    }

    #[Group('product-download')]
    public function test_downloadValidation_whenInValidProductIdPassed_returnsFalse()
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user_id = $this->user->id;
        $product = Product::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $user_id]);
        $order = Order::factory()->create(['client' => $user_id, 'invoice_id' => $invoice->id, 'product' => $product->id]);
        $subscription = Subscription::factory()->create(['user_id' => $user_id, 'product_id' => $product->id, 'order_id' => $order->id]);
        $cont = new \App\Http\Controllers\Product\ExtendedBaseProductController();
        $response = $this->getPrivateMethod($cont, 'downloadValidation', ['true', '1223434', $invoice->number, false]);
        $this->assertEquals($response, false);
    }

    #[Group('product-download')]
    public function test_downloadValidation_whenInValidInvoiceNoPassed_returnsFalse()
    {
        $this->expectException(\Exception::class);
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user_id = $this->user->id;
        $product = Product::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $user_id]);
        $order = Order::factory()->create(['client' => $user_id, 'invoice_id' => $invoice->id, 'product' => $product->id]);
        $subscription = Subscription::factory()->create(['user_id' => $user_id, 'order_id' => $order->id]);
        $cont = new \App\Http\Controllers\Product\ExtendedBaseProductController();
        $response = $this->getPrivateMethod($cont, 'downloadValidation', ['true', $product->id, '2222', false]);
    }

    #[Group('product-download')]
    public function test_downloadValidation_whenNoOrdersAttachedToAnInvoice_returnsFalse()
    {
        $this->expectException(\Exception::class);
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user_id = $this->user->id;
        $product = Product::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $user_id]);
        $cont = new \App\Http\Controllers\Product\ExtendedBaseProductController();
        $response = $this->getPrivateMethod($cont, 'downloadValidation', ['true', $product->id, $invoice->number, false]);
    }

    #[Group('product-download')]
    public function test_downloadValidation_whenUserRoleIsAdmin_returnsTrue()
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
        $user_id = $this->user->id;
        $product = Product::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $user_id]);
        $cont = new \App\Http\Controllers\Product\ExtendedBaseProductController();
        $response = $this->getPrivateMethod($cont, 'downloadValidation', ['true', $product->id, $invoice->number, false]);
        $this->assertEquals($response, true);
    }

    /** @group order  */
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
}
