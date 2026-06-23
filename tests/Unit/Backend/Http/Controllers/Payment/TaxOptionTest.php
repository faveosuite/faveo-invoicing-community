<?php

namespace Tests\Unit\Backend\Http\Controllers\Payment;

use App\Model\Payment\TaxOption;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TaxOptionTest extends TestCase
{
    use DatabaseTransactions;

    #[Group('taxController')]
    public function test_options_when_gst_is_enable(): void
    {
        $this->withoutMiddleware();
        TaxOption::where('id', 1)->update(['tax_enable' => '1']);
        $response = $this->call('POST', 'taxes/option', [
            'Gst_no' => '2323244',
        ]);
        $response->assertSessionHas('success');
    }

    #[Group('taxController')]
    public function test_options_when_tax_class_is_created_when_tax_type_is_others(): void
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
    public function test_options_when_tax_class_is_created_when_tax_type_is_gst(): void
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
