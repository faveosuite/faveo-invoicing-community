<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Services\Tax;

use App\Model\Payment\TaxClass;
use App\Model\Payment\TaxOption;
use App\Model\Payment\TaxProductRelation;
use App\Services\Tax\TaxEngine;
use App\Services\Tax\TaxRateResolver;
use App\Services\Tax\TaxService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Mockery\MockInterface;
use Tests\DBTestCase;

class TaxServiceTest extends DBTestCase
{
    use DatabaseTransactions;
    /** @var TaxRateResolver&MockInterface */
    private TaxRateResolver $resolver;

    private TaxEngine $engine;

    private TaxService $service;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var TaxRateResolver&MockInterface $resolver */
        $resolver = Mockery::mock(TaxRateResolver::class);
        $this->resolver = $resolver;
        $this->engine = new TaxEngine();
        $this->service = new TaxService($this->resolver, $this->engine);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // --- calculate() — reads TaxOption(1) from real DB ---

    public function test_calculate_returns_empty_when_tax_globally_disabled(): void
    {
        // TaxOption::find(1) is read from the real DB inside calculate().
        // When tax_enable = 0 OR no option row exists, the result must be empty.
        // We test only the shape: if the real DB has tax disabled this passes directly.
        // If enabled, we still test resolver is NOT called for a tax-exempt user.
        $exemptUser = (object) ['is_tax_exempt' => true, 'country' => 'US'];

        // Resolver must NOT be called when user is exempt (or tax disabled globally).
        $this->resolver->shouldReceive('ratesForCustomer')->never()->byDefault();

        $result = $this->service->calculate(100.0, 1, $exemptUser);

        // Either tax is disabled globally OR user is exempt — both yield applicable=false.
        $this->assertFalse($result['applicable']);
        $this->assertEqualsWithDelta(0.0, $result['total'], 0.001);
        $this->assertSame([], $result['lines']);
    }

    public function test_calculate_returns_empty_for_non_taxable_product(): void
    {
        // Product ID 0 will never exist in tax_product_relations → taxClassFor returns null.
        // This assumes tax IS enabled in the real DB (tax_enable=1). If it isn't, the
        // method still returns empty — the assertion holds either way.
        $user = (object) ['is_tax_exempt' => false, 'country' => 'US'];

        // resolver may or may not be called depending on whether tax is enabled globally.
        $this->resolver->shouldReceive('ratesForCustomer')->andReturn([])->byDefault();

        $result = $this->service->calculate(100.0, 0, $user);

        $this->assertFalse($result['applicable']);
    }

    public function test_calculate_returns_empty_when_resolver_returns_no_rates(): void
    {
        // Product 0 → taxClassFor returns null → returns empty before resolver is even called.
        // For a product WITH a tax class but no matching rates, resolver returns [].
        // We cannot create a tax_product_relation row without knowing a valid tax_class_id,
        // so we test this path via a product that has no relation.
        $user = (object) ['is_tax_exempt' => false, 'country' => 'XX'];

        $this->resolver->shouldReceive('ratesForCustomer')->andReturn([])->byDefault();

        $result = $this->service->calculate(100.0, 0, $user);

        $this->assertFalse($result['applicable']);
        $this->assertSame([], $result['rates']);
    }

    public function test_calculate_structure_keys_always_present(): void
    {
        $user = (object) ['is_tax_exempt' => false, 'country' => 'US'];

        $this->resolver->shouldReceive('ratesForCustomer')->andReturn([])->byDefault();

        $result = $this->service->calculate(100.0, 0, $user);

        $requiredKeys = ['applicable', 'prices_include_tax', 'rates', 'total', 'percent', 'name', 'lines'];
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $result, "Missing key: $key");
        }
    }

    // --- taxClassFor() — reads from DB (tax_product_relations) ---

    public function test_tax_class_for_returns_null_when_product_has_no_relation(): void
    {
        // Product ID 0 should have no tax_product_relation row in any real DB.
        $result = $this->service->taxClassFor(0);

        $this->assertNull($result);
    }

    public function test_tax_class_for_returns_null_for_very_large_product_id(): void
    {
        $result = $this->service->taxClassFor(PHP_INT_MAX);

        $this->assertNull($result);
    }

    // --- legacyCondition() ---

    public function test_legacy_condition_returns_null_sentinel_when_not_applicable(): void
    {
        // Product 0 has no tax class → calculate returns applicable=false →
        // legacyCondition returns the 'null' sentinel array.
        $user = (object) ['is_tax_exempt' => false];

        $this->resolver->shouldReceive('ratesForCustomer')->andReturn([])->byDefault();

        $result = $this->service->legacyCondition(0, $user);

        $this->assertSame('null', $result['name']);
        $this->assertSame('0%', $result['value']);
        $this->assertArrayHasKey('type', $result);
    }

    public function test_legacy_condition_from_admin_panel_omits_type_key(): void
    {
        $user = (object) ['is_tax_exempt' => false];

        $this->resolver->shouldReceive('ratesForCustomer')->andReturn([])->byDefault();

        $result = $this->service->legacyCondition(0, $user, fromAdminPanel: true);

        $this->assertSame('null', $result['name']);
        $this->assertArrayNotHasKey('type', $result);
    }

    public function test_legacy_condition_null_user_does_not_crash(): void
    {
        $this->resolver->shouldReceive('ratesForCustomer')->andReturn([])->byDefault();

        $result = $this->service->legacyCondition(0, null);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('value', $result);
    }

    public function test_calculate_returns_empty_for_tax_exempt_user(): void
    {
        TaxOption::where('id', 1)->update(['tax_enable' => 1]);

        $user = (object) ['is_tax_exempt' => true];
        $result = $this->service->calculate(100.0, 1, $user);

        TaxOption::where('id', 1)->update(['tax_enable' => 0]);

        $this->assertFalse($result['applicable']);
    }

    public function test_calculate_returns_full_breakdown_when_rates_apply(): void
    {
        TaxOption::where('id', 1)->update(['tax_enable' => 1, 'inclusive' => 0]);

        // Create a tax class and product relation
        $taxClass = TaxClass::create(['name' => 'Test Standard', 'slug' => 'test-standard-'.uniqid()]);
        $productRelation = TaxProductRelation::create(['product_id' => 9999, 'tax_class_id' => $taxClass->id]);

        $rates = [
            ['id' => 99, 'rate' => 10.0, 'label' => 'VAT', 'compound' => false, 'priority' => 1],
        ];
        $this->resolver->shouldReceive('ratesForCustomer')->andReturn($rates);

        $user = (object) ['is_tax_exempt' => false];
        $result = $this->service->calculate(100.0, 9999, $user);

        TaxOption::where('id', 1)->update(['tax_enable' => 0]);

        $this->assertTrue($result['applicable']);
        $this->assertEqualsWithDelta(10.0, $result['total'], 0.01);
        $this->assertSame('VAT', $result['name']);
        $this->assertCount(1, $result['lines']);
    }

    public function test_legacy_condition_returns_applicable_result(): void
    {
        TaxOption::where('id', 1)->update(['tax_enable' => 1, 'inclusive' => 0]);

        $taxClass = TaxClass::create(['name' => 'Legacy Class', 'slug' => 'legacy-'.uniqid()]);
        TaxProductRelation::create(['product_id' => 8888, 'tax_class_id' => $taxClass->id]);

        $this->resolver->shouldReceive('ratesForCustomer')->andReturn([
            ['id' => 88, 'rate' => 18.0, 'label' => 'GST', 'compound' => false, 'priority' => 1],
        ]);

        $user = (object) ['is_tax_exempt' => false];
        $result = $this->service->legacyCondition(8888, $user);

        TaxOption::where('id', 1)->update(['tax_enable' => 0]);

        $this->assertSame('GST', $result['name']);
        $this->assertStringContainsString('%', $result['value']);
        $this->assertArrayHasKey('type', $result);
    }

    public function test_tax_class_for_returns_slug_when_relation_exists(): void
    {
        $taxClass = TaxClass::create(['name' => 'Slug Test', 'slug' => 'slug-test-'.uniqid()]);
        TaxProductRelation::create(['product_id' => 7777, 'tax_class_id' => $taxClass->id]);

        $slug = $this->service->taxClassFor(7777);

        $this->assertSame($taxClass->slug, $slug);
    }

    public function test_calculate_returns_empty_when_no_product_tax_relation(): void
    {
        // Tax enabled but product 6666 has no tax relation → taxClassFor returns null → line 62
        TaxOption::where('id', 1)->update(['tax_enable' => 1]);

        $user = (object) ['is_tax_exempt' => false];
        $result = $this->service->calculate(100.0, 6666, $user);

        TaxOption::where('id', 1)->update(['tax_enable' => 0]);

        $this->assertFalse($result['applicable']);
    }

    public function test_calculate_returns_empty_when_resolver_returns_no_rates_with_enabled_tax(): void
    {
        TaxOption::where('id', 1)->update(['tax_enable' => 1]);

        $taxClass = TaxClass::create(['name' => 'No Rates', 'slug' => 'no-rates-'.uniqid()]);
        TaxProductRelation::create(['product_id' => 5555, 'tax_class_id' => $taxClass->id]);

        // Resolver returns empty → line 67
        $this->resolver->shouldReceive('ratesForCustomer')->andReturn([]);

        $user = (object) ['is_tax_exempt' => false];
        $result = $this->service->calculate(100.0, 5555, $user);

        TaxOption::where('id', 1)->update(['tax_enable' => 0]);

        $this->assertFalse($result['applicable']);
    }

    public function test_legacy_condition_admin_panel_with_applicable_result(): void
    {
        TaxOption::where('id', 1)->update(['tax_enable' => 1, 'inclusive' => 0]);

        $taxClass = TaxClass::create(['name' => 'Admin Class', 'slug' => 'admin-'.uniqid()]);
        TaxProductRelation::create(['product_id' => 4444, 'tax_class_id' => $taxClass->id]);

        $this->resolver->shouldReceive('ratesForCustomer')->andReturn([
            ['id' => 44, 'rate' => 10.0, 'label' => 'Tax', 'compound' => false, 'priority' => 1],
        ]);

        // fromAdminPanel: true → line 120 covered
        $result = $this->service->legacyCondition(4444, (object) ['is_tax_exempt' => false], fromAdminPanel: true);

        TaxOption::where('id', 1)->update(['tax_enable' => 0]);

        $this->assertSame('Tax', $result['name']);
        $this->assertArrayNotHasKey('type', $result);
    }
}
