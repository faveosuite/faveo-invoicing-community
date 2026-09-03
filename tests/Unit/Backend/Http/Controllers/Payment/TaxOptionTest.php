<?php

namespace Tests\Unit\Backend\Http\Controllers\Payment;

use App\Model\Payment\TaxOption;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
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
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    #[Group('taxController')]
    public function test_options_when_tax_class_is_created_when_tax_type_is_others(): void
    {
        $this->withoutMiddleware();
        $response = $this->call('POST', 'taxes/option', [
            'tax_enable' => '1',
            'inclusive' => '0',
            'tax_based_on' => 'billing',
            'rounding' => '2',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    #[Group('taxController')]
    public function test_options_when_tax_class_is_created_when_tax_type_is_gst(): void
    {
        $this->withoutMiddleware();
        $response = $this->call('POST', 'taxes/option', [
            'tax_enable' => '1',
            'Gst_no' => 'GST123456',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
