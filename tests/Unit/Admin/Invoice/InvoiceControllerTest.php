<?php

namespace Tests\Unit\Admin\Invoice;

use App\Http\Controllers\Order\InvoiceController;
use App\Http\Requests\InvoiceRequest;
use App\Model\Common\Setting;
use App\Model\Payment\Currency;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class InvoiceControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classObject = new InvoiceController();
    }

    #[Group('invoice')]
    public function test_generateInvoice_generatesInvoiceAndInvoiceItem()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $setting = Setting::factory()->create(['default_currency' => 'INR']);
        $taxCondition = new \Darryldecode\Cart\CartCondition([
            'name' => 'GST', 'type' => 'tax',
            'value' => 5,
        ]);
        \Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => 1000,
            'quantity' => 1,
            'attributes' => [],
            'conditions' => $taxCondition,
        ]);
        $invoice = $this->classObject->generateInvoice();
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }

    #[Group('invoice')]
    public function test_createInvoiceItems_createsNewInvoiceItem()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $setting = Setting::factory()->create(['default_currency' => 'INR']);
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $taxCondition = new \Darryldecode\Cart\CartCondition([
            'name' => 'GST', 'type' => 'tax',
            'value' => 5,
        ]);
        \Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => 1000,
            'quantity' => 1,
            'attributes' => [],
            'conditions' => $taxCondition,
        ]);
        $invoice = $this->classObject->generateInvoice();
        foreach (\Cart::getContent() as $cart) {
            $invoiceItem = $this->classObject->createInvoiceItems($invoice->id, $cart);
            $this->assertDatabaseHas('invoice_items', ['invoice_id' => $invoice->id]);
        }
    }

    #[Group('invoice')]
    public function test_invoiceGenerateByForm_createsNewInvoice()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        Currency::where('code', $this->user->currency)->update(['status' => 1]);
        $setting = Setting::factory()->create(['state' => 'IN-KA']);
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => $this->user->currency, 'add_price' => '1000', 'renew_price' => '500', 'product_quantity' => 1, 'no_of_agents' => 0]);

        $invoice = $this->classObject->invoiceGenerateByForm(new InvoiceRequest(['user' => $this->user->id, 'date' => '09/16/2020', 'product' => $product->id, 'price' => $planPrice->add_price, 'code' => '', 'quantity' => $planPrice->product_quantity, 'plan' => $plan->id, 'subscription' => true, 'description' => '']));
        $message = json_decode($invoice->getContent())->message->success;
        $this->assertEquals($message, 'Invoice generated successfully');
    }
}
