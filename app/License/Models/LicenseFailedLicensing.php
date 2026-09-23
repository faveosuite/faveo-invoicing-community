<?php

declare(strict_types=1);

namespace App\License\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Tracks failed license verify/install attempts per IP, for auto-ban.
 *
 * @property int $id
 * @property string $failed_licensing_ip
 * @property int $failed_licensing_attempts
 * @property string|null $failed_licensing_last_attempt_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseFailedLicensing newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseFailedLicensing newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseFailedLicensing query()
 *
 * @mixin \Eloquent
 */
class LicenseFailedLicensing extends Model
{
    protected $table = 'license_failed_licensings';

    protected $fillable = [
        'failed_licensing_ip',
        'failed_licensing_attempts',
        'failed_licensing_last_attempt_date',
    ];
}
