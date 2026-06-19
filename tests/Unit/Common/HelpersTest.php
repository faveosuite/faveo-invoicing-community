<?php

namespace Tests\Unit\Common;

use App\Model\Payment\TaxOption;
use Cache;
use Closure;
use Illuminate\Support\Facades\Date;
use Tests\DBTestCase;

class HelpersTest extends DBTestCase
{
    public function test_get_time_in_logged_in_user_time_zone_when_user_timezone_is_present_should_consider_timezone_as_user_timezone(): void
    {
        $this->getLoggedInUser('admin');
        $this->user->timezone()->updateOrCreate(['name' => 'Asia/Kolkata']);
        $this->assertEquals('Jan 1, 2001, 5:30 am', getTimeInLoggedInUserTimeZone(Date::now()->startOfMillennium()));
    }

    public function test_get_time_in_logged_in_user_time_zone_caches_user_timezone_for_five_seconds(): void
    {
        $this->getLoggedInUser('admin');

        Cache::shouldReceive('remember')->once()->withArgs(['user_timezone_'.$this->user->id, 5, Closure::class])->andReturn('Asia/Kolkata');

        getTimeInLoggedInUserTimeZone(Date::now()->startOfMillennium());
    }

    public function test_get_date_html_when_date_time_string_is_passed_as_null_should_return_dash(): void
    {
        $this->getLoggedInUser('admin');

        $this->assertEquals('--', getDateHtml());
    }

    public function test_get_date_html_when_valid_date_time_string_is_passed_as_null_should_return_formatted_date_in_html_form(): void
    {
        $this->getLoggedInUser('admin');

        $now = Date::now();

        $expectedDateTime = $now->clone()->setTimezone('Asia/Kolkata')->format('M j, Y, g:i a');
        $expectedDate = $now->clone()->setTimezone('Asia/Kolkata')->format('M j, Y');
        $this->assertEquals(sprintf("<label data-toggle='tooltip'style='font-weight:500; margin: 0px' data-placement='top' title='%s'>%s</label>", $expectedDateTime, $expectedDate), getDateHtml($now->toDateTimeString()));
    }

    public function test_bifurcate_tax_when_intra_state_tax_passed_returns_array_of_tax_and_value(): void
    {
        $this->getLoggedInUser();
        $this->user->country = 'IN';
        $this->withoutMiddleware();

        $a = bifurcateTax('CGST+SGST', '18%', 'INR', 'KA', '1000');
        $this->assertEquals($a, ['html' => 'CGST@9%<br>SGST@9%', 'tax' => '₹90.00<br>₹90.00']);
    }

    public function test_bifurcate_tax_when_inter_state_tax_passed_returns_array_of_tax_and_value(): void
    {
        $this->getLoggedInUser();
        $this->user->country = 'IN';
        $this->withoutMiddleware();

        $a = bifurcateTax('IGST', '18%', 'INR', 'IN-AP', '1000');
        $this->assertEquals($a, ['html' => 'IGST@18%', 'tax' => '₹180.00']);
    }

    public function test_bifurcate_tax_when_union_terretory_tax_passed_returns_array_of_tax_and_value(): void
    {
        $this->getLoggedInUser();
        $this->user->country = 'IN';
        $this->withoutMiddleware();

        $a = bifurcateTax('CGST+UTGST', '18%', 'INR', 'AN', '1000');
        $this->assertEquals($a, ['html' => 'CGST@9%<br>UTGST@9%', 'tax' => '₹90.00<br>₹90.00']);
    }

    public function test_bifurcate_tax_when_user_from_other_country_returns_array_of_tax_and_value(): void
    {
        $this->getLoggedInUser();
        $this->user->country = 'US';
        $this->withoutMiddleware();

        $a = bifurcateTax('VAT', '20%', 'INR', 'US-VA', '1000');
        $this->assertEquals($a, ['html' => 'VAT@20%', 'tax' => '₹200.00']);
    }

    // public function test_userCurrency_whenUserIsNotLoggedIn_returnsCurrencyAndSymbol()
    // {
    //     $this->withoutMiddleware();
    //     $currency = userCurrency();
    //     $this->assertEquals($currency['currency'], 'USD');
    // }

    // public function test_userCurrency_whenUserIsLoggedInAndRoleIsClient_returnsCurrencyAndSymbol()
    // {
    //     $this->getLoggedInUser();
    //     $this->withoutMiddleware();
    //     $currency = userCurrency();
    //     $this->assertEquals($currency['currency'], 'INR');
    // }

    // public function test_userCurrency_whenUserIsLoggedInAndRoleIsAdmin_returnsCurrencyAndSymbol()
    // {
    //     $this->getLoggedInUser('admin');
    //     $this->withoutMiddleware();
    //     $currency = userCurrency($this->user->id);
    //     $this->assertEquals($currency['currency'], 'INR');
    // }

    public function test_rounding_when_rounding_is_on_returns_rounded_off_price(): void
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $price = rounding('999.90');
        $this->assertEquals($price, '1000');
    }

    public function test_rounding_when_rounding_is_off_returns_price_upto_two_decimal_place(): void
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $tax_rule = new TaxOption;
        $tax_rule->findOrFail(1)->update(['rounding' => 0]);
        $price = rounding('999.6677777');
        $this->assertEquals($price, '999.67');
    }
}
