<?php

namespace Tests\Unit\Backend\Services;

use App\Services\DateRules;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DateRulesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-19 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_each_option_at_purchase(): void
    {
        $sub = '2027-11-19 10:00:00';
        $this->assertSame($sub, DateRules::ends(['ends' => 'subscription'], 'purchase', null, $sub));
        $this->assertSame('2027-02-19', DateRules::ends(['ends' => 'custom', 'every' => 3, 'unit' => 'month'], 'purchase', null, $sub));
        $this->assertSame('2026-11-30', DateRules::ends(['ends' => 'month_end'], 'purchase', null, $sub));
        $this->assertSame('2026-12-31', DateRules::ends(['ends' => 'year_end'], 'purchase', null, $sub));
        $this->assertSame('2027-03-31', DateRules::ends(['ends' => 'fixed', 'date' => '2027-03-31'], 'purchase', null, $sub));
        $this->assertNull(DateRules::ends(['ends' => 'never'], 'purchase', null, $sub));
    }

    public function test_renewal_continues_a_running_date_and_restarts_an_ended_one(): void
    {
        $days = ['ends' => 'custom', 'every' => 30, 'unit' => 'day'];
        $this->assertSame('2027-01-14', DateRules::ends($days, 'renewal', '2026-12-15', null)); // still running
        $this->assertSame('2026-12-19', DateRules::ends($days, 'renewal', '2026-10-01', null)); // ended: from today
        $this->assertSame('2026-12-31', DateRules::ends(['ends' => 'month_end'], 'renewal', '2026-11-30', null));
        $this->assertSame('2026-12-19', DateRules::ends($days, 'change', '2027-06-01', null)); // a plan change restarts
    }

    public function test_options_are_validated(): void
    {
        $this->assertSame('{"ends":"custom","every":90,"unit":"day"}', DateRules::normalize(['ends' => 'custom', 'every' => '90', 'unit' => 'day', 'x' => 1]));
        $this->assertNull(DateRules::normalize(null));
        $this->assertFalse(DateRules::normalize(['ends' => 'custom', 'every' => 0, 'unit' => 'day']));
        $this->assertFalse(DateRules::normalize(['ends' => 'fixed', 'date' => 'soon']));
        $this->assertFalse(DateRules::normalize('{"ends":"weekly"}'));
        $this->assertSame(['ends' => 'subscription'], DateRules::decode(null));
    }
}
