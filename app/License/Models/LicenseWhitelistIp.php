<?php

declare(strict_types=1);

namespace App\License\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $whitelist_host_ip
 * @property string|null $whitelist_host_comments
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseWhitelistIp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseWhitelistIp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseWhitelistIp query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseWhitelistIp whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseWhitelistIp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseWhitelistIp whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseWhitelistIp whereWhitelistHostComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseWhitelistIp whereWhitelistHostIp($value)
 * @mixin \Eloquent
 */
class LicenseWhitelistIp extends Model
{
    protected $table = 'license_whitelist_ips';

    protected $fillable = [
        'whitelist_host_ip',
        'whitelist_host_comments',
    ];
}
