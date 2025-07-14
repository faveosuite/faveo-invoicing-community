<?php

namespace Tests\Unit\Admin\Setting\Tax;

use App\Model\Payment\TaxOption;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TaxOptionTest extends TestCase
{
    use DatabaseTransactions;

    #[Group('taxController')]
    public function test_options_whenGstIsEnable()
    {
        $this->withoutMiddleware();
        $rule = TaxOption::where('id', 1)->update(['tax_enable' => '1']);
        $response = $this->call('POST', 'taxes/option', [
            'Gst_no' => '2323244',
        ]);
        $response->assertSessionHas('success');
    }

    #[Group('taxController')]
    public function test_options_whenTaxClassIsCreated_whenTaxTypeIsOthers()
    {
        $this->withoutMiddleware();
        $response = $this->call('POST', 'taxes/option', [
            'name' => 'Others',
            'tax-name' => 'VAT',
            'active' => 1,
            'country' => 'AU',
            'state' => 'QLD',
            'rate' => '20',
        ]);
        $response->assertSessionHas('success');
    }

    #[Group('taxController')]
    public function test_options_whenTaxClassIsCreated_whenTaxTypeIsGst()
    {
        $this->withoutMiddleware();
        $response = $this->call('POST', 'taxes/option', [
            'name' => 'Inter State GST',
            'tax-name' => 'CGST',
            'active' => 1,
            'country' => 'IN',
            'state' => 'IN-MH',
            'rate' => '20',
        ]);
        $response->assertSessionHas('success');
    }
}
