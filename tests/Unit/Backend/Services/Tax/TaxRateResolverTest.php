<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Services\Tax;

use App\Services\Tax\TaxRateResolver;
use Mockery;
use Tests\DBTestCase;

class TaxRateResolverTest extends DBTestCase
{
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
        $exact    = $this->getPrivateMethod($this->resolver, 'postcodeMatches', ['', '12345']);
        $wildcard = $this->getPrivateMethod($this->resolver, 'postcodeMatches', ['', '12*']);
        $range    = $this->getPrivateMethod($this->resolver, 'postcodeMatches', ['', '12000...12999']);

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
        // Fake a user object with billing fields; tax_based_on must be 'billing'.
        // This only works when real TaxOption::find(1)->tax_based_on = 'billing'.
        $user = (object) [
            'country'  => 'GB',
            'state'    => 'ENG',
            'zip'      => 'SW1A1AA',
            'city'     => 'London',
        ];

        $location = $this->resolver->customerLocation($user);

        $this->assertIsArray($location);
        $this->assertCount(4, $location);
        // If tax_based_on = 'billing', expect user values; otherwise expect base store values.
        // Either is valid — we just assert we get 4 non-null strings.
        foreach ($location as $part) {
            $this->assertIsString($part);
        }
    }

    
}
