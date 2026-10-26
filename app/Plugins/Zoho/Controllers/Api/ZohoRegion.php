<?php

declare(strict_types=1);

namespace App\Plugins\Zoho\Controllers\Api;

enum ZohoRegion: string
{
    case UnitedStates = 'us';
    case Europe = 'eu';
    case India = 'in';
    case Australia = 'au';
    case Japan = 'jp';
    case China = 'cn';
    case Canada = 'ca';

    public function label(): string
    {
        return match ($this) {
            self::UnitedStates => 'United States',
            self::Europe => 'Europe',
            self::India => 'India',
            self::Australia => 'Australia',
            self::Japan => 'Japan',
            self::China => 'China',
            self::Canada => 'Canada',
        };
    }

    public function campaignsDomain(): string
    {
        return match ($this) {
            ZohoRegion::Europe => 'campaigns.zoho.eu',
            ZohoRegion::Australia => 'campaigns.zoho.com.au',
            ZohoRegion::India => 'campaigns.zoho.in',
            ZohoRegion::Japan => 'campaigns.zoho.jp',
            ZohoRegion::China => 'campaigns.zoho.com.cn',
            ZohoRegion::UnitedStates => 'campaigns.zoho.com',
            ZohoRegion::Canada => throw new \LogicException('Zoho Campaigns is not available in the Canada data center'),
        };
    }

    /** OAuth / Accounts domain */
    public function accountsDomain(): string
    {
        return match ($this) {
            self::Europe => 'accounts.zoho.eu',
            self::Australia => 'accounts.zoho.com.au',
            self::India => 'accounts.zoho.in',
            self::Japan => 'accounts.zoho.jp',
            self::China => 'accounts.zoho.com.cn',
            self::UnitedStates => 'accounts.zoho.com',
            self::Canada => 'accounts.zohocloud.ca',
        };
    }

    /** API base domain */
    public function apiDomain(): string
    {
        return match ($this) {
            self::Europe => 'www.zohoapis.eu',
            self::Australia => 'www.zohoapis.com.au',
            self::India => 'www.zohoapis.in',
            self::Japan => 'www.zohoapis.jp',
            self::China => 'www.zohoapis.com.cn',
            self::UnitedStates => 'www.zohoapis.com',
            self::Canada => 'www.zohoapis.ca',
        };
    }
}
