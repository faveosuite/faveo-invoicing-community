<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\BaseModel;

/**
 * @property int $state_subdivision_id
 * @property string $state_subdivision_name
 * @property string $country_code
 * @property string|null $iso2
 * @property string|null $primary_level_name
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property int $country_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereIso2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State wherePrimaryLevelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereStateSubdivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereStateSubdivisionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class State extends BaseModel
{
    protected $table = 'states_subdivisions';

    protected $primaryKey = 'state_subdivision_id';

    protected $fillable = [
        'state_subdivision_id', 'state_subdivision_name',
        'country_code', 'iso2',
        'primary_level_name', 'country_id',
        'latitude',
        'longitude',
    ];
}
