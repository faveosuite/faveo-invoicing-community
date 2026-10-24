<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Plugins\Payment\Gateways;

use App\Plugins\Payment\Gateways\StripeGateway;
use PHPUnit\Framework\TestCase;

class StripeGatewayTest extends TestCase
{
    private function gateway(): StripeGateway
    {
        return new StripeGateway('sk_test_fake123');
    }

    /**
     * @return array{interval: string, interval_count: int}
     */
    private function recurringInterval(int $intervalDays): array
    {
        $method = new \ReflectionMethod(StripeGateway::class, 'recurringInterval');

        return $method->invoke($this->gateway(), $intervalDays);
    }

    public function test_short_interval_uses_days_or_months_under_365(): void
    {
        $resultDays = $this->recurringInterval(14); // 2-week plan
        $this->assertSame('day', $resultDays['interval']);
        $this->assertSame(14, $resultDays['interval_count']);

        $resultMonthly = $this->recurringInterval(30); // 1-month plan
        $this->assertSame('month', $resultMonthly['interval']);
        $this->assertSame(1, $resultMonthly['interval_count']);

        $resultQuarterly = $this->recurringInterval(90); // 3-month plan
        $this->assertSame('month', $resultQuarterly['interval']);
        $this->assertSame(3, $resultQuarterly['interval_count']);

        $result365 = $this->recurringInterval(365);
        $this->assertSame('year', $result365['interval']);
        $this->assertSame(1, $result365['interval_count']);
    }

    public function test_leap_year_366_days_maps_to_one_year_preventing_stripe_exception(): void
    {
        $result = $this->recurringInterval(366);
        $this->assertSame('year', $result['interval']);
        $this->assertSame(1, $result['interval_count']);
    }

    public function test_multi_year_plans_map_to_year_interval(): void
    {
        $result = $this->recurringInterval(730); // 2 years
        $this->assertSame('year', $result['interval']);
        $this->assertSame(2, $result['interval_count']);

        $result3 = $this->recurringInterval(1095); // 3 years
        $this->assertSame('year', $result3['interval']);
        $this->assertSame(3, $result3['interval_count']);
    }
}
