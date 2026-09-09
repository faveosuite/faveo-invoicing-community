<?php

declare(strict_types=1);

namespace App\Plugins\Zoho\Integrations\Campaigns\Facades;

use App\Plugins\Zoho\Integrations\Campaigns\Controllers\Campaigns;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\LazyCollection;

/**
 * @method static void subscribe(string $email, array<mixed> $contactInfo = [], ?string $list = null)
 * @method static void resubscribe(string $email, array<mixed> $contactInfo = [], ?string $list = null)
 * @method static void unsubscribe(string $email, ?string $list = null)
 * @method static LazyCollection<int, mixed> subscribers(string $status = 'active', string $sort = 'asc', int $chunkSize = 500, ?string $list = null)
 * @method static int subscribersCount(string $status = 'active', ?string $list = null)
 * @method static Collection<int|string, mixed> tags()
 * @method static void attachTag(string $email, string $tag)
 * @method static void detachTag(string $email, string $tag)
 *
 * @see Campaigns
 */
class ZohoCampaigns extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'zoho.campaigns';
    }
}
