<?php

namespace Tests\Unit\Admin\Invoice;

use App\Http\Controllers\Order\BaseInvoiceController;
use App\Model\Payment\TaxOption;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class BaseInvoiceControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classObject = new BaseInvoiceController;
    }

    #[Group('baseinvoicecontroller')]
    public function test_calculate_total_calculate_total_after_applying_rate_when_inclusive_of_tax_returns_price_after_adding_tax(): void
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $price = $this->classObject->calculateTotal('10%', '1000');
        $this->assertEquals($price, '1100');
    }

    #[Group('baseinvoicecontroller')]
    public function test_calculate_total_calculate_total_after_applying_rate_when_exclusive_of_tax_returns_price_without_tax(): void
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $tax_rule = new TaxOption;
        $tax_rule->findOrFail(1)->update(['inclusive' => 1]);
        $price = $this->classObject->calculateTotal('10%', '1000');
        $this->assertEquals($price, '1000');
    }
}
