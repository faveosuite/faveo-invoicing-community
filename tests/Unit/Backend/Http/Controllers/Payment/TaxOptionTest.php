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

    public function test_activity_log_mappings_format_each_option(): void
    {
        $mappings = (new \ReflectionMethod(TaxOption::class, 'getMappings'))->invoke(new TaxOption);
        $format = fn (string $key, mixed $value) => [$mappings[$key][0], $mappings[$key][1]($value)];

        $this->assertSame(['Tax Enable', __('message.active')], $format('tax_enable', 1));
        $this->assertSame(['Tax Enable', __('message.inactive')], $format('tax_enable', '1'));
        $this->assertSame(['Prices Entered With Tax', 'Yes'], $format('inclusive', 1));
        $this->assertSame(['Prices Entered With Tax', 'No'], $format('inclusive', 0));
        $this->assertSame(['Calculate Tax Based On', 'Company address'], $format('tax_based_on', 'base'));
        $this->assertSame(['Calculate Tax Based On', 'Billing address'], $format('tax_based_on', 'billing'));
        $this->assertSame(['Shop Prices Entered With Tax', 'Yes'], $format('shop_inclusive', 1));
        $this->assertSame(['Shop Prices Entered With Tax', 'No'], $format('shop_inclusive', null));
        $this->assertSame(['Cart Prices Entered With Tax', 'Yes'], $format('cart_inclusive', 1));
        $this->assertSame(['Cart Prices Entered With Tax', 'No'], $format('cart_inclusive', 2));
        $this->assertSame(['Round Tax To Whole Number', __('message.enable')], $format('rounding', 1));
        $this->assertSame(['Round Tax To Whole Number', __('message.disable')], $format('rounding', 0));
        $this->assertSame(['GST Number', 'GST123'], $format('Gst_no', 'GST123'));
        $this->assertSame(['GST Number', 'N/A'], $format('Gst_no', ''));
        $this->assertSame(['CIF Number', 'CIF9'], $format('cif_no', 'CIF9'));
        $this->assertSame(['CIF Number', 'N/A'], $format('cif_no', null));
        $this->assertSame(['tax_enable', 'inclusive', 'tax_based_on', 'shop_inclusive', 'cart_inclusive', 'rounding', 'Gst_no', 'cif_no'], array_keys($mappings));
    }
}
