<?php

namespace Tests\Unit\Backend\Models\Payment;

use App\Model\Payment\Tax;
use App\Model\Payment\TaxClass;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class TaxModelTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getLoggedInUser('admin');
    }

    public function test_tax_model_can_be_instantiated(): void
    {
        $tax = new Tax;
        $this->assertInstanceOf(Tax::class, $tax);
    }

    public function test_tax_class_relation_returns_belongs_to(): void
    {
        $tax = new Tax;
        $relation = $tax->taxClass();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    }

    public function test_tax_query_returns_collection(): void
    {
        $taxes = Tax::query()->limit(5)->get();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $taxes);
    }

    public function test_tax_can_be_created_and_retrieved(): void
    {
        $taxClass = TaxClass::first();
        if (! $taxClass) {
            $taxClass = \App\Model\Payment\TaxClass::create(['name' => 'Test Tax Class '.uniqid()]);
        }

        $tax = Tax::create([
            'name' => 'Test Tax '.uniqid(),
            'rate' => '10.00',
            'country' => 'US',
            'active' => 1,
            'tax_classes_id' => $taxClass->id,
            'level' => 1,
            'priority' => 1,
            'compound' => 0,
            'apply_to_shipping' => 0,
        ]);

        $this->assertDatabaseHas('taxes', ['name' => $tax->name]);
        $this->assertEquals('10.00', $tax->rate);
    }

    public function test_get_mappings_returns_array(): void
    {
        $tax = new Tax;
        $reflection = new \ReflectionClass($tax);
        $method = $reflection->getMethod('getMappings');
        $method->setAccessible(true);

        $mappings = $method->invoke($tax);
        $this->assertIsArray($mappings);
    }
}
