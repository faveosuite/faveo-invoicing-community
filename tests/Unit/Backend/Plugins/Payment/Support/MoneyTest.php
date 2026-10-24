<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Plugins\Payment\Support;

use App\Plugins\Payment\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_zero_decimal_currencies_including_isk(): void
    {
        $this->assertSame(0, Money::decimals('ISK'));
        $this->assertSame(0, Money::decimals('JPY'));
        $this->assertSame(0, Money::decimals('KRW'));

        // fromMinor: 1000 ISK / JPY should remain 1000.0 (not divided by 100)
        $this->assertSame(1000.0, Money::fromMinor(1000, 'ISK'));
        $this->assertSame(1000.0, Money::fromMinor(1000, 'JPY'));

        // toMinor: 1000 ISK -> 1000
        $this->assertSame(1000, Money::toMinor(1000.0, 'ISK'));
    }

    public function test_standard_two_decimal_currencies(): void
    {
        $this->assertSame(2, Money::decimals('USD'));
        $this->assertSame(2, Money::decimals('EUR'));
        $this->assertSame(2, Money::decimals('INR'));

        $this->assertSame(10.50, Money::fromMinor(1050, 'USD'));
        $this->assertSame(1050, Money::toMinor(10.50, 'USD'));
    }

    public function test_three_decimal_currencies(): void
    {
        $this->assertSame(3, Money::decimals('KWD'));
        $this->assertSame(3, Money::decimals('BHD'));
        $this->assertSame(3, Money::decimals('OMR'));

        $this->assertSame(1.250, Money::fromMinor(1250, 'KWD'));
        $this->assertSame(1250, Money::toMinor(1.25, 'KWD'));
    }
}
