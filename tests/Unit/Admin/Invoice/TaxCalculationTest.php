<?php

namespace Tests\Unit\Admin\Invoice;

use App\Http\Controllers\Order\InvoiceController;
use App\Model\Common\Setting;
use App\Model\License\LicenseType;
use App\Model\Payment\TaxClass;
use App\Model\Payment\TaxOption;
use App\Model\Payment\TaxProductRelation;
use App\Model\Product\Product;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class TaxCalculationTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classObject = new InvoiceController;
    }

    #[Group('tax')]
    public function test_calculate_tax_when_no_tax_is_applied_on_product(): void
    {
        $user = User::factory()->create();
        Setting::factory()->create(['state' => 'Tamilnadu']);
        $product = Product::factory()->create();
        new InvoiceController;
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'null');
        $this->assertEquals($tax['value'], '0%');
    }

    #[Group('tax')]
    public function test_calculate_tax_when_intra_state_gst_applied_on_product_when_gst_is_disabled_tax_value_is_null(): void
    {
        $user = User::factory()->create();
        Setting::factory()->create(['state' => 'Tamilnadu']);
        $this->withoutMiddleware();
        $this->call('POST', 'taxes/class', ['name' => 'Intra State GST', 'tax-name' => 'null', 'active' => 0]);
        $this->call('POST', 'license-type', ['name' => 'Download Perpetual']);
        $taxClass = TaxClass::first();
        $licenseType = LicenseType::first();
        $product = Product::factory()->create(['type' => $licenseType->id, 'product_sku' => 'test']);
        TaxProductRelation::create(['product_id' => $product->id, 'tax_class_id' => $taxClass->id]);
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'null');
        $this->assertEquals($tax['value'], '0%');
    }

    #[Group('tax')]
    public function test_calculate_tax_when_intra_state_gst_applied_on_product_tax_value_and_name_is_returned(): void
    {
        $user = User::factory()->create(['state' => 'KA', 'country' => 'IN']);
        Setting::factory()->create(['state' => 'KA', 'country' => 'IN']);
        $this->withoutMiddleware();
        $this->call('POST', 'taxes/class', ['name' => 'Intra State GST', 'tax-name' => 'CGST+SGST', 'active' => 1]);
        $this->call('POST', 'license-type', ['name' => 'Download Perpetual']);
        $taxClass = TaxClass::first();
        $licenseType = LicenseType::first();
        $product = Product::factory()->create(['type' => $licenseType->id, 'product_sku' => 'test']);
        TaxOption::where('id', 1)->update(['tax_enable' => 1]);
        TaxProductRelation::create(['product_id' => $product->id, 'tax_class_id' => $taxClass->id]);
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'CGST+SGST');
        $this->assertEquals($tax['value'], '18%');
    }

    #[Group('tax')]
    public function test_calculate_tax_when_inter_state_gst_applied_but_user_state_equals_origin_state_tax_value_is_null(): void
    {
        $user = User::factory()->create(['state' => 'IN-KA', 'country' => 'IN']);
        Setting::factory()->create(['state' => 'Tamilnadu']);
        $this->withoutMiddleware();
        $this->call('POST', 'taxes/class', ['name' => 'Inter State GST', 'tax-name' => 'IGST', 'active' => 1]);
        $this->call('POST', 'license-type', ['name' => 'Download Perpetual']);
        $taxClass = TaxClass::first();
        $licenseType = LicenseType::first();
        $product = Product::factory()->create(['type' => $licenseType->id, 'product_sku' => 'test']);
        TaxOption::where('id', 1)->update(['tax_enable' => 1]);
        TaxProductRelation::create(['product_id' => $product->id, 'tax_class_id' => $taxClass->id]);
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'null');
        $this->assertEquals($tax['value'], '0%');
    }

    #[Group('tax')]
    public function test_calculate_tax_when_inter_state_gst_applied_tax_value_and_name_is_returned(): void
    {
        $user = User::factory()->create(['state' => 'DL', 'country' => 'IN']);
        Setting::factory()->create(['state' => 'KA', 'country' => 'IN']);
        $taxClass = TaxClass::first();
        $this->withoutMiddleware();
        $this->call('POST', 'taxes/class', ['name' => 'Inter State GST', 'tax-name' => 'IGST', 'active' => 1]);
        $this->call('POST', 'license-type', ['name' => 'Download Perpetual']);
        $taxClass = TaxClass::first();
        $licenseType = LicenseType::first();
        $product = Product::factory()->create(['type' => $licenseType->id, 'product_sku' => 'test']);
        TaxOption::where('id', 1)->update(['tax_enable' => 1]);
        TaxProductRelation::create(['product_id' => $product->id, 'tax_class_id' => $taxClass->id]);
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'IGST');
        $this->assertEquals($tax['value'], '18%');
    }

    #[Group('tax')]
    public function test_calculate_tax_when_inter_state_gst_applied_when_status_is_inactive_tax_value_is_null(): void
    {
        $user = User::factory()->create(['state' => 'DL', 'country' => 'IN']);
        Setting::factory()->create(['state' => 'Tamilnadu']);
        $this->withoutMiddleware();
        $this->call('POST', 'taxes/class', ['name' => 'Inter State GST', 'tax-name' => 'IGST', 'active' => 0]);
        $this->call('POST', 'license-type', ['name' => 'Download Perpetual']);
        $taxClass = TaxClass::first();
        $licenseType = LicenseType::first();
        $product = Product::factory()->create(['type' => $licenseType->id, 'product_sku' => 'test']);
        TaxOption::where('id', 1)->update(['tax_enable' => 1]);
        TaxProductRelation::create(['product_id' => $product->id, 'tax_class_id' => $taxClass->id]);
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'null');
        $this->assertEquals($tax['value'], '0%');
    }

    #[Group('tax')]
    public function test_calculate_tax_when_union_territory_gst_applied_when_user_state_is_not_u_t_tax_value_is_null(): void
    {
        $user = User::factory()->create(['state' => 'DL', 'country' => 'IN']);
        Setting::factory()->create(['state' => 'Tamilnadu']);
        $this->withoutMiddleware();
        $this->call('POST', 'taxes/class', ['name' => 'Union Territory GST', 'tax-name' => 'CGST+UTGST', 'active' => 1]);
        $this->call('POST', 'license-type', ['name' => 'Download Perpetual']);
        $taxClass = TaxClass::first();
        $licenseType = LicenseType::first();
        $product = Product::factory()->create(['type' => $licenseType->id, 'product_sku' => 'test']);
        TaxOption::where('id', 1)->update(['tax_enable' => 1]);
        TaxProductRelation::create(['product_id' => $product->id, 'tax_class_id' => $taxClass->id]);
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'null');
        $this->assertEquals($tax['value'], '0%');
    }

    #[Group('tax')]
    public function test_calculate_tax_when_union_territory_gst_applied_tax_value_and_name_is_returned(): void
    {
        $user = User::factory()->create(['state' => 'AN', 'country' => 'IN']);
        Setting::factory()->create(['state' => 'KA', 'country' => 'IN']);
        $this->withoutMiddleware();
        $this->call('POST', 'taxes/class', ['name' => 'Union Territory GST', 'tax-name' => 'CGST+UTGST', 'active' => 1]);
        $this->call('POST', 'license-type', ['name' => 'Download Perpetual']);
        $taxClass = TaxClass::first();
        $licenseType = LicenseType::first();
        $product = Product::factory()->create(['type' => $licenseType->id, 'product_sku' => 'test']);
        TaxOption::where('id', 1)->update(['tax_enable' => 1]);
        TaxProductRelation::create(['product_id' => $product->id, 'tax_class_id' => $taxClass->id]);
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'CGST+UTGST');
        $this->assertEquals($tax['value'], '18%');
    }

    #[Group('tax')]
    public function test_calculate_tax_when_other_tax_applied_when_user_state_is_indian_tax_value_is_null(): void
    {
        $user = User::factory()->create(['state' => 'DL', 'country' => 'IN']);
        Setting::factory()->create(['state' => 'KA', 'country' => 'IN']);
        $this->withoutMiddleware();
        $this->call('POST', 'taxes/class', ['name' => 'Others', 'tax-name' => 'VAT', 'active' => 1, 'rate' => '20']);
        $this->call('POST', 'license-type', ['name' => 'Download Perpetual']);
        $taxClass = TaxClass::first();
        $licenseType = LicenseType::first();
        $product = Product::factory()->create(['type' => $licenseType->id, 'product_sku' => 'test']);
        TaxOption::where('id', 1)->update(['tax_enable' => 1]);
        TaxProductRelation::create(['product_id' => $product->id, 'tax_class_id' => $taxClass->id]);
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'null');
        $this->assertEquals($tax['value'], '0%');
    }

    #[Group('tax')]
    public function test_calculate_tax_when_other_tax_applied_tax_value_and_name_is_returned(): void
    {
        $user = User::factory()->create(['state' => 'NT', 'country' => 'AU']);
        Setting::factory()->create(['state' => 'Tamilnadu']);
        $this->withoutMiddleware();
        $this->call('POST', 'taxes/class', ['name' => 'Others', 'tax-name' => 'VAT', 'active' => 1, 'rate' => '20']);
        $this->call('POST', 'license-type', ['name' => 'Download Perpetual']);
        $taxClass = TaxClass::first();
        $licenseType = LicenseType::first();
        $product = Product::factory()->create(['type' => $licenseType->id, 'product_sku' => 'test']);
        TaxOption::where('id', 1)->update(['tax_enable' => 1]);
        TaxProductRelation::create(['product_id' => $product->id, 'tax_class_id' => $taxClass->id]);
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'VAT');
        $this->assertEquals($tax['value'], '20%');
    }

    #[Group('tax')]
    public function test_calculate_tax_when_other_tax_appliedwhen_tax_is_inactive_tax_value_is_null(): void
    {
        $user = User::factory()->create(['state' => 'NT', 'country' => 'AU']);
        Setting::factory()->create(['state' => 'Tamilnadu']);
        $this->withoutMiddleware();
        $this->call('POST', 'taxes/class', ['name' => 'Others', 'tax-name' => 'VAT', 'active' => 0, 'rate' => '20']);
        $this->call('POST', 'license-type', ['name' => 'Download Perpetual']);
        $taxClass = TaxClass::first();
        $licenseType = LicenseType::first();
        $product = Product::factory()->create(['type' => $licenseType->id, 'product_sku' => 'test']);
        TaxOption::where('id', 1)->update(['tax_enable' => 1]);
        TaxProductRelation::create(['product_id' => $product->id, 'tax_class_id' => $taxClass->id]);
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'null');
        $this->assertEquals($tax['value'], '0%');
    }

    #[Group('tax')]
    public function test_calculate_tax_when_other_tax_appliedwhen_when_user_is_from_other_state_tax_value_is_null(): void
    {
        $user = User::factory()->create(['state' => 'NT', 'country' => 'AU']);
        Setting::factory()->create(['state' => 'Tamilnadu']);
        $this->withoutMiddleware();
        $this->call('POST', 'taxes/class', ['name' => 'Others', 'tax-name' => 'VAT', 'active' => 0, 'rate' => '20', 'country' => 'AF', 'state' => 'AF-BDG']);
        $this->call('POST', 'license-type', ['name' => 'Download Perpetual']);
        $taxClass = TaxClass::first();
        $licenseType = LicenseType::first();
        $product = Product::factory()->create(['type' => $licenseType->id, 'product_sku' => 'test']);
        TaxOption::where('id', 1)->update(['tax_enable' => 1]);
        TaxProductRelation::create(['product_id' => $product->id, 'tax_class_id' => $taxClass->id]);
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'null');
        $this->assertEquals($tax['value'], '0%');
    }

    #[Group('tax')]
    public function test_calculate_tax_when_other_tax_appliedwhen_when_user_is_from_other_state_tax_value_and_name_is_returned(): void
    {
        $user = User::factory()->create(['state' => 'NT', 'country' => 'AU']);
        Setting::factory()->create(['state' => 'Tamilnadu']);
        $this->withoutMiddleware();
        $this->call('POST', 'taxes/class', ['name' => 'Others', 'tax-name' => 'VAT', 'active' => 1, 'rate' => '20', 'country' => 'AU', 'state' => 'NT']);
        $this->call('POST', 'license-type', ['name' => 'Download Perpetual']);
        $taxClass = TaxClass::first();
        $licenseType = LicenseType::first();
        $product = Product::factory()->create(['type' => $licenseType->id, 'product_sku' => 'test']);
        TaxOption::where('id', 1)->update(['tax_enable' => 1]);
        TaxProductRelation::create(['product_id' => $product->id, 'tax_class_id' => $taxClass->id]);
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'VAT');
        $this->assertEquals($tax['value'], '20%');
    }

    #[Group('tax')]
    public function test_calculate_tax_when_other_tax_applied_when_user_is_from_same_country_other_state_tax_value_is_null(): void
    {
        $user = User::factory()->create(['state' => 'NT', 'country' => 'AU']);
        Setting::factory()->create(['state' => 'Tamilnadu']);
        $this->withoutMiddleware();
        $this->call('POST', 'taxes/class', ['name' => 'Others', 'tax-name' => 'VAT', 'active' => 1, 'rate' => '20', 'country' => 'AU', 'state' => 'AU-NSW']);
        $this->call('POST', 'license-type', ['name' => 'Download Perpetual']);
        $taxClass = TaxClass::first();
        $licenseType = LicenseType::first();
        $product = Product::factory()->create(['type' => $licenseType->id, 'product_sku' => 'test']);
        TaxOption::where('id', 1)->update(['tax_enable' => 1]);
        TaxProductRelation::create(['product_id' => $product->id, 'tax_class_id' => $taxClass->id]);
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'null');
        $this->assertEquals($tax['value'], '0%');
    }

    #[Group('tax')]
    public function test_calculate_tax_when_other_tax_applied_for_all_statesof_users_country_tax_value_and_name_is_returned(): void
    {
        $user = User::factory()->create(['state' => 'NT', 'country' => 'AU']);
        Setting::factory()->create(['state' => 'Tamilnadu']);
        $this->withoutMiddleware();
        $this->call('POST', 'taxes/class', ['name' => 'Others', 'tax-name' => 'VAT', 'active' => 1, 'rate' => '20', 'country' => 'AU', 'state' => '']);
        $this->call('POST', 'license-type', ['name' => 'Download Perpetual']);
        $taxClass = TaxClass::first();
        $licenseType = LicenseType::first();
        $product = Product::factory()->create(['type' => $licenseType->id, 'product_sku' => 'test']);
        TaxOption::where('id', 1)->update(['tax_enable' => 1]);
        TaxProductRelation::create(['product_id' => $product->id, 'tax_class_id' => $taxClass->id]);
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'VAT');
        $this->assertEquals($tax['value'], '20%');
    }

    #[Group('tax')]
    public function test_calculate_tax_when_other_tax_applied_for_all_states_when_tax_inactive_tax_value_is_null(): void
    {
        $user = User::factory()->create(['state' => 'NT', 'country' => 'AU']);
        Setting::factory()->create(['state' => 'Tamilnadu']);
        $this->withoutMiddleware();
        $this->call('POST', 'taxes/class', ['name' => 'Others', 'tax-name' => 'VAT', 'active' => 0, 'rate' => '20', 'country' => 'AU', 'state' => '']);
        $this->call('POST', 'license-type', ['name' => 'Download Perpetual']);
        $taxClass = TaxClass::first();
        $licenseType = LicenseType::first();
        $product = Product::factory()->create(['type' => $licenseType->id, 'product_sku' => 'test']);
        TaxOption::where('id', 1)->update(['tax_enable' => 1]);
        TaxProductRelation::create(['product_id' => $product->id, 'tax_class_id' => $taxClass->id]);
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'null');
        $this->assertEquals($tax['value'], '0%');
    }

    #[Group('tax')]
    public function test_calculate_tax_when_tax_is_created_but_not_linked_to_a_product_tax_value_is_null(): void
    {
        Setting::factory()->create(['state' => 'Tamilnadu']);
        $user = User::factory()->create(['state' => 'NT', 'country' => 'AU']);
        $this->withoutMiddleware();
        $this->call('POST', 'taxes/class', ['name' => 'Others', 'tax-name' => 'VAT', 'active' => 1, 'rate' => '20', 'country' => 'AU', 'state' => '']);
        $this->call('POST', 'license-type', ['name' => 'Download Perpetual']);
        TaxClass::first();
        $licenseType = LicenseType::first();
        $product = Product::factory()->create(['type' => $licenseType->id, 'product_sku' => 'test']);
        TaxOption::where('id', 1)->update(['tax_enable' => 1]);
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'null');
        $this->assertEquals($tax['value'], '0%');
    }

    #[Group('tax')]
    public function test_calculate_tax_when_tax_is_applied_to_all_countries_all_states_tax_value_and_name_is_returned(): void
    {
        Setting::factory()->create(['state' => 'Tamilnadu']);
        $user = User::factory()->create(['state' => 'NT', 'country' => 'AU']);
        $this->withoutMiddleware();
        $this->call('POST', 'taxes/class', ['name' => 'Others', 'tax-name' => 'VAT', 'active' => 1, 'rate' => '20', 'country' => '', 'state' => '']);
        $this->call('POST', 'license-type', ['name' => 'Download Perpetual']);
        $taxClass = TaxClass::first();
        $licenseType = LicenseType::first();
        $product = Product::factory()->create(['type' => $licenseType->id, 'product_sku' => 'test']);
        TaxOption::where('id', 1)->update(['tax_enable' => 1]);
        TaxProductRelation::create(['product_id' => $product->id, 'tax_class_id' => $taxClass->id]);
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'VAT');
        $this->assertEquals($tax['value'], '20%');
    }

    #[Group('tax')]
    public function test_calculate_tax_when_tax_is_applied_to_all_countries_all_state_user_state_is_null_tax_value_and_name_is_returned(): void
    {
        $user = User::factory()->create(['state' => '', 'country' => 'AU']);
        Setting::factory()->create(['state' => 'Tamilnadu']);
        $this->withoutMiddleware();
        $this->call('POST', 'taxes/class', ['name' => 'Others', 'tax-name' => 'VAT', 'active' => 1, 'rate' => '20', 'country' => '', 'state' => '']);
        $this->call('POST', 'license-type', ['name' => 'Download Perpetual']);
        $taxClass = TaxClass::first();
        $licenseType = LicenseType::first();
        $product = Product::factory()->create(['type' => $licenseType->id, 'product_sku' => 'test']);
        TaxOption::where('id', 1)->update(['tax_enable' => 1]);
        TaxProductRelation::create(['product_id' => $product->id, 'tax_class_id' => $taxClass->id]);
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'VAT');
        $this->assertEquals($tax['value'], '20%');
    }

    #[Group('tax')]
    public function test_calculate_tax_when_tax_is_applied_to_all_countries_all_state_when_gst_disabled_tax_value_and_name_is_returned(): void
    {
        $user = User::factory()->create(['state' => 'KA', 'country' => 'IN']);
        Setting::factory()->create(['state' => 'Tamilnadu']);
        $this->withoutMiddleware();
        $this->call('POST', 'taxes/class', ['name' => 'Others', 'tax-name' => 'VAT', 'active' => 1, 'rate' => '20', 'country' => '', 'state' => '']);
        $this->call('POST', 'license-type', ['name' => 'Download Perpetual']);
        $taxClass = TaxClass::first();
        $licenseType = LicenseType::first();
        $product = Product::factory()->create(['type' => $licenseType->id, 'product_sku' => 'test']);
        TaxProductRelation::create(['product_id' => $product->id, 'tax_class_id' => $taxClass->id]);
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'VAT');
        $this->assertEquals($tax['value'], '20%');
    }

    #[Group('tax')]
    public function test_calculate_tax_when_other_tax_applied_user_is_indian_gst_disabled_tax_value_is_null(): void
    {
        $user = User::factory()->create(['state' => 'KA', 'country' => 'IN']);
        Setting::factory()->create(['state' => 'Tamilnadu']);
        $this->withoutMiddleware();
        $this->call('POST', 'taxes/class', ['name' => 'Others', 'tax-name' => 'VAT', 'active' => 1, 'rate' => '20', 'country' => 'AU', 'state' => '']);
        $this->call('POST', 'license-type', ['name' => 'Download Perpetual']);
        $taxClass = TaxClass::first();
        $licenseType = LicenseType::first();
        $product = Product::factory()->create(['type' => $licenseType->id, 'product_sku' => 'test']);
        TaxProductRelation::create(['product_id' => $product->id, 'tax_class_id' => $taxClass->id]);
        $tax = $this->classObject->calculateTax($product->id, $user->state, $user->country, true);
        $this->assertEquals($tax['name'], 'null');
        $this->assertEquals($tax['value'], '0%');
    }
}
