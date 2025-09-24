<?php

namespace App\Plugins\Zoho\Tests\Controllers\Api;

use App\Plugins\Zoho\Controllers\Api\ZohoRegion;
use Tests\DBTestCase;

class ZohoRegionTest extends DBTestCase
{
    public function test_it_returns_correct_label_for_each_region()
    {
        $expectations = [
            'us' => 'United States',
            'eu' => 'Europe',
            'in' => 'India',
            'au' => 'Australia',
            'jp' => 'Japan',
            'cn' => 'China',
        ];

        foreach ($expectations as $regionValue => $expectedDomain) {
            $region = ZohoRegion::from($regionValue);
            $this->assertEquals($expectedDomain, $region->label());
        }
    }

    public function test_it_returns_correct_campaigns_domain_for_each_region()
    {
        $expectations = [
            'us' => 'campaigns.zoho.com',
            'eu' => 'campaigns.zoho.eu',
            'in' => 'campaigns.zoho.in',
            'au' => 'campaigns.zoho.com.au',
            'jp' => 'campaigns.zoho.jp',
            'cn' => 'campaigns.zoho.com.cn',
        ];

        foreach ($expectations as $regionValue => $expectedDomain) {
            $region = ZohoRegion::from($regionValue);
            $this->assertEquals($expectedDomain, $region->campaignsDomain());
        }
    }

    public function test_it_returns_correct_accounts_domain_for_each_region()
    {
        $expectations = [
            'us' => 'accounts.zoho.com',
            'eu' => 'accounts.zoho.eu',
            'in' => 'accounts.zoho.in',
            'au' => 'accounts.zoho.com.au',
            'jp' => 'accounts.zoho.jp',
            'cn' => 'accounts.zoho.com.cn',
        ];

        foreach ($expectations as $regionValue => $expectedDomain) {
            $region = ZohoRegion::from($regionValue);
            $this->assertEquals($expectedDomain, $region->accountsDomain());
        }
    }

    public function test_it_returns_correct_api_domain_for_each_region()
    {
        $expectations = [
            'us' => 'www.zohoapis.com',
            'eu' => 'www.zohoapis.eu',
            'in' => 'www.zohoapis.in',
            'au' => 'www.zohoapis.com.au',
            'jp' => 'www.zohoapis.jp',
            'cn' => 'www.zohoapis.com.cn',
        ];

        foreach ($expectations as $regionValue => $expectedDomain) {
            $region = ZohoRegion::from($regionValue);
            $this->assertEquals($expectedDomain, $region->apiDomain());
        }
    }

    public function test_it_can_be_instantiated_from_string_value()
    {
        $this->assertEquals(ZohoRegion::UnitedStates, ZohoRegion::from('us'));
        $this->assertEquals(ZohoRegion::Europe, ZohoRegion::from('eu'));
        $this->assertEquals(ZohoRegion::India, ZohoRegion::from('in'));
        $this->assertEquals(ZohoRegion::Australia, ZohoRegion::from('au'));
        $this->assertEquals(ZohoRegion::Japan, ZohoRegion::from('jp'));
        $this->assertEquals(ZohoRegion::China, ZohoRegion::from('cn'));
    }

    public function test_it_returns_null_for_invalid_region_with_try_from()
    {
        $this->assertNull(ZohoRegion::tryFrom('invalid'));
    }

    public function test_it_has_correct_enum_values()
    {
        $this->assertEquals('us', ZohoRegion::UnitedStates->value);
        $this->assertEquals('eu', ZohoRegion::Europe->value);
        $this->assertEquals('in', ZohoRegion::India->value);
        $this->assertEquals('au', ZohoRegion::Australia->value);
        $this->assertEquals('jp', ZohoRegion::Japan->value);
        $this->assertEquals('cn', ZohoRegion::China->value);
    }

    public function test_it_returns_all_enum_cases()
    {
        $cases = ZohoRegion::cases();

        $this->assertCount(6, $cases);
        $this->assertContains(ZohoRegion::UnitedStates, $cases);
        $this->assertContains(ZohoRegion::Europe, $cases);
        $this->assertContains(ZohoRegion::India, $cases);
        $this->assertContains(ZohoRegion::Australia, $cases);
        $this->assertContains(ZohoRegion::Japan, $cases);
        $this->assertContains(ZohoRegion::China, $cases);
    }
}
