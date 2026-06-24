<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Models;

use App\Model\Payment\Period;
use App\Model\Payment\Tax;
use App\Model\Payment\TaxClass;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\DBTestCase;

class PaymentModelsTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    // =========================================================================
    // Tax
    // =========================================================================

    public function test_tax_can_be_created_and_persisted(): void
    {
        $tax = Tax::create([
            'tax_classes_id' => 1,
            'level' => 1,
            'active' => 1,
            'name' => 'Test Tax',
            'country' => 'US',
            'state' => '',
            'rate' => '10',
            'compound' => 0,
            'c_gst' => '',
            's_gst' => '',
            'i_gst' => '',
            'ut_gst' => '',
        ]);

        $this->assertNotNull($tax->id);
        $this->assertDatabaseHas('taxes', ['id' => $tax->id, 'name' => 'Test Tax']);
    }

    public function test_tax_tax_class_relation_returns_belongs_to(): void
    {
        $tax = Tax::create([
            'tax_classes_id' => 1,
            'level' => 1,
            'active' => 1,
            'name' => 'Relation Tax',
            'country' => 'US',
            'state' => '',
            'rate' => '5',
            'compound' => 0,
            'c_gst' => '',
            's_gst' => '',
            'i_gst' => '',
            'ut_gst' => '',
        ]);

        $relation = $tax->taxClass();

        $this->assertInstanceOf(BelongsTo::class, $relation);
    }

    // =========================================================================
    // TaxClass
    // =========================================================================

    public function test_tax_class_can_be_created_and_persisted(): void
    {
        $taxClass = TaxClass::create([
            'name' => 'Test Tax Class',
            'slug' => 'test-tax-class',
        ]);

        $this->assertNotNull($taxClass->id);
        $this->assertDatabaseHas('tax_classes', ['id' => $taxClass->id, 'name' => 'Test Tax Class']);
    }

    public function test_tax_class_rates_relation_returns_has_many(): void
    {
        $taxClass = TaxClass::create([
            'name' => 'Rates TaxClass',
            'slug' => 'rates-taxclass',
        ]);

        $relation = $taxClass->rates();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    public function test_tax_class_tax_relation_returns_has_many(): void
    {
        $taxClass = TaxClass::create([
            'name' => 'Tax Rel TaxClass',
            'slug' => 'tax-rel-taxclass',
        ]);

        $relation = $taxClass->tax();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    public function test_tax_class_tax_product_relation_returns_has_many(): void
    {
        $taxClass = TaxClass::create([
            'name' => 'Prod Rel TaxClass',
            'slug' => 'prod-rel-taxclass',
        ]);

        $relation = $taxClass->tax_product_relation();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    // =========================================================================
    // Period
    // =========================================================================

    public function test_period_can_be_created_and_persisted(): void
    {
        $period = Period::create([
            'name' => 'Monthly',
            'days' => '30',
        ]);

        $this->assertNotNull($period->id);
        $this->assertDatabaseHas('periods', ['id' => $period->id, 'name' => 'Monthly']);
    }

    public function test_period_first_record_is_retrievable(): void
    {
        // Ensure at least one period exists; if seeded data is present, use it.
        $existing = Period::first();
        if ($existing === null) {
            $existing = Period::create(['name' => 'Annual', 'days' => '365']);
        }

        $found = Period::find($existing->id);

        $this->assertNotNull($found);
        $this->assertSame($existing->id, $found->id);
    }
}
