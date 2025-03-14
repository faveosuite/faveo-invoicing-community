<?php

namespace Tests\Unit\Client\Product;

use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
}
