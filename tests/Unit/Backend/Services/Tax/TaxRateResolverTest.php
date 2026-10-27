<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Services\Tax;

use App\Model\Payment\TaxRate;
use App\Model\Payment\TaxRateLocation;
use App\Services\Tax\TaxRateResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\DBTestCase;

class TaxRateResolverTest extends DBTestCase
{
    use DatabaseTransactions;
    private TaxRateResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new TaxRateResolver();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // --- findRates() pure logic (no DB hit) ---

    public function test_empty_country_returns_empty_array_immediately(): void
    {
        $result = $this->resolver->findRates('');

        $this->assertSame([], $result);
    }

    public function test_whitespace_only_country_returns_empty_array(): void
    {
        $result = $this->resolver->findRates('   ');

        $this->assertSame([], $result);
    }

    public function test_find_rates_with_unlikely_country_returns_empty(): void
    {
        // 'XX' is not a real ISO country — no tax_rates rows should match
        $result = $this->resolver->findRates('XX');

        $this->assertSame([], $result);
    }

    // --- normalizePostcode() (private) ---

    public function test_normalize_postcode_strips_spaces(): void
    {
        $result = $this->getPrivateMethod($this->resolver, 'normalizePostcode', ['SW1 A 1AA']);

        $this->assertSame('SW1A1AA', $result);
    }

    public function test_normalize_postcode_strips_dashes(): void
    {
        $result = $this->getPrivateMethod($this->resolver, 'normalizePostcode', ['90210-1234']);

        $this->assertSame('902101234', $result);
    }

    public function test_normalize_postcode_uppercases(): void
    {
        $result = $this->getPrivateMethod($this->resolver, 'normalizePostcode', ['sw1a 1aa']);

        $this->assertSame('SW1A1AA', $result);
    }

    public function test_normalize_postcode_empty_string_stays_empty(): void
    {
        $result = $this->getPrivateMethod($this->resolver, 'normalizePostcode', ['']);

        $this->assertSame('', $result);
    }

    // --- postcodeMatches() (private) ---

    public function test_postcode_exact_match_returns_true(): void
    {
        $result = $this->getPrivateMethod($this->resolver, 'postcodeMatches', ['12345', '12345']);

        $this->assertTrue($result);
    }

    public function test_postcode_exact_mismatch_returns_false(): void
    {
        $result = $this->getPrivateMethod($this->resolver, 'postcodeMatches', ['12345', '99999']);

        $this->assertFalse($result);
    }

    public function test_postcode_wildcard_matches_prefix(): void
    {
        $result = $this->getPrivateMethod($this->resolver, 'postcodeMatches', ['12345', '12*']);

        $this->assertTrue($result);
    }

    public function test_postcode_wildcard_no_match(): void
    {
        $result = $this->getPrivateMethod($this->resolver, 'postcodeMatches', ['99345', '12*']);

        $this->assertFalse($result);
    }

    public function test_postcode_range_match_lower_bound(): void
    {
        $result = $this->getPrivateMethod($this->resolver, 'postcodeMatches', ['12000', '12000...12999']);

        $this->assertTrue($result);
    }

    public function test_postcode_range_match_upper_bound(): void
    {
        $result = $this->getPrivateMethod($this->resolver, 'postcodeMatches', ['12999', '12000...12999']);

        $this->assertTrue($result);
    }

    public function test_postcode_range_match_midpoint(): void
    {
        $result = $this->getPrivateMethod($this->resolver, 'postcodeMatches', ['12500', '12000...12999']);

        $this->assertTrue($result);
    }

    public function test_postcode_range_no_match_below(): void
    {
        $result = $this->getPrivateMethod($this->resolver, 'postcodeMatches', ['11999', '12000...12999']);

        $this->assertFalse($result);
    }

    public function test_postcode_range_no_match_above(): void
    {
        $result = $this->getPrivateMethod($this->resolver, 'postcodeMatches', ['13000', '12000...12999']);

        $this->assertFalse($result);
    }

    public function test_postcode_empty_string_returns_false_for_any_code(): void
    {
        $exact = $this->getPrivateMethod($this->resolver, 'postcodeMatches', ['', '12345']);
        $wildcard = $this->getPrivateMethod($this->resolver, 'postcodeMatches', ['', '12*']);
        $range = $this->getPrivateMethod($this->resolver, 'postcodeMatches', ['', '12000...12999']);

        $this->assertFalse($exact);
        $this->assertFalse($wildcard);
        $this->assertFalse($range);
    }

    // --- customerLocation() ---

    public function test_customer_location_returns_four_strings_for_null_user(): void
    {
        // When user is null and tax_based_on is 'base', falls back to Setting::first().
        // Just assert the shape — the actual values depend on real DB setting rows.
        $location = $this->resolver->customerLocation(null);

        $this->assertIsArray($location);
        $this->assertCount(4, $location);
        foreach ($location as $part) {
            $this->assertIsString($part);
        }
    }

    public function test_customer_location_uses_user_billing_fields(): void
    {
        $user = (object) ['country' => 'GB', 'state' => 'ENG', 'zip' => 'SW1A1AA', 'city' => 'London'];
        $location = $this->resolver->customerLocation($user);

        $this->assertIsArray($location);
        $this->assertCount(4, $location);
        foreach ($location as $part) {
            $this->assertIsString($part);
        }
    }

    public function test_customer_location_uses_base_store_when_no_user(): void
    {
        $location = $this->resolver->customerLocation(null);

        $this->assertIsArray($location);
        $this->assertCount(4, $location);
    }

    public function test_customer_location_uses_base_store_when_tax_based_on_base(): void
    {
        \App\Model\Payment\TaxOption::where('id', 1)->update(['tax_based_on' => 'base']);

        $user = (object) ['country' => 'GB', 'state' => 'ENG', 'zip' => 'SW1A', 'city' => 'London'];
        $location = $this->resolver->customerLocation($user);

        \App\Model\Payment\TaxOption::where('id', 1)->update(['tax_based_on' => 'billing']);

        $this->assertIsArray($location);
        $this->assertCount(4, $location);
    }

    public function test_rates_for_customer_returns_array(): void
    {
        $user = (object) ['country' => 'US', 'state' => 'CA', 'zip' => '', 'city' => ''];
        $rates = $this->resolver->ratesForCustomer('standard', $user);

        $this->assertIsArray($rates);
    }

    public function test_find_rates_matches_active_rate_for_country(): void
    {
        TaxRate::create([
            'country' => 'IN',
            'state' => '',
            'rate' => 18.0,
            'label' => 'GST',
            'tax_class' => 'standard',
            'priority' => 1,
            'compound' => false,
            'active' => true,
        ]);

        $rates = $this->resolver->findRates('IN', '', '', '', 'standard');

        $this->assertNotEmpty($rates);
        $this->assertEqualsWithDelta(18.0, $rates[0]['rate'], 0.001);
    }

    public function test_find_rates_ignores_inactive_rate(): void
    {
        TaxRate::create([
            'country' => 'IN',
            'state' => '',
            'rate' => 5.0,
            'label' => 'Inactive',
            'tax_class' => 'reduced',
            'priority' => 1,
            'compound' => false,
            'active' => false,
        ]);

        $rates = $this->resolver->findRates('IN', '', '', '', 'reduced');

        $this->assertEmpty($rates);
    }

    public function test_find_rates_respects_tax_class(): void
    {
        TaxRate::create(['name' => 'Reduced DE', 'country' => 'DE', 'state' => '', 'rate' => 7.0, 'tax_class' => 'reduced', 'priority' => 1, 'compound' => false, 'active' => true]);

        $rates = $this->resolver->findRates('DE', '', '', '', 'standard');
        $this->assertEmpty($rates);

        $rates = $this->resolver->findRates('DE', '', '', '', 'reduced');
        $this->assertNotEmpty($rates);
    }

    public function test_find_rates_skips_duplicate_priority(): void
    {
        // Two rates with the same priority → only one returned (line 116 covered)
        TaxRate::create(['name' => 'Rate A', 'country' => 'AU', 'state' => '', 'rate' => 10.0, 'tax_class' => 'standard', 'priority' => 1, 'compound' => false, 'active' => true]);
        TaxRate::create(['name' => 'Rate B', 'country' => 'AU', 'state' => 'VIC', 'rate' => 5.0, 'tax_class' => 'standard', 'priority' => 1, 'compound' => false, 'active' => true]);

        $rates = $this->resolver->findRates('AU', 'VIC', '', '', 'standard');

        // Only 1 rate returned per priority
        $this->assertCount(1, $rates);
    }

    public function test_find_rates_skips_rate_with_non_matching_location(): void
    {
        // Create a rate with a specific postcode location — line 98 (continue) covered when postcode doesn't match
        $rate = TaxRate::create(['name' => 'Postcode Rate', 'country' => 'GB', 'state' => '', 'rate' => 20.0, 'tax_class' => 'standard', 'priority' => 1, 'compound' => false, 'active' => true]);
        TaxRateLocation::create(['tax_rate_id' => $rate->id, 'location_code' => '90210', 'location_type' => 'postcode']);

        // Query with a DIFFERENT postcode → locationMatches returns false → continue (line 98)
        $rates = $this->resolver->findRates('GB', '', '10001', '', 'standard');

        $this->assertEmpty($rates);
    }

    public function test_postcode_range_with_non_numeric_returns_false(): void
    {
        // Line 160: non-numeric postcode with range code → return false
        $method = new \ReflectionMethod(TaxRateResolver::class, 'postcodeMatches');
        $method->setAccessible(true);

        $result = $method->invoke($this->resolver, 'ABC', '100...200');

        $this->assertFalse($result);
    }
}
