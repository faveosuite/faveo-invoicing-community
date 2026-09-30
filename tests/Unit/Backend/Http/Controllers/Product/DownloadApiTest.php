<?php

namespace Tests\Unit\Backend\Http\Controllers\Product;

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
    public function test_download_validation_when_in_valid_invoice_no_passed_returns_false(): void
    {
        $this->expectException(Exception::class);
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user_id = $this->user->id;
        $product = Product::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $user_id]);
        $order = Order::factory()->create(['client' => $user_id, 'product' => $product->id]);
        \App\Model\Order\OrderInvoiceRelation::create(['order_id' => $order->id, 'invoice_id' => $invoice->id]);
        Subscription::factory()->create(['user_id' => $user_id, 'order_id' => $order->id]);
        $cont = new ExtendedBaseProductController($this->app->make(\App\License\Services\ProductBundleStampingService::class));
        $this->getPrivateMethod($cont, 'downloadValidation', ['true', $product->id, '2222', false]);
    }

    #[Group('product-download')]
    public function test_download_validation_when_no_orders_attached_to_an_invoice_returns_false(): void
    {
        $this->expectException(Exception::class);
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user_id = $this->user->id;
        $product = Product::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $user_id]);
        $cont = new ExtendedBaseProductController($this->app->make(\App\License\Services\ProductBundleStampingService::class));
        $this->getPrivateMethod($cont, 'downloadValidation', ['true', $product->id, $invoice->number, false]);
    }
}
