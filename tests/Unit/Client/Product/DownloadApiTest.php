<?php

namespace Tests\Unit\Client\Product;

use App\Http\Controllers\Product\ExtendedBaseProductController;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class DownloadApiTest extends DBTestCase
{
    use DatabaseTransactions;

    #[Group('product-download')]
    public function test_downloadValidation_whenValidParamasPassed_returnstrue(): void
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user_id = $this->user->id;
        $product = Product::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $user_id]);
        $order = Order::factory()->create(['client' => $user_id, 'invoice_id' => $invoice->id, 'product' => $product->id]);
        Subscription::factory()->create(['user_id' => $user_id, 'product_id' => $product->id, 'order_id' => $order->id]);
        $cont = new ExtendedBaseProductController();
        $response = $this->getPrivateMethod($cont, 'downloadValidation', ['true', $product->id, $invoice->number, false]);
        $this->assertEquals($response, actual: true);
    }

    #[Group('product-download')]
    public function test_downloadValidation_whenInValidProductIdPassed_returnsFalse(): void
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user_id = $this->user->id;
        $product = Product::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $user_id]);
        $order = Order::factory()->create(['client' => $user_id, 'invoice_id' => $invoice->id, 'product' => $product->id]);
        Subscription::factory()->create(['user_id' => $user_id, 'product_id' => $product->id, 'order_id' => $order->id]);
        $cont = new ExtendedBaseProductController();
        $response = $this->getPrivateMethod($cont, 'downloadValidation', ['true', '1223434', $invoice->number, false]);
        $this->assertEquals($response, actual: false);
    }

    #[Group('product-download')]
    public function test_downloadValidation_whenInValidInvoiceNoPassed_returnsFalse(): void
    {
        $this->expectException(Exception::class);
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user_id = $this->user->id;
        $product = Product::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $user_id]);
        $order = Order::factory()->create(['client' => $user_id, 'invoice_id' => $invoice->id, 'product' => $product->id]);
        Subscription::factory()->create(['user_id' => $user_id, 'order_id' => $order->id]);
        $cont = new ExtendedBaseProductController();
        $this->getPrivateMethod($cont, 'downloadValidation', ['true', $product->id, '2222', false]);
    }

    #[Group('product-download')]
    public function test_downloadValidation_whenNoOrdersAttachedToAnInvoice_returnsFalse(): void
    {
        $this->expectException(Exception::class);
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user_id = $this->user->id;
        $product = Product::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $user_id]);
        $cont = new ExtendedBaseProductController();
        $this->getPrivateMethod($cont, 'downloadValidation', ['true', $product->id, $invoice->number, false]);
    }

    #[Group('product-download')]
    public function test_downloadValidation_whenUserRoleIsAdmin_returnsTrue(): void
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
        $user_id = $this->user->id;
        $product = Product::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $user_id]);
        $cont = new ExtendedBaseProductController();
        $response = $this->getPrivateMethod($cont, 'downloadValidation', ['true', $product->id, $invoice->number, false]);
        $this->assertEquals($response, actual: true);
    }
}
