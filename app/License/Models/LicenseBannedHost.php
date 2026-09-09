<?php

declare(strict_types=1);

namespace App\License\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $banned_host_ip
 * @property string|null $comments
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseBannedHost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseBannedHost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseBannedHost query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseBannedHost whereBannedHostIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseBannedHost whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseBannedHost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseBannedHost whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseBannedHost whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class LicenseBannedHost extends Model
{
    protected $table = 'license_banned_hosts';

    protected $fillable = [
        'banned_host_ip',
        'comments',
    ];
}
