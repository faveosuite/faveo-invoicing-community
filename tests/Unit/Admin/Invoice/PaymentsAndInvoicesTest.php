<?php

namespace Tests\Unit\Admin\Invoice;

use App\Http\Controllers\Order\InvoiceController;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class PaymentsAndInvoicesTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classObject = new InvoiceController();
    }

    #[Group('paymentandinvoice')]
    public function test_getAgents_whenAgentsIsPassed_returnsNoOfAgents()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => $this->user->currency, 'add_price' => '1000', 'renew_price' => '500', 'product_quantity' => 1, 'no_of_agents' => 5]);
        $agents = $this->classObject->getAgents(5, $product->id, $plan->id);
        $this->assertEquals($agents, 5);
    }

    #[Group('paymentandinvoice')]
    public function test_getAgents_whenAgentsIsPassedIsNull_returnsNoOfAgents()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => $this->user->currency, 'add_price' => '1000', 'renew_price' => '500', 'product_quantity' => 1, 'no_of_agents' => 5]);
        $agents = $this->classObject->getAgents('', $product->id, $plan->id);
        $this->assertEquals($agents, 5);
    }

    #[Group('paymentandinvoice')]
    public function test_getAgents_whenAgentsIsPassedIsNullWhenPlanDoesNotExistForProduct_returnsNoOfAgents()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $agents = $this->classObject->getAgents('', $product->id, '');
        $this->assertEquals($agents, 0);
    }

    #[Group('paymentandinvoice')]
    public function test_getQuantity_whenQuantityIsPassed_returnsProductQuantity()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => $this->user->currency, 'add_price' => '1000', 'renew_price' => '500', 'product_quantity' => 1, 'no_of_agents' => 5]);
        $qty = $this->classObject->getQuantity(1, $product->id, $plan->id);
        $this->assertEquals($qty, 1);
    }

    #[Group('paymentandinvoice')]
    public function test_getAgents_whenQtyIsPassedIsNull_returnsProductQuantity()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => $this->user->currency, 'add_price' => '1000', 'renew_price' => '500', 'product_quantity' => 2, 'no_of_agents' => 5]);
        $qty = $this->classObject->getQuantity('', $product->id, $plan->id);
        $this->assertEquals($qty, 2);
    }

    #[Group('paymentandinvoice')]
    public function test_getAgents_whenQtyIsPassedIsNullWhenPlanDoesNotExistForProduct_returnsProductQuantity()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $qty = $this->classObject->getQuantity('', $product->id, '');
        $this->assertEquals($qty, 1);
    }
}
