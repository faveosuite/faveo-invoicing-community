<?php

declare(strict_types=1);

namespace App\License\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $scheme_query
 * @property int $scheme_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseScheme newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseScheme newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseScheme query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseScheme whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseScheme whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseScheme whereSchemeQuery($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseScheme whereSchemeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseScheme whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LicenseScheme extends Model
{
    protected $table = 'license_schemes';

    protected $fillable = [
        'scheme_query',
        'scheme_status',
    ];
}
